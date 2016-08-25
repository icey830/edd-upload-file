<?php
/**
 * Helper Functions
 *
 * @package     EDD\UploadFile\Functions
 * @since       1.0.1
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
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
 * @since       1.1.0
 * @param       bool $echo Whether or not to echo the list
 * @return      array|string $file_types The list of file types
 */
function edd_upload_file_get_allowed_file_types( $echo = false ) {
	$mime_types = get_allowed_mime_types();
	$file_types = array_keys( $mime_types );

	if( $echo ) {
		$third      = ceil( count( $file_types ) / 3 );
		$ext_list   = array_chunk( $file_types, $third );
		$file_types = '<div class="edd-upload-file-ext-list">';

		foreach( $ext_list as $list => $col ) {
			$count = count( $col );
			$i     = 1;

			$file_types .= '<div class="edd-upload-file-ext-col">';

			foreach( $col as $ext ) {
				$file_types .= $ext;

				if( $i < $count ) {
					$file_types .= '<br />';
				}

				$i++;
			}

			if( $list == '2' ) {
				for( $i = $count; $i <= $third; $i++) {
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
 * @since       1.1.0
 * @param       int $download_id The download to get the limit for
 * @return      int $limit The upload limit for this download
 */
function edd_upload_file_get_limit( $download_id = 0 ) {
	// Get the global limit
	$limit         = edd_get_option( 'edd_upload_file_limit', 1 );
	$product_limit = get_post_meta( $download_id, '_edd_upload_file_limit', true );

	if( $product_limit && $product_limit !== 0 ) {
		$limit = $product_limit;
	}

	return $limit;
}


/**
 * Get the allowed extensions for a product
 *
 * @since       1.1.0
 * @param       int $download_id The download to get the allowed extensions for
 * @return      false|string $extensions The allowed extensions for this download
 */
function edd_upload_file_get_allowed_extensions( $download_id = 0 ) {
	// Get the global extensions
	$extensions         = edd_get_option( 'edd_upload_file_extensions', array() );
	$product_extensions = get_post_meta( $download_id, '_edd_upload_file_extensions', true );

	if( $product_extensions && $product_extensions !== '' ) {
		$extensions = $product_extensions;
	}

	if( ! $extensions || $extensions == '' ) {
		$extensions = false;
	} else {
		$extensions = str_replace( ' ', '', $extensions );
	}

	return $extensions;
}


/**
 * Parse an individual line item for the upload form
 *
 * @since       1.1.0
 * @param       string $line_item The line item to parse
 * @param       int $download_id The ID of the download to parse for
 * @param       int $price_id An (optional) price ID
 * @return      string $line_item The parsed line item
 */
function edd_upload_file_parse_line_item( $line_item, $download_id = 0, $price_id = null ) {
	$limit = edd_upload_file_get_limit( $download_id );

	if( strstr( $line_item, '{limit}' ) ) {
		$line_item = str_replace( '{limit}', $limit, $line_item );
	}

	if( strstr( $line_item, '{files}' ) ) {
		$files     = ( $limit == 1 ) ? __( 'file', 'edd-upload-file' ) : __( 'files', 'edd-upload-file' );
		$line_item = str_replace( '{files}', $files, $line_item );
	}

	if( strstr( $line_item, '{product}' ) ) {
		$download_name = get_the_title( $download_id );

		if( edd_has_variable_prices( $download_id ) ) {
			$prices         = edd_get_variable_prices( $download_id );
			$download_name .= ' - ' . $prices[$price_id]['name'];
		}

		$line_item = str_replace( '{product}', $download_name, $line_item );
	}

	return apply_filters( 'edd_upload_file_parse_line_item', $line_item, $download_id, $price_id );
}


/**
 * Display an upload form
 *
 * @since       1.1.0
 * @param       array $cart_items The cart items
 * @return      void
 */
function edd_upload_file_display_form( $cart_items, $page, $payment_id = 0 ) {
	$allowed_items = array();

	// Check files for upload permission
	if( count( $cart_items ) > 0 ) {
		foreach( $cart_items as $cart_item ) {
			if( get_post_meta( $cart_item['id'], '_edd_upload_file_enabled', true ) ? true : false ) {
				$allowed_items[$cart_item['id']] = array(
					'download_id' => $cart_item['id'],
					'quantity'    => ( isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1 )
				);

				if( ! empty( $cart_item['options'] ) && isset( $cart_item['options']['price_id'] ) ) {
					$allowed_items[$cart_item['id']]['price_id'] = $cart_item['options']['price_id'];
				}
			}
		}
	}

	// Bail if nothing has upload enabled
	if( empty( $allowed_items ) ) {
		return;
	}

	do_action( 'edd_upload_file_before' );

	if( $page == 'checkout' ) {
		echo '<fieldset id="edd_checkout_user_info">';
		echo '<legend>' . edd_get_option( 'edd_upload_file_form_title', __( 'Upload File(s)', 'edd-upload-file' ) ) . '</legend>';
	} else {
		echo '<h3>' . edd_get_option( 'edd_upload_file_form_title', __( 'Upload File(s)', 'edd-upload-file' ) ) . '</h3>';
		echo '<table id="edd-upload-file-form" class="edd-table"><tr><td>';
		echo '<input type="hidden" id="edd-upload-file-payment-id" value="' . $payment_id . '" />';
	}

	$desc      = edd_get_option( 'edd_upload_file_form_desc', false );
	$line_item = edd_get_option( 'edd_upload_file_line_item', sprintf( __( 'Upload up to %s %s for %s', 'edd-upload-file' ), '{limit}', '{files}', '{product}' ) );

	if( $desc ) {
		echo '<span class="edd-description">' . $desc . '</span><hr />';
	}

	foreach( $allowed_items as $download ) {
		for( $i = 1; $i <= $download['quantity']; $i++ ) {
			echo '<div class="edd-upload-file-upload-row">';

			$price_id = isset( $download['price_id'] ) ? $download['price_id'] : false;
			$field_id = $download['download_id'] . ( $price_id ? '-' . $price_id : '' ) . '-' . $i;

			$extensions = edd_upload_file_get_allowed_extensions( $download['download_id'] );
			$extensions = $extensions ? $extensions : 'false';

			echo '<label class="edd-label">' . edd_upload_file_parse_line_item( $line_item, $download['download_id'], $price_id ) . ' <a href="#" class="edd-upload-file-uploader-toggle"><span class="dashicons dashicons-sort"></span></a></label>';

			echo '<div id="edd-upload-file-uploader-' . $field_id . '" class="edd-upload-file-uploader" data-limit="' . edd_upload_file_get_limit( $download['download_id'] ) . '" data-extensions="' . $extensions . '" data-item-id="' . $field_id . '"></div>';
			echo '<input type="hidden" name="edd_upload_file_files[]" value="" />';
			echo '</div>';
		}
	}

	if( $page == 'checkout' ) {
		echo '</fieldset>';
	} else {
		echo '</td></tr></table>';
	}

	do_action( 'edd_upload_file_after' );
}
