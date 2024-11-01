<?php
/**
 * @package : Where are my post types?
 * @version : 1.0
 * @last modified : March 21 2014
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="wrap supt-wrapper">
	<h2><?php _e( 'Where are my post types?', SUPT_TEXT_DOMAIN ); ?></h2>

	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="donation">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
		<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>

	<table class="form-table supt-main">
		<tbody>
			<tr valign="top">
				<th scope="row"><?php _e( 'Activated Post Types', SUPT_TEXT_DOMAIN ); ?></th>
				<td>
					<?php
					global $default_post_types;

					$activated_post_types = get_post_types();
					foreach ( $activated_post_types as $post_type ) {
						if ( !in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item' ) ) ) {
							$post_type_object = get_post_type_object( $post_type );

							?>
							<p>
								<?php if ( in_array( $post_type, $default_post_types ) ) echo '<code>' . __( 'Default Post Type', SUPT_TEXT_DOMAIN ) . '</code>'; ?>
								<a href="<?php echo $_SERVER['REQUEST_URI'] ?>&mode=posttype&posttype=<?php echo $post_type ?>"><?php echo $post_type_object->labels->name ?></a>
							</p>
							<?php
						}
					}
					?>
					<p class="description"><?php _e( 'Click a post type link which you want to modify.', SUPT_TEXT_DOMAIN ); ?></p>

					<input type="hidden" id="supt_activated_post_types" value="<?php echo implode( ',', $activated_post_types ) ?>" />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Missing Post Types', SUPT_TEXT_DOMAIN ); ?></th>
				<td id="wrap-missing_post_type">
					<?php
					$missing_post_types = get_option( 'supt_missing_posttypes' );

					if ($missing_post_types) {
						$missing_post_types = explode( ',', $missing_post_types );
						foreach ( $missing_post_types as $post_type ) {
							?>
							<p class="post_type">
								<a href="<?php echo $_SERVER['REQUEST_URI'] ?>&mode=posttype&posttype=<?php echo $post_type ?>">
									<?php echo $post_type ?>
								</a>
							</p>
							<?php
						}
					} else {
						?>
						<div id="message" class="updated"><p><?php _e( 'No missing post types which was founded.', SUPT_TEXT_DOMAIN ); ?></p></div>
						<?php
					}
					?>

					<p class="description"><?php _e( 'If you want to find your missing post types, click the button below.', SUPT_TEXT_DOMAIN ); ?></p>
					<button class="button button-primary" id="btn-find-missing-post-types"><?php _e('Find Missing Post Types', SUPT_TEXT_DOMAIN); ?></button> <img class="loading_spinner" src="<?php echo get_admin_url() ?>images/loading.gif" />
				</td>
			</tr>
		</tbody>
	</table>
</div>
