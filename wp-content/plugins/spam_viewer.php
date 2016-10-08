<?php
/*
Plugin Name: SpamViewer
Plugin URI: http://bueltge.de/wp-spamviewer-zum-loeschen-und-retten-von-spam/255/
Description: Delete or rescure spam entries in your database, drawn from entries from the Plugin <a href="http://seclab.cs.rice.edu/proj/trackback/trackback-validator-plugin/">Trackback Validator</a> and Spam entries in your WordPress Table comments.
Version: 1.6.3
Author: Frank Bueltge
Author URI: http://bueltge.de/
*/

// How many files you will see?
$paginationCount = 10000;

// include language-file
if(function_exists('load_plugin_textdomain'))
	load_plugin_textdomain('spamviewer', str_replace( ABSPATH, '', dirname(__FILE__) ) );

$fbtbv_link = $_SERVER['REQUEST_URI'];
$fbtbv_link = str_replace("\\", "/", $fbtbv_link);

// pagination
if ($spam_count > $paginationCount) {
	$paginationMsg = $paginationCount . ' ' . __('from', 'spamviewer') . ' ' . $spam_count;
} else {
	$paginationView = $spam_count;
	$paginationMsg  = '';
}

// Counter for spam entries in comments
if (! function_exists('fbtbv_get_count')) {
	function fbtbv_get_count() {
		global $wpdb, $comments;
			$comments = $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = 'spam'");
		return $comments;
	}
}

// Counter for entries with type SPAM in db-table
if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$wpdb->prefix . 'tb_data'."'") ) == 1) {
	if (! function_exists('fbtbv_get_spam_count')) {
		function fbtbv_get_spam_count() {
			global $wpdb, $comments;
			$comments = $wpdb->get_var("SELECT COUNT(tb_ID) FROM " . $wpdb->prefix . 'tb_data' . " WHERE tb_type = 'spam'");
			return $comments;
		}
	}
}

// variable for comment-counter
if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$wpdb->comments."'") ) == 1) {
	$count = fbtbv_get_count();
} else {
	$count = 0;
}

// variable for tb_data-counter
if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$wpdb->prefix . 'tb_data'."'") ) == 1) {
	$spam_count = fbtbv_get_spam_count();
} else {
	$spam_count = 0;
}

// Add page in WordPress-admin-panel
if (! function_exists('fbtbv_delete_add_manage_page')) {
	function fbtbv_delete_add_manage_page() {
		global $wpdb, $count, $spam_count, $paginationCount, $paginationMsg;
		$viewcount = $spam_count + $count;

		// ask for wp-version
		if (function_exists('add_options_page'))
			if (get_bloginfo('version') <= '2.0.99') {
				add_management_page("Spam Viewer", 'SpamV ('.$viewcount.')', 8, __FILE__);
				// It's Wordpress 1.5.2 or 2.x. since it has been loaded successfully
			} else {
				add_submenu_page('edit-comments.php', 'Spam Viewer', 'SpamV ('.$viewcount.')', 8, __FILE__);
				// In Wordpress 2.1, a new file name is being used
			}
		}
}

// Delete entries in comment-table
if (is_plugin_page()) {
	global $wpdb, $count, $spam_count;
	if ( ($_POST['action'] == 'comment_killed') ) {
		$killed = $wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam' LIMIT $paginationCount");
		$wpdb->query("OPTIMIZE TABLE $wpdb->comments");
		if (isset($killed)){
			echo '<div class="updated"><p>';
			if ($killed) {
				echo __('Spam comments destroyed!', 'spamviewer');
			}
			echo "</p></div>";
		}
		$count = fbtbv_get_count();
	}
	
	// Delete entries in db_data-table
	if (function_exists('fbtbv_get_spam_count')) {
		if ( ($_POST['action'] == 'tb_killed') ) {
				$killed = $wpdb->query("DELETE FROM " . $wpdb->prefix . 'tb_data' . " LIMIT $paginationCount");
				$wpdb->query("OPTIMIZE TABLE " . $wpdb->prefix . 'tb_data');
				if (isset($killed)){
					echo '<div class="updated"><p>';
					if ($killed) {
						echo __('Trackback-Spamentries destroyed!', 'spamviewer');
					}
					echo "</p></div>";
				}
						$spam_count = fbtbv_get_spam_count();
		}
	}
	
	if ( (isset($_POST['submit'])) && ($_POST['action'] == 'unspam') ) {
		if ($_POST['not_tbspam'] == 0) {
			echo '<div class="updated"><p>' . __('No comments selected.', 'spamviewer') . "</p></div>"; 
		} else {
			$i = 0;
			foreach ($_POST['not_tbspam'] as $comment) : 
				$comment = (int) $comment;
				$wpdb->query("UPDATE $wpdb->comments SET comment_approved = '0' WHERE comment_ID = '$comment'");
				if (function_exists('fbtbv_get_spam_count')) {
					$wpdb->query("DELETE FROM " . $wpdb->prefix . 'tb_data' . " WHERE tb_comments_ID = '$comment'");
				}
				++$i;
			endforeach;
			if ($i == 1) {
				echo '<div class="updated"><p>' . sprintf(__('%s entry unspam.', 'spamviewer'), $i) . "</p></div>";
			} else {
				echo '<div class="updated"><p>' . sprintf(__('%s entries unspam.', 'spamviewer'), $i) . "</p></div>";
			}
		}
	}
	// Page in WP-admin-panel
?>
	<div class="wrap">
		<a name="top" id="top"></a><h2><?php _e('Spam Viewer', 'spamviewer') ?></h2>
		<?php
		echo '<p>';
		_e('The Spam Viewer allows you to delete or rescue any comments marked as Spam out of your database.<br />', 'spamviewer');
		_e('<strong>Warning: Once started, deletion cannot be cancelled.</strong><br />', 'spamviewer');
		_e('All comments marked as Spam in the WordPress table <em>comments</em> is listed below', 'spamviewer');
		if (function_exists('fbtbv_get_spam_count')) { 
				_e(' and from the plugin <a href=\'http://seclab.cs.rice.edu/proj/trackback/trackback-validator-plugin/\'>Trackback Validator</a> in the table <em>wp_data</em>', 'spamviewer');
		}
		echo '.</p>'."\n";
		if ( ($spam_count == 0) && ($count == 0) ) {
			echo '<div class="updated"><p align="center">';
			_e('<strong>Congratulations</strong>, you are free of Spam!', 'spamviewer');
			echo '</p></div>'."\n";
		} else {
		?>
		<p>
		<?php if ( ($spam_count == '') || ($spam_count == 0) ) {
		_e('There currently are no trackback spams', 'spamviewer');
		} elseif ($spam_count == 1) {
		_e('The current total is:', 'spamviewer'); ?> <strong><?php echo $spam_count; ?></strong> <a href="#tbe" title="<?php _e('to the entry', 'spamviewer'); ?>"><?php _e('Trackback', 'spamviewer'); ?></a>
		<?php } else {
		_e('The current total is', 'spamviewer'); ?> <strong><?php echo $spam_count; ?></strong> <a href="#tbe" title="<?php _e('to the entries', 'spamviewer'); ?>"><?php _e('Trackback', 'spamviewer'); ?></a>
		<?php } if ( ($count == 0) || ($count == '') ) {
		_e(' and no comments identified as Spam.', 'spamviewer');
		} elseif ($count == 1) {
		_e(' and', 'spamviewer'); ?> <strong><?php echo $count; ?></strong> <a href="#commente" title="<?php _e('to the entry', 'spamviewer'); ?>"><?php _e('comment', 'spamviewer'); ?></a> <?php _e('identified as Spam.', 'spamviewer'); ?>
		<?php } else {
		_e(' and', 'spamviewer'); ?> <strong><?php echo $count; ?></strong> <a href="#commente" title="<?php _e('to the entries', 'spamviewer'); ?>"><?php _e('comments', 'spamviewer'); ?></a> <?php _e('identified as Spam.', 'spamviewer'); ?>
		<?php } ?>
		</p>
		<?php if (function_exists('fbtbv_get_spam_count') && ($spam_count != 0)) { ?>
		<form method="post" action="<?php echo $fbtbv_link; ?>&amp;action=tb_killed" name="form1">
			<div class="tablenav">
				<input class="button" type="submit" name="Submit_tb_data" value="<?php _e('Trackback-Spam - All delete!', 'spamviewer'); ?>" />
				<input type="hidden" name="action" value="tb_killed" />
			</div>
		</form>
		<?php }
		if ($count != 0) { ?>
		<form method="post" action="<?php echo $fbtbv_link; ?>&amp;action=comment_killed" name="form2">
			<p class="submit">
				<input type="submit" name="Submit_comments" value="<?php _e('Comment Spam - Delete All!&nbsp;', 'spamviewer'); ?>" class="button-secondary" />
				<input type="hidden" name="action" value="comment_killed" />
			</p>
		</form>
		<?php } ?>
	</div>
	
	<div class="wrap">
	<form method="post" action="<?php echo $fbtbv_link; ?>&amp;action=unspam" name="spam_form">
<?php if ($spam_count != 0) { ?>  
	<h3><a name="tbe" id="tbe"></a><?php _e('Trackback-Spam', 'spamviewer'); ?> <small><?php echo $paginationMsg; ?></small></h3>
		<p class="submit">
			<input type="hidden" name="action" value="unspam" />
			<input class="button" type="submit" name="submit" value="<?php _e('Unspam me!', 'spamviewer'); ?>" />   
		</p>
		<table class="widefat" id="fb_trspam" border="0" summary="FB TRSpam" width="100%" cellpadding="3" cellspacing="3">
			<thead>
				<tr>
					<th style="text-align:center; width:7%;"><?php _e('Unspam?', 'spamviewer'); ?></th>
					<th style="text-align:center; width:7%;"><?php _e('ID', 'spamviewer'); ?></th>
					<th style="text-align:center; width:7%;"><?php _e('Post-ID', 'spamviewer'); ?></th>
					<th style="text-align:center; width:10%;"><?php _e('Author', 'spamviewer'); ?></th>
					<th style="text-align:left; width:19%;"><?php _e('URL', 'spamviewer'); ?></th>
					<th style="text-align:left; width:30%;"><?php _e('Content', 'spamviewer'); ?></th>
					<th style="text-align:center; width:10%;"><?php _e('IP', 'spamviewer'); ?></th>
				</tr>
			</thead>
			<tbody id="the-list" class="list:spam">
	<?php 
		$results = $wpdb->get_results("SELECT *
										 FROM " . $wpdb->prefix . 'tb_data' . "
									 GROUP BY tb_comments_ID
									 ORDER BY tb_date DESC
										LIMIT $paginationCount
									  ")
				   or die ('<div class="wrap"><p style="color:red;"> In Table <em>tb_data</em> there are no data!</p></div>' . mysql_error());

		foreach ($results as $result) {
			$tb_author		= substr($result->tb_author, 0, 15);
			$tb_author_url	= substr($result->tb_author_url, 7, 20).' ...';
			$class			= (' class="alternate"' == $class) ? '' : ' class="alternate"';
			$iplink_title		= __('More Informations over IP:', 'spamviewer');
			if ($result->tb_type == 'ham') {
				$checkbox_ask = "<input type=\"checkbox\" name=\"not_tbspam[]\" value=\"$result->tb_comments_ID\" />";
		  	} else {
				$checkbox_ask = '';
			};
			$tb_content = stripslashes(strip_tags($result->tb_content));
			if ($tb_content == '') {
				$tb_content = '...';
			}
			$tb_content =  substr($tb_content, 0, 50);
			$tb_content_if = strpos($tb_content, '[...]');
			if ($tb_content_if === false) {
				$tb_content_if = '';
			} else {
				$tb_content_if = ' style="background: #ffdfdf;"';
			}
			if ($result->tb_author_IP == '') {
				$result->tb_author_IP = '...';
			}
			if ($result->comment_author_email == '') {
				$tb_author_email = '...';
			} else {
				$tb_author_email = $result->comment_author_email;
			}
			if ($result->tb_author_IP == '') {
				$tb_author_IP = '...';
			} else {
				$tb_author_IP = $result->tb_author_IP;
			}
			
		  echo "
		  <tr$class$tb_content_if>
			  <td style=\"text-align:center;\">$checkbox_ask</td>
			  <td style=\"text-align:center;\">$result->tb_comments_ID</td>
			  <td style=\"text-align:center;\">$result->tb_ID</td>
			  <td>$tb_author</td>
			  <td><a href=\"$result->tb_author_url\" title=\"$result->tb_author_url\">$tb_author_url</a></td>
			  <td>$tb_content</td>
			  <td style=\"text-align:center;\"><a href=\"http://ws.arin.net/cgi-bin/whois.pl?queryinput=$result->tb_author_IP\" title=\"$iplink_title $result->tb_author_IP\">$tb_author_IP</a></td>
		  </tr>\n";
	  } // End foreach tb_data
	?>
		</tbody>
		</table>
		<p class="submit">
			<input class="button" type="submit" name="submit" value="<?php _e('Unspam me!', 'spamviewer'); ?>" />
		</p>
		<div class="tablenav">
			<div style="float: right"><a href="#top"><?php _e('Top', 'spamviewer'); ?></a></div>
		</div>
<?php } // endif tb_data
	if ($count != 0) { ?>
	<h3><a name="commente" id="commente"></a><?php _e('Comment-Spam', 'spamviewer'); ?> <small><?php echo $paginationMsg; ?></small></h3>
		<p class="submit">
			<div style="width:150px; height:20px; border:1px solid #000; background:#ffffb0; float:right; margin:2px; padding:2px; text-align:center;">Pingback</div>
			<div style="width:150px; height:20px; border:1px solid #000; background:#d7fdb5; float:right; margin:2px; padding:2px; text-align:center;">Trackback</div>
			<div style="width:150px; height:20px; border:1px solid #000; background:#ffdfdf; float:right; margin:2px; padding:2px; text-align:center;"><?php _e('maybe no Spam', 'spamviewer'); ?></div>
			<input type="hidden" name="action" value="unspam" />
			<input class="button" type="submit" name="submit" value="<?php _e('Unspam me!', 'spamviewer'); ?>" />
			<br /><br />
		</p>
		<table class="widefat" id="fb_commentspam" border="0" summary="FB CommentSpam" width="100%" cellpadding="3" cellspacing="3">
			<thead>
				<tr>
					<th style="text-align:center; width:7%;"><?php _e('Unspam?', 'spamviewer'); ?></th>
					<th style="text-align:center; width:7%;"><?php _e('ID', 'spamviewer'); ?></th>
					<th style="text-align:center; width:7%;"><?php _e('Post-ID', 'spamviewer'); ?></th>
					<th style="text-align:center; width:10%;"><?php _e('Author', 'spamviewer'); ?></th>
					<th style="text-align:left; width:19%;"><?php _e('URL', 'spamviewer'); ?></th>
					<th style="text-align:left; width:30%;"><?php _e('Content', 'spamviewer'); ?></th>
					<th style="text-align:center; width:10%;"><?php _e('IP', 'spamviewer'); ?></th>
				</tr>
			</thead>
			<tbody id="the-list" class="list:spam">
  <?php $results = $wpdb->get_results("SELECT *
										 FROM $wpdb->comments
										WHERE comment_approved = 'spam'
								 GROUP BY comment_ID, comment_author_IP
								 ORDER BY comment_type DESC, comment_date DESC
										LIMIT $paginationCount
									  ")
				   or die ('<div class="wrap"><p style="color:red;"> The table <em>comments</em> is empty!</p></div>' . mysql_error());

		foreach ($results as $result) {
			$comment_author     = substr($result->comment_author, 0, 15);
			$comment_author_url = substr($result->comment_author_url, 7, 20).' ...';
			$class              = (' class="alternate"' == $class) ? '' : ' class="alternate"';
			$iplink_title       = __('More Informations over IP:', 'spamviewer');
			if ($result->comment_approved == 'spam') {
				$checkbox_ask = "<input type=\"checkbox\" name=\"not_tbspam[]\" value=\"$result->comment_ID\" />";
			} else {
				$checkbox_ask = '';
			};
			$comment_content = stripslashes(strip_tags($result->comment_content));
			if ($comment_content == '') {
				$comment_content = '...';
			}
			$comment_content =  substr($comment_content, 0, 50);
			$comment_content_if = strpos($comment_content, '[...]');
			if ($comment_content_if === false) {
				$comment_content_if = '';
			} else {
				$comment_content_if = ' style="background: #ffdfdf;"';
			}
			if ($result->comment_author_email == '') {
				$comment_author_email = '...';
			} else {
				$comment_author_email = $result->comment_author_email;
			}
			if ($result->comment_author_IP == '') {
				$comment_author_IP = '...';
			} else {
				$comment_author_IP = $result->comment_author_IP;
			}
			if ($result->comment_type == 'pingback') {
				$is_pingback = 'background:#ffffb0;';
			} else {
				$is_pingback = '';
			}
			if ($result->comment_type == 'trackback') {
				$is_pingback = 'background:#d7fdb5;';
			} else {
				$is_pingback = '';
			}
			
		  echo "
		  <tr$class$comment_content_if>
			  <td style=\"text-align:center;$is_pingback\">$checkbox_ask</td>
			  <td style=\"text-align:center;\">$result->comment_ID</td>
			  <td style=\"text-align:center;\">$result->comment_post_ID</td>
			  <td>$comment_author</td>
			  <td><a href=\"$result->comment_author_url\" title=\"$result->comment_author_url\">$comment_author_url</a></td>
			  <td>$comment_content</td>
			  <td style=\"text-align:center;\"><a href=\"http://ws.arin.net/cgi-bin/whois.pl?queryinput=$result->comment_author_IP\" title=\"$iplink_title $result->comment_author_IP\">$comment_author_IP</a></td>
		  </tr>\n";
	  } // End foreach comment
	?>
		</tbody>
		</table>
		<p class="submit">
			<input class="button" type="submit" name="submit" value="<?php _e('Unspam me!', 'spamviewer'); ?>" />
		</p>
		<div class="tablenav">
			<div style="float: right"><a href="#top"><?php _e('Top', 'spamviewer'); ?></a></div>
		</div>
<?php } // endif comment ?>
	</form>
<?php } // End function ?>
	<br /><br /><hr />
	<p><small><?php _e('For further information or to grab the latest version of this plugin, visit the <a href=\'http://bueltge.de/wp-spamviewer-zum-loeschen-und-retten-von-spam/255\' title=\'to the plugin-website\' >plugin\'s homepage</a>.', 'spamviewer'); ?><br />&copy; Copyright 2006 - 2007 <a href="http://bueltge.de" title="<?php _e('to the Website', 'spamviewer'); ?>" >Frank B&uuml;ltge</a> | <?php _e('Want to say thank you? Visit my <a href=\'http://bueltge.de/wunschliste/\' title=\'to the wishlist\' >wishlist</a>.', 'spamviewer'); ?></small></p>
	</div>
<?php
}

//  Apply the admin-menu
add_action('admin_menu', 'fbtbv_delete_add_manage_page');

?>
