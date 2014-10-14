<?php
/**
 * Checkout template
 *
 * @package     EDD\UploadFile\Templates\Checkout
 * @since       1.0.1
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Add upload field to checkout
 *
 * @since		1.0.1
 * @return		void
 */
function edd_upload_file_checkout_upload_field() {
	$cart_items		= edd_get_cart_contents();
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

	do_action( 'edd_upload_file_before' );

	// Print uploaded files
	EDD_Upload_File_Manager::instance()->print_temp_uploaded_files();

	// Get the file upload limit
    //$limit = (int) edd_get_option( 'edd_upload_file_limit', 1 );
    $limit = (int) edd_upload_file_max_files();

    // Make sure we aren't over our limit
    $uploaded_files = EDD_Upload_File_Manager::instance()->get_session_files();
    if( $limit == 0 || empty( $uploaded_files ) || count( $uploaded_files ) < $limit ) {
		?>
		<fieldset id="edd_checkout_user_info">
			<span><legend><?php _e( 'File Upload', 'edd-upload-file' ); ?></legend></span>

			<p id="edd-upload-file-wrap">
				<label class="edd-label" for="edd-upload-file">
					<?php _e( 'File', 'edd-upload-file' ); ?> <span class="edd-required-indicator">*</span>
				</label>
				<span class="edd-description"><?php echo edd_get_option( 'edd_upload_file_form_desc', __( 'Please select the file to attach to this order.', 'edd-upload-file' ) ); ?></span>

				<form action="" method="post" enctype="multipart/form-data">
					<input type="file" name="edd-upload-file" value="" />
					<input type="submit" name="Submit" value="<?php _e( 'Upload', 'edd-upload-file' ); ?>" class="<?php echo $button_style . ' ' . $color; ?>" />
				</form>
			</p>
		</fieldset>
		<?php
	}

	do_action( 'edd_upload_file_after' );
}


// Hook to the checkout page
if( edd_get_option( 'edd_upload_file_location' ) == 'checkout' ) {
	add_action( 'edd_before_purchase_form', 'edd_upload_file_checkout_upload_field', 10 );
	add_action( 'edd_complete_purchase', array( EDD_Upload_File_Manager::instance(), 'attach_temp_files_to_payment' ), 10, 1 );
}