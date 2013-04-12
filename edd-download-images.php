<?php
/*
Plugin Name: EDD Download Images
Plugin URI: http://sumobi.com/store/edd-download-images/
Description: Allows you to add additional images to a download
Version: 1.0
Author: Andrew Munro - Sumobi
Author URI: http://sumobi.com
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php
*/


/**
 * Internationalization
 */
function edd_di_textdomain() {
	load_plugin_textdomain( 'edd-di', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'edd_di_textdomain' );


/**		
 * Hook into save filter and add the download image fields
 * @since 1.0 
*/
function edd_di_metabox_fields_save( $fields ) {

	$fields[] = 'edd_download_images';

	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_di_metabox_fields_save' );


/**
 * Gets all images for a download.
 * @since       1.0
 * @return      array	
 */
function edd_di_get_images() {

	$images = array();
	$download_images = get_post_meta( get_the_ID(), 'edd_download_images', true );

	$images = $download_images;

	return $images;
}

/**		
 * Outputs each images with a CSS of 'edd-di-image'
 * @since 1.0
*/
function edd_di_display_images() {

	$download_images = edd_di_get_images();

	if( $download_images ) {
		foreach ( $download_images as $download_image ) {
			$html = '<img class="edd-di-image" src="' . $download_image['image'] . '" />';
			echo apply_filters( 'edd_di_display_images', $html, $download_image );
		}	
	}
	
}

/**
 * Sanitize the images downloads
 * Ensures files are correctly mapped to an array starting with an index of 0
 * @since 1.0
 * @return array
 */
function edd_sanitize_images_save( $images ) {
	// Make sure all files are rekeyed starting at 0
	return array_values( $images );
}
add_filter( 'edd_metabox_save_edd_download_images', 'edd_sanitize_images_save' );


/**
 * Render the download images fields
 * @since 1.0 
 */
function edd_render_download_images_field( $post_id ) {
	$images = edd_di_get_images();
?>
	<div id="edd_download_images">

		<p>
			<strong><?php _e( 'Download Images:', 'edd-di' ); ?></strong>
		</p>

		<input type="hidden" id="edd_download_images" class="edd_repeatable_upload_name_field" value=""/>

		<div id="edd_image_fields" class="edd_meta_table_wrap">
			<table class="widefat" width="100%" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th><?php _e( 'Image URL', 'edd-di' ); ?></th>
						<?php do_action( 'edd_download_image_table_head', $post_id ); ?>
						<th style="width: 2%"></th>
					</tr>
				</thead>
				<tbody>
				<?php
					if ( ! empty( $images ) ) :
						foreach ( $images as $key => $value ) :
							$image = isset( $value['image'] ) ? $value['image'] : '';

							$args = apply_filters( 'edd_image_row_args', compact( 'image' ), $value );
				?>
						<tr class="edd_repeatable_upload_wrapper">
							<?php do_action( 'edd_render_image_row', $key, $args, $post_id ); ?>
						</tr>
				<?php
						endforeach;
					else :
				?>
					<tr class="edd_repeatable_upload_wrapper">
						<?php do_action( 'edd_render_image_row', 0, array(), $post_id ); ?>
					</tr>
				<?php endif; ?>
					<tr>
						<td class="submit" colspan="4" style="float: none; clear:both; background: #fff;">
							<a class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php _e( 'Add New Image', 'edd-di' ); ?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
<?php
}
add_action( 'edd_meta_box_fields', 'edd_render_download_images_field', 30 );


/**
 * Individual image row.
 * @since       1.0  
 */
function edd_render_image_row( $key = '', $args = array(), $post_id ) {
	$defaults = array(
		'image' => null,
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

?>

	<td>
		<div class="edd_repeatable_upload_field_container">
			<input type="text" class="edd_repeatable_upload_field edd_upload_field" name="edd_download_images[<?php echo $key; ?>][image]" id="edd_download_images[<?php echo $key; ?>][image]" value="<?php echo $image; ?>" placeholder="<?php _e( 'http://', 'edd-di' ); ?>" style="width:100%" />

			<span class="edd_upload_file">
				<a href="#" data-uploader_title="" data-uploader_button_text="<?php _e( 'Insert', 'edd-di' ); ?>" class="edd_upload_image_button" onclick="return false;"><?php _e( 'Upload an Image', 'edd-di' ); ?></a>
			</span>
		</div>
	</td>

	<?php do_action( 'edd_download_image_table_row', $post_id, $key, $args ); ?>

	<td>
		<a href="#" class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
	</td>
<?php
}
add_action( 'edd_render_image_row', 'edd_render_image_row', 10, 3 );


/**
 * Don't save blank rows.
 *
 * When saving, check the image table for blank rows.
 * If the image field is empty, that row should not
 * be saved.
 * @since 1.0
 */
function edd_metabox_image_save_check_blank_rows( $new ) {
	foreach ( $new as $key => $value ) {
		if ( empty( $value['image'] ) )
			unset( $new[ $key ] );
	}

	return $new;
}
add_filter( 'edd_metabox_save_edd_download_images', 'edd_metabox_image_save_check_blank_rows' );


/**		
 * Shortcode
 * @since 1.0
*/

function edd_di_shortcode( $atts ) {
	edd_di_display_images();
}
add_shortcode( 'edd_download_images', 'edd_di_shortcode' );