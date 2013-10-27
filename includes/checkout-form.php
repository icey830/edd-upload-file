<?php

function edd_fu_confirmation_upload_field( $payment, $edd_receipt_args ) {

	// Handle the upload
	EDD_File_Upload::instance()->handle_file_upload( $payment );

	// Handle the delete
	EDD_File_Upload::instance()->handle_file_delete( $payment );

	// Print uploaded files
	EDD_File_Upload::instance()->print_uploaded_files( $payment->ID );

	?>
		<h3><?php _e( 'Upload new file', 'edd-fu' ); ?></h3>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="edd-fu-file" value="" />
			<input type="submit" name="Submit" value="<?php _e( 'Upload', 'edd-fu' ); ?>" />
		</form>

	<?php

	/*
?>
<fieldset id="edd_checkout_user_info">
	<span><legend><?php _e( 'File Upload', 'edd-fu' ); ?></legend></span>
	<p id="edd-email-wrap">
		<label class="edd-label" for="edd-fu-file">
			<?php _e( 'File', 'edd-fu' ); ?> <span class="edd-required-indicator">*</span>
		</label>
		<span class="edd-description"><?php _e( 'Please select the file to attach to this order.', 'edd-fu' ); ?></span>
		<input class="edd-input required" type="file" name="edd_fu_file" id="edd-fu-file" value="" />
	</p>
</fieldset>
<?php
*/
}

//add_action( 'edd_checkout_form_top', 'edd_fu_purchase_form_upload_field' );
add_action( 'edd_payment_receipt_after', 'edd_fu_confirmation_upload_field', 10, 2 );