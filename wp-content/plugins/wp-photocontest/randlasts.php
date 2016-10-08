<?php
/*
+-------------------------------------------------------------------+
|																    |
|	WordPress Plugin: WP-PhotoContest                               | 
|	Copyright (c) 2009-2010 Frank van der Stad	                    |
|																    |
|	File Written By:											    |
|	- Frank van der Stad										    |
|	- http://www.vanderstad.nl/wp-photocontest					    |
|																    |
|	File Information:											    |
|	- Returns a random last photo of a contest 	                    |
|	- wp-content/plugins/wp-photocontest/randlasts.php              |
|																    |
+-------------------------------------------------------------------+
*/
### Include the configfile
require_once(dirname(__FILE__).'/wp-photocontest-config.php');

### Add the scriptfiles to the header
$myScripts = array('jquery','wp-photocontest.js','common.js');
foreach ($myScripts as $myScript)
{
	if (ereg(".js",$myScript))
	{
		$myScriptUrl = WP_PLUGIN_URL . '/wp-photocontest/js/'.$myScript;
		$myScriptFile = WP_PLUGIN_DIR . '/wp-photocontest/js/'.$myScript;
		if ( file_exists($myScriptFile) ) {
			wp_register_script($myScript.'-script', $myScriptUrl);
			wp_enqueue_script($myScript.'-script');	
		}
	}
	else
	{
		wp_enqueue_script($myScript,'',array(),false,false);
	}
}


### Add the stylesheets to the header
$myStyles = array('skins/'.CONTESTS_SKIN.'/theme.css');
foreach ($myStyles as $myStyle)
{
	$myStyleUrl = WP_PLUGIN_URL . '/wp-photocontest/'.$myStyle;
	$myStyleFile = WP_PLUGIN_DIR . '/wp-photocontest/'.$myStyle;
	if ( file_exists($myStyleFile) ) {
		wp_register_style($myStyle.'-Style', $myStyleUrl);
		wp_enqueue_style($myStyle.'-Style');	
	}
}

##############################
### Get the page variables ###
##############################
$q2		= "SELECT contest_id,img_id,img_path,img_name FROM ".$wpdb->prefix."photocontest WHERE visibile=1 ORDER BY img_id DESC";
$o2		= $wpdb->get_results($q2);
$who	= rand(0,(count($o2) - 1));
$o3		= (array) $wpdb->get_row( $wpdb->prepare( "SELECT contest_path,post_id FROM ".$wpdb->prefix."photocontest_admin WHERE contest_id = %d", $o2[$who]->contest_id) );

##############################
### Get the page content   ###
##############################
?>
<a href="<?php echo get_option('siteurl');?>/?page_id=<?php echo $o3['post_id'];?>">
	<img src="<?php echo get_option('siteurl');?>/wp-content/plugins/wp-photocontest/<?php echo CONTESTS_PATH;?>/<?php echo $o3['contest_path'];?>/med_<?php echo $o2[$who]->img_name;?>">
</a>