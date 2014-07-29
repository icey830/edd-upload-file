<?php
/**
 * Receipt template
 *
 * @package     EDD\UploadFile\Templates\Receipt
 * @since       1.0.1
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Add upload field to receipt
 *
 * @since		1.0.1
 * @param		object $payment The purchase we are working with
 * @param		array $edd_receipt_args Arguemnts for this receipt
 * @return		void
 */
function edd_upload_file_receipt_upload_field( $payment, $edd_receipt_args ) {
	$cart_items		= edd_get_payment_meta_cart_details( $payment->ID, true );
	$upload_enabled	= false;

	// Check files for upload permission
	if( count( $cart_items ) > 0 ) {
		foreach( $cart_items as $cart_item ) {
			if( get_post_meta( $cart_item['id'], '_edd_upload_file_enabled', true ) ? true : false ) {
				$upload_enabled = true;
				break;
			}
		}
	}

	// Return if we can't upload
	if( false === $upload_enabled ) {
		return;
	}

	// Get button style from EDD core
	$button_style	= edd_get_option( 'button_style', 'button' );
	$color			= edd_get_option( 'checkout_color', 'blue' );

	// Get the file upload limit
    //$limit = (int) edd_get_option( 'edd_upload_file_limit', 1 );
    $limit = (int) edd_upload_file_max_files( $payment );

    // Make sure we aren't over our limit
    $uploaded_files = EDD_Upload_File_Manager::instance()->get_session_files();
    if( $limit == 0 || empty( $uploaded_files ) || count( $uploaded_files ) < $limit ) {
		?>
		<h3><?php _e( 'Upload new file', 'edd-upload-file' ); ?></h3>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="edd-upload-file" value="" />
			<input type="submit" name="Submit" value="<?php _e( 'Upload', 'edd-upload-file' ); ?>" class="<?php echo $button_style . ' ' . $color; ?>" />
		</form>
		<?php
	}
}


/**
 * Print uploaded files on receipt
 *
 * @since		1.0.1
 * @param		object $payment The purchase we are working with
 * @param		array $edd_receipt_args Arguemnts for this receipt
 * @return		void
 */
function edd_upload_file_print_uploaded_files( $payment, $edd_receipt_args ) {
	EDD_Upload_File_Manager::instance()->print_uploaded_files( $payment->ID );
}


// Hook to the receipt page
if( edd_get_option( 'edd_upload_file_location' ) == 'receipt' ) {
	add_action( 'edd_payment_receipt_after_table', 'edd_upload_file_print_uploaded_files', 11, 2 );
	add_action( 'edd_payment_receipt_after_table', 'edd_upload_file_receipt_upload_field', 12, 2 );
}