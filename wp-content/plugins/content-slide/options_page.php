<div class="wrap">
	<h2>WP Content Slide Options</h2>
	<form method="post" action="">
	<?php $options = get_option('wpcs_options'); ?>
	<!-- SETTINGS PAGE FIRST COLUMN -->
	<div class="metabox-holder">
		<div class="postbox">
		<h3><?php _e("General Setings", 'wp_content_slider'); ?></h3>
			<div class="inside" style="padding:0px 15px 0px 15px;">	
			<p><?php _e("Image width", 'wp_content_slider'); ?>:<input type="text" name="wpcs_options[width]" value="<?php echo $options['width'] ?>" size="3" />px&nbsp;&nbsp;<?php _e("height", 'wp_content_slider'); ?>:<input type="text" name="wpcs_options[height]" value="<?php echo $options['height'] ?>" size="3" />px</p>
			<p><?php _e("Border width", 'wp_content_slider'); ?>:<input type="text" name="wpcs_options[border_width]" value="<?php echo $options['border_width'] ?>" size="3" />px&nbsp;&nbsp;&nbsp;&nbsp;<?php _e("Border Color", 'wp_content_slider'); ?>:<input type="text" name="wpcs_options[border_color]" value="<?php echo $options['border_color'] ?>" size="3" />px</p>
			<p><?php _e("Font family", 'wp_content_slider'); ?>:<select name="wpcs_options[font_family]"><option value="'Trebuchet MS', Helvetica, sans-serif" <?php selected("'Trebuchet MS', Helvetica, sans-serif", $options['font_family']); ?>>'Trebuchet MS', Helvetica, sans-serif</option><option value="Arial, Helvetica, sans-serif" <?php selected('Arial, Helvetica, sans-serif', $options['font_family']); ?>>Arial, Helvetica, sans-serif</option><option value="Tahoma, Geneva, sans-serif" <?php selected('Tahoma, Geneva, sans-serif', $options['font_family']); ?>>Tahoma, Geneva, sans-serif</option><option value="Verdana, Geneva, sans-serif" <?php selected('Verdana, Geneva, sans-serif', $options['font_family']); ?>>Verdana, Geneva, sans-serif</option><option value="Georgia, serif" <?php selected('Georgia, serif', $options['font_family']); ?>>Georgia, serif</option><option value="'Arial Black', Gadget, sans-serif" <?php selected("'Arial Black', Gadget, sans-serif", $options['font_family']); ?>>'Arial Black', Gadget, sans-serif</option><option value="'Bookman Old Style', serif" <?php selected("'Bookman Old Style', serif", $options['font_family']); ?>>'Bookman Old Style', serif</option><option value="'Comic Sans MS', cursive" <?php selected("'Comic Sans MS', cursive", $options['font_family']); ?>>'Comic Sans MS', cursive</option><option value="'Courier New', Courier, monospace" <?php selected("'Courier New', Courier, monospace", $options['font_family']); ?>>'Courier New', Courier, monospace</option><option value="Garamond, serif" <?php selected("Garamond, serif", $options['font_family']); ?>>Garamond, serif</option><option value="'Times New Roman', Times, serif" <?php selected("'Times New Roman', Times, serif", $options['font_family']); ?>>'Times New Roman', Times, serif</option><option value="Impact, Charcoal, sans-serif" <?php selected("Impact, Charcoal, sans-serif", $options['font_family']); ?>>Impact, Charcoal, sans-serif</option><option value="'Lucida Console', Monaco, monospace" <?php selected("'Lucida Console', Monaco, monospace", $options['font_family']); ?>>'Lucida Console', Monaco, monospace</option><option value="'MS Sans Serif', Geneva, sans-serif" <?php selected("'MS Sans Serif', Geneva, sans-serif", $options['font_family']); ?>>'MS Sans Serif', Geneva, sans-serif</option></select></p>
			
			<p><?php _e("Text font size", 'wp_content_slider'); ?>:<input type="text" name="wpcs_options[font_size]" value="<?php echo $options['font_size'] ?>" size="3" />px&nbsp;&nbsp;&nbsp;<?php _e("Text color", 'wp_content_slider'); ?>:<input id="textColor" type="text" name="wpcs_options[color]" value="<?php echo $options['color'] ?>" size="8" />&nbsp;HEX</p>
			<p><?php _e("Heading font size", 'wp_content_slider'); ?>:<input type="text" name="wpcs_options[heading_font]" value="<?php echo $options['heading_font'] ?>" size="3" />px &nbsp;&nbsp;&nbsp;<?php _e("Heading color", 'wp_content_slider'); ?>:<input id="textColor" type="text" name="wpcs_options[heading_color]" value="<?php echo $options['heading_color'] ?>" size="8" />&nbsp;HEX</p>
            
            <p><?php _e("Background color", 'wp_content_slider'); ?>:<input id="bgColor" type="text" name="wpcs_options[background_color]" value="<?php echo $options['background_color'] ?>" size="8" />&nbsp;HEX</p>
			 <input type="hidden" name="wpcs_options[update]" value="UPDATED" />
                <p><input type="submit" class="button-primary" value="<?php _e('Save Settings') ?>" /></p>
			</div>
		</div>
	</div>
	<!-- End First column -->
	<!-- SETTINGS PAGE SECONT COLUMN -->
	<div class="metabox-holder">
		<div class="postbox">
		<h3><?php _e("Effects &amp; Animation Setings", 'wp_content_slider'); ?></h3>
			<div class="inside" style="padding:0px 15px 0px 15px;">
			 <p><?php _e("Squares per width", 'wp_content_slider'); ?>:<input id="square_per_width" type="text" name="wpcs_options[square_per_width]" value="<?php echo $options['square_per_width'] ?>" size="5" />&nbsp;&nbsp;&nbsp;&nbsp;<?php _e("Squares per height", 'wp_content_slider'); ?>:<input id="square_per_height" type="text" name="wpcs_options[square_per_height]" value="<?php echo $options['square_per_height'] ?>" size="5" /></p>
			 <p><?php _e("Delay between images in ms", 'wp_content_slider'); ?>:<input id="delay" type="text" name="wpcs_options[delay]" value="<?php echo $options['delay'] ?>" size="5" />
			 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e("Delay beetwen squares in ms", 'wp_content_slider'); ?>:<input id="sDelay" type="text" name="wpcs_options[sDelay]" value="<?php echo $options['sDelay'] ?>" size="5" />
			 </p>
			 <p><?php _e("Opacity of title and navigation", 'wp_content_slider'); ?>:<input id="opacity" type="text" name="wpcs_options[opacity]" value="<?php echo $options['opacity'] ?>" size="5" />
			 </p>
			 <p><?php _e("Speed of title appereance in ms", 'wp_content_slider'); ?>:<input id="titleSpeed" type="text" name="wpcs_options[titleSpeed]" value="<?php echo $options['titleSpeed'] ?>" size="5" />
			 </p>
			 <p><?php _e("Effect", 'wp_content_slider'); ?>:<select name="wpcs_options[effect]" id="wpcs_effect"><option value="" <?php selected('', $options['effect']); ?>>all combined</option><option value="random" <?php selected('random', $options['effect']); ?>>random</option><option value="swirl" <?php selected('swirl', $options['effect']); ?>>swirl</option><option value="rain" <?php selected('rain', $options['effect']); ?>>rain</option><option value="straight" <?php selected('straight', $options['effect']); ?>>straight</option>
			 <option value="fade" <?php selected('fade', $options['effect']); ?>>fade</option>
			 </select><span id="fade_message_popup">For Fade Effect Navigation,Heading and overlay text is not available</span></p>
			 <p><?php _e("Mouse Over Pause <small>( Stop Animation on mouseover.)</small>", 'wp_content_slider'); ?>:<select name="wpcs_options[hoverPause]"><option value="true" <?php selected('true', $options['hoverPause']); ?>>Yes</option><option value="false" <?php selected('false', $options['hoverPause']); ?>>No</option></select></p>

			 <p><?php _e("Navigation Previous/Next <small>( Previous/Next buttons on image.)</small>", 'wp_content_slider'); ?>:<select name="wpcs_options[navigation_next_previous]"><option value="true" <?php selected('true', $options['navigation_next_previous']); ?>>Yes</option><option value="false" <?php selected('false', $options['navigation_next_previous']); ?>>No</option></select></p><p>
			 <p><?php _e("Navigation Buttons <small>( Square buttons at bottom )</small>", 'wp_content_slider'); ?>:<select name="wpcs_options[navigation_buttons]"><option value="true" <?php selected('true', $options['navigation_buttons']); ?>>Yes</option><option value="false" <?php selected('false', $options['navigation_buttons']); ?>>No</option></select></p><p>
			 <?php _e("Navigation Buttons Color <small>( Square buttons at bottom )</small>", 'wp_content_slider'); ?>::<input id="navigation_color" type="text" name="wpcs_options[navigation_color]" value="<?php echo $options['navigation_color'] ?>" size="10" />
			 </p>

			  <input type="hidden" name="wpcs_options[update]" value="UPDATED" />
                <p><input type="submit" class="button-primary" value="<?php _e('Save Settings') ?>" /></p>
			</div>
		</div>
    </div>
	<div class="metabox-holder" id="wpsc_custom_images">
		<div class="postbox">
		<script type="text/javascript" language="javascript">
		jQuery(document).ready(function($) {
			var val1=$('#wpcs_image_source').attr('value');
			$('#wpcs_effect').change(function(){
				var val2=$('#wpcs_effect').attr('value');
				if(val2=="fade")
				{
					$('#fade_message_popup').fadeIn(1000).fadeTo(1000, 1);
				}
				else
				{
					$('#fade_message_popup').fadeOut(1000);
	
				}
			});
			$('#fade_message_popup').hide();
			if(val1=="true")
			{
			$('#wpcs_source_category').hide();
			}
			else
			{
			$('#wpcs_custom_images').hide();
			}
			$('#wpcs_image_source').change(function(){
				var val=$('#wpcs_image_source').attr('value');
				if(val=="true")
				{
					$('#wpcs_source_category').fadeOut(1000);
					$('#wpcs_custom_images').fadeIn(1000).fadeTo(1000, 1);
				}
				else
				{
					$('#wpcs_custom_images').fadeOut(1000);
					$('#wpcs_source_category').fadeIn(1000).fadeTo(1000, 1);
					
				}
			});
		});
			
		</script>
		<h3><?php _e("Images Source Settings", 'wp_content_slider'); ?></h3>
			<div class="inside" style="padding:0px 15px 0px 15px;">
			<p><?php _e("Display Heading and Text", 'wp_content_slider'); ?>?&nbsp;<select name="wpcs_options[show_excerpt]"><option value="true" <?php selected('true', $options['show_excerpt']); ?>>Yes</option><option value="false" <?php selected('false', $options['show_excerpt']); ?>>No</option></select>&nbsp;<?php _e("No. of chars", 'wp_content_slider'); ?>:<input type="text" name="wpcs_options[char_length]" value="<?php echo $options['char_length'] ?>" size="3" /></p>
			<p><?php _e("Open Images/Links In New Window", 'wp_content_slider'); ?>?&nbsp;<select name="wpcs_options[new_window]"><option value="true" <?php selected('true', $options['new_window']); ?>>Yes</option><option value="false" <?php selected('false', $options['new_window']); ?>>No</option></select></p>
			<p><?php _e("Order Images Randomally", 'wp_content_slider'); ?>?&nbsp;<select name="wpcs_options[order]"><option value="true" <?php selected('true', $options['order']); ?>>Yes</option><option value="false" <?php selected('false', $options['order']); ?>>No</option></select>
			</p>
			<p><?php _e("Use custom images", 'wp_content_slider'); ?>?&nbsp;<select name="wpcs_options[custom_image]" id="wpcs_image_source"><option value="true" <?php selected('true', $options['custom_image']); ?>>Yes, Custom Images.</option><option value="false" <?php selected('false', $options['custom_image']); ?>>No, Using Posts from a Category</option></select> <small>If you select custom you dont have to create posts.</small></p>
			<div id="wpcs_source_category">
			<p><?php _e("Select a Category:", 'wp_content_slider'); ?><br /><?php wp_dropdown_categories(array('selected' => $options['post_category'], 'name' => 'wpcs_options[post_category]', 'orderby' => 'Name' , 'hierarchical' => 1, 'show_option_all' => __("All Categories", 'wp_content_slider'), 'hide_empty' => '0' )); ?></p>
            <p><?php _e("No. of posts/images", 'wp_content_slider'); ?>:<input type="text" name="wpcs_options[number_of_posts]" value="<?php echo $options['number_of_posts'] ?>" size="3" /></p>
            </div>
			<div id="wpcs_custom_images">
				<p><?php _e("Number of custom Images", 'wp_content_slider'); ?></td><td>
				<input type="text" name="wpcs_options[no_of_custom_images]" value="<?php echo $options['no_of_custom_images'] ?>" size="5" /></p>
				<p><input type="submit" class="button-primary" value="<?php _e('Save Settings') ?>" /></p>
				<?php 
				for($i=1;$i<=$options['no_of_custom_images'];$i++)
				{
				?>
				<h4>Custom Image <?php echo $i;?></h4>
				<table cellspacing="0" cellpadding="0" border="0">
				<tr><td width="150">
				<?php _e("Image ".$i." SRC", 'wp_content_slider'); ?></td><td>
				<input type="text" name="wpcs_options[slide_image<?php echo $i;?>]" value="<?php echo $options['slide_image'.$i] ?>" size="90" /></td></tr>
				<tr><td width="150"><?php _e("Image ".$i." Link", 'wp_content_slider'); ?></td><td>
				<input type="text" name="wpcs_options[slide_imagelink<?php echo $i;?>]" value="<?php echo $options['slide_imagelink'.$i] ?>" size="90" /></td></tr><tr>
				<?php if($options['show_excerpt']=='true') : ?>
				<td width="150"><?php _e("Image ".$i." Heading", 'wp_content_slider'); ?></td><td>
				<input type="text" name="wpcs_options[slide_imageheading<?php echo $i;?>]" value="<?php echo stripslashes($options['slide_imageheading'.$i]); ?>" size="90" /></td></tr>
				<tr><td width="150"><?php _e("Image ".$i." Text", 'wp_content_slider'); ?></td><td>
				<textarea name="wpcs_options[slide_imagetext<?php echo $i;?>]" cols="40" rows="4"><?php echo stripslashes($options['slide_imagetext'.$i]); ?></textarea></td></tr>
				<?php endif;?>
				</table>
				<?php
				}
				?>
			</div>
                <input type="hidden" name="wpcs_options[update]" value="UPDATED" />
                <p><input type="submit" class="button-primary" value="<?php _e('Save Settings') ?>" /></p>
			</div>
		</div>
	</div>
	</form>
</div>