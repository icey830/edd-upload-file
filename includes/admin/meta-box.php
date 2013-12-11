<?php
/**
 * EDD File upload Meta Box
 *
 * @since 1.0
 */
function edd_fu_add_meta_box() {
	add_meta_box( 'edd_fu_meta_box', __( 'File Uploads', 'edd-vps' ), 'edd_fu_render_meta_box', 'download', 'side', 'core' );
}
add_action( 'add_meta_boxes', 'edd_fu_add_meta_box', 100 );


/**
 * Render the file upload meta box
 *
 * @since 1.0
 */
function edd_fu_render_meta_box()	{
	global $post;

	// Use nonce for verification
	echo '<input type="hidden" name="edd_fu_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	$enabled = get_post_meta( $post->ID, '_edd_fu_enabled', true ) ? true : false;

	echo '<tr>';
	echo '<td class="edd_field_type_text" colspan="2">';
	echo '<input type="checkbox" name="edd_fu_enabled" id="edd_fu_enabled" value="1" ' . checked( true, $enabled, false ) . '/>&nbsp;';
	echo '<label for="edd_fu_enabled">' . __( 'Check to enable file uploads', 'edd-fu' ) . '</label>';
	echo '<td>';
	echo '</tr>';

	echo '</table>';
}


/**
 * Save meta box data
 *
 * @since 1.0
 */
function edd_fu_meta_box_save( $post_id ) {

	global $post;

	// verify nonce
	if ( isset( $_POST['edd_fu_meta_box_nonce'] ) && ! wp_verify_nonce( $_POST['edd_fu_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// Check post type
	if ( isset( $_POST['post_type'] ) && 'download' != $_POST['post_type'] ) {
		return $post_id;
	}

	// Check capabilities
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// Update meta data
	if ( isset( $_POST['edd_fu_enabled'] ) ) {
		update_post_meta( $post_id, '_edd_fu_enabled', true );
	} else {
		delete_post_meta( $post_id, '_edd_fu_enabled' );
	}

}
add_action( 'save_post', 'edd_fu_meta_box_save' );