<?php
/**
 * Add metaboxes
 *
 * @package     EDD\UploadFile\Download\Metabox
 * @since       1.0.1
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register meta box for Upload File
 *
 * @since       1.0.1
 * @return      void
 */
function edd_upload_file_add_meta_box() {
	add_meta_box( 'upload_file', __( 'File Uploads', 'edd-upload-file' ), 'edd_upload_file_render_meta_box', 'download', 'side', 'core' );
}
add_action( 'add_meta_boxes', 'edd_upload_file_add_meta_box' );


/**
 * Render meta box
 *
 * @since       1.0.1
 * @global      object $post The post we are editing
 * @return      void
 */
function edd_upload_file_render_meta_box() {
	global $post;

	$post_id = $post->ID;

	// Convert old values
	$enabled = get_post_meta( $post_id, '_edd_fu_enabled', true ) ? true : false;

	if ( $enabled ) {
		update_post_meta( $post_id, '_edd_upload_file_enabled', true );
		delete_post_meta( $post_id, '_edd_fu_enabled' );
	}

	$enabled    = get_post_meta( $post_id, '_edd_upload_file_enabled', true ) ? true : false;
	$limit      = get_post_meta( $post_id, '_edd_upload_file_limit', true );
	$limit      = (int) $limit > 0 ? $limit : 0;
	$extensions = get_post_meta( $post_id, '_edd_upload_file_extensions', true );
	?>
	<div id="edd_upload_file_enabled_wrap">
		<p>
			<strong><?php _e( 'Enable Uploads:', 'edd-upload-file' ); ?></strong>
		</p>
		<input type="checkbox" name="_edd_upload_file_enabled" id="_edd_upload_file_enabled" value="1" <?php echo checked( true, $enabled, false ); ?>/>
		<label for="_edd_upload_file_enabled">
			<?php _e( 'Enable uploads for this product?', 'edd-upload-file' ); ?>
		</label>
	</div>

	<div id="edd_upload_file_limit_wrap" style="display: none;">
		<p>
			<strong><?php _e( 'Upload Limit:', 'edd-upload-file' ); ?></strong>
		</p>
		<input type="number" class="small-text" min="0" step="1" name="_edd_upload_file_limit" id="_edd_upload_file_limit" value="<?php echo $limit; ?>" />
		<label for="_edd_upload_file_limit">
			<?php _e( 'Set the upload limit for this product', 'edd-upload-file' ); ?>
			<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php echo '<strong>' . __( 'Upload Limit:', 'edd-upload-file' ) . '</strong> ' . __( 'Set the file upload limit for this product, or set to \'0\' to use the system default.', 'edd-upload-file' ); ?>"></span>
		</label>
	</div>

	<div id="edd_upload_file_extensions_wrap" style="display: none;">
		<p>
			<strong><?php _e( 'Allowed Extensions:', 'edd-upload-file' ); ?></strong>
		</p>
		<input type="text" class="widefat" name="_edd_upload_file_extensions" id="_edd_upload_file_extensions" value="<?php echo $extensions; ?>" />
		<label for="_edd_upload_file_extensions">
			<?php _e( 'Comma-separated list of allowed extensions', 'edd-upload-file' ); ?>
			<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php echo '<strong>' . __( 'Allowed Extensions:', 'edd-upload-file' ) . '</strong> ' . __( 'Enter a comma-separated list of allowed extensions, or leave blank to use the system default.', 'edd-upload-file' ) . '<br /><br /><strong>' . __( 'Note:', 'edd-upload-file' ) . '</strong> ' . __( 'Check the main Upload File setting page for a list of extensions currently allowed by WordPress.', 'edd-upload-file' ); ?>"></span>
		</label>
	</div>
	<?php
	// Allow extension of the PDF Stamper metabox
	do_action( 'edd_upload_file_meta_box_fields', $post_id );

	wp_nonce_field( basename( __FILE__ ), 'edd_upload_file_meta_box_nonce' );
}


/**
 * Save post meta when the save_post action is called
 *
 * @since       1.0.1
 * @param       int $post_id The ID of the post we are saving
 * @global      object $post The post we are saving
 * @return      void
 */
function edd_upload_file_meta_box_save( $post_id ) {
	global $post;

	// Don't process if nonce can't be validated
	if ( ! isset( $_POST['edd_upload_file_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['edd_upload_file_meta_box_nonce'], basename( __FILE__ ) ) ) return $post_id;

	// Don't process if this is an autosave
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) return $post_id;

	// Don't process if this is a revision
	if ( isset( $post->post_type ) && $post->post_type == 'revision' ) return $post_id;

	// Don't process if the current user shouldn't be editing this product
	if ( ! current_user_can( 'edit_product', $post_id ) ) return $post_id;

	// The default fields that get saved
	$fields = apply_filters( 'edd_upload_file_metabox_fields_save', array(
		'_edd_upload_file_enabled',
		'_edd_upload_file_limit',
		'_edd_upload_file_extensions'
	) );

	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			if ( is_string( $_POST[ $field ] ) ) {
				$new = esc_attr( $_POST[ $field ] );
			} else {
				$new = $_POST[ $field ];
			}

			$new = apply_filters( 'edd_upload_file_meta_box_save_' . $field, $new );

			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}
}
add_action( 'save_post', 'edd_upload_file_meta_box_save' );
