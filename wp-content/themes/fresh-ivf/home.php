<?php get_header(); ?>

<div id="content">
<?php //include("wp-content/adsense.txt");?>
<?php /*if (is_home()) { query_posts('&category_name=news'); }*/ ?>
<?php if (is_home()) { ?>
    <?php query_posts($query_string . '&category_name=news'); ?>
    <?php } ?>

<?php $pnum = 0; ?>
<?php $exclude = array(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post();
    $pnum++; ?>
    <?php
    $anons_img_url = get_post_meta($post->ID, "anons_img_url", true);
    if (empty($anons_img_url)) {
        $img = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID));
        $anons_img_url = $img[0];
    }
    $anons_img_url = empty($anons_img_url) ? get_bloginfo('stylesheet_directory') . "/images/imgholder.gif" : $anons_img_url;
    $anons_img_title = get_post_meta($post->ID, "anons_img_title", true);
    $anons_img_title = empty($anons_img_title) ? "Место для картинки" : $anons_img_title;
    ?>

    <?php if ($pnum == 1) { ?>
        <?php array_push($exclude, $post->ID); ?>
    <div class="fpost" onclick="location.href='<?php the_permalink() ?>'">
        <h3 class="entrytitle fpost-title" id="post-<?php the_ID(); ?>">
            <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a>
        </h3>

        <div class="entrymeta">
            <?php the_time('j M Y г. ');
            //comments_popup_link('', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" /> 1 комментарий', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" />  Комментарии: %');
            edit_post_link(__('Edit This'), '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/edit.gif"  title="" alt="*" /> ');
            ?>
        </div>
        <div class="fpost-body">
            <div class="fpost-body-img">
                <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>"/>
            </div>
            <div class="fpost-body-text">
                <?php
                global $more;
                $more = 0;
                the_content("Узнай больше &raquo;");
                ?>
            </div>
        </div>
    </div>

    <!-- Баннер на главной -->
	<!--
    <div style="margin-top: 15px;">
        <a href="http://videostyle.com.ua"><img src="http://www.ivideoforum.org/images/videostyle.gif" width="500"
                                                height="105" border="3"/></a>
    </div>
	-->
    <!-- // Баннер на главной -->

        <?php } elseif ($pnum == 2) { ?>
        <?php array_push($exclude, $post->ID); ?>
            <div class="fpost2-wrap clearfix">
                <div class="fpost2">
                    <h3 class="entrytitle fpost2-title" id="post-<?php the_ID(); ?>">
                        <a href="<?php the_permalink() ?>" rel="bookmark"
                           title="Опубликовано <?php the_time('j M Y г. '); ?>"><?php the_title(); ?></a>
                    </h3>
                    <!--<div class="entrymeta">
                    <?php
                        the_time('j M Y г. ');
                        comments_popup_link('', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" /> 1 комментарий', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" />  Комментарии: %');
                        edit_post_link(__('Edit This'), '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/edit.gif"  title="" alt="*" /> ');
                        ?>
                    </div>-->
                    <div class="entrybody fpost2-body">
                        <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>"/>
                        <?php the_excerpt(); ?>
                        <!--<?php the_content('Читать полностью &raquo;');?>-->
                    </div>
                </div>
        <?php } elseif ($pnum == 3) { ?>
        <?php array_push($exclude, $post->ID); ?>
        <div class="fpost3">
            <h3 class="entrytitle fpost3-title" id="post-<?php the_ID(); ?>">
                <a href="<?php the_permalink() ?>" rel="bookmark"
                   title="Опубликовано <?php the_time('j M Y г. '); ?>"><?php the_title(); ?></a>
            </h3>
            <!--<div class="entrymeta">
                    <?php
                the_time('j M Y г. ');
                comments_popup_link('', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" /> 1 комментарий', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" />  Комментарии: %');
                edit_post_link(__('Edit This'), '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/edit.gif"  title="" alt="*" /> ');
                ?>
                    </div>-->
            <div class="entrybody fpost3-body">
                <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>"/>
                <?php the_excerpt(); ?>
                <!--<?php the_content('Читать полностью &raquo;');?>-->
            </div>
        </div>
            </div>
    <!-- Лучшие Фото -->
    <div id="gallery" class="clearfix">
        <div class="fpost">
            <h3>Лучшие свадебные фото</h3>
            <?php
            $args = array(
                'post_type' => 'photo',
                'posts_per_page' => 4,
                'meta_key' => 'ratings_average',
                'orderby' => 'meta_value_num',
                'order' => 'DESC'
            );
            $photos = query_posts($args);
            ?>
            <?php if ($photos) { ?>
            <ul>
                <?php foreach ($photos as $photo) { ?>
                <?php $thumbImage = wp_get_attachment_image_src(get_post_thumbnail_id($photo->ID), 'thumbnail'); ?>
                <?php $fullImage = wp_get_attachment_image_src(get_post_thumbnail_id($photo->ID), 'full'); ?>
                <?php if ($thumbImage) { ?>
                    <li>
                        <a href="<?php echo get_permalink(get_page_id('contest')); ?>"><img
                                src="<?php echo $thumbImage[0]; ?>" alt=""/></a>
                    </li>
                    <?php } ?>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <p>Еще никто не загрузил фотографии</p>
            <?php } ?>
            <?php wp_reset_query(); ?>
        </div>
    </div>
    
    <!-- Последние объявления -->
    <!--
        <div id="ads-list" class="clearfix">
            <div class="fpost">
                <h3>Новые объявления</h3>
                <?php
        $args = array(
            'post_type' => 'ads',
            'posts_per_page' => 2
        );
        $ads = query_posts($args);
        ?>
                <?php if ($ads) { ?>
                <div id="ads" class="clearfix">
                    <ul>
                        <?php foreach ($ads as $add) { ?>
                        <?php $thumbImage = wp_get_attachment_image_src(get_post_thumbnail_id($add->ID), 'thumbnail'); ?>
                        <?php $fullImage = wp_get_attachment_image_src(get_post_thumbnail_id($add->ID), 'full'); ?>
                        <?php if ($thumbImage) { ?>
                            <li class="clearfix">
                                <div class="thumb">
                                    <a href="<?php echo get_permalink($add->ID); ?>"><img src="<?php echo $thumbImage[0]; ?>" alt="" /></a>
                                    <a class="zoom" href="<?php echo $fullImage[0]; ?>">&nbsp;</a>
                                </div>
                                <div class="text">
                                    <h4><?php echo $add->post_title; ?></h4>
                                    <div class="details">
                                        <p><strong>Описание:</strong> <?php echo apply_filters('the_content', $add->post_content); ?></p>
                                    </div>
                                    <p class="more"><a href="<?php echo get_permalink($add->ID); ?>">подробнее</a></p>
                                </div>
                            </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
                <?php } else { ?>
                    <p>Объявлений по Вашему запросу не найдено.</p>
                <?php } ?>
                <?php wp_reset_query(); ?>
                <p><a href="<?php echo get_permalink(get_page_id('obyavleniya')); ?>">Доска объявлений</a></p>
            </div>
        </div>
        -->
    <div class="adwrap">
        <!-- banner -->
        <!-- <center>
        <br />
        <a title="Регистрация учасников!" href="http://www.ivideoforum.org/meetings/forum-2011-zima/registratsiya-uchastnikov/">
        <img src="http://www.ivideoforum.org/images/Reg.jpg " alt="Регистрация учасников!" />
        </a>
        </center> -->
        <!-- end of banner -->

        <?php // include (TEMPLATEPATH . '/adv-1.php'); ?>
    </div>

        <?php } else { ?>
        <?php if (!in_array($post->ID, $exclude)) { ?>
        <div class="home-entry">
            <h3 class="entrytitle home-title" id="post-<?php the_ID(); ?>">
                <span class="home-entry-date"><?php the_time('d.m.y'); ?></span>
                <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a>
            </h3>

            <div class="entrybody home-body">
                <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>"/>
                <?php the_excerpt(); ?>
            </div>
        </div>
            <?php } ?>
        <?php } ?>
    <?php endwhile; else: ?>
<p><?php _e('Извините, записей не обнаружено.'); ?></p>
    <?php endif; ?>
<p><?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?></p>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>