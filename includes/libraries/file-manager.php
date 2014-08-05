<?php
/**
 * File manager
 *
 * @package     EDD\UploadFile\FileManager
 * @since       1.0.1
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'EDD_Upload_File_Manager' ) ) {

    /**
     * Main EDD_Upload_File_Manager class
     *
     * @since       1.0.1
     */
    class EDD_Upload_File_Manager {

    	/**
    	 * @var			EDD_Upload_File_Manager $instance The one true EDD_Upload_File_Manager
		 * @since		1.0.1
		 */
    	private static $instance;


    	/**
         * Get active instance
         *
         * @access      public
         * @since       1.0.1
         * @return      object self::$instance The one true EDD_Upload_File_Manager
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Upload_File_Manager();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.1
         * @return      void
         */
        private function hooks() {
            if( edd_get_option( 'edd_upload_file_location' ) == 'receipt' ) {
            	add_action( 'edd_payment_receipt_before', array( $this, 'handle_file_upload' ), 0, 1 );
            	add_action( 'edd_payment_receipt_after_table', array( $this, 'handle_file_delete' ), 0, 1 );
            } else {
            	add_action( 'template_redirect', array( $this, 'handle_temp_file_upload' ) );
            }
        }


        /**
         * Generate filenames
         *
         * @access		private
         * @since		1.0.1
         * @param		string $filename The original filename for a file
         * @return		string $filename The new filename for a file
         */
        private function generate_filename( $filename ) {
        	$filename_parts = pathinfo( $filename );

        	return $filename_parts['filename'] . '-' . uniqid() . '.' . $filename_parts['extension'];
        }


        /**
         * Remove unique ID from filenames
         *
         * @access		public
         * @since		1.0.1
         * @param		string $filename The original filename for a file
         * @return		string $filename The filename sans unique ID
         */
        public function get_original_filename( $filename ) {
        	$filename_parts = pathinfo( $filename );

        	$filename = substr( $filename_parts['filename'], 0, strrpos( $filename_parts['filename'], '-' ) );

        	return $filename . '.' . $filename_parts['extension'];
        }


        /**
         * Check if a given file has a permitted extension
         *
         * @access		private
         * @since		1.0.1
         * @param		string $filename The file to check
         * @return		bool $is_allowed True if extension is allowed, false otherwise
         */
        private function check_extension( $filename ) {
        	$extensions = edd_get_option( 'edd_upload_file_extensions', '' );
        	$is_allowed	= true;

        	if( $extensions != '' ) {
        		$extensions = explode( ',', $extensions );

        		if( ! in_array( edd_get_file_extension( $_FILES['edd-upload-file']['name'] ), $extensions ) ) {
        			$is_allowed = false;
        		}
        	}

        	return $is_allowed;
        }


        /**
         * Process file uploads
         *
         * @access		public
         * @since		1.0.1
         * @param		object $payment The purchase we are working with
         * @return		void
         */
        public function handle_file_upload( $payment ) {
            global $edd_upload_file_errors;

        	if( isset( $_FILES['edd-upload-file'] ) && $_FILES['edd-upload-file']['error'] == 0 ) {
        		// Get the file upload limit
                $limit = edd_upload_file_max_files( $payment );

        		// Make sure we aren't over our limit
        		$uploaded_files = get_post_meta( $payment->ID, 'edd_upload_file' );
        		if( $limit != 0 && count( $uploaded_files ) >= $limit ) {
        			$edd_upload_file_error[] = __( 'Maximum number of uploads reached!', 'edd-upload-file' );
        			return;
        		}

        		// Verify file extension validity
        		if( ! $this->check_extension( $_FILES['edd-upload-file']['name'] ) ) {
        			$edd_upload_file_errors[] = __( 'File extension not allowed!', 'edd-upload-file' );
        			return;
        		}

        		$filename = $this->generate_filename( $_FILES['edd-upload-file']['name'] );

        		// Upload!
        		if( move_uploaded_file( $_FILES['edd-upload-file']['tmp_name'], edd_upload_file_get_upload_dir() . '/' . $filename ) ) {
        			// Attach to post
        			add_post_meta( $payment->ID, 'edd_upload_file_files', $filename );
        		} else {
        			$edd_upload_file_errors[] = __( 'File upload failed!', 'edd-upload-file' );
        			return;
        		}
        	}
        }


        /**
         * Process file delete
         *
         * @access		public
         * @since		1.0.1
         * @param		object $payment The purchase we are working with
         * @return		void
         */
        public function handle_file_delete( $payment ) {
        	// Only display delete link on the receipt page
        	if( edd_get_option( 'edd_upload_file_location' ) == 'receipt' && isset( $_GET['delete-file'] ) ) {

        		// Remove from post meta
        		if( delete_post_meta( $payment->ID, 'edd_upload_file_files', $_GET['delete-file'] ) ) {

        			// Actually delete file
        			if( file_exists( edd_upload_file_get_upload_dir() . '/' . $_GET['delete-file'] ) ) {
        				unlink( edd_upload_file_get_upload_dir() . '/' . $_GET['delete-file'] );
        			}
        		}
        	}
        }


        /**
         * Process temp file uploads in session
         *
         * @access		public
         * @since		1.0.1
         * @return		void
         */
        public function handle_temp_file_upload() {
            global $edd_upload_file_errors;

        	if( edd_is_checkout() && isset( $_FILES['edd-upload-file'] ) && $_FILES['edd-upload-file']['error'] == 0 ) {
				// Get the file upload limit
        		$limit = edd_upload_file_max_files();

        		// Make sure we aren't over our limit
        		$uploaded_files = $this->get_session_files();
        		if( $limit != 0 && count( $uploaded_files ) >= $limit ) {
        			$edd_upload_file_errors[] = __( 'Maximum number of uploads reached!', 'edd-upload-file' );
        			return;
        		}

        		// Verify file extension validity
        		if( ! $this->check_extension( $_FILES['edd-upload-file']['name'] ) ) {
        			$edd_upload_file_errors[] = __( 'File extension not allowed!', 'edd-upload-file' );
        			return;
        		}

        		$filename = $this->generate_filename( $_FILES['edd-upload-file']['name'] );

        		// Upload!
        		if( move_uploaded_file( $_FILES['edd-upload-file']['tmp_name'], get_temp_dir() . $filename ) ) {
        			$this->add_file_to_session( $filename );
        		} else {
        			$edd_upload_file_errors[] = __( 'File upload failed!', 'edd-upload-file' );
        			return;
        		}
        	}
        }


        /**
         * Get uploaded files from session
         *
         * @access		public
         * @since		1.0.1
         * @return		array The uploaded files
         */
        public function get_session_files() {
        	return wp_parse_args( EDD()->session->get( 'edd_upload_files' ), array() );
        }


        /**
         * Add uploaded files from session
         *
         * @access		public
         * @since		1.0.1
         * @param		string $filename The file to add to the session
         * @return		void
         */
        public function add_file_to_session( $filename ) {
        	$session_files		= $this->get_session_files();
        	$session_files[]	= $filename;

        	EDD()->session->set( 'edd_upload_files', $session_files );
        }


        /**
         * Delete uploaded file from session
         *
         * @access		public
         * @since		1.0.1
         * @param		string $filename The file to delete from the session
         * @return		bool $return True if file removed successfully, false otherwise
         */
        public function delete_file_from_session( $filename ) {
        	$session_files		= $this->get_session_files();
        	$file_key			= array_search( $filename, $session_files );
        	$return				= false;

        	if( $file_key !== false ) {
        		unset( $session_files[$file_key] );
        		EDD()->session->set( 'edd_upload_files', $session_files );

        		$return = true;
        	}

        	return $return;
        }


        /**
         * Print uploaded files on receipt page
         *
         * @access		public
         * @since		1.0.1
         * @param		int $payment_id The ID for a given purchase
         * @return		void
         */
        public function print_uploaded_files( $payment_id ) {
        	$uploaded_files = get_post_meta( $payment_id, 'edd_upload_file_files' );

        	if( $uploaded_files != '' && count( $uploaded_files ) > 0 ) {
        		echo '<h3>' . __( 'Uploaded Files', 'edd-upload-files' ) . '</h3>';
        		echo '<table>';

        		$i = 1;

        		foreach( $uploaded_files as $key => $file ) {
        			echo '<tr>';
        			echo '<td><a href="' . edd_upload_file_get_upload_url() . '/' . $file . '" target="_blank">' . $this->get_original_filename( $file ) . '</a></td>';

        			if( edd_get_option( 'edd_upload_file_location' ) == 'receipt' ) {
        				echo '<td>';
        				echo '<a href="?edd_action=upload_file_delete&delete-file=' . $file . '">' . __( 'Delete File', 'edd-upload-file' ) . '</a>';
        				echo '</td>';
        			}

        			echo '</tr>';

        			$i++;
        		}

        		echo '</table>';
        	}
        }


        /**
         * Print uploaded temp files
         *
         * @access		public
         * @since		1.0.1
         * @return		void
         */
        public function print_temp_uploaded_files() {
        	$uploaded_files = $this->get_session_files();

        	if( $uploaded_files != '' && count( $uploaded_files ) > 0 ) {
        		echo '<fieldset id="edd_checkout_user_info">';
        		echo '<span><legend>' . __( 'Uploaded Files', 'edd-upload-file' ) . '</legend></span>';
        		echo '<p id="edd-upload-file-wrap">';
        		echo '<table>';

        		$i = 1;

        		foreach( $uploaded_files as $key => $file ) {
        			echo '<tr>';
        			echo '<td>' . $this->get_original_filename( $file ) . '</td>';
        			echo '<td><a href="?edd_action=upload_file_delete&delete-file=' . $file . '">' . __( 'Delete File', 'edd-upload-file' ) . '</a></td>';
        			echo '</tr>';

        			$i++;
        		}

        		echo '</table>';
        		echo '</p>';
        		echo '</fieldset>';
        	}
        }


        /**
         * Attach temp files to payment on payment completion
         *
         * @access		public
         * @since		1.0.1
         * @param		int $payment_id The ID for the purchase we're working with
         * @return		void
         */
        public function attach_temp_files_to_payment( $payment_id ) {
        	$temp_files = $this->get_session_files();

        	if( is_array( $temp_files ) && count( $temp_files ) > 0 ) {
        		foreach( $temp_files as $temp_file ) {
        			// Copy file to upload dir
        			if( copy( get_temp_dir() . $temp_file, edd_upload_file_get_upload_dir() . '/' . $temp_file ) ) {
        				// Attach uploaded file to post
        				add_post_meta( $payment_id, 'edd_upload_file_files', $temp_file );

        				// Remove from temp dir
        				if( file_exists( get_temp_dir() . $temp_file ) ) {
        					unlink( get_temp_dir() . $temp_file );
        				}

        				// Remove file from session
        				$this->delete_file_from_session( $temp_file );
        			}
        		}
        	}
        }
    }
}