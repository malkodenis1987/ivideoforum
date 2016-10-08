<?php
/**
 * Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the Settings->Visual Composer page
 */
add_action('vc_before_init', 'cththemes_gather_vcSetAsTheme');
function cththemes_gather_vcSetAsTheme() {
    vc_set_as_theme($disable_updater = true);
}



//if(class_exists('WPBakeryVisualComposerSetup')){
function cththemes_gather_theme_custom_css_classes_for_vc_row_and_vc_column($class_string, $tag) {
    if ($tag == 'vc_row' || $tag == 'vc_row_inner') {
        $class_string = str_replace('vc_row', 'row', $class_string);
    }
    if ($tag == 'vc_column' || $tag == 'vc_column_inner') {
        $class_string = preg_replace('/vc_col-(xs|sm|md|lg)-(\d{1,2})/', 'col-$1-$2', $class_string);
        $class_string = preg_replace('/vc_col-(xs|sm|md|lg)-offset-(\d{1,2})/', 'col-$1-offset-$2', $class_string);

    }
    return $class_string;
}

// Filter to Replace default css class for vc_row shortcode and vc_column

add_filter('vc_shortcodes_css_class', 'cththemes_gather_theme_custom_css_classes_for_vc_row_and_vc_column', 10, 2);

function cththemes_gather_ace_settings_field($settings, $value) {
    if (!isset($settings['ace_mode'])) {
        $settings['ace_mode'] = 'html';
    }
    if (isset($settings['ace_style'])) {
        $ace_style = 'style="' . $settings['ace_style'] . '"';
    } 
    else {
        $ace_style = 'style="min-height:300px;border:1px solid #bbb;"';
    }
    return '<div id="cth_ace_editor" ' . $ace_style . '>' . '</div>' . '<input name="' . esc_attr($settings['param_name']) . '" class="wpb_vc_param_value wpb-hidden ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_field" type="hidden" value="' . esc_attr($value) . '" />' . '<script src="' . get_template_directory_uri() . '/vc_extend/ace/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>' . '<script src="' . get_template_directory_uri() . '/vc_extend/ace/src-min-noconflict/mode-' . esc_attr($settings['ace_mode']) . '.js" type="text/javascript" charset="utf-8"></script>' . '<script>'
    
    //. "function htmlEntities(str) {return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;');}"
     . 'var cth_ace_editor = ace.edit("cth_ace_editor");' . 'cth_ace_editor.getSession().setMode("ace/mode/' . esc_attr($settings['ace_mode']) . '");' . 'cth_ace_editor.setValue( decodeURIComponent( atob( jQuery("#cth_ace_editor").next(".cth_ace_field").val() ) ) );' . 'cth_ace_editor.getSession().on("change", function(e) {' . 'jQuery("#cth_ace_editor").next(".cth_ace_field").val( btoa( encodeURIComponent(  cth_ace_editor.getValue() ) ) );'
    
    //. 'jQuery(".content.cth_ace").html(  htmlEntities(cth_ace_editor.getValue()) );'
     . '});' . '</script>';
}


// if(function_exists('add_shortcode_param')){
//     add_shortcode_param('cth_ace', 'cththemes_gather_ace_settings_field');
// }



// Add new Param in Row

if (function_exists('vc_add_param')) {
    
    vc_add_param(
        'vc_row', 
        array(
            "type" => "dropdown", 
            "heading" => __('Section Layout', 'wpb'), 
            "param_name" => "cth_layout", 
            "value" => array(
                __('Default', 'wpb') => 'default', 
                __('Gather Header','gather') => 'gather_header',
                __('Gather Header Video','gather') => 'gather_header_video',
                __('Gather Page Section','gather') => 'gather_sec',
            ), 
            "description" => __("Select one of the pre made page sections or using default", "wpb"),
        )
        
    );

    vc_add_param(
        'vc_row',
        array(
            "type" => "dropdown",
            "class"=>"",
            "heading" => __('Video Background Settings', 'gather'),
            "param_name" => "usevideobg",
            "value" => array(   
                            
                            __('No', 'gather') => 'no', 
                            __('Yes', 'gather') => 'yes',                                                                                   
                                                                                                          
                        ),
            
        )
    );
    vc_add_param(
        'vc_row',
        array(
            "type"      => "textfield",
            //"holder"    => "div",
            "class"     => "",
            "heading"   => __(".MP4 Video Link", 'gather'),
            "param_name"=> "mp4video",
            "description" => __(".MP4 Video Link", 'gather'),
            'dependency' => array(
                    'element' => 'usevideobg',
                    'value' => array( 'yes' ),
                    'not_empty' => false,
            ),
        )
    );
    vc_add_param(
        'vc_row',
        array(
            "type"      => "textfield",
            //"holder"    => "div",
            "class"     => "",
            "heading"   => __(".WebM Video Link", 'gather'),
            "param_name"=> "webmvideo",
            "description" => __(".WebM Video Link", 'gather'),
            'dependency' => array(
                    'element' => 'usevideobg',
                    'value' => array( 'yes' ),
                    'not_empty' => false,
            ),
        )
    );
    vc_add_param(
        'vc_row',
        array(
            "type"      => "attach_image",
            //"holder"    => "div",
            "class"     => "",
            "heading"   => __("Video Background Image", 'gather'),
            "param_name"=> "videobgimg",
            "description" => __("Video Background Image", 'gather'),
            'dependency' => array(
                    'element' => 'usevideobg',
                    'value' => array( 'yes' ),
                    'not_empty' => false,
            ),
        )
    );

    vc_add_param('vc_column',array(
                                  "type" => "dropdown",
                                  "heading" => __('Use Animation', 'wpb'),
                                  "param_name" => "animation",
                                  "value" => array(   
                                                    __('No', 'wpb') => 'no',  
                                                    __('Yes', 'wpb') => 'yes',                                                                                
                                                  ),
                                  "description" => __("Use animation effect or not", "wpb"),      
                                ) 
        );

        vc_add_param('vc_column',array(
                                  "type" => "dropdown",
                                  "heading" => __('Data effect', 'wpb'),
                                  "param_name" => "effect",
                                  "value" => array(
                                                    __('bounce','wpb')=>'bounce',
                                                    __('flash','wpb')=>'flash',
                                                    __('pulse','wpb')=>'pulse',
                                                    __('rubberBand','wpb')=>'rubberBand',
                                                    __('shake','wpb')=>'shake',
                                                    __('swing','wpb')=>'swing',
                                                    __('tada','wpb')=>'tada',
                                                    __('wobble','wpb')=>'wobble',

                                                    __('bounceIn','wpb')=>'bounceIn',
                                                    __('bounceInUp','wpb')=>'bounceInUp',
                                                    __('bounceInDown','wpb')=>'bounceInDown',
                                                    __('bounceInLeft','wpb')=>'bounceInLeft',
                                                    __('bounceInRight','wpb')=>'bounceInRight',
                                                    __('bounceOut','wpb')=>'bounceOut',
                                                    __('bounceOutUp','wpb')=>'bounceOutUp',
                                                    __('bounceOutDown','wpb')=>'bounceOutDown',
                                                    __('bounceOutLeft','wpb')=>'bounceOutLeft',
                                                    __('bounceOutRight','wpb')=>'bounceOutRight',

                                                    __('fadeIn','wpb')=>'fadeIn',
                                                    __('fadeInUp','wpb')=>'fadeInUp',
                                                    __('fadeInDown','wpb')=>'fadeInDown',
                                                    __('fadeInLeft','wpb')=>'fadeInLeft',
                                                    __('fadeInRight','wpb')=>'fadeInRight',
                                                    __('fadeInUpBig','wpb')=>'fadeInUpBig',
                                                    __('fadeInDownBig','wpb')=>'fadeInDownBig',
                                                    __('fadeInLeftBig','wpb')=>'fadeInLeftBig',
                                                    __('fadeInRightBig','wpb')=>'fadeInRightBig',

                                                    __('fadeOut','wpb')=>'fadeOut',
                                                    __('fadeOutUp','wpb')=>'fadeOutUp',
                                                    __('fadeOutDown','wpb')=>'fadeOutDown',
                                                    __('fadeOutLeft','wpb')=>'fadeOutLeft',
                                                    __('fadeOutRight','wpb')=>'fadeOutRight',
                                                    __('fadeOutUpBig','wpb')=>'fadeOutUpBig',
                                                    __('fadeOutDownBig','wpb')=>'fadeOutDownBig',
                                                    __('fadeOutLeftBig','wpb')=>'fadeOutLeftBig',
                                                    __('fadeOutRightBig','wpb')=>'fadeOutRightBig',

                                                    __('flipInX','wpb')=>'flipInX',
                                                    __('flipInY','wpb')=>'flipInY',
                                                    __('flipOutX','wpb')=>'flipOutX',
                                                    __('flipOutY','wpb')=>'flipOutY',
                                                    __('rotateIn','wpb')=>'rotateIn',
                                                    __('rotateInDownLeft','wpb')=>'rotateInDownLeft',
                                                    __('rotateInDownRight','wpb')=>'rotateInDownRight',
                                                    __('rotateInUpLeft','wpb')=>'rotateInUpLeft',
                                                    __('rotateInUpRight','wpb')=>'rotateInUpRight',

                                                    __('rotateOut','wpb')=>'rotateOut',
                                                    __('rotateOutDownLeft','wpb')=>'rotateOutDownLeft',
                                                    __('rotateOutDownRight','wpb')=>'rotateOutDownRight',
                                                    __('rotateOutUpLeft','wpb')=>'rotateOutUpLeft',
                                                    __('rotateOutUpRight','wpb')=>'rotateOutUpRight',

                                                    __('rotateOut','wpb')=>'rotateOut',
                                                    __('rotateOutDownLeft','wpb')=>'rotateOutDownLeft',
                                                    __('rotateOutDownRight','wpb')=>'rotateOutDownRight',
                                                    __('rotateOutUpLeft','wpb')=>'rotateOutUpLeft',
                                                    __('rotateOutUpRight','wpb')=>'rotateOutUpRight',

                                                    __('slideInDown','wpb')=>'slideInDown',
                                                    __('slideInLeft','wpb')=>'slideInLeft',
                                                    __('slideInRight','wpb')=>'slideInRight',
                                                    __('slideOutLeft','wpb')=>'slideOutLeft',
                                                    __('slideOutRight','wpb')=>'slideOutRight',
                                                    __('slideOutUp','wpb')=>'slideOutUp',
                                                    __('slideInUp','wpb')=>'slideInUp',
                                                    __('slideOutDown','wpb')=>'slideOutDown',

                                                    __('hinge','wpb')=>'hinge',

                                                    __('rollIn','wpb')=>'rollIn',
                                                    __('rollOut','wpb')=>'rollOut',
                                                    

                                                    __('zoomIn','wpb')=>'zoomIn',
                                                    __('zoomInUp','wpb')=>'zoomInUp',
                                                    __('zoomInDown','wpb')=>'zoomInDown',
                                                    __('zoomInLeft','wpb')=>'zoomInLeft',
                                                    __('zoomInRight','wpb')=>'zoomInRight',

                                                    __('zoomOut','wpb')=>'zoomOut',
                                                    __('zoomOutUp','wpb')=>'zoomOutUp',
                                                    __('zoomOutDown','wpb')=>'zoomOutDown',
                                                    __('zoomOutLeft','wpb')=>'zoomOutLeft',
                                                    __('zoomOutRight','wpb')=>'zoomOutRight',
                                                ),                              
                                  "description" => __("Add data effect", "wpb"),      
                                ) 

        );

        vc_add_param('vc_column',
            array(
                "type" => "textfield",
                "heading" => __('Animation Delay', 'wpb'),
                "param_name" => "delay",
                "value" => "",
                "description" => __("Animation delay in second like 2s", "wpb"),
            ) 

        );
}

//if(function_exists('vc_remove_param')){ }
