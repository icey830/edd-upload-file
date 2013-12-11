<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_fu_checkout_upload_field() {

	// Get cart contents
	$cart_items = edd_get_cart_contents();

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
	$options = EDD_File_Upload::get_options();

	// Get correct button classes
	$button_style = isset( $options[ 'button_style' ] ) ? $options[ 'button_style' ] : 'button' ;
	$color				= isset( $options[ 'checkout_color' ] ) ? $options[ 'checkout_color' ] : 'blue' ;

	// Print uploaded files
	EDD_FU_File_Manager::instance()->print_temp_uploaded_files();

	?>
	<fieldset id="edd_checkout_user_info">
		<span><legend><?php _e( 'File Upload', 'edd-fu' ); ?></legend></span>

		<p id="edd-fu-upload-wrap">
			<label class="edd-label" for="edd-fu-file">
				<?php _e( 'File', 'edd-fu' ); ?> <span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'Please select the file to attach to this order.', 'edd-fu' ); ?></span>

		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="edd-fu-file" value="" />
			<input type="submit" name="Submit" value="<?php _e( 'Upload', 'edd-fu' ); ?>" class="<?php echo $button_style . ' ' . $color; ?>" />
		</form>
		</p>
	</fieldset>
<?php
}

$edd_fu_options = EDD_File_Upload::get_options();

if ( $edd_fu_options['fu_upload_location'] == 'checkout' ) {
	add_action( 'edd_before_purchase_form', 'edd_fu_checkout_upload_field', 10 );
	add_action( 'edd_complete_purchase', array( EDD_FU_File_Manager::instance(), 'attach_temp_files_to_payment' ), 10, 1 );
}