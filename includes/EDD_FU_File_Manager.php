<?php

class EDD_FU_File_Manager {

	private static $instance = null;

	/**
	 * Singleton getter
	 *
	 * @return EDD_FU_File_Manager|null
	 */
	public static function instance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->hooks();
	}

	/**
	 * Setup hooks
	 */
	private function hooks() {

		// Get options
		$edd_fu_options = EDD_File_Upload::get_options();

		if ( $edd_fu_options['fu_upload_location'] == 'checkout' ) {
			add_action( 'template_redirect', array( $this, 'handle_temp_file_upload' ) );
		} else {
			add_action( 'edd_payment_receipt_before', array( $this, 'handle_file_upload' ), 0, 1 );
		}

	}

	/**
	 * Function to get the path to the files directory
	 *
	 * @return string
	 */
	public function get_file_dir() {
		$wp_upload_dir = wp_upload_dir();
		return $wp_upload_dir['basedir'] . '/edd-upload-files';
	}

	/**
	 * Function to get the url to the files directory
	 *
	 * @return string
	 */
	public function get_file_url() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . '/edd-upload-files';
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
		$options    = EDD_File_Upload::get_options();
		$extensions = $options['fu_file_extensions'];

		// Check file extension
		if ( $extensions != '' ) {

			$extensions = explode( ',', $extensions );

			if ( ! in_array( edd_get_file_extension( $_FILES['edd-fu-file']['name'] ), $extensions ) ) {

				return false;

			}

		}

		return true;

	}

	/**
	 * Function that prints uploaded files of payment
	 *
	 * @param $payment_id
	 */
	public function print_uploaded_files( $payment_id ) {

		echo "<h3>" . __( 'Uploaded Files', 'edd-fu' ) . "</h3>\n";

		$uploaded_files = get_post_meta( $payment_id, 'edd_fu_file' );
		if ( count( $uploaded_files ) > 0 ) {

			echo "<table>\n";

			$i = 1;
			foreach ( $uploaded_files as $uploaded_file ) {
				echo "<tr>\n";

				echo "<td>\n";
				echo "<a href='" . $this->get_file_url() . '/' . $uploaded_file . "' target='_blank'>" . __( 'File', 'edd-fu' ) . " {$i}</a>";
				echo "</td>\n";

				echo "<td>\n";
				echo "<a href='?delete-file={$uploaded_file}'>" . __( 'Delete', 'edd-fu' ) . "</a>";
				echo "</td>\n";

				echo "</tr>\n";
				$i ++;
			}

			echo "</table>\n";

		}
		else {
			echo "<p>" . __( 'No files found', 'edd-fu' ) . "</p>";
		}

	}

	/**
	 * Function to handle the file upload, also does the actual file upload
	 *
	 * @todo Add security checks
	 *
	 * @param $payment
	 */
	public function handle_file_upload( $payment ) {

		if ( isset ( $_FILES['edd-fu-file'] ) && $_FILES['edd-fu-file']['error'] == 0 ) {

			// Get options
			$options = EDD_File_Upload::get_options();

			$file_limit = (int) $options['fu_file_limit'];

			// Empty string is also unlimited
			if( $file_limit == '' ) {
				$file_limit = 0;
			}

			// Check if the maximum
			$uploaded_files = get_post_meta( $payment->ID, 'edd_fu_file' );
			if ( $file_limit != 0 && count( $uploaded_files ) >= $options['fu_file_limit'] ) {
				EDD_File_Upload::error_message( __( 'Maximum number of file uploads reached.', 'edd-fu' ) );
				return;
			}

			// Check extension
			if ( ! $this->check_file_extension( $_FILES['edd-fu-file']['name'] ) ) {
				EDD_File_Upload::error_message( __( 'File extension not allowed.', 'edd-fu' ) );
				return;
			}

			// Create temp name
			$new_file_name = uniqid() . '.' . edd_get_file_extension( $_FILES['edd-fu-file']['name'] );

			// Upload file
			if ( move_uploaded_file( $_FILES['edd-fu-file']['tmp_name'], $this->get_file_dir() . '/' . $new_file_name ) ) {

				// Attach uploaded file to post
				add_post_meta( $payment->ID, 'edd_fu_file', $new_file_name );

			}

		}

	}

	/**
	 * Function to handle the file delete
	 *
	 * @todo Add security checks
	 *
	 * @param $payment
	 */
	public function handle_file_delete( $payment ) {

		if ( isset( $_GET['delete-file'] ) ) {

			if ( delete_post_meta( $payment->ID, 'edd_fu_file', $_GET['delete-file'] ) ) {

				// delete file
				if ( file_exists( $this->get_file_dir() . '/' . $_GET['delete-file'] ) )
					unlink( $this->get_file_dir() . '/' . $_GET['delete-file'] );

			}

		}

	}

	/**
	 * SESION FUNCTIONS
	 */

	/**
	 * Function to handle the temp file upload
	 */
	public function handle_temp_file_upload() {

		if ( edd_is_checkout() && isset ( $_FILES['edd-fu-file'] ) && $_FILES['edd-fu-file']['error'] == 0 ) {

			// Get options
			$options = EDD_File_Upload::get_options();

			// Check if the maximum
			$uploaded_files = $this->get_session_files();
			if ( count( $uploaded_files ) >= $options['fu_file_limit'] ) {
				EDD_File_Upload::error_message( __( 'Maximum number of file uploads reached.', 'edd-fu' ) );
				return;
			}

			// Check extension
			if ( ! $this->check_file_extension( $_FILES['edd-fu-file']['name'] ) ) {
				EDD_File_Upload::error_message( __( 'File extension not allowed.', 'edd-fu' ) );
				return;
			}

			// Create temp name
			$new_file_name = uniqid() . '.' . pathinfo( $_FILES['edd-fu-file']['name'], PATHINFO_EXTENSION );

			// Upload file
			if ( move_uploaded_file( $_FILES['edd-fu-file']['tmp_name'], get_temp_dir() . $new_file_name ) ) {

				// Add temp file to session
				$this->add_file_to_session( $new_file_name );

			}

		}

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
		$session_files   = $this->get_session_files();
		$session_files[] = $file_name;
		EDD()->session->set( 'edd_fu_files', $session_files );
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
		if ( $file_key !== false ) {
			unset( $session_files[$file_key] );
			EDD()->session->set( 'edd_fu_files', $session_files );
			return true;
		}

		return false;

	}

	/**
	 * Function that prints the temp uploaded files
	 */
	public function print_temp_uploaded_files() {

		echo "<fieldset id='edd_checkout_user_info'>\n";
		echo "<span><legend>" . __( 'Uploaded Files', 'edd-fu' ) . "</legend></span>\n";
		echo "<p id='edd-fu-files-wrap'>\n";

		$uploaded_files = $this->get_session_files();
		if ( count( $uploaded_files ) > 0 ) {

			echo "<table>\n";

			$i = 1;
			foreach ( $uploaded_files as $uploaded_file ) {
				echo "<tr>\n";

				echo "<td>\n";
				echo __( 'File', 'edd-fu' ) . $i;
				echo "</td>\n";

				echo "<td>\n";
				echo "<a href='?delete-file={$uploaded_file}'>" . __( 'Delete', 'edd-fu' ) . "</a>";
				echo "</td>\n";

				echo "</tr>\n";
				$i ++;
			}

			echo "</table>\n";

		}
		else {
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

		if ( isset( $_GET['delete-file'] ) ) {

			if ( $this->delete_file_from_session( $_GET['delete-file'] ) ) {

				// delete file
				if ( file_exists( get_temp_dir() . $_GET['delete-file'] ) )
					unlink( get_temp_dir() . $_GET['delete-file'] );

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

		if ( is_array( $temp_files ) && count( $temp_files ) > 0 ) {

			foreach ( $temp_files as $temp_file ) {

				// Copy file from temp to upload dir
				if ( copy( get_temp_dir() . $temp_file, $this->get_file_dir() . '/' . $temp_file ) ) {

					// Attach uploaded file to post
					add_post_meta( $payment_id, 'edd_fu_file', $temp_file );

					// Remove file from temp
					if ( file_exists( get_temp_dir() . $temp_file ) )
						unlink( get_temp_dir() . $temp_file );

					// Remove file from session
					$this->delete_file_from_session( $temp_file );

				}

			}

		}

	}

} 