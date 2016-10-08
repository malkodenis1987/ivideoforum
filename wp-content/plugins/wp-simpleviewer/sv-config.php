<?php

$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
$wp_path = (isset($wp_path[0]) && $wp_path[0] !== '') ? $wp_path[0] : $_SERVER['DOCUMENT_ROOT'];

require_once($wp_path . '/wp-load.php');

$title = 'Add SimpleViewer Gallery';

$direction = is_rtl() ? 'rtl' : 'ltr';

$options = get_option('simpleviwer_options', array());
$gallery_id = isset($options['last_id']) ? $options['last_id'] + 1 : 1;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"<?php do_action('admin_xml_ns'); ?> <?php language_attributes('xhtml'); ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
		<link rel="stylesheet" href="<?php echo admin_url('css/colors-classic.css'); ?>" type="text/css" charset="utf-8" />
		<link rel="stylesheet" href="<?php echo admin_url('load-styles.php?c=0&amp;dir=' . $direction . '&amp;load=wp-admin'); ?>" type="text/css" charset="utf-8" />
		<link rel="stylesheet" href="<?php echo plugins_url('css/generate.css', __FILE__); ?>?ver=<?php echo $SimpleViewer->version ?>" type="text/css" charset="utf-8" />
		<script src="<?php echo includes_url('js/jquery/jquery.js'); ?>" type="text/javascript" charset="utf-8"></script>
		<script src="<?php echo plugins_url('js/generate.js', __FILE__); ?>?ver=<?php echo $SimpleViewer->version ?>" type="text/javascript" charset="utf-8"></script>
		<script src="<?php echo plugins_url('js/edit.js', __FILE__); ?>?ver=<?php echo $SimpleViewer->version ?>" type="text/javascript" charset="utf-8"></script>
		<title><?php echo esc_html($title); ?> &lsaquo; <?php bloginfo('name') ?> &#8212; WordPress</title>
	</head>
	<body class="<?php echo apply_filters('admin_body_class', ''); ?>">

		<div id="generator" class="wrap">

			<h2><img src ="<?php echo plugins_url('img/icon_trans_35x26.png', __FILE__); ?>" align="top" alt="logo" /><?php echo esc_html($title); ?> Id <?php echo $gallery_id; ?></h2>
<?php
			$custom_values = $SimpleViewer->get_default_values();
			$pro_options = $SimpleViewer->get_pro_options($custom_values);
?>
			<form id="build-form-generate" action="" method="post">
<?php
				include plugin_dir_path(__FILE__) . 'fieldset.php';
?>
				<div class="col1">
					<input type="button" class="button" id="generate" name="generate" value="Add Gallery" />
					<input type="button" class="button" id="do-not-generate" name="do-not-generate" value="Cancel" />
				</div>

			</form>

		</div>

		<script type="text/javascript">
			// <![CDATA[
			jQuery(document).ready(function() {
				try {
					SV.Gallery.Generator.postUrl = "<?php echo plugins_url('save-gallery.php', __FILE__); ?>";
					SV.Gallery.Generator.initialize();
				} catch (e) {
					throw "SV is not defined. A SimpleViewer shortcode tag will not be inserted into the post.";
				}
			});
			// ]]>
		</script>

	</body>
</html>
