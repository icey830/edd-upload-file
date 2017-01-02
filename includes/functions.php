<?php
/**
 * Helper Functions
 *
 * @package     EDD\UploadFile\Functions
 * @since       1.0.1
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Return file upload directory
 *
 * @since       1.0.1
 * @return      string $upload_dir The file upload directory
 */
function edd_upload_file_get_upload_dir() {
	$upload_dir = wp_upload_dir();

	return $upload_dir['basedir'] . '/edd-upload-files';
}


/**
 * Return file upload URL
 *
 * @since       1.0.1
 * @return      string $upload_url The file upload URL
 */
function edd_upload_file_get_upload_url() {
	$upload_dir = wp_upload_dir();

	return $upload_dir['baseurl'] . '/edd-upload-files';
}


/**
 * Get a list of allowed file types
 *
 * @since       2.0.0
 * @param       bool $echo Whether or not to echo the list
 * @return      array|string $file_types The list of file types
 */
function edd_upload_file_get_allowed_file_types( $echo = false ) {
	$mime_types = get_allowed_mime_types();
	$file_types = array_keys( $mime_types );

	if ( $echo ) {
		$third      = ceil( count( $file_types ) / 3 );
		$ext_list   = array_chunk( $file_types, $third );
		$file_types = '<div class="edd-upload-file-ext-list">';

		foreach ( $ext_list as $list => $col ) {
			$count = count( $col );
			$i     = 1;

			$file_types .= '<div class="edd-upload-file-ext-col">';

			foreach ( $col as $ext ) {
				$file_types .= $ext;

				if ( $i < $count ) {
					$file_types .= '<br />';
				}

				$i++;
			}

			if ( $list == '2' ) {
				for ( $i = $count; $i <= $third; $i++) {
					$file_types .= '<br />';
				}
			}

			$file_types .= '</div>';
		}

		$file_types .= '</div>';
	}

	return $file_types;
}


/**
 * Get the upload limit for a product
 *
 * @since       2.0.0
 * @param       int $download_id The download to get the limit for
 * @return      int $limit The upload limit for this download
 */
function edd_upload_file_get_limit( $download_id = 0 ) {
	// Get the global limit
	$limit         = edd_get_option( 'edd_upload_file_limit', 1 );
	$product_limit = get_post_meta( $download_id, '_edd_upload_file_limit', true );

	if ( $product_limit && $product_limit !== 0 ) {
		$limit = $product_limit;
	}

	return $limit;
}


/**
 * Get the allowed extensions for a product
 *
 * @since       2.0.0
 * @param       int $download_id The download to get the allowed extensions for
 * @return      false|string $extensions The allowed extensions for this download
 */
function edd_upload_file_get_allowed_extensions( $download_id = 0 ) {
	// Get the global extensions
	$extensions         = edd_get_option( 'edd_upload_file_extensions', array() );
	$product_extensions = get_post_meta( $download_id, '_edd_upload_file_extensions', true );

	if ( $product_extensions && $product_extensions !== '' ) {
		$extensions = $product_extensions;
	}

	if ( ! $extensions || $extensions == '' ) {
		$extensions = false;
	} else {
		$extensions = str_replace( ' ', '', $extensions );
	}

	return $extensions;
}


/**
 * Delete a file
 *
 * @since       2.1.0
 * @param       string $file The file to delete
 * @return      void
*/
function edd_upload_file_delete_file( $file = '' ) {
	$fullpath = edd_upload_file_get_upload_dir() . '/' . $file;

	if ( file_exists( $fullpath ) ) {
		if ( unlink( $fullpath ) ) {
			return true;
		}
	}

	return false;
}
