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
|	- Handles all the photocontest viewing (order and overview) 	|
|	- wp-content/plugins/wp-photocontest/view.php                   |
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
					"order"=>"",
					"img_id"=>""
				);

$_REQUEST = array_merge($checkArray,$_REQUEST);
$post_id	= wppc_checkInteger($_REQUEST['post_id']);
$p 			= wppc_checkInteger($_REQUEST['p']);
$order		= wppc_checkOptions($_REQUEST['order'],array('chrono','most_voted','most_viewed','recent'),'chrono');
$img_id		= wppc_checkInteger($_REQUEST['img_id'],1);

### Check if we have a valid user [uncomment this if your want the viewing only for subscribers]
/*if (is_user_logged_in())
{
	get_currentuserinfo();	
}
else 
{
	if ( (!empty($_COOKIE[AUTH_COOKIE]) &&	!wp_validate_auth_cookie($_COOKIE[AUTH_COOKIE])) ||	(empty($_COOKIE[AUTH_COOKIE])) ) 
	{
		nocache_headers();
		wp_redirect(get_option('siteurl') . '/wp-content/plugins/wp-photocontest/login.php?post_id='.$post_id.'&redirect_to=' .urlencode(get_option('siteurl').'/wp-content/plugins/wp-photocontest/view.php?post_id='.$post_id));
		exit();
	}
}*/

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
//check real num photo:

//default is 9 photo per page 
if (N_PHOTO_X_PAGE>0)
{
	$n_photo_x_page 	= N_PHOTO_X_PAGE;
}
else
{
	$n_photo_x_page 	= 9;
}

$wppc_prp 			= new photoContest();
$page_menu			= $wppc_prp->get_top_menu(get_option('siteurl'),$post_id);
$page_data			= $wppc_prp->get_page_data($photo_contest_id, $post_id, $p, $n_photo_x_page, $order, $current_user->data->user_email);
$content_title		= '';
$scrolling			= '';

switch($order)
{
	case "most_viewed":
		$content_title = __('Most viewed', 'wp-photocontest');
		$number_of_photos 	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(*) AS total_nr FROM ".$wpdb->prefix."photocontest WHERE visibile = 1 AND contest_id = %d AND img_view_count>0", $photo_contest_id) );
		$number_of_pages 	= (($number_of_photos / $n_photo_x_page) < 1 ? 1 : ($number_of_photos / $n_photo_x_page));
		$scrolling		= $wppc_prp->get_page_scrolling($post_id, $number_of_photos, $p, $number_of_pages, $order);
	break;
	
	case "most_voted":
		$number_of_photos 	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(*) AS total_nr FROM ".$wpdb->prefix."photocontest WHERE visibile = 1 AND contest_id = %d AND sum_votes>0", $photo_contest_id) );
		$number_of_pages 	= (($number_of_photos / $n_photo_x_page) < 1 ? 1 : ($number_of_photos / $n_photo_x_page));
		$content_title = __('Top rated', 'wp-photocontest');
		$scrolling		= $wppc_prp->get_page_scrolling($post_id, $number_of_photos, $p, $number_of_pages, $order);
	break;

	case "recent":
		$number_of_photos 	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(*) AS total_nr FROM ".$wpdb->prefix."photocontest WHERE visibile = 1 AND contest_id = %d", $photo_contest_id) );
		$number_of_pages 	= (($number_of_photos / $n_photo_x_page) < 1 ? 1 : ($number_of_photos / $n_photo_x_page));
		$content_title	= __('Recently added', 'wp-photocontest');
		$scrolling		= $wppc_prp->get_page_scrolling($post_id, $number_of_photos, $p, $number_of_pages, $order);
	break;
		
	case "chrono":
		$number_of_photos 	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(*) AS total_nr FROM ".$wpdb->prefix."photocontest WHERE visibile = 1 AND contest_id = %d", $photo_contest_id) );
		$number_of_pages 	= (($number_of_photos / $n_photo_x_page) < 1 ? 1 : ($number_of_photos / $n_photo_x_page));
		$content_title	= __('Chronologic view', 'wp-photocontest');
		$scrolling		= $wppc_prp->get_page_scrolling($post_id, $number_of_photos, $p, $number_of_pages, $order);
	break;
			
	default:
		$number_of_photos 	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(*) AS total_nr FROM ".$wpdb->prefix."photocontest WHERE visibile = 1 AND contest_id = %d", $photo_contest_id) );
		$number_of_pages 	= (($number_of_photos / $n_photo_x_page) < 1 ? 1 : ($number_of_photos / $n_photo_x_page));
		$content_title	= __('Recently added', 'wp-photocontest');
		$scrolling		= $wppc_prp->get_page_scrolling($post_id, $number_of_photos, $p, $number_of_pages, "chrono");
	break;
}

if (count($page_data)==0)
{

	$today 		= date('Y-m-d');
	$total_nr 	= (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(img_id) as total_nr from ".$wpdb->prefix."photocontest WHERE wp_uid = %d and visibile=1 and contest_id = %d", $current_user->ID, $photo_contest_id) );
	
	if (($total_nr > 0) && ($total_nr >= $max_photos))
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
		$notice_string .= sprintf(__("Last possible date for voting on the photos is %s"), wppc_niceDate(substr($end_date, 0, 10)));
		$notice_string .= '<BR />';
		$notice_string .= '</div><BR />';	
	} 
	else
	{
		$notice_string = '<div class="updated">';
		$notice_string .= '<h3>'.__('No photos found.', 'wp-photocontest').'</h3>';	
		$notice_string .= "<BR /><p><a href=\"".get_option('siteurl')."/wp-content/plugins/wp-photocontest/play.php?post_id=".$post_id."\">";		
		$notice_string .= __('Be the first!', 'wp-photocontest');
		$notice_string .= '</a></p>';	
		$notice_string .= '</div><BR />';
	}
}
##############################
### Get the page content   ###
##############################
ob_start();
?>
<div id="post-<?php echo $post_id;?>" class="post-<?php echo $post_id;?> page">
	<h2><?php echo str_replace("!","",$contest_name);?>: <?php echo $content_title;?></h2>
	<div class="entry">
		<p><?php echo $page_menu;?></p>
		<?php
		if (!empty($notice_string))
		{
			echo $notice_string;
		}
		else
		{
			?>
			<table class="prtable" border=0>
				<tr>
					<td>
						<div class="polaroid_container">
						<center><?php echo($scrolling);?></center>
						<?php
						foreach ($page_data as $page_row)
						{
							if (WP_USE_FLASH == 'false')
							{
								?>
								<div class="polaroid">
									<div class="polaroid_photo" id="photo_<?php echo $page_row['img_id'];?>">
										<span class="polaroid_title"><?php echo $page_row['img_title'];?></span>
										<a href="<?php echo get_option('siteurl').'/wp-content/plugins/wp-photocontest/viewimg.php?img_id='.$page_row['img_id'].'&post_id='.$post_id;?>"><img src="<?php echo $page_row['pre_thumb'];?>" border="0" /></a>
										<br />
										<span class="polaroid_votes"><?php printf(_n("%d vote", "%d votes", $page_row['sum_votes'], 'wp-photocontest'), $page_row['sum_votes']); ?></span>
										<br />
										<span class="polaroid_button"><a href="<?php echo get_option('siteurl').'/wp-content/plugins/wp-photocontest/viewimg.php?img_id='.$page_row['img_id'].'&post_id='.$post_id;?>"><?php __('Vote', 'wp-photocontest');?>Гласувай</a></span>
										<br />
									</div>
								</div>
								<?php
							}
							else
							{
								?>
								<div class="polaroid"><div id="photo_<?php echo $page_row['img_id'];?>">
								<img src="<?php echo $page_row['med_thumb'];?>" /></div></div>
								<script type="text/javascript">
								var flashvars = {photonaam:"<?php echo $page_row['img_title'];?>", photo: "<?php echo $page_row['swf_thumb'];?>", foto_id :"<?php echo $page_row['img_id'];?>", post_id :"<?php echo $post_id;?>", order :"<?php echo $order;?>", poltype: "<?php echo $page_row['rows'];?>", stemmen:"<?php printf(_n("%d vote", "%d votes", $page_row['sum_votes'], 'wp-photocontest'), $page_row['sum_votes']); ?>", vote_url:"<?php echo urlencode(get_option('siteurl').'/wp-content/plugins/wp-photocontest/viewimg.php');?>" }; 
								var params = {  menu: "false", wmode:"transparent"};
								var attributes = {  id: "<?php echo $page_row['img_id'];?>" };
								swfobject2.embedSWF("<?php echo get_option('siteurl');?>/wp-content/plugins/wp-photocontest/skins/<?php echo CONTESTS_SKIN;?>/polaroid.swf", "photo_<?php echo $page_row['img_id'];?>", "162", "200", "7.0.0","expressInstall.swf", flashvars, params, attributes);
								</script>
								<?php

							}
						}
						?>
						<center><?php echo($scrolling);?></center>
						</div>
					</td>
				</tr>
			</table>
			<?php
		}
		?>
	</div>									
</div>
<?php
$wp_photocontest_content = ob_get_clean();
$template_data = wppc_getTemplate();
$output = str_replace("[WP-PHOTOCONTEST CONTENT]", $wp_photocontest_content, $template_data);
print($output);
?>