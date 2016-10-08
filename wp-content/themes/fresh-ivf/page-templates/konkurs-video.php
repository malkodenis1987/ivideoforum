<?php
/**
 * Template Name: Конкурс - Видео
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in Twenty Twelve consists of a page content area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage ivideoforum
 * @since ivideoforum 1.0
 */
session_start();
get_header(); ?>
<?php $current_user = wp_get_current_user(); ?>
<div id="content" class="full-width">
    <div class="entry">
        <?php if (have_posts()) { ?>
            <?php while (have_posts()) { ?>
                <?php the_post(); ?>
                <h3 class="entrytitle" id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>
                <div class="columns clearfix">
                    <!-- Условия -->
                    <div class="column cont">
                        <?php the_content(); ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>

        <div class="entrybody">
            <?php
				$orderby = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'post_date';
                $paged = get_query_var('paged') ? get_query_var('paged') : 1;
                $args = array(
                    'post_type'         => 'photo',
                    'posts_per_page'    => 50,
					//'meta_key'          => 'ratings_average',
                    //'orderby'           => $orderby,
                    'paged'             => $paged,
					'order'             => 'DESC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'post_format',
                            'field' => 'slug',
                            'terms' => 'post-format-video'
                        )
                    )
                );
                $photos = query_posts($args);
            ?>
            <?php if ($photos) { ?>
				<p class="sortby">Сортировать по <a class="<?php if ($_GET['orderby'] === 'post_date') echo 'active'; ?>" href="?orderby=post_date">дате</a> | <a class="<?php if ($_GET['orderby'] === 'meta_value_num') echo 'active'; ?>" href="?orderby=meta_value_num">рейтингу</a></p>
                <div id="gallery" class="clearfix">
                    <ul>
                        <?php foreach ($photos as $photo) { ?>
                            <?php $thumbImage = wp_get_attachment_image_src(get_post_thumbnail_id($photo->ID), 'thumbnail'); ?>
                            <?php $fullImage = wp_get_attachment_image_src(get_post_thumbnail_id($photo->ID), 'full'); ?>
                            <?php if ($thumbImage) { ?>
                                <li>
                                    <?php if(function_exists('the_ratings')) { the_ratings('div', $photo->ID); } ?>
                                    <a data-fancybox-type="iframe" class="fancybox" href="<?php echo get_permalink($photo->ID); ?>"><img src="<?php echo $thumbImage[0]; ?>" alt="" /></a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            <?php } else { ?>
                <p>Еще не добавлено ни одной работы.</p>
            <?php } ?>
            <?php wp_pagenavi(); ?>
            <?php wp_reset_query(); ?>
        </div>
        <!-- Comments -->
        <h3>Обсуждение</h3>
        <ul class="tabs">
            <li class="disqus"><a rel="nofollow" href="#tab1">Disqus</a></li>
            <li class="vk"><a rel="nofollow" href="#tab2">ВКонтакте</a></li>
            <li class="facebook"><a rel="nofollow" href="#tab3">Facebook</a></li>
        </ul>
        <div class="tab_container">
            <div id="tab1" class="tab_content">
                <?php disqus_embed('ivideoforum'); ?>
            </div>
            <div id="tab2" class="tab_content">
                <div id="vk_comments"></div>
                <script type="text/javascript">
                    VK.Widgets.Comments("vk_comments", {limit: 20, width: "496", attach: "*"});
                </script>
            </div>
            <div id="tab3" class="tab_content">
                <fb:comments href="<?php the_permalink() ?>" num_posts="10" width="470"></fb:comments>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>