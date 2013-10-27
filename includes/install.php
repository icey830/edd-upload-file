<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function edd_fu_install() {

	// Create the EDD Files Upload dir
	wp_mkdir_p( EDD_File_Upload::instance()->get_file_dir() );

}

register_activation_hook( EDD_FILE_UPLOAD_PLUGIN_FILE, 'edd_fu_install' );

