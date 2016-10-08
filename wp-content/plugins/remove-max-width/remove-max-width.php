<?php
/*
Plugin Name: Remove Max Width
Version: 1.3
Plugin URI: http://dd32.id.au/wordpress-plugins/remove-max-width/
Description: This plugin removes the max-width of the WordPress 2.5 Admin interface.
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/

/*
Changelog:
1.0: Initial Release
1.1: s/is_ie/is_IE/
1.2: consolodate max-width & override .error and .updated
1.3: add_action correction. Thanks Viper007Bond :)
*/


add_filter('tiny_mce_before_init', 'rmw_tinymce');
function rmw_tinymce($init){
	$init['theme_advanced_resize_horizontal'] = true;
	return $init;
}

add_action('admin_head','rmw_head',99); //Hook late after all css has been done
function rmw_head(){
	global $is_IE; ?>
<style type="text/css" media="all">
	.wrap, 
	.updated,
	.error,
	#the-comment-list td.comment {
		max-width: none !important;
	}
<?php if( $is_IE ){ ?>

	* html #wpbody { 
 		_width: 99.9% !important; 
 	}

<?php } ?>
</style>
<?php } ?>