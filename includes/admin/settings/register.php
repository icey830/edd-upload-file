<?php
/**
 * Settings
 *
 * @package         EDD\UploadFile\Admin\Settings
 * @since           1.0.6
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add settings section
 *
 * @since       1.0.6
 * @param       array $sections The existing extensions sections
 * @return      array The modified extensions settings
 */
function edd_upload_file_add_settings_section( $sections ) {
	$sections['upload-file'] = __( 'Upload File', 'edd-upload-file' );

	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'edd_upload_file_add_settings_section' );


/**
 * Add settings
 *
 * @since       1.0.6
 * @param       array $settings the existing plugin settings
 * @return      array
 */
function edd_upload_file_register_settings( $settings ) {
	$new_settings = array(
		'upload-file' => apply_filters( 'edd_upload_file_settings', array(
			array(
				'id'   => 'edd_upload_file_general_settings',
				'name' => '<strong>' . __( 'General Settings', 'edd-upload-file' ) . '</strong>',
				'desc' => '',
				'type' => 'header'
			),
			array(
				'id'            => 'edd_upload_file_location',
				'name'          => __( 'File Upload Location', 'edd-upload-file' ),
				'desc'          => __( 'Specify where to display the file upload form', 'edd-upload-file' ),
				'type'          => 'select',
				'std'           => 'checkout',
				'tooltip_title' => __( 'What does this mean?', 'edd-upload-file' ),
				'tooltip_desc'  => __( 'Upload File works by adding a form that allows users to upload files during the purchase process. By default, this is displayed on the checkout page but it can be relocated to the receipt page if you so choose.', 'edd-upload-file' ),
				'options'       => array(
					'checkout' => __( 'Checkout Page', 'edd-upload-file' ),
					'receipt'  => __( 'Receipt Page', 'edd-upload-file' )
				)
			),
			array(
				'id'            => 'edd_upload_file_extensions',
				'name'          => __( 'Allowed File Extensions', 'edd-upload-file' ),
				'desc'          => __( 'Comma separate list of allowed extensions, leave blank to allow all', 'edd-upload-file' ),
				'type'          => 'text',
				'tooltip_title' => __( 'Note', 'edd-upload-file' ),
				'tooltip_desc'  => __( 'WordPress disallows certain file types for security reasons. Click the link below for a complete list of currently allowed file types. The allowed extension list can also be overridden on a per-product basis.', 'edd-upload-file' ),
			),
			array(
				'id'   => 'edd_upload_file_extension_list',
				'name' => '',
				'desc' => '<div class="edd-upload-file-show-file-types-wrap"><a href="#" class="edd-upload-file-show-file-types">' . __( 'Show allowed file types', 'edd-upload-file' ) . '</a></div>' . edd_upload_file_get_allowed_file_types( true ),
				'type' => 'descriptive_text'
			),
			array(
				'id'            => 'edd_upload_file_limit',
				'name'          => __( 'Maximum number of files', 'edd-upload-file' ),
				'desc'          => __( 'Enter the allowed number of file uploads per download, or 0 for unlimited', 'edd-upload-file' ),
				'type'          => 'number',
				'size'          => 'small',
				'std'           => 1,
				'tooltip_title' => __( 'Note', 'edd-upload-file' ),
				'tooltip_desc'  => __( 'This setting can be overridden on a per-product basis.', 'edd-upload-file' ),
			),
			array(
				'id'   => 'edd_upload_file_allow_download',
				'name' => __( 'Allow User Download', 'edd-upload-file' ),
				'desc' => __( 'Check to allow users to download files from the purchase details page', 'edd-upload-file' ),
				'type' => 'checkbox'
			),
			array(
				'id'   => 'edd_upload_file_text_settings',
				'name' => '<strong>' . __( 'Text Settings', 'edd-upload-file' ) . '</strong>',
				'desc' => '',
				'type' => 'header'
			),
			array(
				'id'   => 'edd_upload_file_form_title',
				'name' => __( 'Upload Form Title', 'edd-upload-file' ),
				'desc' => __( 'Specify the title for the file upload form', 'edd-upload-file' ),
				'type' => 'text',
				'std'  => __( 'Upload File(s)', 'edd-upload-file' )
			),
			array(
				'id'   => 'edd_upload_file_form_desc',
				'name' => __( 'Upload Form Description', 'edd-upload-file' ),
				'desc' => __( 'Specify the description to display on the file upload form, or leave blank for none', 'edd-upload-file' ),
				'type' => 'text',
				'std'  => __( 'Upload any files to attach to this order.', 'edd-upload-file' ),
			),
			array(
				'id'            => 'edd_upload_file_line_item',
				'name'          => __( 'Upload Form Line Item', 'edd-upload-file' ),
				'desc'          => __( 'Specify the title to display for individual product upload fields', 'edd-upload-file' ),
				'type'          => 'text',
				'std'           => sprintf( __( 'Upload %s %s for %s', 'edd-upload-file' ), '{limit}', '{files}', '{product}' ),
				'tooltip_title' => __( 'Template Tags', 'edd-upload-file' ),
				'tooltip_desc'  => __( 'This field supports the following template tags:', 'edd-upload-file' ) . '<br /><br />' .
									'{limit}<br />' . __( 'The upload limit for a given product', 'edd-upload-file' ) . '<br /><br />' .
									'{files}<br />' . __( 'Displays \'file\' or \'files\' depending on the upload limit', 'edd-upload-file' ) . '<br /><br />' .
									'{product}<br />' . __( 'The name of the product uploaded files are relevant to', 'edd-upload-file' )
			)
		) )
	);

	return array_merge( $settings, $new_settings );
}
add_filter( 'edd_settings_extensions', 'edd_upload_file_register_settings', 1 );


/**
 * Add debug option if the S214 Debug plugin is enabled
 *
 * @since       2.0.0
 * @param       array $settings The current settings
 * @return      array $settings The updated settings
 */
function edd_upload_file_add_debug( $settings ) {
	if ( class_exists( 'S214_Debug' ) ) {
		$debug_setting[] = array(
			'id'   => 'edd_upload_file_debugging',
			'name' => '<strong>' . __( 'Debugging', 'edd-upload-file' ) . '</strong>',
			'desc' => '',
			'type' => 'header'
		);

		$debug_setting[] = array(
			'id'   => 'edd_upload_file_enable_debug',
			'name' => __( 'Enable Debug', 'edd-upload-file' ),
			'desc' => sprintf( __( 'Log plugin errors. You can view errors %s and in the Javascript console', 'edd-upload-file' ), '<a href="' . admin_url( 'tools.php?page=s214-debug-logs' ) . '">' . __( 'here', 'edd-upload-file' ) . '</a>' ),
			'type' => 'checkbox'
		);

		$settings = array_merge( $settings, $debug_setting );
	}

	return $settings;
}
add_filter( 'edd_upload_file_settings', 'edd_upload_file_add_debug' );
