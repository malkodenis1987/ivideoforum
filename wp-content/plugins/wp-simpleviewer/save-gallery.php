<?php

$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
$wp_path = (isset($wp_path[0]) && $wp_path[0] !== '') ? $wp_path[0] : $_SERVER['DOCUMENT_ROOT'];

require_once($wp_path . '/wp-load.php');

$options = get_option('simpleviwer_options', array());
$options['last_id'] = isset($options['last_id']) ? $options['last_id'] + 1 : 1;
update_option('simpleviwer_options', $options);

$gallery_path = $SimpleViewer->get_gallery_path();
$gallery_id = $options['last_id'];
$gallery_filename = $gallery_path . $gallery_id . '.xml';

echo 'gallery_id="' . $gallery_id . '"' ;

$SimpleViewer->build_gallery($gallery_filename, $_POST);

?>
