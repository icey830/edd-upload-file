<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_fu_receipt_upload_field( $payment, $edd_receipt_args ) {

	// Get EDD options
	$options = EDD_File_Upload::get_options();

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

$edd_fu_options = EDD_File_Upload::get_options();

if ( $edd_fu_options['fu_upload_location'] == 'receipt' ) {
	add_action( 'edd_payment_receipt_after_table', 'edd_fu_receipt_upload_field', 12, 2 );
}