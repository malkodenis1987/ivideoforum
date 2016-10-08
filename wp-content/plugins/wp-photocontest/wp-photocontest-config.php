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
|	- Config PhotoContest											|
|	- wp-content/plugins/wp-photocontest/wp-photocontest-config.php |
|																    |
+-------------------------------------------------------------------+
*/

### Based on ABSPATH include the wp-config
if ( defined('ABSPATH') )
{
	require_once( ABSPATH . 'wp-config.php');
}
else
{
    require_once('../../../wp-config.php');
    require_once('../../../wp-includes/wp-db.php');
}

### Add the wordpress-database class
global $wpdb;

### Include the debuglib
require_once(dirname(__FILE__).'/lib/debug.func.php');

$default_array = array(
					'option_id' => 1,
					'WP_USE_THEMES' => 'true',
					'DEBUG_FLAG' => 0,
					'CONTESTS_PATH' => 'contests_holder',
					'CONTESTS_SKIN' => 'aqua',
					'DEFAULT_STATUS' => 'publish',
					'DEFAULT_UID' => 1,
					'DEFAULT_PAGENAME' => '',
					'DEFAULT_PARENT' => 0,
					'DEFAULT_TYPE' => 'page',
					'DEFAULT_COMMENTS' => 'closed',
					'VOTING_METHOD' => 'star5',
					'ROLE_VOTING'=> '',
					'ROLE_UPLOAD'=> '',
					'VIEWIMG_BOX' => 442,
					'VISIBLE_UPLOAD' => 1,
					'VISIBLE_VOTING' => 1,					
					'N_PHOTO_X_PAGE' => 9,
					'SKIP_CAPTHA' => 0,
					'REDIRECT_AFTER_VOTE' => 0,
					'edit_date' => date('Y-m-d h:m:i')
				); 
$option_sql = "SELECT * FROM ".$wpdb->prefix."photocontest_config LIMIT 0,1";
$options_array = @(array) $wpdb->get_row( $wpdb->prepare( $option_sql ));

if (count($options_array)==0)
{
			$default_options = $wpdb->insert
			(
				$wpdb->prefix.'photocontest_config',
				$default_array,
				array(
					'%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d'
				)
			);
			$options_array = (array) $wpdb->get_row( $wpdb->prepare( $option_sql ));
}

if (count($options_array)==0)
{
	$options_array = $default_array;
}
//Use the flash polariod (true) of plain html (false)?
define('WP_USE_FLASH', 'true');

//Use Wordpress themes?
define('WP_USE_THEMES', $options_array['WP_USE_THEMES']);
	
//Print some debug 
define('DEBUG_FLAG',$options_array['DEBUG_FLAG']);

//Defines where to store the photos uploaded by users.
define('CONTESTS_PATH',$options_array['CONTESTS_PATH']);

//Defines which skin (kind of template) the plugin uses
define('CONTESTS_SKIN',$options_array['CONTESTS_SKIN']);

//Defines the default status when creating a new contest
define('DEFAULT_STATUS',$options_array['DEFAULT_STATUS']);

//Defines the default author (UID) when creating a new contest
define('DEFAULT_UID',$options_array['DEFAULT_UID']);

//Defines the default type when creating a new contest
//page: The plugin creates a page which handles a contest
//post: The plugin creates a post which handles a contest
define('DEFAULT_TYPE',$options_array['DEFAULT_TYPE']);

//Defines the default page/post name when creating a new contest
define('DEFAULT_PAGENAME',$options_array['DEFAULT_PAGENAME']);

//Defines the default page/post name when creating a new contest
define('DEFAULT_PARENT',$options_array['DEFAULT_PARENT']);

//Defines the default comments status when creating a new contest
//open: people can comment on a content/photo
//closed: people can't comment on a content/photo
define('DEFAULT_COMMENTS',$options_array['DEFAULT_COMMENTS']);

//Defines the way the votes method is shown
//star5: Star rating from 1-5
//star10: Star rating from 1-10
//hidden Hidden vote of 1
//option5: Option list from 1-5
//option10: Option list from 1-10
define('VOTING_METHOD',$options_array['VOTING_METHOD']);

//Defines the role that a user need when voting
define('ROLE_VOTING',$options_array['ROLE_VOTING']);

//Defines the role that a user need when uploading
define('ROLE_UPLOAD',$options_array['ROLE_UPLOAD']);

//Defines the height and width the pictures a generated.
//Note: if you are using paddings, margins or borders in your style
//distract this from your total size (I need 454 - 2px border - 10px
//padding, so the result = 442px
define('VIEWIMG_BOX',$options_array['VIEWIMG_BOX']);

//Defines if the uploaded pictures a shown of hidden by default.
define('VISIBLE_UPLOAD',$options_array['VISIBLE_UPLOAD']);

//Defines if the vote a shown of hidden by default.
define('VISIBLE_VOTING',$options_array['VISIBLE_VOTING']);

//Defines the number of picutes per page
define('N_PHOTO_X_PAGE',$options_array['N_PHOTO_X_PAGE']);

//True hides the captha when voting
define('SKIP_CAPTHA',$options_array['SKIP_CAPTHA']);

//True shows a link back to the contest after voting
define('REDIRECT_AFTER_VOTE',$options_array['REDIRECT_AFTER_VOTE']);

//Don't change this. This helps upgrading the plugin
define('PC_DB_VERSION','1.5.5');
define('PC_PL_VERSION','1.5.5');

### Set some debug vars
if (DEBUG_FLAG == 1)
{
	error_reporting(E_ALL);
	if (!ini_get("display_errors") || ini_get("display_errors")==0)
	{
		ini_set("display_errors", 1);
	} 
}
else
{
	//error_reporting(E_ALL);
	ini_set("display_errors", 0);
}

### Add the lib classes
require_once(dirname(__FILE__).'/lib/php_captcha.class.php');
require_once(dirname(__FILE__).'/lib/thumbnail.class.php');
require_once(dirname(__FILE__).'/lib/wp-photocontest.class.php');
require_once(dirname(__FILE__).'/lib/wp-photocontest.functions.php');
require_once(dirname(__FILE__).'/lib/wp-photocontest.widget.php');

?>