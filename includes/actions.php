<?php
/**
 * Misc actions
 *
 * @package     EDD\UploadFile\Actions
 * @since       1.0.1
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
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
	if ( ! is_dir( $uploadPath ) ) {
		wp_mkdir_p( $uploadPath );
	}

	// Top level blank index.php
	if ( ! file_exists( $uploadPath . 'index.php' ) ) {
		@file_put_contents( $uploadPath . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
	}

	// Top level .htaccess
	$rules  = "Options -Indexes \n";
	$rules .= "php_flag engine off";
	if ( file_exists( $uploadPath . '.htaccess' ) ) {
		$contents = @file_get_contents( $uploadPath . '.htaccess' );

		if ( $contents !== $rules || ! $contents ) {
			@file_put_contents( $uploadPath . '.htaccess', $rules );
		}
	} else {
		@file_put_contents( $uploadPath . '.htaccess', $rules );
	}

	// Ensure that the chunk directory exists
	if ( ! is_dir( $chunkPath ) ) {
		wp_mkdir_p( $chunkPath );
	}

	// Top level blank index.php
	if ( ! file_exists( $chunkPath . 'index.php' ) ) {
		@file_put_contents( $chunkPath . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
	}

	// Top level .htaccess
	$rules  = "Options -Indexes \n";
	$rules .= "php_flag engine off";
	if ( file_exists( $chunkPath . '.htaccess' ) ) {
		$contents = @file_get_contents( $chunkPath . '.htaccess' );

		if ( $contents !== $rules || ! $contents ) {
			@file_put_contents( $chunkPath . '.htaccess', $rules );
		}
	} else {
		@file_put_contents( $chunkPath . '.htaccess', $rules );
	}
}
add_action( 'admin_init', 'edd_upload_file_directory_exists' );


/**
 * Display uploaded files on the [edd_receipt] short code
 *
 * @since       2.0.0
 * @param       object $payment The payment object
 * @param       array $edd_receipt_args Arguments for the receipt
 * @return      void
 */
function edd_upload_file_show_files_on_receipt( $payment, $edd_receipt_args ) {
	if ( empty( $payment ) || empty( $payment->ID ) ) {
		return;
	}

	$uploaded_files = get_post_meta( $payment->ID, 'edd_upload_file_files', false );

	if ( ! $uploaded_files ) {
		$uploaded_files = get_post_meta( $payment->ID, '_edd_upload_file', false );
	}

	$allow_download = edd_get_option( 'edd_upload_file_allow_download', false );

	if ( $uploaded_files != '' && count( $uploaded_files ) > 0 ) {
		echo '<tr class="edd_upload_file_files">';
		echo '<td colspan="2"><strong>' . __( 'Uploaded Files:', 'edd-upload-file' ) . '</strong></td>';
		echo '</tr>';

		if ( is_string( $uploaded_files[0] ) ) {
			foreach ( $uploaded_files as $key => $file ) {
				echo '<tr><td colspan="2">';

				if ( $allow_download ) {
					$download_url = wp_nonce_url( add_query_arg( array(
						'edd_action' => 'upload_file_download',
						'filename'   => edd_upload_file_get_original_filename( $file ),
						'filepath'   => $file
					) ), 'edd_upload_file_download_nonce', '_wpnonce' );

					echo '&mdash;&nbsp;<a href="' . $download_url . '">' . edd_upload_file_get_original_filename( $file ) . '</a>';
				} else {
					echo '&mdash;&nbsp;' . edd_upload_file_get_original_filename( $file );
				}

				echo '</td></tr>';
			}
		} else {
			$display = array();

			foreach ( $uploaded_files as $file ) {
				$download_id   = $file['download']['id'];
				$download_name = get_the_title( $download_id );

				if ( edd_has_variable_prices( $download_id ) ) {
					$prices         = edd_get_variable_prices( $download_id );
					$download_name .= ' - ' . $prices[ $file['download']['price_id'] ]['name'];
				}

				$display[ $download_id ][ $file['download']['item_id'] ] = array(
					'name' => $download_name,
					'file' => $file['filename'],
					'uuid' => $file['uuid']
				);
			}

			foreach ( $display as $download ) {
				echo '<tr>';
				echo '<td>' . $download[1]['name'] . '</td>';
				echo '<td>';

				foreach ( $download as $file ) {
					if ( $allow_download ) {
						$download_url = wp_nonce_url( add_query_arg( array(
							'edd_action' => 'upload_file_download',
							'filename'   => $file['file'],
							'filepath'   => trailingslashit( $file['uuid'] ) . $file['file']
						) ), 'edd_upload_file_download_nonce', '_wpnonce' );

						echo '<div class="edd-upload-file-receipt-item">&nbsp;&mdash;&nbsp;<a href="' . $download_url . '">' . $file['file'] . '</a></div>';
					} else {
						echo '<div class="edd-upload-file-receipt-item">&nbsp;&mdash;&nbsp;' . $file['file'] . '</div>';
					}
				}

				echo '</td>';
				echo '</tr>';
			}
		}
	}
}
add_action( 'edd_payment_receipt_after', 'edd_upload_file_show_files_on_receipt', 10, 2 );


/**
 * Add uploaded files to the view details page
 *
 * @since       1.0.1
 * @param       int $payment_id The ID for the purchase we are viewing
 * @return      void
 */
function edd_upload_file_view_files( $payment_id ) {
	$uploaded_files = get_post_meta( $payment_id, 'edd_upload_file_files', false );

	if ( ! $uploaded_files ) {
		$uploaded_files = get_post_meta( $payment_id, '_edd_upload_file', false );
	}
	?>
	<div id="edd-upload-file-files" class="postbox">
		<h3 class="hndle"><?php _e( 'Uploaded Files', 'edd-upload-file' ); ?></h3>

		<div class="inside">
			<?php
			if ( $uploaded_files != '' && count( $uploaded_files ) > 0 ) {
				if ( is_string( $uploaded_files[0] ) ) {
					$i = 0;

					echo '<table class="wp-list-table widefat fixed" cellspacing="0">';

					echo '<thead>';
					echo '<tr>';
					echo '<th class="name column-name">' . __( 'File', 'edd-upload-file' ) . '</th>';
					echo '<th class="action column-action">' . __( 'Action', 'edd-upload-file' ) . '</th>';
					echo '</tr>';
					echo '</thead>';

					echo '<tbody id="edd-upload-files-list">';

					foreach ( $uploaded_files as $key => $file ) {
						echo '<tr class="'  . ( $i % 2 == 0 ? 'alternate' : '' ) . '">';
						echo '<td class="name column-name">' . edd_upload_file_get_original_filename( $file ) . '</td>';

						echo '<td class="upload-file column-upload-file">';
						$actions_style = ( file_exists( edd_upload_file_get_upload_dir() . '/' . $file ) ? '' : 'display: none' );
						$deleted_style = ( $actions_style == '' ? 'display: none' : '' );

						echo '<div id="edd-upload-file-' . $file['file'] . '">';
						echo '<span class="edd-upload-file-actions" style="' . $actions_style . '">';
						$download_url = wp_nonce_url( add_query_arg( array(
							'edd-action' => 'upload_file_download',
							'filename'   => edd_upload_file_get_original_filename( $file ),
							'filepath'   => $file
						) ), 'edd_upload_file_download_nonce', '_wpnonce' );
						echo '<a href="' . $download_url . '" title="' . __( 'Download', 'edd-upload-file' ) . '"><span class="dashicons dashicons-download"></span></a>';
						echo '<a href="#" class="edd-upload-file-delete-file" data-file-name="' . edd_upload_file_get_original_filename( $file ) . '" data-file-path="' . $file . '" data-payment-id="' . $payment_id . '" title="' . __( 'Delete', 'edd-upload-file' ) . '"><span class="dashicons dashicons-trash"></span></a>';
						echo '</span>';
						echo '<span class="edd-upload-file-deleted">' . __( 'File Deleted', 'edd-upload-file' ) . '</span>';
						echo '</div>';
						echo '</td>';
						echo '</tr>';

						$i++;
					}

					echo '</tbody>';
					echo '</table>';
				} else {
					echo '<table class="wp-list-table widefat fixed" cellspacing="0">';

					echo '<thead>';
					echo '<tr>';
					echo '<th class="name column-name">' . __( 'Product', 'edd-upload-file' ) . '</th>';
					echo '<th class="upload-file column-upload-file">' . __( 'Files', 'edd-upload-file' ) . '</th>';
					echo '</tr>';
					echo '</thead>';

					echo '<tbody id="edd-upload-files-list">';

					$display = array();
					$i       = 0;

					foreach ( $uploaded_files as $file ) {
						$download_id   = $file['download']['id'];
						$download_name = get_the_title( $download_id );

						if ( edd_has_variable_prices( $download_id ) ) {
							$prices         = edd_get_variable_prices( $download_id );
							$download_name .= ' - ' . $prices[ $file['download']['price_id'] ]['name'];
						}

						$display[ $download_id ][ $file['download']['item_id'] ] = array(
							'name' => $download_name,
							'file' => $file['filename'],
							'uuid' => $file['uuid']
						);
					}

					foreach ( $display as $download ) {
						echo '<tr class="'  . ( $i % 2 == 0 ? 'alternate' : '' ) . '">';
						echo '<td class="name column-name">' . $download[1]['name'] . '</td>';
						echo '<td class="upload-file column-upload-file">';

						foreach ( $download as $file ) {
							$filepath      = trailingslashit( $file['uuid'] ) . $file['file'];
							$actions_style = ( file_exists( edd_upload_file_get_upload_dir() . '/' . $filepath ) ? '' : 'display: none' );
							$deleted_style = ( $actions_style == '' ? 'display: none' : '' );

							echo '<div class="edd-upload-file-receipt-item" id="edd-upload-file-' . $file['file'] . '">&nbsp;&mdash;&nbsp;' . $file['file'] . '&nbsp;';
							$download_url = wp_nonce_url( add_query_arg( array(
								'edd-action' => 'upload_file_download',
								'filename'   => $file['file'],
								'filepath'   => $filepath
							) ), 'edd_upload_file_download_nonce', '_wpnonce' );

							echo '<span class="edd-upload-file-actions" style="' . $actions_style . '">';
							echo '<a href="' . $download_url . '" title="' . __( 'Download', 'edd-upload-file' ) . '"><span class="dashicons dashicons-download"></span></a>';
							echo '<a href="#" class="edd-upload-file-delete-file" data-file-name="' . $file['file'] . '" data-file-path="' . $filepath . '" data-payment-id="' . $payment_id . '" title="' . __( 'Delete', 'edd-upload-file' ) . '"><span class="dashicons dashicons-trash"></span></a>';
							echo '</span>';
							echo '<span class="edd-upload-file-deleted" style="' . $deleted_style . '">' . __( 'File Deleted', 'edd-upload-file' ) . '</span>';
							echo '</div>';
						}

						echo '</td>';
						echo '</tr>';

						$i++;
					}

					echo '</tbody>';
					echo '</table>';
				}
			} else {
				echo '<div class="inside"><p>' . __( 'No files uploaded', 'edd-upload-file' ) . '</p></div>';
			}
			?>
		</div>
	</div>
	<?php
}
add_action( 'edd_view_order_details_main_after', 'edd_upload_file_view_files' );


/**
 * Delete a file with ajax
 *
 * @since       2.1.0
 * @return      void
*/
function edd_ajax_edd_upload_file_delete_file() {
	if ( ! current_user_can( 'edit_shop_payments', $_POST['payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'edd-upload-file' ), __( 'Error', 'edd-upload-file' ), array( 'response' => 403 ) );
	}

	if ( edd_upload_file_delete_file( $_POST['file_path'] ) ) {
		die( '1' );
	} else {
		die( '-1' );
	}

}
add_action( 'wp_ajax_edd_upload_file_delete_file', 'edd_ajax_edd_upload_file_delete_file' );
