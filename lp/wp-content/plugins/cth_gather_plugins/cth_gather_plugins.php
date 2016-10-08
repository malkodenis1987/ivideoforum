<?php
/*
Plugin Name: Gather theme plugins
Plugin URI: https://cththemes.com
Description: A custom plugin for Gather - Event Landing Page Wordpress Theme
Version: 1.0
Text Domain: cth-gather-plugins
Author: CTHthemes
Author URI: http://themeforest.net/user/cththemes
License: GNU General Public License version 3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined('ABSPATH') ) {
    die('Please do not load this file directly!');
}
define ('CTH_EVENTRES_DIR',plugin_dir_path(__FILE__ ));
define ('CTH_EVENTRES_DIR_URL',plugin_dir_url(__FILE__ ));
/* form fields for options page */
require_once dirname(__FILE__).'/includes/form_fields.php';
/*shortcodes for theme*/
require_once dirname(__FILE__).'/includes/theme_shortcodes.php';


// admin style
add_action('admin_head', 'cth_gather_admin_style');
if(!function_exists('cth_gather_admin_style')){
    function cth_gather_admin_style() {
        echo '<link rel="stylesheet" href="'.CTH_EVENTRES_DIR_URL.'assets/admin/style.css" type="text/css" media="all" />';
    } 
}



function cth_register_cpt_Cth_Schedule() {
    
    $labels = array( 
        'name' => __( 'Schedule', 'cth' ),
        'singular_name' => __( 'Schedule', 'cth' ),
        'add_new' => __( 'Add New Schedule', 'cth' ),
        'add_new_item' => __( 'Add New Schedule', 'cth' ),
        'edit_item' => __( 'Edit Schedule', 'cth' ),
        'new_item' => __( 'New Schedule', 'cth' ),
        'view_item' => __( 'View Schedule', 'cth' ),
        'search_items' => __( 'Search Schedules', 'cth' ),
        'not_found' => __( 'No Schedules found', 'cth' ),
        'not_found_in_trash' => __( 'No Schedules found in Trash', 'cth' ),
        'parent_item_colon' => __( 'Parent Schedule:', 'cth' ),
        'menu_name' => __( 'Gather Event Schedules', 'cth' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'List Schedules',
        'supports' => array( 'title', 'editor', 'thumbnail'/*,'comments', 'post-formats'*/),
        'taxonomies' => array('cth_schedule_cat'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        'menu_icon'   => 'dashicons-calendar-alt',
        //'menu_icon' => plugin_dir_url( __FILE__ ) .'assets/admin_ico_portfolio.png', 
        'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        //'rewrite' => true,
        'rewrite' => array('slug'=>'cth_schedule'),
        'capability_type' => 'post'
    );

    register_post_type( 'cth_schedule', $args );
}

//Register Schedule 
add_action( 'init', 'cth_register_cpt_Cth_Schedule' );


//create a custom taxonomy name it cth_schedule_cat for your posts

function cth_create_Cth_Schedule_Cat_hierarchical_taxonomy() {

// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI

  $labels = array(
    'name' => __( 'Schedule Categories', 'cth' ),
    'singular_name' => __( 'Schedule Category', 'cth' ),
    'search_items' =>  __( 'Search Schedule Categories','cth' ),
    'all_items' => __( 'All Schedule Categories','cth' ),
    'parent_item' => __( 'Parent Category','cth' ),
    'parent_item_colon' => __( 'Parent Category:','cth' ),
    'edit_item' => __( 'Edit Category','cth' ), 
    'update_item' => __( 'Update Category','cth' ),
    'add_new_item' => __( 'Add New Category','cth' ),
    'new_item_name' => __( 'New Category Name','cth' ),
    'menu_name' => __( 'Schedule Categories','cth' ),
  );     

// Now register the taxonomy

  register_taxonomy('cth_schedule_cat',array('cth_schedule'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'show_in_nav_menus' => false,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'cth_schedule_cat' ),
  ));

}

//Add Portfolio Skills
    
add_action( 'init', 'cth_create_Cth_Schedule_Cat_hierarchical_taxonomy', 0 );

function cth_register_cpt_Cth_Gallery() {
    
    $g_labels = array( 
        'name' => __( 'Gallery', 'cth' ),
        'singular_name' => __( 'Gallery', 'cth' ),
        'add_new' => __( 'Add New Gallery', 'cth' ),
        'add_new_item' => __( 'Add New Gallery', 'cth' ),
        'edit_item' => __( 'Edit Gallery', 'cth' ),
        'new_item' => __( 'New Gallery', 'cth' ),
        'view_item' => __( 'View Gallery', 'cth' ),
        'search_items' => __( 'Search Galleries', 'cth' ),
        'not_found' => __( 'No Galleries found', 'cth' ),
        'not_found_in_trash' => __( 'No Galleries found in Trash', 'cth' ),
        'parent_item_colon' => __( 'Parent Gallery:', 'cth' ),
        'menu_name' => __( 'Gather Event Galleries', 'cth' ),
    );

    $g_args = array( 
        'labels' => $g_labels,
        'hierarchical' => false,
        'description' => 'List Galleries',
        'supports' => array( 'title'/*, 'editor', 'thumbnail','comments', 'post-formats'*/),
        'taxonomies' => array('cth_gallery_cat'),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        'menu_icon'   => 'dashicons-images-alt2',
        //'menu_icon' => plugin_dir_url( __FILE__ ) .'assets/admin_ico_portfolio.png', 
        'show_in_nav_menus' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => false,
        'can_export' => true,
        'rewrite' => true,
        //'rewrite' => array('slug'=>'cth_schedule'),
        'capability_type' => 'post'
    );

    register_post_type( 'cth_gallery', $g_args );
}

//Register Schedule 
add_action( 'init', 'cth_register_cpt_Cth_Gallery' );

//create a custom taxonomy name it cth_gallery_cat for your posts

function cth_create_Cth_Gallery_Cat_hierarchical_taxonomy() {

// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI

  $labels = array(
    'name' => __( 'Gallery Categories', 'cth' ),
    'singular_name' => __( 'Gallery Category', 'cth' ),
    'search_items' =>  __( 'Search Gallery Categories','cth' ),
    'all_items' => __( 'All Gallery Categories','cth' ),
    'parent_item' => __( 'Parent Category','cth' ),
    'parent_item_colon' => __( 'Parent Category:','cth' ),
    'edit_item' => __( 'Edit Category','cth' ), 
    'update_item' => __( 'Update Category','cth' ),
    'add_new_item' => __( 'Add New Category','cth' ),
    'new_item_name' => __( 'New Category Name','cth' ),
    'menu_name' => __( 'Gallery Categories','cth' ),
  );     

// Now register the taxonomy

  register_taxonomy('cth_gallery_cat',array('cth_gallery'), array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'show_in_nav_menus' => false,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'cth_gallery_cat' ),
  ));

}

//Add Portfolio Skills
    
add_action( 'init', 'cth_create_Cth_Gallery_Cat_hierarchical_taxonomy', 0 );

function cth_gather_plugins_get_cats_for_vc($tax){
    $args = array(
        'orderby'           => 'name', 
        'order'             => 'ASC',
        'hide_empty'        => false, 
        'exclude'           => array(), 
        'exclude_tree'      => array(), 
        'include'           => array(),
        'number'            => '', 
        'fields'            => 'id=>name', 
        'slug'              => '',
        'parent'            => '0',
        'hierarchical'      => true, 
        'child_of'          => 0,
        'childless'         => false,
        'get'               => '', 
        'name__like'        => '',
        'description__like' => '',
        'pad_counts'        => false, 
        'offset'            => '', 
        'search'            => '', 
        'cache_domain'      => 'core'
    ); 
    $terms      = get_terms( $tax, $args );

    return $terms;
}

function cth_gather_plugins_register_vc_elements(){
    if(function_exists('vc_map')){
        vc_map( array(
            "name"      => __("Gather Schedules", 'cth-gather-plugins'),
            "base"      => "cth_schedules",
            "class"     => "",
            "icon" => plugin_dir_url(__FILE__ ) . "/assets/cth-icon.png",
            "category"=>"Gather",
            "html_template" => plugin_dir_path(__FILE__ ).'/vc_templates/cth_schedules.php',
            "params"    => array(
                array(
                    "type" => "textfield", 
                    "heading" => __("Schedule Category IDs to exclude", "gather"), 
                    "param_name" => "cat_ids", 
                    "description" => __("Enter schedule category ids to exclude, separated by a comma. Leave empty to disaplay all categories.", "gather")
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Order Schedule Categories by', 'gather'), 
                    "param_name" => "cat_order_by", 
                    "value" => array(
                        __('Name', 'gather') => 'name', 
                        __('ID', 'gather') => 'id', 
                        __('Count', 'gather') => 'count', 
                        __('Slug', 'gather') => 'slug', 
                        __('None', 'gather') => 'none',
                    ), 
                    "description" => __("Order Schedule Categories by", 'gather'), 
                    "default" => 'name',
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Order Schedule Categories', 'gather'), 
                    "param_name" => "cat_order", 
                    "value" => array(
                        __('Ascending', 'gather') => 'ASC',
                        __('Descending', 'gather') => 'DESC', 
                        
                    ), 
                    "description" => __("Order Schedule Categories", 'gather'),
                    "default" => 'ASC',
                ), 
                array(
                    "type" => "textfield", 
                    "heading" => __("Enter Schedule IDs", "gather"), 
                    "param_name" => "ids", 
                    "description" => __("Enter schedule ids to show, separated by a comma.", "gather")
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Order Schedules by', 'gather'), 
                    "param_name" => "order_by", 
                    "value" => array(
                        __('Date', 'gather') => 'date', 
                        __('ID', 'gather') => 'ID', 
                        __('Author', 'gather') => 'author', 
                        __('Title', 'gather') => 'title', 
                        __('Modified', 'gather') => 'modified',
                    ), 
                    "description" => __("Order Schedules by", 'gather'), 
                    "default" => 'date',
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Order Schedules', 'gather'), 
                    "param_name" => "order", 
                    "value" => array(
                        __('Ascending', 'gather') => 'ASC',
                        __('Descending', 'gather') => 'DESC', 
                        
                    ), 
                    "description" => __("Order Schedules", 'gather'),
                    "default" => 'ASC',
                ), 
                
                array(
                    "type" => "textfield",
                    "heading" => __("Extra class name", "gather"),
                    "param_name" => "el_class",
                    "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "gather")
                ),

            )));


        if ( class_exists( 'WPBakeryShortCode' ) ) {
            class WPBakeryShortCode_Cth_Schedules extends WPBakeryShortCode {}
        }

        vc_map( array(
            "name"      => __("Gather Gallery", 'cth-gather-plugins'),
            "base"      => "cth_gather_gallery",
            "class"     => "",
            "icon" => plugin_dir_url(__FILE__ ) . "/assets/cth-icon.png",
            "category"=>"Gather",
            "html_template" => plugin_dir_path(__FILE__ ).'/vc_templates/cth_gather_gallery.php',
            "params"    => array(
                array(
                    "type" => "textfield", 
                    "heading" => __("Gallery Category IDs to exclude", "gather"), 
                    "param_name" => "cat_ids", 
                    "description" => __("Enter gallery category ids to exclude, separated by a comma. Leave empty to disaplay all categories.", "gather")
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Order Gallery Categories by', 'gather'), 
                    "param_name" => "cat_order_by", 
                    "value" => array(
                        __('Name', 'gather') => 'name', 
                        __('ID', 'gather') => 'id', 
                        __('Count', 'gather') => 'count', 
                        __('Slug', 'gather') => 'slug', 
                        __('None', 'gather') => 'none',
                    ), 
                    "description" => __("Order Gallery Categories by", 'gather'), 
                    "default" => 'name',
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Order Gallery Categories', 'gather'), 
                    "param_name" => "cat_order", 
                    "value" => array(
                        __('Ascending', 'gather') => 'ASC',
                        __('Descending', 'gather') => 'DESC', 
                        
                    ), 
                    "description" => __("Order Gallery Categories", 'gather'),
                    "default" => 'ASC',
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Show Nav Tabs', 'gather'), 
                    "param_name" => "showtabs", 
                    "value" => array(
                        __('Yes', 'gather') => 'yes',
                        __('No', 'gather') => 'no', 
                        
                    ), 
                    "description" => __("Show nav tab for each gallery category", 'gather'),
                    "default" => 'yes',
                ), 
                array(
                    "type" => "textfield", 
                    "heading" => __("Enter Gallery IDs", "gather"), 
                    "param_name" => "ids", 
                    "description" => __("Enter galley ids to show, separated by a comma.", "gather")
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Order Galleries by', 'gather'), 
                    "param_name" => "order_by", 
                    "value" => array(
                        __('Date', 'gather') => 'date', 
                        __('ID', 'gather') => 'ID', 
                        __('Author', 'gather') => 'author', 
                        __('Title', 'gather') => 'title', 
                        __('Modified', 'gather') => 'modified',
                    ), 
                    "description" => __("Order Galleries by", 'gather'), 
                    "default" => 'date',
                ), 
                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Order Galleries', 'gather'), 
                    "param_name" => "order", 
                    "value" => array(
                        __('Ascending', 'gather') => 'ASC',
                        __('Descending', 'gather') => 'DESC', 
                        
                    ), 
                    "description" => __("Order Galleries", 'gather'),
                    "default" => 'ASC',
                ), 

                array(
                "type" => "dropdown",
                "heading" => __('Animation Effect', 'gather'),
                "param_name" => "effect",
                "value" => array(
                                __('bounce','gather')=>'bounce',
                                __('flash','gather')=>'flash',
                                __('pulse','gather')=>'pulse',
                                __('rubberBand','gather')=>'rubberBand',
                                __('shake','gather')=>'shake',
                                __('swing','gather')=>'swing',
                                __('tada','gather')=>'tada',
                                __('wobble','gather')=>'wobble',

                                __('bounceIn','gather')=>'bounceIn',
                                __('bounceInUp','gather')=>'bounceInUp',
                                __('bounceInDown','gather')=>'bounceInDown',
                                __('bounceInLeft','gather')=>'bounceInLeft',
                                __('bounceInRight','gather')=>'bounceInRight',
                                __('bounceOut','gather')=>'bounceOut',
                                __('bounceOutUp','gather')=>'bounceOutUp',
                                __('bounceOutDown','gather')=>'bounceOutDown',
                                __('bounceOutLeft','gather')=>'bounceOutLeft',
                                __('bounceOutRight','gather')=>'bounceOutRight',

                                __('fadeIn','gather')=>'fadeIn',
                                __('fadeInUp','gather')=>'fadeInUp',
                                __('fadeInDown','gather')=>'fadeInDown',
                                __('fadeInLeft','gather')=>'fadeInLeft',
                                __('fadeInRight','gather')=>'fadeInRight',
                                __('fadeInUpBig','gather')=>'fadeInUpBig',
                                __('fadeInDownBig','gather')=>'fadeInDownBig',
                                __('fadeInLeftBig','gather')=>'fadeInLeftBig',
                                __('fadeInRightBig','gather')=>'fadeInRightBig',

                                __('fadeOut','gather')=>'fadeOut',
                                __('fadeOutUp','gather')=>'fadeOutUp',
                                __('fadeOutDown','gather')=>'fadeOutDown',
                                __('fadeOutLeft','gather')=>'fadeOutLeft',
                                __('fadeOutRight','gather')=>'fadeOutRight',
                                __('fadeOutUpBig','gather')=>'fadeOutUpBig',
                                __('fadeOutDownBig','gather')=>'fadeOutDownBig',
                                __('fadeOutLeftBig','gather')=>'fadeOutLeftBig',
                                __('fadeOutRightBig','gather')=>'fadeOutRightBig',

                                __('flipInX','gather')=>'flipInX',
                                __('flipInY','gather')=>'flipInY',
                                __('flipOutX','gather')=>'flipOutX',
                                __('flipOutY','gather')=>'flipOutY',
                                __('rotateIn','gather')=>'rotateIn',
                                __('rotateInDownLeft','gather')=>'rotateInDownLeft',
                                __('rotateInDownRight','gather')=>'rotateInDownRight',
                                __('rotateInUpLeft','gather')=>'rotateInUpLeft',
                                __('rotateInUpRight','gather')=>'rotateInUpRight',

                                __('rotateOut','gather')=>'rotateOut',
                                __('rotateOutDownLeft','gather')=>'rotateOutDownLeft',
                                __('rotateOutDownRight','gather')=>'rotateOutDownRight',
                                __('rotateOutUpLeft','gather')=>'rotateOutUpLeft',
                                __('rotateOutUpRight','gather')=>'rotateOutUpRight',

                                __('rotateOut','gather')=>'rotateOut',
                                __('rotateOutDownLeft','gather')=>'rotateOutDownLeft',
                                __('rotateOutDownRight','gather')=>'rotateOutDownRight',
                                __('rotateOutUpLeft','gather')=>'rotateOutUpLeft',
                                __('rotateOutUpRight','gather')=>'rotateOutUpRight',

                                __('slideInDown','gather')=>'slideInDown',
                                __('slideInLeft','gather')=>'slideInLeft',
                                __('slideInRight','gather')=>'slideInRight',
                                __('slideOutLeft','gather')=>'slideOutLeft',
                                __('slideOutRight','gather')=>'slideOutRight',
                                __('slideOutUp','gather')=>'slideOutUp',
                                __('slideInUp','gather')=>'slideInUp',
                                __('slideOutDown','gather')=>'slideOutDown',

                                __('hinge','gather')=>'hinge',

                                __('rollIn','gather')=>'rollIn',
                                __('rollOut','gather')=>'rollOut',
                                

                                __('zoomIn','gather')=>'zoomIn',
                                __('zoomInUp','gather')=>'zoomInUp',
                                __('zoomInDown','gather')=>'zoomInDown',
                                __('zoomInLeft','gather')=>'zoomInLeft',
                                __('zoomInRight','gather')=>'zoomInRight',

                                __('zoomOut','gather')=>'zoomOut',
                                __('zoomOutUp','gather')=>'zoomOutUp',
                                __('zoomOutDown','gather')=>'zoomOutDown',
                                __('zoomOutLeft','gather')=>'zoomOutLeft',
                                __('zoomOutRight','gather')=>'zoomOutRight',
                            ),                              
              "description" => __("Select animation effect for each gallery item (You must need enter animation delay time one gallery item editing screen to make it work. Example: 0s)", "gather"),      
              "default" => 'fadeIn',
            ),
                
                array(
                    "type" => "textfield",
                    "heading" => __("Extra class name", "gather"),
                    "param_name" => "el_class",
                    "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "gather")
                ),

            )));


        if ( class_exists( 'WPBakeryShortCode' ) ) {
            class WPBakeryShortCode_Cth_Gather_Gallery extends WPBakeryShortCode {}
        }

    }
}
add_action('init','cth_gather_plugins_register_vc_elements' );
if(!function_exists('cth_schedule_cat_columns_head')){
    function cth_schedule_cat_columns_head($defaults) {
        $defaults['cth_schedule_cat_id'] = __('ID','cth-gather-plugins');
        return $defaults;
    }
}

if(!function_exists('cth_schedule_cat_columns_content')){
    function cth_schedule_cat_columns_content($c, $column_name, $term_id) {
        if ($column_name == 'cth_schedule_cat_id') {
            echo $term_id;
        }
    }
}
add_filter('manage_edit-cth_schedule_cat_columns', 'cth_schedule_cat_columns_head');
add_filter('manage_cth_schedule_cat_custom_column', 'cth_schedule_cat_columns_content', 10, 3);

if(!function_exists('cth_schedule_columns_head')){
    function cth_schedule_columns_head($defaults) {
        $defaults['cth_schedule_time'] = 'Time';
        $defaults['cth_schedule_speaker'] = 'Speaker';
        $defaults['cth_schedule_id'] = 'ID';
        //unset($defaults['date']);
        return $defaults;
    }
}
if(!function_exists('cth_schedule_columns_content')){
    // CUSTOM POSTS
    function cth_schedule_columns_content($column_name, $schedule_ID) {
        if ($column_name == 'cth_schedule_id') {
            echo $schedule_ID;
            //print_r(cth_gather_plugins_get_cats_for_vc('cth_schedule_cat'));
        }
        if($column_name == 'cth_schedule_time'){
            $schedule_time = get_post_meta( $schedule_ID, 'cth_schedule_time', true );
            if($schedule_time){
                echo $schedule_time;
            }
        }
        if($column_name == 'cth_schedule_speaker'){
            $schedule_speaker = get_post_meta( $schedule_ID, 'cth_schedule_speaker', true );
            if($schedule_speaker){
                echo $schedule_speaker;
            }
        }
    }
}

add_filter('manage_cth_schedule_posts_columns', 'cth_schedule_columns_head', 10);
add_action('manage_cth_schedule_posts_custom_column', 'cth_schedule_columns_content', 10, 2);

add_filter( 'manage_edit-cth_schedule_sortable_columns', 'cth_schedule_sortable_columns' );
if(!function_exists('cth_schedule_sortable_columns')){
    function cth_schedule_sortable_columns( $columns ) {
        $columns['taxonomy-cth_schedule_cat'] = 'taxonomy-cth_schedule_cat';
        $columns['cth_schedule_time'] = 'cth_schedule_time';
        $columns['cth_schedule_speaker'] = 'cth_schedule_speaker';
     
        //To make a column 'un-sortable' remove it from the array
        //unset($columns['date']);
     
        return $columns;
    }
}

add_action( 'pre_get_posts', 'cth_schedule_orderby' );
function cth_schedule_orderby( $query ) {
    if( ! is_admin() )
        return;
 
    $orderby = $query->get( 'orderby');
    // if( 'taxonomy-cth_schedule_cat' == $orderby ) {
    //     $query->set('meta_key','taxonomy-cth_schedule_cat');
    //     $query->set('orderby','meta_value');
    // }
    if( 'cth_schedule_time' == $orderby ) {
        $query->set('meta_key','cth_schedule_time');
        $query->set('orderby','meta_value');
    }
    if( 'cth_schedule_speaker' == $orderby ) {
        $query->set('meta_key','cth_schedule_speaker');
        $query->set('orderby','meta_value');
    }

}

if(!function_exists('cth_gallery_cat_columns_head')){
    function cth_gallery_cat_columns_head($defaults) {
        $defaults['cth_gallery_cat_id'] = __('ID','cth-gather-plugins');
        return $defaults;
    }
}

if(!function_exists('cth_gallery_cat_columns_content')){
    function cth_gallery_cat_columns_content($c, $column_name, $term_id) {
        if ($column_name == 'cth_gallery_cat_id') {
            echo $term_id;
        }
    }
}
add_filter('manage_edit-cth_gallery_cat_columns', 'cth_gallery_cat_columns_head');
add_filter('manage_cth_gallery_cat_custom_column', 'cth_gallery_cat_columns_content', 10, 3);


if(!function_exists('cth_gallery_columns_head')){
    function cth_gallery_columns_head($defaults) {
        $return = array();
        $return['title'] = $defaults['title'];
        //$return['cth_gallery_categories'] = 'Categories';
        $return['cth_gallery_type'] = __('Media Type','cth-gather-plugins');
        $return['cth_gallery_thumb'] = __('Thumbnail','cth-gather-plugins');
        $return['date'] = $defaults['date'];
        $return['gallery_id'] = __('ID','cth-gather-plugins');
        //$return = array_merge($return,$defaults);
        return $return;
    }
}
if(!function_exists('cth_gallery_columns_content')){
    // CUSTOM POSTS
    function cth_gallery_columns_content($column_name, $gallery_ID) {
        if ($column_name == 'cth_gallery_thumb') {
            $single_image_thumb = get_post_meta( $gallery_ID, 'cth_gallery_image', true );
            if($single_image_thumb){
                echo '<img src="'.$single_image_thumb['url'].'" alt="" style="max-width:100px;height:auto;">';
            }
            
        }
        if ($column_name == 'cth_gallery_type') {
            $type_arrs =  array(
                                'single_image'=>__('Single Image','cth-gather-plugins'),
                                'video'=>__('Video','cth-gather-plugins'),
                            );
            $type = get_post_meta( $gallery_ID, 'cth_gallery_type', true );
            if(isset($type_arrs[$type])){
                echo '<strong>'.$type_arrs[$type].'</strong>';
            }else{
                echo '<strong>'.__('Single Image','cth-gather-plugins').'</strong>';
            }
        }
        if($column_name == 'gallery_id'){
            echo $gallery_ID;
        }
    }
}

add_filter('manage_cth_gallery_posts_columns', 'cth_gallery_columns_head', 10);
add_action('manage_cth_gallery_posts_custom_column', 'cth_gallery_columns_content', 10, 2);

add_filter( 'manage_edit-cth_gallery_sortable_columns', 'cth_gallery_sortable_columns' );
if(!function_exists('cth_gallery_sortable_columns')){
    function cth_gallery_sortable_columns( $columns ) {
        $columns['cth_gallery_type'] = 'cth_gallery_type';
     
        //To make a column 'un-sortable' remove it from the array
        //unset($columns['date']);
     
        return $columns;
    }
}

add_action( 'pre_get_posts', 'cth_gallery_orderby' );
function cth_gallery_orderby( $query ) {
    if( ! is_admin() )
        return;
 
    $orderby = $query->get( 'orderby');
 
    if( 'cth_gallery_type' == $orderby ) {
        $query->set('meta_key','cth_gallery_type');
        $query->set('orderby','meta_value');
    }

}
/**
 * Adds a box to the main column on the Reservation edit screens.
 */
function cth_gather_plugins_add_meta_box() {

    $screens = array( 'cth_schedule');

    foreach ( $screens as $screen ) {

        add_meta_box(
            'cth_schedule_time',
            __( 'Schedule Time', 'cth-gather-plugins' ),
            'cth_gather_plugins_meta_box_schedule_time_callback',
            $screen
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_schedule_speaker',
            __( 'Schedule Speaker', 'cth-gather-plugins' ),
            'cth_gather_plugins_meta_box_schedule_speaker_callback',
            $screen
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
    }
    $screens = array( 'cth_gallery');

    foreach ( $screens as $screen ) {

        add_meta_box(
            'cth_gallery_type',
            __( 'Media Type', 'cth-gather-plugins' ),
            'cth_gather_plugins_meta_box_gallery_type_callback',
            $screen
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_gallery_image',
            __( 'Select Image for Single Image media type or Thumnail for Video media type', 'cth-gather-plugins' ),
            'cth_gather_plugins_meta_box_gallery_image_callback',
            $screen
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        
        add_meta_box(
            'cth_gallery_video_link',
            __( 'Video Link for Video media type', 'cth-gather-plugins' ),
            'cth_gather_plugins_meta_box_gallery_video_link_callback',
            $screen
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_gallery_image_grid_size',
            __( 'Grid Size', 'cth-gather-plugins' ),
            'cth_gather_plugins_meta_box_gallery_image_grid_size_callback',
            $screen
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_gallery_media_info',
            __( 'Media Info', 'cth-gather-plugins' ),
            'cth_gather_plugins_meta_box_gallery_media_info_callback',
            $screen
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_gallery_ani_delay',
            __( 'Animation Delay Time', 'cth-gather-plugins' ),
            'cth_gather_plugins_meta_box_gallery_ani_delay_callback',
            $screen
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        
    }
}
add_action( 'add_meta_boxes', 'cth_gather_plugins_add_meta_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current schedule.
 */
function cth_gather_plugins_meta_box_schedule_time_callback( $post ) {

    
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'cth_schedule_time', true );

    echo '<table class="form-table"><tbody><tr>';
    echo '<th style="width:20%">';
        echo '<label for="cth_schedule_time">';
        _e( 'Schedule Time', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<input type="text" id="cth_schedule_time" name="cth_schedule_time" value="' . esc_attr( $value ) . '" size="25" />';
    echo '</td>';
    echo '</tr></tbody></table>';

    
    
}
/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current schedule.
 */
function cth_gather_plugins_meta_box_schedule_speaker_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'cth_schedule_speaker', true );

    echo '<table class="form-table"><tbody><tr>';
    echo '<th style="width:20%">';
        echo '<label for="cth_schedule_speaker">';
        _e( 'Schedule Speaker', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<input type="text" id="cth_schedule_speaker" name="cth_schedule_speaker" value="' . esc_attr( $value ) . '" size="25" />';
    echo '</td>';
    echo '</tr></tbody></table>';

    
    
}
/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function cth_gather_plugins_save_meta_box_datas( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['cth_gather_plugins_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['cth_gather_plugins_meta_box_nonce'], 'cth_gather_plugins_save_meta_box_data' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'cth_schedule' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */
    
    // Make sure that it is set.
    if ( ! isset( $_POST['cth_schedule_time'] ) || ! isset( $_POST['cth_schedule_speaker'] ) ) {
        return;
    }

    // Sanitize user input.
    $schedule_time_data = sanitize_text_field( $_POST['cth_schedule_time'] );
    $schedule_speaker_data = sanitize_text_field( $_POST['cth_schedule_speaker'] );


    // Update the meta field in the database.
    update_post_meta( $post_id, 'cth_schedule_time', $schedule_time_data );
    update_post_meta( $post_id, 'cth_schedule_speaker', $schedule_speaker_data );

}
add_action( 'save_post', 'cth_gather_plugins_save_meta_box_datas' );

function cth_gather_plugins_meta_box_gallery_type_callback( $post ) {

    // default gallery media type
    $defauls = array('single_image'=>__('Single Image','cth-gather-plugins'), 'video'=>__('Video','cth-gather-plugins'));

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'cth_gallery_type', true );

    echo '<table class="form-table"><tbody><tr>';
    echo '<th style="width:20%">';
        echo '<label for="cth_gallery_type">';
        _e( 'Media Type', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<select id="cth_gallery_type" name="cth_gallery_type">';
        foreach ($defauls as $key => $val) {
            $selected = '';
            if($value === $key){
                $selected = ' selected="selected"';
            }
            echo '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
        }
        echo '</select>';
    echo '</td>';
    echo '</tr></tbody></table>';

    
    
}

function cth_gather_plugins_meta_box_gallery_image_grid_size_callback( $post ) {

    // default gallery media type
    $defauls = array('size_one'=>__('Size One','cth-gather-plugins'), 'size_two'=>__('Size Two','cth-gather-plugins'), 'size_three'=>__('Size Three','cth-gather-plugins') , 'size_full'=>__('Size Full','cth-gather-plugins')  );

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'cth_gallery_image_grid_size', true );

    echo '<table class="form-table"><tbody><tr>';
    echo '<th style="width:20%">';
        echo '<label for="cth_gallery_image_grid_size">';
        _e( 'Grid Size', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<select id="cth_gallery_image_grid_size" name="cth_gallery_image_grid_size">';
        foreach ($defauls as $key => $val) {
            $selected = '';
            if($value === $key){
                $selected = ' selected="selected"';
            }
            echo '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
        }
        echo '</select>';
        
    echo '</td>';
    echo '</tr></tbody></table>';

    
    
}



function cth_gather_plugins_meta_box_gallery_image_callback( $post ) {

    wp_enqueue_media();
    wp_enqueue_script('gather_tax_meta', plugin_dir_url(__FILE__ ) . '/assets/admin/media_selector.js', array('jquery'), null, true);

    
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'cth_gallery_image', true );

    ?>
    <table class="form-table"><tbody><tr>
    <th style="width:20%"><label><?php
    _e('Image Url', 'cth-gather-plugins'); ?></label></th>
        <td>
            <input type="text" class="width100per mb20" name="cth_gallery_image[url]" id="cth_gallery_image[url]" value="<?php
    echo isset($value['url']) ? esc_attr($value['url']) : ''; ?>">
            <img id="cth_gallery_image[preview]" class="mb20" src="<?php
    echo isset($value['url']) ? esc_attr($value['url']) : ''; ?>" alt="" <?php
    echo isset($value['url']) ? ' style="display:block;width:200px;height=auto;"' : ' style="display:none;width:200px;height=auto;"'; ?>>
            <input type="hidden" name="cth_gallery_image[id]" id="cth_gallery_image[id]" value="<?php
    echo isset($value['id']) ? esc_attr($value['id']) : ''; ?>">
            
            <p class="description"><a href="#" class="button button-primary upload_image_button metakey-term_meta fieldkey-cth_gallery_image"><?php
    _e('Upload Image', 'cth-gather-plugins'); ?></a>  <a href="#" class="button button-secondary remove_image_button metakey-term_meta fieldkey-cth_gallery_image"><?php
    _e('Remove', 'cth-gather-plugins'); ?></a></p>
        </td>
    </tr></tbody></table>

    <?php
    
}
function cth_gather_plugins_meta_box_gallery_media_info_callback( $post ) {

    
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'cth_gallery_media_info', true );

    echo '<table class="form-table"><tbody><tr>';
    echo '<th style="width:20%">';
        echo '<label for="cth_gallery_media_info">';
        _e( 'Media Title', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<input type="text" id="cth_gallery_media_info" name="cth_gallery_media_info" value="' . esc_attr( $value ) . '" size="25" />';
    echo '</td>';
    echo '</tr></tbody></table>';

    
    
}
function cth_gather_plugins_meta_box_gallery_video_link_callback( $post ) {
    global $wp_embed;
    
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'cth_gallery_video_link', true );

    echo '<table class="form-table"><tbody><tr>';
    echo '<th style="width:20%">';
        echo '<label for="cth_gallery_video_link">';
        _e( 'Video Link', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<input type="text" class="mb20 width100per" id="cth_gallery_video_link" name="cth_gallery_video_link" value="' . esc_attr( $value ) . '" size="25" />';
        if(!empty($value)){
            // ping WordPress for an embed
        $check_embed = $wp_embed->run_shortcode( '[embed width="200" height="112"]'. $value .'[/embed]' );
        echo '<div class="embed_status">'. $check_embed .'<p class="cmb_remove_wrapper"><a href="#" class="cmb_remove_file_button" >'. __( 'Remove Embed', 'cmb' ) .'</a></p></div>';
        }
    echo '</td>';
    echo '</tr></tbody></table>';

    
    
}
function cth_gather_plugins_meta_box_gallery_ani_delay_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'cth_gallery_ani_delay', true );

    echo '<table class="form-table"><tbody><tr>';
    echo '<th style="width:20%">';
        echo '<label for="cth_gallery_ani_delay">';
        _e( 'Animation Dealy Time - in seconds, ex: 0.1s', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<input type="text" id="cth_gallery_ani_delay" name="cth_gallery_ani_delay" value="' . esc_attr( $value ) . '" size="25" />';
    echo '</td>';
    echo '</tr></tbody></table>';

    
    
}
/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function cth_gather_plugins_save_meta_box_gallery_datas( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['cth_gather_plugins_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['cth_gather_plugins_meta_box_nonce'], 'cth_gather_plugins_save_meta_box_data' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'cth_gallery' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */
    
    // Make sure that it is set.
    if ( ! isset( $_POST['cth_gallery_type'] ) || 
         ! isset( $_POST['cth_gallery_image'] ) || 
         !isset($_POST['cth_gallery_media_info']) || 
         !isset($_POST['cth_gallery_image_grid_size']) || 
         !isset($_POST['cth_gallery_video_link']) ) {
        return;
    }

    // Sanitize user input.
    $gallery_type_data = sanitize_text_field( $_POST['cth_gallery_type'] );
    $gallery_media_info_data = sanitize_text_field( $_POST['cth_gallery_media_info'] );
    $gallery_image_grid_size_data = sanitize_text_field( $_POST['cth_gallery_image_grid_size'] );
    $gallery_video_link_data = sanitize_text_field( $_POST['cth_gallery_video_link'] );
    $gallery_ani_delay_data = sanitize_text_field( $_POST['cth_gallery_ani_delay'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'cth_gallery_type', $gallery_type_data );
    update_post_meta( $post_id, 'cth_gallery_image', $_POST['cth_gallery_image'] );
    update_post_meta( $post_id, 'cth_gallery_media_info', $gallery_media_info_data );
    update_post_meta( $post_id, 'cth_gallery_image_grid_size', $gallery_image_grid_size_data );
    update_post_meta( $post_id, 'cth_gallery_video_link', $gallery_video_link_data );
    update_post_meta( $post_id, 'cth_gallery_ani_delay', $gallery_ani_delay_data );
}
add_action( 'save_post', 'cth_gather_plugins_save_meta_box_gallery_datas' );

function cth_gather_plugins_register_image_size(){
    add_image_size('gallery_size_one', 263, 175, true);
    add_image_size('gallery_size_two', 555, 387, true);
}

add_action('init','cth_gather_plugins_register_image_size' );

require_once dirname(__FILE__).'/includes/event_registration.php';

function cth_gather_plugins_enqueue_scripts($hook) {
    // if ( 'edit.php' != $hook ) {
    //     return;
    // }
    wp_enqueue_script('gather-admin', plugin_dir_url(__FILE__ ) . 'assets/admin/scripts.js', array('jquery'), null, true);
}
add_action( 'admin_enqueue_scripts', 'cth_gather_plugins_enqueue_scripts' );

register_activation_hook( __FILE__, 'cth_gather_plugins_activation_callback' );
function cth_gather_plugins_activation_callback(){
      global $wpdb;
      $confirm_page_title = __('Gather Purchase Confirm','cth-gather-plugins');
      $return_page_title = __('Gather Purchase Return','cth-gather-plugins');
      $cancelled_page_title = __('Gather Purchase Cancelled','cth-gather-plugins');
      
      $reg_com_page_title = __('Gather Registration Completed','cth-gather-plugins');

      $confirm_page_id = 0;
      $return_page_id = 0;
      $cancelled_page_id = 0;
      $reg_com_page_id = 0;

      delete_option('cth_eventres_confirm_page_id');
      add_option('cth_eventres_confirm_page_id', $confirm_page_id , '', 'yes');

      delete_option('cth_eventres_return_page_id');
      add_option('cth_eventres_return_page_id', $return_page_id , '', 'yes');

      delete_option('cth_eventres_cancelled_page_id');
      add_option('cth_eventres_cancelled_page_id', $cancelled_page_id , '', 'yes');

      delete_option('cth_eventres_reg_com_page_id');
      add_option('cth_eventres_reg_com_page_id', $reg_com_page_id , '', 'yes');

      $confirm_page = get_page_by_title($confirm_page_title);

      if (!$confirm_page)
      {
        // Create post object
        $_p = array();
        $_p['post_title']     = $confirm_page_title;
        //$_p['post_name']     = $page_name;
        $_p['post_content']   = '[eventres_confirm]';//__("This purchase comfirm page was generated by the plugin. Please don't edit or delete.",'cth-gather-plugins');
        $_p['post_status']    = 'publish';
        $_p['post_type']      = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status']    = 'closed';

        // Insert the post into the database
        $confirm_page_id = wp_insert_post($_p);

      }
      else
      {
        // the plugin may have been previously active and the page may just be trashed...
        $confirm_page_id = $confirm_page->ID;

        //make sure the page is not trashed...
        $confirm_page->post_status = 'publish';
        $confirm_page_id = wp_update_post($confirm_page);
      }

      delete_option('cth_eventres_confirm_page_id');
      add_option('cth_eventres_confirm_page_id', $confirm_page_id);

      // Return Page
      $return_page = get_page_by_title($return_page_title);

      if (!$return_page)
      {
        // Create post object
        $_p = array();
        $_p['post_title']     = $return_page_title;
        //$_p['post_name']     = $page_name;
        $_p['post_content']   = '<section id="top" class="text-center vertical-space-lg">
    <div class="container">
        <div class="logo">
            <a href="#"><img src="'.CTH_EVENTRES_DIR_URL.'assets/img/check.png" alt="Error Icon"></a>
        </div>
        <h1 class="headline">Thank you! </h1>
        <h5 class="headline-support">Your payment is received and we will contact you soon.</h5>
    </div>
</section>
<p class="text-center"><a href="http://themeforest.net/item/event-conference-landing-page-template-gather/12137712?license=regular&amp;open_purchase_for_item_id=12137712&amp;ref=surjithctly&amp;purchasable=source&amp;utm_source=gather-demo-landing" class="btn btn-success btn-lg">Buy this Template Now</a></p>
<p class="text-center"> <a href="'.home_url('/' ).'" class="btn btn-link"> ← Back to Home</a> </p>';//__("This purchase comfirm page was generated by the plugin. Please don't edit or delete.",'cth-gather-plugins');
        $_p['post_status']    = 'publish';
        $_p['post_type']      = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status']    = 'closed';

        // Insert the post into the database
        $return_page_id = wp_insert_post($_p);

        // for gather theme
        update_post_meta( $return_page_id, '_wp_page_template', 'homepage-nonav.php');
        update_post_meta( $return_page_id, '_cmb_navigation_type', 'reveal');
      }
      else
      {
        // the plugin may have been previously active and the page may just be trashed...
        $return_page_id = $return_page->ID;

        //make sure the page is not trashed...
        $return_page->post_status = 'publish';
        $return_page_id = wp_update_post($return_page);
      }

      delete_option('cth_eventres_return_page_id');
      add_option('cth_eventres_return_page_id', $return_page_id);

      // Cancelled page

      $cancelled_page = get_page_by_title($cancelled_page_title);

      if (!$cancelled_page)
      {
        // Create post object
        $_p = array();
        $_p['post_title']     = $cancelled_page_title;
        //$_p['post_name']     = $page_name;
        $_p['post_content']   = '[eventres_cancelled]<section id="top" class="text-center vertical-space-lg">
<div class="container">
<div class="err-icon">[faicon name="exclamation"]</div>
<h1 class="headline">Oops!</h1>
<h5 class="headline-support">You have cancelled the pruchase.</h5>
</div>
</section>
<p class="text-center"><a class="btn btn-link" href="'.home_url('/' ).'"> ← Back to Home</a></p>';//__("This purchase comfirm page was generated by the plugin. Please don't edit or delete.",'cth-gather-plugins');
        $_p['post_status']    = 'publish';
        $_p['post_type']      = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status']    = 'closed';

        // Insert the post into the database
        $cancelled_page_id = wp_insert_post($_p);

        // for gather theme
        update_post_meta( $cancelled_page_id, '_wp_page_template', 'homepage-nonav.php');
        update_post_meta( $cancelled_page_id, '_cmb_navigation_type', 'reveal');
      }
      else
      {
        // the plugin may have been previously active and the page may just be trashed...
        $cancelled_page_id = $cancelled_page->ID;

        //make sure the page is not trashed...
        $cancelled_page->post_status = 'publish';
        $cancelled_page_id = wp_update_post($cancelled_page);
      }

      delete_option('cth_eventres_cancelled_page_id');
      add_option('cth_eventres_cancelled_page_id', $cancelled_page_id);

      // Registration Completed Page

      $reg_com_page = get_page_by_title($reg_com_page_title);

      if (!$reg_com_page)
      {
        // Create post object
        $_p = array();
        $_p['post_title']     = $reg_com_page_title;
        //$_p['post_name']     = $page_name;
        $_p['post_content']   = '<section id="top" class="text-center vertical-space-lg">
    <div class="container">
        <div class="logo">
            <a href="#"><img src="'.CTH_EVENTRES_DIR_URL.'assets/img/check.png" alt="Error Icon"></a>
        </div>
        <h1 class="headline">Thank you! </h1>
        <h5 class="headline-support">Your submission is received and we will contact you soon.</h5>
    </div>
</section>
<p class="text-center"><a href="http://themeforest.net/item/event-conference-landing-page-template-gather/12137712?license=regular&amp;open_purchase_for_item_id=12137712&amp;ref=surjithctly&amp;purchasable=source&amp;utm_source=gather-demo-landing" class="btn btn-success btn-lg">Buy this Template Now</a></p>
<p class="text-center"> <a href="'.home_url('/' ).'" class="btn btn-link"> ← Back to Home</a> </p>';
        $_p['post_status']    = 'publish';
        $_p['post_type']      = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status']    = 'closed';

        // Insert the post into the database
        $reg_com_page_id = wp_insert_post($_p);

        // for gather theme
        update_post_meta( $reg_com_page_id, '_wp_page_template', 'homepage-nonav.php');
        update_post_meta( $reg_com_page_id, '_cmb_navigation_type', 'reveal');
      }
      else
      {
        // the plugin may have been previously active and the page may just be trashed...
        $reg_com_page_id = $reg_com_page->ID;

        //make sure the page is not trashed...
        $reg_com_page->post_status = 'publish';
        $reg_com_page_id = wp_update_post($reg_com_page);
      }

      delete_option('cth_eventres_reg_com_page_id');
      add_option('cth_eventres_reg_com_page_id', $reg_com_page_id);
}
register_deactivation_hook(__FILE__ , 'cth_gather_plugins_deactivation_callback' );
function cth_gather_plugins_deactivation_callback(){
    global $wpdb;
    $hard = true;

    $confirm_id = get_option('cth_eventres_confirm_page_id');
    if($confirm_id && $hard == true)
        wp_delete_post($confirm_id, true);
    elseif($confirm_id && $hard == false)
        wp_delete_post($confirm_id);

    delete_option('cth_eventres_confirm_page_id');

    //return page
    $return_id = get_option('cth_eventres_return_page_id');
    if($return_id && $hard == true)
        wp_delete_post($return_id, true);
    elseif($return_id && $hard == false)
        wp_delete_post($return_id);

    delete_option('cth_eventres_return_page_id');

    // cancelled page
    $cancelled_id = get_option('cth_eventres_cancelled_page_id');
    if($cancelled_id && $hard == true)
        wp_delete_post($cancelled_id, true);
    elseif($cancelled_id && $hard == false)
        wp_delete_post($cancelled_id);

    delete_option('cth_eventres_cancelled_page_id');

    // registration completed page
    $reg_com_page_id = get_option('cth_eventres_reg_com_page_id');
    if($reg_com_page_id && $hard == true)
        wp_delete_post($reg_com_page_id, true);
    elseif($reg_com_page_id && $hard == false)
        wp_delete_post($reg_com_page_id);

    delete_option('cth_eventres_reg_com_page_id');

}

function cth_gather_plugins_init() {
    $plugin_dir = basename(dirname(__FILE__));
    load_plugin_textdomain( 'cth-gather-plugins', false, $plugin_dir . '/languages' );
}
add_action('plugins_loaded', 'cth_gather_plugins_init');

?>
