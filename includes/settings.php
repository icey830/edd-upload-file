<?php

function edd_fu_settings( $settings ) {
	$edd_fu_settings = array(
		array(
			'id' 		=> 'fu_settings',
			'name' 	=> '<strong>' . __('File Upload Settings', 'edd-fu') . '</strong>',
			'desc' 	=> '',
			'type' 	=> 'header'
		),
		array(
			'id'    => 'fu_file_location',
			'name'  => __( 'File Upload Location', 'edd-fu' ),
			'desc'  => '',
			'type'  => 'select',
			'options' => array(
				'receipt' 	=> 'Receipt Page',
				'checkout' 	=> 'Checkout Page',
			),
		),
		array(
			'id' 		=> 'fu_file_extensions',
			'name' 	=> __('Allowed File Extensions', 'edd-fu'),
			'desc' 	=> __('Comma separate extensions, leave blank to allow all', 'edd-fu'),
			'type' 	=> 'text'
		),
		array(
			'id' 		=> 'fu_file_limit',
			'name' 	=> __('Allowed number of files', 'edd-fu'),
			'desc' 	=> __('Enter the allowed number of file uploads per download, enter 0 for unlimited', 'edd-fu'),
			'type' 	=> 'text',
			'size'  => 'regular',
			'std'		=> '1',
		),
	);

	return array_merge( $settings, $edd_fu_settings );
}

add_filter( 'edd_settings_extensions', 'edd_fu_settings', 1 );