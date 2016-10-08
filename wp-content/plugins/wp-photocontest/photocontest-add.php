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
|	- Add PhotoContest                      	                    |
|	- wp-content/plugins/wp-photocontest/photocontest-add.php       |
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
					"photocontest_new"=>"",
					"photocontest_init_day"=>"",
					"photocontest_end_day"=>"",
					"photocontest_end_upload"=>"",
					"photocontest_contest_name"=>"",
					"photocontest_intro_text"=>"",
					"photocontest_enter_text"=>"",
					"photocontest_max_photos"=>"",
					"DEFAULT_STATUS"=>"",
					"user"=>"",
					"parent_id"=>"",
					"DEFAULT_PAGENAME"=>"",
					"DEFAULT_TYPE"=>"",
					"DEFAULT_COMMENTS"=>""
				);

$_REQUEST = array_merge($checkArray,$_REQUEST);

$action						= wppc_checkOptions($_REQUEST['do'],array(__('Add PhotoContest', 'wp-photocontest')));
$photocontest_new 			= wppc_checkInteger($_REQUEST['photocontest_new']);
$photocontest_init_day 		= wppc_checkValidDate($_REQUEST['photocontest_init_day'],date('Y-m-d'));
$photocontest_end_day 		= wppc_checkValidDate($_REQUEST['photocontest_end_day'],date('Y-m-d', strtotime('+1 week')));
$photocontest_end_upload	= wppc_checkValidDate($_REQUEST['photocontest_end_upload'],date('Y-m-d', strtotime('+1 week')));
$photocontest_contest_name	= wppc_checkString($_REQUEST['photocontest_contest_name']);
$photocontest_intro_text	= wppc_checkString	(
												$_REQUEST['photocontest_intro_text'],
													array(
														'a' => 
															array(
																'href' => array(),
																'title' => array()
															),
														'br' => array(),
														'em' => array(),
														'strong' => array(),
														'ul' => array(),
														'li' => array(),
														'ol' => array(),
														'img' => array()																												
													)
												);
$photocontest_enter_text	= wppc_checkString	(
												$_REQUEST['photocontest_enter_text'],
													array(
														'a' => 
															array(
																'href' => array(),
																'title' => array()
															),
														'br' => array(),
														'em' => array(),
														'strong' => array(),
														'ul' => array(),
														'li' => array(),
														'ol' => array(),
														'img' => 
															array(
																'src' => array(),
																'align' => array(),
																'hspace' => array(),
																'vspace' => array(),																
																'alt' => array()
															)																												
													)
												);
$photocontest_max_photos	= wppc_checkInteger($_REQUEST['photocontest_max_photos'],1);

$DEFAULT_STATUS 			= wppc_checkOptions($_REQUEST['DEFAULT_STATUS'], array('publish','pending','draft'),DEFAULT_STATUS);
$DEFAULT_UID 				= wppc_checkInteger($_REQUEST['user'], DEFAULT_UID);
$DEFAULT_PARENT				= wppc_checkInteger($_REQUEST['parent_id'], DEFAULT_PARENT);
$DEFAULT_PAGENAME 			= wppc_checkString($_REQUEST['DEFAULT_PAGENAME'], DEFAULT_PAGENAME);
$DEFAULT_TYPE 				= wppc_checkOptions($_REQUEST['DEFAULT_TYPE'], array('page','post'),DEFAULT_TYPE);
$DEFAULT_COMMENTS 			= wppc_checkOptions($_REQUEST['DEFAULT_COMMENTS'], array('open','closed'),DEFAULT_COMMENTS);

### Add some vars
$show_form 		= true;


### Form Processing 
if(!empty($action)) {
	// Decide What To Do
	switch($action) {
		// Add PhotoContest
		case __('Add PhotoContest', 'wp-photocontest'):
			// Check if contest name is set
			if (!$photocontest_contest_name)
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('You have not provided a contest name', 'wp-photocontest').'</p>';
				break;	
			}
					
			// Check if the dates are entered correctly
			if (!$photocontest_init_day) 
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Invalid startdate.', 'wp-photocontest').'</p>';
				break;		
			}
			// Check if the dates are entered correctly
			if (!$photocontest_end_day) 
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Invalid enddate.', 'wp-photocontest').'</p>';
				break;		
			}
			// Check if the dates are entered correctly
			if (!$photocontest_end_upload) 
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Invalid end upload date.', 'wp-photocontest').'</p>';
				break;		
			}						
			
			$photocontest_index_path = wppc_strNormalizeName($photocontest_contest_name);
						
			// Check if directory doesn't conflict with existing dirs/files
			if (! wppc_isRightDir($photocontest_index_path))
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in checking PhotoContest directory \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('This name is invalid, choose an other name', 'wp-photocontest').'</p>';
				break;	
			}
			
			// Check if directory exists
			if (file_exists(dirname(__FILE__)."/".CONTESTS_PATH."/".$photocontest_index_path))
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in checking PhotoContest directory \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('There is already a contest with this name, choose an other name', 'wp-photocontest').'</p>';
				break;		
			}			
			
			// Create upload directory
			$photo_directory=dirname(__FILE__)."/".CONTESTS_PATH."/".$photocontest_index_path;
			umask('000');
			if (!$mkdir_result = mkdir($photo_directory))
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in checking PhotoContest directory \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Can\'t create the directory:', 'wp-photocontest').' '.$photo_directory.'</p>';
				$text .= '<p style="color: red;"><pre>'.$mkdir_result.'</pre></p>';				
				break;		
			}				
						
			// Create a new page
			// create post object
			class wm_mypost {
				var $post_title;
				var $post_content;
				var $post_status;
				var $post_author;    		/* author user id (optional) */
				var $post_name;				/* slug (optional) */
				var $post_type;				/* 'page' or 'post' (optional, defaults to 'post') */
				var $comment_status;		/* open or closed for commenting (optional) */
			}
			// initialize post object
			$wm_mypost = new wm_mypost();
			
			// fill object
			$photocontestPage = new photoContest();
			$photocontestPage->setInitHtml($photocontest_intro_text);
			
			$wm_mypost->post_title		= $photocontest_contest_name;
			$wm_mypost->post_content	= $photocontestPage->getInitHtml();
		
			$wm_mypost->post_status		= $DEFAULT_STATUS;
			$wm_mypost->post_author		= $DEFAULT_UID;
			$wm_mypost->post_name 		= $DEFAULT_PAGENAME;			
			$wm_mypost->post_type		= $DEFAULT_TYPE;
			$wm_mypost->post_parent		= $DEFAULT_PARENT;			
			$wm_mypost->comment_status	= $DEFAULT_COMMENTS;
			
			// feed object to wp_insert_post
			$photocontestPostID = wp_insert_post($wm_mypost);

			// Add an link to Enter the contest
			$wm_mypost->post_content .= "<BR /><p><a href=\"".get_option('siteurl')."/wp-content/plugins/wp-photocontest/view.php?post_id=".$photocontestPostID."\">";
			$wm_mypost->post_content .= __('Enter the Contest', 'wp-photocontest');
			$wm_mypost->post_content .= "</a></p>";

			//Save the ID			
			$wm_mypost->ID	= $photocontestPostID;

			//Update the post
			wp_update_post($wm_mypost);
			
			//Enter the data into the photocontest_admin table		
			//$wpdb->show_errors();
			$new_contest_id = $wpdb->insert
			(
				$wpdb->prefix.'photocontest_admin',
				array(
					'post_id' => $photocontestPostID,
					'start_date' => $photocontest_init_day,
					'end_date' => $photocontest_end_day,
					'upload_date' => $photocontest_end_upload,
					'contest_path' => $photocontest_index_path,
					'contest_name' => $photocontest_contest_name,
					'intro_text' => $photocontest_intro_text,
					'enter_text' => $photocontest_enter_text,
					'num_photo' => 0,
					'max_photos' => $photocontest_max_photos
				), 
				array(
					'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d'
				)
			);		

			if ($new_contest_id)
			{
				$text .= '<p style="color: green;">'.sprintf(__('Succes saving PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: green;"><a href="'.$base_page.'">'.__('Click here to continue', 'wp-photocontest').'</a></p>';
				$show_form = false;
			}
			else
			{
				//TODO: Rollback of directory and page
				$text .= '<p style="color: red;">'.sprintf(__('Error saving PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Can\'t insert into the database', 'wp-photocontest').'</p>';
				$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';
				$show_form = false;			
			}
			
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
?>
	<div id="icon-wp-photocontest" class="icon32"><br /></div>
	<h2><?php _e('Add Contest', 'wp-photocontest'); ?></h2>
	<!-- Contest info -->
	<h3><?php _e('Details', 'wp-photocontest'); ?></h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Name', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input name="photocontest_contest_name" type="text" width="100" value="<?php echo $photocontest_contest_name;?>">
			  	<br />
				<?php _e('Enter the title you give to this contest.', 'wp-photocontest');?>
				<?php _e('(this field will also be the path of the photos for the competition).', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Photos by user', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input name="photocontest_max_photos" type="text" width="10" value="<?php echo $photocontest_max_photos;?>">
				<br /><?php _e('Enter the maximum number of photos that a user can upload.', 'wp-photocontest');?>
				<br/><?php _e('If you leave this empty, it will set 1 by default.', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Introduction text', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<textarea rows="6" cols="50" name="photocontest_intro_text"><?php echo $photocontest_intro_text;?></textarea>
				<br/>
				<?php _e('Enter the initial content of the page as an introduction.', 'wp-photocontest') ?><br />
				<em>
				<?php _e('To format the content in this textarea field, enter HTML tags.', 'wp-photocontest');?><br />
				<?php _e('Don\'t use the following tags', 'wp-photocontest');?>: <strong>HTML, HEAD and BODY</strong>
				</em>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Upload text', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<textarea rows="6" cols="50" name="photocontest_enter_text"><?php echo $photocontest_enter_text;?></textarea>
				<br/>
				<?php _e('Enter the content of the page where people upload photos.', 'wp-photocontest') ?><br />
				<?php _e('Use this text for contest rules, disclaimers, etc.', 'wp-photocontest') ?><br />
				<em>
				<?php _e('To format the content in this textarea field, enter HTML tags.', 'wp-photocontest');?><br />
				<?php _e('Don\'t use the following tags', 'wp-photocontest');?>: <strong>HTML, HEAD and BODY</strong>
				</em>
			</td>
		</tr>				
	</table>
	<!-- Contest dates -->
	<h3><?php _e('Dates', 'wp-photocontest'); ?></h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Startdate', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input type="text" name="photocontest_init_day" id="photocontest_init_day" size="30" value="<?php echo $photocontest_init_day;?>"><input type="reset" value=" ... " onclick="return showCalendar('photocontest_init_day', '%Y-%m-%d');">
				<br /><?php _e('Choose the date on which you want the contest to begin.', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Enddate', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input type="text" name="photocontest_end_day" id="photocontest_end_day" size="30" value="<?php echo $photocontest_end_day;?>"><input type="reset" value=" ... " onclick="return showCalendar('photocontest_end_day', '%Y-%m-%d');">
				<br /><?php _e('Enter the date for the contest.', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('End upload date', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<input type="text" name="photocontest_end_upload" id="photocontest_end_upload" size="30" value="<?php echo $photocontest_end_upload;?>"><input type="reset" value=" ... " onclick="return showCalendar('photocontest_end_upload', '%Y-%m-%d');">
				<br /><?php _e('Enter the latest date allowed to upload photos.', 'wp-photocontest');?>
			</td>
		</tr>		
	</table>
	<p style="text-align: center;"><input type="submit" name="do" value="<?php _e('Add PhotoContest', 'wp-photocontest'); ?>"  class="button" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-photocontest'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
	
	<!-- Pages details -->
	<h3><?php _e('Optional page details', 'wp-photocontest');?> </h3>
	<table class="form-table">
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Status', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php
				$status_values = array('publish'=>__('Publish','wp-photocontest'),'pending'=>__('Pending','wp-photocontest'),'draft'=>__('Draft','wp-photocontest'));
				$selectstring = '<select name="DEFAULT_STATUS">';
				foreach ($status_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($DEFAULT_STATUS == $status_k)
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
				<?php wp_dropdown_users(array('selected'=>$DEFAULT_UID));?>
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
					if ($DEFAULT_TYPE == $status_k)
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
				<input name="DEFAULT_PAGENAME" type="text" width="100" value="<?php $DEFAULT_PAGENAME ?>">
				<br />
				<?php _e('Defines the default page/post name when creating a new contest', 'wp-photocontest');?>
			</td>
		</tr>
		<tr>
			<th width="20%" scope="row" valign="top"><?php _e('Parent page', 'wp-photocontest') ?>:</th>
			<td width="80%">
				<?php wp_dropdown_pages(array('exclude_tree' => 0, 'selected' => $DEFAULT_PARENT, 'name' => 'parent_id', 'show_option_none' => __('Main Page (no parent)','wp-photocontest'), 'sort_column'=> 'menu_order, post_title')); ?>
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
					if ($DEFAULT_COMMENTS == $status_k)
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
	</table>
	<p style="text-align: center;"><input type="submit" name="do" value="<?php _e('Add PhotoContest', 'wp-photocontest'); ?>"  class="button" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-photocontest'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
<?php
}
?>
</div>
</form>