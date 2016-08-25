<?php
/**
 * Process EDD actions
 *
 * @package     EDD\UploadFile\Actions
 * @since       1.0.1
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Ensure the upload directory exists
 *
 * @since       1.0.6
 * @return      void
 */
function edd_upload_file_directory_exists() {
	$uploadPath = trailingslashit( edd_upload_file_get_upload_dir() );
	$chunkPath  = trailingslashit( edd_upload_file_get_upload_dir() . '-chunk' );

	// Ensure that the upload directory exists
	if( ! is_dir( $uploadPath ) ) {
		wp_mkdir_p( $uploadPath );
	}

	// Top level blank index.php
	if( ! file_exists( $uploadPath . 'index.php' ) ) {
		@file_put_contents( $uploadPath . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
	}

	// Top level .htaccess
	$rules = "Options -Indexes";
	if( file_exists( $uploadPath . '.htaccess' ) ) {
		$contents = @file_get_contents( $uploadPath . '.htaccess' );

		if( $contents !== $rules || ! $contents ) {
			@file_put_contents( $uploadPath . '.htaccess', $rules );
		}
	} else {
		@file_put_contents( $uploadPath . '.htaccess', $rules );
	}

	// Ensure that the chunk directory exists
	if( ! is_dir( $chunkPath ) ) {
		wp_mkdir_p( $chunkPath );
	}

	// Top level blank index.php
	if( ! file_exists( $chunkPath . 'index.php' ) ) {
		@file_put_contents( $chunkPath . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
	}

	// Top level .htaccess
	$rules = "Options -Indexes";
	if( file_exists( $chunkPath . '.htaccess' ) ) {
		$contents = @file_get_contents( $chunkPath . '.htaccess' );

		if( $contents !== $rules || ! $contents ) {
			@file_put_contents( $chunkPath . '.htaccess', $rules );
		}
	} else {
		@file_put_contents( $chunkPath . '.htaccess', $rules );
	}
}
add_action( 'admin_init', 'edd_upload_file_directory_exists' );


/**
 * Link files on payment completion
 *
 * @since       1.1.3
 * @param       int $payment_id The ID for this purchase
 * @return      void
 */
function edd_upload_file_link_files( $payment_id ) {
	if( isset( $_POST['edd-upload-file'] ) ) {
		foreach( $_POST['edd-upload-file'] as $file ) {
			$file_data = substr( $file, 1, -1 );
			$file_data = explode( '}{', $file_data );

			$download_data = explode( '-', $file_data[0] );
			$meta_data     = array(
				'uuid'     => $file_data[1],
				'filename' => $file_data[2]
			);

			if( count( $download_data ) == 3 ) {
				$meta_data['download'] = array(
					'id'       => $download_data[0],
					'price_id' => $download_data[1],
					'item_id'  => $download_data[2]
				);
			} else {
				$meta_data['download'] = array(
					'id'      => $download_data[0],
					'item_id' => $download_data[1]
				);
			}

			add_post_meta( $payment_id, '_edd_upload_file', $meta_data );
		}
	}
}
add_action( 'edd_complete_purchase', 'edd_upload_file_link_files', 10, 1 );


/**
 * Link files on receipt upload
 *
 * @since       1.1.0
 * @return      void
 */
function edd_upload_file_ajax_update_files() {
	if( ! empty( $_POST['download_data'] ) || ! empty( $_POST['uuid'] ) || ! empty( $_POST['filename'] ) || ! empty( $_POST['payment_id'] ) ) {

		$download_data = explode( '-', $_POST['download_data'] );
		$meta_data     = array(
			'uuid'     => $_POST['uuid'],
			'filename' => $_POST['filename']
		);

		if( count( $download_data ) == 3 ) {
			$meta_data['download'] = array(
				'id'       => $download_data[0],
				'price_id' => $download_data[1],
				'item_id'  => $download_data[2]
			);
		} else {
			$meta_data['download'] = array(
				'id'      => $download_data[0],
				'item_id' => $download_data[1]
			);
		}

		$return = add_post_meta( $_POST['payment_id'], '_edd_upload_file', $meta_data );

		echo json_encode($return);
	}
	edd_die();
}
add_action( 'wp_ajax_edd_upload_file_process', 'edd_upload_file_ajax_update_files' );
add_action( 'wp_ajax_nopriv_edd_upload_file_process', 'edd_upload_file_ajax_update_files' );


/**
 * Unink files on receipt delete
 *
 * @since       1.1.0
 * @return      void
 */
function edd_upload_file_ajax_delete_files() {
	if( ! empty( $_POST['download_data'] ) || ! empty( $_POST['payment_id'] ) ) {

		$file_data = substr( $_POST['download_data'], 1, -1 );
		$file_data = explode( '}{', $file_data );

		$download_data = explode( '-', $file_data[0] );
		$meta_data     = array(
			'uuid'     => $file_data[1],
			'filename' => $file_data[2]
		);

		if( count( $download_data ) == 3 ) {
			$meta_data['download'] = array(
				'id'       => $download_data[0],
				'price_id' => $download_data[1],
				'item_id'  => $download_data[2]
			);
		} else {
			$meta_data['download'] = array(
				'id'      => $download_data[0],
				'item_id' => $download_data[1]
			);
		}

		$return = delete_post_meta( $_POST['payment_id'], '_edd_upload_file', $meta_data );

		echo json_encode($return);
	}
	edd_die();
}
add_action( 'wp_ajax_edd_upload_file_delete', 'edd_upload_file_ajax_delete_files' );
add_action( 'wp_ajax_nopriv_edd_upload_file_delete', 'edd_upload_file_ajax_delete_files' );


/**
 * Add uploaded files to the view details page
 *
 * @since       1.0.1
 * @param       int $payment_id The ID for the purchase we are viewing
 * @return      void
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
				$upload_dir = wp_upload_dir();
				$upload_dir = $upload_dir['basedir'] . '/edd-upload-files';

				echo '<table class="wp-list-table widefat fixed" cellspacing="0">';
				echo '<tbody id="edd-upload-files-list">';

				foreach( $uploaded_files as $key => $file ) {
					echo '<tr class="'  . ( $i % 2 == 0 ? 'alternate' : '' ) . '">';
					echo '<td class="name column-name">' . edd_upload_file_get_original_filename( $file ) . '</td>';
					echo '<td class="price column-price"><a href="' . edd_upload_file_get_upload_url() . '/' . $file . '" target="_blank">' . __( 'View File', 'edd-upload-file' ) . '</a></td>';
					echo '</tr>';

					$i++;
				}

				echo '</tbody>';
				echo '</table>';
			} else {
				echo __( 'No files uploaded', 'edd-upload-file' );
			}
			?>
		</div>
	</div>
	<?php
}
add_action( 'edd_view_order_details_main_after', 'edd_upload_file_view_files' );


/**
 * Add upload field to checkout
 *
 * @since       1.0.1
 * @return      void
 */
function edd_upload_file_checkout_upload_field() {
	// Bail if the form is displayed on receipt
	if( edd_get_option( 'edd_upload_file_location', 'checkout' ) != 'checkout' ) {
		return;
	}

	$cart_items = edd_get_cart_contents();

	edd_upload_file_display_form( $cart_items, 'checkout' );
}
add_action( 'edd_before_purchase_form', 'edd_upload_file_checkout_upload_field', 10 );


/**
 * Add upload field to receipt
 *
 * @since       1.0.1
 * @param		object $payment The purchase we are working with
 * @param		array $edd_receipt_args Arguemnts for this receipt
 * @return      void
 */
function edd_upload_file_receipt_upload_field( $payment, $edd_receipt_args ) {
	// Bail if the form is displayed on checkout
	if( edd_get_option( 'edd_upload_file_location', 'checkout' ) == 'checkout' ) {
		return;
	}

	if( isset( $_GET['payment_key'] ) || get_post_meta( $payment->ID, '_edd_upload_file', false ) ) {
		return;
	}

	$cart_items = edd_get_payment_meta_cart_details( $payment->ID, true );

	edd_upload_file_display_form( $cart_items, 'receipt', $payment->ID );
}
add_action( 'edd_payment_receipt_after_table', 'edd_upload_file_receipt_upload_field', 12, 2 );


/**
 * Display uploaded files on the [edd_receipt] short code
 *
 * @since       1.1.0
 * @param       object $payment The payment object
 * @param       array $edd_receipt_args Arguments for the receipt
 * @return      void
 */
function edd_upload_file_show_files_on_receipt( $payment, $edd_receipt_args ) {
	if( empty( $payment ) || empty( $payment->ID ) ) {
		return;
	}

	$files = get_post_meta( $payment->ID, '_edd_upload_file', false );

	if( $files ) {
		echo '<tr class="edd_upload_file_files">';
		echo '<td colspan="2"><strong>' . __( 'Uploaded Files:', 'edd-upload-file' ) . '</strong></td>';
		echo '</tr>';

		$display = array();

		foreach( $files as $file ) {
			$download_id   = $file['download']['id'];
			$download_name = get_the_title( $download_id );

			if( edd_has_variable_prices( $download_id ) ) {
				$prices         = edd_get_variable_prices( $download_id );
				$download_name .= ' - ' . $prices[$file['download']['price_id']];
			}

			$display[$download_id][$file['download']['item_id']] = array(
				'name'    => $download_name,
				'file'    => $file['filename']
			);
		}

		foreach( $display as $download ) {
			echo '<tr>';
			echo '<td>' . $download[1]['name'] . '</td>';
			echo '<td>';

			foreach( $download as $file ) {
				echo '<div class="edd-upload-file-receipt-item">&nbsp;&mdash;&nbsp;' . $file['file'] . '</div>';
			}

			echo '</td>';
			echo '</tr>';
		}
	}
}
add_action( 'edd_payment_receipt_after', 'edd_upload_file_show_files_on_receipt', 10, 2 );
