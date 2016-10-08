<?php
/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * @var $full_width
 * @var $full_height
 * @var $content_placement
 * @var $parallax
 * @var $parallax_image
 * @var $css
 * @var $el_id
 * @var $video_bg
 * @var $video_bg_url
 * @var $video_bg_parallax
 * @var $content - shortcode content
 *
 * @var $cth_layout
 *
 * Shortcode class
 * @var $this WPBakeryShortCode_VC_Row
 */
$output = $after_output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
if($cth_layout === 'gather_header') :?>
<?php
    $el_class = $this->getExtraClass( $el_class );

    $css_classes = array(
        'gather_sec',
        'header',
        $el_class,
        vc_shortcode_custom_css_class( $css ),
    );
    $css_class = preg_replace( '/\s+/', ' ', implode( ' ', array_filter( $css_classes ) ) );
?>
<section <?php
    echo isset($el_id) && !empty($el_id) ? "id='" . esc_attr($el_id) . "'" : ""; ?> <?php
    echo !empty($css_class) ? "class='" . esc_attr( trim( $css_class ) ) . "'" : ""; ?>>
    <div class="background-opacity"></div>
<?php 
    if ( ! empty( $full_width ) ) { ?>
    <div class="container-fluid">
<?php }else { ?>
    <div class="container">
<?php
}    ?>
        <div class="row no-margin <?php //echo esc_attr($el_class );?>">
            <?php echo wpb_js_remove_wpautop($content); ?>
        </div>
    </div>
</section>
<?php
elseif($cth_layout === 'gather_header_video') :?>
<?php
    /**
    * @var $usevideobg
    * @var $mp4video
    * @var $webmvideo
    * @var $videobgimg
    **/
    $el_class = $this->getExtraClass( $el_class );

    $css_classes = array(
        'gather_sec',
        'header-video-module',
        $el_class,
        vc_shortcode_custom_css_class( $css ),
    );
    $css_class = preg_replace( '/\s+/', ' ', implode( ' ', array_filter( $css_classes ) ) );
?>
<section <?php
    echo isset($el_id) && !empty($el_id) ? "id='" . esc_attr($el_id) . "'" : ""; ?> <?php
    echo !empty($css_class) ? "class='" . esc_attr( trim( $css_class ) ) . "'" : ""; ?>>
    <div class="video-container">
        <div class="header">
        <?php 
            if ( ! empty( $full_width ) ) { ?>
            <div class="container-fluid">
        <?php }else { ?>
            <div class="container">
        <?php
        }    ?>
                <div class="row no-margin <?php //echo esc_attr($el_class );?>">
                    <?php echo wpb_js_remove_wpautop($content); ?>
                </div>
            </div>
        </div>
        <?php if($usevideobg == 'yes') :?>
        <div class="filter"></div>
        <video autoplay loop class="fillWidth video-header">
        <?php if(!empty($mp4video)) :?>
            <source src="<?php echo esc_url($mp4video );?>" type="video/mp4" />
        <?php endif;?>
        <?php if(!empty($webmvideo)) :?>
            <source src="<?php echo esc_url($webmvideo );?>" type="video/webm" />
        <?php endif;?>
        </video>
        <?php if(!empty($videobgimg)) :?>
        <div class="poster hidden">
            <?php wp_get_attachment_image( $videobgimg, 'full' );?>
        </div>
        <?php endif;?>
        <?php endif;?>
    </div>
</section>
<?php
elseif ($cth_layout === 'gather_sec'): ?>
<?php
    $el_class = $this->getExtraClass( $el_class );

    $css_classes = array(
        'gather_sec',
        vc_shortcode_custom_css_class( $css ),
    );
    $css_class = preg_replace( '/\s+/', ' ', implode( ' ', array_filter( $css_classes ) ) );

    ?>
<section <?php
    echo isset($el_id) && !empty($el_id) ? "id='" . esc_attr($el_id) . "'" : ""; ?> <?php
    echo !empty($css_class) ? "class='" . esc_attr( trim( $css_class ) ) . "'" : ""; ?>>
<?php 
    if ( ! empty( $full_width ) ) { ?>
    <div class="container-fluid">
<?php }else { ?>
    <div class="container">
<?php
}    ?>
        <div class="row no-margin <?php echo esc_attr($el_class );?>">
            <?php echo wpb_js_remove_wpautop($content); ?>
        </div>
    </div>
    <!-- end .container -->
</section>
<?php

else :
    //for default layout;

    wp_enqueue_script( 'wpb_composer_front_js' );

    $el_class = $this->getExtraClass( $el_class );

    $css_classes = array(
        'vc_row',
        'wpb_row', //deprecated
        'vc_row-fluid',
        $el_class,
        vc_shortcode_custom_css_class( $css ),
    );
    $wrapper_attributes = array();
    // build attributes for wrapper
    if ( ! empty( $el_id ) ) {
        $wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
    }
    if ( ! empty( $full_width ) ) {
        $wrapper_attributes[] = 'data-vc-full-width="true"';
        $wrapper_attributes[] = 'data-vc-full-width-init="false"';
        if ( 'stretch_row_content' === $full_width ) {
            $wrapper_attributes[] = 'data-vc-stretch-content="true"';
        } elseif ( 'stretch_row_content_no_spaces' === $full_width ) {
            $wrapper_attributes[] = 'data-vc-stretch-content="true"';
            $css_classes[] = 'vc_row-no-padding';
        }
        $after_output .= '<div class="vc_row-full-width"></div>';
    }

    if ( ! empty( $full_height ) ) {
        $css_classes[] = ' vc_row-o-full-height';
        if ( ! empty( $content_placement ) ) {
            $css_classes[] = ' vc_row-o-content-' . $content_placement;
        }
    }

    // use default video if user checked video, but didn't chose url
    if ( ! empty( $video_bg ) && empty( $video_bg_url ) ) {
        $video_bg_url = 'https://www.youtube.com/watch?v=lMJXxhRFO1k';
    }

    $has_video_bg = ( ! empty( $video_bg ) && ! empty( $video_bg_url ) && vc_extract_youtube_id( $video_bg_url ) );

    if ( $has_video_bg ) {
        $parallax = $video_bg_parallax;
        $parallax_image = $video_bg_url;
        $css_classes[] = ' vc_video-bg-container';
        wp_enqueue_script( 'vc_youtube_iframe_api_js' );
    }

    if ( ! empty( $parallax ) ) {
        wp_enqueue_script( 'vc_jquery_skrollr_js' );
        $wrapper_attributes[] = 'data-vc-parallax="1.5"'; // parallax speed
        $css_classes[] = 'vc_general vc_parallax vc_parallax-' . $parallax;
        if ( strpos( $parallax, 'fade' ) !== false ) {
            $css_classes[] = 'js-vc_parallax-o-fade';
            $wrapper_attributes[] = 'data-vc-parallax-o-fade="on"';
        } elseif ( strpos( $parallax, 'fixed' ) !== false ) {
            $css_classes[] = 'js-vc_parallax-o-fixed';
        }
    }

    if ( ! empty ( $parallax_image ) ) {
        if ( $has_video_bg ) {
            $parallax_image_src = $parallax_image;
        } else {
            $parallax_image_id = preg_replace( '/[^\d]/', '', $parallax_image );
            $parallax_image_src = wp_get_attachment_image_src( $parallax_image_id, 'full' );
            if ( ! empty( $parallax_image_src[0] ) ) {
                $parallax_image_src = $parallax_image_src[0];
            }
        }
        $wrapper_attributes[] = 'data-vc-parallax-image="' . esc_attr( $parallax_image_src ) . '"';
    }
    if ( ! $parallax && $has_video_bg ) {
        $wrapper_attributes[] = 'data-vc-video-bg="' . esc_attr( $video_bg_url ) . '"';
    }
    $css_class = preg_replace( '/\s+/', ' ', apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode( ' ', array_filter( $css_classes ) ), $this->settings['base'], $atts ) );
    $wrapper_attributes[] = 'class="' . esc_attr( trim( $css_class ) ) . '"';

    $output .= '<div ' . implode( ' ', $wrapper_attributes ) . '>';
    $output .= wpb_js_remove_wpautop( $content );
    $output .= '</div>';
    $output .= $after_output;
    $output .= $this->endBlockComment( $this->getShortcode() );

    echo $output;

    // end default layout

endif; ?>