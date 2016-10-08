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
|	- Handles all the login actions								    |
|	- wp-content/plugins/wp-photocontest/login.php				    |
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
					"post_id"=>""
				);

$_REQUEST = array_merge($checkArray,$_REQUEST);
$post_id	= wppc_checkInteger($_REQUEST['post_id'],38788);

//Set a cookie now to see if they are supported by the browser.
setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
if ( SITECOOKIEPATH != COOKIEPATH )
{
	setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);
}

delete_option('wppc_redirect_to',$redirect_to);
if ( isset( $_REQUEST['redirect_to'] ) )
{
	$redirect_to = $_REQUEST['redirect_to'];
}
else
{
	$redirect_to = urlencode(get_option('siteurl').'/wp-content/plugins/wp-photocontest/play.php?post_id='.$post_id);
}
add_option('wppc_redirect_to',$redirect_to);

$user = wp_signon();

if ( !is_wp_error($user) )
{
	// If the user can't edit posts, send them to their profile.
	/*
	if ( !$user->has_cap('edit_posts') && ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' ) )
	{
		$redirect_to = get_option('siteurl') . '/wp-admin/profile.php';
	}
	*/
	wp_redirect($redirect_to);
	exit();
}

// If cookies are disabled we can't log in even with a valid user+pass
if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
{
	$errors->add('test_cookie', '<h3>'.__('Error:', 'wp-photocontest').'</h3>'.__('Cookies are blocked or not supported by your browser.', 'wp-photocontest')." ".__('You must enable cookies to use WordPress.', 'wp-photocontest'));
}


if ( empty($wp_error) )
{
	$wp_error = new WP_Error();
}

add_option('wppc_redirect_to',$redirect_to);
ob_start();
?>
<script type="text/javascript">
	function focusit() {
		document.getElementById('user_login').focus();
	}
	window.onload = focusit;
</script>
<div id="post-login" class="post-login page">
	<h2><?php _e('Log In', 'wp-photocontest');?></h2>
	<div class="entry">
		<div id="login">
			<div class="wp-photocontest_detailslist">
				<span class="wp-photocontest_details wp-photocontest_details_text">
					<h3 class="wp-photocontest_detailstitle">&nbsp;</h3>
					<div class="wp-photocontest_detailstext">					
					<?php
						if ( !empty( $message ) ) 
						{
							echo apply_filters('login_message', $message) . "\n";
						}
					
						// Incase a plugin uses $error rather than the $errors object
						if ( !empty( $error ) ) {
							$wp_error->add('error', $error);
							unset($error);
						}
	
						if ( $wp_error->get_error_code() )
						{
							$errors = '';
							$messages = '';
							foreach ( $wp_error->get_error_codes() as $code )
							{
								$severity = $wp_error->get_error_data($code);
								foreach ( $wp_error->get_error_messages($code) as $error )
								{
									if ( 'message' == $severity )
									{
										$messages .= '	' . $error . "<br />\n";
									}
									else
									{
										$errors .= '	' . $error . "<br />\n";
									}
								}
							}
						}
						if ( !empty($errors) )
						{
							echo '<div class="error">' . apply_filters('login_errors', $errors) . "</div><br />\n";
						}
						if ( !empty($messages) )
						{
							echo '<div class="error">' . apply_filters('login_messages', $messages) . "</div><br />\n";
						}
	

						?>
						
						<form name="loginform" id="loginform" action="<?php echo wp_login_url( );?>" method="post">
							<input type="hidden" name="redirect_to" value="<?php echo attribute_escape($redirect_to); ?>" />
							<input type="hidden" name="testcookie" value="1" />		
							<table class="sTable">
								<tr>
									<td class="firstCol"><?php _e('Username', 'wp-photocontest') ?></td>
									<td class="secondCol">&nbsp;</td>
									<td class="otherCol"><input type="text" name="log" id="user_login" class="input" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" tabindex="10" /></td>
								</tr>	
								
								<tr>
									<td class="firstCol"><?php _e('Password', 'wp-photocontest') ?></td>
									<td class="secondCol">&nbsp;</td>
									<td class="otherCol"><input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" /></td>
								</tr>				
							
								<?php do_action('login_form'); ?>
								
								<tr>
									<td class="firstCol"><?php _e('Remember Me', 'wp-photocontest'); ?></td>
									<td class="secondCol">&nbsp;</td>
									<td class="otherCol"><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /></td>
								</tr>
									
								<tr>
									<td class="firstCol">&nbsp;</td>
									<td class="secondCol">&nbsp;</td>
									<td class="otherCol"><input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Log In', 'wp-photocontest'); ?>" tabindex="100" /></td>
								</tr>
							</table>
						</form>

						<span>
							<a href="<?php if ($wp_version >= 2.8) { wp_lostpassword_url( $redirect_to ); }	else { echo site_url('wp-login.php?action=lostpassword', 'login'); }	?>"><?php _e('Lost password', 'wp-photocontest') ?></a>
							<?php
							if (get_option('users_can_register'))
							{ 
								echo wp_register('| ', '');
								//echo apply_filters('register', '?post_id='.$post_id) . " | ";
							}
							
							?>
						</span>
						<!-- <?php echo get_option('wppc_redirect_to'); ?> -->
					</div>
				</span>									
			</div>			
		</div>
	</div>
</div>
<?php
$wp_photocontest_content = ob_get_clean();
$template_data = wppc_getTemplate();
$output = str_replace("[WP-PHOTOCONTEST CONTENT]", $wp_photocontest_content, $template_data);
print($output);
?>