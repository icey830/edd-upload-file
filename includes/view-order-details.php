<?php

function edd_fu_view_uploaded_files( $payment_id ) {

?>

	<div id="edd-purchased-files" class="postbox">
		<h3 class="hndle"><?php _e( 'Uploaded Files', 'edd-fu' ); ?></h3>
		<div class="inside">
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<tbody id="the-list">
				<?php
				$uploaded_files = get_post_meta( $payment_id, 'edd_fu_file' );
				if( $uploaded_files != '' ) {
					$i = 0;
					foreach ( $uploaded_files as $key => $uploaded_file ) {
						?>
						<tr class="<?php if ( $i % 2 == 0 ) { echo 'alternate'; } ?>">
							<td class="name column-name">
								<?php
									echo __( 'File', 'edd-fu' ) . ' ' . ( $i + 1 );
								?>
							</td>
							<td class="price column-price">
							<?php
								echo '<a href="' . EDD_File_Upload::instance()->get_file_url() . '/' . $uploaded_file . '" target="_blank">' . __( 'View', 'edd-fu' ) . '</a>';
							?>
							</td>
						</tr>
						<?php
						$i++;
					}
				}
				?>
				</tbody>
			</table>
		</div><!-- /.inside -->
	</div><!-- /#edd-purchased-files -->

<?php

}

add_action( 'edd_view_order_details_main_after', 'edd_fu_view_uploaded_files' );