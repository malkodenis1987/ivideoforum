<?php
get_header();
?>

<div id="content">
    <?php if (have_posts()) : ?>
    <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
    <?php if (is_category()) { ?>
        <h2 class='archives'><span><?php echo single_cat_title(); ?>:</span> все статьи</h2>
        <br>
        <?php
    } elseif (is_tag()) {
        ?>
        <h2 class='archives'><span><?php echo single_tag_title(); ?>:</span> все статьи</h2>
        <?php
    } elseif (is_day()) {
        ?>
        <h2 class='archives'><span><?php the_time('j M Y'); ?>:</span> все статьи</h2>
        <?php
    } elseif (is_month()) {
        ?>
        <h2 class='archives'><span><?php the_time('F Y'); ?>:</span> все статьи</h2>
        <?php
    } elseif (is_year()) {
        ?>
        <h2 class='archives'><span><?php the_time('Y'); ?>:</span> все статьи</h2>
        <?php
    } elseif (is_author()) {
        ?>
        <h2 class='archives'>Author Archive</h2>
        <?php
    } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {
        ?>
        <h2>Blog Archives</h2>
        <?php } ?>

    <?php while (have_posts()) : the_post(); ?>

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

        <div class="home-entry">

            <h3 class="entrytitle home-title" id="post-<?php the_ID(); ?>">
                <span class="home-entry-date"><?php the_time('d.m.y'); ?></span>
                <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a>
            </h3>

            <div class="entrybody home-body">
                <img src="<?php echo $anons_img_url ?>" alt="<?php echo $anons_img_title ?>" width="80" height="60"/>
                <?php the_excerpt(); ?>
            </div>
            <?php trackback_rdf(); ?>
        </div>

        <?php endwhile; else: ?>
    <p>
        <?php _e('Извините, записей не обнаружено.'); ?>
    </p>
    <?php endif; ?>

    <p><?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?></p>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
