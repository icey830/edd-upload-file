<?php
/*
Plugin Name: EDD File Upload
Plugin URI: http://www.barrykooij.com/edd-file-upload
Description: EDD File Upload Extension
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

	private function __construct() {
		$this->includes();
		$this->init();
	}

	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new EDD_File_Upload();
		}

		return self::$instance;
	}

	/**
	 * Function that requires all includes
	 */
	private function includes() {

		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/EDD_FU_File_Manager.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/admin/settings.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/admin/view-order-details.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/admin/install.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/frontend/print-uploaded-files.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/frontend/receipt.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/frontend/checkout.php';
		require_once EDD_FILE_UPLOAD_PLUGIN_DIR . 'includes/updater/EDD_License_Handler.php';

	}

	private function init() {

		// Load plugin textdomain
		load_plugin_textdomain( 'edd-fu', false, dirname( plugin_basename( EDD_FILE_UPLOAD_PLUGIN_FILE ) ) . '/languages/' );


		// Instantiate the licensing / updater.
		$license = new EDD_License( __FILE__, self::PLUGIN_NAME, self::PLUGIN_VERSION_NAME, self::PLUGIN_AUTHOR );

	}

	/**
	 * Function to load EDD options, settings default file upload options when not set by user
	 *
	 * @return array EDD options
	 */
	public function get_options() {
		global $edd_options;
		return wp_parse_args( $edd_options, array(
			'fu_upload_location' => 'receipt',
			'fu_file_limit'      => '1',
			'fu_file_extensions' => '',
		) );
	}

}

function EDD_File_Upload() {
	return EDD_File_Upload::instance();
}

// Run EDD File Upload
EDD_File_Upload();