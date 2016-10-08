<?php get_header(); ?>

<div id="content">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="entry">
        <h3 class="entrytitle" id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>

        <div class="entrymeta-single"></div>

        <?php $children = wp_list_pages('title_li=&child_of=' . $post->ID . '&echo=0'); ?>

        <div id="subpages">
            <?php $back = show_page_parent() ? show_page_parent_links() : ""; ?>
            <?php if ($back) { ?>
                <p>&larr; <?php echo $back; ?></p>
            <?php } ?>
            <?php if ($children) { ?>
                <ul>
                    <?php echo $children; ?>
                </ul>
            <?php } ?>
        </div>

        <div class="entrybody">
            <?php the_content(__('Read more &raquo;'));?>
            <?php wp_link_pages('before=<div class="page-links">Страницы: &after=</div>'); ?>
        </div>
        <!-- <?php trackback_rdf(); ?> -->
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
    <?php // comments_template(); ?>
    <?php endwhile; else: ?>
    <p>
        <?php _e('Извините, не найдено записей.'); ?>
    </p>
    <?php endif; ?>
    <p>
        <?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>
    </p>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
