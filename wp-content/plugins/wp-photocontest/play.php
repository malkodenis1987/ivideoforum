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
|	- Handles all the photocontest entering 	                    |
|	- wp-content/plugins/wp-photocontest/play.php                   |
|																    |
+-------------------------------------------------------------------+
*/
### Include the configfile
require_once(dirname(__FILE__).'/wp-photocontest-config.php');

### Add the scriptfiles to the header
$myScripts = array('jquery','jquery-form','wp-photocontest.js','common.js','validation.js','vote.js','enter.js');

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
### Variables
$checkArray = 	array(
					"post_id"=>"",
					"p"=>"",
					"order"=>""
				);

$_REQUEST = array_merge($checkArray,$_REQUEST);
$post_id	= wppc_checkInteger($_REQUEST['post_id']);
$p 			= wppc_checkInteger($_REQUEST['p']);
$order		= wppc_checkOptions($_REQUEST['order'],array('chrono','most_voted','most_viewed','recent'),'chrono');

### Check if we have a valid user
if (ROLE_UPLOAD != '')
{
	$upload_role_details = get_role(ROLE_UPLOAD);
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
			wp_redirect(get_option('siteurl') . '/wp-content/plugins/wp-photocontest/login.php?post_id='.$post_id.'&redirect_to=' .urlencode(get_option('siteurl').'/wp-content/plugins/wp-photocontest/play.php?post_id='.$post_id));
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
$wppc_prp 	= new photoContest();
$page_menu	= $wppc_prp->get_top_menu(get_option('siteurl'),$post_id,'with_login');

$today 		= date('Y-m-d');
$total_nr 	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(img_id) as total_nr from ".$wpdb->prefix."photocontest WHERE wp_uid = %d and visibile=1 and contest_id = %d", $current_user->ID, $photo_contest_id) );

if (($total_nr > 0) && ($total_nr >= $max_photos) && ($current_user->ID>0))
{
	$notice_string = '<div class="error">';
	$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
	$notice_string .= '<BR />';			
	$notice_string .= sprintf(__ngettext("I'm sorry, you already have uploaded %d photo.", "I'm sorry, you already have uploaded %d photos.", $total_nr, 'wp-photocontest'), $total_nr);
	$notice_string .= '<BR />';	
	$notice_string .= sprintf(__ngettext("You can participate with %d photo.", "You can participate with %d photos.", $max_photos, 'wp-photocontest'), $max_photos);
	$notice_string .= '<BR />';
	$notice_string .= '</div><BR />';	
}
elseif( $today < substr($start_date, 0, 10))
{
	$notice_string = '<div class="error">';
	$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
	$notice_string .= '<BR />';			
	$notice_string .= __('I\'m sorry, you can not upload photos.', 'wp-photocontest');
	$notice_string .= '<BR />';	
	$notice_string .= sprintf(__("The competition begins on %s and today is only the %s.", 'wp-photocontest'),wppc_niceDate(substr($start_date, 0, 10)), wppc_niceDate($today));
	$notice_string .= '<BR />';
	$notice_string .= '</div><BR />';	
}
elseif ($today > substr($upload_date, 0, 10))
{
	$notice_string = '<div class="error">';
	$notice_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
	$notice_string .= '<BR />';			
	$notice_string .= __('I\'m sorry, you can not upload photos.', 'wp-photocontest');
	$notice_string .= '<BR />';	
	$notice_string .= sprintf(__("Last possible date for voting on the photos is %s", 'wp-photocontest'), wppc_niceDate(substr($end_date, 0, 10)));
	$notice_string .= '<BR />';
	$notice_string .= '</div><BR />';	
}
else
{
	if(!empty($_POST) && $_FILES['imagefile']['tmp_name']) 
	{
		ini_set("memory_limit", "160M");
		ini_set("upload_max_filesize", "80M");
		ini_set("upload_tmp_dir", dirname(__FILE__)."/".CONTESTS_PATH);
		$post			= $_POST;
		$files			= $_FILES;
		$fixed_contest_path = CONTESTS_PATH.'/'.$contest_path;
		
		if ($files['imagefile']["size"] == 0)
		{
			$upload_result_string = '<div class="error">';
			$upload_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';		
			$upload_result_string .= __('The file appears to be empty', 'wp-photocontest');
			$upload_result_string .= '<BR />';
			$upload_result_string .= '<a href="'.$_SERVER['PHP_SELF'].'?post_id='.$post_id.'"><b>'.__('Retry your upload', 'wp-photocontest').'</b></a>';
			$upload_result_string .= '<BR />';			
			$upload_result_string .= '</div><BR />';	
		}
		else
		{
			$upload_email 	= $post['upload_email'];

			if (!empty($current_user->data->ID))
			{
				$user_id = $current_user->data->ID;
				$user_login = $current_user->data->user_login;								
			}
			else
			{
				$user_id = 0;
				$user_login = substr($upload_email, 0, strpos($upload_email,'@'));
				//$user_punkt = substr($upload_email, 0, strpos($upload_email,'.'));
				//$user_login = substr($user_punkt, 0, strpos($user_punkt,'@'));
			}	

			// the upload is done
			$source					= $files["imagefile"]["tmp_name"];
			$file_name_array		= explode(".",$files["imagefile"]["name"]);
			$file_ext				= array_pop($file_name_array);
			$file_name				= wppc_strNormalizeName(implode("_",$file_name_array));
			$destcontest_namefile	= str_replace(".","_",$user_login)."_".$file_name.".".$file_ext;
			$destcontest_orgfile	= str_replace(".","_",$user_login)."_".$file_name;
			$dest					= $upload_directory."/".$destcontest_namefile;
			if (in_array(strtolower($file_ext),array('jpg','jpeg','gif','png')))
			{
				if (file_exists($dest) && filesize($dest)>0)
				{
					$upload_result_string = '<div class="error">';
					$upload_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';			
					$upload_result_string .= __('A photo of the same name is already exist.', 'wp-photocontest');
					$upload_result_string .= '<BR />';
					$upload_result_string .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-photocontest/'.$fixed_contest_path.'/med_'.$destcontest_namefile.'">';
					$upload_result_string .= '<BR />';				
					$upload_result_string .= '<a href="'.$_SERVER['PHP_SELF'].'?post_id='.$post_id.'"><b>'.__('Do another upload and change the file name.', 'wp-photocontest').'</b></a>';
					$upload_result_string .= '<BR />';			
					$upload_result_string .= '</div><BR />';	
				}
				else
				{ 
					if (is_uploaded_file($source))
					{
						$stat = stat( dirname( $source ));
						$perms = $stat['mode'] & 0000666;
						@chmod( $source, $perms );
						//keep the original
						if ($mvrv = move_uploaded_file($source, $dest))
						{
							$stat = stat( dirname( $dest ));
							$perms = $stat['mode'] & 0000666;
							@chmod( $dest, $perms );		
							$thumbnail = new thumbnail();
							// create small, medium and large thumbnails
							$usrImage = $thumbnail->generate($dest,$destcontest_namefile, $upload_directory, VIEWIMG_BOX.'_'.$destcontest_orgfile, VIEWIMG_BOX); // medium file					
							$medImage = $thumbnail->generate($dest,$destcontest_namefile, $upload_directory, 'med_'.$destcontest_orgfile, 240); // medium file					
							$preImage = $thumbnail->generate($dest,$destcontest_namefile, $upload_directory, 'pre_'.$destcontest_orgfile, 260, -1, 1, 130, 130); // flash file					
											
							// If all files are ok
							if ($usrImage && $medImage && $preImage) 
							{
								//startif : create thumbs ok
								$upload_result_string = '<div class="updated">';
								$upload_result_string .= '<h3>'.__('Success:', 'wp-photocontest').'</h3>';			
								$upload_result_string .= __('File successfully transmitted.', 'wp-photocontest');
								$upload_result_string .= '<BR />';	
								$upload_result_string .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-photocontest/'.$fixed_contest_path.'/med_'.$destcontest_namefile.'">';
								$upload_result_string .= '<BR />';			
								$upload_result_string .= '</div><BR />';	
						
								$img_title 		= wp_kses($post['image_title'],array('a' => array('href' => array(),'title' => array()),'br' => array(),'em' => array(),'strong' => array()));
								$img_comment	= wp_kses($post['image_comment'],array('a' => array('href' => array(),'title' => array()),'br' => array(),'em' => array(),'strong' => array()));
							
								//insert nel db
								$wpdb->insert
								(
									$wpdb->prefix.'photocontest',
									array(
										'contest_id' => $contest_id,
										'wp_uid' => $user_id,
										'wp_email' => wptexturize($upload_email),
										'img_path' => '/wp-content/plugins/wp-photocontest/'.$fixed_contest_path.'/'.$destcontest_namefile,
										'img_name' => $destcontest_namefile,
										'img_title' => wptexturize($img_title),
										'img_comment' => wptexturize($img_comment),
										'insert_time' => date('Y-m-d H:i:s'),
										'visibile' => VISIBLE_UPLOAD
									), 
									array(
										'%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d'
									)
								);
								//update total count images
								if (VISIBLE_UPLOAD > 0)
								{
									$wpdb->update
									(
										$wpdb->prefix.'photocontest_admin',
										array( 'num_photo' => $num_photo+1),
										array( 'contest_id' => $contest_id )
									);
								}
		
								//$wppc_prp = new photoContest();
								//$wppc_prp->refresh_page($post_id);
								$subject = 'New photo uploaded by '.$user_login;
								$message = '
New photo uploaded in contest '.$contest_id.'

Author : '.$user_login.' (ID: '.$user_id.')

View the contest : 
'.get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=wp-photocontest/photocontest-manager.php&mode=view_contest&contest_id='.$photo_contest_id.'
Refresh main page: 
'.get_bloginfo( 'wpurl' ).'/wp-admin/admin.php?page=wp-photocontest/photocontest-manager.php&mode=refresh&contest_id='.$photo_contest_id;
								$admin_email = get_bloginfo( 'admin_email' );
								if (is_email($admin_email))
								{
									$mail_result = wp_mail($admin_email , $subject, $message);
								}
							}
							else
							{
								//something goes wrong in thumbnails create
								$upload_result_string = '<div class="error">';
								$upload_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
								$upload_result_string .= __('Problems while creating the thumbnail files.', 'wp-photocontest');
								$upload_result_string .= '<BR />';	
								$upload_result_string .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-photocontest/'.$fixed_contest_path.'/med_'.$destcontest_namefile.'">';
								$upload_result_string .= '<BR />';				
								$upload_result_string .= '<a href="'.$_SERVER['PHP_SELF'].'?post_id='.$post_id.'"><b>'.__('Retry your upload', 'wp-photocontest').'</b></a>';
								$upload_result_string .= '<BR />';			
								$upload_result_string .= '</div><BR />';	
							}
						}
						else
						{
							//	something goes wrong in move_uploaded_files
							$upload_result_string = '<div class="error">';
							$upload_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
							$upload_result_string .= __('Problems while reading the file data.', 'wp-photocontest');
							$upload_result_string .= '<pre>'.__('Error:', 'wp-photocontest').'<br>'.wppc_file_upload_error_message($files["imagefile"]["error"])."</pre>";
							$upload_result_string .= '<BR />';	
							$upload_result_string .= '<a href="'.$_SERVER['PHP_SELF'].'?post_id='.$post_id.'"><b>'.__('Retry your upload', 'wp-photocontest').'</b></a>';
							$upload_result_string .= '<BR />';			
							$upload_result_string .= '</div><BR />';	
						}
					}
					else
					{
						//	something goes wrong in move_uploaded_files
						$upload_result_string = '<div class="error">';
						$upload_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
						$upload_result_string .= __('Problems while reading the file data.', 'wp-photocontest');
						$upload_result_string .= '<pre>'.__('Error:', 'wp-photocontest').'<br>'.wppc_file_upload_error_message($files["imagefile"]["error"])."</pre>";					
						$upload_result_string .= '<BR />';				
						$upload_result_string .= '<a href="'.$_SERVER['PHP_SELF'].'?post_id='.$post_id.'"><b>'.__('Retry your upload', 'wp-photocontest').'</b></a>';
						$upload_result_string .= '<BR />';			
						$upload_result_string .= '</div><BR />';	
					}
				}
			}
			else
			{
				//	something goes wrong in move_uploaded_files
				$upload_result_string = '<div class="error">';
				$upload_result_string .= '<h3>'.__('Error:', 'wp-photocontest').'</h3>';
				$upload_result_string .= __('Only jpg, gif or png files are permitted.', 'wp-photocontest');
				$upload_result_string .= '<BR />';				
				$upload_result_string .= '<a href="'.$_SERVER['PHP_SELF'].'?post_id='.$post_id.'"><b>'.__('Retry your upload', 'wp-photocontest').'</b></a>';
				$upload_result_string .= '<BR />';			
				$upload_result_string .= '</div><BR />';
			}
		}
	}
}

ob_start();
?>


<div id="post-<?php echo $photo_contest_id;?>" class="post-<?php echo $photo_contest_id;?> page">
	<h2><?php echo str_replace("!","",$contest_name);?>: Uploaden</h2>
	<div class="entry">
		<p><?php echo $page_menu;?></p>
		<p>
		<?php
		if (!empty($upload_result_string))
		{
			echo $upload_result_string;
		}
		else
		{	
			if (!empty($notice_string))
			{
				echo $notice_string;
			}
			else
			{
				if (isset($current_user->ID))
				{
					$currentuser_id = $current_user->ID;
				}				
				if (isset($current_user->data->ID))
				{
					$currentuser_id = $current_user->data->ID;
				}				
				
				$currentuser_email = '';
				if (isset($current_user->data->user_email))
				{
					$currentuser_email = $current_user->data->user_email;
				}				

				if (function_exists('get_the_author_meta'))
				{
					$display_name = get_the_author_meta('display_name', $currentuser_id);
				}
				else
				{
					$display_name = get_author_name($currentuser_id);
				}
				?>			
				<b><?php _e('Hello', 'wp-photocontest');?>  <?php echo $display_name; ?>,</b><br />
				<p><?php echo stripslashes($enter_text);?></p>
				<div class="wp-photocontest_detailslist">
					<span class="wp-photocontest_details wp-photocontest_details_text">
						<h2 class="wp-photocontest_detailstitle"><?php _e('Upload your photo', 'wp-photocontest');?></h2>
						<div class="wp-photocontest_detailstext">
							<div id="photocontest-enter_form_container">
								<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data" id="photocontest-enter_form">
									<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
									<table class="sTable">
										<tr>
											<td class="firstCol"><?php _e('Email', 'wp-photocontest');?>:</td>
											<td class="secondCol">&nbsp;</td>
											<td class="otherCol">
											<div class="feedback hidden" id="error_upload_email"><?php _e('Error:', 'wp-photocontest');?> <?php _e("No email provided", 'wp-photocontest');?></div>
											<input type="text" name="upload_email" id="upload_email" value="<?php echo $currentuser_email; ?>" />
											</td>
										</tr>
										<tr>
											<td class="firstCol"><?php _e('File', 'wp-photocontest');?>:</td>
											<td class="secondCol">&nbsp;</td>
											<td class="otherCol">
											<div class="feedback hidden" id="error_imagefile"><?php _e('Error:', 'wp-photocontest');?> <?php _e("No image file provided", 'wp-photocontest');?></div>
											<input type="file" name="imagefile" id="imagefile"  value="" />&nbsp;<i><?php _e('(Only jpg, gif or png)', 'wp-photocontest');?></i></td>
										</tr>
										<tr>
											<td class="firstCol"><?php _e('Title', 'wp-photocontest');?>:</td>
											<td class="secondCol">&nbsp;</td>
											<td class="otherCol">
											<div class="feedback hidden" id="error_image_title"><?php _e('Error:', 'wp-photocontest');?> <?php _e("No title provided", 'wp-photocontest');?></div>
											<input type="text" name="image_title" id="image_title"  value="" />
											</td>
										</tr>
										<tr>
											<td class="firstCol"><?php _e('About', 'wp-photocontest');?>:</td>
											<td class="secondCol">&nbsp;</td>
											<td class="otherCol">
											<div class="feedback hidden" id="error_image_comment"><?php _e('Error:', 'wp-photocontest');?> <?php _e("No comment provided", 'wp-photocontest');?></div>
											<textarea rows=10 cols="35" name="image_comment" id="image_comment"></textarea>
											</td>									
										</tr>	
										<tr>
											<td class="firstCol">&nbsp;</td>
											<td class="secondCol">&nbsp;</td>
											<td class="otherCol"><input type="submit" name="s" id="s"  value="<?php _e('Save', 'wp-photocontest');?> " /></td>
										</tr>
									</table>
								</form>
							</div>
						</div>									
					</span>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>
<?php
$wp_photocontest_content = ob_get_clean();
if (!empty($upload_result_string))
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