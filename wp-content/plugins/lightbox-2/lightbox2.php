<?php
/*
Plugin Name: Lightbox 2
Plugin URI: http://www.stimuli.ca/lightbox/
Description: Used to overlay images on the current page. Lightbox JS v2.2 by <a href="http://www.huddletogether.com/projects/lightbox2/" title="Lightbox JS v2.2 ">Lokesh Dhakar</a>. Mad props to <a href="http://0xtc.com/" title="visit his site">Tanin</a> and <a href="http://www.bkjproductions.com" title="visit his site">Eric Strathmeyer</a> for improvements and fixes!
Version: 2.7.7
Author: Rupert Morris
Author URI: http://www.stimuli.ca/
*/

/* Where our theme reside: */
$lightbox_2_theme_path = (dirname(__FILE__)."/Themes");
update_option('lightbox_2_theme_path', $lightbox_2_theme_path);
/* Set the default theme to Black */
add_option('lightbox_2_theme', 'Black');
add_option('lightbox_2_automate', 1);

/* options page */
$options_page = get_option('siteurl') . '/wp-admin/admin.php?page=lightbox-2/options.php';
/* Adds our admin options under "Options" */
function lightbox_2_options_page() {
	add_options_page('Lightbox Options', 'Lightbox 2', 10, 'lightbox-2/options.php');
}

function lightbox_styles() {
    /* The next lines figures out where the javascripts and images and CSS are installed,
    relative to your wordpress server's root: */
    $lightbox_2_theme_path = (dirname(__FILE__)."/Themes");
    $lightbox_2_theme = urldecode(get_option('lightbox_2_theme'));
    $lightbox_style = (get_bloginfo('wpurl')."/wp-content/plugins/lightbox-2/Themes/".$lightbox_2_theme."/");
    $lightbox_path =  get_bloginfo('wpurl')."/wp-content/plugins/lightbox-2/";

    /* The xhtml header code needed for lightbox to work: */
	$lightboxscript = "
	<!-- begin lightbox scripts -->
	<script type=\"text/javascript\">
    //<![CDATA[
    document.write('<link rel=\"stylesheet\" href=\"".$lightbox_style."lightbox.css\" type=\"text/css\" media=\"screen\" />');
    //]]>
    </script>
	<!-- end lightbox scripts -->\n";
	/* Output $lightboxscript as text for our web pages: */
	echo($lightboxscript);
}

/* Added a code to automatically insert rel="lightbox[nameofpost]" to every image with no manual work. 
If there are already rel="lightbox[something]" attributes, they are not clobbered. 
Tanin, you are a regular expressions god! ;) 
http://0xtc.com/2008/05/27/auto-lightbox-function.xhtml
*/
function autoexpand_rel_wlightbox ($content) {
	global $post;
	$pattern[0] = "/<a(.*?)href=('|\")([A-Za-z0-9\/_\.\~\:-]*?)(\.bmp|\.gif|\.jpg|\.jpeg|\.png)('|\")([^\>]*?)>/i";
	$pattern[1] = "/<a(.*?)href=('|\")([A-Za-z0-9\/_\.\~\:-]*?)(\.bmp|\.gif|\.jpg|\.jpeg|\.png)('|\")(.*?)(rel=('|\")lightbox(.*?)('|\"))([ \t\r\n\v\f]*?)((rel=('|\")lightbox(.*?)('|\"))?)([ \t\r\n\v\f]?)([^\>]*?)>/i";
	$replacement[0] = '<a$1href=$2$3$4$5$6 rel="lightbox['.$post->ID.']">';
	$replacement[1] = '<a$1href=$2$3$4$5$6$7>';
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
}

if (get_option('lightbox_2_automate') == 1){
	add_filter('the_content', 'autoexpand_rel_wlightbox');
	add_filter('the_excerpt', 'autoexpand_rel_wlightbox');
}

if (!is_admin()) { // if we are *not* viewing an admin page, like writing a post or making a page:
	wp_enqueue_script('lightbox', (get_bloginfo('wpurl')."/wp-content/plugins/lightbox-2/lightbox.js"), array('scriptaculous-effects'), '2.2');
}

/* we want to add the above xhtml to the header of our pages: */
add_action('wp_head', 'lightbox_styles');
add_action('admin_menu', 'lightbox_2_options_page');
?>
