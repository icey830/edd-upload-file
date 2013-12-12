<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_fu_dependency_notice() {
	echo '<div class="error"><p><strong>' . __( 'EDD Upload File requires Easy Digital Downloads to be installed and activated.', 'edd-fu' ) . '</strong></p></div>';
}

function edd_fu_dependency_check() {

	// Check if EDD is installed and activated
	if ( ! is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
		add_action( 'admin_notices', 'edd_fu_dependency_notice' );
		deactivate_plugins( plugin_basename( EDD_UPLOAD_FILE_PLUGIN_FILE ) );
	}

}

// Plugin dependency check
add_action( 'admin_init', 'edd_fu_dependency_check', 0 );