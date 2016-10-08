<?php
/*
Flexible Upload plugin

WP 2.0.x specific code (derived from inline-uploading.php from 2.0.x)

Author: Antoine Choppin
Author URI: http://blog.japonophile.com/
*/

/*
 History:

 0.1   Initial version
 1.1   Made alignment style optional
 1.2   Support for WP versions older than 2.0.4
       Fixed iframe size (WP 2.0.x only)
 1.5   Fix for 2.0.3
 1.10  Fix for JS error when inserting non-image attachment
*/

// Changes wrt original:
//  - allow to resize image at upload (and to specify its size)
//  - allow to create or not a thumbnail and to specify its size
//  - make "link to image" the default when a thumbnail is created
//  - support for lightbox2 links
//  - when sending to the editor, wrap image in a div and add a caption
//  - handle left/right/center alignments (Japonophile-style)


// We do what inline-uploading did, with some improvements

require_once('../../../wp-config.php');
require_once(ABSPATH.'wp-admin/admin.php');
require_once(ABSPATH.'wp-content/plugins/flexible-upload/flexible-upload.php');

if ($wp_version == "2.0") {
if (!current_user_can('edit_posts'))
        die(__('You do not have permission to edit posts.', 'fup'));
}
else {
header('Content-Type: text/html; charset=' . get_option('blog_charset'));

if (!current_user_can('upload_files'))
	die(__('You do not have permission to upload files.', 'fup'));
}

if ($fup_wpver[2] < 3) { // before 2.0.3
    function attribute_escape($text) {
        return htmlentities($text, ENT_QUOTES);
    }
    function wp_nonce_url($url) {
        return $url;
    }
    function wp_nonce_field($tag) {
    }
}
else if ($fup_wpver[2] < 6) { // 2.0.4, 2.0.5
    function attribute_escape($text) {
        return wp_specialchars($text, ENT_QUOTES);
    }
}

if ($fup_wpver[2] >= 4) { // after 2.0.4
    function fup_check_referer() {
        check_admin_referer('inlineuploading');
    }
}
else {
    function fup_check_referer() {}
}

$wpvarstoreset = array('action', 'post', 'all', 'last', 'link', 'sort', 'start', 'imgtitle', 'descr', 'attachment');

for ($i=0; $i<count($wpvarstoreset); $i += 1) {
	$wpvar = $wpvarstoreset[$i];
	if (!isset($$wpvar)) {
		if (empty($_POST["$wpvar"])) {
			if (empty($_GET["$wpvar"])) {
				$$wpvar = '';
			} else {
			$$wpvar = $_GET["$wpvar"];
			}
		} else {
			$$wpvar = $_POST["$wpvar"];
		}
	}
}

$all = ( 'true' == $all ) ? 'true' : 'false';
$start = (int) $start;
$post = (int) $post;
$images_width = 1;

switch($action) {
case 'links':
// Do not pass GO.
break;

case 'delete':

fup_check_referer();

if ( !current_user_can('edit_post', (int) $attachment) )
	die(__('You are not allowed to delete this attachment.', 'fup').' <a href="'.basename(__FILE__)."?post=$post&amp;all=$all&amp;action=upload\">".__('Go back', 'fup').'</a>');

wp_delete_attachment($attachment);

wp_redirect(basename(__FILE__) ."?post=$post&all=$all&action=view&start=$start");
die;

case 'save':

fup_check_referer();

$overrides = array('action'=>'save');

$file = wp_handle_upload($_FILES['image'], $overrides);

if ( isset($file['error']) )
	die($file['error'] . '<br /><a href="' . basename(__FILE__) . '?action=upload&post=' . $post . '">'.__('Back to Image Uploading', 'fup').'</a>');

$url = $file['url'];
$type = $file['type'];
$file = $file['file'];
$filename = basename($file);

// Construct the attachment array
$attachment = array(
	'post_title' => $imgtitle ? $imgtitle : $filename,
	'post_content' => $descr,
	'post_status' => 'attachment',
	'post_parent' => $post,
	'post_mime_type' => $type,
	'guid' => $url
	);

// Save the data
$id = wp_insert_attachment($attachment, $file, $post);

if ( preg_match('!^image/!', $attachment['post_mime_type']) ) {
    // Resize, create thumbnail & incrust watermark (if needed)
	fup_resize_and_thumbnail($file);

	// Generate the attachment's postmeta.
	$imagesize = getimagesize($file);
	$imagedata['width'] = $imagesize['0'];
	$imagedata['height'] = $imagesize['1'];
	list($uwidth, $uheight) = get_udims($imagedata['width'], $imagedata['height']);
	$imagedata['hwstring_small'] = "height='$uheight' width='$uwidth'";
	$imagedata['file'] = $file;

    // Update and save attachment metadata
    $imagedata = fup_update_attachment_metadata($imagedata);
	add_post_meta($id, '_wp_attachment_metadata', $imagedata);
} else {
	add_post_meta($id, '_wp_attachment_metadata', array());
}

wp_redirect(basename(__FILE__) . "?post=$post&all=$all&action=view&start=0");
die();

case 'upload':

$current_1 = ' class="current"';
$back = $next = false;
$script = "
function isImageAttachment() {
    var e = new RegExp('(jpg|jpeg|jpe|gif|png|bmp|tif|tiff|ico)$');
    return (document.getElementById('upload').value.toLowerCase().match(e));
}
function updateIFrame() {
    var displayifimg = 'none';
    var narrowifimg = '100%';
    
    if (isImageAttachment()) {
        displayifimg = 'block';
        narrowifimg = '60%';
        updateResizeOptions('thumbnail');
        updateResizeOptions('imgresize');
    }
    var r = document.getElementById('imageattachmentresize');
    r.style.display = displayifimg;
    var t = document.getElementById('imageattachmentthumb');
    t.style.display = displayifimg;
    var a = document.getElementById('attachmenttable');
    a.style.width = narrowifimg;

    resizeIframe(parent.document.getElementById('uploading'));
}
function updateResizeOptions(prefix) {
    chkbox = document.getElementById(prefix+'_select');
    if (chkbox.checked) {
        document.getElementById(prefix+'_size').disabled = false;
        document.getElementById(prefix+'_side').disabled = false;
    }
    else {
        document.getElementById(prefix+'_size').disabled = 'disabled';
        document.getElementById(prefix+'_side').disabled = 'disabled';
    }
}
function checkBeforeSubmit() {
    if (isImageAttachment()) {
        if ((document.getElementById('imgresize_select').checked) &&
            (!document.getElementById('imgresize_size').value)) {
            alert('".__('Please enter the size to which the image should be resized.', 'fup')."');
            return;
         }
        else if ((document.getElementById('thumbnail_select').checked) &&
            (!document.getElementById('thumbnail_size').value)) {
            alert('".__('Please enter the thumbnail size.', 'fup')."');
            return;
         }
    }
    document.getElementById('upload-file').submit();
}
function resizeIframe(iframe) {
    // Find the submit button absolute position
    // and set the iframe height accordingly
    var s = document.getElementById(\"uploadsubmitbutton\");
    var y = 40; // for the button height
    while( s != null ) {
        y += s.offsetTop;
        s = s.offsetParent;
    }
    iframe.style.height = y+'px';
}
";
break;

case 'view':

// How many images do we show? How many do we query?
$num = 5;
$double = $num * 2;

if ( $post && (empty($all) || $all == 'false') ) {
	$and_post = "AND post_parent = '$post'";
	$current_2 = ' class="current"';
} else {
	$current_3 = ' class="current"';
}

if (! current_user_can('edit_others_posts') )
	$and_user = "AND post_author = " . $user_ID;

if ( $last )
	$start = $wpdb->get_var("SELECT count(ID) FROM $wpdb->posts WHERE post_status = 'attachment' $and_user $and_post") - $num;
else
	$start = (int) $start;

if ( $start < 0 )
	$start = 0;

if ( '' == $sort )
	$sort = "post_date_gmt DESC";

$attachments = $wpdb->get_results("SELECT ID, post_date, post_title, post_content, post_mime_type, guid FROM $wpdb->posts WHERE post_status = 'attachment' $and_type $and_post $and_user ORDER BY $sort LIMIT $start, $double", ARRAY_A);

if ( count($attachments) == 0 ) {
	wp_redirect( basename(__FILE__) ."?post=$post&action=upload" );
	die;
} elseif ( count($attachments) > $num ) {
	$next = $start + count($attachments) - $num;
} else {
	$next = false;
}

if ( $start > 0 ) {
	$back = $start - $num;
	if ( $back < 1 )
		$back = '0';
} else {
	$back = false;
}

$uwidth_sum = 0;
$html = '';
$popups = '';
$style = '';
$script = '';
if ( count($attachments) > 0 ) {
	$attachments = array_slice( $attachments, 0, $num );
	$__delete = __('Delete', 'fup');
	$__not_linked = __('Not Linked', 'fup');
	$__linked_to_page = __('Linked to Page', 'fup');
	$__linked_to_image = __('Linked to Image', 'fup');
	$__linked_to_file = __('Linked to File', 'fup');
	$__using_thumbnail = __('Using Thumbnail', 'fup');
	$__using_original = __('Using Original', 'fup');
	$__using_title = __('Using Title', 'fup');
	$__using_filename = __('Using Filename', 'fup');
	$__using_icon = __('Using Icon', 'fup');
	$__no_thumbnail = '<del>'.__('No Thumbnail', 'fup').'</del>';
	$__align_none = __('No alignment', 'fup');
	$__align_left = __('Align left', 'fup');
	$__align_right = __('Align right', 'fup');
	$__align_center = __('Align center', 'fup');
	$__send_to_editor = __('Send to editor', 'fup');
	$__close = __('Close Options', 'fup');
	$__confirmdelete = __('Delete this file from the server?', 'fup');
	$__nothumb = __('There is no thumbnail associated with this photo.', 'fup');
	$script .= "notlinked = '$__not_linked';
linkedtoimage = '$__linked_to_image';
linkedtopage = '$__linked_to_page';
linkedtofile = '$__linked_to_file';
usingthumbnail = '$__using_thumbnail';
usingoriginal = '$__using_original';
usingtitle = '$__using_title';
usingfilename = '$__using_filename';
usingicon = '$__using_icon';
";
	if (get_option(FUP_ALIGNMENT_MODE_OPTION)!='none') {
		$script .= "
alignnone = '$__align_none';
alignleft = '$__align_left';
alignright = '$__align_right';
";
		if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') {
			$script .= "
aligncenter = '$__align_center';
";
		}
	}
	$script .= "
var aa = new Array();
var ab = new Array();
var imga = new Array();
var imgb = new Array();
var srca = new Array();
var srcb = new Array();
var title = new Array();
var desc = new Array();
var picWidth = new Array();
var picHeight = new Array();
var filename = new Array();
var icon = new Array();
";
	foreach ( $attachments as $key => $attachment ) {
		$ID = $attachment['ID'];
		$href = get_attachment_link($ID);
		$meta = get_post_meta($ID, '_wp_attachment_metadata', true);
		if (!is_array($meta)) {
			$meta = get_post_meta($ID, 'imagedata', true); // Try 1.6 Alpha meta key
			if (!is_array($meta)) {
				$meta = array();
			}
			add_post_meta($ID, '_wp_attachment_metadata', $meta);
		}
		$attachment = array_merge($attachment, $meta);
		$noscript = "<noscript>
		<div class='caption'><a href=\"".basename(__FILE__)."?action=links&amp;attachment={$ID}&amp;post={$post}&amp;all={$all}&amp;start={$start}\">Choose Links</a></div>
		</noscript>
";
		$send_delete_cancel = "<a onclick=\"sendToEditor({$ID});return false;\" href=\"javascript:void()\">$__send_to_editor</a>
<a onclick=\"return confirm('$__confirmdelete')\" href=\"" . wp_nonce_url( basename(__FILE__) . "?action=delete&amp;attachment={$ID}&amp;all=$all&amp;start=$start&amp;post=$post", inlineuploading) . "\">$__delete</a>
		<a onclick=\"popup.style.display='none';return false;\" href=\"javascript:void()\">$__close</a>
";
		$uwidth_sum += 128;
		if ( preg_match('!^image/!', $attachment['post_mime_type'] ) ) {
			$image = & $attachment;
			if ( !empty($image['thumb']) && file_exists(dirname($image['file']).'/'.$image['thumb']) ) {
				$src = str_replace(basename($image['guid']), $image['thumb'], $image['guid']);
				$script .= "srca[{$ID}] = '$src';
srcb[{$ID}] = '{$image['guid']}';
";
				$thumb = 'true';
				$thumbtext = $__using_thumbnail;
				list($imagewidth, $imageheight) = getimagesize(dirname($image['file']).'/'.$image['thumb']);
			} else {
				$src = $image['guid'];
				$thumb = 'false';
				$thumbtext = $__no_thumbnail;
				$imagewidth = $image['width'];
				$imageheight = $image['height'];
			}
			list($image['uwidth'], $image['uheight']) = get_udims($image['width'], $image['height']);
			$height_width = 'height="'.$image['uheight'].'" width="'.$image['uwidth'].'"';
			$xpadding = (128 - $image['uwidth']) / 2;
			$ypadding = (96 - $image['uheight']) / 2;
			$style .= "#target{$ID} img { padding: {$ypadding}px {$xpadding}px; }\n";
			$title = attribute_escape($image['post_title']);
			$script .= "aa[{$ID}] = '<a id=\"p{$ID}\" rel=\"attachment\" class=\"imagelink\" href=\"$href\" onclick=\"doPopup({$ID});return false;\" title=\"{$title}\">';
ab[{$ID}] = '<a class=\"imagelink\" href=\"{$image['guid']}\" ".fup_get_link_target()."onclick=\"doPopup({$ID});return false;\" title=\"{$title}\">';
imga[{$ID}] = '<img id=\"image{$ID}\" src=\"$src\" alt=\"{$title}\" $height_width />';
imgb[{$ID}] = '<img id=\"image{$ID}\" src=\"{$image['guid']}\" alt=\"{$title}\" $height_width />';
desc[{$ID}] = '".attribute_escape($image['post_content'])."';
picWidth[{$ID}] = '".$imagewidth."';
picHeight[{$ID}] = '".$imageheight."';
";
			$html .= "<div id='target{$ID}' class='attwrap left'>
	<div id='div{$ID}' class='imagewrap' onclick=\"doPopup({$ID});\">";
			if ($thumb == 'true') {
				$html .= "
		<a class=\"imagelink\" href=\"{$image['guid']}\" ".fup_get_link_target()."onclick=\"doPopup({$ID});return false;\" title=\"{$title}\"><img id=\"image{$ID}\" src=\"$src\" alt=\"{$title}\" $height_width /></a>";
			}
			else {
				$html .= "
		<img id=\"image{$ID}\" src=\"$src\" alt=\"{$title}\" $height_width />";
			}
			$html .= "
	</div>
	{$noscript}
</div>
";
			$popups .= "<div id='popup{$ID}' class='popup'>
	<a id=\"I{$ID}\" onclick=\"if($thumb)toggleImage({$ID});else alert('$__nothumb');return false;\" href=\"javascript:void()\">$thumbtext</a>
	<a id=\"L{$ID}\" onclick=\"toggleLink({$ID});return false;\" href=\"javascript:void()\">"
				.(($thumb == 'true') ? $__linked_to_image : $__not_linked)."</a>";
			if (get_option(FUP_ALIGNMENT_MODE_OPTION)!='none') {
				$popups .= "
	<a id=\"A{$ID}\" onclick=\"toggleAlign({$ID});return false;\" href=\"javascript:void()\">";
				switch (get_option(FUP_DEFAULT_ALIGN_OPTION)) {
					case 'left':
						$popups .= $__align_left;
						break;
					case 'right':
						$popups .= $__align_right;
						break;
					case 'center':
						if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') {
							$popups .= $__align_center;
							break;
						} // else fall through
					case 'none':
					default:
						$popups .= $__align_none;
						break;
				}
				$popups .= "</a>";
			}
			$popups .= "
	{$send_delete_cancel}
</div>
";
		} else {
			$title = attribute_escape($attachment['post_title']);
			$filename = basename($attachment['guid']);
			$icon = get_attachment_icon($ID);
			$toggle_icon = "<a id=\"I{$ID}\" onclick=\"toggleOtherIcon({$ID});return false;\" href=\"javascript:void()\">$__using_title</a>";
			$script .= "aa[{$ID}] = '<a id=\"p{$ID}\" rel=\"attachment\" href=\"$href\" onclick=\"doPopup({$ID});return false;\" title=\"{$title}\">';
ab[{$ID}] = '<a id=\"p{$ID}\" href=\"{$filename}\" onclick=\"doPopup({$ID});return false;\" title=\"{$title}\">';
title[{$ID}] = '{$title}';
filename[{$ID}] = '{$filename}';
icon[{$ID}] = '{$icon}';
";
			$html .= "<div id='target{$ID}' class='attwrap left'>
	<div id='div{$ID}' class='otherwrap usingtext' onmousedown=\"selectLink({$ID})\" onclick=\"doPopup({$ID});return false;\">
		<a id=\"p{$ID}\" href=\"{$attachment['guid']}\" onmousedown=\"selectLink({$ID});\" onclick=\"return false;\">{$title}</a>
	</div>
	{$noscript}
</div>
";
			$popups .= "<div id='popup{$ID}' class='popup'>
	<div class='filetype'>".__('File Type:', 'fup').' '.str_replace('/',"/\n",$attachment['post_mime_type'])."</div>
	<a id=\"L{$ID}\" onclick=\"toggleOtherLink({$ID});return false;\" href=\"javascript:void()\">$__linked_to_file</a>
	{$toggle_icon}
	{$send_delete_cancel}
</div>
";
		}
	}
}

$images_width = $uwidth_sum + ( count($images) * 6 ) + 35;

break;

default:
die(__('This script was not meant to be called directly.', 'fup'));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_settings('blog_charset'); ?>" />
<title></title>
<meta http-equiv="imagetoolbar" content="no" />
<script type="text/javascript">
// <![CDATA[
/* Define any variables we'll need, such as alternate URLs. */
<?php echo $script; ?>
function htmldecode(st) {
	o = document.getElementById('htmldecode');
	if (! o) {
		o = document.createElement("A");
		o.id = "htmldecode"
	}
	o.innerHTML = st;
	r = o.innerHTML;
	return r;
}
function doPopup(i) {
	if ( popup )
	popup.style.display = 'none';
	target = document.getElementById('target'+i);
	popup = document.getElementById('popup'+i);
	popup.style.left = (target.offsetLeft) + 'px';
	popup.style.top = (target.offsetTop) + 'px';
	popup.style.display = 'block';
}
popup = false;
function selectLink(n) {
	o=document.getElementById('div'+n);
	if ( typeof document.body.createTextRange == 'undefined' || typeof win.tinyMCE == 'undefined' || win.tinyMCE.configs.length < 1 )
		return;
	r = document.body.createTextRange();
	if ( typeof r != 'undefined' ) {
		r.moveToElementText(o);
		r.select();
	}
}
function toggleLink(n) {
	ol=document.getElementById('L'+n);
	if ( ol.innerHTML == htmldecode(notlinked) ) {
		ol.innerHTML = linkedtoimage;
	} else if ( ol.innerHTML == htmldecode(linkedtoimage) ) {
		ol.innerHTML = linkedtopage;
	} else {
		ol.innerHTML = notlinked;
	}
	updateImage(n);
}
function toggleOtherLink(n) {
	ol=document.getElementById('L'+n);
	if ( ol.innerHTML == htmldecode(linkedtofile) ) {
		ol.innerHTML = linkedtopage;
	} else {
		ol.innerHTML = linkedtofile;
	}
	updateOtherIcon(n);
}
function toggleImage(n) {
	oi = document.getElementById('I'+n);
	if ( oi.innerHTML == htmldecode(usingthumbnail) ) {
		oi.innerHTML = usingoriginal;
	} else {
		oi.innerHTML = usingthumbnail;
	}
	updateImage(n);
}
function toggleOtherIcon(n) {
	od = document.getElementById('div'+n);
	oi = document.getElementById('I'+n);
	if ( oi.innerHTML == htmldecode(usingtitle) ) {
		oi.innerHTML = usingfilename;
		od.className = 'otherwrap usingtext';
	} else if ( oi.innerHTML == htmldecode(usingfilename) && icon[n] != '' ) {
		oi.innerHTML = usingicon;
		od.className = 'otherwrap usingicon';
	} else {
		oi.innerHTML = usingtitle;
		od.className = 'otherwrap usingtext';
	}
	updateOtherIcon(n);
}
function updateImage(n) {
	od=document.getElementById('div'+n);
	ol=document.getElementById('L'+n);
	oi=document.getElementById('I'+n);
	if ( oi.innerHTML == htmldecode(usingthumbnail) ) {
		img = imga[n];
	} else {
		img = imgb[n];
	}
	if ( ol.innerHTML == htmldecode(linkedtoimage) ) {
		od.innerHTML = ab[n]+img+'</a>';
	} else if ( ol.innerHTML == htmldecode(linkedtopage) ) {
		od.innerHTML = aa[n]+img+'</a>';
	} else {
		od.innerHTML = img;
	}
}
function updateOtherIcon(n) {
	od=document.getElementById('div'+n);
	ol=document.getElementById('L'+n);
	oi=document.getElementById('I'+n);
	if ( oi.innerHTML == htmldecode(usingfilename) ) {
		txt = filename[n];
	} else if ( oi.innerHTML == htmldecode(usingicon) ) {
		txt = icon[n];
	} else {
		txt = title[n];
	}
	if ( ol.innerHTML == htmldecode(linkedtofile) ) {
		od.innerHTML = ab[n]+txt+'</a>';
	} else if ( ol.innerHTML == htmldecode(linkedtopage) ) {
		od.innerHTML = aa[n]+txt+'</a>';
	} else {
		od.innerHTML = txt;
	}
}
<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)!='none') { ?>
function toggleAlign(n) {
	oa=document.getElementById('A'+n);
	if (oa.innerHTML == htmldecode(alignnone)) {
		oa.innerHTML = alignleft;
	} else if (oa.innerHTML == htmldecode(alignleft)) {
		oa.innerHTML = alignright;
<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
	} else if (oa.innerHTML == htmldecode(alignright)) {
		oa.innerHTML = aligncenter;
<?php } ?>
	} else {
		oa.innerHTML = alignnone;
	}
}
<?php } ?>

var win = window.opener ? window.opener : window.dialogArguments;
if (!win) win = top;
tinyMCE = win.tinyMCE;
richedit = ( typeof tinyMCE == 'object' && tinyMCE.configs.length > 0 );
function sendToEditor(n) {
	o = document.getElementById('div'+n);
	h = o.innerHTML.replace(new RegExp('^\\s*(.*?)\\s*$', ''), '$1'); // Trim
	h = h.replace(new RegExp(' (class|title|width|height|id|onclick|onmousedown)=([^\'"][^ ]*)(?=( |/|>))', 'g'), ' $1="$2"'); // Enclose attribs in quotes
	h = h.replace(new RegExp(' width="[^"]*"', 'g'), ' width="'+picWidth[n]+'"'); // Adjust size constraints
	h = h.replace(new RegExp(' height="[^"]*"', 'g'), ' height="'+picHeight[n]+'"'); // Adjust size constraints
	h = h.replace(new RegExp(' on(click|mousedown)="[^"]*"', 'g'), ''); // Drop menu events
	h = h.replace(new RegExp('<(/?)A', 'g'), '<$1a'); // Lowercase tagnames
	h = h.replace(new RegExp('<IMG', 'g'), '<img'); // Lowercase again
	h = h.replace(new RegExp('(<img .+?")>', 'g'), '$1 />'); // XHTML

	var imgalignment = '';
	var cssalignment = '';
<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
	oa=document.getElementById('A'+n);
	if (oa) {
	    if (oa.innerHTML == htmldecode(alignleft)) {
		cssalignment = ' imgalignleft';
	    } else if (oa.innerHTML == htmldecode(alignright)) {
		cssalignment = ' imgalignright';
	    } else if (oa.innerHTML == htmldecode(aligncenter)) {
		cssalignment = ' imgaligncenter';
	    }
	}
<?php } else if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='img') { ?>
	oa=document.getElementById('A'+n);
	if (oa) {
	    if (oa.innerHTML == htmldecode(alignleft)) {
		imgalignment = 'align="left" ';
	    } else if (oa.innerHTML == htmldecode(alignright)) {
		imgalignment = 'align="right" ';
	    }
	}
<?php } ?>
<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
	if (desc[n] != '') { // Caption
		h = '<div class="imageframe' + cssalignment + '" style="'
		    +'width:'+picWidth[n]+'px;">'+h
		    +'<div class="imagecaption">'+desc[n]+'</div></div>';
	}
	else
<?php } ?>
	{
		h = h.replace(new RegExp('(<img .+?") />', 'g'),
		        '$1 class="imageframe' + cssalignment + '" '
		        + imgalignment + '/>');
	}

<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
	if (oa && oa.innerHTML == htmldecode(aligncenter)) {
		h = '<div style="text-align: center;">' + h + '</div>';
	}
<?php } ?>

	if ( richedit )
		win.tinyMCE.execCommand('mceInsertContent', false, h);
	else
		win.edInsertContent(win.edCanvas, h);
}
// ]]>
</script>
<style type="text/css">
<?php if ( $action == 'links' ) : ?>
* html { overflow-x: hidden; }
<?php else : ?>
* html { overflow-y: hidden; }
<?php endif; ?>
body {
	font: 13px "Lucida Grande", "Lucida Sans Unicode", Tahoma, Verdana;
	border: none;
	margin: 0px;
	background: #dfe8f1;
}
form {
	margin: 3px 2px 0px 6px;
}
#wrap {
	clear: both;
	padding: 0px;
	width: 100%;
}
#images {
	position: absolute;
	clear: both;
	margin: 0px;
	padding: 15px 15px;
	width: <?php echo $images_width; ?>px;
}
#images img {
	background-color: rgb(209, 226, 239);
}
<?php echo $style; ?>
.attwrap, .attwrap * {
	margin: 0px;
	padding: 0px;
	border: 0px;
}
.imagewrap {
	margin-right: 5px;
	overflow: hidden;
	width: 128px;
}
.otherwrap {
	margin-right: 5px;
	overflow: hidden;
	background-color: #f9fcfe;
}
.otherwrap a {
	display: block;
}
.otherwrap a, .otherwrap a:hover, .otherwrap a:active, .otherwrap a:visited {
	color: blue;
}
.usingicon {
	padding: 0px;
	height: 96px;
	text-align: center;
	width: 128px;
}
.usingtext {
	padding: 3px;
	height: 90px;
	text-align: left;
	width: 122px;
}
.filetype {
	font-size: 80%;
	border-bottom: 3px double #89a
}
.imagewrap, .imagewrap img, .imagewrap a, .imagewrap a img, .imagewrap a:hover img, .imagewrap a:visited img, .imagewrap a:active img {
	text-decoration: none;
}
#upload-menu {
	background: #fff;
	margin: 0px;
	padding: 0;
	list-style: none;
	height: 2em;
	border-bottom: 1px solid #448abd;
	width: 100%;
}
#upload-menu li {
	float: left;
	margin: 0 0 0 .75em;
}
#upload-menu a {
	display: block;
	padding: 5px;
	text-decoration: none;
	color: #000;
	border-top: 3px solid #fff;
}
#upload-menu .current a {
	background: #dfe8f1;
	border-right: 2px solid #448abd;
}
#upload-menu a:hover {
	background: #dfe8f1;
	color: #000;
}
.tip {
	color: rgb(68, 138, 189);
	padding: 2px 1em;
}
.inactive {
	color: #fff;
	padding: 1px 3px;
}
.left {
	float: left;
}
.right {
	float: right;
}
.center {
	text-align: center;
}
#upload-menu li.spacer {
	margin-left: 40px;
}
#title, #descr {
	width: 99%;
	margin-top: 1px;
}
th {
	width: 4.5em;
}
#descr {
	height: 36px;
}
#buttons {
	margin-top: 2px;
	text-align: right;
}
.popup {
	margin: 4px 4px;
	padding: 1px;
	position: absolute;
	width: 114px;
	display: none;
	background-color: rgb(240, 240, 238);
	border-top: 2px solid #fff;
	border-right: 2px solid #ddd;
	border-bottom: 2px solid #ddd;
	border-left: 2px solid #fff;
	text-align: center;
}
.imagewrap .popup {
	opacity: .90;
	filter:alpha(opacity=90);
}
.otherwrap .popup {
	padding-top: 20px;
}
.popup a, .popup a:visited, .popup a:active {
	background-color: transparent;
	display: block;
	width: 100%;
	text-decoration: none;
	color: #246;
}
.popup a:hover {
	background-color: #fff;
	color: #000;
}
.caption {
	text-align: center;
}
#submit {
	margin: 1px;
	width: 99%;
}
#submit input, #submit input:focus {
	background: url( images/fade-butt.png );
	border: 3px double #999;
	border-left-color: #ccc;
	border-top-color: #ccc;
	color: #333;
	padding: 0.25em;
}
#submit input:active {
	background: #f4f4f4;
	border: 3px double #ccc;
	border-left-color: #999;
	border-top-color: #999;
}
.zerosize {
	width: 0px;
	height: 0px;
	overflow: hidden;
	position: absolute;
}
#links {
	margin: 3px 8px;
	line-height: 2em;
}
#links textarea {
	width: 95%;
	height: 4.5em;
}
<?php if ( $action == 'upload' ) { ?>
#attachmenttable { float: left; width: auto; }
div.imageattachmentoption { float: left; display: none; margin-left: 1em; }
#buttons { clear: both; }
form#upload-file .imageattachmentoption input { width: auto; }
<?php } ?>
</style>
</head>
<body>
<ul id="upload-menu">
<li<?php echo $current_1; ?>><a href="<?php echo basename(__FILE__) . "?action=upload&amp;post=$post&amp;all=$all&amp;start=$start"; ?>"><?php _e('Upload', 'fup'); ?></a></li>
<?php if ( $attachments = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_parent = '$post'") ) { ?>
<li<?php echo $current_2; ?>><a href="<?php echo basename(__FILE__) . "?action=view&amp;post=$post&amp;all=false"; ?>"><?php _e('Browse', 'fup'); ?></a></li>
<?php } ?>
<?php if ($wpdb->get_var("SELECT count(ID) FROM $wpdb->posts WHERE post_status = 'attachment'")) { ?>
<li<?php echo $current_3; ?>><a href="<?php echo basename(__FILE__) . "?action=view&amp;post=$post&amp;all=true"; ?>"><?php _e('Browse All', 'fup'); ?></a></li>
<?php } ?>
<li> </li>
<?php if ( $action == 'view' ) { ?>
<?php if ( false !== $back ) : ?>
<li class="spacer"><a href="<?php echo basename(__FILE__) . "?action=$action&amp;post=$post&amp;all=$all&amp;start=0"; ?>" title="<?php _e('First', 'fup'); ?>">|&laquo;</a></li>
<li><a href="<?php echo basename(__FILE__) . "?action=$action&amp;post=$post&amp;all=$all&amp;start=$back"; ?>">&laquo; <?php _e('Back', 'fup'); ?></a></li>
<?php else : ?>
<li class="inactive spacer">|&laquo;</li>
<li class="inactive">&laquo; <?php _e('Back', 'fup'); ?></li>
<?php endif; ?>
<?php if ( false !== $next ) : ?>
<li><a href="<?php echo basename(__FILE__) . "?action=$action&amp;post=$post&amp;all=$all&amp;start=$next"; ?>"><?php _e('Next &raquo;', 'fup'); ?></a></li>
<li><a href="<?php echo basename(__FILE__) . "?action=$action&amp;post=$post&amp;all=$all&amp;last=true"; ?>" title="<?php _e('Last', 'fup'); ?>">&raquo;|</a></li>
<?php else : ?>
<li class="inactive"><?php _e('Next', 'fup'); ?> &raquo;</li>
<li class="inactive">&raquo;|</li>
<?php endif; ?>
<?php } // endif not upload?>
</ul>
<?php if ( $action == 'view' ) : ?>
<div id="wrap">
<!--<div class="tip"><?php _e('You can drag and drop these items into your post. Click on one for more options.', 'fup'); ?></div>-->
<div id="images">
<?php echo $html; ?>
<?php echo $popups; ?>
</div>
</div>
<?php elseif ( $action == 'upload' ) : ?>
<div class="tip"></div>
<form enctype="multipart/form-data" id="uploadForm" method="post" action="<?php echo basename(__FILE__); ?>">
<table id="attachmenttable" style="width:99%;">
<tr>
<th scope="row" align="right"><label for="upload"><?php _e('File:', 'fup'); ?></label></th>
<td><input type="file" id="upload" name="image" onchange="updateIFrame();"/></td>
</tr>
<tr>
<th scope="row" align="right"><label for="title"><?php _e('Title:', 'fup'); ?></label></th>
<td><input type="text" id="title" name="imgtitle" /></td>
</tr>
<tr>
<th scope="row" align="right"><label for="descr"><?php _e('Description:', 'fup'); ?></label></th>
<td><input type="textarea" name="descr" id="descr" value="" /></td>
</tr>
</table>
<div class="imageattachmentoption" id="imageattachmentresize">
  <label for="imgresize_select">
   <input type="checkbox" name="imgresize_select" id="imgresize_select" <?php
   if (get_option(FUP_DEFAULT_LARGE_OPTION) > 0) { echo 'checked="checked" '; }
    ?>onchange="updateResizeOptions('imgresize')"/>
   <?php _e('Resize image', 'fup') ?>
  </label>
  <label for="imgresize_size">
   <input type="text" name="imgresize_size" id="imgresize_size" size="4" value="<?php
      echo get_option(FUP_DEFAULT_LARGE_OPTION); ?>" /><?php _e('px', 'fup') ?>
  </label><br />
  <label for="imgresize_side"><?php _e('Resize against: ', 'fup') ?>
   <select name="imgresize_side" id="imgresize_side">
    <option value="largest_side"<?php if (get_option(FUP_RESIZE_SIDE_OPTION) == 'largest_side') echo ' selected="selected"'; ?>><?php _e('largest side', 'fup') ?></option>
    <option value="width"<?php if (get_option(FUP_RESIZE_SIDE_OPTION) == 'width') echo ' selected="selected"'; ?>><?php _e('width', 'fup') ?></option>
    <option value="height"<?php if (get_option(FUP_RESIZE_SIDE_OPTION) == 'height') echo ' selected="selected"'; ?>><?php _e('height', 'fup') ?></option>
    <option value="smallest_side"<?php if (get_option(FUP_RESIZE_SIDE_OPTION) == 'smallest_side') echo ' selected="selected"'; ?>><?php _e('smallest side (crop)', 'fup') ?></option>
   </select>
  </label>
</div>
<div class="imageattachmentoption" id="imageattachmentthumb">
  <label for="thumbnail_select">
   <input type="checkbox" name="thumbnail_select" id="thumbnail_select" <?php
   if (get_option(FUP_DEFAULT_THUMB_OPTION) > 0) { echo 'checked="checked" '; }
    ?>onchange="updateResizeOptions('thumbnail')"/>
   <?php _e('Create a thumbnail', 'fup') ?>
  </label>
  <label for="thumbnail_size">
   <input type="text" name="thumbnail_size" id="thumbnail_size" size="4" value="<?php
      echo get_option(FUP_DEFAULT_THUMB_OPTION); ?>" /><?php _e('px', 'fup') ?>
  </label><br />
  <label for="thumbnail_side"><?php _e('Resize against: ', 'fup') ?>
   <select name="thumbnail_side" id="thumbnail_side">
    <option value="largest_side"<?php if (get_option(FUP_THUMB_SIDE_OPTION) == 'largest_side') echo ' selected="selected"'; ?>><?php _e('largest side', 'fup') ?></option>
    <option value="width"<?php if (get_option(FUP_THUMB_SIDE_OPTION) == 'width') echo ' selected="selected"'; ?>><?php _e('width', 'fup') ?></option>
    <option value="height"<?php if (get_option(FUP_THUMB_SIDE_OPTION) == 'height') echo ' selected="selected"'; ?>><?php _e('height', 'fup') ?></option>
    <option value="smallest_side"<?php if (get_option(FUP_THUMB_SIDE_OPTION) == 'smallest_side') echo ' selected="selected"'; ?>><?php _e('smallest side (crop)', 'fup') ?></option>
   </select>
  </label>
  <?php if (get_option(FUP_WATERMARK_PIC_OPTION) != '' &&
            file_exists(ABSPATH.get_option(FUP_WATERMARK_PIC_OPTION)) &&
            function_exists('imagecopymerge') &&
            get_option(FUP_WAT_OPT_PER_PIC_OPTION) == 'true') { ?>
  <br />
  <label for="watermark_location">
   <?php _e('Location of Watermark: ', 'fup'); ?>
   <select name="watermark_location" id="watermark_location">
    <option value="BR"<?php if (get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'BR') echo ' selected="selected"'; ?>><?php _e('Bottom Right', 'fup') ?></option>
    <option value="TR"<?php if (get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'TR') echo ' selected="selected"'; ?>><?php _e('Top Right', 'fup') ?></option>
   </select>
  </label>
  <br />
  <label for="watermark_rotation">
   <input type="checkbox" name="watermark_rotation" id="watermark_rotation" value="ROT" <?php if (get_option(FUP_DEFAULT_WAT_ORI_OPTION) == 'ROT') echo ' checked="checked" '; ?>/>
   <?php _e('Rotated Watermark', 'fup'); ?> 
  </label>
  <?php } ?>
</div>
<div id="buttons" class="submit">
  <input type="hidden" name="action" value="save" />
  <input type="hidden" name="post" value="<?php echo $post; ?>" />
  <input type="hidden" name="all" value="<?php echo $all; ?>" />
  <input type="hidden" name="start" value="<?php echo $start; ?>" />
  <?php wp_nonce_field( 'inlineuploading' ); ?>
  <div id="submit">
    <input type="submit" id="uploadsubmitbutton" value="<?php _e('Upload', 'fup'); ?>" />
  </div>
</div>
</form>
<?php elseif ( $action == 'links' ) : ?>
<div id="links">
<?php the_attachment_links($attachment); ?>
</div>
<?php endif; ?>
</body>
</html>
<?php if ( $action == 'upload' ) { ?>
<script type="text/javascript">
// <![CDATA[
//resizeIframe(parent.document.getElementById('uploading'));
// ]]>
</script>
<?php } ?>
