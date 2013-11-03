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
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/hooks.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/view-order-details.php';

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
	 * Function to load EDD options, settings default file upload options when not set by user
	 *
	 * @return array EDD options
	 */
	public function get_options() {
		global $edd_options;
		return wp_parse_args( $edd_options, array(
			'fu_upload_location' 	=> 'receipt',
			'fu_file_limit' 			=> '1',
			'fu_file_extensions' 	=> '',
		) );
	}

	/**
	 * Function to get uploaded files from EDD session
	 *
	 * @return array EDD file upload files
	 */
	public function get_session_files() {
		return wp_parse_args( EDD()->session->get( 'edd_fu_files' ), array() );
	}

	/**
	 * Function to add file to session
	 *
	 * @param $file_name
	 */
	public function add_file_to_session( $file_name ) {
		$session_files = $this->get_session_files();
		$session_files[] = $file_name;
		EDD()->session->set( 'edd_fu_files',  $session_files );
	}

	/**
	 * Function to delete file from session
	 *
	 * @param $file_name
	 *
	 * @return bool
	 */
	public function delete_file_from_session( $file_name ) {
		$session_files = $this->get_session_files();

		$file_key = array_search( $file_name, $session_files );
		if( $file_key !== false ) {
			unset( $session_files[ $file_key ] );
			EDD()->session->set( 'edd_fu_files',  $session_files );
			return true;
		}

		return false;

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
	 * Function to check file extension
	 *
	 * @param String $file_name
	 *
	 * @return bool
	 */
	private function check_file_extension( $file_name ) {

		// Get options
		$options 		= $this->get_options();
		$extensions = $options[ 'fu_file_extensions' ];

		// Check file extension
		if( $extensions != '' ) {

			$extensions = explode( ',', $extensions );

			if( ! in_array( edd_get_file_extension( $_FILES[ 'edd-fu-file' ][ 'name' ] ), $extensions ) ) {

				return false;

			}

		}

		return true;

	}

	/**
	 * Function to handle the file upload, also does the actual file upload
	 *
	 * @todo Add security checks
	 * @param $payment
	 */
	public function handle_file_upload( $payment ) {

		if( isset ( $_FILES[ 'edd-fu-file' ] ) && $_FILES[ 'edd-fu-file' ][ 'error' ] == 0 ) {

			// Get options
			$options = $this->get_options();

			// Check if the maximum
			$uploaded_files = get_post_meta( $payment->ID, 'edd_fu_file' );
			if( count( $uploaded_files ) >= $options[ 'fu_file_limit' ] ) {
				_e( 'Maximum number of file uploads reached.', 'edd-fu' );
				return;
			}

			// Check extension
			if( ! $this->check_file_extension( $_FILES[ 'edd-fu-file' ][ 'name' ] ) ) {
				_e( 'File extension not allowed.', 'edd-fu' );
				return;
			}

			// Create temp name
			$new_file_name = uniqid() . '.' . edd_get_file_extension( $_FILES[ 'edd-fu-file' ][ 'name' ] );

			// Upload file
			if( move_uploaded_file( $_FILES[ 'edd-fu-file' ][ 'tmp_name' ], EDD_File_Upload::instance()->get_file_dir() . '/' . $new_file_name ) ) {

				// Attach uploaded file to post
				add_post_meta( $payment->ID, 'edd_fu_file', $new_file_name );

			}

		}

	}

	/**
	 * Function to handle the temp file upload
	 */
	public function handle_temp_file_upload() {

		if( isset ( $_FILES[ 'edd-fu-file' ] ) && $_FILES[ 'edd-fu-file' ][ 'error' ] == 0 ) {

			// Get options
			$options = $this->get_options();

			// Check if the maximum
			$uploaded_files = $this->get_session_files();
			if( count( $uploaded_files ) >= $options[ 'fu_file_limit' ] ) {
				_e( 'Maximum number of file uploads reached.', 'edd-fu' );
				return;
			}

			// Check extension
			if( ! $this->check_file_extension( $_FILES[ 'edd-fu-file' ][ 'name' ] ) ) {
				_e( 'File extension not allowed', 'edd-fu' );
				return;
			}

			// Create temp name
			$new_file_name = uniqid() . '.' . pathinfo( $_FILES[ 'edd-fu-file' ][ 'name' ], PATHINFO_EXTENSION );

			// Upload file
			if( move_uploaded_file( $_FILES[ 'edd-fu-file' ][ 'tmp_name' ], get_temp_dir()  . $new_file_name ) ) {

				// Add temp file to session
				$this->add_file_to_session( $new_file_name );

			}

		}

	}

	/**
	 * Function that prints the temp uploaded files
	 */
	public function print_temp_uploaded_files() {

		echo "<fieldset id='edd_checkout_user_info'>\n";
			echo "<span><legend>" . __( 'Uploaded Files', 'edd-fu' ) . "</legend></span>\n";
			echo "<p id='edd-fu-files-wrap'>\n";

				$uploaded_files = $this->get_session_files();
				if( count( $uploaded_files ) > 0 ) {

					echo "<table>\n";

					$i = 1;
					foreach( $uploaded_files as $uploaded_file ) {
						echo "<tr>\n";

						echo "<td>\n";
						echo __ ( 'File', 'edd-fu' ) . $i;
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
			echo "</p>";
		echo "</fieldset>\n";

	}

	/**
	 * Function to handle the temp file delete
	 *
	 * @todo Add security checks
	 */
	public function handle_temp_file_delete() {

		if( isset( $_GET[ 'delete-file' ] ) ) {

			if( $this->delete_file_from_session( $_GET[ 'delete-file' ] ) ) {

				// delete file
				unlink( get_temp_dir() . $_GET[ 'delete-file' ] );

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

	/**
	 * Function to attach temp files (attached in checkout screen) to payment (on payment complete)
	 *
	 * @param $payment_id
	 */
	public function attach_temp_files_to_payment( $payment_id ) {

		$temp_files = $this->get_session_files();

		if( is_array( $temp_files ) && count( $temp_files ) > 0 ) {

			foreach( $temp_files as $temp_file ) {

				// Copy file from temp to upload dir
				if( copy( get_temp_dir() . $temp_file, EDD_File_Upload::instance()->get_file_dir() . '/' . $temp_file ) ) {

					// Attach uploaded file to post
					add_post_meta( $payment_id, 'edd_fu_file', $temp_file );

					// Remove file from temp
					unlink( get_temp_dir() . $temp_file );

					// Remove file from session
					$this->delete_file_from_session( $temp_file );

				}

			}

		}

	}

}

function EDD_File_Upload() {
	return EDD_File_Upload::instance();
}

// Run EDD File Upload
EDD_File_Upload();