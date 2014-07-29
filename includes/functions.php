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


/**
 * Add uploaded files to the view details page
 *
 * @since		1.0.1
 * @param		int $payment_id The ID for the purchase we are viewing
 * @return		void
 */
function edd_upload_file_view_files( $payment_id ) {
	?>
	<div id="edd-purchased-files" class="postbox">
		<h3 class="hndle"><?php _e( 'Uploaded Files', 'edd-upload-file' ); ?></h3>

		<div class="inside">
			<?php
			$uploaded_files = get_post_meta( $payment_id, 'edd_upload_file_files' );

			if( $uploaded_files != '' && count( $uploaded_files ) > 0 ) {
				$i = 0;

				echo '<table class="wp-list-table widefat fixed" cellspacing="0">';
				echo '<tbody id="edd-upload-files-list">';

				foreach( $uploaded_files as $key => $file ) {
					echo '<tr class="'  . ( $i % 2 == 0 ? 'alternate' : '' ) . '">';
					echo '<td class="name column-name">' . EDD_Upload_File_Manager::instance()->get_user_friendly_filename( $file ) . '</td>';
					echo '<td class="price column-price"><a href="' . EDD_Upload_File_Manager::instance()->get_url() . '/' . $file . '" target="_blank">' . __( 'View File', 'edd-upload-file' ) . '</a></td>';
					echo '</tr>';

					$i++;
				}

				echo '</tbody>';
				echo '</table>';
			} else {
				echo '<tr><td colspan="2">' . __( 'No files uploaded', 'edd-upload-file' ) . '</td>';
			}
			?>
		</div>
	</div>
	<?php
}
add_action( 'edd_view_order_details_main_after', 'edd_upload_file_view_files' );