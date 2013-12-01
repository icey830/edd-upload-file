<?php
/*
Plugin Name: Easy Digital Downloads - File Upload
Plugin URI: http://www.barrykooij.com/edd-file-upload
Description: The File Upload extension allows your customers to attach a file to their order. Files can be attached at the checkout page or at the receipt page.
Version: 1.0.0
Author: Barry Kooij
Author URI: http://www.barrykooij.com/
*/

if ( ! defined( 'EDD_FILE_UPLOAD_PLUGIN_DIR' ) ) {
	define( 'EDD_FILE_UPLOAD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EDD_FILE_UPLOAD_PLUGIN_FILE' ) ) {
	define( 'EDD_FILE_UPLOAD_PLUGIN_FILE', __FILE__ );
}

class EDD_File_Upload {

	private static $instance = null;

	const PLUGIN_NAME         = 'EDD File Upload';
	const PLUGIN_VERSION_NAME = '1.0.0';
	const PLUGIN_VERSION_CODE = '1';
	const PLUGIN_AUTHOR       = 'Barry Kooij';

	public function __construct() {
		$this->includes();
		$this->init();
	}

	/**
	 * Function that requires all includes
	 */
	private function includes() {

		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/EDD_FU_File_Manager.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/admin/plugin-dependency.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/admin/settings.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/admin/view-order-details.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/frontend/print-uploaded-files.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/frontend/receipt.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/frontend/checkout.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/updater/EDD_License_Handler.php';

	}

	/**
	 * Function that setups the plugin
	 */
	private function init() {

		// Load plugin textdomain
		load_plugin_textdomain( 'edd-fu', false, dirname( plugin_basename( EDD_FILE_UPLOAD_PLUGIN_FILE ) ) . '/languages/' );


		// Instantiate the licensing / updater.
		$license = new EDD_License( __FILE__, self::PLUGIN_NAME, self::PLUGIN_VERSION_NAME, self::PLUGIN_AUTHOR );

		// Setup the File Manager
		EDD_FU_File_Manager::instance();

	}

	/**
	 * Function to load EDD options, settings default file upload options when not set by user
	 *
	 * @return array EDD options
	 */
	public static function get_options() {
		global $edd_options;
		return wp_parse_args( $edd_options, array(
			'fu_upload_location' => 'receipt',
			'fu_file_limit'      => '1',
			'fu_file_extensions' => '',
		) );
	}

	/**
	 * Function to display error messages
	 *
	 * @param $message
	 */
	public static function error_message( $message ) {

		$edd_fu_options = self::get_options();

		if ( $edd_fu_options['fu_upload_location'] == 'checkout' ) {

			$messages = EDD()->session->get( 'edd_cart_messages' );

			if ( ! $messages ) {
				$messages = array();
			}

			$messages['edd_fu_error_message'] = $message;

			EDD()->session->set( 'edd_cart_messages', $messages );

		}else {

			echo "<tr><td colspan='2' style='color:#ff0000;font-weight:bold;'>{$message}</td></tr>\n";

		}

	}

	/**
	 * Function that is run when plugin is installed
	 */
	public static function install() {

		// Check if EDD is installed and activated
		if ( ! is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			return;
		}

		// Load the File Manager
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/EDD_FU_File_Manager.php';

		// Create the EDD Files Upload dir
		wp_mkdir_p( EDD_FU_File_Manager::instance()->get_file_dir() );

	}

}

// Create object - Plugin init
add_action( 'plugins_loaded', create_function( '', 'new EDD_File_Upload();' ) );

// Activation hook
register_activation_hook( EDD_FILE_UPLOAD_PLUGIN_FILE, array( 'EDD_File_Upload', 'install' ) );