<?php
/**
 * Plugin Name:     Easy Digital Downloads - Upload File
 * Plugin URI:      https://easydigitaldownloads.com/downloads/edd-upload-file/
 * Description:     Allows your customers to attach a file to their order. Files can be attached at the checkout page or at the receipt page.
 * Version:         2.1.2
 * Author:          Easy Digital Downloads, LLC
 * Author URI:      https://easydigitaldownloads.com
 *
 * @package         EDD\UploadFile
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'EDD_Upload_File' ) ) {

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
		 * @var         object $uploader The upload handler object
		 * @since       2.0.0
		 */
		public $uploader;


		/**
		 * @var         bool $debugging Whether or not debugging is available
		 * @since       2.0.0
		 */
		public $debugging = false;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.1
		 * @return      object self::$instance The one true EDD_Upload_File
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new EDD_Upload_File();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
				self::$instance->uploader = new EDD_Upload_File_Uploader();

				if ( class_exists( 'S214_Debug' ) ) {
					if ( edd_get_option( 'edd_upload_file_enable_debug', false ) ) {
						self::$instance->debugging = true;
					}
				}
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
			define( 'EDD_UPLOAD_FILE_VER', '2.1.2' );

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
			require_once EDD_UPLOAD_FILE_DIR . 'includes/filters.php';
			require_once EDD_UPLOAD_FILE_DIR . 'includes/functions.php';
			require_once EDD_UPLOAD_FILE_DIR . 'includes/scripts.php';
			require_once EDD_UPLOAD_FILE_DIR . 'includes/upload/actions.php';
			require_once EDD_UPLOAD_FILE_DIR . 'includes/upload/functions.php';
			require_once EDD_UPLOAD_FILE_DIR . 'includes/upload/class.edd-upload-file-uploader.php';
			require_once EDD_UPLOAD_FILE_DIR . 'includes/download/actions.php';
			require_once EDD_UPLOAD_FILE_DIR . 'includes/download/functions.php';

			require_once EDD_UPLOAD_FILE_DIR . 'includes/deprecated-functions.php';

			if ( is_admin() ) {
				require_once EDD_UPLOAD_FILE_DIR . 'includes/admin/download/metabox.php';
				require_once EDD_UPLOAD_FILE_DIR . 'includes/admin/settings/register.php';
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
			if ( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Upload File', EDD_UPLOAD_FILE_VER, 'Daniel J Griffiths' );
			}
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
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-upload-file/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-upload-file/ folder
				load_textdomain( 'edd-upload-file', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-upload-file/languages/ folder
				load_textdomain( 'edd-upload-file', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-upload-file', false, $lang_dir );
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
function edd_upload_file() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		if ( ! class_exists( 'S214_EDD_Activation' ) ) {
			require_once( 'includes/libraries/class.s214-edd-activation.php' );
		}

		$activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();
	} else {
		return EDD_Upload_File::instance();
	}
}
add_action( 'plugins_loaded', 'edd_upload_file' );


/**
 * Process upgrades
 *
 * @since       1.0.1
 * @global      array $edd_options The EDD options array
 * @return      void
 */
function edd_upload_file_upgrade() {
	global $edd_options;

	if ( empty( $edd_options['edd_upload_file_location'] ) && ! empty( $edd_options['fu_upload_location'] ) ) {
		$edd_options['edd_upload_file_location'] = $edd_options['fu_upload_location'];
		unset( $edd_options['fu_upload_location'] );
	}

	if ( empty( $edd_options['edd_upload_file_extensions'] ) && ! empty( $edd_options['fu_file_extensions'] ) ) {
		$edd_options['edd_upload_file_extensions'] = $edd_options['fu_file_extensions'];
		unset( $edd_options['fu_file_extensions'] );
	}

	if ( empty( $edd_options['edd_upload_file_limit'] ) && ! empty( $edd_options['fu_file_limit'] ) ) {
		$edd_options['edd_upload_file_limit'] = $edd_options['fu_file_limit'];
		unset( $edd_options['fu_file_limit'] );
	}

	update_option( 'edd_settings', $edd_options );
}
register_activation_hook( __FILE__, 'edd_upload_file_upgrade' );
