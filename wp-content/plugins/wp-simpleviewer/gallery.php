<?php

header('Content-type: application/xml');

$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
$wp_path = (isset($wp_path[0]) && $wp_path[0] !== '') ? $wp_path[0] : $_SERVER['DOCUMENT_ROOT'];

require_once($wp_path . '/wp-load.php');
require_once($wp_path . '/wp-admin/includes/plugin.php');

$gallery_path = $SimpleViewer->get_gallery_path();
$gallery_id=$_GET['gallery_id'];
$gallery_filename = $gallery_path . $gallery_id . '.xml';

if (file_exists($gallery_filename)) {

	$dom_doc = new DOMDocument();
	$dom_doc->load($gallery_filename);

	$settings_tags = $dom_doc->getElementsByTagName('simpleviewergallery');
	$settings_tag = $settings_tags->item(0);

	$new_dom_doc = new DOMDocument();
	$new_dom_doc->formatOutput = true;

	$new_settings_tag = $new_dom_doc->createElement('simpleviewergallery');				

	if ($settings_tag->hasAttributes()) {
		foreach ($settings_tag->attributes as $attribute) {
			$name = $attribute->nodeName;
			$value = $attribute->nodeValue;
			$new_settings_tag->setAttribute($name, $value);
		}
	}

	$selected_library = 'media';

	if ($settings_tag->hasAttribute('e_library')) {
		$selected_library = $settings_tag->getAttribute('e_library');
	} elseif ($settings_tag->hasAttribute('useFlickr') && $settings_tag->getAttribute('useFlickr') === 'true') {
		$selected_library = 'flickr';
	}

	switch ($selected_library) {
		case 'media':
			$post_id = $settings_tag->hasAttribute('postID') ? $settings_tag->getAttribute('postID') : '0';
			$post_record = get_post($post_id);
			if (!is_null($post_record)) {
				$attachments = array();
				$featured_image = $settings_tag->hasAttribute('e_featuredImage') ? $settings_tag->getAttribute('e_featuredImage') : 'true';
				if ($featured_image === 'true') {
					$attachments = get_children(array('post_parent'=>$post_id, 'post_type'=>'attachment', 'orderby'=>'menu_order ASC, ID', 'order'=>'DESC'));
				} else {
					$attachments = get_children(array('post_parent'=>$post_id, 'post_type'=>'attachment', 'orderby'=>'menu_order ASC, ID', 'order'=>'DESC',  'exclude'=>get_post_thumbnail_id($post_id)));
				}
				if ($attachments) {
					foreach ($attachments as $attachment) {
						$thumbnail = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
						$image = wp_get_attachment_image_src($attachment->ID, 'full');
						if ($thumbnail && $image) {
							$thumbnail_url = $thumbnail[0];
							$image_url = $image[0];
							$image_element = $new_dom_doc->createElement('image');
							$image_element->setAttribute('imageURL', $image_url);
							$image_element->setAttribute('thumbURL', $thumbnail_url);
							$image_element->setAttribute('linkURL', $image_url);
							$image_element->setAttribute('linkTarget', '_blank');
							$caption_element = $new_dom_doc->createElement('caption');
							$caption_text = $new_dom_doc->createCDATASection($attachment->post_excerpt);
							$caption_element->appendChild($caption_text);
							$image_element->appendChild($caption_element);
							$new_settings_tag->appendChild($image_element);
						}
					}
				}
			}
			break;
		case 'nextgen':
			if (is_plugin_active("nextgen-gallery/nggallery.php")) {
				global $wpdb;
				$ngg_options = get_option('ngg_options', array());
				$nextgen_gallery_id = $settings_tag->hasAttribute('e_nextgenGalleryId') ? $settings_tag->getAttribute('e_nextgenGalleryId') : '0';
				$attachments = array();
				if (isset($ngg_options['galSort']) && isset($ngg_options['galSortDir'])) {
					$attachments = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = '$nextgen_gallery_id' AND tt.exclude != 1 ORDER BY tt.$ngg_options[galSort] $ngg_options[galSortDir]");
				} else {
					$attachments = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = '$nextgen_gallery_id' AND tt.exclude != 1");
				}
				if ($attachments) {
					$base_url = site_url('/' . $attachments[0]->path . '/');
					foreach ($attachments as $attachment) {
						$image_basename = $attachment->filename;
						$image_url = $base_url . $image_basename;
						$image_element = $new_dom_doc->createElement('image');
						$image_element->setAttribute('imageURL', $image_url);
						$image_element->setAttribute('thumbURL', $base_url . "thumbs/thumbs_" . $image_basename);
						$image_element->setAttribute('linkURL', $image_url);
						$image_element->setAttribute('linkTarget', '_blank');
						$caption_element = $new_dom_doc->createElement('caption');
						$caption_text = $new_dom_doc->createCDATASection($attachment->description);
						$caption_element->appendChild($caption_text);
						$image_element->appendChild($caption_element);
						$new_settings_tag->appendChild($image_element);
					}
				}
			}
			break;
		case 'picasa':
			$picasa_user_id = $settings_tag->hasAttribute('e_picasaUserId') ? $settings_tag->getAttribute('e_picasaUserId') : '';
			$picasa_album_name = $settings_tag->hasAttribute('e_picasaAlbumName') ? $settings_tag->getAttribute('e_picasaAlbumName') : '';
			$picasa_feed = 'http://picasaweb.google.com/data/feed/api/user/' . $picasa_user_id . '/album/' . $picasa_album_name . '?kind=photo&imgmax=1600';
			$attachments = @simplexml_load_file($picasa_feed);			
			if ($attachments) {
				foreach ($attachments->entry as $attachment) {
					$media = $attachment->children('http://search.yahoo.com/mrss/');
					$media_group = $media->group;
					$media_group_content = $media_group->content;
					$image_url = $media_group_content->attributes()->{'url'};
					$image_element = $new_dom_doc->createElement('image');
					$image_element->setAttribute('imageURL', $image_url);
					$image_element->setAttribute('thumbURL', $media_group->thumbnail[1]->attributes()->{'url'});
					$image_element->setAttribute('linkURL', $image_url);
					$image_element->setAttribute('linkTarget', '_blank');
					$caption_element = $new_dom_doc->createElement('caption');
					$caption_text = $new_dom_doc->createCDATASection($attachment->summary);
					$caption_element->appendChild($caption_text);
					$image_element->appendChild($caption_element);
					$new_settings_tag->appendChild($image_element);
				}
			}
			break;
	}

	$new_dom_doc->appendChild($new_settings_tag);

	echo $new_dom_doc->saveXML();
}

?>
