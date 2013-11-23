<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_fu_install() {

	// Check if EDD is installed and activated
	if ( ! is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
		return;
	}

	// Create the EDD Files Upload dir
	wp_mkdir_p( EDD_FU_File_Manager::instance()->get_file_dir() );

}

register_activation_hook( EDD_FILE_UPLOAD_PLUGIN_FILE, 'edd_fu_install' );

