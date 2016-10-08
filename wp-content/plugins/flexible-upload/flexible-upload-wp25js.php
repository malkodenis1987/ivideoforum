<?php
/*
Flexible Upload plugin

WP 2.5+ specific Javascript code

Author: Antoine Choppin
Author URI: http://blog.japonophile.com/
*/

 require_once('../../../wp-config.php');
 require_once('../../../wp-admin/admin.php');
 require_once('flexible-upload.php');
 cache_javascript_headers(); ?>

theUploadForm = {

	formId: 0,

	setupForm: function() {
		var u = $('html-upload-ui');
		if (u) {
			u.innerHTML = '\
	<div id="addfield" style="float:left; clear: both; width:100%;">\
		[<a href="#" onclick="theUploadForm.addNewTable(); return false;"><?php _e('+add field', 'fup'); ?></a>]\
	</div>\
	<div id="buttons" class="fupsubmit">\
		<input type="hidden" name="from_tab" value="upload" />\
		<input type="hidden" name="action" value="upload" />\
<?php	global $post_id;
		if ( $post_id ) { ?>
		<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />\
<?php	} ?>
		<div class="fupsubmit">\
			<input type="submit" class="button" value="<?php _e('Upload &raquo;', 'fup'); ?>" onclick="return theUploadForm.checkBeforeSubmit();" />\
		</div>\
	</div>';
			this.addNewTable();
		}
	},

	addNewTable: function() {
		new Insertion.Before('addfield', this.uploadTable());
		this.updateIFrame($('upload' + this.formId));
		this.formId ++;
	},

	removeForm: function(ele) {
		var f = ele.parentNode.parentNode.parentNode;
		f.parentNode.removeChild(f);
	},

	uploadTable: function() {
		return '\
       <table>' +
       	(this.formId > 0 ? '\
           <tr>\
		    <td colspan="3">\
				    [<a href="#" onclick="theUploadForm.removeForm(this); return false;"><?php _e('- remove', 'fup'); ?></a>]\
				</td>\
			</tr>' : '') + '\
            <tr>\
                <th scope="row"><label for="upload"><?php _e('File', 'fup'); ?></label></th>\
                <td><input type="file" id="upload' + this.formId + '" name="image[]" onchange="theUploadForm.updateIFrame(this);"/></td>\
                <td rowspan="3" id="imageattachmentopt' + this.formId + '" class="imageattachmentoption">' + this.uploadOptions() + '</td>\
            </tr>\
            <tr>\
                <th scope="row"><label for="post_title"><?php _e('Title', 'fup'); ?></label></th>\
                <td><input type="text" id="post_title' + this.formId + '" name="post_title[]" value="<?php echo $attachment->post_title; ?>" /></td>\
            </tr>\
            <tr>\
                <th scope="row"><label for="post_content"><?php _e('Description', 'fup'); ?></label></th>\
                <td><textarea id="post_content' + this.formId + '" name="post_content[]" rows="3" cols="30"><?php echo $attachment->post_content; ?></textarea></td>\
            </tr>\
        </table>';
	},

	uploadOptions: function() {
		return '\
         <label for="imgresize_select' + this.formId + '">\
          <input type="checkbox" name="imgresize_select' + this.formId + '" id="imgresize_select' + this.formId + '" <?php
		if (get_option(FUP_DEFAULT_LARGE_OPTION) > 0) {
			echo 'checked="checked" ';
		} ?>onchange="theUploadForm.updateResizeOptions(\'imgresize\', \'' + this.formId + '\')"/>\
          <?php _e('Resize image', 'fup'); ?>\
         </label>\
         <label for="imgresize_size' + this.formId + '">\
          <input type="text" name="imgresize_size' + this.formId + '" id="imgresize_size' + this.formId + '" size="4" value="<?php
		echo get_option(FUP_DEFAULT_LARGE_OPTION); ?>" /><?php _e('px', 'fup'); ?></label><br />\
         <label for="imgresize_side' + this.formId + '"><?php _e('Resize against: ', 'fup'); ?>\
          <select name="imgresize_side' + this.formId + '" id="imgresize_side' + this.formId + '">\
           <option value="largest_side"<?php
		if (get_option(FUP_RESIZE_SIDE_OPTION) == 'largest_side') {
			echo ' selected="selected"';
		} ?>><?php _e('largest side', 'fup'); ?></option>\
           <option value="width"<?php
		if (get_option(FUP_RESIZE_SIDE_OPTION) == 'width') {
			echo ' selected="selected"';
		} ?>><?php _e('width', 'fup'); ?></option>\
           <option value="height"<?php
		if (get_option(FUP_RESIZE_SIDE_OPTION) == 'height') {
			echo ' selected="selected"';
		} ?>><?php _e('height', 'fup'); ?></option>\
           <option value="smallest_side"<?php
		if (get_option(FUP_RESIZE_SIDE_OPTION) == 'smallest_side') {
			echo ' selected="selected"';
		} ?>><?php _e('smallest side (crop)', 'fup'); ?></option>\
          </select>\
         </label><br />\
         <label for="thumbnail_select' + this.formId + '">\
          <input type="checkbox" name="thumbnail_select' + this.formId + '" id="thumbnail_select' + this.formId + '" <?php
		if (get_option(FUP_DEFAULT_THUMB_OPTION) > 0) {
			echo 'checked="checked" ';
		} ?>onchange="theUploadForm.updateResizeOptions(\'thumbnail\', \'' + this.formId + '\')"/>\
          <?php _e('Create a thumbnail', 'fup'); ?>\
         </label>\
         <label for="thumbnail_size' + this.formId + '">\
          <input type="text" name="thumbnail_size' + this.formId + '" id="thumbnail_size' + this.formId + '" size="4" value="<?php
		echo get_option(FUP_DEFAULT_THUMB_OPTION); ?>" /><?php _e('px', 'fup'); ?></label><br />\
         <label for="thumbnail_side' + this.formId + '"><?php _e('Resize against: ', 'fup'); ?>\
          <select name="thumbnail_side' + this.formId + '" id="thumbnail_side' + this.formId + '">\
           <option value="largest_side"<?php
		if (get_option(FUP_THUMB_SIDE_OPTION) == 'largest_side') {
			echo ' selected="selected"';
		} ?>><?php _e('largest side', 'fup'); ?></option>\
           <option value="width"<?php
		if (get_option(FUP_THUMB_SIDE_OPTION) == 'width') {
			echo ' selected="selected"';
		} ?>><?php _e('width', 'fup'); ?></option>\
           <option value="height"<?php
		if (get_option(FUP_THUMB_SIDE_OPTION) == 'height') {
			echo ' selected="selected"';
		} ?>><?php _e('height', 'fup'); ?></option>\
           <option value="smallest_side"<?php
		if (get_option(FUP_THUMB_SIDE_OPTION) == 'smallest_side') {
			echo ' selected="selected"';
		} ?>><?php _e('smallest side (crop)', 'fup'); ?></option>\
          </select>\
         </label><?php
		if (get_option(FUP_WATERMARK_PIC_OPTION) != '' &&
			file_exists(ABSPATH.get_option(FUP_WATERMARK_PIC_OPTION)) &&
			function_exists('imagecopymerge') &&
			get_option(FUP_WAT_OPT_PER_PIC_OPTION) == 'true') {
			?>\
         <br />\
         <label for="watermark_location' + this.formId + '">\
           <?php _e('Location of Watermark: ', 'fup'); ?>\
           <select name="watermark_location' + this.formId + '" id="watermark_location' + this.formId + '" onchange="if (this.value == \'NO\') { $(\'watermark_rotation\').disabled = \'disabled\' } else { $(\'watermark_rotation\').disabled = false }">\
            <option value="NO"<?php
			if (get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'NO') {
				echo ' selected="selected"';
			} ?>><?php _e('None', 'fup'); ?></option>\
            <option value="BR"<?php
			if (get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'BR') {
				echo ' selected="selected"';
			} ?>><?php _e('Bottom Right', 'fup'); ?></option>\
            <option value="TR"<?php
			if (get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'TR') {
				echo ' selected="selected"';
			} ?>><?php _e('Top Right', 'fup'); ?></option>\
           </select>\
          </label>\
          <br />\
          <label for="watermark_rotation' + this.formId + '">\
           <input type="checkbox" name="watermark_rotation' + this.formId + '" id="watermark_rotation' + this.formId + '" value="ROT"<?php
			if (get_option(FUP_DEFAULT_WAT_ORI_OPTION) == 'ROT') {
				echo ' checked="checked" ';
			} ?>/><?php _e('Rotated Watermark', 'fup'); ?>\
          </label><?php
		} /* if (get_option(FUP_WATERMARK_PIC_OPTION) ... */ ?>';
	},

	isImageAttachment: function(el) {
		var e = new RegExp('(jpg|jpeg|jpe|gif|png|bmp|tif|tiff|ico)$');
		return el.value.toLowerCase().match(e);
	},

	updateIFrame: function(el) {
		var id = el.id.replace('upload', '');
		id = (id == '' ? '0' : id);
		if (this.isImageAttachment(el)) {
			this.updateResizeOptions('thumbnail', id);
			this.updateResizeOptions('imgresize', id);
			$('imageattachmentopt' + id).style.display = '';
		}
		else {
			$('imageattachmentopt' + id).style.display = 'none';
		}
	},

	updateResizeOptions: function(prefix, id) {
		chkbox = $(prefix+'_select' + id);
		if (chkbox.checked) {
			$(prefix+'_size' + id).disabled = false;
			$(prefix+'_side' + id).disabled = false;
		}
		else {
			$(prefix+'_size' + id).disabled = 'disabled';
			$(prefix+'_side' + id).disabled = 'disabled';
		}
	},

	checkBeforeSubmit: function() {
		var elts = document.getElementsByName('image[]');
		for (x=0; elts[x]; x++) {
			var id = elts[x].id.replace('upload', '');
			id = (id == '' ? '0' : id);
			if (this.isImageAttachment(elts[x])) {
				if (($('imgresize_select' + id).checked) &&
					(!$('imgresize_size' + id).value)) {
					alert('<?php _e('Please enter the size to which the image should be resized.', 'fup'); ?>');
					return false;
				}
				else if (($('thumbnail_select' + id).checked) &&
					(!$('thumbnail_size' + id).value)) {
					alert('<?php _e('Please enter the thumbnail size.', 'fup'); ?>');
					return false;
				}
			}
		} // for
		return true;
	}
};

<?php if (get_option(FUP_DISABLE_WPAUTOP_OPTION) == 'true') { ?>
var switchEditors;
if (typeof(switchEditors) === "function") {
    switchEditors.pre_wpautop = function(content) { return content; }
    switchEditors.wpautop = function(content) { return content; }
}
<?php } ?>

// send html to the post excerpt
function send_to_excerpt(h) {
    var win = window.opener ? window.opener : window.dialogArguments;
    if ( !win )
        win = top;
    edExcerpt = document.getElementById('excerpt');
    if (edExcerpt) {
        try {
            win.edInsertContent(edExcerpt, h);
        } catch (e) {
            // Open the post excerpt box and retry
            jQuery(jQuery("#postexcerpt").get(0)).toggleClass('closed');
            save_postboxes_state('post');
            try {
                win.edInsertContent(edExcerpt, h);
            } catch (e2) {}
        }
    }
}

var prepareMediaItem;
if (typeof(prepareMediaItem) === "function") {
    old_prepareMediaItem = prepareMediaItem;
    prepareMediaItem = function(fileObj, serverData) {
        old_prepareMediaItem(fileObj, serverData);
        jQuery("#media-item-" + fileObj.id + " button.button[@name^='send']").after(" <button type='submit' class='button' value='2' name='send[" + fileObj.id + "]'><?php _e('Insert into Excerpt', 'fup') ?></button>");
    }
}

