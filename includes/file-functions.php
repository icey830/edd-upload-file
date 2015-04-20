<?php
/**
 * File functions
 *
 * @package     EDD\UploadFile\FileFunctions
 * @since       1.0.3
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Generate filenames
 *
 * @since       1.0.3
 * @param       string $filename The original filename
 * @return      string $filename The new filename
 */
function edd_upload_file_generate_filename( $filename ) {
    $filename_parts = pathinfo( $filename );

    // Generate a hash... safer than uniqid
    $hash = wp_hash( $filename_parts['filename'] . current_time( 'timestamp' ) );

    return $filename_parts['filename'] . '-' . $hash . '.' . $filename_parts['extension'];
}


/**
 * Strip unique ID from filename
 *
 * @since       1.0.3
 * @param       string $filename The original filename
 * @return      string $filename The filename sans ID
 */
function edd_upload_file_get_original_filename( $filename ) {
    $filename_parts = pathinfo( $filename );

    $filename = substr( $filename_parts['filename'], 0, strrpos( $filename_parts['filename'], '-' ) );

    return $filename . '.' . $filename_parts['extension'];
}


/**
 * Check if a file has a permitted extension
 *
 * @since       1.0.3
 * @param       string $filename The file to check
 * @return      bool $is_allowed True if allowed, false otherwise
 */
function edd_upload_file_check_extension( $filename ) {
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
 * Process file uploads (receipt page)
 *
 * @since       1.0.3
 * @param       object $payment The purchase we are working with
 * @return      void
 */
function edd_upload_file_process_receipt_upload( $payment ) {
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
add_action( 'edd_payment_receipt_before', 'edd_upload_file_process_receipt_upload', 0, 1 );


/**
 * Process file deletion
 *
 * @since       1.0.3
 * @param       object $payment The purchase we are working with
 * @return      void
 */
function edd_upload_file_process_deletion( $payment ) {
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
add_action( 'edd_payment_receipt_after_table', 'edd_upload_file_process_deletion', 0, 1 );



/**
 * Process file uploads (checkout page)
 *
 * @since       1.0.3
 * @return      void
 */
function edd_upload_file_process_checkout_upload() {
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
add_action( 'template_redirect', 'edd_upload_file_process_checkout_upload' );


/**
 * Get uploaded files from session
 *
 * @since       1.0.3
 * @return      array The uploaded files
 */
function edd_upload_file_get_session_files() {
    return wp_parse_args( EDD()->session->get( 'edd_upload_files' ), array() );
}


/**
 * Add uploaded files from session
 *
 * @since       1.0.3
 * @param       string $filename The file to add
 * @return      void
 */
function edd_upload_file_add_to_session( $filename ) {
    $session_files      = edd_upload_file_get_session_files();
    $session_files[]    = $filename;

    EDD()->session->set( 'edd_upload_files', $session_files );
}


/**
 * Delete uploaded files from session
 *
 * @since       1.0.3
 * @param       string $filename The file to delete
 * @return      bool $return True if successful, false otherwise
 */
function edd_upload_file_delete_from_session( $filename ) {
    $session_files  = edd_upload_file_get_session_files();
    $file_key       = array_search( $filename, $session_files );
    $return         = false;

    if( $file_key !== false ) {
        unset( $session_files[$file_key] );
        EDD()->session->set( 'edd_upload_files', $session_files );

        $return = true;
    }

    return $return;
}


/**
 * Print uploaded files (receipt page)
 *
 * @since       1.0.3
 * @param       int $payment_id The ID for a given purchase
 * @return      void
 */
function edd_upload_file_print_receipt_files( $payment_id ) {
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
 * Print uploaded files (checkout page)
 *
 * @since       1.0.3
 * @return      void
 */
function edd_upload_file_print_checkout_files() {
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
 * Attach temp files on payment completion
 *
 * @since       1.0.3
 * @param       int $payment_id The ID for this purchase
 * @return      void
 */
function edd_upload_file_attach_files( $payment_id ) {
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
