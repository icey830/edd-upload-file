<?php
/**
 * Download functions
 *
 * @package     EDD\UploadFile\Download\Functions
 * @since       2.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Strip unique ID from filename
 *
 * This function is no longer used and is only left
 * for users who have pre-2.0.0 uploads which don't
 * include the data present in newer uploads.
 *
 * @since       1.0.3
 * @param       string $filename The original filename
 * @return      string $filename The filename sans ID
 */
function edd_upload_file_get_original_filename( $filename ) {
	$filename_parts = pathinfo( $filename );
	$filename       = substr( $filename_parts['filename'], 0, strrpos( $filename_parts['filename'], '-' ) );

	return $filename . '.' . $filename_parts['extension'];
}
