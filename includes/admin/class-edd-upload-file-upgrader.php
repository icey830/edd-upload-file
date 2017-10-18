<?php
/**
 * Upgrader routines.
 *
 * @package    EDD_UplaodFile
 * @subpackage Admin\Upgrades
 * @since      2.1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Upload_File_Upgrader Class.
 *
 * @since   2.1.3
 * @version 1.0
 */
final class EDD_Upload_File_Upgrader {

	/**
	 * Constructor Function
	 *
	 * @since  2.1.3
	 * @access protected
	 * @see    EDD_Upload_File_Upgrader::hooks()
	 */
	public function __construct() {
		$this->hooks();
		$this->check_if_upgrade_needed();
	}

	/**
	 * Checks if it's necessary to display an upgrade notice.
	 * 
	 * @since  2.1.3
	 * @access private
	 * @return void
	 */
	private function check_if_upgrade_needed() {
		global $wpdb;

		if ( edd_has_upgrade_completed( 'upload_file_upgrade_213_meta' ) ) {
			return;
		}

		$sql = "
			SELECT meta_key
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_edd_upload_file'
			LIMIT 1
		";
		$has_meta = $wpdb->get_col( $sql );

		if ( empty( $has_meta ) ) {
			edd_set_upgrade_complete( 'upload_file_upgrade_213_meta' );
		}
	}

	/**
	 * Adds all the hooks/filters.
	 *
	 * @since  2.1.3
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'edd_upload_file_upgrade_213_meta', array( $this, 'v213_meta_upgrades' ) );
	}

	/**
	 * Display Upgrade Notices
	 *
	 * @since  2.1.3
	 * @access public
	 * @return void
	 */
	public function admin_notices() {
		global $wpdb;

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'edd-upgrades' ) {
			return;
		}

		if ( ! edd_has_upgrade_completed( 'upload_file_upgrade_213_meta' ) ) {
			printf( '<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade the Upload File database, click <a href="%s">here</a> to start the upgrade.', 'edd-upload-file' ) . '</p></div>', esc_url_raw( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upload_file_upgrade_213_meta' ) ) );
		}
	}

	/**
	 * Meta upgrades for 2.1.3.
	 *
	 * @since 2.1.3
	 */
	public function v213_meta_upgrades() {
		global $wpdb;

		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_die( __( 'You do not have permission to do shop upgrades', 'edd-upload-file' ), __( 'Error', 'edd-upload-file' ), array( 'response' => 403 ) );
		}

		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}

		$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
		$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 10;
		$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

		$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

		if ( $step < 2 ) {
			// Check meta exists before progressing
			$sql = "
				SELECT meta_key
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_edd_upload_file'
				LIMIT 1
			";
			$has_meta = $wpdb->get_col( $sql );

			if ( empty( $has_meta ) ) {
				edd_set_upgrade_complete( 'upload_file_upgrade_213_meta' );
				delete_option( 'edd_doing_upgrade' );
				wp_redirect( admin_url() );
				exit;
			}
		}

		if ( empty( $total ) || $total <= 1 ) {
			$total_rows = $wpdb->get_row( "
				SELECT COUNT(meta_id) AS total
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_edd_upload_file'
				" );
			$total = $total_rows->total;
		}

		$existing_meta = $wpdb->get_results( $wpdb->prepare( "
				SELECT *
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				LIMIT %d, %d
				", '_edd_upload_file', $offset, $number ) );

		if ( ! empty( $existing_meta ) ) {
			$new_meta = array();

			// Filter all duplicate existing meta
			foreach ( $existing_meta as $result ) {
				$meta_value                                                      = maybe_unserialize( $result->meta_value );
				$new_meta[ $result->post_id ][ $meta_value['download']['id'] ][] = $meta_value;
				$new_meta[ $result->post_id ][ $meta_value['download']['id'] ]   = array_unique( $new_meta[ $result->post_id ][ $meta_value['download']['id'] ], SORT_REGULAR );
			}

			// Delete all meta
			$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_edd_upload_file' ) );

			// Recreate all the meta using the new schema
			foreach ( $new_meta as $post_id => $meta ) {
				update_post_meta( $post_id, '_edd_upload_file', $meta );
			}

			$step++;
			$redirect = esc_url_raw( add_query_arg( array(
				'page'        => 'edd-upgrades',
				'edd-upgrade' => 'upload_file_upgrade_213_meta',
				'step'        => $step,
				'number'      => $number,
				'total'       => $total,
			), admin_url( 'index.php' ) ) );
			wp_redirect( $redirect );
		} else {
			edd_set_upgrade_complete( 'upload_file_upgrade_213_meta' );
			delete_option( 'edd_doing_upgrade' );
			wp_redirect( admin_url() );
			exit;
		}
	}
}