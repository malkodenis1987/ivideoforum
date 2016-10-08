<fieldset>

	<div id="toggleable">

		<div class="col1">
			<label for="title" class="info">Gallery Title</label>
		</div>
		<div class="col2">
			<input type="text" id="title" name="title" value="<?php echo htmlspecialchars($custom_values['title']); ?>" />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="e_library" class="info">Image Source</label>
		</div>
		<div class="col3">
			<select id="e_library" name="e_library">
				<option <?php selected('media', $custom_values['e_library']); ?> value="media">Media Library</option>
				<option <?php selected('flickr', $custom_values['e_library']); ?> value="flickr">Flickr</option>
				<option <?php selected('nextgen', $custom_values['e_library']); ?> value="nextgen">NextGEN Gallery</option>
				<option <?php selected('picasa', $custom_values['e_library']); ?> value="picasa">Picasa Web Album</option>
			</select>
		</div>

		<div class="clear">&nbsp;</div>

		<div id="toggle-media">
			<div class="col1">
				<label for="e_featuredImage" class="info">Include Featured Image</label>
			</div>
			<div class="col3">
<?php
				$checked='';
				if (isset($custom_values['e_featuredImage']) && ($custom_values['e_featuredImage'] === 'true' || $custom_values['e_featuredImage'] === '')) {
					$checked = ' checked=\'checked\'';
				}
?>
				<input type="checkbox" id="e_featuredImage" name="e_featuredImage" value="true" <?php echo $checked; ?> />
			</div>

			<div class="col1">
				<span>Use the Upload/Insert&nbsp;&nbsp;<img src="<?php echo get_bloginfo('wpurl') . '/wp-admin/images/media-button.png'; ?>" width="15" height="15" alt="Add Media" />&nbsp;&nbsp;button to add images</span>
			</div>
		</div>

		<div id="toggle-flickr">
			<div class="col1">
				<label for="flickrUserName" class="info">Flickr Username</label>
			</div>
			<div class="col3">
				<input type="text" id="flickrUserName" name="flickrUserName" value="<?php echo $custom_values['flickrUserName']; ?>" />
			</div>

			<div class="col1">
				<label for="flickrTags" class="info">Flickr Tags</label>
			</div>
			<div class="col3">
				<input type="text" id="flickrTags" name="flickrTags" value="<?php echo $custom_values['flickrTags']; ?>" />
			</div>
		</div>

		<div id="toggle-nextgen">
			<div class="col1">
				<label for="e_nextgenGalleryId" class="info">NextGEN Gallery Id</label>
			</div>
			<div class="col3">
				<input type="text" id="e_nextgenGalleryId" name="e_nextgenGalleryId" value="<?php echo $custom_values['e_nextgenGalleryId']; ?>" />
			</div>
		</div>

		<div id="toggle-picasa">
			<div class="col1">
				<label for="e_picasaUserId" class="info">Picasa User Id</label>
			</div>
			<div class="col3">
				<input type="text" id="e_picasaUserId" name="e_picasaUserId" value="<?php echo $custom_values['e_picasaUserId']; ?>" />
			</div>

			<div class="col1">
				<label for="e_picasaAlbumName" class="info">Picasa Album Name</label>
			</div>
			<div class="col3">
				<input type="text" id="e_picasaAlbumName" name="e_picasaAlbumName" value="<?php echo $custom_values['e_picasaAlbumName']; ?>" />
			</div>
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="galleryStyle" class="info">Gallery Style</label>
		</div>
		<div class="col3">
			<select id="galleryStyle" name="galleryStyle">
				<option <?php selected('MODERN', $custom_values['galleryStyle']); ?> value="MODERN">Modern</option>
				<option <?php selected('CLASSIC', $custom_values['galleryStyle']); ?> value="CLASSIC">Classic</option>
				<option <?php selected('COMPACT', $custom_values['galleryStyle']); ?> value="COMPACT">Compact</option>
			</select>
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="thumbPosition" class="info">Thumb Position</label>
		</div>
		<div class="col3">
			<select id="thumbPosition" name="thumbPosition">
				<option <?php selected('TOP', $custom_values['thumbPosition']); ?> value="TOP">Top</option>
				<option <?php selected('BOTTOM', $custom_values['thumbPosition']); ?> value="BOTTOM">Bottom</option>
				<option <?php selected('LEFT', $custom_values['thumbPosition']); ?> value="LEFT">Left</option>
				<option <?php selected('RIGHT', $custom_values['thumbPosition']); ?> value="RIGHT">Right</option>
				<option <?php selected('NONE', $custom_values['thumbPosition']); ?> value="NONE">None</option>
			</select>
		</div>

		<div class="col1">
			<label for="frameWidth" class="info">Frame Width, px</label>
		</div>
		<div class="col3">
			<input type="text" id="frameWidth" name="frameWidth" value="<?php echo $custom_values['frameWidth']; ?>" />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="maxImageWidth" class="info">Max Image Width, px</label>
		</div>
		<div class="col3">
			<input type="text" id="maxImageWidth" name="maxImageWidth" value="<?php echo $custom_values['maxImageWidth'] ?>" />
		</div>

		<div class="col1">
			<label for="maxImageHeight" class="info">Max Image Height, px</label>
		</div>
		<div class="col3">
			<input type="text" id="maxImageHeight" name="maxImageHeight" value="<?php echo $custom_values['maxImageHeight'] ?>" />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="textColor" class="info">Text Color</label>
		</div>
		<div class="col3">
			<input type="text" id="textColor" name="textColor" value="<?php echo str_replace('0x', '', $custom_values['textColor']); ?>" />
		</div>

		<div class="col1">
			<label for="frameColor" class="info">Frame Color</label>
		</div>
		<div class="col3">
			<input type="text" id="frameColor" name="frameColor" value="<?php echo str_replace('0x', '', $custom_values['frameColor']); ?>" />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="showOpenButton" class="info">Open Button</label>
		</div>
		<div class="col3">
			<input type="checkbox" id="showOpenButton" name="showOpenButton" value="true" <?php checked($custom_values['showOpenButton'], 'true'); ?> />
		</div>

		<div class="col1">
			<label for="showFullscreenButton" class="info">Fullscreen Button</label>
		</div>
		<div class="col3">
			<input type="checkbox" id="showFullscreenButton" name="showFullscreenButton" value="true" <?php checked($custom_values['showFullscreenButton'], 'true'); ?> />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="thumbRows" class="info">Thumbnail Rows</label>
		</div>
		<div class="col3">
			<input type="text" id="thumbRows" name="thumbRows" value="<?php echo $custom_values['thumbRows']; ?>" />
		</div>

		<div class="col1">
			<label for="thumbColumns" class="info">Thumbnail Columns</label>
		</div>
		<div class="col3">
			<input type="text" id="thumbColumns" name="thumbColumns" value="<?php echo $custom_values['thumbColumns']; ?>" />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="e_g_width" class="info">Gallery Width</label>
		</div>
		<div class="col3">
			<input type="text" id="e_g_width" name="e_g_width" value="<?php echo $custom_values['e_g_width']; ?>" />
		</div>

		<div class="col1">
			<label for="e_g_height" class="info">Gallery Height</label>
		</div>
		<div class="col3">
			<input type="text" id="e_g_height" name="e_g_height" value="<?php echo $custom_values['e_g_height']; ?>" />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="e_bgColor" class="info">Background Color</label>
		</div>
		<div class="col3">
			<input type="text" id="e_bgColor" name="e_bgColor" value="<?php echo ($custom_values['e_bgColor'] === 'transparent') ? '' : $custom_values['e_bgColor']; ?>" />
		</div>

		<div class="col1">
			<label for="background-transparent" class="info">Background Transparent</label>
		</div>
		<div class="col3">
			<input type="checkbox" id="background-transparent" name="background-transparent" value="true" <?php checked($custom_values['e_bgColor'], 'transparent'); ?> />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="e_useFlash" class="info">Use Flash</label>
		</div>
		<div class="col3">
			<input type="checkbox" id="e_useFlash" name="e_useFlash" value="true" <?php checked($custom_values['e_useFlash'], 'true'); ?> />
		</div>

		<div class="clear">&nbsp;</div>

		<div class="col1">
			<label for="proOptions" class="info">Pro Options</label>
		</div>
		<div class="col2">
			<textarea id="proOptions" name="proOptions" cols="50" rows="5" ><?php echo $pro_options; ?></textarea>
		</div>

		<div class="clear">&nbsp;</div>

	</div>

</fieldset>
