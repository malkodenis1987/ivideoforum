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
|	- Handles the admin panel actions        	                    |
|	- wp-content/plugins/wp-photocontest/photocontest-manager.php   |
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
					"mode"=>"",
					"contest_id"=>"",
					"post_id"=>"",
					"p"=>"",
					"img_id"=>"",
					"photocontest_init_day"=>"",
					"photocontest_end_day"=>"",
					"photocontest_upload_day"=>"",
					"photocontest_intro_text"=>"",
					"photocontest_enter_text"=>"",		
					"photocontest_contest_name"=>"",										
					"visible"=>"",
					"v"=>"",
					"vote_id"=>""
				);
$_REQUEST = array_merge($checkArray,$_REQUEST);

$mode						= wppc_checkOptions($_REQUEST['mode'],
									array
									(
										__('Edit Introduction text', 'wp-photocontest'),
										__('Edit Upload text', 'wp-photocontest'),
										__('Edit PhotoContest', 'wp-photocontest'),
										__('Edit Startdate', 'wp-photocontest'),
										__('Edit Enddate', 'wp-photocontest'),
										__('Edit Uploaddate', 'wp-photocontest'),
										__('Set to publish', 'wp-photocontest'),
										__('Set to pending', 'wp-photocontest'),
										__('Set to drafts', 'wp-photocontest'),																														
										'show_tables',
										'delete',
										'view_contest',
										'view_votes',
										'refresh',
										'changeto',
										'changefrom',
										'changeup',
										'changeenter',
										'changeintro'
									)
								);
$contest_id 				= wppc_checkInteger($_REQUEST['contest_id']);
$post_id 					= wppc_checkInteger($_REQUEST['post_id']);
$p		 					= wppc_checkInteger($_REQUEST['p'],0);
$img_id 					= wppc_checkInteger($_REQUEST['img_id']);
$photocontest_init_day 		= wppc_checkValidDate($_REQUEST['photocontest_init_day']);
$photocontest_end_day 		= wppc_checkValidDate($_REQUEST['photocontest_end_day']);
$photocontest_upload_day 	= wppc_checkValidDate($_REQUEST['photocontest_upload_day']);
$visible 					= wppc_checkOptions($_REQUEST['v'],array('Y', 'N'));
$photocontest_contest_name 	= wppc_checkString($_REQUEST['photocontest_contest_name'],array(),'');
$photocontest_intro_text	= wppc_checkString	(
												$_REQUEST['photocontest_intro_text'],
													array(
														'a' => 
															array(
																'href' => array(),
																'title' => array(),
																'class' => array()
															),
														'br' => array('clear' => array(), 'class' => array()),
														'em' => array(),
														'strong' => array(),
														'ul' => array(),
														'li' => array(),
														'ol' => array(),
														'h1' => array('class'=> array()),
														'h2' => array('class'=> array()),
														'h3' => array('class'=> array()),
														'img' => 
															array(
																'src' => array(),
																'align' => array(),
																'hspace' => array(),
																'vspace' => array(),																
																'alt' => array(),
																'class' => array()
															)																												
													)
												);
$photocontest_enter_text	= wppc_checkString	(
												$_REQUEST['photocontest_enter_text'],
														array(
														'a' => 
															array(
																'href' => array(),
																'title' => array(),
																'class' => array()
															),
														'br' => array('clear' => array(), 'class' => array()),
														'em' => array(),
														'strong' => array(),
														'ul' => array(),
														'li' => array(),
														'ol' => array(),
														'h1' => array('class'=> array()),
														'h2' => array('class'=> array()),
														'h3' => array('class'=> array()),
														'img' => 
															array(
																'src' => array(),
																'align' => array(),
																'hspace' => array(),
																'vspace' => array(),																
																'alt' => array(),
																'class' => array()
															)																												
													)
												);
$votes_array 	=$_REQUEST['vote_id'];											

### Add some vars
$filter_post_id	= null;
$show_list 		= true;

//wppc_pr($mode);
### Form Processing 
if(!empty($mode)) {
	// Decide What To Do
	switch($mode) {
		// Edit the enter text of the PhotoContest
		case __('Edit Upload text', 'wp-photocontest'):
			// PhotoContest ID
			if ($photocontest_enter_text && $contest_id)
			{
				//update total count images
				$update_result = $wpdb->update
				(
					$wpdb->prefix.'photocontest_admin',
					array( 'enter_text' => $photocontest_enter_text ),
					array( 'contest_id' => $contest_id )
				);	
				if ($update_result)
				{
					$text .= '<p style="color: green;">'.sprintf(__('PhotoContest with id \'%d\' updated successfully.', 'wp-photocontest'), $contest_id).'</p>';
					break;
				}
				else
				{
					$text .= '<p style="color: red;">'.sprintf(__('Error in updating PhotoContest with id \'%d\'.', 'wp-photocontest'), $contest_id).'</p>';
					$text .= '<p style="color: red;">'.__('Database update failed.', 'wp-photocontest').'</p>';
					$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';					
					break;
				}		
			}
			else
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Invalid enter text.', 'wp-photocontest').'</p>';
				break;				
			}
		break;
		
		// Edit the intro text of the PhotoContest
		case __('Edit Introduction text', 'wp-photocontest'):
			// PhotoContest ID
			if ($photocontest_intro_text && $contest_id)
			{
				//update total count images
				$update_result = $wpdb->update
				(
					$wpdb->prefix.'photocontest_admin',
					array( 'intro_text' => $photocontest_intro_text ),
					array( 'contest_id' => $contest_id )
				);	
				if ($update_result)
				{
					$text .= '<p style="color: green;">'.sprintf(__('PhotoContest with id \'%d\' updated successfully.', 'wp-photocontest'), $contest_id).'</p>';
					break;
				}
				else
				{
					$text .= '<p style="color: red;">'.sprintf(__('Error in updating PhotoContest with id \'%d\'.', 'wp-photocontest'), $contest_id).'</p>';
					$text .= '<p style="color: red;">'.__('Database update failed.', 'wp-photocontest').'</p>';
					$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';					
					break;
				}		
			}
			else
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Invalid intro text.', 'wp-photocontest').'</p>';
				break;				
			}
		break;
		
		// Edit the startdate of the PhotoContest		
		case __('Edit Startdate', 'wp-photocontest'):
			// PhotoContest ID
			if ($photocontest_init_day && $contest_id)
			{
				//update total count images
				$update_result = $wpdb->update
				(
					$wpdb->prefix.'photocontest_admin',
					array( 'start_date' => $photocontest_init_day ),
					array( 'contest_id' => $contest_id )
				);	
				if ($update_result)
				{
					$text .= '<p style="color: green;">'.sprintf(__('PhotoContest with id \'%d\' updated successfully.', 'wp-photocontest'), $contest_id).'</p>';
					break;
				}
				else
				{
					$text .= '<p style="color: red;">'.sprintf(__('Error in updating PhotoContest with id \'%d\'.', 'wp-photocontest'), $contest_id).'</p>';
					$text .= '<p style="color: red;">'.__('Database update failed.', 'wp-photocontest').'</p>';
					$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';
					break;
				}		
			}
			else
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Invalid startdate.', 'wp-photocontest').'</p>';
				break;				
			}
		break;	
		
		// Edit the enddate of the PhotoContest
		case __('Edit Enddate', 'wp-photocontest'):
			// PhotoContest ID
			if ($photocontest_end_day && $contest_id)
			{
				//update total count images
				$update_result = $wpdb->update
				(
					$wpdb->prefix.'photocontest_admin',
					array( 'end_date' => $photocontest_end_day ),
					array( 'contest_id' => $contest_id )
				);	
				if ($update_result)
				{
					$text .= '<p style="color: green;">'.sprintf(__('PhotoContest with id \'%d\' updated successfully.', 'wp-photocontest'), $contest_id).'</p>';
					break;
				}
				else
				{
					$text .= '<p style="color: red;">'.sprintf(__('Error in updating PhotoContest with id \'%d\'.', 'wp-photocontest'), $contest_id).'</p>';
					$text .= '<p style="color: red;">'.__('Database update failed.', 'wp-photocontest').'</p>';
					$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';					
					break;
				}		
			}
			else
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Invalid enddate.', 'wp-photocontest').'</p>';
				break;				
			}
		
		break;
		
		// Edit the upload date of the PhotoContest
		case __('Edit Uploaddate', 'wp-photocontest'):
			// PhotoContest ID
			if ($photocontest_upload_day && $contest_id)
			{
				//update total count images
				$update_result = $wpdb->update
				(
					$wpdb->prefix.'photocontest_admin',
					array( 'upload_date' => $photocontest_upload_day ),
					array( 'contest_id' => $contest_id )
				);	
				if ($update_result)
				{
					$text .= '<p style="color: green;">'.sprintf(__('PhotoContest with id \'%d\' updated successfully.', 'wp-photocontest'), $contest_id).'</p>';
					break;
				}
				else
				{
					$text .= '<p style="color: red;">'.sprintf(__('Error in updating PhotoContest with id \'%d\'.', 'wp-photocontest'), $contest_id).'</p>';
					$text .= '<p style="color: red;">'.__('Database update failed.', 'wp-photocontest').'</p>';
					$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';					
					break;
				}		
			}
			else
			{
				$text .= '<p style="color: red;">'.sprintf(__('Error in adding PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				$text .= '<p style="color: red;">'.__('Invalid uploaddate.', 'wp-photocontest').'</p>';
				break;				
			}
		
		break;

		case 'Set to publish':
			$voter_status = 'publish';
			
			foreach ($votes_array as $key=>$votes_id)
			{
			
				//wppc_pr($key."=>".$votes_id);
				$update_result = $wpdb->update
				(
					$wpdb->prefix.'photocontest_votes',
					array( 'voter_status' => $voter_status ),
					array( 'voter_id' => $votes_id ),
					array( '%s' ), 
					array( '%s' )				
				);	
				if ($update_result)
				{
					$text .= '<p style="color: green;">'.sprintf(__('Vote with id \'%d\' updated successfully.', 'wp-photocontest'), $votes_id).'</p>';
				}
			}		
			//wppc_prx($votes_array);						
			$mode = 'view_votes';
		break;
		
		case 'Set to pending':
			$voter_status = 'pending';
			
			foreach ($votes_array as $key=>$votes_id)
			{
			
				//wppc_pr($key."=>".$votes_id);
				$update_result = $wpdb->update
				(
					$wpdb->prefix.'photocontest_votes',
					array( 'voter_status' => $voter_status ),
					array( 'voter_id' => $votes_id ),
					array( '%s' ), 
					array( '%s' )				
				);	
				if ($update_result)
				{
					$text .= '<p style="color: green;">'.sprintf(__('Vote with id \'%d\' updated successfully.', 'wp-photocontest'), $votes_id).'</p>';
				}
			}		
			//wppc_prx($votes_array);						
			$mode = 'view_votes';
		break;	
							
		case 'Set to drafts':
			$voter_status = 'draft';
			
			foreach ($votes_array as $key=>$votes_id)
			{
			
				//wppc_pr($key."=>".$votes_id);
				$update_result = $wpdb->update
				(
					$wpdb->prefix.'photocontest_votes',
					array( 'voter_status' => $voter_status ),
					array( 'voter_id' => $votes_id ),
					array( '%s' ), 
					array( '%s' )				
				);	
				if ($update_result)
				{
					$text .= '<p style="color: green;">'.sprintf(__('Vote with id \'%d\' updated successfully.', 'wp-photocontest'), $votes_id).'</p>';
				}
			}		
			//wppc_prx($votes_array);						
			$mode = 'view_votes';
		break;
		
		default:
			// We don't have a valid action. Just show the add-form
		break;			
	}
}		
?>
<div id="wppc_donate">
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="9509726">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/nl_NL/i/scr/pixel.gif" width="1" height="1" />
	</form>
</div>
<div class="wrap">
	<h2 id="wppc_header"><?php _e('PhotoContest Admin', 'wp-photocontest');?></h2>
	<br clear="all" />	
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">	
	<?php
		### Determines Which Mode It Is
		switch($mode) {

			
			// Change intro text
			case 'changeintro':
				$prq = "SELECT contest_id, contest_name, intro_text FROM ".$wpdb->prefix."photocontest_admin WHERE contest_id=".$contest_id;
				$out = (array) $wpdb->get_row($prq);
				?>
				<input type="hidden" name="contest_id" value="<?php echo $contest_id; ?>" />
				<input type="hidden" name="photocontest_contest_name" value="<?php echo $out['contest_name']; ?>" />
				<h3><?php _e('Edit Introduction text', 'wp-photocontest') ?></h3>
				<table class="form-table">
					<tr>
						<th width="20%" scope="row" valign="top"><?php _e('Introduction text', 'wp-photocontest') ?>:</th>
						<td width="80%">
							<textarea rows="6" cols="50" name="photocontest_intro_text"><?php echo $out['intro_text'];?></textarea>
							<br/>
							<?php _e('Enter the initial content of the page as an introduction.', 'wp-photocontest') ?><br />
							<em>
							<?php _e('To format the content in this textarea field, enter HTML tags.', 'wp-photocontest');?><br />
							<?php _e('Don\'t use the following tags', 'wp-photocontest');?>: <strong>HTML, HEAD and BODY</strong>
							</em>
						</td>
					</tr>		
				</table>
				<p style="text-align: center;"><input type="submit" name="mode" value="<?php _e('Edit Introduction text', 'wp-photocontest'); ?>"  class="button" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-photocontest'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
				<?php
				$show_list=false;
			break;
			case 'changeenter':
				$prq = "SELECT contest_id, contest_name, enter_text FROM ".$wpdb->prefix."photocontest_admin WHERE contest_id=".$contest_id;
				$out = (array) $wpdb->get_row($prq);
				?>
				<input type="hidden" name="contest_id" value="<?php echo $contest_id; ?>" />
				<input type="hidden" name="photocontest_contest_name" value="<?php echo $out['contest_name']; ?>" />
				<h3><?php _e('Edit Upload text', 'wp-photocontest') ?></h3>
				<table class="form-table">
					<tr>
						<th width="20%" scope="row" valign="top"><?php _e('Upload text', 'wp-photocontest') ?>:</th>
						<td width="80%">
							<textarea rows="6" cols="50" name="photocontest_enter_text"><?php echo $out['enter_text'];?></textarea>
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
				<p style="text-align: center;"><input type="submit" name="mode" value="<?php _e('Edit Upload text', 'wp-photocontest'); ?>"  class="button" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-photocontest'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
				<?php
				$show_list=false;
			break;			
			// Change from date
			case 'changefrom':
				$prq = "SELECT contest_id, contest_name, start_date FROM ".$wpdb->prefix."photocontest_admin WHERE contest_id=".$contest_id;
				$out = (array) $wpdb->get_row($prq);
				?>
				<input type="hidden" name="contest_id" value="<?php echo $contest_id; ?>" />
				<input type="hidden" name="photocontest_contest_name" value="<?php echo $out['contest_name']; ?>" />
				<h3><?php _e('Edit Startdate', 'wp-photocontest'); ?></h3>
				<table class="form-table">
					<tr>
						<th width="20%" scope="row" valign="top"><?php _e('Startdate', 'wp-photocontest') ?>:</th>
						<td width="80%">
							<input type="text" name="photocontest_init_day" id="photocontest_init_day" size="30" value="<?php echo wppc_calDate($out['start_date']);?>"><input type="reset" value=" ... " onclick="return showCalendar('photocontest_init_day', '%Y-%m-%d');">
							<br /><?php _e('Click here the date on which you want to start the contest.', 'wp-photocontest');?>
						</td>
					</tr>		
				</table>
				<p style="text-align: center;"><input type="submit" name="mode" value="<?php _e('Edit Startdate', 'wp-photocontest'); ?>"  class="button" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-photocontest'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
				<?php
				$show_list=false;
			break;
			// Change to date
			case 'changeto':
				$prq = "SELECT contest_id, contest_name, end_date FROM ".$wpdb->prefix."photocontest_admin WHERE contest_id=".$contest_id;
				$out = (array) $wpdb->get_row($prq);
				?>
				<input type="hidden" name="contest_id" value="<?php echo $contest_id; ?>" />
				<input type="hidden" name="photocontest_contest_name" value="<?php echo $out['contest_name']; ?>" />
				<h3><?php _e('Edit Enddate', 'wp-photocontest'); ?></h3>
				<table class="form-table">
					<tr>
						<th width="20%" scope="row" valign="top"><?php _e('Enddate', 'wp-photocontest') ?>:</th>
						<td width="80%">
							<input type="text" name="photocontest_end_day" id="photocontest_end_day" size="30" value="<?php echo wppc_calDate($out['end_date']);?>"><input type="reset" value=" ... " onclick="return showCalendar('photocontest_end_day', '%Y-%m-%d');">
							<br /><?php _e('Click here the date on which you want to finish the contest.', 'wp-photocontest');?>
						</td>
					</tr>		
				</table>
				<p style="text-align: center;"><input type="submit" name="mode" value="<?php _e('Edit Enddate', 'wp-photocontest'); ?>"  class="button" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-photocontest'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
				<?php
				$show_list=false;
			break;
			// Change to date
			case 'changeup':
				$prq = "SELECT contest_id, contest_name, upload_date FROM ".$wpdb->prefix."photocontest_admin WHERE contest_id=".$contest_id;
				$out = (array) $wpdb->get_row($prq);
				?>
				<input type="hidden" name="contest_id" value="<?php echo $contest_id; ?>" />
				<input type="hidden" name="photocontest_contest_name" value="<?php echo $out['contest_name']; ?>" />
				<h3><?php _e('Edit Uploaddate', 'wp-photocontest'); ?></h3>
				<table class="form-table">
					<tr>
						<th width="20%" scope="row" valign="top"><?php _e('Uploaddate', 'wp-photocontest') ?>:</th>
						<td width="80%">
							<input type="text" name="photocontest_upload_day" id="photocontest_upload_day" size="30" value="<?php echo wppc_calDate($out['upload_date']);?>"><input type="reset" value=" ... " onclick="return showCalendar('photocontest_upload_day', '%Y-%m-%d');">
							<br /><?php _e('Click here the date on which you want to end the upload of photos.', 'wp-photocontest');?>
						</td>
					</tr>		
				</table>
				<p style="text-align: center;"><input type="submit" name="mode" value="<?php _e('Edit Uploaddate', 'wp-photocontest'); ?>"  class="button" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-photocontest'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
				<?php
				$show_list=false;
			break;			
			
			// Refresh the page
			case 'refresh':
			
				$wppc_prp 	= new photoContest();
				$updated	= $wppc_prp->refresh_page($contest_id,$p);
				
				if ($updated)
				{
					$show_list=false;
					$text .= '<p style="color: green;">'.sprintf(__('Refreshing PhotoContest \'%s\' succesfull.', 'wp-photocontest'), stripslashes($contest_id)).'.<BR /> '.__('The result is shown below', 'wp-photocontest').':</p>';
				}
				else
				{
					$text .= '<p style="color: red;">'.sprintf(__('Failed to refresh the PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($contest_id)).'</p>';
				}
				$text .= '<p><fieldset style="background-color:#ffffff"><legend><strong>&nbsp;&nbsp;';
				$text .= __('Result', 'wp-photocontest');
				$text .= '</p></strong></legend>'.$updated.'</fieldset></p>';

			break;	
							
			// view the contest details
			case 'view_votes':
				$wppc_prp 	= new photoContest();
				$view_votes	= $wppc_prp->view_votes($img_id);
				
				if (!$view_votes)
				{
					$text .= '<p style="color: red;">'.sprintf(__('Failed to view the PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($contest_id)).'</p>';
					$text .= '<p style="color: red;">'.sprintf(__('No votes found in the PhotoContest.', 'wp-photocontest')).'</p>';
				}
				else
				{
					$view_votes_sorted	= wppc_multiArraySort($view_votes,'vote_time','ASC');
					$filter_post_id 	= $contest_id;
					$show_list			= false;
					?>
					<input type="hidden" name="contest_id" value="<?php echo $contest_id; ?>" />
					<input type="hidden" name="img_id" value="<?php echo $img_id; ?>" />
					<input type="hidden" name="photocontest_contest_name" value="<?php echo $out['contest_name']; ?>" />
					<table class="widefat">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th><?php _e('Email', 'wp-photocontest');?></th>							
								<th><?php _e('Date', 'wp-photocontest');?> / <?php _e('Time', 'wp-photocontest');?></th>
								<th><?php _e('Vote', 'wp-photocontest');?></th>
								<th><?php _e('Captcha', 'wp-photocontest');?></th>
								<th><?php _e('ID', 'wp-photocontest');?></th>								
								<th><?php _e('STATUS', 'wp-photocontest');?></th>								
							</tr>
						</thead>
						<tbody>
						<?php
							foreach($view_votes_sorted as $view_votes)
							{
								if($i%2 == 0) {
									$style = 'class="alternate"';
								}  else {
									$style = '';
								}
								?>
								<tr <?php echo $style;?>>
									<td><input name="vote_id[]" type="checkbox" value="<?php echo $view_votes['voter_id'];?>" /></td>
									<td><?php echo $view_votes['voter_email'];?></td>
									<td><?php echo $view_votes['vote_time'];?></td>
									<td><?php echo $view_votes['vote'];?></td>
									<td><?php echo $view_votes['captcha_text'];?></td>
									<td><?php echo $view_votes['voter_id'];?></td>									
									<td><?php echo $view_votes['voter_status'];?></td>									
									
								</tr>								
								<?php
								$i++;
							}
						?>
						</tbody>
					</table>
					<BR />
					<p style="text-align: center;">
					Click the following button the set status of the the selected votes to
					<input type="submit" name="mode" value="<?php _e('Set to publish', 'wp-photocontest'); ?>"  class="button" />
					<input type="submit" name="mode" value="<?php _e('Set to pending', 'wp-photocontest'); ?>"  class="button" />
					<input type="submit" name="mode" value="<?php _e('Set to drafts', 'wp-photocontest'); ?>"  class="button" />
					</p>
					<?php
				}
				$show_list_replace_content .= '<a href="'.$base_page.'&mode=view_contest&contest_id='.$contest_id.'">'.__('Back', 'wp-photocontest').'</a>';

			break;	

			
			// view the contest details
			case 'view_contest':
				// Update the visible of a img
				if (!empty($visible))
				{
					if ($visible == 'Y')
					{
						$visible_flag = 1;
					}
					else
					{
						$visible_flag = 0;
					}	
					$update_result = $wpdb->update
					(
						$wpdb->prefix.'photocontest',
						array( 'visibile' => $visible_flag),
						array( 'img_id' => $img_id ),
						array( '%d' ),
						array( '%d' )
					);
	
					if (!$update_result)
					{
						$text .= '<p style="color: red;">'.sprintf(__('Failed to update the visiblity for img \'%s\'.', 'wp-photocontest'), stripslashes($img_id)).'</p>';
						$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';
					}	
					else
					{	
						$contest_row	= (array) $wpdb->get_row( $wpdb->prepare( "SELECT contest_id FROM ".$wpdb->prefix."photocontest WHERE img_id = %d", $img_id) ); 
						$contest_id		= $contest_row['contest_id'];
						if ($contest_id) {
							$count_row	= (array) $wpdb->get_row( $wpdb->prepare( "SELECT count(*) as counter FROM ".$wpdb->prefix."photocontest WHERE visibile=1 AND contest_id= %d", $contest_id) ); 
							$counter	= $count_row['counter'];
							$update_counter = $wpdb->update
							(
								$wpdb->prefix.'photocontest_admin',
								array( 'num_photo' => $counter),
								array( 'contest_id' => $contest_id ),
								array( '%d' ),
								array( '%d' )
							);					
							if (!$update_counter)
							{
								$text .= '<p style="color: red;">'.sprintf(__('Failed to update the visiblity for img \'%s\'.', 'wp-photocontest'), stripslashes($img_id)).'</p>';
								$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';
							}	
							else
							{
								$text .= '<p style="color: green;">'.sprintf(__('Updated the visiblity for img \'%s\'.', 'wp-photocontest'), stripslashes($img_id)).'</p>';
								$wppc_prp 	= new photoContest();
								$updated	= $wppc_prp->refresh_page($contest_id,$p);	
							}					
						}
					}
				}
				
				$wppc_prp 		= new photoContest();
				$view_contest	= $wppc_prp->view_contest($contest_id,-1,$p);
				if (!$view_contest['data'])
				{
					$text .= '<p style="color: red;">'.sprintf(__('Failed to view the PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($contest_id)).'</p>';
					$text .= '<p style="color: red;">'.sprintf(__('No photos found in the PhotoContest.', 'wp-photocontest')).'</p>';
				}
				else
				{
					$contest_items_sorted = wppc_multiArraySort($view_contest['data'],'nr_of_votes','DESC');		
					$filter_post_id = $contest_id;
					$show_list=false;
					?>
					<table class="widefat">
						<thead>
							<tr>
								<th><?php echo $view_contest['scrolling'];?></th>
							</tr>
						</thead>
					</table>
					<br />
					<table class="widefat">
						<thead>
							<tr>
								<th><?php _e('Preview', 'wp-photocontest');?></th>
								<th><?php _e('ID', 'wp-photocontest');?></th>
								<th><?php _e('Image name', 'wp-photocontest');?></th>
								<th><?php _e('Upload by', 'wp-photocontest');?></th>
								<th><?php _e('Date', 'wp-photocontest');?> / <?php _e('Time', 'wp-photocontest');?></th>
								<th width="200"><?php _e('Visible', 'wp-photocontest');?></th>
								<th><?php _e('Votes', 'wp-photocontest');?></th>
								<th><?php _e('Rank', 'wp-photocontest');?></th>
								<th colspan="3"><?php _e('Actions', 'wp-photocontest');?></th>
							</tr>
						</thead>
						<tbody>
						<?php
							foreach($contest_items_sorted as $contest_items)
							{
								if($i%2 == 0) {
									$style = 'class="alternate"';
								}  else {
									$style = '';
								}
								?>
								<tr <?php echo $style;?>>
									<td><a href="<?php echo get_option('siteurl');?><?php echo $contest_items['img_path'];?>" target="_blank"><img src="<?php echo get_option('siteurl');?><?php echo $contest_items['med_thumb'];?>" /></a></td>								
									<td><?php echo $contest_items['img_id'];?></td>
									<td><?php echo $contest_items['img_name'];?></td>
									<td><a href="<?php echo get_option('siteurl');?>/wp-admin/user-edit.php?user_id=<?php echo $contest_items['userid'];?>&wp_http_referer=%2Fwp251%2Fwp-admin%2Fusers.php"><?php echo $contest_items['userlogin'];?></a> (<?php echo $contest_items['userid'];?>)</td>
									<td><?php echo wppc_niceDateTime($contest_items['insert_time']);?></td>
									<td><?php
									if ($contest_items['visibile'] == 1)
									{
										_e('YES', 'wp-photocontest');
										echo ' (<a href="'.$base_page.'&mode=view_contest&img_id='.$contest_items['img_id'].'&contest_id='.$contest_id.'&v=N">';
										_e('click to deactivate', 'wp-photocontest');
										echo '</a>)';
									}
									else
									{
										_e('NO', 'wp-photocontest');
										echo ' (<a href="'.$base_page.'&mode=view_contest&img_id='.$contest_items['img_id'].'&contest_id='.$contest_id.'&v=Y">';
										_e('click to activate', 'wp-photocontest');
										echo '</a>)';										
									}	
									?>								
									</td>
									<td><?php echo $contest_items['nr_of_votes'];?></td>
									<td><?php echo $contest_items['rank'];?> %</td>
									<td><a href="<?php echo $base_page;?>&mode=view_votes&img_id=<?php echo $contest_items['img_id'];?>&contest_id=<?php echo $contest_id;?>"><?php _e('View votes', 'wp-photocontest'); ?></a></td>								
								</tr>								
								<?php
								$i++;
							}
						?>
						</tbody>
					</table>
					<BR />
					<table class="widefat">
						<thead>
							<tr>
								<th><?php echo $view_contest['scrolling'];?></th>
							</tr>
						</thead>
					</table>
					<br />
					<?php
					$show_list_replace_content = '<a href="'.$base_page.'">'.__('Back', 'wp-photocontest').'</a>';
				}

			break;	

										
			// Delete the page
			case 'delete':
				$out		= (array) $wpdb->get_row( $wpdb->prepare( "SELECT contest_id, post_id, contest_path, contest_name FROM ".$wpdb->prefix."photocontest_admin WHERE contest_id = %d", $contest_id) ); 

				if ($out)
				{
					$contest_id 				= $out['contest_id'];
					$contest_path 				= $out['contest_path'];
					$photocontest_contest_name 	= $out['contest_name'];						

					$photocontest_index_path = $contest_path;

					// Check if directory exists
					if (!file_exists(dirname(__FILE__)."/".CONTESTS_PATH."/".$photocontest_index_path))
					{
						$text .= '<p style="color: red;">'.sprintf(__('Error in checking PhotoContest directory \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
						$text .= '<p style="color: red;">'.__('There is no directory with this contest name.', 'wp-photocontest').'</p>';
						$text .= '<p style="color: red;">'.__('If you believe this is not correct, please remove the directory manualy', 'wp-photocontest').'</p>';
						//break;		
					}			
					
					// Create upload directory
					$old_photo_directory=dirname(__FILE__)."/".CONTESTS_PATH."/".$photocontest_index_path;					
					$new_photo_directory=dirname(__FILE__)."/".CONTESTS_PATH."/".$contest_id."_".$photocontest_index_path;	
														
					if (!rename($old_photo_directory,$new_photo_directory))
					{
						$text .= '<p style="color: red;">'.sprintf(__('Error in checking PhotoContest directory \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
						$text .= '<p style="color: red;">'.__('Failed to rename the directory.', 'wp-photocontest').'</p>';
						$text .= '<p style="color: red;">'.__('If you believe this is not correct, please rename the directory manualy', 'wp-photocontest').'</p>';
						//break;	
					}
					
					
					$clear_photocontest_admin	= $wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."photocontest_admin  WHERE contest_id=%d",$contest_id ) );
					
					if (!$clear_photocontest_admin)
					{
						$text .= '<p style="color: red;">'.sprintf(__('Error in deleting PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
						$text .= '<p style="color: red;">'.sprintf(__('Directory is empty, but database table \'%s\' delete failed', 'wp-photocontest'), $wpdb->prefix."photocontest_admin").'</p>';
						$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';
						break;		
					}	
										
					$image_array		= (array) $wpdb->get_results( $wpdb->prepare( "SELECT img_id FROM ".$wpdb->prefix."photocontest WHERE contest_id = %d", $contest_id) ); 
					if ($image_array)
					{
						foreach ($image_array as $img_id)
						{
							$delete_id = $img_id->img_id;
							$clear_photocontest_votes 	= $wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."photocontest_votes  WHERE img_id=%d",$delete_id ) );
	
						}
						
						$clear_photocontest 		= $wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."photocontest  WHERE contest_id=%d",$contest_id ) );
						if (!$clear_photocontest)
						{
							$text .= '<p style="color: red;">'.sprintf(__('Error in deleting PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
							$text .= '<p style="color: red;">'.sprintf(__('Directory is empty, but database table \'%s\' delete failed', 'wp-photocontest'), $wpdb->prefix."photocontest").'</p>';
							$text .= '<p style="color: red;">'.wppc_printDatabaseError().'</p>';
							break;		
						}
					}						
															
					$text .= '<p style="color: green;">'.sprintf(__('Succes deleting PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
					$text .= '<p style="color: green;">'.sprintf(__('Note: You need to delete the page/post \'%s\' manually!', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
				}
				else
				{
					$text .= '<p style="color: red;">'.sprintf(__('Error in deleting PhotoContest \'%s\'.', 'wp-photocontest'), stripslashes($photocontest_contest_name)).'</p>';
					$text .= '<p style="color: red;">'.__('Unknown PhotoContest', 'wp-photocontest').'</p>';
				}
		
			break;	
			
			case "show_tables":

				wppc_pr("photocontest_admin");
				$photocontest_admin		= (array) $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."photocontest_admin") ); 
				wppc_pr($photocontest,"RESULT:");

				wppc_pr("photocontest");
				$photocontest		= (array) $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."photocontest") ); 
				wppc_pr($photocontest,"RESULT:");

				wppc_pr("photocontest_votes");
				$photocontest_votes		= (array) $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."photocontest_votes") ); 
				wppc_pr($photocontest,"RESULT:");
						
			break;
			default:

			break;
		}
		
		if(!empty($text)) { 
			echo '<!-- Last Action --><div id="message" class="updated fade">'.stripslashes($text).'</div>'; 
		}		
		if ($show_list)
		{
			
			$pr_qlist = "SELECT contest_id, post_id, start_date, end_date, upload_date, contest_path, contest_name, max_photos, num_photo  FROM ".$wpdb->prefix."photocontest_admin";
			if ($filter_post_id)
			{
				$pr_qlist .= " WHERE post_id=".$filter_post_id;
			}
			$out		= (array) $wpdb->get_results( $wpdb->prepare($pr_qlist ) ); 
			if ($out)
			{
				?>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php _e('Contest ID', 'wp-photocontest');?></th>
							<th><?php _e('Page ID', 'wp-photocontest');?></th>
							<th><?php _e('From', 'wp-photocontest');?></th>
							<th><?php _e('To', 'wp-photocontest');?></th>
							<th><?php _e('Upload untill', 'wp-photocontest');?></th>
							<th><?php _e('Path', 'wp-photocontest');?></th>
							<th><?php _e('Name', 'wp-photocontest');?></th>
							<th><?php _e('Max photos', 'wp-photocontest');?></th>
							<th><?php _e('Photo #', 'wp-photocontest');?></th>
							<th colspan="3" width="250"><?php _e('Actions', 'wp-photocontest');?></th>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ($out as $k => $v)
					{
						$v = (array) $v;
						$style = '';
						if($k%2 != 0) {
							$style = 'class="alternate"';
						}
						?>
						<tr <?php echo $style;?>>
							<td><?php echo $v['contest_id'];?></td>
							<td><a href="<?php echo get_option('siteurl');?>/?page_id=<?php echo $v['post_id'];?>"><?php echo $v['post_id'];?></a></td>
							<td><a href="<?php echo $base_page;?>&mode=changefrom&contest_id=<?php echo $v['contest_id'];?>"><?php echo wppc_niceDate($v['start_date']);?></a></td>
							<td><a href="<?php echo $base_page;?>&mode=changeto&contest_id=<?php echo $v['contest_id'];?>"><?php echo wppc_niceDate($v['end_date']);?></a></td>
							<td><a href="<?php echo $base_page;?>&mode=changeup&contest_id=<?php echo $v['contest_id'];?>"><?php echo wppc_niceDate($v['upload_date']);?></a></td>
							<td><?php echo $v['contest_path'];?></td>
							<td><?php echo $v['contest_name'];?></td>
							<td><?php echo $v['max_photos'];?></td>
							<td><?php echo $v['num_photo'];?></td>
							<td colspan="3">
								<a href="<?php echo $base_page;?>&mode=view_contest&contest_id=<?php echo $v['contest_id'];?>"><?php _e('View', 'wp-photocontest');?></a><br />
								<a href="<?php echo $base_page;?>&mode=refresh&contest_id=<?php echo $v['contest_id'];?>"><?php _e('Refresh', 'wp-photocontest');?></a><br />
								<a href="<?php echo $base_page;?>&mode=delete&contest_id=<?php echo $v['contest_id'];?>"><?php _e('Delete', 'wp-photocontest');?></a><br />
								<a href="<?php echo $base_page;?>&mode=changeintro&contest_id=<?php echo $v['contest_id'];?>"><?php _e('Edit Introduction text', 'wp-photocontest');?></a><br />
								<a href="<?php echo $base_page;?>&mode=changeenter&contest_id=<?php echo $v['contest_id'];?>"><?php _e('Edit Upload text', 'wp-photocontest');?></a>
							</td>													
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
				<?php
			}
			else
			{
				?>
				<div class="updated fade" id="photocontest-warning" style="background-color: rgb(255, 255, 224);">
				<br />
				<?php _e('Currently no contest availble.', 'wp-photocontest');?><br />
				<a href='<?php echo $base_page_add;?>'><?php _e('Add PhotoContest', 'wp-photocontest');?></a><br />
				<br />
				</div>
				<?php
			}
		}
		else
		{
			if(!empty($show_list_replace_content))
			{ 
				echo $show_list_replace_content; 
			}
			else
			{
				echo '<a href="javascript:history.go(-1)">';
				_e('Back', 'wp-photocontest');
				echo '</a>';
			}
		}
	?>
	</form>
			
</div>