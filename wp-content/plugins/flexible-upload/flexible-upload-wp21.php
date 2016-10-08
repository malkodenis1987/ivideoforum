<?php
/*
Flexible Upload plugin

WP 2.1+ specific code

Author: Antoine Choppin
Author URI: http://blog.japonophile.com/
*/

if ( !is_admin() ) {
    return;
}

function fup_upload_action() {
	global $action, $fup_key;

	if ($action == 'upload') {

		if (!is_array($_FILES['image']['error'])) return;

		global $from_tab, $post_id, $style;
		if ( !$from_tab )
			$from_tab = 'upload';

		check_admin_referer( 'inlineuploading' );

		global $post_id, $post_title, $post_content;

		if ( !current_user_can( 'upload_files' ) )
			wp_die( __('You are not allowed to upload files.', 'fup')
				. " <a href='" . get_option('siteurl') . "/wp-admin/upload.php?style=" . attribute_escape($style . "&amp;tab=browse-all&amp;post_id=$post_id") . "'>"
				. __('Browse Files', 'fup') . '</a>'
			);

		$overrides = array('action'=>'upload');

		$errors = array();
		$successed = array();
	
		foreach ($_FILES['image']['error'] as $key=>$value) {

			if ($value == 4) {
				$errors[] = "File $key(" . wp_specialchars($_FILES['image']['name'][$key]) . "): " . __( "No file was uploaded." , 'fup');
				continue;
			}

			$the_file = array(
							'name' => $_FILES['image']['name'][$key],
							'type' => $_FILES['image']['type'][$key],
							'tmp_name' => $_FILES['image']['tmp_name'][$key],
							'error' => $_FILES['image']['error'][$key],
							'size' => $_FILES['image']['size'][$key]
						);

			$file = wp_handle_upload($the_file, $overrides);

			if ( isset($file['error']) ) {
				$errors[] = "File $key: " . $file['error'];
				continue;
			}

			$url = $file['url'];
			$type = $file['type'];
			$file = $file['file'];
			$filename = basename($file);

			// Construct the attachment array
			$attachment = array(
				'post_title' => $post_title[$key] ? $post_title[$key] : $filename,
				'post_content' => $post_content[$key],
				'post_type' => 'attachment',
				'post_parent' => $post_id,
				'post_mime_type' => $type,
				'guid' => $url
			);

			$fup_key = sprintf('%d',$key);

			// Resize image and create thumbnail if applicable
			fup_resize_and_thumbnail($file);

			// Save the data
			$id = wp_insert_attachment($attachment, $file, $post_id);

			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );

			$successed[] = $filename;
		}

		if (empty($successed)) {
			wp_die( implode('<br />', $errors ). "<br /><a href='" . get_option('siteurl')
			. "/wp-admin/upload.php?style=$style&amp;tab=$from_tab&amp;post_id=$post_id'>" . __('Back to Image Uploading', 'fup') . '</a>'
			);
		}

		if (count($successed) > 1)
			wp_redirect( get_option('siteurl') . "/wp-admin/upload.php?style=$style&tab=browse&post_id=$post_id");
		else
			wp_redirect( get_option('siteurl') . "/wp-admin/upload.php?style=$style&tab=browse&action=view&ID=$id&post_id=$post_id");
		
		die;
	}
}

/**
 * fup_thumbnail_creation_size_limit
 *
 * This filter returns a size limit of 0, so that Wordpress does not create
 * a thumbnail (because it has been done before, if needed).
 */
function fup_thumbnail_creation_size_limit($max_size, $attachment_id, $file) {

    // Forbid thumbnail creation by Wordpress (already done)
    return 0;
}

add_filter('wp_thumbnail_creation_size_limit',
           'fup_thumbnail_creation_size_limit', 10, 3);

add_filter('wp_generate_attachment_metadata',
           'fup_update_attachment_metadata');

/**
 * fup_print_css
 * 
 * This CSS code is for the Browse tab in the upload iframe
 */
function fup_postupload_stylesheet() {
?>
<style type='text/css' media='screen'>
    .uploadoptionbox { float: left; padding: 1em 2em; width: auto; }
    .uploadoptionsubmit { clear: both; text-align: right; }
    .uploadoptionboxtitle { font-weight: bold; padding-bottom: 0.5em; }
    #upload-file-view { float: left; }
	.imageattachmentoption { padding-left: 1em; }
	form#upload-file .imageattachmentoption input { width: auto; }
</style>
<?php
}

add_action('admin_print_scripts', 'fup_postupload_stylesheet');

function fup_admin_header() {
    global $fup_rel_dir;

    wp_deregister_script('upload');
    wp_enqueue_script( 'upload', '/'.$fup_rel_dir.'flexible-upload-wp21js.php', array('prototype','jquery'), false);
}

if (strpos($_SERVER['PHP_SELF'], 'wp-admin/upload.php') &&
    $_GET['style'] == 'inline') {
    add_action('admin_print_scripts', 'fup_admin_header');
}
add_action( 'upload_files_upload', 'fup_upload_action', 9);

?>
