<?php
/*
Plugin Name: EDD File Upload
Plugin URI: http://www.barrykooij.com/edd-file-upload
Description: EDD File Upload Extension
Version: 1.0.0
Author: Barry Kooij
Author URI: http://www.barrykooij.com/
*/

if ( ! defined( 'EDD_FILE_UPLOAD_PLUGIN_DIR' ) ) {
	define( 'EDD_FILE_UPLOAD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EDD_FILE_UPLOAD_PLUGIN_FILE' ) ) {
	define( 'EDD_FILE_UPLOAD_PLUGIN_FILE', __FILE__ );
}

class EDD_File_Upload {

	private static $instance 			= null;

	const PLUGIN_NAME							= 'EDD File Upload';
	const PLUGIN_VERSION_NAME 		= '1.0.0';
	const PLUGIN_VERSION_CODE 		= '1';
	const PLUGIN_AUTHOR						= 'Barry Kooij';

	private function __construct() {
		$this->includes();
	}

	public static function instance() {
		if( self::$instance == null ) {
			self::$instance = new EDD_File_Upload();
		}

		return self::$instance;
	}

	private function includes() {
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/settings.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/checkout-form.php';

		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/install.php';
	}

	public function get_file_dir() {
		return edd_get_upload_dir() . '/files';
	}

	public function get_file_url() {
		$upload_dir = wp_upload_dir();
		return $upload_dir[ 'baseurl' ] . '/edd/files';
	}

	/**
	 * Function that prints uploaded files of payment
	 *
	 * @param $payment_id
	 */
	public function print_uploaded_files( $payment_id ) {
		echo "<h3>" . __( 'Uploaded Files', 'edd-fu' ) . "</h3>\n";

		$uploaded_files = get_post_meta( $payment_id, 'edd_fu_file' );
		if( count( $uploaded_files ) > 0 ) {

			echo "<table>\n";

			$i = 1;
			foreach( $uploaded_files as $uploaded_file ) {
				echo "<tr>\n";

				echo "<td>\n";
				echo "<a href='" . EDD_File_Upload::instance()->get_file_url() . '/' . $uploaded_file . "' target='_blank'>" . __ ( 'File', 'edd-fu' ) . " {$i}</a>";
				echo "</td>\n";

				echo "<td>\n";
				echo "<a href='?delete-file={$uploaded_file}'>" . __ ( 'Delete', 'edd-fu' ) . "</a>";
				echo "</td>\n";

				echo "</tr>\n";
				$i++;
			}

			echo "</table>\n";

		}else {
			echo "<p>" . __( 'No files found', 'edd-fu' ) . "</p>";
		}

	}

	/**
	 * Function to handle the file upload, also does the actual file upload
	 *
	 * @todo Add security checks
	 * @param $payment
	 */
	public function handle_file_upload( $payment ) {

		if( isset ( $_FILES[ 'edd-fu-file' ] ) && $_FILES[ 'edd-fu-file' ][ 'error' ] == 0 ) {

			// Create temp name
			$new_file_name = uniqid() . '.' . pathinfo( $_FILES[ 'edd-fu-file' ][ 'name' ], PATHINFO_EXTENSION );

			// Upload file
			if( move_uploaded_file( $_FILES[ 'edd-fu-file' ][ 'tmp_name' ], EDD_File_Upload::instance()->get_file_dir() . '/' . $new_file_name ) ) {

				// Attach uploaded file to post
				add_post_meta( $payment->ID, 'edd_fu_file', $new_file_name );

			}

		}

	}

	/**
	 * Function to handle the file delete
	 *
	 * @todo Add security checks
	 * @param $payment
	 */
	public function handle_file_delete( $payment ) {

		if( isset( $_GET[ 'delete-file' ] ) ) {

			if( delete_post_meta( $payment->ID, 'edd_fu_file', $_GET[ 'delete-file' ] ) ) {

				// delete file
				unlink( EDD_File_Upload::instance()->get_file_dir() . '/' . $_GET[ 'delete-file' ] );

			}

		}

	}

}

function EDD_File_Upload() {
	return EDD_File_Upload::instance();
}

// Run EDD File Upload
EDD_File_Upload();