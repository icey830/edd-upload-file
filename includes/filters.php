<?php
/**
 * Filters
 *
 * @package         EDD\UploadFile\Filters
 * @since           2.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Adds our templates dir to the EDD template stack
 *
 * @since       2.0.0
 * @param       array $paths The existing template stack
 * @return      array $paths The updated template stack
 */
function edd_upload_file_add_template_stack( $paths ) {
	$paths[60] = EDD_UPLOAD_FILE_DIR . 'templates/';

	return $paths;
}
add_filter( 'edd_template_paths', 'edd_upload_file_add_template_stack' );
