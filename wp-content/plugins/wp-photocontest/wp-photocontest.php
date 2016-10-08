<?php
/*
Plugin Name: WP-PhotoContest
Plugin URI: http://www.vanderstad.nl/wordpress/plugins/wp-photocontest
Description: Adds a photo contest to your post or page. Note: Only apply auto-upgrade when you don't care about losing your contests!! (and if you do: Backup you tables, 'CONTEST_FOLDER'- and 'skins'-directories!)
Version: 1.5.6
Author: Frank van der Stad
Author URI: http://www.vanderstad.nl/wordpress/
Text Domain: wp-photocontest
Year: 2010
Last Translator: Frank van der Stad <frank@vanderstad.nl>
Language Team: Frank van der Stad <frank@vanderstad.nl>
*/

/*  
    WP PhotoContest: Adds a photo contest to Wordpress
    Copyright (C) 2009-2010  Frank van der Stad

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
/*
+------------------------------------------------------------------+
|																   |
|	WordPress Plugin: WP-PhotoContest                              | 
|	Copyright (c) 2009-2010 Frank van der Stad	                   |
|																   |
|	File Written By:											   |
|	- Frank van der Stad										   |
|	- http://www.vanderstad.nl/wp-photocontest					   |
|																   |
|	File Information:											   |
|	- Add PhotoContest											   |
|	- wp-content/plugins/wp-photocontest/wp-photocontest.php	   |
|																   |
+------------------------------------------------------------------+
*/
### Include the configfile
require_once(dirname(__FILE__).'/wp-photocontest-config.php');

### Function: Load localization files
$plugin_dir	= basename(dirname(__FILE__));
add_action('init', 'wppc_photocontest_textdomain');

### Function: PhotoContest Installer hooks
register_activation_hook(__FILE__, 'photocontest_install', 1);
register_uninstall_hook(__FILE__, 'photocontest_deinstall');
//register_deactivation_hook(__FILE__, 'photocontest_deinstall');

### Function: PhotoContest Auto upgrade filters
add_filter('upgrader_pre_install', 'photocontest_upgrader_backup', 10, 2);
add_filter('upgrader_post_install', 'photocontest_upgrader_restore', 10, 2);

### Function: PhotoContest Administration Menu
add_action('admin_menu', 'photocontest_sub_menu');
add_action('admin_init', 'photocontest_scripts_and_styles');
if (function_exists('wp_photocontest_load_widget'))
	add_action('widgets_init', 'wp_photocontest_load_widget');


### START OF FUNCTIONS ###

### Function: Load localization files
function wppc_photocontest_textdomain() {
	load_plugin_textdomain('wp-photocontest', false, 'wp-photocontest/localizations');
}

### Function: Handle installation
function photocontest_install($method='') 
{
	global $wpdb;
	
	if ($method == 'force')
	{
		$photocontest_db_version = 1;
	}
	else
	{
		$photocontest_db_version = PC_DB_VERSION;
	}
	
	$table_name = $wpdb->prefix . "photocontest_admin";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
		$structure = "CREATE TABLE ".$table_name." (
		contest_id int(10) unsigned NOT NULL AUTO_INCREMENT,
		post_id bigint unsigned NOT NULL,
		start_date datetime NOT NULL,
		end_date datetime NOT NULL,
		upload_date datetime NOT NULL,
		contest_path varchar(255) NOT NULL,
		contest_name varchar(255),
		intro_text text,
		enter_text text,
		num_photo int unsigned default 0,
		max_photos int unsigned default 1,
		UNIQUE KEY start_date (start_date,end_date,contest_path),
		PRIMARY KEY  (contest_id)
		);";
		
		if (!dbDelta($structure))
		{
			die('Error: Table '.$table_name.' not added!');
		}
		
		unset($table_name);
		unset($structure);
		
		add_option( "PC_DB_VERSION", PC_DB_VERSION );
	}	
	
	$table_name = $wpdb->prefix . "photocontest";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
		$structure = "CREATE TABLE ".$table_name." (
		contest_id int(10) unsigned default NULL,
		wp_uid bigint(20) unsigned default NULL,
		wp_email varchar(255) NOT NULL,
		img_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		img_path varchar(255) NOT NULL,
		img_name varchar(255) default NULL,
		img_title varchar(500) default NULL,
		img_comment text,
		sum_votes int unsigned default '0',
		img_view_count bigint(20) unsigned default '0',
		insert_time datetime default NULL,
		visibile tinyint default 1,
		UNIQUE KEY contest_id (contest_id,wp_uid,img_id),
		PRIMARY KEY  img_id (img_id)
		);";
		
		if (!dbDelta($structure))
		{
			die('Error: Table '.$table_name.' not added!');
		}
		
		unset($table_name);
		unset($structure);
		
		add_option( "PC_DB_VERSION", PC_DB_VERSION );
	}	
			
	$table_name = $wpdb->prefix . "photocontest_votes";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
		$structure = "CREATE TABLE ".$table_name." (
		img_id bigint(20) unsigned NOT NULL,
		voter_id varchar(36) NOT NULL,
		vote_time timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		vote smallint unsigned CHECK (vote <= 10 and vote >= 0),
		captcha_text varchar(5) NOT NULL,
		voter_email varchar(255) NOT NULL,
		voter_status enum('publish','pending','draft') NOT NULL default 'draft',
		UNIQUE KEY img_id (img_id, voter_id)
		);";
		
		if (!dbDelta($structure))
		{
			die('Error: Table '.$table_name.' not added!');
		}
		
		unset($table_name);
		unset($structure);
		
		add_option( "PC_DB_VERSION", PC_DB_VERSION );
	}	
			
	$table_name = $wpdb->prefix . "photocontest_config";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
		$structure = "CREATE TABLE ".$table_name." (
		option_id int(5) unsigned NOT NULL,
		WP_USE_THEMES enum('true','false') NOT NULL DEFAULT 'true', 
		DEBUG_FLAG enum('0','1') NOT NULL default '0',
		CONTESTS_PATH varchar(255) NOT NULL default 'contests_holder',
		CONTESTS_SKIN varchar(255) NOT NULL default 'aqua',
		DEFAULT_STATUS enum('publish','pending','draft') NOT NULL default 'publish',
		DEFAULT_UID int(5) NOT NULL default '1',
		DEFAULT_PAGENAME varchar(255) NOT NULL,
		DEFAULT_TYPE enum('page','post') NOT NULL default 'page',
		DEFAULT_PARENT int(5) NOT NULL default '0',		
		DEFAULT_COMMENTS enum('open','closed') NOT NULL default 'closed',
		VOTING_METHOD enum('star5','star10','option5','option10','hidden') NOT NULL default 'star5',
		ROLE_VOTING varchar(255),
		ROLE_UPLOAD varchar(255) default 'subscriber',
		VIEWIMG_BOX int(5) NOT NULL default '442',
		VISIBLE_UPLOAD int(5) NOT NULL default '1',
		VISIBLE_VOTING int(5) NOT NULL default '1',
		N_PHOTO_X_PAGE int(5) NOT NULL default '9',
		SKIP_CAPTHA int(5) NOT NULL default '1',
		REDIRECT_AFTER_VOTE int(5) NOT NULL default '0',
		edit_date timestamp NOT NULL default CURRENT_TIMESTAMP,		
		UNIQUE KEY options (option_id, WP_USE_THEMES),
		PRIMARY KEY  (option_id)  
		);";
		
		if (!dbDelta($structure))
		{
			die('Error: Table '.$table_name.' not added!');
		}
		
		unset($table_name);
		unset($structure);
		
		add_option( "PC_DB_VERSION", PC_DB_VERSION );
	}		
		
	// Upgrade table code
	$installed_ver = get_option( "PC_DB_VERSION" );
	
	if( $photocontest_db_version != $installed_ver ) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
		$table_name = $wpdb->prefix . "photocontest_admin";
		$structure = "CREATE TABLE ".$table_name." (
		contest_id int(10) unsigned NOT NULL AUTO_INCREMENT,
		post_id bigint unsigned NOT NULL,
		start_date datetime NOT NULL,
		end_date datetime NOT NULL,
		upload_date datetime NOT NULL,
		contest_path varchar(255) NOT NULL,
		contest_name varchar(255),
		intro_text text,
		enter_text text,
		num_photo int unsigned default 0,
		max_photos int unsigned default 1,
		UNIQUE KEY start_date (start_date,end_date,contest_path),
		PRIMARY KEY  (contest_id)
		);";
		if (!dbDelta($structure))
		{
			die('Error: Table '.$table_name.' not updated!');
		}
		unset($table_name);
		unset($structure);

		$table_name = $wpdb->prefix . "photocontest";
		$structure = "CREATE TABLE ".$table_name." (
		contest_id int(10) unsigned default NULL,
		wp_uid bigint(20) unsigned default NULL,
		wp_email varchar(255) NOT NULL,
		img_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		img_path varchar(255) NOT NULL,
		img_name varchar(255) default NULL,
		img_title varchar(500) default NULL,
		img_comment text,
		sum_votes int unsigned default '0',
		img_view_count bigint(20) unsigned default '0',
		insert_time datetime default NULL,
		visibile tinyint default 1,
		UNIQUE KEY contest_id (contest_id,wp_uid,img_id),
		PRIMARY KEY  img_id (img_id)
		);";
		if (!dbDelta($structure))
		{
			die('Error: Table '.$table_name.' not updated!');
		}
		unset($table_name);
		unset($structure);

		$table_name = $wpdb->prefix . "photocontest_votes";
		$structure = "CREATE TABLE ".$table_name." (	
		img_id bigint(20) unsigned NOT NULL,
		voter_id varchar(36) NOT NULL,
		vote_time timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		vote smallint unsigned CHECK (vote <= 10 and vote >= 0),
		captcha_text varchar(5) NOT NULL,
		voter_email varchar(255) NOT NULL,
		UNIQUE KEY img_id (img_id, voter_id)
		);";
		if (!dbDelta($structure))
		{
			die('Error: Table '.$table_name.' not updated!');
		}
		unset($table_name);
		unset($structure);

		$table_name = $wpdb->prefix . "photocontest_config";		
		$structure = "CREATE TABLE ".$table_name." (
		option_id int(5) unsigned NOT NULL,
		WP_USE_THEMES enum('true','false') NOT NULL DEFAULT 'true', 
		DEBUG_FLAG enum('0','1') NOT NULL default '0',
		CONTESTS_PATH varchar(255) NOT NULL default 'contests_holder',
		CONTESTS_SKIN varchar(255) NOT NULL default 'aqua',
		DEFAULT_STATUS enum('publish','pending','draft') NOT NULL default 'publish',
		DEFAULT_UID int(5) NOT NULL default '1',
		DEFAULT_PAGENAME varchar(255) NOT NULL,
		DEFAULT_TYPE enum('page','post') NOT NULL default 'page',
		DEFAULT_PARENT int(5) NOT NULL default '0',		
		DEFAULT_COMMENTS enum('open','closed') NOT NULL default 'closed',
		VOTING_METHOD enum('star5','star10','option5','option10','hidden') NOT NULL default 'star5',
		ROLE_VOTING varchar(255),
		ROLE_UPLOAD varchar(255) default 'subscriber',
		VIEWIMG_BOX int(5) NOT NULL default '442',
		VISIBLE_UPLOAD int(5) NOT NULL default '1',
		VISIBLE_VOTING int(5) NOT NULL default '1',
		N_PHOTO_X_PAGE int(5) NOT NULL default '9',
		SKIP_CAPTHA int(5) NOT NULL default '1',
		REDIRECT_AFTER_VOTE int(5) NOT NULL default '0',
		edit_date timestamp NOT NULL default CURRENT_TIMESTAMP,		
		UNIQUE KEY options (option_id, WP_USE_THEMES),
		PRIMARY KEY  (option_id)  
		);";
		if (!dbDelta($structure))
		{
			die('Error: Table '.$table_name.' not updated!');
		}
		unset($table_name);
		unset($structure);
	
		update_option( "PC_DB_VERSION", PC_DB_VERSION );
	}
	
	$role = get_role('administrator');

	if(!$role->has_cap('manage_photocontest')) {
		$role->add_cap('manage_photocontest');
	}
	
	if(!$role->has_cap('manage_photocontests')) {
		$role->add_cap('manage_photocontests');
	}	
	
	add_action( 'admin_notices', 'wppc_noticebox',1,1);
	
	//Check if the restore is successfull:
	if (@is_dir(WP_CONTENT_DIR   . '/upgrade/wp-photocontest/_CONTESTS_PATH'))
	{
		if (!@rename(WP_CONTENT_DIR   . '/upgrade/wp-photocontest/_CONTESTS_PATH',WP_PLUGIN_DIR . '/wp-photocontest/' . CONTESTS_PATH))
		{
				do_action( 'admin_notices', 'Error: Restore of CONTESTS_PATH failed!<br>Please copy '.WP_CONTENT_DIR   . '/upgrade/wp-photocontest/_CONTESTS_PATH to '.WP_PLUGIN_DIR . '/wp-photocontest/' . CONTESTS_PATH.'!');
		}
	}
	
	//Check if the content path exists:
	if (!@is_dir(WP_PLUGIN_DIR . '/wp-photocontest/' . CONTESTS_PATH))
	{
		if (!@mkdir(WP_PLUGIN_DIR . '/wp-photocontest/' . CONTESTS_PATH, 0755))
		{
			do_action( 'admin_notices', 'Error: Contest directory doesn\'t exist!<br>Please create <strong>'.WP_PLUGIN_DIR . '/wp-photocontest/' . CONTESTS_PATH.'</strong> ');
		}
	}
	
	//Check if the plugin path is writeable:
	$check_file1 = WP_PLUGIN_DIR . '/wp-photocontest/permission_check.txt';
	if (file_exists($check_file1))
	{
		unlink($check_file1);
	}
	if (!@touch($check_file1))
	{
		if (!@chmod(WP_PLUGIN_DIR . '/wp-photocontest', 0755))
		{
			do_action( 'admin_notices', 'Error: Plugin directory isn\'t writeable<br>Please change permissions on : <strong>'.WP_PLUGIN_DIR . '/wp-photocontest</strong> ');
		}
	}	
	
	//Check if the contest path is writeable:
	$check_file2 = WP_PLUGIN_DIR . '/wp-photocontest/' . CONTESTS_PATH . '/permission_check.txt';	
	if (file_exists($check_file2))
	{
		unlink($check_file2);
	}	
	if (!@touch($check_file2))
	{
		if (!@chmod(WP_PLUGIN_DIR . '/wp-photocontest/' . CONTESTS_PATH, 0755))
		{
			do_action( 'admin_notices', 'Error: Contest directory isn\'t writeable<br>Please change permissions on : <strong>'.WP_PLUGIN_DIR . '/wp-photocontest/' . CONTESTS_PATH.'</strong> ');
		}
	}

}

### Function: print message reminding fix the checks
function wppc_noticebox($string){
  echo '<div class="error fade"><p><strong>'.$string.'</strong></p></div>';
}

### Function: Handle de-installation
// settings get deleted when plugin is deleted from admin plugins page
function photocontest_deinstall() {
	global $wpdb, $wp_version;
	
	$wppc_table_pl = $wpdb->prefix . 'photocontest';
	$wppc_table_ad = $wpdb->prefix . 'photocontest_admin';
	$wppc_table_co = $wpdb->prefix . 'photocontest_config';
	$wppc_table_vo = $wpdb->prefix . 'photocontest_votes';	
	
	//$wpdb->query("DROP TABLE IF EXISTS `". $wppc_table_pl . "`");
	//$wpdb->query("DROP TABLE IF EXISTS `". $wppc_table_ad . "`");
	//$wpdb->query("DROP TABLE IF EXISTS `". $wppc_table_co . "`");
	//$wpdb->query("DROP TABLE IF EXISTS `". $wppc_table_vo . "`");
	
	//delete_option('PC_DB_VERSION');
	//delete_option('PC_PL_VERSION');
	//delete_option('widget_wp-photocontest-widget');
	//delete_option('wppc_redirect_to');
	
} // end function photocontest_deinstall

### Function: Handle backup when upgrading
function photocontest_upgrader_backup() {
	$upgrade_dir 		= WP_CONTENT_DIR   . '/upgrade';
	$upgrade_plugin_dir = WP_CONTENT_DIR   . '/upgrade/wp-photocontest_'.date('Ymd');
	$upgrade_skins_dir 	= WP_CONTENT_DIR   . '/upgrade/wp-photocontest_'.date('Ymd').'/skins';
	$upgrade_upload_dir = WP_CONTENT_DIR   . '/upgrade/wp-photocontest_'.date('Ymd').'/'.CONTESTS_PATH;
	$plugin_dir 		= WP_PLUGIN_DIR    . '/wp-photocontest';
	$plugin_plugin_dir  = WP_PLUGIN_DIR    . '/wp-photocontest';
	$plugin_skins_dir 	= WP_PLUGIN_DIR    . '/wp-photocontest/skins';
	$plugin_upload_dir  = WP_PLUGIN_DIR    . '/wp-photocontest/'.CONTESTS_PATH;	
	
	if (!@is_dir($upgrade_dir))
	{
		if (!@mkdir($upgrade_dir, 0755))
		{
			
			do_action( 'admin_upgrade_backup_notices', 'Error: Upgrade directory doesn\'t exist!<br>Please create: <strong>'. $upgrade_dir   . '</strong>');
			die('Upgrade error');
		}
	}
	
	if (!@is_dir($upgrade_plugin_dir))
	{
		if (!@mkdir($upgrade_plugin_dir, 0755))
		{
			do_action( 'admin_upgrade_backup_notices', 'Error: Upgrade Contest directory doesn\'t exist!<br>Please create: <strong>'.$upgrade_plugin_dir.'</strong>');
			die('Upgrade error');
		}
	}	
	
	if (@is_dir($plugin_upload_dir))
	{
		if (!@rename($plugin_upload_dir, $upgrade_upload_dir))
		{
				do_action( 'admin_upgrade_backup_notices', 'Error: Backup of CONTESTS_PATH failed!<br>Please backup your CONTESTS_PATH (<strong>'.$plugin_upload_dir.'</strong>)');
				die('Upgrade error');
		}
	}
	
	if (@is_dir($plugin_skins_dir))
	{
		if (!@rename($plugin_skins_dir, $upgrade_skins_dir))
		{
				do_action( 'admin_upgrade_backup_notices', 'Error: Backup of SKINS failed!<br>Please backup your SKINS directory (<strong>'.$plugin_skins_dir.'</strong>)');
				die('Upgrade error');
		}
	}	

} // end function photocontest_upgrader_backup

### Function: Handle restore when upgrading
function photocontest_upgrader_restore() {
	$upgrade_dir 		= WP_CONTENT_DIR   . '/upgrade';
	$upgrade_plugin_dir = WP_CONTENT_DIR   . '/upgrade/wp-photocontest_'.date('Ymd');
	$upgrade_skins_dir 	= WP_CONTENT_DIR   . '/upgrade/wp-photocontest_'.date('Ymd').'/skins';
	$upgrade_upload_dir = WP_CONTENT_DIR   . '/upgrade/wp-photocontest_'.date('Ymd').'/'.CONTESTS_PATH;
	$plugin_dir 		= WP_PLUGIN_DIR    . '/wp-photocontest';
	$plugin_plugin_dir  = WP_PLUGIN_DIR    . '/wp-photocontest';
	$plugin_skins_dir 	= WP_PLUGIN_DIR    . '/wp-photocontest/skins';
	$plugin_upload_dir  = WP_PLUGIN_DIR    . '/wp-photocontest/'.CONTESTS_PATH;	
	
	add_action( 'admin_upgrade_backup_notices', 'wppc_noticebox',1,1);
	if (@is_dir($upgrade_upload_dir))
	{
		if (!@rename($upgrade_upload_dir, $plugin_upload_dir))
		{
				$error_text = 'Error: Restore of CONTESTS_PATH failed!<br>Please restore your CONTESTS_PATH (<strong>'.$upgrade_upload_dir.'</strong>)';
				do_action( 'admin_upgrade_backup_notices', $error_text);
		}
		@chmod($plugin_upload_dir, 0755);
		@rmdir($upgrade_upload_dir);		
	}
	
	if (@is_dir($upgrade_skins_dir))
	{
		if (!@rename($upgrade_skins_dir, $plugin_skins_dir))
		{
				$error_text = 'Error: Restore of SKINS failed!<br>Please restore your SKINS directory (<strong>'.$upgrade_skins_dir.'</strong>)';
				do_action( 'admin_upgrade_backup_notices', $error_text);
		}
		@chmod($plugin_skins_dir, 0755);
		@rmdir($upgrade_skins_dir);
	}	

} // end function photocontest_upgrader_restore

if ( !function_exists('wp_new_user_notification') ) {

	function wp_new_user_notification($user_id, $plaintext_pass = '') {
		$user = new WP_User($user_id);
	
		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);
	
		$message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";
	
		@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);
	
		if ( empty($plaintext_pass) )
			return;
	
		$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
		$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
		$message .= get_option('wppc_redirect_to') . "\r\n";
	
		if (wp_mail($user_email, sprintf(__('[%s] Your username and password'), get_option('blogname')), $message))
		{
		
			if (get_option('wppc_redirect_to'))
			{
				$redirect_to = get_option('wppc_redirect_to');
				add_option('wppc_redirect_to',false);
				header("Location:".$redirect_to);
				exit();
			}
		}
		
	}
}
?>
