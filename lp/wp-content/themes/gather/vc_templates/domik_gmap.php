<?php
/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * @var $zoom
 * @var $latitude
 * @var $longitude
 * @var $address
 * @var $add_address
 * @var $marker
 * @var $mapheight
 * @var $colorbg
 * Shortcode class
 * @var $this WPBakeryShortCode_Domik_Gmap
 */
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if(!empty($marker)){
	$marker = wp_get_attachment_url($marker );
}else{
	$marker = get_template_directory_uri() ."/images/marker.png";
}
?>
<!-- 
 Location Map
 ====================================== -->
<div class="g-maps <?php echo esc_attr($el_class ); ?>" id="venue">
    <!-- Tip:  You can change location, zoom, color theme, height, image and Info text by changing data-* attribute below. -->
    <!-- Available Colors:    red, orange, yellow, green, mint, aqua, blue, purple, pink, white, grey, black, invert -->
    <div class="map" id="map_canvas" data-maplat="<?php echo esc_attr($latitude );?>" data-maplon="<?php echo esc_attr($longitude);?>" data-mapzoom="<?php echo esc_attr($zoom);?>" data-color="<?php echo esc_attr($colorbg );?>" data-height="<?php echo esc_attr($mapheight );?>" data-img="<?php echo esc_url($marker );?>" data-info="<?php echo esc_attr($address);?>"></div>
</div>
<!-- end div.g-maps -->