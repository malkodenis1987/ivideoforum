<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>"/>
    <meta name="robots" content="follow, all"/>
    <meta name="language" content="en, sv"/>
    <link rel="shortcut icon" href="/favicon.png" type="image/x-icon"/>

    <title>
        <?php bloginfo('name'); ?> &mdash; <?php bloginfo('description'); ?>
        <?php wp_title(); ?>
    </title>

    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>"/>
    <link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>"/>
    <link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>"/>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
    <script type="text/javascript" src="http://userapi.com/js/api/openapi.js?49"></script>
    <script type="text/javascript">
        VK.init({apiId : 2019521, onlyWidgets : true});
    </script>
    <script>
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
    <?php wp_get_archives('type=monthly&format=link'); ?>
    <?php wp_head(); ?>
</head>

<body id="home" class="log">

<div id="fb-root"></div>

<?php if ($post->post_type !== 'photo') { ?>
    <div id="headerbg">
        <div class="cab">
            <div class="container">
                <?php if (is_user_logged_in()) { ?>
                    <p>
                        <?php if (isset($_GET['success']) && $_GET['success'] === '1') { ?>
                            <span style="color: green;"><strong>Вы удачно вошли!</strong> </span>
                        <?php } ?>
                        <a href="<?php echo wp_logout_url(get_permalink(get_page_id('login'))); ?>"">выход</a> | <a href="<?php echo get_permalink(get_page_id('settings')); ?>">мои настройки</a>
                    </p>
                <?php } else { ?>
                    <p><a href="<?php echo get_permalink(get_page_id('login')); ?>">вход</a> | <a href="<?php echo get_permalink(get_page_id('registration')); ?>">регистрация</a></p>
                <?php } ?>
            </div>
        </div>
        <div id="wrapper">
            <div id="header">
                <img src='<?php bloginfo('template_directory'); ?>/images/ivflogo.gif' title='' alt='iVideoForum &mdash; международный форум видеомейкеров' onclick="location.href='<?php bloginfo('url'); ?>'" style="margin-left:20px;cursor: pointer;"/>
            </div>
            <div class="navbg">
                <div class="navwidth">
                    <ul class="navigation">
                        <?php if (is_home()) { $pg_li .= "current_page_item"; } ?>
                        <?php if (is_category("articles")) { $pg_li1 .= "current_page_item"; } ?>
                        <li class="<?php echo $pg_li; ?>"><a href="<?php bloginfo('siteurl'); ?>" title=""><span>Главная</span></a></li>
                        <li class="<?php echo $pg_li1; ?>"><a href="<?php bloginfo('siteurl'); ?>/category/articles/" title=""><span>Статьи</span></a></li>
                        <li><a rel="nofollow" href="http://forum.ivideoforum.org" title="Интернет-форум сообщества" target="_blank"><span>Общение</span></a></li>
                        <?php wp_list_page('depth=1&title_li=&exclude=143,5814,5879,6222&sort_column=menu_order'); ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div id="wrap">
        <!-- <div style="margin-top: 15px;"><a href="http://www.ivideoforum.org/meetings/forum-2011-zima/"><img src="<?php bloginfo('template_directory'); ?>/images/forum6.jpg" alt="7 Международный форум видеомейкеров" width="1000" height="150" border="0" /></a></div> -->
        <br/>
        <?php $query = new WP_Query(array('post_type' => 'slide')); ?>
        <div class="cycle-slideshow">
            <div class="cycle-prev"></div>
            <div class="cycle-next"></div>
            <?php
                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        echo wp_get_attachment_image(get_post_thumbnail_id( $post->ID ), 'full');
                    }
            ?>
        </div>
    <?php } ?>
<?php } ?>
