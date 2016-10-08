<?php
/*
Flexible Upload plugin

WP 2.1+ specific Javascript code

Author: Antoine Choppin
Author URI: http://blog.japonophile.com/
*/

 require_once('../../../wp-config.php');
 require_once('../../../wp-admin/admin.php');
 require_once('flexible-upload.php');
 cache_javascript_headers(); ?>

addLoadEvent( function() {
	theFileList = {
		currentImage: {ID: 0},
		nonce: '',
		tab: '',
		postID: 0,

		initializeVars: function() {
			this.urlData  = document.location.href.split('?');
			this.params = this.urlData[1].toQueryParams();
			this.postID = this.params['post_id'];
			this.tab = this.params['tab'];
			this.style = this.params['style'];
			this.ID = this.params['ID'];
			if ( !this.style )
				this.style = 'default';
			var nonceEl = $('nonce-value');
			if ( nonceEl )
				this.nonce = nonceEl.value;
			if ( this.ID ) {
				this.grabImageData( this.ID );
				this.imageView( this.ID );
			}
		},

		initializeLinks: function() {
			if ( this.ID )
				return;
			$$('a.file-link').each( function(i) {
				var id = i.id.split('-').pop();
				if (i.onclick == null) {
					i.onclick = function(e) { theFileList[ 'inline' == theFileList.style ? 'imageView' : 'editView' ](id, e); }
				}
			} );
		},

		grabImageData: function(id) {
			if ( id == this.currentImage.ID )
				return;
			var thumbEl = $('attachment-thumb-url-' + id);
			if ( thumbEl ) {
				this.currentImage.thumb = ( 0 == id ? '' : thumbEl.value );
				this.currentImage.thumbBase = ( 0 == id ? '' : $('attachment-thumb-url-base-' + id).value );
				if (this.currentImage.thumb) {
					this.currentImage.thumbPreload = new Image();
					this.currentImage.thumbPreload.src = this.currentImage.thumb;
				}
			} else {
				this.currentImage.thumb = false;
			}
			this.currentImage.src = ( 0 == id ? '' : $('attachment-url-' + id).value );
			this.currentImage.srcBase = ( 0 == id ? '' : $('attachment-url-base-' + id).value );

			if (this.currentImage.src) {
				this.currentImage.imagePreload = new Image();
				this.currentImage.imagePreload.src = this.currentImage.src;
			}

			this.currentImage.page = ( 0 == id ? '' : $('attachment-page-url-' + id).value );
			this.currentImage.title = ( 0 == id ? '' : $('attachment-title-' + id).value );
			this.currentImage.description = ( 0 == id ? '' : $('attachment-description-' + id).value );
			var widthEl = $('attachment-width-' + id);
			if ( widthEl ) {
				this.currentImage.width = ( 0 == id ? '' : widthEl.value );
				this.currentImage.height = ( 0 == id ? '' : $('attachment-height-' + id).value );
			} else {
				this.currentImage.width = false;
				this.currentImage.height = false;
			}
			this.currentImage.isImage = ( 0 == id ? 0 : $('attachment-is-image-' + id).value );
			this.currentImage.ID = id;
		},

		imageView: function(id, e) {
			this.prepView(id);
			var h = '';

			h += "<div id='upload-file'>";
			if ( this.ID ) {
				var params = $H(this.params);
				params.ID = '';
				params.action = '';
				h += "<a href='" + this.urlData[0] + '?' + params.toQueryString() + "' title='<?php echo attribute_escape(__('Browse your files', 'fup')); ?>' class='back'><?php echo attribute_escape(__('&laquo; Back', 'fup')); ?></a>";
			} else {
				h += "<a href='#' onclick='return theFileList.cancelView();'  title='<?php echo attribute_escape(__('Browse your files', 'fup')); ?>' class='back'><?php echo attribute_escape(__('&laquo; Back', 'fup')) ?></a>";
			}
			h += "<div id='file-title'>"
			if ( 0 == this.currentImage.isImage )
				h += "<h2><a href='" + this.currentImage.srcBase + this.currentImage.src + "' onclick='return false;' title='<?php echo attribute_escape(__('Direct link to file', 'fup')); ?>'>" + this.currentImage.title + "</a></h2>";
			else
				h += "<h2>" + this.currentImage.title + "</h2>";
			h += " &#8212; <span>";
			h += "<a href='#' onclick='return theFileList.editView(" + id + ");'><?php echo attribute_escape(__('Edit', 'fup')); ?></a>"
			h += "</span>";
			h += '</div>'
			h += "<div id='upload-file-view'>";
			if ( 1 == this.currentImage.isImage ) {
				h += "<a href='" + this.currentImage.srcBase + this.currentImage.src + "' onclick='return false;' title='<?php echo attribute_escape(__('Direct link to file', 'fup')); ?>'>";
				h += "<img src='" + ( this.currentImage.thumb ? this.currentImage.thumb : this.currentImage.src ) + "' alt='" + this.currentImage.title + "' width='" + this.currentImage.width + "' height='" + this.currentImage.height + "' />";
				h += "</a>";
			} else
				h += '&nbsp;';
			h += "</div>";

			var win = window.opener ? window.opener : window.dialogArguments;
			if ( !win )
				win = top;
			tinyMCE = win.tinyMCE;
			var useWysiwygEditor = ( typeof tinyMCE != 'undefined' );

			h += "<form name='uploadoptions' id='uploadoptions'>";
			h += "<div>";
			var display = [];
			var checked = 'display-title';
			if ( 1 == this.currentImage.isImage ) {
				checked = 'display-full';
				if ( this.currentImage.thumb ) {
					display.push("<label for='display-thumb'><input type='radio' name='display' id='display-thumb' value='thumb' /> <?php echo attribute_escape(__('Thumbnail', 'fup')); ?></label><br />");
					checked = 'display-thumb';
				}
				display.push("<label for='display-full'><input type='radio' name='display' id='display-full' value='full' /> <?php echo attribute_escape(__('Full size', 'fup')); ?></label>");
			} else if ( this.currentImage.thumb ) {
				display.push("<label for='display-thumb'><input type='radio' name='display' id='display-thumb' value='thumb' /> <?php echo attribute_escape(__('Icon', 'fup')); ?></label>");
			}
			if ( display.length ) {
				display.push("<br /><label for='display-title'><input type='radio' name='display' id='display-title' value='title' /> <?php echo attribute_escape(__('Title', 'fup')); ?></label>");

				h += "<div class='uploadoptionbox'><p class='uploadoptionboxtitle'><?php echo attribute_escape(__('Show:', 'fup')); ?></p>";
				$A(display).each( function(i) { h += i; } );
				h += "</div>";
				<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)!='none') { ?>
				h += "<div class='uploadoptionbox'><p class='uploadoptionboxtitle'><?php echo attribute_escape(__('Align:', 'fup')); ?></p>";
				h += "<label for='align-none'><input type='radio' name='align' id='align-none' value='none' <?php if (get_option(FUP_DEFAULT_ALIGN_OPTION)=='none') { echo "checked='checked' "; } ?>/> <?php echo attribute_escape(__('No', 'fup')); ?></label>";
				h += "<br /><label for='align-left'><input type='radio' name='align' id='align-left' value='left' <?php if (get_option(FUP_DEFAULT_ALIGN_OPTION)=='left') { echo "checked='checked' "; } ?>/> <?php echo attribute_escape(__('Left', 'fup')); ?></label>";
				<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
				h += "<br /><label for='align-center'><input type='radio' name='align' id='align-center' value='center' <?php if (get_option(FUP_DEFAULT_ALIGN_OPTION)=='center') { echo "checked='checked' "; } ?>/> <?php echo attribute_escape(__('Center', 'fup')); ?></label>";
				<?php } ?>
				h += "<br /><label for='align-right'><input type='radio' name='align' id='align-right' value='right' <?php if (get_option(FUP_DEFAULT_ALIGN_OPTION)=='right') { echo "checked='checked' "; } ?>/> <?php echo attribute_escape(__('Right', 'fup')); ?></label>";
				h += "</div>";
				<?php } ?>
				<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
				if ( !useWysiwygEditor ) {
					var nodesc  = (this.currentImage.description == "" ? "checked='checked' " : "");
					var yesdesc = (this.currentImage.description == "" ? "" : "checked='checked' ");
					h += "<div class='uploadoptionbox'><p class='uploadoptionboxtitle'><?php echo attribute_escape(__('Caption:', 'fup')); ?></p>";
					h += "<label for='caption-no'><input type='radio' name='caption' id='caption-no' value='no' "+nodesc+"/> <?php echo attribute_escape(__('No', 'fup')); ?></label>";
					h += "<br /><label for='caption-yes'><input type='radio' name='caption' id='caption-yes' value='yes' "+yesdesc+"/> <?php echo attribute_escape(__('Yes', 'fup')); ?></label>";
					h += "</div>";
				}
				<?php } ?>
			}

			h += "<div class='uploadoptionbox'><p class='uploadoptionboxtitle'><?php echo attribute_escape(__('Link to:', 'fup')); ?></p>";
			h += "<label for='link-file'><input type='radio' name='link' id='link-file' value='file' checked='checked'/> <?php echo attribute_escape(__('File', 'fup')); ?></label><br />";
			h += "<label for='link-page'><input type='radio' name='link' id='link-page' value='page' /> <?php echo attribute_escape(__('Page', 'fup')); ?></label><br />";
			h += "<label for='link-none'><input type='radio' name='link' id='link-none' value='none' /> <?php echo attribute_escape(__('None', 'fup')); ?></label>";
			h += "</div>";

			h += "<p class='submit uploadoptionsubmit'>";
			h += "<input type='button' class='button' id='uploadsubmitbutton' name='send' onclick='theFileList.sendToEditor(" + id + ")' value='<?php echo attribute_escape(__('Send to editor &raquo;', 'fup')); ?>' />";
			h += "</p>";
			h += "</form>";

			h += "</div>";

			new Insertion.Top('upload-content', h);
			var displayEl = $(checked);
			if ( displayEl )
				displayEl.checked = true;

			if (e) Event.stop(e);
			this.resizeIFrame(parent.document.getElementById('uploading'));
			return false;
		},

		editView: function(id, e) {
			this.prepView(id);
			var h = '';

			var action = 'upload.php?style=' + this.style + '&amp;tab=upload';
			if ( this.postID )
				action += '&amp;post_id=' + this.postID;

			h += "<form id='upload-file' method='post' action='" + action + "'>";
			if ( this.ID ) {
				var params = $H(this.params);
				params.ID = '';
				params.action = '';
				h += "<a href='" + this.urlData[0] + '?' + params.toQueryString() + "'  title='<?php echo attribute_escape(__('Browse your files', 'fup')); ?>' class='back'><?php echo attribute_escape(__('&laquo; Back', 'fup')); ?></a>";
			} else {
				h += "<a href='#' onclick='return theFileList.cancelView();'  title='<?php echo attribute_escape(__('Browse your files', 'fup')); ?>' class='back'><?php echo attribute_escape(__('&laquo; Back', 'fup')); ?></a>";
			}
			h += "<div id='file-title'>"
			if ( 0 == this.currentImage.isImage )
				h += "<h2><a href='" + this.currentImage.srcBase + this.currentImage.src + "' onclick='return false;' title='<?php echo attribute_escape(__('Direct link to file', 'fup')); ?>'>" + this.currentImage.title + "</a></h2>";
			else
				h += "<h2>" + this.currentImage.title + "</h2>";
			h += " &#8212; <span>";
			h += "<a href='#' onclick='return theFileList.imageView(" + id + ");'><?php echo attribute_escape(__('Insert', 'fup')); ?></a>"
			h += "</span>";
			h += '</div>'
			h += "<div id='upload-file-view' class='alignleft'>";
			if ( 1 == this.currentImage.isImage ) {
				h += "<a href='" + this.currentImage.srcBase + this.currentImage.src + "' onclick='return false;' title='<?php echo wp_specialchars(__('Direct link to file', 'fup')); ?>'>";
				h += "<img src='" + ( this.currentImage.thumb ? this.currentImage.thumb : this.currentImage.src ) + "' alt='" + this.currentImage.title + "' width='" + this.currentImage.width + "' height='" + this.currentImage.height + "' />";
				h += "</a>";
			} else
				h += '&nbsp;';
			h += "</div>";


			h += "<table><col /><col class='widefat' /><tr>"
			h += "<th scope='row'><label for='url'><?php echo attribute_escape(__('URL', 'fup')); ?></label></th>";
			h += "<td><input type='text' id='url' class='readonly' value='" + this.currentImage.srcBase + this.currentImage.src + "' readonly='readonly' /></td>";
			h += "</tr><tr>";
			h += "<th scope='row'><label for='post_title'><?php echo attribute_escape(__('Title', 'fup')); ?></label></th>";
			h += "<td><input type='text' id='post_title' name='post_title' value='" + this.currentImage.title + "' /></td>";
			h += "</tr><tr>";
			h += "<th scope='row'><label for='post_content'><?php echo attribute_escape(__('Description', 'fup')); ?></label></th>";
			h += "<td><textarea name='post_content' id='post_content'>" + this.currentImage.description + "</textarea></td>";
			h += "</tr><tr id='buttons' class='submit'><td colspan='2'><input type='button' id='delete' name='delete' class='delete alignleft' value='<?php echo attribute_escape(__('Delete File', 'fup')); ?>' onclick='theFileList.deleteFile(" + id + ");' />";
			h += "<input type='hidden' name='from_tab' value='" + this.tab + "' />";
			h += "<input type='hidden' name='action' id='action-value' value='save' />";
			h += "<input type='hidden' name='ID' value='" + id + "' />";
			h += "<input type='hidden' name='_wpnonce' value='" + this.nonce + "' />";
			h += "<div class='submit'><input type='submit' value='<?php echo attribute_escape(__('Save &raquo;', 'fup')); ?>' /></div>";
			h += "</td></tr></table></form>";

			new Insertion.Top('upload-content', h);
			if (e) Event.stop(e);
			return false;		
		},

		prepView: function(id) {
			this.cancelView( true );
			var filesEl = $('upload-files');
			if ( filesEl )
				filesEl.hide();
			var navEl = $('current-tab-nav');
			if ( navEl )
				navEl.hide();
			this.grabImageData(id);
		},

		cancelView: function( prep ) {
			if ( !prep ) {
				var filesEl = $('upload-files');
				if ( filesEl )
					Element.show(filesEl);
				var navEl = $('current-tab-nav');
				if ( navEl )
					Element.show(navEl);
			}
			if ( !this.ID )
				this.grabImageData(0);
			var div = $('upload-file');
			if ( div )
				Element.remove(div);
			return false;
		},

		sendToEditor: function(id) {
			this.grabImageData(id);
			var link = '';
			var display = '';
			var align = '';
			var cssalignment = '';
			var imgalignment = '';
			var caption = false;
			var h = '';

			link = $A(document.forms.uploadoptions.elements.link).detect( function(i) { return i.checked; } ).value;
			displayEl = $A(document.forms.uploadoptions.elements.display).detect( function(i) { return i.checked; } )
			if ( displayEl )
				display = displayEl.value;
			else if ( 1 == this.currentImage.isImage )
				display = 'full';
			<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)!='none') { ?>
			alignEl = $A(document.forms.uploadoptions.elements.align).detect( function(i) { return i.checked; } )
			if ( alignEl )
				align = alignEl.value;
			else
				align = 'left';
			<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
			if ( align == 'left' || align == 'right' || align == 'center' )
				cssalignment = ' imgalign'+align;
			<?php } else if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='img') { ?>
			if ( align == 'left' || align == 'right' )
				imgalignment = 'align="' + align + '" ';
			<?php } ?>
			<?php } ?>

			<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
			captionEl = $A(document.forms.uploadoptions.elements.caption).detect( function(i) { return i.checked; } )
			if ( captionEl )
				caption = ( captionEl.value == 'yes' );
			else
				caption = false;
			<?php } ?>

			if ( 'none' != link ) {
				h += '<a href=';
				if ( 'file' == link ) {
					h += '"' + this.currentImage.srcBase
						 + this.currentImage.src + '" ';
					if ( 1 == this.currentImage.isImage ) {
						h += '<?php echo fup_get_link_target(); ?>';
					}
				}
				else {
					h += '"' + this.currentImage.page + '" '
						 + 'rel="attachment wp-att-'
						 + this.currentImage.ID + '" ';
				}
				<?php if (get_option(FUP_IMAGE_TITLE_OPTION) != 'none') { ?>
				h += 'title="' + this.currentImage.<?php echo get_option(FUP_IMAGE_TITLE_OPTION); ?> + '"';
				<?php } ?>
				h += '>';
			}
			if ( display && 'title' != display ) {
				h += '<img src=';
				if ( 'thumb' == display ) {
					h += '"' + this.currentImage.thumbBase
						 + this.currentImage.thumb + '" '
						 + 'width="' + this.currentImage.thumbPreload.width + '" '
						 + 'height="' + this.currentImage.thumbPreload.height + '" ';
				}
				else {
					h += '"' + this.currentImage.srcBase
						 + this.currentImage.src + '" '
						 + 'width="' + this.currentImage.imagePreload.width + '" '
						 + 'height="' + this.currentImage.imagePreload.height + '" ';
				}
				h += 'alt="' + this.currentImage.title + '" ';
				if ( !caption ) {
					h += 'class="imageframe' + cssalignment + '" ' + imgalignment;
				}
				h += '/>';
			}
			else { // no image
				h += this.currentImage.title;
			}
			if ( 'none' != link ) {
				h += '</a>';
			}

			<?php if (get_option(FUP_ALIGNMENT_MODE_OPTION)=='css') { ?>
			if ( caption ) {
				imagetag = h;
				h = '<div class="imageframe' + cssalignment + '" '
					 + 'style="width:';
				if ( 'thumb' == display ) {
					h += this.currentImage.thumbPreload.width;
				}
				else {
					h += this.currentImage.imagePreload.width;
				}
				h += 'px;">' + imagetag + '<div class="imagecaption">';
				if ( this.currentImage.description ) {
					h += this.currentImage.description;
				}
				else {
					h += this.currentImage.title;
				}
				h += '</div></div>';
			} // caption

			if ( align == 'center' ) {
				if ( caption ) {
					h = '<div style="text-align: center;">' + h + '</div>';
				}
				else {
					h = '<p style="text-align: center;">' + h + '</p>';
				}
			}
			<?php } ?>

			var win = window.opener ? window.opener : window.dialogArguments;
			if ( !win )
				win = top;
			tinyMCE = win.tinyMCE;
                        var oEditor = win.FCKeditorAPI;
			if ( typeof tinyMCE != 'undefined' && tinyMCE.getInstanceById('content') ) {
				tinyMCE.selectedInstance.getWin().focus();
				tinyMCE.execCommand('mceInsertContent', false, h);
			} else if (oEditor != null && typeof oEditor != 'undefined') {
				oEditor.GetInstance('content').InsertHtml(h);
			} else
				win.edInsertContent(win.edCanvas, h);
			if ( !this.ID )
				this.cancelView();
			return false;
		},

		deleteFile: function(id) {
			if ( confirm("<?php printf(js_escape(__("Are you sure you want to delete the file '%s'?\nClick ok to delete or cancel to go back.", 'fup')), '" + this.currentImage.title + "'); ?>") ) {
				$('action-value').value = 'delete';
				$('upload-file').submit();
				return true;
			}
			return false;
		},

		resizeIFrame: function(iframe) {
		    // Find the submit button absolute position
		    // and set the iframe height accordingly
		    var s = $('uploadsubmitbutton');
		    var y = 40; // for the button height
    		while( s != null ) {
		        y += s.offsetTop;
		        s = s.offsetParent;
		    }
		    iframe.style.height = y+'px';
		}
	};

	theUploadForm = {

		formId: 0,

		setupForm: function() {
			var u = $('upload-file');
			if (u && u.tagName == 'FORM') {
				u.innerHTML = '\
		<div id="addfield" style="float:left; clear: both; width:100%;">\
			[<a href="#" onclick="theUploadForm.addNewTable(); return false;"><?php _e('+add field', 'fup'); ?></a>]\
		</div>\
		<div id="buttons" class="submit">\
			<input type="hidden" name="from_tab" value="upload" />\
			<input type="hidden" name="action" value="upload" />\
<?php	global $post_id;
		if ( $post_id ) { ?>
			<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />\
<?php	} ?>
			<?php wp_nonce_field( 'inlineuploading' ); ?>\
			<div class="submit">\
				<input type="submit" value="<?php _e('Upload &raquo;', 'fup'); ?>" onclick="return theUploadForm.checkBeforeSubmit();" />\
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

			//this.resizeIframe(parent.document.getElementById('uploading'));
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

	theFileList.initializeVars();
	theFileList.initializeLinks();
	theUploadForm.setupForm();
} );
