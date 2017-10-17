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
	 * @since 2.0
	 * @access public
	 * @return void
	 */
	public function admin_notices() {
		global $wpdb;

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'edd-upgrades' ) {
			return;
		}

		$upload_file_version = get_option( 'edd_upload_file_version' );

		if ( version_compare( edd_reviews()->version, '2.1.3', '<' ) || ! edd_has_upgrade_completed( 'upload_file_upgrade_213_meta' ) ) {
			printf(
				'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade the Upload File database, click <a href="%s">here</a> to start the upgrade.', 'edd-upload-file' ) . '</p></div>',
				esc_url_raw( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upload_file_upgrade_213_meta' ) )
			);
		}
	}

	/**
	 * Meta upgrades for 2.1.3.
	 *
	 * @since 2
	 */
	public function v213_meta_upgrades() {
		global $wpdb;
	}
}