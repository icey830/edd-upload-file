<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_fu_receipt_upload_field( $payment, $edd_receipt_args ) {
?>
	<h3><?php _e( 'Upload new file', 'edd-fu' ); ?></h3>
	<form action="" method="post" enctype="multipart/form-data">
		<input type="file" name="edd-fu-file" value="" />
		<input type="submit" name="Submit" value="<?php _e( 'Upload', 'edd-fu' ); ?>" />
	</form>

<?php

}

$edd_fu_options = EDD_File_Upload::get_options();

if ( $edd_fu_options['fu_upload_location'] == 'receipt' ) {
	add_action( 'edd_payment_receipt_after_table', 'edd_fu_receipt_upload_field', 12, 2 );
}