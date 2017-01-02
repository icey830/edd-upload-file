<?php
/**
 * Upload handler
 *
 * @package     EDD/UploadFile/UploadHandler
 * @since       2.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDD_Upload_File_Uploader Class
 *
 * @since       2.0.0
 */
class EDD_Upload_File_Uploader {


	/**
	 * Setup our endpoint
	 *
	 * @access      public
	 * @since       2.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( 'wp', array( $this, 'process_query' ), -1 );
	}


	/**
	 * Registers a new rewrite endpoint
	 *
	 * @access      public
	 * @since       2.0.0
	 * @param       array $rewrite_rules WordPress Rewrite Rules
	 */
	public function add_endpoint( $rewrite_rules ) {
		add_rewrite_endpoint( 'edd-upload-file', EP_ALL );
	}


	/**
	 * Listens for and process uploads
	 *
	 * @access      public
	 * @since       2.0.0
	 * @return      void
	 */
	public function process_query() {
		global $wp_query;

		// Check for edd-upload-file var. Get out if not present
		if ( empty( $wp_query->query_vars['edd-upload-file'] ) ) {
			return;
		}

		require_once EDD_UPLOAD_FILE_DIR . 'includes/upload/upload-handler.php';

		$uploader = new UploadHandler();

		$uploader->allowedExtensions = array();
		$uploader->sizeLimit         = null;
		$uploader->inputName         = "qqfile";
		$uploader->chunksFolder      = edd_upload_file_get_upload_dir() . '-chunk';
		$output_folder               = edd_upload_file_get_upload_dir();

		$method = $_SERVER['REQUEST_METHOD'];
		if ( $method == 'POST' ) {
			header('Content-Type: text/plain');

			if ( $wp_query->query_vars['edd-upload-file'] == 'done' ) {
				$result = $uploader->combineChunks( $output_folder );
			} else {
				$result = $uploader->handleUpload( $output_folder );

				$result['uploadName'] = $uploader->getUploadName();
			}

			echo json_encode( $result );
		} elseif ( $method == 'DELETE' ) {
			$result = $uploader->handleDelete( $output_folder );

			echo json_encode( $result );
		} else {
			header( 'HTTP/1.0 405 Method Not Allowed' );
		}

		edd_die();
	}
}
