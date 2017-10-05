<?php
/**
 * Upload actions
 *
 * @package     EDD\UploadFile\Actions
 * @since       1.0.1
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add upload field to checkout
 *
 * @since       1.0.1
 * @return      void
 */
function edd_upload_file_display_checkout_upload_field() {
	// Bail if the form is displayed on receipt
	if ( edd_get_option( 'edd_upload_file_location', 'checkout' ) != 'checkout' ) {
		return;
	}

	$cart_items = edd_get_cart_contents();

	edd_upload_file_display_form( $cart_items, 'checkout' );
}
add_action( 'edd_before_purchase_form', 'edd_upload_file_display_checkout_upload_field', 10 );


/**
 * Add upload field to receipt
 *
 * @since       1.0.1
 * @param		object $payment The purchase we are working with
 * @param		array $edd_receipt_args Arguemnts for this receipt
 * @return      void
 */
function edd_upload_file_display_receipt_upload_field( $payment, $edd_receipt_args ) {
	// Bail if the form is displayed on checkout
	if ( edd_get_option( 'edd_upload_file_location', 'checkout' ) == 'checkout' ) {
		return;
	}

	if ( isset( $_GET['payment_key'] ) || get_post_meta( $payment->ID, '_edd_upload_file', false ) ) {
		return;
	}

	$cart_items = edd_get_payment_meta_cart_details( $payment->ID, true );

	edd_upload_file_display_form( $cart_items, 'receipt', $payment->ID );
}
add_action( 'edd_payment_receipt_after_table', 'edd_upload_file_display_receipt_upload_field', 12, 2 );


/**
 * Link files on payment completion
 *
 * @since       1.1.3
 *
 * @param       int    $payment_id The ID for this purchase
 * @param       object $payment    The payment object being saved
 *
 * @return      void
 */
function edd_upload_file_link_files( $payment_id, $payment ) {
	if ( isset( $_POST['edd-upload-file'] ) ) {
		foreach ( $_POST['edd-upload-file'] as $file ) {
			$file_data = substr( $file, 1, -1 );
			$file_data = explode( '}{', $file_data );

			$download_data = explode( '-', $file_data[0] );
			$meta_data     = array(
				'uuid'     => $file_data[1],
				'filename' => $file_data[2]
			);

			if ( count( $download_data ) == 3 ) {
				$meta_data['download'] = array(
					'id'       => $download_data[0],
					'price_id' => $download_data[1],
					'item_id'  => $download_data[2]
				);
			} else {
				$meta_data['download'] = array(
					'id'      => $download_data[0],
					'item_id' => $download_data[1]
				);
			}

			add_post_meta( $payment_id, '_edd_upload_file', $meta_data );
		}
	}
}
add_action( 'edd_payment_saved', 'edd_upload_file_link_files', 10, 2 );


/**
 * Link files on receipt upload
 *
 * @since       2.0.0
 * @return      void
 */
function edd_upload_file_ajax_update_files() {
	if ( ! empty( $_POST['download_data'] ) || ! empty( $_POST['uuid'] ) || ! empty( $_POST['filename'] ) || ! empty( $_POST['payment_id'] ) ) {

		$download_data = explode( '-', $_POST['download_data'] );
		$meta_data     = array(
			'uuid'     => $_POST['uuid'],
			'filename' => $_POST['filename']
		);

		if ( count( $download_data ) == 3 ) {
			$meta_data['download'] = array(
				'id'       => $download_data[0],
				'price_id' => $download_data[1],
				'item_id'  => $download_data[2]
			);
		} else {
			$meta_data['download'] = array(
				'id'      => $download_data[0],
				'item_id' => $download_data[1]
			);
		}

		$return = add_post_meta( $_POST['payment_id'], '_edd_upload_file', $meta_data );

		echo json_encode($return);
	}
	edd_die();
}
add_action( 'wp_ajax_edd_upload_file_process', 'edd_upload_file_ajax_update_files' );
add_action( 'wp_ajax_nopriv_edd_upload_file_process', 'edd_upload_file_ajax_update_files' );


/**
 * Unink files on receipt delete
 *
 * @since       2.0.0
 * @return      void
 */
function edd_upload_file_ajax_delete_files() {
	if ( ! empty( $_POST['download_data'] ) || ! empty( $_POST['payment_id'] ) ) {

		$file_data = substr( $_POST['download_data'], 1, -1 );
		$file_data = explode( '}{', $file_data );

		$download_data = explode( '-', $file_data[0] );
		$meta_data     = array(
			'uuid'     => $file_data[1],
			'filename' => $file_data[2]
		);

		if ( count( $download_data ) == 3 ) {
			$meta_data['download'] = array(
				'id'       => $download_data[0],
				'price_id' => $download_data[1],
				'item_id'  => $download_data[2]
			);
		} else {
			$meta_data['download'] = array(
				'id'      => $download_data[0],
				'item_id' => $download_data[1]
			);
		}

		$return = delete_post_meta( $_POST['payment_id'], '_edd_upload_file', $meta_data );

		echo json_encode($return);
	}
	edd_die();
}
add_action( 'wp_ajax_edd_upload_file_delete', 'edd_upload_file_ajax_delete_files' );
add_action( 'wp_ajax_nopriv_edd_upload_file_delete', 'edd_upload_file_ajax_delete_files' );
