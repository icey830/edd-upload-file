<?php
/**
 * Plugin Name:     Easy Digital Downloads - Upload File
 * Plugin URI:      https://easydigitaldownloads.com/extensions/edd-upload-file/
 * Description:     Allows your customers to attach a file to their order. Files can be attached at the checkout page or at the receipt page.
 * Version:         1.0.3
 * Author:          Daniel J Griffiths and Barry Kooij
 * Author URI:      https://easydigitaldownloads.com
 *
 * @package         EDD\UploadFile
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'EDD_Upload_File' ) ) {

    /**
     * Main EDD_Upload_File class
     *
     * @since       1.0.1
     */
    class EDD_Upload_File {

        /**
         * @var         EDD_Upload_File $instance The one true EDD_Upload_File
         * @since       1.0.1
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.1
         * @return      object self::$instance The one true EDD_Upload_File
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Upload_File();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.1
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_UPLOAD_FILE_VERSION', '1.0.3' );

            // Plugin path
            define( 'EDD_UPLOAD_FILE_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_UPLOAD_FILE_URL', plugin_dir_url( __FILE__ ) );

            // Plugin File
            define( 'EDD_UPLOAD_FILE_FILE', __FILE__ );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.1
         * @return      void
         */
        private function includes() {
            // Include scripts
            require_once EDD_UPLOAD_FILE_DIR . 'includes/actions.php';
            require_once EDD_UPLOAD_FILE_DIR . 'includes/functions.php';
            require_once EDD_UPLOAD_FILE_DIR . 'includes/file-functions.php';
            require_once EDD_UPLOAD_FILE_DIR . 'includes/templates/checkout.php';
            require_once EDD_UPLOAD_FILE_DIR . 'includes/templates/receipt.php';

            if( is_admin() ) {
            	require_once EDD_UPLOAD_FILE_DIR . 'includes/metabox.php';
            }
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.1
         * @return      void
         */
        private function hooks() {
            // Handle licensing
            if( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'Upload File', EDD_UPLOAD_FILE_VERSION, 'Daniel J Griffiths' );
            }

            // Ensure the upload directory exists
            add_action( 'admin_init', array( $this, 'create_upload_dir' ) );

            // Register settings
            add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.1
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_UPLOAD_FILE_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_upload_file_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-upload-file' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-upload-file', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-upload-file/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-upload-file/ folder
                load_textdomain( 'edd-upload-file', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-upload-file/languages/ folder
                load_textdomain( 'edd-upload-file', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-upload-file', false, $lang_dir );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {
            $new_settings = array(
            	array(
					'id'	=> 'edd_upload_file_settings',
					'name'	=> '<strong>' . __( 'File Upload Settings', 'edd-upload-file' ) . '</strong>',
					'desc'	=> '',
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_upload_file_location',
					'name'	=> __( 'File Upload Location', 'edd-upload-file' ),
					'desc'	=> __( 'Specify where to display the file upload form', 'edd-upload-file' ),
					'type'	=> 'select',
					'options'	=> array(
						'checkout'	=> __( 'Checkout Page', 'edd-upload-file' ),
						'receipt'	=> __( 'Receipt Page', 'edd-upload-file' )
					),
					'std'	=> 'checkout',
				),
				array(
					'id'	=> 'edd_upload_file_extensions',
					'name'	=> __( 'Allowed File Extensions', 'edd-upload-file' ),
					'desc'	=> __( 'Comma separate list of allowed extensions, leave blank to allow all', 'edd-upload-file' ),
					'type'	=> 'text'
				),
				array(
					'id'	=> 'edd_upload_file_limit',
					'name'	=> __( 'Maximum number of files', 'edd-upload-file' ),
					'desc'	=> __( 'Enter the allowed number of file uploads per download, or 0 for unlimited', 'edd-upload-file' ),
					'type'	=> 'number',
					'size'	=> 'small',
					'std'	=> 1,
				),
                array(
                    'id'    => 'edd_upload_file_form_desc',
                    'name'  => __( 'Upload form description', 'edd-upload-file' ),
                    'desc'  => __( 'Specify the description to display on the file upload form', 'edd-upload-file' ),
                    'type'  => 'text',
                    'std'   => __( 'Please select the file to attach to this order.', 'edd-upload-file' ),
                )
            );

            return array_merge( $settings, $new_settings );
        }


        /**
         * Ensure that the upload dir exists
         *
         * @access      public
         * @since       1.0.1
         * @return      void
         */
        public static function create_upload_dir() {
            $uploadPath = edd_upload_file_get_upload_dir();

            if( !is_dir( $uploadPath ) ) {
                // Ensure that the upload directory is protected
                wp_mkdir_p( $uploadPath );

                // Top level blank index.php
                if( !file_exists( $uploadPath . 'index.php' ) ) {
                    @file_put_contents( $uploadPath . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
                }

                // Top level .htaccess
                $rules = "Options -Indexes";
                if( file_exists( $uploadPath . '.htaccess' ) ) {
                    $contents = @file_get_contents( $uploadPath . '.htaccess' );

                    if( $contents !== $rules || !$contents ) {
                        @file_put_contents( $uploadPath . '.htaccess', $rules );
                    }
                }
            }


		}
    }
}


/**
 * The main function responsible for returning the one true EDD_Upload_File
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Upload_File The one true EDD_Upload_File
 */
function EDD_Upload_File_load() {
    if( !class_exists( 'Easy_Digital_Downloads' ) ) {
        if( !class_exists( 'S214_EDD_Activation' ) ) {
            require_once( 'includes/class.s214-edd-activation.php' );
        }

        $activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return EDD_Upload_File::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_Upload_File_load' );


/**
 * Process upgrades
 *
 * @since       1.0.1
 * @global		array $edd_options The EDD options array
 * @return      void
 */
function edd_upload_file_upgrade() {
	global $edd_options;

    if( empty( $edd_options['edd_upload_file_location'] ) && ! empty( $edd_options['fu_upload_location'] ) ) {
    	$edd_options['edd_upload_file_location'] = $edd_options['fu_upload_location'];
    	unset( $edd_options['fu_upload_location'] );
    }

    if( empty( $edd_options['edd_upload_file_extensions'] ) && ! empty( $edd_options['fu_file_extensions'] ) ) {
    	$edd_options['edd_upload_file_extensions'] = $edd_options['fu_file_extensions'];
    	unset( $edd_options['fu_file_extensions'] );
    }

    if( empty( $edd_options['edd_upload_file_limit'] ) && ! empty( $edd_options['fu_file_limit'] ) ) {
    	$edd_options['edd_upload_file_limit'] = $edd_options['fu_file_limit'];
    	unset( $edd_options['fu_file_limit'] );
    }

    update_option( 'edd_settings', $edd_options );
}
register_activation_hook( __FILE__, 'edd_upload_file_upgrade' );
