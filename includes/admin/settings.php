<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_fu_settings( $settings ) {

	$edd_fu_settings = array(
		'fu_settings' => array(
			'id'   => 'fu_settings',
			'name' => '<strong>' . __( 'File Upload Settings', 'edd-fu' ) . '</strong>',
			'desc' => '',
			'type' => 'header'
		),
		'fu_upload_location' => array(
			'id'      => 'fu_upload_location',
			'name'    => __( 'File Upload Location', 'edd-fu' ),
			'desc'    => '',
			'type'    => 'select',
			'options' => array(
				'receipt'  => 'Receipt Page',
				'checkout' => 'Checkout Page',
			),
		),
		'fu_file_extensions' => array(
			'id'   => 'fu_file_extensions',
			'name' => __( 'Allowed File Extensions', 'edd-fu' ),
			'desc' => __( 'Comma separate extensions, leave blank to allow all', 'edd-fu' ),
			'type' => 'text'
		),
		'fu_file_limit' => array(
			'id'   => 'fu_file_limit',
			'name' => __( 'Allowed number of files', 'edd-fu' ),
			'desc' => __( 'Enter the allowed number of file uploads per download, enter 0 for unlimited', 'edd-fu' ),
			'type' => 'text',
			'size' => 'regular',
			'std'  => '1',
		),
	);

	return array_merge( $settings, $edd_fu_settings );

}

add_filter( 'edd_settings_extensions', 'edd_fu_settings', 1 );

function edd_fu_sanitize_file_limit( $value, $key ) {

	if ( 'fu_file_limit' == $key ) {
			return (int) $value;
	}else if ( 'fu_file_extensions' == $key ) {
		return str_ireplace( '.', '', str_ireplace( ' ', '', $value ) );
	}

	return $value;

}

add_filter( 'edd_settings_sanitize', 'edd_fu_sanitize_file_limit', 10, 2 );