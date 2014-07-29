<?php
/**
 * Helper Functions
 *
 * @package     EDD\UploadFile\Functions
 * @since       1.0.1
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Process and display error messages
 *
 * @since		1.0.1
 * @param		string $message The error message to display
 * @return		void
 */
function edd_upload_file_error( $message ) {
	if( edd_get_option( 'edd_file_upload_location', 'checkout' ) ) {
		$messages = EDD()->session->get( 'edd_cart_messages' );

		if( ! $messages ) {
			$messages = array();
		}

		$messages['edd_file_upload_error'] = $message;

		EDD()->session->set( 'edd_cart_messages', $messages );
	} else {
		echo '<tr><td colspan="2" style="color: #ff0000; font-weight: bold;">{' . $message . '}</td></tr>' . "\n";
	}
}