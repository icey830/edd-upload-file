<?php
/**
 * Deprecated functions
 *
 * @package     EDD\UploadFile\Deprecated
 * @since       2.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get the max number of uploads allowed
 *
 * @since       1.0.1
 * @deprecated  2.0.0
 * @param       object $payment The purchase we are working with
 * @return      int $limit The max number of files
 */
function edd_upload_file_max_files( $payment = false ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	if( edd_is_checkout() ) {
		$cart_items	= edd_get_cart_contents();
	} else {
		$cart_items	= edd_get_payment_meta_cart_details( $payment->ID, true );
	}

	$global_limit = edd_get_option( 'edd_upload_file_limit', 0 );
	$global_limit = ( $global_limit == 0 ? 999 : $global_limit );
	$limit        = 0;

	// Check files for upload permission
	if( count( $cart_items ) > 0 ) {
		foreach( $cart_items as $cart_item ) {
			if( get_post_meta( $cart_item['id'], '_edd_upload_file_enabled', true ) ? true : false ) {
				$limit = $limit + $global_limit;
			}
		}
	}

	return $limit;
}


/**
 * Delete an uploaded file
 *
 * @since       1.0.1
 * @deprecated  2.0.0
 * @return      string $upload_dir The file upload directory
 */
function edd_upload_file_delete() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	if( ! isset( $_GET['edd-upload-file-nonce'] ) || ! wp_verify_nonce( $_GET['edd-upload-file-nonce'], 'edd-upload-file-nonce' ) ) {
		return;
	}

	if( isset( $_GET['delete-file']) ) {
		edd_upload_file_delete_from_session( $_GET['delete-file'] );

		// Actually delete file
		if( file_exists( get_temp_dir() . $_GET['delete-file'] ) ) {
			unlink( get_temp_dir() . $_GET['delete-file'] );
		}

		if( ! edd_is_checkout() ) {
			wp_safe_redirect( remove_query_arg( array( 'edd_action' ) ) );
		} else {
			wp_safe_redirect( remove_query_arg( array( 'edd_action', 'delete-file' ) ) );
		}
		edd_die();
	}
}
//add_action( 'edd_upload_file_delete', 'edd_upload_file_delete' );


/**
 * Process and display error messages
 *
 * @since       1.0.1
 * @deprecated  2.0.0
 * @param       string $message The error message to display
 * @return      void
 */
function edd_upload_file_error( $message ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	global $edd_upload_file_errors;

	if( is_array( $edd_upload_file_errors ) ) {
		foreach( $edd_upload_file_errors as $error ) {
			if( edd_get_option( 'edd_upload_file_location', 'checkout' ) ) {
				echo '<div class="edd_errors"><p class="edd_error" id="edd_msg_edd_upload_file_error">' . $error . '</p></div>';
			} else {
				echo '<tr><td colspan="2" style="color: #ff0000; font-weight: bold;">' . $error . '</td></tr>' . "\n";
			}
		}
	}
}
//add_action( 'edd_upload_file_before', 'edd_upload_file_error', 11 );


/**
 * Attach temp files on payment completion
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @param       int $payment_id The ID for this purchase
 * @return      void
 */
function edd_upload_file_attach_files( $payment_id ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	$temp_files = edd_upload_file_get_session_files();

	if( is_array( $temp_files ) && count( $temp_files ) > 0 ) {
		foreach( $temp_files as $temp_file ) {
			// Copy to upload dir
			if( copy( get_temp_dir() . $temp_file, edd_upload_file_get_upload_dir() . '/' . $temp_file ) ) {
				add_post_meta( $payment_id, 'edd_upload_file_files', $temp_file );

				// Remove from temp dir
				if( file_exists( get_temp_dir() . $temp_file ) ) {
					unlink( get_temp_dir() . $temp_file );
				}

				edd_upload_file_delete_from_session( $temp_file );
			}
		}
	}
}
//add_action( 'edd_complete_purchase', 'edd_upload_file_attach_files', 10, 1 );


/**
 * Print uploaded files (checkout page)
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @return      void
 */
function edd_upload_file_print_checkout_files() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	$uploaded_files = edd_upload_file_get_session_files();

	if( $uploaded_files != '' && count( $uploaded_files ) > 0 ) {
		echo '<fieldset id="edd_checkout_user_info">';
		echo '<span><legend>' . __( 'Upload Files', 'edd-upload-file' ) . '</legend></span>';
		echo '<p id="edd-upload-file-wrap">';
		echo '<table>';

		$i = 1;

		foreach( $uploaded_files as $key => $file ) {
			echo '<tr>';
			echo '<td>' . edd_upload_file_get_original_filename( $file ) . '</td>';
			echo '<td><a href="' . wp_nonce_url( add_query_arg( array( 'edd_action' => 'upload_file_delete', 'delete-file' => $file ) ), 'edd-upload-file-nonce', 'edd-upload-file-nonce' ) . '">' . __( 'Delete File', 'edd-upload-file' ) . '</a></td>';
			echo '</tr>';

			$i++;
		}

		echo '</table>';
		echo '</p>';
		echo '</fieldset>';
	}
}


/**
 * Print uploaded files (receipt page)
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @param       int $payment_id The ID for a given purchase
 * @return      void
 */
function edd_upload_file_print_receipt_files( $payment_id ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	$uploaded_files = get_post_meta( $payment_id, 'edd_upload_file_files' );

	if( $uploaded_files != '' && count( $uploaded_files ) > 0 ) {
		echo '<h3>' . __( 'Uploaded Files', 'edd-upload-file' ) . '</h3>';
		echo '<table>';

		$i = 1;

		foreach( $uploaded_files as $key => $file ) {
			echo '<tr>';
			echo '<td><a href="' . esc_url( edd_upload_file_get_upload_url() . '/' . $file ) . '" target="_blank">' . edd_upload_file_get_original_filename( $file ) . '</a></td>';

			if( edd_get_option( 'edd_upload_file_location', 'checkout' ) == 'receipt' ) {
				echo '<td>';
				echo '<a href="' . wp_nonce_url( add_query_arg( array( 'edd_action' => 'upload_file_delete', 'delete-file' => $file ) ), 'edd-upload-file-nonce', 'edd-upload-file-nonce' ) . '">' . __( 'Delete File', 'edd-upload-file' ) . '</a>';
				echo '</td>';
			}

			echo '</tr>';

			$i++;
		}

		echo '</table>';
	}
}


/**
 * Process file deletion
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @param       object $payment The purchase we are working with
 * @return      void
 */
function edd_upload_file_process_deletion( $payment ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	// Only display on the receipt page
	if( edd_get_option( 'edd_upload_file_location', 'checkout' ) == 'receipt' && isset( $_GET['delete-file'] ) ) {
		// Remove from post meta
		if( delete_post_meta( $payment->ID, 'edd_upload_file_files', $_GET['delete-file'] ) ) {
			if( file_exists( edd_upload_file_get_upload_dir() . '/' . $_GET['delete-file'] ) ) {
				unlink( edd_upload_file_get_upload_dir() . '/' . $_GET['delete-file'] );
			}
		}
	}
}
//add_action( 'edd_payment_receipt_after_table', 'edd_upload_file_process_deletion', 0, 1 );



/**
 * Process file uploads (checkout page)
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @return      void
 */
function edd_upload_file_process_checkout_upload() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	global $edd_upload_file_errors;

	if( edd_is_checkout() && isset( $_FILES['edd-upload-file'] ) && $_FILES['edd-upload-file']['error'] == 0 ) {
		// Get the upload limit
		$limit = edd_upload_file_max_files();

		// Max sure we aren't over the limit
		$uploaded_files = edd_upload_file_get_session_files();
		if( $limit != 0 && count( $uploaded_files ) >= $limit ) {
			$edd_upload_file_errors[] = __( 'Maximum number of uploads reached!', 'edd-upload-file' );
			return;
		}

		// Verify extension
		if( ! edd_upload_file_check_extension( $_FILES['edd-upload-file']['name'] ) ) {
			$edd_upload_file_errors[] = __( 'File extension not allowed!', 'edd-upload-file' );
			return;
		}

		$filename = edd_upload_file_generate_filename( $_FILES['edd-upload-file']['name'] );

		// Upload!
		if( move_uploaded_file( $_FILES['edd-upload-file']['tmp_name'], get_temp_dir() . $filename ) ) {
			edd_upload_file_add_to_session( $filename );
		} else {
			$edd_upload_file_errors[] = __( 'File upload failed!', 'edd-upload-file' );
			return;
		}
	}
}
//add_action( 'template_redirect', 'edd_upload_file_process_checkout_upload' );


/**
 * Get uploaded files from session
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @return      array The uploaded files
 */
function edd_upload_file_get_session_files() {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	return wp_parse_args( EDD()->session->get( 'edd_upload_files' ), array() );
}


/**
 * Add uploaded files from session
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @param       string $filename The file to add
 * @return      void
 */
function edd_upload_file_add_to_session( $filename ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	$session_files   = edd_upload_file_get_session_files();
	$session_files[] = $filename;

	EDD()->session->set( 'edd_upload_files', $session_files );
}


/**
 * Delete uploaded files from session
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @param       string $filename The file to delete
 * @return      bool $return True if successful, false otherwise
 */
function edd_upload_file_delete_from_session( $filename ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	$session_files = edd_upload_file_get_session_files();
	$file_key      = array_search( $filename, $session_files );
	$return        = false;

	if( $file_key !== false ) {
		unset( $session_files[$file_key] );
		EDD()->session->set( 'edd_upload_files', $session_files );

		$return = true;
	}

	return $return;
}


/**
 * Generate filenames
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @param       string $filename The original filename
 * @return      string $filename The new filename
 */
function edd_upload_file_generate_filename( $filename ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	$filename_parts = pathinfo( $filename );

	// Generate a hash... safer than uniqid
	$hash = wp_hash( $filename_parts['filename'] . current_time( 'timestamp' ) );

	return $filename_parts['filename'] . '-' . $hash . '.' . $filename_parts['extension'];
}


/**
 * Check if a file has a permitted extension
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @param       string $filename The file to check
 * @return      bool $is_allowed True if allowed, false otherwise
 */
function edd_upload_file_check_extension( $filename ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'edd_upload_file_get_allowed_extensions()', $backtrace );

	$extensions = edd_get_option( 'edd_upload_file_extensions', '' );
	$is_allowed = true;

	if( $extensions != '' ) {
		$extensions = explode( ',', $extensions );

		if( ! in_array( edd_get_file_extension( $_FILES['edd-upload-file']['name'] ), $extensions ) ) {
			$is_allowed = false;
		}
	}

	return $is_allowed;
}


/**
 * Add upload field to receipt
 *
 * @since       1.0.1
 * @deprecated  2.0.0
 * @param       object $payment The purchase we are working with
 * @param       array $edd_receipt_args Arguemnts for this receipt
 * @return      void
 */
function edd_upload_file_receipt_upload_field( $payment, $edd_receipt_args ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'edd_upload_file_display_receipt_upload_field', $backtrace );

	$cart_items		= edd_get_payment_meta_cart_details( $payment->ID, true );
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

	// Get the file upload limit
	$limit = 1; //(int) edd_upload_file_max_files( $payment );

	// Make sure we aren't over our limit
	$uploaded_files = get_post_meta( $payment->ID, 'edd_upload_file_files', true );

	if( $limit == 0 || empty( $uploaded_files ) || count( $uploaded_files ) < $limit ) {
		?>
		<h3><?php _e( 'Upload new file', 'edd-upload-file' ); ?></h3>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="edd-upload-file" value="" />
			<input type="submit" name="Submit" value="<?php _e( 'Upload', 'edd-upload-file' ); ?>" class="<?php echo $button_style . ' ' . $color; ?>" />
		</form>
		<?php
	}
}


/**
 * Print uploaded files on receipt
 *
 * @since       1.0.1
 * @deprecated  2.0.0
 * @param       object $payment The purchase we are working with
 * @param       array $edd_receipt_args Arguemnts for this receipt
 * @return      void
 */
function edd_upload_file_print_uploaded_files( $payment, $edd_receipt_args ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'edd_upload_file_show_files_on_receipt()', $backtrace );

	edd_upload_file_print_receipt_files( $payment->ID );
}


/**
 * Process file uploads (receipt page)
 *
 * @since       1.0.3
 * @deprecated  2.0.0
 * @param       object $payment The purchase we are working with
 * @return      void
 */
function edd_upload_file_process_receipt_upload( $payment ) {
	$backtrace = debug_backtrace();

	_edd_deprecated_function( __FUNCTION__, '2.0.0', 'no alternatives', $backtrace );

	global $edd_upload_file_errors;

	if( isset( $_FILES['edd-upload-file'] ) && $_FILES['edd-upload-file']['error'] == 0 ) {
		// Get the file upload limit
		$limit = edd_upload_file_max_files( $payment );

		// Make sure we aren't over our limit
		$uploaded_files = get_post_meta( $payment->ID, 'edd_upload_file_files' );
		if( $limit != 0 && count( $uploaded_files ) >= $limit ) {
			$edd_upload_file_error[] = __( 'Maximum number of uploads reached!', 'edd-upload-file' );
			return;
		}

		// Extension validity
		if( ! edd_upload_file_check_extension( $_FILES['edd-upload-file']['name'] ) ) {
			$edd_upload_file_errors[] = __( 'File extension not allowed!', 'edd-upload-file' );
			return;
		}

		$filename = edd_upload_file_generate_filename( $_FILES['edd-upload-file']['name'] );

		// Upload!
		if( move_uploaded_file( $_FILES['edd-upload-file']['tmp_name'], edd_upload_file_get_upload_dir() . '/' . $filename ) ) {
			add_post_meta( $payment->ID, 'edd_upload_file_files', $filename );
		} else {
			$edd_upload_file_errors[] = __( 'File upload failed!', 'edd-upload-file' );
			return;
		}
	}
}
//add_action( 'edd_payment_receipt_before', 'edd_upload_file_process_receipt_upload', 0, 1 );
