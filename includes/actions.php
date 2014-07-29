<?php
/**
 * Process EDD actions
 *
 * @package     EDD\UploadFile\Actions
 * @since       1.0.1
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Return file upload directory
 *
 * @since		1.0.1
 * @return		string $upload_dir The file upload directory
 */
function edd_upload_file_delete() {
	if( isset( $_GET['delete-file']) ) {
		EDD_Upload_File_Manager::instance()->delete_file_from_session( $_GET['delete-file'] );

		// Actually delete file
		if( file_exists( get_temp_dir() . $_GET['delete-file'] ) ) {
			unlink( get_temp_dir() . $_GET['delete-file'] );
		}

		wp_redirect( remove_query_arg( array( 'edd_action', 'delete-file' ) ) );
		edd_die();
	}
}
add_action( 'edd_upload_file_delete', 'edd_upload_file_delete' );