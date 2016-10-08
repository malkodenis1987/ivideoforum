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
|	- Handles all the photocontest voting and photoshowing			|
|	- wp-content/plugins/wp-photocontest/viewimg.php                |
|																    |
+-------------------------------------------------------------------+
*/
### Include the configfile
require_once(dirname(__FILE__).'/wp-photocontest-config.php');

### Add the scriptfiles to the header
$myScripts = array('jquery','jquery-form','wp-photocontest.js','common.js','validation.js','vote.js','enter.js','jquery.MetaData.js','jquery.rating.js');

if (WP_USE_FLASH != 'false')
{
	$myScripts[] = 'swfobject2.js';
}
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
$myStyles = array('skins/'.CONTESTS_SKIN.'/theme.css','skins/'.CONTESTS_SKIN.'/starrating.css');
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
### Variables
$checkArray = 	array(
					"post_id"=>"",
					"prid"=>"",
					"p"=>"",
					"order"=>"",
					"img_id"=>"",
					"wppc_voter_email"=>"",
					"wppc_vote"=>"",
					"wppc_captcha"=>""
				);

$_REQUEST = array_merge($checkArray,$_REQUEST);
$post_id	= wppc_checkInteger($_REQUEST['post_id']);
$post_id	= wppc_checkInteger($_REQUEST['prid'],$post_id);
$p 			= wppc_checkInteger($_REQUEST['p']);
$order		= wppc_checkOptions($_REQUEST['order'],array('chrono','most_voted','most_viewed','recent'),'chrono');
$img_id		= wppc_checkInteger($_REQUEST['img_id'],1);

if (isset($_REQUEST['wppc_uuid']))
{
	$pc_cookie 		= substr($_REQUEST['wppc_uuid'],0,18).substr($_REQUEST['wppc_uuid'],22);
	$img_id 		= str_replace("-","",substr($_REQUEST['wppc_uuid'],18,4));
	$status_check	= (array) $wpdb->get_row ( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d AND voter_id = %s", $img_id , $pc_cookie) );
	$contest_status	= $status_check['voter_status'];	

	//$contest_id	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT contest_id FROM ".$wpdb->prefix."photocontest WHERE img_id= %d", $img_id ) );
	//$post_id	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM ".$wpdb->prefix."photocontest_admin WHERE contest_id= %d", $contest_id ) );	

	if ($contest_status == 'publish')
	{
		$notice_string = '<div class="error">';
		$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
		$notice_string .= __('Your vote is already confirmed!', 'wp-photocontest');
		$notice_string .= '<BR />';		
		$notice_string .= '</div><BR />';
	}
	else
	{
		$update_vote = 	$wpdb->update
							(
								$wpdb->prefix.'photocontest_votes',
								array( 'voter_status' => 'publish'),
								array( 'img_id' => $img_id, 'voter_id' => $pc_cookie)
							);

		if ($update_vote)
		{
			$r12	= (array) $wpdb->get_row( $wpdb->prepare( "SELECT SUM(vote) AS sum_votes FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d AND voter_status='publish'", $img_id) );
			$q13 = 	$wpdb->update
					(
						$wpdb->prefix.'photocontest',
						array( 'sum_votes' => $r12['sum_votes']),
						array( 'img_id' => $img_id)
					);		
			$notice_string = '<div class="updated">';
			$notice_string .= __('Thanks for confirming your vote.', 'wp-photocontest');
			$notice_string .= '<BR />';
			$notice_string .= '</div><BR />';		
		}
		else
		{
			$notice_string = '<div class="error">';
			$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
			$notice_string .= __('Error saving your vote. Please use the correct url from the email you have received!', 'wp-photocontest');
			$notice_string .= '<BR />';	
			$notice_string .= '</div><BR />';
		}
	}
}


if (is_email($_REQUEST['wppc_voter_email']))
{
	 $emailadres = $_REQUEST['wppc_voter_email'];
}
else
{
	 $emailadres = '';
}
$vote 		= wppc_checkInteger($_REQUEST['wppc_vote']);
$captcha	= wppc_checkAlphaNum($_REQUEST['wppc_captcha']);


//unset($_COOKIE['photocontestVote']);
### Check if we have a returning user
if (!isset($_COOKIE['photocontestVote']))
{
	$pc_cookie = (string) $wpdb->get_var( $wpdb->prepare( "SELECT UUID() as unique_id"));
	$prexpire = mktime() + 86400 * 50;
	setcookie('photocontestVote',$pc_cookie,$prexpire);
	$cookie_vote	= 0;
}
else
{
	$pc_cookie = $_COOKIE['photocontestVote'];
	/* Check if the user already voted for this image */
	$cookie_check	= (array) $wpdb->get_row ( $wpdb->prepare( "SELECT vote,captcha_text FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d AND voter_id = %s", $img_id , $pc_cookie) );
	$captcha_text	= $cookie_check['captcha_text'];
	$cookie_vote	= $cookie_check['vote'];	
}

### Check if we have a valid user
if (ROLE_VOTING != '')
{
	$upload_role_details = get_role(ROLE_VOTING);
	$user_level = 0;
	if (is_array($upload_role_details->capabilities))
	{
		$user_level = array_reduce(array_keys($upload_role_details->capabilities), 'wppc_level_reduction');
	}

	if (is_user_logged_in())
	{
		get_currentuserinfo();
		if ($user_level>$current_user->data->user_level)
		{
			$notice_string = '<div class="error">';
			$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
			$notice_string .= '<BR />';			
			$notice_string .= __('Not enough rights.', 'wp-photocontest');
			$notice_string .= '<BR />';	
			$notice_string .= '</div><BR />';
		}
	}
	else 
	{
		if ( (!empty($_COOKIE[AUTH_COOKIE]) &&	!wp_validate_auth_cookie($_COOKIE[AUTH_COOKIE])) ||	(empty($_COOKIE[AUTH_COOKIE])) ) 
		{
			nocache_headers();
			wp_redirect(get_option('siteurl') . '/wp-content/plugins/wp-photocontest/login.php?post_id='.$post_id.'&img_id='.$img_id.'&redirect_to=' .urlencode(get_option('siteurl').'/wp-content/plugins/wp-photocontest/viewimg.php?post_id='.$post_id.'&img_id='.$img_id));
			exit();
		}
	}
}

### Set the upload directory and get the contest details
$upload_directory = '';
if ($post_id != NULL)
{
	$contest_details 				= (array) $wpdb->get_row( $wpdb->prepare( "SELECT contest_id, post_id, start_date, end_date, upload_date, max_photos, contest_path, contest_name, num_photo, intro_text, enter_text FROM ".$wpdb->prefix."photocontest_admin WHERE post_id = %d", $post_id) );

	if (count($contest_details)<=0)
	{
		$notice_string = '<div class="error">';
		$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
		$notice_string .= '<BR />';			
		$notice_string .= __('No details for this contest id', 'wp-photocontest');
		$notice_string .= '<BR />';	
		$notice_string .= '</div><BR />';	
	}
	
	$upload_directory	= dirname(__FILE__)."/".CONTESTS_PATH."/".$contest_details['contest_path'];
	$photo_contest_id	= $contest_details['contest_id'];
	$post_id			= $contest_details['post_id'];
	$contest_id 		= $contest_details['contest_id'];
	$contest_name		= $contest_details['contest_name'];
	$contest_path		= $contest_details['contest_path'];
	$intro_text			= $contest_details['intro_text'];
	$enter_text			= $contest_details['enter_text'];	
	$num_photo  		= $contest_details['num_photo'];	
	$start_date   		= $contest_details['start_date'];
	$upload_date   		= $contest_details['upload_date'];	
	$end_date   		= $contest_details['end_date'];	
	$max_photos   		= $contest_details['max_photos'];	
}
else
{
	$notice_string = '<div class="error">';
	$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
	$notice_string .= '<BR />';			
	$notice_string .= __('No contest id given', 'wp-photocontest');
	$notice_string .= '<BR />';	
	$notice_string .= '</div><BR />';	
}

##############################
### Get the page content   ###
##############################
$image_details		= (array) $wpdb->get_row( $wpdb->prepare("SELECT contest_id, wp_uid, img_id, img_path, img_name, img_title, img_comment, sum_votes, img_view_count, insert_time, wp_email FROM ".$wpdb->prefix."photocontest WHERE img_id = %d", $img_id));	

##############################
### Get the next/prev btns ###
##############################
$next_id    	     = NULL;
$prev_id	         = NULL;
$prev_details		= (array) $wpdb->get_row( $wpdb->prepare("SELECT contest_id,img_id FROM ".$wpdb->prefix."photocontest WHERE contest_id = %d AND visibile = 1 AND img_id < %d ORDER BY img_id DESC LIMIT 1", $contest_id, $img_id));	
if ($prev_details['img_id'] > 0)
{
	$prev_id			= $prev_details['img_id'];
}
$next_details		= (array) $wpdb->get_row( $wpdb->prepare("SELECT contest_id,img_id FROM ".$wpdb->prefix."photocontest WHERE contest_id = %d AND visibile = 1 AND img_id > %d ORDER BY img_id DESC LIMIT 1", $contest_id, $img_id));	
if ($next_details['img_id'] > 0)
{
	$next_id			= $next_details['img_id'];
}
// today is??
$today = date('Y-m-d');

//set the cookie and form elements to null
$capthcadata        = NULL;
$captchatext        = NULL;
$vote_result_string = NULL;
$check_vote         = NULL;

####################################
### Do some checks (where are we ###
####################################


//Show the photo, form and captcha
if ( empty($vote) && empty($wppc_action) && empty($captcha) )
{
	if ($today > substr($end_date, 0, 10))
	{
		$notice_string = '<div class="error">';
		$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
		$notice_string .= __('This contest is closed!', 'wp-photocontest');
		$notice_string .= '<BR />';	
		$notice_string .= '</div><BR />';
	} 
	elseif( $today < substr($start_date, 0, 10))
	{
		$notice_string = '<div class="error">';
		$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
		$notice_string .= __('This contest has not started yet!', 'wp-photocontest');
		$notice_string .= '<BR />';	
		$notice_string .= '</div><BR />';
	}	
	elseif ($cookie_vote > 0)
	{
		/*
		if (!isset($_REQUEST['wppc_uuid']))
		{
			$notice_string = '<div class="error">';
			$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
			$notice_string .= __('Your vote is already saved.', 'wp-photocontest');
			$notice_string .= '<BR />';		
			$notice_string .= '</div><BR />';
		}
		*/
	}
	else
	{
	
		$r3a	= (array) $wpdb->get_results( $wpdb->prepare( "SELECT vote FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d AND voter_id = %s", $img_id , $pc_cookie) );
		$captcha_insert = count($r3a);
		$captcha = new phpCaptcha();
		$capthcadata=$captcha->create(CONTESTS_PATH."/".$contest_path);
		$captchatext=$captcha->getResultStr();
		//Nog geen vote in de database.
		if ($captcha_insert == 0)
		{	
			$insert_captcha = 	$wpdb->insert
								(	
									$wpdb->prefix.'photocontest_votes',
									array(
										'img_id' => $img_id,
										'voter_id' => $pc_cookie,
										'vote' => 0,
										'captcha_text' => $captchatext,
										'voter_status' => 'draft'
									), 
									array(
										'%d', '%s', '%d', '%s', '%s'
									)
								);
			//Add an view to the image
			$add_a_view =		$wpdb->update
								(
									$wpdb->prefix.'photocontest',
									array( 'img_view_count' => 'img_view_count+1'),
									array( 'img_id' => $img_id)
								);	
		}	
		else
		{
			$update_captcha = 	$wpdb->update
								(
									$wpdb->prefix.'photocontest_votes',
									array( 'captcha_text' => $captchatext, 'voter_status' => 'draft'),
									array( 'img_id' => $img_id, 'voter_id' => $pc_cookie)
								);	
		}			
	}
}
else
{
	//Closed contest:
	if ($today > substr($end_date, 0, 10))
	{
		$vote_result_string = '<div class="error">';
		$vote_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
		$vote_result_string .= __('This contest is closed!', 'wp-photocontest');
		$vote_result_string .= '<BR />';	
		$vote_result_string .= '</div><BR />';
	} 
	elseif( $today < substr($start_date, 0, 10))
	{
		$vote_result_string = '<div class="error">';
		$vote_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
		$vote_result_string .= __('This contest has not started yet!', 'wp-photocontest');
		$vote_result_string .= '<BR />';	
		$vote_result_string .= '</div><BR />';
	} 
	elseif (empty($emailadres))
	{
		$vote_result_string = '<div class="error">';
		$vote_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
		$vote_result_string .= __('You have provided an invalid emailadress.', 'wp-photocontest');
		$vote_result_string .= '<BR />';		
		$vote_result_string .= '</div><BR />';	
	}
	else
	{	
		$email_check	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT vote FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d  AND voter_email = %s  AND voter_email!=''", $img_id , $emailadres) );

		if ($email_check>0)
		{
			$vote_result_string = '<div class="error">';
			$vote_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
			$vote_result_string .= __('You already voted with this emailadress.', 'wp-photocontest');
			$vote_result_string .= '<BR />';		
			$vote_result_string .= '</div><BR />';
		}
		else
		{
			$cookie_check	= (array) $wpdb->get_row ( $wpdb->prepare( "SELECT vote,captcha_text FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d AND voter_id = %s", $img_id , $pc_cookie) );
			$captcha_text	= $cookie_check['captcha_text'];
			$cookie_vote	= $cookie_check['vote'];
			
			if ($cookie_vote > 0)
			{
				if (!isset($_REQUEST['wppc_uuid']))
				{			
					$vote_result_string = '<div class="error">';
					$vote_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
					$vote_result_string .= __('You already voted.', 'wp-photocontest');
					$vote_result_string .= '<BR />';		
					$vote_result_string .= '</div><BR />';
				}
			}
			else
			{
				if ($captcha_text != $captcha)
				{
					$vote_result_string = '<div class="error">';
					$vote_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
					$vote_result_string .= __('The code you entered incorrect.', 'wp-photocontest');
					$vote_result_string .= '<BR />';
					$vote_result_string .= sprintf(__('The correct tekst was %1$s while you typed %2$s.', 'wp-photocontest'),$captcha_text,wppc_checkString($captcha));
					$vote_result_string .= '<BR />';
					$vote_result_string .= sprintf(__('<a href=\'%1$s?img_id=%2$d&post_id=%3$d&order=%4$s\'>Try it again</a>', 'wp-photocontest'),$_SERVER['PHP_SELF'],$img_id,$post_id,$order);
					$vote_result_string .= '<BR />';					
					$vote_result_string .= '</div><BR />';	
				}
				else 
				{
					$ok_to_save = false;
					switch (VOTING_METHOD) {
						case "hidden":
							$vote = 1;
							$ok_to_save = true;						
						break;
						
						case "star5":
						case "option5":
							if ($vote <= 5 && $vote >= 1)
							{
								$ok_to_save = true;						
							}
							else
							{
								$vote_result_string = '<div class="error">';
								$vote_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
								$vote_result_string .= __('The vote must be a number between 1 and 5.', 'wp-photocontest');
								$vote_result_string .= '<BR />';
								$vote_result_string .= sprintf(__('<a href=\'%1$s?img_id=%2$d&post_id=%3$d&order=%4$s\'>Try it again</a>', 'wp-photocontest'),$_SERVER['PHP_SELF'],$img_id,$post_id,$order);
								$vote_result_string .= '<BR />';					
								$vote_result_string .= '</div><BR />';						
							}
						break;
									
						case "star10":
						case "option10":						
							if ($vote <= 10 && $vote >= 1)
							{
								$ok_to_save = true;						
							}
							else
							{
								$vote_result_string = '<div class="error">';
								$vote_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
								$vote_result_string .= __('The vote must be a number between 1 and 10.', 'wp-photocontest');
								$vote_result_string .= '<BR />';
								$vote_result_string .= sprintf(__('<a href=\'%1$s?img_id=%2$d&post_id=%3$d&order=%4$s\'>Try it again</a>', 'wp-photocontest'),$_SERVER['PHP_SELF'],$img_id,$post_id,$order);
								$vote_result_string .= '<BR />';					
								$vote_result_string .= '</div><BR />';						
							}
						break;
					}
						
					if ($ok_to_save)
					{
						if (VISIBLE_VOTING == true)
						{		
							
							$q5 =	$wpdb->update
									(
										$wpdb->prefix.'photocontest_votes',
										array( 'vote' => $vote, 'voter_email' => $emailadres, 'voter_status' => 'pending' ),
										array( 'img_id' => $img_id, 'voter_id' => $pc_cookie)
									);					
									
							//Here we should send out the voting confirm mail
							$voter_uuid = substr($pc_cookie,0,18).str_pad($img_id, 4, "-", STR_PAD_LEFT).substr($pc_cookie,18);
														
							$subject = __('Confirm your vote', 'wp-photocontest');

							$name    = get_option('blogname');
							$mail    = get_option('admin_email');
							$headers = 'From: '.$name.' <'.$mail.'>' . "\r\n\\";
							
							$message = '';
							$message .= sprintf(__('Thanks for voting on the %1$s', 'wp-photocontest'),$image_details['img_title']);							
							$message .= '
';
							$message .= __('Click here to confirm your vote:', 'wp-photocontest');
							$message .= '
';							
							$message .= ''.get_bloginfo( 'wpurl' ).'/wp-content/plugins/wp-photocontest/viewimg.php?wppc_uuid='.$voter_uuid.'&post_id='.$post_id;
							if (is_email($emailadres))
							{
								$mail_result = wp_mail($emailadres , $subject, $message, $headers);
								if ($mail_result)
								{
									$vote_result_string = '<div class="updated">';
									$vote_result_string .= __('Thanks for voting for this photo. You will shortly receive an email to confirm your vote.', 'wp-photocontest');
									$vote_result_string .= '<BR />';
									$vote_result_string .= '</div><BR />';								
								}
								else
								{
									$admin_email = get_bloginfo( 'admin_email' );
									if (is_email($admin_email))
									{
										$message .= '

------------------------------------------------
';									
										$message .= 'Administrator: This mail wasn\'t send to the voter, due to an error. Please confirm the vote for him/her!!'.
										$mail_result_admin = wp_mail($admin_email , $subject, $message, $headers);
										if ($mail_result_admin)
										{
											$vote_result_string = '<div class="updated">';
											$vote_result_string .= __('Something went wrong with sending the confirm mail. The administrator is notified. We will confirm the vote for you!', 'wp-photocontest');
											$vote_result_string .= '<BR />';
											$vote_result_string .= '</div><BR />';								
										}
										else
										{
											$vote_result_string = '<div class="updated">';
											$vote_result_string .= __('Something went wrong with sending the confirm mail. Please email the administrator of this site!', 'wp-photocontest');
											$vote_result_string .= '<BR />';
											$vote_result_string .= '</div><BR />';								
										}										
									}								
								}
							}
							else
							{
								$vote_result_string = '<div class="error">';
								$vote_result_string .= __('You have provided an invalid emailadress.', 'wp-photocontest');
								$vote_result_string .= '<BR />';
								$vote_result_string .= '</div><BR />';							
							}							
						

						}
						else
						{	
							$q5 =	$wpdb->update
									(
										$wpdb->prefix.'photocontest_votes',
										array( 'vote' => $vote, 'voter_email' => $emailadres, 'voter_status' => 'publish' ),
										array( 'img_id' => $img_id, 'voter_id' => $pc_cookie)
									);					
							$r12	= (array) $wpdb->get_row( $wpdb->prepare( "SELECT SUM(vote) AS sum_votes FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d AND voter_status='publish'", $img_id) );
							$q13 = 	$wpdb->update
									(
										$wpdb->prefix.'photocontest',
										array( 'sum_votes' => $r12['sum_votes']),
										array( 'img_id' => $img_id)
									);							
							$vote_result_string = '<div class="updated">';
							$vote_result_string .= __('Thanks for voting for this photo.', 'wp-photocontest');
							$vote_result_string .= '<BR />';
							$vote_result_string .= '</div><BR />';						
						}
					}
				}
			}
		}
	}
}


$wppc_prp 		= new photoContest();
$large_thumb	= preg_replace("/".$image_details['img_name']."/", VIEWIMG_BOX."_".$image_details['img_name'], $image_details['img_path']);
$page_menu 		= $wppc_prp->get_top_menu(get_option("siteurl"), $post_id);

##############################
### Get the page content   ###
##############################
ob_start();
?>
<div id="post-<?php echo $post_id;?>" class="post-<?php echo $post_id;?> page">
	<h2><?php echo str_replace("!","",$contest_details['contest_name']);?></h2>
	<div class="entry">
		<p><?php echo $page_menu;?></p>
		<?php
		if ( (isset($notice_string)) && ($notice_string != null) )
		{
			echo $notice_string;
		}
		else
		{
			?>
			<br clear="all">
			<?php
			if ($prev_id)
			{
				?>
				<div id="prev_btn" style="float:left;font-size:11px;font-weight:normal !important"><a href="<?php echo get_option("siteurl");?>/wp-content/plugins/wp-photocontest/viewimg.php?post_id=<?php echo $post_id;?>&img_id=<?php echo $prev_id;?>">&laquo; <?php _e('Previous entry', 'wp-photocontest');?></a></div>
				<?php
			}
			if ($next_id)
			{
				?>
				<div id="next_btn" style="float:right;font-size:11px;font-weight:normal !important"><a href="<?php echo get_option("siteurl");?>/wp-content/plugins/wp-photocontest/viewimg.php?post_id=<?php echo $post_id;?>&img_id=<?php echo $next_id;?>"><?php _e('Next entry', 'wp-photocontest');?> &raquo;</a></div>
				<?php
			}			
			?>
			<br clear="all">			
			<div id="vote">
				<div class="wp-photocontest_detailslist">			
					<span class="wp-photocontest_details wp-photocontest_details_text">
						<h2 class="wp-photocontest_detailstitle"><?php _e('Photo', 'wp-photocontest');?></h2>
						<div class="wp-photocontest_detailstext">
							<a href="<?php echo get_option("siteurl");?><?php echo $image_details['img_path'];?>" rel="lightbox[<?php echo $image_details['img_id'];?>]" title="<?php echo $image_details['img_title'];?>"><img border="0" title="<?php echo $image_details['img_title'];?>" src="<?php echo get_option("siteurl");?><?php echo $large_thumb;?>"></a>
						</div>									
					</span>
					<span class="wp-photocontest_details wp-photocontest_details_text">
						<h2 class="wp-photocontest_detailstitle"><?php _e('Photo details', 'wp-photocontest');?></h2>
						<div class="wp-photocontest_detailstext">
							<table class="sTable">
								<tr>
									<td class="firstCol"><?php _e('Title', 'wp-photocontest');?>:</td>
									<td class="secondCol">&nbsp;</td>
									<td class="otherCol"><?php echo $image_details['img_title'];?></td>
								</tr>
								<tr>
									<td class="firstCol"><?php _e('Poster', 'wp-photocontest');?>:</td>
									<td class="secondCol">&nbsp;</td>
									<td class="otherCol">
										<?php
										if (function_exists('get_the_author_meta'))
										{
											$auther_name = get_the_author_meta('display_name', $image_details['wp_uid']);
										}
										else
										{
											$auther_name = get_author_name($image_details['wp_uid']);
										}										
										if (empty($auther_name))
										{
											list($name,$domain) = explode("@",$image_details['wp_email']);
											echo ucfirst($name);
										}
										else
										{
											//here you can put the author page?
											echo $auther_name;
										}
										?>
									</td>
								</tr>													
								<tr>
									<td class="firstCol"><?php _e('Date', 'wp-photocontest');?>:</td>
									<td class="secondCol">&nbsp;</td>
									<td class="otherCol"><?php printf(__('on %1$s at %2$s', 'wp-photocontest'),wppc_niceDateTime($image_details['insert_time'],'date'),wppc_niceDateTime($image_details['insert_time'],'time'));?></td>
								</tr>
								<tr>
									<td class="firstCol" valign="top"><?php _e('Description', 'wp-photocontest');?></td>
									<td class="secondCol">&nbsp;</td>
									<td class="otherCol"><?php echo stripslashes($image_details['img_comment']);?></td>
								</tr>
							</table>
						</div>									
					</span>
					<span class="wp-photocontest_details wp-photocontest_details_text">
						<h2 class="wp-photocontest_detailstitle"><?php _e('Rate this picture', 'wp-photocontest');?></h2>
						<div class="wp-photocontest_detailstext">							
						<?php
						if ($vote_result_string != null)
						{
							if (REDIRECT_AFTER_VOTE > 0)
							{
								$vote_result_string .= '<BR /><div class="updated">';
								$vote_result_string .= sprintf(__('<a href=\'%1$s?post_id=%2$d&order=%3$s\'>Back to the contest</a>', 'wp-photocontest'),str_replace("viewimg.php","view.php",$_SERVER['PHP_SELF']),$post_id,$order);
								$vote_result_string .= '<BR />';
								$vote_result_string .= '</div><BR />';	
							}
							echo $vote_result_string;						
						}
						else
						{
							if ($check_vote != null)
							{
								$vote_result_string = '<div class="updated">';
								$vote_result_string .= __('Vote already saved!', 'wp-photocontest');
								$vote_result_string .= '<BR />';						
								$vote_result_string .= sprintf(__('on %1$s at %2$s', 'wp-photocontest'),wppc_niceDateTime($r4['vote_time'],'date'),wppc_niceDateTime($r4['vote_time'],'time'));
								$vote_result_string .= '<BR />';
								$vote_result_string .= '</div><BR />';
								echo $vote_result_string;
							}
							else
							{	
								/* Check if the user already voted for this image */
								if (isset($current_user->data))
								{
									$email_already_voted_check	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT vote FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d  AND (voter_email = %s OR voter_email = %s) AND voter_email!=''", $img_id , $current_user->data->user_email, $emailadres) );									
									$useremailaddress = $current_user->data->user_email;
								}
								else
								{
									$email_already_voted_check	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT vote FROM ".$wpdb->prefix."photocontest_votes WHERE img_id= %d  AND voter_email = %s AND voter_email!=''", $img_id , $emailadres) );
									$useremailaddress = $emailadres;
								}
								
								if ($email_already_voted_check>0)
								{
									$already_vote_string = '<div class="updated">';
									$already_vote_string .= __('You already voted with this emailadress.', 'wp-photocontest');
									$already_vote_string .= '<BR />';		
									$already_vote_string .= '</div><BR />';
									echo $already_vote_string;
								}									
								else
								{
									?>	
									<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" id="photocontest-vote_form"> 
										<input type="hidden" name="img_id" value="<?php echo $img_id;?>">
										<input type="hidden" name="post_id" value="<?php echo $post_id;?>">										
										<table class="sTable">
											<tr>
												<td class="firstCol"><?php _e('Email', 'wp-photocontest');?>:</td>
												<td class="secondCol">&nbsp;</td>
												<td class="otherCol">
												<div class="feedback hidden" id="error_voter_email"><?php _e('Error:', 'wp-photocontest');?> <?php _e("No email provided", 'wp-photocontest');?></div>
												<input type="text" id="voter_email" name="wppc_voter_email" size="26" value="<?php echo $useremailaddress;?>">
												</td>
											</tr>
											<?php
											if (VOTING_METHOD != 'hidden')
											{
												?>
												<tr>
													<td class="firstCol"><?php _e('Votes', 'wp-photocontest');?>:</td>
													<td class="secondCol">&nbsp;</td>																			
													<td class="otherCol">
														<div class="feedback hidden" id="error_vote"></div>
														<?php
														switch (VOTING_METHOD)
														{
															case 'star5':
																for ($i=1;$i<6;$i++)
																{
																	echo "<input name='wppc_vote' type='radio' class='star' value='".$i."'/>";
																} 														
																?>
																<script>
																jQuery('.star').rating.options.cancel = '<?php _e('Cancel rating', 'wp-photocontest');?>';
																jQuery('.star').rating();
																</script>
																<?php
															break;
															case 'star10':
																for ($i=1;$i<11;$i++)
																{
																	echo "<input name='wppc_vote' type='radio' class='star' value='".$i."'/>";
																} 														
																?>
																<script>
																jQuery('.star').rating.options.cancel = '<?php _e('Cancel rating', 'wp-photocontest');?>';
																jQuery('.star').rating();
																</script>
																<?php
															break;
															case 'option5':
																echo "<select name='wppc_vote'>";
																for ($i=1;$i<6;$i++)
																{
																	echo "<option value='".$i."' label='".$i."'>".$i."</option>";
																} 
																echo "</select>";
															break;
															case 'option10':
																echo "<select name='wppc_vote'>";
																for ($i=1;$i<11;$i++)
																{
																	echo "<option value='".$i."' label='".$i."'>".$i."</option>";
																} 	
																echo "</select>";													
															break;
															default:
																echo '<input type="hidden" name="wppc_vote" id="vote" value="1" />';
															break;																																							
														}
														?>
													</td>
												</tr>
												<?php
											}
											else
											{
												echo '<input type="hidden" name="wppc_vote" id="vote" value="1" />';
											}
											
											if (SKIP_CAPTHA)
											{
												?>
												<input type="hidden" name="wppc_captcha" value="<?php echo $captchatext;?>" id="captcha" size="5">
												<?											
											}
											else
											{
											
												?>
												<tr>
													<td class="firstCol"><?php _e('Captcha', 'wp-photocontest');?>: </td>
													<td class="secondCol">&nbsp;</td>																			
													<td class="otherCol">
													<div class="feedback hidden" id="error_captcha"><?php _e('Error:', 'wp-photocontest');?> <?php _e("No captcha provided", 'wp-photocontest');?></div>
													<img src="<?php echo $capthcadata;?>" align="left" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="wppc_captcha" id="captcha" size="5">
													</td>
												</tr>
												<?php
											}
											?>
											<tr>
												<td class="firstCol"></td>
												<td class="secondCol">&nbsp;</td>																			
												<td class="otherCol"><input type="submit" name="wppc_action" value="<?php _e('send', 'wp-photocontest');?>"></td>
											</tr>								
										</table>
									</form>									
									<?php
								}
							}
						}
						?>
						</div>
					</span>	
				</div>
			</div>
			<br clear="all">
			<?php
			if ($prev_id)
			{
				?>
				<div id="prev_btn" style="float:left;font-size:11px;font-weight:normal !important"><a href="<?php echo get_option("siteurl");?>/wp-content/plugins/wp-photocontest/viewimg.php?post_id=<?php echo $post_id;?>&img_id=<?php echo $prev_id;?>">&laquo; <?php _e('Previous entry', 'wp-photocontest');?></a></div>
				<?php
			}
			if ($next_id)
			{
				?>
				<div id="next_btn" style="float:right;font-size:11px;font-weight:normal !important"><a href="<?php echo get_option("siteurl");?>/wp-content/plugins/wp-photocontest/viewimg.php?post_id=<?php echo $post_id;?>&img_id=<?php echo $next_id;?>"><?php _e('Next entry', 'wp-photocontest');?> &raquo;</a></div>
				<?php
			}			
			?>
			<br clear="all">			
		<?php
		}
		?>								
	</div>
</div>
<?php
$wp_photocontest_content = ob_get_clean();
if (!empty($vote_result_string))
{
	print($wp_photocontest_content);
}
else
{
	$template_data = wppc_getTemplate();
	$output = str_replace("[WP-PHOTOCONTEST CONTENT]", $wp_photocontest_content, $template_data);
	print($output);	
}
?>
