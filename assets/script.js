/**
 * @package : Where are my post types?
 * @version : 1.0
 * @last modified : March 21 2014
 */

jQuery(document).ready(function($) {
	$( '#btn-find-missing-post-types' ).click( function( e ) {
		e.preventDefault();

		var data = {
			action: 'supt_find_missing_post_types',
			activated_post_types: $( '#supt_activated_post_types' ).val()
		};

		$( '.supt-main .loading_spinner' ).show();
		$( '#wrap-missing_post_type .post_type' ).remove();

		$.post( ajaxurl, data, function( response ) {
			if ( response ) {
				response = response.split( ',' );
				$.each( response, function( i, post_type ) {
					$( '#wrap-missing_post_type' ).prepend( '<p class="post_type"><a href="' + ajaxurl + '&mode=posttype&posttype=' + post_type + '">' + post_type + '</a></p>' );
				});
			} else {
				$( '#wrap-missing_post_type .description' ).html( 'No missing post type. Enjoy!' );
			}
			$( '.supt-main .loading_spinner' ).hide();
		});
		return false;
	});
});