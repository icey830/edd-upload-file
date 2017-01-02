<?php
/**
 * Scripts
 *
 * @package     EDD\UploadFile\Scripts
 * @since       2.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load admin scripts
 *
 * @since       2.0.0
 * @return      void
 */
function edd_upload_file_load_admin_scripts() {
	wp_enqueue_style( 'edd-upload-file', EDD_UPLOAD_FILE_URL . 'assets/css/admin.css', array(), EDD_UPLOAD_FILE_VER );
	wp_enqueue_script( 'edd-upload-file', EDD_UPLOAD_FILE_URL . 'assets/js/admin.js', array( 'jquery' ), EDD_UPLOAD_FILE_VER );
	wp_localize_script( 'edd-upload-file', 'edd_upload_file_vars', array(
		'hide_file_types' => __( 'Hide allowed file types', 'edd-upload-file' ),
		'show_file_types' => __( 'Show allowed file types', 'edd-upload-file' ),
		'delete_file'     => __( 'Are you sure you want to delete {filename}?', 'edd-upload-file' ),
		'ajaxurl'         => edd_get_ajax_url()
	) );
}
add_action( 'admin_enqueue_scripts', 'edd_upload_file_load_admin_scripts' );


/**
 * Load frontend scripts
 *
 * @since       2.0.0
 * @return      void
 */
function edd_upload_file_load_scripts() {
	wp_enqueue_style( 'edd-upload-file-fine', EDD_UPLOAD_FILE_URL . 'assets/js/fine-uploader/fine-uploader-gallery.css', array(), EDD_UPLOAD_FILE_VER );
	wp_enqueue_script( 'edd-upload-file-fine', EDD_UPLOAD_FILE_URL . 'assets/js/fine-uploader/fine-uploader.js', array( 'jquery' ), EDD_UPLOAD_FILE_VER );
	wp_enqueue_style( 'edd-upload-file', EDD_UPLOAD_FILE_URL . 'assets/css/edd-upload-file.css', array(), EDD_UPLOAD_FILE_VER );
	wp_enqueue_script( 'edd-upload-file', EDD_UPLOAD_FILE_URL . 'assets/js/edd-upload-file.js', array( 'edd-upload-file-fine' ), EDD_UPLOAD_FILE_VER );
	wp_localize_script( 'edd-upload-file', 'edd_upload_file_vars', array(
		'debug'           => edd_upload_file()->debugging,
		'ajaxurl'         => edd_get_ajax_url(),
		'endpoint'        => get_home_url() . '?edd-upload-file=',
		'placeholder_url' => EDD_UPLOAD_FILE_URL . 'assets/js/fine-uploader/placeholders'
	) );
}
add_action( 'wp_enqueue_scripts', 'edd_upload_file_load_scripts' );


/**
 * Load the uploader template
 *
 * @since       2.0.0
 * @return      void
 */
function edd_upload_file_load_template() {
	ob_start();
	edd_get_template_part( 'uploader', 'row' );
	echo ob_get_clean();
}
add_action( 'wp_head', 'edd_upload_file_load_template' );
