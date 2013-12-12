<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_fu_receipt_upload_field( $payment, $edd_receipt_args ) {

	// Get cart contents
	$cart_items = edd_get_payment_meta_cart_details( $payment->ID, true );

	// Set default file upload
	$fu_enabled = false;

	// Loop & check
	if ( count( $cart_items ) > 0 ) {
		foreach ( $cart_items as $cart_item ) {
			if ( ( get_post_meta( $cart_item['id'], '_edd_fu_enabled', true ) ? true : false ) ) {
				$fu_enabled = true;
				break;
			}
		}
	}

	// Check if there is at least one product that has file uploads enabled
	if ( false === $fu_enabled ) {
		return;
	}

	// Get EDD options
	$options = EDD_Upload_File::get_options();

	// Get correct button classes
	$button_style = isset( $options[ 'button_style' ] ) ? $options[ 'button_style' ] : 'button' ;
	$color				= isset( $options[ 'checkout_color' ] ) ? $options[ 'checkout_color' ] : 'blue' ;

?>
	<h3><?php _e( 'Upload new file', 'edd-fu' ); ?></h3>
	<form action="" method="post" enctype="multipart/form-data">
		<input type="file" name="edd-fu-file" value="" />
		<input type="submit" name="Submit" value="<?php _e( 'Upload', 'edd-fu' ); ?>" class="<?php echo $button_style . ' ' . $color; ?>" />
	</form>

<?php

}

$edd_fu_options = EDD_Upload_File::get_options();

if ( $edd_fu_options['fu_upload_location'] == 'receipt' ) {
	add_action( 'edd_payment_receipt_after_table', 'edd_fu_receipt_upload_field', 12, 2 );
}