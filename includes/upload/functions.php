<?php
/**
 * Upload functions
 *
 * @package     EDD/UploadFile/Upload/Functions
 * @since       2.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Display an upload form
 *
 * @since       2.0.0
 * @param       array $cart_items The cart items
 * @return      void
 */
function edd_upload_file_display_form( $cart_items, $page, $payment_id = 0 ) {
	$allowed_items = array();

	// Check files for upload permission
	if ( count( $cart_items ) > 0 ) {
		foreach ( $cart_items as $cart_item ) {
			if ( get_post_meta( $cart_item['id'], '_edd_upload_file_enabled', true ) ? true : false ) {
				$allowed_items[ $cart_item['id'] ] = array(
					'download_id' => $cart_item['id'],
					'quantity'    => ( isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1 )
				);

				if ( ! empty( $cart_item['options'] ) && isset( $cart_item['options']['price_id'] ) ) {
					$allowed_items[ $cart_item['id'] ]['price_id'] = $cart_item['options']['price_id'];
				}
			}
		}
	}

	// Bail if nothing has upload enabled
	if ( empty( $allowed_items ) ) {
		return;
	}

	// Make sure Dashicons is loaded since the toggle needs it
	if ( ! wp_style_is( 'dashicons', 'enqueued' ) ) {
		wp_enqueue_style( 'dashicons' );
	}

	do_action( 'edd_upload_file_before' );

	if ( $page == 'checkout' ) {
		echo '<fieldset id="edd_checkout_upload_file">';
		echo '<legend>' . edd_get_option( 'edd_upload_file_form_title', __( 'Upload File(s)', 'edd-upload-file' ) ) . '</legend>';
	} else {
		echo '<h3>' . edd_get_option( 'edd_upload_file_form_title', __( 'Upload File(s)', 'edd-upload-file' ) ) . '</h3>';
		echo '<table id="edd-upload-file-form" class="edd-table"><tr><td>';
		echo '<input type="hidden" id="edd-upload-file-payment-id" value="' . $payment_id . '" />';
	}

	$desc      = edd_get_option( 'edd_upload_file_form_desc', false );
	$line_item = edd_get_option( 'edd_upload_file_line_item', sprintf( __( 'Upload up to %s %s for %s', 'edd-upload-file' ), '{limit}', '{files}', '{product}' ) );

	if ( $desc ) {
		echo '<p class="edd-upload-file-description"><span class="edd-description">' . $desc . '</span></p>';
	}

	foreach ( $allowed_items as $download ) {
		for ( $i = 1; $i <= $download['quantity']; $i++ ) {
			echo '<div class="edd-upload-file-upload-row">';

			$price_id = isset( $download['price_id'] ) ? $download['price_id'] : false;
			$field_id = $download['download_id'] . ( $price_id ? '-' . $price_id : '' ) . '-' . $i;

			$extensions = edd_upload_file_get_allowed_extensions( $download['download_id'] );
			$extensions = $extensions ? $extensions : 'false';

			echo '<label class="edd-label">' . edd_upload_file_parse_line_item( $line_item, $download['download_id'], $price_id ) . ' <a href="#" class="edd-upload-file-uploader-show"><span class="dashicons dashicons-arrow-down-alt2"></span></a><a href="#" class="edd-upload-file-uploader-hide" style="display: none;"><span class="dashicons dashicons-arrow-up-alt2"></span></a></label>';

			echo '<div id="edd-upload-file-uploader-' . $field_id . '" class="edd-upload-file-uploader" data-limit="' . edd_upload_file_get_limit( $download['download_id'] ) . '" data-extensions="' . $extensions . '" data-item-id="' . $field_id . '"></div>';
			echo '<input type="hidden" name="edd_upload_file_files[]" value="" />';
			echo '</div>';
		}
	}

	if ( $page == 'checkout' ) {
		echo '</fieldset>';
	} else {
		echo '</td></tr></table>';
	}

	do_action( 'edd_upload_file_after' );
}


/**
 * Parse an individual line item for the upload form
 *
 * @since       2.0.0
 * @param       string $line_item The line item to parse
 * @param       int $download_id The ID of the download to parse for
 * @param       int $price_id An (optional) price ID
 * @return      string $line_item The parsed line item
 */
function edd_upload_file_parse_line_item( $line_item, $download_id = 0, $price_id = null ) {
	$limit = edd_upload_file_get_limit( $download_id );

	if ( strstr( $line_item, '{limit}' ) ) {
		$line_item = str_replace( '{limit}', $limit, $line_item );
	}

	if ( strstr( $line_item, '{files}' ) ) {
		$files     = ( $limit == 1 ) ? __( 'file', 'edd-upload-file' ) : __( 'files', 'edd-upload-file' );
		$line_item = str_replace( '{files}', $files, $line_item );
	}

	if ( strstr( $line_item, '{product}' ) ) {
		$download_name = get_the_title( $download_id );

		if ( edd_has_variable_prices( $download_id ) ) {
			$prices         = edd_get_variable_prices( $download_id );
			$download_name .= ' - ' . $prices[$price_id]['name'];
		}

		$line_item = str_replace( '{product}', $download_name, $line_item );
	}

	return apply_filters( 'edd_upload_file_parse_line_item', $line_item, $download_id, $price_id );
}
