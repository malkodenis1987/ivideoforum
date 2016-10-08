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
|	- Edit PhotoContest	settings             	                    |
|	- wp-content/plugins/wp-photocontest/photocontest-settings.php  |
|																    |
+-------------------------------------------------------------------+
*/


### Check Whether User Can Manage PhotoContest
if(!current_user_can('manage_photocontest')) {
	die('Access Denied');
}

### Include the configfile
require_once(dirname(__FILE__).'/wp-photocontest-config.php');

### PhotoContest Manager
$base_name		= plugin_basename('wp-photocontest/photocontest-manager.php');
$base_name_add	= str_replace('manager','add',plugin_basename('wp-photocontest/photocontest-manager.php'));
$base_name_cfg	= str_replace('manager','settings',plugin_basename('wp-photocontest/photocontest-manager.php'));
$base_page 		= 'admin.php?page='.$base_name;
$base_page_add	= 'admin.php?page='.$base_name_add;
$base_page_cfg	= 'admin.php?page='.$base_name_cfg;

### Variables
$checkArray = 	array(
					"do"=>"",
					"WP_USE_THEMES"=>"",
					"DEBUG_FLAG"=>"",
					"CONTESTS_PATH"=>"",
					"CONTESTS_SKIN"=>"",
					"DEFAULT_STATUS"=>"",
					"user"=>"",
					"parent_id"=>"",
					"DEFAULT_PAGENAME"=>"",
					"DEFAULT_TYPE"=>"",
					"DEFAULT_COMMENTS"=>"",
					"VOTING_METHOD"=>"",
					"ROLE_VOTING"=>"",
					"ROLE_UPLOAD"=>"",		
					"VIEWIMG_BOX"=>"",
					"VISIBLE_UPLOAD"=>"",
					"VISIBLE_VOTING"=>"",					
					"N_PHOTO_X_PAGE"=>"",
					"SKIP_CAPTHA"=>"",
					"REDIRECT_AFTER_VOTE"=>"",
				);

$_REQUEST = array_merge($checkArray,$_REQUEST);
foreach($wp_roles->role_names as $role => $name) {
	$role_values[$role] = translate_with_context($name);
	$user_roles[] = $role;
}
$user_roles[] = '';
$action						= wppc_checkOptions($_REQUEST['do'],
													array(
															__('Save settings', 'wp-photocontest'),
															'fixpermissions',
															'forcedatabaseupgrade',
															'show_tables'													
													)
												);
$db_to_show						= wppc_checkOptions($_REQUEST['db'],
													array(
															'photocontest',
															'photocontest_admin',
															'photocontest_config',
															'photocontest_votes',															
													)
												);												
$WP_USE_THEMES 				= wppc_checkOptions($_REQUEST['WP_USE_THEMES'], array('true','false'), WP_USE_THEMES);
$DEBUG_FLAG 				= wppc_checkOptions($_REQUEST['DEBUG_FLAG'], array('0','1'),DEBUG_FLAG);
$CONTESTS_PATH 				= wppc_checkString($_REQUEST['CONTESTS_PATH'], CONTESTS_PATH);
$CONTESTS_SKIN 				= wppc_checkString($_REQUEST['CONTESTS_SKIN'], CONTESTS_SKIN);
$DEFAULT_STATUS 			= wppc_checkOptions($_REQUEST['DEFAULT_STATUS'], array('publish','pending','draft'),DEFAULT_STATUS);
$DEFAULT_UID 				= wppc_checkInteger($_REQUEST['user'], DEFAULT_UID);
$DEFAULT_PARENT				= wppc_checkInteger($_REQUEST['parent_id'], DEFAULT_PARENT);
$DEFAULT_PAGENAME 			= wppc_checkString($_REQUEST['DEFAULT_PAGENAME'], DEFAULT_PAGENAME);
$DEFAULT_TYPE 				= wppc_checkOptions($_REQUEST['DEFAULT_TYPE'], array('page','post'),DEFAULT_TYPE);
$DEFAULT_COMMENTS 			= wppc_checkOptions($_REQUEST['DEFAULT_COMMENTS'], array('open','closed'),DEFAULT_COMMENTS);
$VOTING_METHOD 				= wppc_checkOptions($_REQUEST['VOTING_METHOD'], array('star5','star10','option5','option10','hidden'),VOTING_METHOD);
$ROLE_VOTING 				= wppc_checkOptions($_REQUEST['ROLE_VOTING'], $user_roles,ROLE_VOTING);
$ROLE_UPLOAD 				= wppc_checkOptions($_REQUEST['ROLE_UPLOAD'], $user_roles,ROLE_UPLOAD);
$VIEWIMG_BOX 				= wppc_checkInteger($_REQUEST['VIEWIMG_BOX'], VIEWIMG_BOX);
$VISIBLE_UPLOAD 			= wppc_checkOptions($_REQUEST['VISIBLE_UPLOAD'], array('0','1'),VISIBLE_UPLOAD);
$VISIBLE_VOTING 			= wppc_checkOptions($_REQUEST['VISIBLE_VOTING'], array('0','1'),VISIBLE_VOTING);
$N_PHOTO_X_PAGE 			= wppc_checkInteger($_REQUEST['N_PHOTO_X_PAGE'], N_PHOTO_X_PAGE);
$SKIP_CAPTHA 				= wppc_checkOptions($_REQUEST['SKIP_CAPTHA'], array('0','1'),SKIP_CAPTHA);
$REDIRECT_AFTER_VOTE		= wppc_checkOptions($_REQUEST['REDIRECT_AFTER_VOTE'], array('0','1'),REDIRECT_AFTER_VOTE);

$EDIT_DATE 					= date('Y-m-d h:m:i');

### Add some vars
$show_form 		= true;


### Form Processing 
if(!empty($action)) {
	// Decide What To Do
	switch($action) {
	
		case "fixpermissions":
			$role = get_role('administrator');
	
			if(!$role->has_cap('manage_photocontest')) {
				$role->add_cap('manage_photocontest');
			}
			
			if(!$role->has_cap('manage_photocontests')) {
				$role->add_cap('manage_photocontests');
			}
			
			$text .= '<p style="color: green;">'.__('Succes saving PhotoContest fix.', 'wp-photocontest').'</p>';
			$text .= '<p style="color: green;"><a href="'.$base_page.'">'.__('Click here to continue', 'wp-photocontest').'</a></p>';
			$show_form = false;
		break;

		case "show_tables":
			$table_name = $wpdb->prefix . $db_to_show;
			$show_tables	= (array) $wpdb->get_results( "SHOW COLUMNS FROM ".$table_name);
			wppc_pr($show_tables, $action.":");
		break;				
	
		case "forcedatabaseupgrade":
			$table_name = $wpdb->prefix . "photocontest_votes";
			$getall	= (array) $wpdb->get_results( "ALTER TABLE `".$table_name."` ADD `voter_status` ENUM( 'publish', 'pending', 'draft' ) NOT NULL DEFAULT 'draft' AFTER `voter_email`");
			$myresult = photocontest_install('force');

			$text .= '<p style="color: green;">'.__('Please check (if available) the boxes above for errors.', 'wp-photocontest').'</p>';
			$text .= '<p style="color: green;"><a href="'.$_SERVER['PHP_SELF'].'?page='.plugin_basename(__FILE__).'">'.__('Click here to return to the settings page', 'wp-photocontest').'</a></p>';
			$show_form = false;
		break;		
		// Add PhotoContest
		case __('Save settings', 'wp-photocontest'):
			// Check if contest name is set
			$default_options = $wpdb->update
			(
				$wpdb->prefix.'photocontest_config',
				array( 
					'WP_USE_THEMES' => $WP_USE_THEMES,
					'DEBUG_FLAG' => $DEBUG_FLAG,
					'CONTESTS_PATH' => $CONTESTS_PATH,
					'CONTESTS_SKIN' => $CONTESTS_SKIN,
					'DEFAULT_STATUS' => $DEFAULT_STATUS,
					'DEFAULT_UID' => $DEFAULT_UID,
					'DEFAULT_PARENT' => $DEFAULT_PARENT,
					'DEFAULT_PAGENAME' => $DEFAULT_PAGENAME,
					'DEFAULT_TYPE' => $DEFAULT_TYPE,
					'DEFAULT_COMMENTS' => $DEFAULT_COMMENTS,
					'VOTING_METHOD' => $VOTING_METHOD,
					'ROLE_VOTING' => $ROLE_VOTING,
					'ROLE_UPLOAD' => $ROLE_UPLOAD,		
					'VIEWIMG_BOX' => $VIEWIMG_BOX,
					'VISIBLE_UPLOAD' => $VISIBLE_UPLOAD,
					'VISIBLE_VOTING' => $VISIBLE_VOTING,
					'N_PHOTO_X_PAGE' => $N_PHOTO_X_PAGE,
					'SKIP_CAPTHA' => $SKIP_CAPTHA,
					'REDIRECT_AFTER_VOTE' => $REDIRECT_AFTER_VOTE,
					'edit_date' => $EDIT_DATE,
				),
				array( 'option_id' => 1 )
			);

			if ($default_options)
			{
				if ($VIEWIMG_BOX != VIEWIMG_BOX)
				{
					$text .= '<p style="color: orange;">'.__('Note:', 'wp-photocontest').'</p>';
					$text .= '<p style="color: orange;">'.__('You changed the VIEWIMG_BOX value! Please manually resize and rename your already uploaded pictures!', 'wp-photocontest').'</p>';
					$text .= '<p style="color: orange;"><hr></p>';					
				}
				$text .= '<p style="color: green;">'.__('Succes saving PhotoContest options.', 'wp-photocontest').'</p>';
				$text .= '<p style="color: green;"><a href="'.$base_page.'">'.__('Click here to continue', 'wp-photocontest').'</a></p>';
				$show_form = false;
			}
			else
			{
				//TODO: Rollback of directory and page
				$text .= '<p style="color: red;">'.__('Error saving PhotoContest options.', 'wp-photocontest').'</p>';
				$text .= '<p style="color: red;">'.__('Can\'t insert into the database', 'wp-photocontest').'</p>';
				$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';
				$show_form = false;			
			}
			
			wppc_printDatabaseError();
			
		break;
		
		default:
			// We don't have a valid action. Just show the add-form
		break;	
	}
}	


photocontest_scripts_and_styles();
?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">
<div class="wrap">
<?php
### Add PhotoContest Form
if(!empty($text)) { 
	echo '<!-- Last Action --><div id="message" class="updated fade">'.stripslashes($text).'</div>';
}
?>

<?php
if ($show_form)
{
	$option_sql = "SELECT * FROM ".$wpdb->prefix."photocontest_config LIMIT 0,1";
	$options_array = (array) $wpdb->get_row( $wpdb->prepare( $option_sql ));
	?>
	<div id="icon-wp-photocontest" class="icon32"><br /></div>
	<h2><?php _e('Config settings', 'wp-photocontest'); ?></h2>
	<br />
	<div id="message" class="updated fade"><i>You do not have sufficient permissions to access this page</i>-error?<br />
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>&do=fixpermissions">Click here to fix it!</a>
	</div>
	<div id="message" class="updated fade"><i>Wanna force the upgrade of the database?</i><br />
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>&do=forcedatabaseupgrade">Force database upgrade!</a>
	</div>
	<div id="message" class="updated fade"><i>Show the structure of the database tables?</i><br />
		<?php
			$table_name = $wpdb->prefix . "photocontest%";
			$show_tables	= (array) $wpdb->get_results( "show tables like '$table_name'", ARRAY_A);
			foreach ($show_tables as $show_table_k=>$show_table_v)
			{
				$tmp_nme = array_values($show_table_v);
				$db_name = $tmp_nme[0];
				$db_link = str_replace($wpdb->prefix,'',$db_name);
				?>
					- <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>&do=show_tables&db=<?php echo $db_link;?>"><?php echo $db_name;?></a><br />
				<?php
			}
		?>		
	</div>	
	<br />	
	<!-- Contest info -->
	<h3><?php _e('Details', 'wp-photocontest'); ?></h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('WP_USE_THEMES', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$wpthemes_values = array('true'=>'True','false'=>'False');
				$selectstring = '<select name="WP_USE_THEMES">';
				foreach ($wpthemes_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['WP_USE_THEMES'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />			
				<?php _e('Use Wordpress themes?', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('DEBUG_FLAG', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$debug_values = array('1'=>'True','0'=>'False');
				$selectstring = '<select name="DEBUG_FLAG">';
				foreach ($debug_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['DEBUG_FLAG'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Print some debug ', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('CONTESTS_PATH', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input name="CONTESTS_PATH" type="text" width="100" value="<?php echo $options_array['CONTESTS_PATH'];?>">
				<br />
				<?php _e('Defines where to store the photos uploaded by users.', 'wp-photocontest');?> <?php _e('(Relative to the wp-photocontest folder)', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('CONTESTS_SKIN', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input name="CONTESTS_SKIN" type="text" width="100" value="<?php echo $options_array['CONTESTS_SKIN'];?>">
				<br />
				<?php _e('Defines which skin (kind of template) the plugin uses.', 'wp-photocontest');?> <?php _e('(Relative to the skins folder in the wp-photocontest folder)', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Status', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$status_values = array('publish'=>__('Publish','wp-photocontest'),'pending'=>__('Pending','wp-photocontest'),'draft'=>__('Draft','wp-photocontest'));
				$selectstring = '<select name="DEFAULT_STATUS">';
				foreach ($status_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['DEFAULT_STATUS'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Defines the default status when creating a new contest.', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Poster', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php wp_dropdown_users(array('selected'=>$options_array['DEFAULT_UID']));?>
				<br />
				<?php _e('Defines the default author (UID) when creating a new contest.', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Page type', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$type_values = array('page'=>__('Page','wp-photocontest'),'post'=>__('Post','wp-photocontest'));
				$selectstring = '<select name="DEFAULT_TYPE">';
				foreach ($type_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['DEFAULT_TYPE'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Defines the default type when creating a new contest.', 'wp-photocontest');?><br>
				<?php _e('page: The plugin creates a page which handles a contest.', 'wp-photocontest');?><br>
				<?php _e('post: The plugin creates a post which handles a contest.', 'wp-photocontest');?><br>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Page name', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input name="DEFAULT_PAGENAME" type="text" width="100" value="<?php $options_array['DEFAULT_PAGENAME'] ?>">
				<br />
				<?php _e('Defines the default page/post name when creating a new contest', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Parent page', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php wp_dropdown_pages(array('exclude_tree' => 0, 'selected' => $options_array['DEFAULT_PARENT'], 'name' => 'parent_id', 'show_option_none' => __('Main Page (no parent)','wp-photocontest'), 'sort_column'=> 'menu_order, post_title')); ?>
				<br />
				<?php _e('Defines the default parent when creating a new contest', 'wp-photocontest');?> <?php _e('subpage of page where post is saved on) ', 'wp-photocontest');?>
			</td>
		</tr>				
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Comments', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$comments_values = array('open'=>__('Open','wp-photocontest'),'closed'=>__('Closed','wp-photocontest'));
				$selectstring = '<select name="DEFAULT_COMMENTS">';
				foreach ($comments_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['DEFAULT_COMMENTS'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);					
				?>
				<br />
				<?php _e('Defines the default comments status when creating a new contest.', 'wp-photocontest');?><br>
				<?php _e('open: people can comment on a content/photo.', 'wp-photocontest');?><br>
				<?php _e('closed: people can\'t comment on a content/photo.', 'wp-photocontest');?><br>

			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('VOTING_METHOD', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$comments_values = array('star5'=>'5 stars','star10'=>'10 stars','option5'=>'5 option list','option10'=>'10 option list','hidden'=>'Hidden');
				$selectstring = '<select name="VOTING_METHOD">';
				foreach ($comments_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['VOTING_METHOD'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);					
				?>			
				<br />			
				<?php _e('Defines the way the votes method is shown', 'wp-photocontest');?>
				<?php _e('star5: Star rating from 1-5', 'wp-photocontest');?>
				<?php _e('star10: Star rating from 1-10', 'wp-photocontest');?>
				<?php _e('hidden: Hidden vote of 1', 'wp-photocontest');?>
				<?php _e('option5: Option list from 1-5', 'wp-photocontest');?>
				<?php _e('option10: Option list from 1-10', 'wp-photocontest');?>			
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('ROLE_VOTING', 'wp-photocontest') ?>:</th>
			<td width="80%">
			<?php
				$selectstring = '<select name="ROLE_VOTING">';
				if (empty($options_array['ROLE_VOTING']))
				{
					$selectstring .= '<option value="" selected="selected">' . __('-- No role --') . '</option>';
				}
				else
				{
					$selectstring .= '<option value="">' . __('-- No role --') . '</option>';
				}				
				foreach ($role_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['ROLE_VOTING'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}

				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Defines the role that is allowed voting.', 'wp-photocontest');?>
			</td>
		</tr>				

		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('ROLE_UPLOAD', 'wp-photocontest') ?>:</th>
			<td width="80%">
			<?php
				$selectstring = '<select name="ROLE_UPLOAD">';
				if (empty($options_array['ROLE_UPLOAD']))
				{
					$selectstring .= '<option value="" selected="selected">' . __('-- No role --') . '</option>';
				}
				else
				{
					$selectstring .= '<option value="">' . __('-- No role --') . '</option>';
				}				
				foreach ($role_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['ROLE_UPLOAD'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}

				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Defines the role that is allowed uploading.', 'wp-photocontest');?>
			</td>
		</tr>				
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('VIEWIMG_BOX', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input name="VIEWIMG_BOX" type="text" width="100" value="<?php echo $options_array['VIEWIMG_BOX'];?>">
				<br />
				<?php _e('Defines the height and width the pictures are generated.', 'wp-photocontest');?>
				<?php _e('Note: If you are using paddings, margins or borders in your style', 'wp-photocontest');?>
				<?php _e('distract this from your total size (I need 454 - 2px border - 10px', 'wp-photocontest');?>
				<?php _e('padding, so the result = 442px', 'wp-photocontest');?>				
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('VISIBLE_UPLOAD', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$visibleupload_values = array('1'=>'True','0'=>'False');
				$selectstring = '<select name="VISIBLE_UPLOAD">';
				foreach ($visibleupload_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['VISIBLE_UPLOAD'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Are uploaded pictures visible by default or hidden (moderate functionality)?', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('VISIBLE_VOTING', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$visiblevoting_values = array('1'=>'True','0'=>'False');
				$selectstring = '<select name="VISIBLE_VOTING">';
				foreach ($visiblevoting_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['VISIBLE_VOTING'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Are votes visible by default or hidden (mails an confirm mail to the user)?', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('N_PHOTO_X_PAGE', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input name="N_PHOTO_X_PAGE" type="text" width="100" value="<?php echo $options_array['N_PHOTO_X_PAGE']; ?>">
				<br />
				<?php _e('Defines the number of picutes per page', 'wp-photocontest');?>
			</td>
		</tr>		
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('SKIP_CAPTHA', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$skipcpatha_values = array('1'=>'True','0'=>'False');
				$selectstring = '<select name="SKIP_CAPTHA">';
				foreach ($skipcpatha_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['SKIP_CAPTHA'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Set to true if you do not want to use captha when voting.', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('REDIRECT_AFTER_VOTE', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$redirecturl_values = array('1'=>'True','0'=>'False');
				$selectstring = '<select name="REDIRECT_AFTER_VOTE">';
				foreach ($redirecturl_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($options_array['REDIRECT_AFTER_VOTE'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				<br />
				<?php _e('Set to true if you want to show a "Back to contest" link after voting.', 'wp-photocontest');?>
			</td>
		</tr>
				
	</table>
	
	<p style="text-align: center;"><input type="submit" name="do" value="<?php _e('Save settings', 'wp-photocontest'); ?>"  class="button" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-photocontest'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
<?php

}
?>
</div>
</form>