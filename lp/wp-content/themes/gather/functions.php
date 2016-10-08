<?php

/**
 * @package Gather - Event Landing Page Wordpress Theme
 * @author Cththemes - http://themeforest.net/user/cththemes
 * @date: 10-8-2015
 *
 * @copyright  Copyright ( C ) 2014 - 2015 cththemes.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
global $cththemes_options;

if (!class_exists('ReduxFramework') && file_exists( get_template_directory() . '/ReduxCore/framework.php')) {
    require_once get_template_directory() . '/ReduxCore/framework.php';
}
if (!isset($cththemes_options) && file_exists(get_template_directory() . '/functions/admin-config.php')) {
    require_once get_template_directory() . '/functions/admin-config.php';
}
 
// Custom fields:
require_once get_template_directory() . '/framework/Custom-Metaboxes/metabox-functions.php';
require_once get_template_directory() . '/framework/wp_bootstrap_navwalker.php';
require_once get_template_directory() . '/inc/overridestyle.php';
require_once get_template_directory() . '/inc/cth_for_vc.php';
require_once get_template_directory() . '/vc_extend/vc_shortcodes.php';
require_once get_template_directory() . '/inc/ajax.php';
/*admin style */
add_action('admin_head', 'cththemes_gather_admin_style');
if(!function_exists('cththemes_gather_admin_style')){
    function cththemes_gather_admin_style() {
        echo '<link rel="stylesheet" href="'.get_template_directory_uri().'/vc_extend/admin_style.css" type="text/css" media="all" />';
    } 
}

/*  Add responsive container to embeds
/* ------------------------------------ */ 
function cththemes_gather_embed_html( $html ) {
    return '<div class="responsive-video">' . $html . '</div>';
}
 
//add_filter( 'embed_oembed_html', 'cththemes_gather_embed_html', 10, 3 );
add_filter( 'video_embed_html', 'cththemes_gather_embed_html' );

//one click import demo data.
include_once get_template_directory().'/includes/cththemes-importer.php';
function cththemes_gather_importer_import_scripts() {
    wp_register_script('cththemes-import', get_template_directory_uri() . '/includes/cththemes-import.js', false, '1.0.0', true);
    wp_enqueue_script('cththemes-import');
}
add_action('admin_enqueue_scripts', 'cththemes_gather_importer_import_scripts');

if (!isset($content_width)) {
    $content_width = 848;
}
add_theme_support('custom-header');
add_theme_support('custom-background');
add_filter('widget_text', 'do_shortcode');

if (!function_exists('_wp_render_title_tag')) {
    function cththemes_gather_render_title() {
?>
<title><?php wp_title(''); ?></title>
<?php
    }
    add_action('wp_head', 'cththemes_gather_render_title');
}

/**
 * Count number of widgets in a sidebar
 * Used to add classes to widget areas so widgets can be displayed one, two, three or four per row
 */
function cththemes_gather_slbd_count_widgets( $sidebar_id ) {
    // If loading from front page, consult $_wp_sidebars_widgets rather than options
    // to see if wp_convert_widget_settings() has made manipulations in memory.
    global $_wp_sidebars_widgets;
    if ( empty( $_wp_sidebars_widgets ) ) :
        $_wp_sidebars_widgets = get_option( 'sidebars_widgets', array() );
    endif;
    
    $sidebars_widgets_count = $_wp_sidebars_widgets;
    
    if ( isset( $sidebars_widgets_count[ $sidebar_id ] ) ) :
        $widget_count = count( $sidebars_widgets_count[ $sidebar_id ] );
        $widget_classes = 'widget-count-' . count( $sidebars_widgets_count[ $sidebar_id ] );
        if ( $widget_count % 6 == 0 && $widget_count >= 6 ) :
            // Six widgets er row if there are exactly four or more than six
            $widget_classes .= ' col-md-2';
        elseif ( $widget_count % 4 == 0 && $widget_count >= 4 ) :
            // Four widgets er row if there are exactly four or more than six
            $widget_classes .= ' col-md-3';
        elseif ( $widget_count % 3 == 0 && $widget_count >= 3 ) :
            // Three widgets per row if there's three or more widgets 
            $widget_classes .= ' col-md-4';
        elseif ( 2 == $widget_count ) :
            // Otherwise show two widgets per row
            $widget_classes .= ' col-md-6';
        elseif ( 1 == $widget_count ) :
            // Otherwise show two widgets per row
            $widget_classes .= ' col-md-12';
        endif; 

        return $widget_classes;
    endif;
}

/* Register Sidebars */
function cththemes_gather_register_sidebars() {
    
    register_sidebar(
        array(
            'name' => __('Main Sidebar', 'gather'), 
            'id' => 'sidebar-1', 
            'description' => __('Appears in the sidebar section of the site.', 'gather'), 
            'before_widget' => '<div id="%1$s" class="widget sidebar-widget %2$s">', 
            'after_widget' => '</div>', 
            'before_title' => '<h3 class="widget-title">', 
            'after_title' => '</h3>',
        )
    );
    
    register_sidebar(
        array(
            'name' => __('Page Sidebar', 'gather'), 
            'id' => 'sidebar-2', 
            'description' => __('Appears in the sidebar section of the page template.', 'gather'), 
            'before_widget' => '<div id="%1$s" class="widget cth %2$s">', 
            'after_widget' => '</div>', 
            'before_title' => '<h3 class="widget-title">', 
            'after_title' => '</h3>',
        )
    );
    register_sidebar(
        array(
            'name' => __('Navigation Right Widget', 'gather'), 
            'id' => 'top_social_widget', 
            'description' => __('Appears in right navigation menu', 'gather'), 
            'before_widget' => '<div id="%1$s" class="widget %2$s">', 
            'after_widget' => '</div>', 
            'before_title' => '<h3 class="widget-title">', 
            'after_title' => '</h3>',
        )
    );
    register_sidebar(
        array(
            'name' => __('Footer Widgets Widget', 'gather'), 
            'id' => 'footer_widgets_widget', 
            'description' => __('Appears above footer contact info widget', 'gather'), 
            'before_widget' => '<div id="%1$s" class="widget footer-widgets %2$s '. cththemes_gather_slbd_count_widgets( 'footer_widgets_widget' ) .'">', 
            'after_widget' => '</div>', 
            'before_title' => '<h3 class="widget-title">', 
            'after_title' => '</h3>',
        )
    );
    register_sidebar(
        array(
            'name' => __('Footer Copyright Widget', 'gather'), 
            'id' => 'footer_copyright_widget', 
            'description' => __('Appears above footer copyright text', 'gather'), 
            'before_widget' => '<div id="%1$s" class="widget %2$s">', 
            'after_widget' => '</div>', 
            'before_title' => '<h3 class="widget-title">', 
            'after_title' => '</h3>',
        )
    );
}

add_action('widgets_init', 'cththemes_gather_register_sidebars');

function cththemes_gather_theme_setup() {
    $lang = get_template_directory() . '/languages';
    load_theme_textdomain('gather', $lang);

    add_image_size('gatherblog-thumb', 360, 240, true);
    add_image_size('gathertesti_thumb',67,67,true );
    
    add_theme_support('post-thumbnails');
    
    // Adds RSS feed links to <head> for posts and comments.
    add_theme_support('automatic-feed-links');
    
    // Switches default core markup for search form, comment form, and comments
    
    add_theme_support('title-tag');

    add_theme_support( 'woocommerce' );
    
    // to output valid HTML5.
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list'));
    
    //Post formats
    add_theme_support('post-formats', array('audio', 'gallery', 'image', 'link', 'quote', 'status', 'video'));
    
    // This theme uses wp_nav_menu() in one location.
    register_nav_menu('primary', __('Main Navigation Menu', 'gather'));
    register_nav_menu('onepage', __('One Page Navigation Menu', 'gather'));
    
    // This theme uses its own gallery styles.
    
    add_editor_style('custom-editor-style.css');
    
    add_filter('use_default_gallery_style', '__return_false');
}

add_action('after_setup_theme', 'cththemes_gather_theme_setup');

function cththemes_gather_theme_scripts_styles() {
    global $cththemes_options;
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    wp_enqueue_script("gatherjsplugins-js", get_template_directory_uri() . "/js/plugins/plugins.js", array('jquery'), false, true);
    //Plugins Data 
    wp_localize_script( 'gatherjsplugins-js', 'plugins_datas', array(
        'url'           => admin_url( 'admin-ajax.php' ),
        'cd_day'           => _x('day','countdown js','gather'),
        'cd_days'           => _x('days','countdown js','gather'),
        'cd_hour'           => _x('hour','countdown js','gather'),
        'cd_hours'           => _x('hours','countdown js','gather'),
        'cd_minute'           => _x('minute','countdown js','gather'),
        'cd_minutes'           => _x('minutes','countdown js','gather'),
        'cd_second'           => _x('second','countdown js','gather'),
        'cd_seconds'           => _x('seconds','countdown js','gather'),
        'invalid_date'           => _x("Invalid date. Here's an example: 12 Tuesday 2016 17:30:00",'countdown js','gather'),
    ) );
    if(!$cththemes_options['disable_animation']){
        wp_enqueue_script("gatherwow-js", get_template_directory_uri() . "/js/plugins/wow.js", array(), false, true);
    }
    wp_enqueue_script("gather-validate-js", get_template_directory_uri() . "/js/plugins/validate.js", array(), false, true);
    wp_enqueue_script('gathergmap-api', 'https://maps.googleapis.com/maps/api/js?signed_in=true&libraries=places', array(), false, true);
    wp_enqueue_script("gatherinfobox-js", get_template_directory_uri() . "/js/plugins/infobox.js", array(), false, true);
    wp_enqueue_script("gathergoogle-map-js", get_template_directory_uri() . "/js/plugins/google-map.js", array(), false, true);
    wp_enqueue_script("gatherdirections-js", get_template_directory_uri() . "/js/plugins/directions.js", array(), false, true);
    if($cththemes_options['show_style_switcher']){
        wp_enqueue_script("gatherstyle-switcher-js", get_template_directory_uri() . "/js/plugins/style-switcher.js", array(), false, true);
    }
    //Main Scripts
    wp_enqueue_script("gathermain-js", get_stylesheet_directory_uri() . "/js/main.js", array(), false, true);
    wp_enqueue_style('gathercssplugins-css', get_template_directory_uri() . '/css/plugins/plugins.css');
    if(!$cththemes_options['disable_animation']){
        wp_enqueue_style('gatheranimate-css', get_template_directory_uri() . '/css/plugins/animate.css');
    }
    wp_enqueue_style('gathertheme-style', get_stylesheet_uri(), array(), '2015-8-4');
    if ($cththemes_options['override-preset']) {
        wp_enqueue_style('gathercustom-style', get_stylesheet_directory_uri() . '/css/custom.css');
        $inline_style = cththemes_gather_theme_overridestyle();
        if(!empty($inline_style)){
            wp_add_inline_style('gathercustom-style', $inline_style);
        }  

    }else{
        wp_enqueue_style('gathertheme-color', get_template_directory_uri() . '/skins/'.$cththemes_options['color-preset'].'.css',array(),false,'screen');
        wp_enqueue_style('gathercustom-style', get_stylesheet_directory_uri() . '/css/custom.css');
    }

    $inline_custom_style = trim($cththemes_options['custom-css']);

    if (!empty($inline_custom_style)) {
        wp_add_inline_style('gathercustom-style', $inline_custom_style);
    }
    
}

add_action('wp_enqueue_scripts', 'cththemes_gather_theme_scripts_styles');

//For IE
function cththemes_gather_script_ie() {
    global $cththemes_options;
if($cththemes_options['show_style_switcher']) {
    echo '<link rel="stylesheet" title="green" media="screen" href="'.get_template_directory_uri() . '/skins/default.css">
<link rel="alternate stylesheet" title="purple" media="screen" href="'.get_template_directory_uri() . '/skins/purple.css">
<link rel="alternate stylesheet" title="red" media="screen" href="'.get_template_directory_uri() . '/skins/red.css">
<link rel="alternate stylesheet" title="mint" media="screen" href="'.get_template_directory_uri() . '/skins/mint.css">
<link rel="alternate stylesheet" title="blue" media="screen" href="'.get_template_directory_uri() . '/skins/blue.css">
<link rel="alternate stylesheet" title="yellow" media="screen" href="'.get_template_directory_uri() . '/skins/yellow.css">
<link rel="alternate stylesheet" title="black" media="screen" href="'.get_template_directory_uri() . '/skins/black.css">
<link href="'.get_template_directory_uri() . '/css/demo.css" rel="stylesheet">';
}
    echo '<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="'.get_template_directory_uri() . '/js/ie/respond.min.js"></script>
<![endif]-->
<!-- Modernizr -->
<script src="'.get_template_directory_uri() . '/js/modernizr.min.js"></script>';
if($cththemes_options['show_loader']) {
    echo '<!-- Subtle Loading bar --><script src="'.get_template_directory_uri() . '/js/plugins/pace.js"></script>';
}
}
add_action('wp_head', 'cththemes_gather_script_ie');


if(!function_exists('cththemes_gather_breadcrumbs')){
    function cththemes_gather_breadcrumbs() {
          
        // Settings
        $separator          = __('/','gather');//'&gt;';
        $breadcrums_id      = 'breadcrumb';
        $breadcrums_class   = 'breadcrumb';
        $home_title         = __('Home','gather');
        $blog_title         = __('Our Blog','gather');
         
        // If you have any custom post types with custom taxonomies, put the taxonomy name below (e.g. product_cat)
        $custom_taxonomy    = 'product_cat,skill';
          
        // Get the query & post information
        global $post,$wp_query;
          
        // Do not display on the homepage
        if ( !is_front_page() ) {
          
            // Build the breadcrums
            echo '<ul id="' . esc_attr($breadcrums_id ) . '" class="' . esc_attr($breadcrums_class ) . '">';
              
            // Home page
            echo '<li class="item-home"><a class="bread-link bread-home" href="' . esc_url(get_home_url(null,'/') ) . '" title="' . esc_attr($home_title ) . '">' . esc_attr($home_title ) . '</a></li>';
            //echo '<li class="separator separator-home"> ' . esc_html($separator ) . ' </li>';

            if(is_home()){
                // Blog page
                echo '<li class="item-current item-blog"><strong class="bread-current item-blog">' . esc_attr($blog_title ) . '</strong></li>';
            }
              
            if ( is_archive() && !is_tax() ) {
                 
                echo '<li class="item-current item-archive"><strong class="bread-current bread-archive">' . get_the_archive_title() . '</strong></li>'; //post_type_archive_title($prefix, false)
                 
            } else if ( is_archive() && is_tax() ) {
                 
                // If post is a custom post type
                $post_type = get_post_type();
                 
                // If it is a custom post type display name and link
                if($post_type != 'post') {
                     
                    $post_type_object = get_post_type_object($post_type);
                    $post_type_archive = get_post_type_archive_link($post_type);
                 
                    echo '<li class="item-cat item-custom-post-type-' . $post_type . '"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . esc_url($post_type_archive ) . '" title="' . $post_type_object->labels->name . '">' . $post_type_object->labels->name . '</a></li>';
                    //echo '<li class="separator"> ' . $separator . ' </li>';
                 
                }
                 
                $custom_tax_name = get_queried_object()->name;
                echo '<li class="item-current item-archive"><strong class="bread-current bread-archive">' . $custom_tax_name . '</strong></li>';
                 
            } else if ( is_single() ) {
                 
                // If post is a custom post type
                $post_type = get_post_type();
                 
                // If it is a custom post type display name and link
                if($post_type != 'post') {
                     
                    $post_type_object = get_post_type_object($post_type);
                    $post_type_archive = get_post_type_archive_link($post_type);

                 
                    echo '<li class="item-cat item-custom-post-type-' . $post_type . '"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . esc_url($post_type_archive ) . '" title="' . $post_type_object->labels->name . '">' . $post_type_object->labels->name . '</a></li>';
                    //echo '<li class="separator"> ' . $separator . ' </li>';

                }
                    // Get post category info
                    $category = get_the_category();
                     
                    // Get last category post is in
                    $last_category = '';
                    if($category){
                        $last_cateogries = array_values($category);
                        $last_category = end($last_cateogries);
                     
                        // Get parent any categories and create array
                        $get_cat_parents = rtrim(get_category_parents($last_category->term_id, true, ','),',');
                        $cat_parents = explode(',',$get_cat_parents);
                         
                        // Loop through parent categories and store in variable $cat_display
                        $cat_display = '';
                        foreach($cat_parents as $parents) {
                            $cat_display .= '<li class="item-cat">'.$parents.'</li>';
                            //$cat_display .= '<li class="separator"> ' . $separator . ' </li>';
                        }
                    }
                    
                    if(!empty($last_category)) {
                        echo wp_kses_post($cat_display );
                        echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';
                         
                    // Else if post is in a custom taxonomy
                    }
                     
                    // If it's a custom post type within a custom taxonomy
                    if(empty($last_category) && !empty($custom_taxonomy)) {
                        $custom_taxonomy_arr = explode(",", $custom_taxonomy) ;
                        foreach ($custom_taxonomy_arr as $key => $custom_taxonomy_val) {
                            $taxonomy_terms = get_the_terms( $post->ID, $custom_taxonomy_val );
                            $cat_id         = $taxonomy_terms[0]->term_id;
                            $cat_nicename   = $taxonomy_terms[0]->slug;
                            $cat_link       = get_term_link($taxonomy_terms[0]->term_id, $custom_taxonomy_val);
                            $cat_name       = $taxonomy_terms[0]->name;

                            if(!empty($cat_id)) {
                         
                                echo '<li class="item-cat item-cat-' . $cat_id . ' item-cat-' . $cat_nicename . '"><a class="bread-cat bread-cat-' . $cat_id . ' bread-cat-' . $cat_nicename . '" href="' . esc_url($cat_link ) . '" title="' . $cat_name . '">' . $cat_name . '</a></li>';
                                //echo '<li class="separator"> ' . $separator . ' </li>';
                                echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';
                             
                            }

                         } 
                        
                      
                    }
                 
                
                 
            } else if ( is_category() ) {
                  
                // Category page
                echo '<li class="item-current item-cat-' . $category[0]->term_id . ' item-cat-' . $category[0]->category_nicename . '"><strong class="bread-current bread-cat-' . $category[0]->term_id . ' bread-cat-' . $category[0]->category_nicename . '">' . $category[0]->cat_name . '</strong></li>';
                  
            } else if ( is_page() ) {
                  
                // Standard page
                if( $post->post_parent ){

                    $parents = '';
                      
                    // If child page, get parents 
                    $anc = get_post_ancestors( $post->ID );
                      
                    // Get parents in the right order
                    $anc = array_reverse($anc);
                      
                    // Parent page loop
                    foreach ( $anc as $ancestor ) {
                        $parents .= '<li class="item-parent item-parent-' . $ancestor . '"><a class="bread-parent bread-parent-' . $ancestor . '" href="' . esc_url(get_permalink($ancestor) ) . '" title="' . get_the_title($ancestor) . '">' . get_the_title($ancestor) . '</a></li>';
                        //$parents .= '<li class="separator separator-' . $ancestor . '"> ' . $separator . ' </li>';
                    }
                      
                    // Display parent pages
                    echo wp_kses_post($parents );
                      
                    // Current page
                    echo '<li class="item-current item-' . $post->ID . '"><strong title="' . get_the_title() . '"> ' . get_the_title() . '</strong></li>';
                      
                } else {
                      
                    // Just display current page if not parents
                    echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '"> ' . get_the_title() . '</strong></li>';
                      
                }
                  
            } else if ( is_tag() ) {
                  
                // Tag page
                  
                // Get tag information
                $term_id = get_query_var('tag_id');
                $taxonomy = 'post_tag';
                $args ='include=' . $term_id;
                $terms = get_terms( $taxonomy, $args );
                  
                // Display the tag name
                echo '<li class="item-current item-tag-' . $terms[0]->term_id . ' item-tag-' . $terms[0]->slug . '"><strong class="bread-current bread-tag-' . $terms[0]->term_id . ' bread-tag-' . $terms[0]->slug . '">' . $terms[0]->name . '</strong></li>';
              
            } elseif ( is_day() ) {
                  
                // Day archive
                  
                // Year link
                echo '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link( get_the_time('Y') ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . __(' Archives','gather').'</a></li>';
                //echo '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';
                  
                // Month link
                echo '<li class="item-month item-month-' . get_the_time('m') . '"><a class="bread-month bread-month-' . get_the_time('m') . '" href="' . get_month_link( get_the_time('Y'), get_the_time('m') ) . '" title="' . get_the_time('M') . '">' . get_the_time('M') . __(' Archives','gather').'</a></li>';
                //echo '<li class="separator separator-' . get_the_time('m') . '"> ' . $separator . ' </li>';
                  
                // Day display
                echo '<li class="item-current item-' . get_the_time('j') . '"><strong class="bread-current bread-' . get_the_time('j') . '"> ' . get_the_time('jS') . ' ' . get_the_time('M') .  __(' Archives','gather').'</strong></li>';
                  
            } else if ( is_month() ) {
                  
                // Month Archive
                  
                // Year link
                echo '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link( get_the_time('Y') ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . __(' Archives','gather').'</a></li>';
                //echo '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';
                  
                // Month display
                echo '<li class="item-month item-month-' . get_the_time('m') . '"><strong class="bread-month bread-month-' . get_the_time('m') . '" title="' . get_the_time('M') . '">' . get_the_time('M') . __(' Archives','gather').'</strong></li>';
                  
            } else if ( is_year() ) {
                  
                // Display year archive
                echo '<li class="item-current item-current-' . get_the_time('Y') . '"><strong class="bread-current bread-current-' . get_the_time('Y') . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . __(' Archives','gather').'</strong></li>';
                  
            } else if ( is_author() ) {
                  
                // Auhor archive
                  
                // Get the author information
                global $author;
                $userdata = get_userdata( $author );
                  
                // Display author name
                echo '<li class="item-current item-current-' . $userdata->user_nicename . '"><strong class="bread-current bread-current-' . $userdata->user_nicename . '" title="' . $userdata->display_name . '">' .  __(' Author: ','gather') . $userdata->display_name . '</strong></li>';
              
            } else if ( get_query_var('paged') ) {
                  
                // Paginated archives
                echo '<li class="item-current item-current-' . get_query_var('paged') . '"><strong class="bread-current bread-current-' . get_query_var('paged') . '" title="'.__('Page','gather') . get_query_var('paged') . '">'.__('Page','gather') . ' ' . get_query_var('paged') . '</strong></li>';
                  
            } else if ( is_search() ) {
              
                // Search results page
                echo '<li class="item-current item-current-' . get_search_query() . '"><strong class="bread-current bread-current-' . get_search_query() . '" title="'.__('Search results for: ','gather') . get_search_query() . '">'.__('Search results for: ','gather') . get_search_query() . '</strong></li>';
              
            } elseif ( is_404() ) {
                  
                // 404 page
                echo '<li>' . __('Error 404','gather') . '</li>';
            }
          
            echo '</ul>';
              
        }
          
    }
}



// //Custom Excerpt Function
function cththemes_gather_excerpt($default = false) {
    global $theme_option;
    if ($default) {
        $limit = (int)$default;
    } 
    else {
        if (isset($theme_option['blog_excerpt'])) {
            $limit = $theme_option['blog_excerpt'];
        } 
        else {
            $limit = 20;
        }
    }
    
    $excerpt = explode(' ', get_the_excerpt(), $limit);
    if (count($excerpt) >= $limit) {
        array_pop($excerpt);
        $excerpt = implode(" ", $excerpt) . '...';
    } 
    else {
        $excerpt = implode(" ", $excerpt);
    }
    $excerpt = preg_replace('`[[^]]*]`', '', $excerpt);
    return $excerpt;
}

function cththemes_gather_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'cththemes_gather_excerpt_more');

//pagination
function cththemes_gather_pagination($prev = 'Prev', $next = 'Next', $pages = '') {
    global $wp_query, $wp_rewrite;
    $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
    if ($pages == '') {
        global $wp_query;
        $pages = $wp_query->max_num_pages;
        if (!$pages) {
            $pages = 1;
        }
    }
    $pagination = array('base' => str_replace(999999999, '%#%', get_pagenum_link(999999999)), 'format' => '', 'current' => max(1, get_query_var('paged')), 'total' => $pages, 'prev_text' => __($prev, 'gather'), 'next_text' => __($next, 'gather'), 'type' => 'list', 'end_size' => 3, 'mid_size' => 3);
    $return = paginate_links($pagination);
    if (!$return) return;
    $return = str_replace("<ul class='page-numbers'>", '<ul class="pagination">', $return);
    echo '<div class="text-center center-block">' . $return . '</div>';
}

function cththemes_gather_custom_pagination($pages = '', $range = 2, $current_query = '') {
    
    $showitems = ($range * 2) + 1;
    
    if ($current_query == '') {
        global $paged;
        if (empty($paged)) $paged = 1;
    } 
    else {
        $paged = $current_query->query_vars['paged'];
    }
    
    if ($pages == '') {
        if ($current_query == '') {
            global $wp_query;
            $pages = $wp_query->max_num_pages;
            if (!$pages) {
                $pages = 1;
            }
        } 
        else {
            $pages = $current_query->max_num_pages;
        }
    }
    
    if (1 != $pages) {
        echo "<div class='cththemes_gather_pagination clearfix'>";
        
        if ($paged > 1) echo "<a class='pagination-prev' href='" . get_pagenum_link($paged - 1) . "'><span class='page-prev'></span>" . __('Previous', 'gather') . "</a>";
        
        for ($i = 1; $i <= $pages; $i++) {
            if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
                echo ($paged == $i) ? "<span class='current'>" . $i . "</span>" : "<a href='" . get_pagenum_link($i) . "' class='inactive' >" . $i . "</a>";
            }
        }
        
        if ($paged < $pages) echo "<a class='pagination-next' href='" . get_pagenum_link($paged + 1) . "'>" . __('Next', 'gather') . "<span class='page-next'></span></a>";
        
        echo "</div>\n";
    }
}

function cththemes_gather_custom_img($image_width = '', $image_height = '', $crop = false, $thumb_size = '') {
    
    global $post;
    
    if (empty($thumb_size)) {
        $thumb_size = 'full';
    }
    
    $params = array();
    
    if (!empty($image_width)) {
        $params['width'] = $image_width;
    }
    
    if (!empty($image_height)) {
        $params['height'] = $image_height;
    }
    
    if ($crop) {
        $params['crop'] = true;
    }
    
    $imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID, ''), $thumb_size);
    $custom_img_src = bfi_thumb($imgsrc[0], $params);
    
    return $custom_img_src;
}

//Get thumbnail url
function cththemes_gather_thumbnail_url($size) {
    global $post;
    
    //$url = wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()),$size );
    if ($size == '') {
        $url = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
        return $url;
    } 
    else {
        $url = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), $size);
        return $url[0];
    }
}

function cththemes_gather_post_nav($extraclass = '') {
    global $post;
    
    // Don't print empty markup if there's nowhere to navigate.
    $previous = (is_attachment()) ? get_post($post->post_parent) : get_adjacent_post(false, '', true);
    $next = get_adjacent_post(false, '', false);
    if (!$next && !$previous) return;
?>
    <ul class="pager <?php
    echo esc_attr($extraclass); ?> clearfix">
      <li class="previous">
        <?php
    previous_post_link('%link', _x('<i class="fa fa-long-arrow-left"></i>', 'Previous post link', 'gather')); ?>
      </li>
      <!-- <li><span>/</span></li> -->
      <li class="next">
        <?php
    next_post_link('%link', _x('<i class="fa fa-long-arrow-right"></i>', 'Next post link', 'gather')); ?>
      </li>
    </ul>   
<?php
}
function cththemes_gather_search_form($form) {
    $form = '
      <form role="search" method="get" id="searchform" action="' . esc_url(home_url('/') ) . '" >
      <div class="search">
      <input type="text" size="16" class="search-field form-control" placeholder="' . __('Search ...', 'gather') . '" value="' . get_search_query() . '" name="s" id="s" />
        <input type="hidden" name="post_type" value="post">
      </div>
      </form>
   ';
    return $form;
}

//Custom comment List:
function cththemes_gather_theme_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    extract($args, EXTR_SKIP);
    
    //echo '<pre>';var_dump($comment);die;
    
    if ('div' == $args['style']) {
        $tag = 'div';
        $add_below = 'comment';
    } 
    else {
        $tag = 'li';
        $add_below = 'div-comment';
    }
?>
    <<?php
    echo esc_attr($tag); ?> <?php
    comment_class() ?> id="li-comment-<?php
    comment_ID() ?>">
    <?php
    if ('div' != $args['style']): ?>
    <div id="div-comment-<?php
        comment_ID() ?>" <?php
        //comment_class('comment-body'); ?> class="comment-wrap clearfix">
    <?php
    endif; ?>
        <div class="comment-meta">
            <div class="comment-author vcard">
                <span class="comment-avatar clearfix">
                    <?php if ($args['avatar_size'] != 0) echo get_avatar($comment, $args['avatar_size']); ?>
                </span>
            </div>
        </div>
        <div class="comment-content clearfix">

            <div class="comment-author">
            <?php echo get_comment_author_link($comment->comment_ID); ?>
            <span><a href="<?php echo esc_url(get_comment_link($comment->comment_ID) ); ?>">
            <?php
            /* translators: 1: date, 2: time */
            printf(__('%1$s at %2$s', 'gather'), get_comment_date(), get_comment_time()); ?></a>
            <?php edit_comment_link(__('/ (Edit)', 'gather'), '  ', ''); ?>
            </span>
            </div>
            <?php comment_text(); ?>

            <?php
            if ($comment->comment_approved == '0'): ?>
                    <em class="comment-awaiting-moderation aligncenter"><?php
                _e('Your comment is awaiting moderation.', 'gather'); ?></em>
                    <br />
                <?php
            endif; ?>
            <?php comment_reply_link(array_merge($args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))); 
            ?> 

        </div>
    
    <?php
    if ('div' != $args['style']): ?>
    </div> 
    <?php
    endif; ?>
<?php
}

function cththemes_gather_custom_tag_cloud_widget($args) {
    // $args['number'] = 0; //adding a 0 will display all tags
    // $args['largest'] = 18; //largest tag
    // $args['smallest'] = 10; //smallest tag
    // $args['unit'] = 'px'; //tag font unit
    $args['format'] = 'flat'; //ul with a class of wp-tag-cloud
    return $args;
}
add_filter( 'widget_tag_cloud_args', 'cththemes_gather_custom_tag_cloud_widget' );
// add_filter( 'woocommerce_product_tag_cloud_widget_args','cththemes_gather_custom_tag_cloud_widget');



/**
 * This file represents an example of the code that themes would use to register
 * the required plugins.
 *
 * It is expected that theme authors would copy and paste this code into their
 * functions.php file, and amend to suit.
 *
 * @package    TGM-Plugin-Activation
 * @subpackage Example
 * @version    2.4.0
 * @author     Thomas Griffin <thomasgriffinmedia.com>
 * @author     Gary Jones <gamajo.com>
 * @copyright  Copyright (c) 2014, Thomas Griffin
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://github.com/thomasgriffin/TGM-Plugin-Activation
 */

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once get_template_directory() . '/framework/class-tgm-plugin-activation.php';
add_action('tgmpa_register', 'cththemes_gather_register_required_plugins');

/**
 * Register the required plugins for this theme.
 *
 * In this example, we register two plugins - one included with the TGMPA library
 * and one from the .org repo.
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function cththemes_gather_register_required_plugins() {
    
    /**
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = array(
    
    // This is an example of how to include a plugin from a private repo in your theme.
    
    array('name' => 'WPBakery Visual Composer',
    
    // The plugin name.
    'slug' => 'js_composer',
    
    // The plugin slug (typically the folder name).
    'source' => 'http://assets.cththemes.net/js_composer/js_composer.4.7.zip',
    
    // The plugin source.
    'required' => true,
    
    // If false, the plugin is only 'recommended' instead of required.
    ), array('name' => 'Rock Lobster Contact Form 7',
    
    // The plugin name.
    'slug' => 'contact-form-7',
    
    // The plugin slug (typically the folder name).
    'source' => 'http://assets.cththemes.net/contact-form-7.zip',
    
    // The plugin source.
    'required' => true,
    
    // If false, the plugin is only 'recommended' instead of required.
    ), 
    array('name' => 'Gather Theme Plugins',
    
    // The plugin name.
    'slug' => 'cth_gather_plugins',
    
    // The plugin slug (typically the folder name).
    'source' => get_template_directory_uri() . '/framework/plugins/cth_gather_plugins.zip',
    
    // The plugin source.
    'required' => true,
    
    // If false, the plugin is only 'recommended' instead of required.
    ),

    );
    
    /**
     * Array of configuration settings. Amend each line as needed.
     * If you want the default strings to be available under your own theme domain,
     * leave the strings uncommented.
     * Some of the strings are added into a sprintf, so see the comments at the
     * end of each line for what each argument will be.
     */
    $config = array('default_path' => '',
    
    // Default absolute path to pre-packaged plugins.
    'menu' => 'tgmpa-install-plugins',
    
    // Menu slug.
    'has_notices' => true,
    
    // Show admin notices or not.
    'dismissable' => true,
    
    // If false, a user cannot dismiss the nag message.
    'dismiss_msg' => '',
    
    // If 'dismissable' is false, this message will be output at top of nag.
    'is_automatic' => false,
    
    // Automatically activate plugins after installation or not.
    'message' => '',
    
    // Message to output right before the plugins table.
    'strings' => array('page_title' => __('Install Required Plugins', 'tgmpa'), 'menu_title' => __('Install Plugins', 'tgmpa'), 'installing' => __('Installing Plugin: %s', 'tgmpa'),
    
    // %s = plugin name.
    'oops' => __('Something went wrong with the plugin API.', 'tgmpa'), 'notice_can_install_required' => _n_noop('This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'tgmpa'),
    
    // %1$s = plugin name(s).
    'notice_can_install_recommended' => _n_noop('This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'tgmpa'),
    
    // %1$s = plugin name(s).
    'notice_cannot_install' => _n_noop('Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa'),
    
    // %1$s = plugin name(s).
    'notice_can_activate_required' => _n_noop('The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'tgmpa'),
    
    // %1$s = plugin name(s).
    'notice_can_activate_recommended' => _n_noop('The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa'),
    
    // %1$s = plugin name(s).
    'notice_cannot_activate' => _n_noop('Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa'),
    
    // %1$s = plugin name(s).
    'notice_ask_to_update' => _n_noop('The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'tgmpa'),
    
    // %1$s = plugin name(s).
    'notice_cannot_update' => _n_noop('Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa'),
    
    // %1$s = plugin name(s).
    'install_link' => _n_noop('Begin installing plugin', 'Begin installing plugins', 'tgmpa'), 'activate_link' => _n_noop('Begin activating plugin', 'Begin activating plugins', 'tgmpa'), 'return' => __('Return to Required Plugins Installer', 'tgmpa'), 'plugin_activated' => __('Plugin activated successfully.', 'tgmpa'), 'complete' => __('All plugins installed and activated successfully. %s', 'tgmpa'),
    
    // %s = dashboard link.
    'nag_type' => 'updated'
    
    // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
    ));
    tgmpa($plugins, $config);
}