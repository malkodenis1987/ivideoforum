<?php
/*
Flexible Upload plugin

WP 2.5 specific code

Author: Antoine Choppin
Author URI: http://blog.japonophile.com/
*/

if ( !is_admin() ) {
    return;
}

function fup_admin_header() {
    global $fup_rel_dir;
    wp_enqueue_script('fup_swfupload',
                      '/'.$fup_rel_dir.'flexible-upload-wp25js.php',
                      array('prototype'), false);
}

add_action('admin_print_scripts', 'fup_admin_header');

function fup_media_upload_image() {

    // Need these scripts (actually, only handlers.js, for the
    // prepareMediaItem function)
    //wp_deregister_script('swfupload');
    //wp_deregister_script('swfupload-degrade');
    //wp_deregister_script('swfupload-queue');
    //wp_deregister_script('swfupload-handlers');

    if ( !empty($_FILES) ) {
        if ((count($_FILES['image']['error']) == 1) &&
            ($_FILES['image']['error'][0] == 4)) {
            // Silently ignore error when no file has been uploaded
            unset($_FILES);
        }
        else {
            // Upload File button was clicked
            $id = fup_media_handle_upload('async-upload', $_REQUEST['post_id']);
            unset($_FILES);
            if ( is_wp_error($id) ) {
                $errors['upload_error'] = $id;
                $id = false;
            }
        }
    }

    if ( !empty($_POST) ) {
        $return = media_upload_form_handler();

        if ( is_string($return) )
            return $return;
        if ( is_array($return) )
            $errors = $return;
    }

    return wp_iframe( 'fup_media_upload_form', $errors, $_REQUEST['post_id'] );
}

function fup_set_actions() {
    remove_action('media_upload_image', 'media_upload_image');
    add_action('media_upload_image', 'fup_media_upload_image');
    add_action('admin_head_fup_media_upload_form', 'media_admin_css');
    remove_filter('media_send_to_editor', 'image_media_send_to_editor');
    add_filter('media_send_to_editor', 'fup_media_send_to_editor', 10, 3);
}

add_action('admin_init', 'fup_set_actions');

function fup_media_handle_upload($file_id, $post_id, $post_data = array()) {

    global $fup_key;
    $overrides = array('test_form'=>false);
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
        $title = preg_replace('/\.[^.]+$/', '', basename($file));
        $content = '';

        // set title and caption, OR (if not sent)
        // use image exif/iptc data for title and caption defaults if possible
        $image_meta = @wp_read_image_metadata($file);

        if (isset($_POST['post_title'][$key]) &&
            (trim($_POST['post_title'][$key]) != "")) {
            $title = $_POST['post_title'][$key];
        }
        elseif (isset($image_meta['title']) &&
                (trim($image_meta['title']) != "")) {
            $title = $image_meta['title'];
        }
        if (isset($_POST['post_content'][$key]) &&
            (trim($_POST['post_content'][$key]) != "")) {
            $content = $_POST['post_content'][$key];
        }
        elseif (isset($image_meta['caption']) &&
                (trim($image_meta['caption']) != "")) {
            $content = $image_meta['caption'];
        }

        // Construct the attachment array
        $attachment = array_merge( array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $post_id,
            'post_title' => $title,
            'post_content' => $content,
        ), $post_data );

        $fup_key = sprintf('%d',$key);

        // Resize image and create thumbnail if applicable
        fup_resize_and_thumbnail($file);

        // Save the data
        $id = wp_insert_attachment($attachment, $file, $post_parent);
        if ( !is_wp_error($id) ) {
            wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
            $successed[] = $filename;
        }
        else {
            $errors[] = "File $key: " . $file['error'];
            continue;
        }
    }

    if (empty($successed)) {
        return new wp_error( 'upload_error', implode($errors, ', ') );
    }

    return $post_id;
}

add_filter('wp_generate_attachment_metadata',
           'fup_update_attachment_metadata');

function fup_media_upload_form($errors = null, $id = null) {

    media_upload_header();
    $post_id = intval($_REQUEST['post_id']);
    $form_action_url = get_option('siteurl') . "/wp-admin/media-upload.php?type=image&tab=type&post_id=$post_id";
?>

<form enctype="multipart/form-data" method="post" action="<?php echo attribute_escape($form_action_url); ?>" class="media-upload-form type-form" id="image-form">
<input type="hidden" name="post_id" id="post_id" value="<?php echo $post_id; ?>" />
<?php wp_nonce_field('media-form'); ?>
<div id="media-upload-error">
<?php if (isset($errors['upload_error']) && is_wp_error($errors['upload_error'])) { ?>
        <?php echo $errors['upload_error']->get_error_message(); ?>
<?php } ?>
</div>

<div id="html-upload-ui">
</div>
<script type="text/javascript">
<!--
theUploadForm.setupForm();
-->
</script>

<script type="text/javascript">
<!--
jQuery(function($){
        var preloaded = $(".media-item.preloaded");
        if ( preloaded.length > 0 ) {
                preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
        }
        updateMediaForm();
});
-->
</script>

<?php if ( $id && (($mediaitems = get_media_items( $id, $errors )) != "") ) : ?>
<div id="media-items">
<?php echo $mediaitems; ?>
</div>
<input type="submit" class="button savebutton" name="save" value="<?php _e('Save all changes'); ?>" />

<?php endif ; ?>
</form>
<?php
}

/**
 * fup_print_css
 */
function fup_postupload_stylesheet() {
?>
<style type='text/css' media='screen'>
    #media-upload .imageattachmentoption br { line-height:0; }
    #media-upload tr.image-caption td.field { text-align: center; }
    #media-upload tr.image-link td.field { text-align: center; }
</style>
<?php
}

add_action('admin_print_scripts', 'fup_postupload_stylesheet');

function fup_intermediate_image_sizes($sizes) {
    return array();
}

add_filter('intermediate_image_sizes', 'fup_intermediate_image_sizes');

/**
 * fup_image_downsize
 *
 * This function is there only to work around a bug in WP2.5
 */
function fup_image_downsize($image_downsize, $id, $size) {

    if ($size == 'thumbnail') {
        // fall back to the old thumbnail
        // This line is buggy:
        //if ( $thumb_file = wp_get_attachment_thumb_file() && $info = getimagesize($thumb_file) ) {
        // This is the proper code:
        if (($thumb_file = wp_get_attachment_thumb_file($id)) &&
            ($info = getimagesize($thumb_file))) {
            $img_url = wp_get_attachment_url($id);
            $img_url = str_replace(basename($img_url),
                                   basename($thumb_file), $img_url);
            $width = $info[0];
            $height = $info[1];
            return array($img_url, $width, $height);
        }
    }

    return $image_downsize;
}

add_filter('image_downsize', 'fup_image_downsize', 10, 3);

function fup_attachment_fields_to_edit($form_fields, $post) {

    if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
        $thumb = wp_get_attachment_thumb_url($post->ID);

        if (get_option(FUP_ALIGNMENT_MODE_OPTION)!='none') {
            $alignhtml = "
                <input type='radio' name='attachments[$post->ID][align]' id='image-align-none-$post->ID'
                       ".(get_option(FUP_DEFAULT_ALIGN_OPTION)=='none' ? "checked='checked' " : "")."value='none' />
                <label for='image-align-none-$post->ID' class='align image-align-none-label'>" . __('None') . "</label>
                <input type='radio' name='attachments[$post->ID][align]' id='image-align-left-$post->ID'
                       ".(get_option(FUP_DEFAULT_ALIGN_OPTION)=='left' ? "checked='checked' " : "")."value='left' />
                <label for='image-align-left-$post->ID' class='align image-align-left-label'>" . __('Left') . "</label>";
            if (get_option(FUP_ALIGNMENT_MODE_OPTION) == 'css') {
                $alignhtml .= "
                <input type='radio' name='attachments[$post->ID][align]' id='image-align-center-$post->ID'
                       ".(get_option(FUP_DEFAULT_ALIGN_OPTION)=='center' ? "checked='checked' " : "")."value='center' />
                <label for='image-align-center-$post->ID' class='align image-align-center-label'>" . __('Center') . "</label>";
            }
            $alignhtml .= "
                <input type='radio' name='attachments[$post->ID][align]' id='image-align-right-$post->ID'
                       ".(get_option(FUP_DEFAULT_ALIGN_OPTION)=='right' ? "checked='checked' " : "")."value='right' />
                <label for='image-align-right-$post->ID' class='align image-align-right-label'>" . __('Right') . "</label>\n";

            $form_fields['align'] = array(
                'label' => __('Align:', 'fup'),
                'input' => 'html',
                'html'  => $alignhtml,
            );
        }
        if ($thumb) {
            $form_fields['image-size'] = array(
                'label' => __('Show:', 'fup'),
                'input' => 'html',
                'html'  => "
                <input type='radio' name='attachments[$post->ID][image-size]' id='image-size-thumb-$post->ID'
                        checked='checked' value='thumbnail' />
                <label for='image-size-thumb-$post->ID'>" . __('Thumbnail') . "</label>
                <input type='radio' name='attachments[$post->ID][image-size]' id='image-size-full-$post->ID'
                       value='full' />
                <label for='image-size-full-$post->ID'>" . __('Full size') . "</label>",
            );
        }
        if (get_option(FUP_ALIGNMENT_MODE_OPTION) == 'css') {
            $captionhtml = "
                <input type='radio' name='attachments[$post->ID][caption]' id='image-caption-title-$post->ID'
                       ".(get_option(FUP_DEFAULT_CAPTION_OPTION)=='title' ? "checked='checked' " : "")."value='title' />
                <label for='image-caption-title-$post->ID' class='caption image-caption-title-label'>" . __('Title', 'fup') . "</label>
                <input type='radio' name='attachments[$post->ID][caption]' id='image-caption-description-$post->ID'
                       ".(get_option(FUP_DEFAULT_CAPTION_OPTION)=='description' ? "checked='checked' " : "")."value='description' />
                <label for='image-caption-description-$post->ID' class='caption image-caption-description-label'>" . __('Description', 'fup') . "</label>
                <input type='radio' name='attachments[$post->ID][caption]' id='image-caption-longdesc-$post->ID'
                       ".(get_option(FUP_DEFAULT_CAPTION_OPTION)=='longdesc' ? "checked='checked' " : "")."value='longdesc' />
                <label for='image-caption-longdesc-$post->ID' class='caption image-caption-longdesc-label'>" . __('Long description', 'fup') . "</label>
                <input type='radio' name='attachments[$post->ID][caption]' id='image-caption-none-$post->ID'
                       ".(get_option(FUP_DEFAULT_CAPTION_OPTION)=='none' ? "checked='checked' " : "")."value='none' />
                <label for='image-caption-none-$post->ID' class='caption image-caption-none-label'>" . __('None', 'fup') . "</label>\n";
            $form_fields['image-caption'] = array(
                'label' => __('Caption:', 'fup'),
                'input' => 'html',
                'html'  => $captionhtml
            );
        }
    }
    return $form_fields;
}

add_filter('attachment_fields_to_edit', 'fup_attachment_fields_to_edit', 11, 2);

function fup_media_send_to_editor($html, $attachment_id, $attachment) {

    $post =& get_post($attachment_id);

    if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
        // Alignment
        $CSSalignmap = array('left' => 'alignleft',
                             'right' => 'alignright',
                             'center' => 'centered');
        $css_alignment = "";
        $img_alignment = "";
        if (get_option(FUP_ALIGNMENT_MODE_OPTION) == 'css') {
            $css_alignment = $CSSalignmap[$attachment['align']];
        }
        elseif (get_option(FUP_ALIGNMENT_MODE_OPTION) == 'img') {
            $img_alignment = ' align="'.attribute_escape($attachment['align']).'"';
        }

        // Caption
        if ((get_option(FUP_ALIGNMENT_MODE_OPTION) == 'css') && 
            (isset($attachment['caption']))) {
            $show_caption = true;
            switch ($attachment['caption']) {
                case 'title':
                    $caption_text = $attachment['post_title'];
                    break;
                case 'description':
                    $caption_text = $attachment['post_excerpt'];
                    break;
                case 'longdesc':
                    $caption_text = $attachment['post_content'];
                    break;
                default:
                    $show_caption = false;
            }
        }
        else {
            $show_caption = false;
        }

        // Image source
        list($img_src, $width, $height) =
            image_downsize($attachment_id, $attachment['image-size']);
        $hwstring = image_hwstring($width, $height);
        $html = '<img src="'.attribute_escape($img_src)
               .'" alt="'
               .((get_option(FUP_IMAGE_ALT_OPTION) == 'title') ?
                  attribute_escape($attachment['post_title']) :
                 ((get_option(FUP_IMAGE_ALT_OPTION) == 'description') ?
                   attribute_escape($attachment['post_excerpt']) :
                  ((get_option(FUP_IMAGE_ALT_OPTION) == 'longdesc') ?
                    attribute_escape($attachment['post_content']) : "")))
               .'"'.$img_alignment.' '.$hwstring
               .'class="attachment wp-att-'.attribute_escape($attachment_id)
               .($show_caption ? "" : " $css_alignment").'" />';

        // Link
        $url = $attachment['url'];
        if ($url != "") {
            $imgtag = $html;
            $html = '<a href="'.attribute_escape($url).'" ';
            if ($url == attribute_escape(wp_get_attachment_url($post->ID))) {
                // Link to file
                $html .= fup_get_link_target($post->post_parent);
            }
            $html .= 'title="'
                     .((get_option(FUP_IMAGE_TITLE_OPTION) == 'title') ?
                        attribute_escape($attachment['post_title']) :
                       ((get_option(FUP_IMAGE_TITLE_OPTION) == 'description') ?
                         attribute_escape($attachment['post_excerpt']) :
                        ((get_option(FUP_IMAGE_TITLE_OPTION) == 'longdesc') ?
                          attribute_escape($attachment['post_content']) : "")))
                     .'">'.$imgtag.'</a>';
        }

        // Caption
        if ($show_caption) {
            $html = '<div class="imageframe '.$css_alignment.'" '
                    .'style="width:'.$width.'px;">'
                    .$html.'<div class="imagecaption">'
                    .attribute_escape(str_replace(
                        array('\\\'', '\\"', '\\\\'), array('\'', '"', '\\'),
                        $caption_text)).'</div></div>';
        }

        // Centering
        if ($attachment['align'] == 'center') {
            $tag = ($show_caption ? "div" : "p");
            $html = '<'.$tag.' style="text-align: center;">'.$html.'</'.$tag.'>';
        }

        if ((int)$_POST['send'][$attachment_id] == 2) {
            media_send_to_excerpt($html);
            $html = null;
        }
    }

    return $html;
}

if (get_option(FUP_DISABLE_WPAUTOP_OPTION) == 'true') {
    remove_filter('the_content', 'wpautop');
    remove_filter('comment_text', 'wpautop');
}

function media_send_to_excerpt($html) {
        ?>
<script type="text/javascript">
<!--
top.send_to_excerpt('<?php echo addslashes($html); ?>');
top.tb_remove();
-->
</script>
        <?php
        exit;
}

?>
