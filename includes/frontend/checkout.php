<?php

function edd_fu_checkout_upload_field() {

	// Handle the temp upload
	EDD_FU_File_Manager::instance()->handle_temp_file_upload();

	// Handle the delete
	EDD_FU_File_Manager::instance()->handle_temp_file_delete();

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
				<input type="submit" name="Submit" value="<?php _e( 'Upload', 'edd-fu' ); ?>" />
			</form>
		</p>
	</fieldset>
<?php
}

function edd_fu_attach_temp_files_to_payment( $payment_id ) {

}

$edd_fu_options = EDD_File_Upload::instance()->get_options();

if( $edd_fu_options[ 'fu_upload_location' ] == 'checkout' ) {
	add_action( 'edd_before_purchase_form', 'edd_fu_checkout_upload_field', 10 );
	add_action( 'edd_complete_purchase', array( EDD_File_Upload(), 'attach_temp_files_to_payment' ) );
}