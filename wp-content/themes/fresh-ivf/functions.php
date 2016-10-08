<?php
global $formErrors;
$formErrors = array(
    0  => 'Неизвестная ошибка',
    1  => 'Введите правильное Имя',
    2  => 'Введите правильную Фамилию',
    3  => 'Введите правильный Email',
    4  => 'Введите пароль',
    5  => 'Пользователь с таким email уже существует',
    6  => 'Пароль не совпадает',
    7  => 'Ошибка входа',
    8  => 'Пользователь с этим email не зарегистрирован',
    9  => 'Введите имя или email',
    10 => 'Изменение пароля для этого пользователя не возможно',
    11 => 'Письмо не может быть отправлено на указаный адрес. Возможно отключена отправка почты на сервере',
    12 => 'Введите новый пароль',
    13 => 'Код активации не верный',
	14 => 'Введите название',
	15 => 'Введите ФИО',
	16 => 'Введите телефон',
	17 => 'Введите описание',
	18 => 'Размер файла слишком большой',
	19 => 'Неправильный формат файла',
	20 => 'Файл не может быть загружен',
	21 => 'Введите название города',
	22 => 'Ваш аккаунт не подтвержден'
);

function ivideo_scripts_styles() {
    global $wp_styles;
    // Adds JavaScript to pages with the comment form to support sites with
    // threaded comments (when in use).
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
        wp_enqueue_script( 'comment-reply' );

    wp_enqueue_script( 'fancybox', get_template_directory_uri() . '/js/jquery.fancybox.js', array( 'jquery' ), '2013-07-18', true );
    wp_enqueue_script( 'forms', get_template_directory_uri() . '/js/forms.js', array( 'jquery' ), '2013-07-18', true );
    wp_enqueue_script( 'gallery', get_template_directory_uri() . '/js/gallery.js', array( 'jquery' ), '2013-07-18', true );
    wp_enqueue_script( 'cycle', get_template_directory_uri() . '/js/jquery.cycle2.min.js', array( 'jquery' ), '2013-07-18', true );
    wp_enqueue_script( 'swfobject', get_template_directory_uri() . '/js/swfobject.js', array( 'jquery' ), '2013-07-18', false );
    wp_enqueue_script( 'main', get_template_directory_uri() . '/js/main.js', array( 'jquery' ), '2013-07-18', true );

    // Loads our main stylesheet.
    wp_enqueue_style( 'fancybox', get_template_directory_uri() . '/css/jquery.fancybox.css', array(), '2013-07-18' );
    wp_enqueue_style( 'ivideo-style', get_stylesheet_uri(), array(), '2013-07-18' );
}
add_action( 'wp_enqueue_scripts', 'ivideo_scripts_styles' );

add_image_size('photo', 500, 9999);
add_image_size('contest', 760, 9999);
add_image_size('small-thumbnail', 24, 24);

add_theme_support( 'post-formats', array( 'video' ) );

add_theme_support( 'post-thumbnails' );

if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'before_widget' => '', // Removes <li>
		'after_widget' => '', // Removes </li>
		'before_title' => '<h2>', // Replaces <h2>
		'after_title' => '</h2>', // Replaces </h2>
	));

if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'before_widget' => '', // Removes <li>
		'after_widget' => '', // Removes </li>
		'before_title' => '<h2>', // Replaces <h2>
		'after_title' => '</h2>', // Replaces </h2>
	));

if ( function_exists('register_sidebar') )
    register_sidebar(array(
        'before_widget' => '', // Removes <li>
        'after_widget' => '', // Removes </li>
        'before_title' => '<h2>', // Replaces <h2>
        'after_title' => '</h2>', // Replaces </h2>
    ));


function show_page_parent()
{
	global $wp_query;

	$out='';
	if($wp_query->post->post_parent != 0)
	{
		$out.= $wp_query->post->post_title;
	}
	//echo($out);
	return $out;
}

function show_page_parent_links()
{
	global $wp_query, $wpdb;

	$out = '';
	
	$page_id = $wp_query->post->post_parent;
	if($page_id != 0)
	{
		$page_title = $wpdb->get_var("SELECT post_title FROM ".$wpdb->prefix."posts WHERE ID=".$page_id);
		$out.= '<a href="'.get_permalink($page_id).'">'.$page_title.'</a></li>';
	}
	//echo($out);
	return $out;
}
function get_page_id($page_name)
{
	global $wpdb;
	$page_name = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$page_name."'");
	return $page_name;
}

function get_ads_cats()
{
	$categories = get_terms('ads_cat', array(
		'orderby'    => 'count',
		'hide_empty' => 0
	));
	return $categories;
}
function get_cities()
{
	global $wpdb;
	$query = "
		SELECT meta_value 
		FROM ivfs_postmeta 
		WHERE meta_value LIKE '%ads_city%'
	";
	$result = unserialize($wpdb->get_var($query));
	return $result['choices'];
}
function get_field_label($field_name, $post_id = false, $id)
{
	global $post, $wpdb; 
	if(!$post_id) 
	{ 
		$post_id = $post->ID; 
	}
	$query = 'SELECT meta_value FROM ivfs_postmeta where meta_key = "'.get_post_meta($post_id,"_".$field_name,true).'"';
	$field = unserialize($wpdb->get_var($query));
	return $field['choices'][$id];
}
function codex_custom_init() {
  $labels = array(
    'name' => 'Slides',
    'singular_name' => 'Slide',
    'add_new' => 'Add New',
    'add_new_item' => 'Add New Slide',
    'edit_item' => 'Edit Slide',
    'new_item' => 'New Slide',
    'all_items' => 'All Slides',
    'view_item' => 'View Slide',
    'search_items' => 'Search Slides',
    'not_found' =>  'No Slides found',
    'not_found_in_trash' => 'No Slides found in Trash', 
    'parent_item_colon' => '',
    'menu_name' => 'Slides'
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'slide' ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array( 'title', 'editor', 'thumbnail' )
  ); 

  register_post_type( 'slide', $args );
}
add_action( 'init', 'codex_custom_init' );

function disqus_embed($disqus_shortname) {
    global $post;
    wp_enqueue_script('disqus_embed','http://'.$disqus_shortname.'.disqus.com/embed.js');
    echo '<div id="disqus_thread"></div>
    <script type="text/javascript">
        var disqus_shortname = "'.$disqus_shortname.'";
        var disqus_title = "'.$post->post_title.'";
        var disqus_url = "'.get_permalink($post->ID).'";
        var disqus_identifier = "'.$disqus_shortname.'-'.$post->ID.'";
    </script>';
}

if ( ! function_exists( 'ivideo_post_nav' ) ) :
    function ivideo_post_nav() {
        global $post;
        $previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
        $next     = get_adjacent_post( false, '', false );
        if ( ! $next && ! $previous )
            return;
        ?>
    <div class="navi post-navigation post-nav-box">
        <div class="previous-post"><?php previous_post_link( '%link', _x( '', 'Previous post link', 'okhorus' ) ); ?></div>
        <div class="next-post"><?php next_post_link( '%link', _x( '', 'Next post link', 'okhorus' ) ); ?></div>
    </div>
    <?php
    }
endif;

if ( function_exists('register_sidebar') )
    register_sidebar(array(
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h2>',
        'after_title' => '</h2>',
    ));

function wp_list_page($args = '') {
    parse_str($args, $r);
    if ( !isset($r['depth']) )
        $r['depth'] = 0;
    if ( !isset($r['show_date']) )
        $r['show_date'] = '';
    if ( !isset($r['child_of']) )
        $r['child_of'] = 0;
    if ( !isset($r['title_li']) )
        $r['title_li'] = __('Pages');
    if ( !isset($r['echo']) )
        $r['echo'] = 1;

    $output = '';

    // Query pages.
    $pages = & get_pages($args);
    if ( $pages ) {

        if ( $r['title_li'] )
            $output .= '<li class="pagenav">' . $r['title_li'] . '<ul>';

        // Now loop over all pages that were selected
        $page_tree = Array();
        foreach ( $pages as $page ) {
            // set the title for the current page
            $page_tree[$page->ID]['title'] = $page->post_title;
            $page_tree[$page->ID]['name'] = $page->post_name;

            // set the selected date for the current page
            // depending on the query arguments this is either
            // the createtion date or the modification date
            // as a unix timestamp. It will also always be in the
            // ts field.
            if ( !empty($r['show_date']) ) {
                if ( 'modified' == $r['show_date'] )
                    $page_tree[$page->ID]['ts'] = $page->post_modified;
                else
                    $page_tree[$page->ID]['ts'] = $page->post_date;
            }

            // The tricky bit!!
            // Using the parent ID of the current page as the
            // array index we set the curent page as a child of that page.
            // We can now start looping over the $page_tree array
            // with any ID which will output the page links from that ID downwards.
            if ( $page->post_parent != $page->ID)
                $page_tree[$page->post_parent]['children'][] = $page->ID;
        }
        // Output of the pages starting with child_of as the root ID.
        // child_of defaults to 0 if not supplied in the query.
        $output .= _page_level_out1($r['child_of'],$page_tree, $r, 0, false);
        if ( $r['title_li'] )
            $output .= '</ul></li>';
    }

    $output = apply_filters('wp_list_page', $output);

    if ( $r['echo'] )
        echo $output;
    else
        return $output;
}


function _page_level_out1($parent, $page_tree, $args, $depth = 0, $echo = true) {
    global $wp_query;
    $queried_obj = $wp_query->get_queried_object();
    $output = '';

    if ( $depth )
        $indent = str_repeat("\t", $depth);
    //$indent = join('', array_fill(0,$depth,"\t"));

    if ( !is_array($page_tree[$parent]['children']) )
        return false;

    foreach ( $page_tree[$parent]['children'] as $page_id ) {
        $cur_page = $page_tree[$page_id];
        $title = $cur_page['title'];

        $css_class = 'page_item';
        if ( $page_id == $queried_obj->ID )
            $css_class .= ' current_page_item';

        $output .= $indent . '<li class="' . $css_class . '"><a href="' . get_page_link($page_id) . '" title="' . wp_specialchars($title) . '"><span>' . $title . '</span></a>';

        if ( isset($cur_page['ts']) ) {
            $format = get_settings('date_format');
            if ( isset($args['date_format']) )
                $format = $args['date_format'];
            $output .= " " . mysql2date($format, $cur_page['ts']);
        }

        if ( isset($cur_page['children']) && is_array($cur_page['children']) ) {
            $new_depth = $depth + 1;

            if ( !$args['depth'] || $depth < ($args['depth']-1) ) {
                $output .= "$indent<ul>\n";
                $output .= _page_level_out1($page_id, $page_tree, $args, $new_depth, false);
                $output .= "$indent</ul>\n";
            }
        }
        $output .= "$indent</li>\n";
    }
    if ( $echo )
        echo $output;
    else
        return $output;
}

?>