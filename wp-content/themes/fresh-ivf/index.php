<?php get_header(); ?>

<div id="content">

  <?php //include("wp-content/adsense.txt");?>

  <?php /*if (is_home()) { query_posts('&category_name=news'); }*/ ?>
  <?php if (is_home()) { query_posts($query_string.'&category_name=news'); } ?>

  <?php $pnum = 0 ?>

  <?php if (have_posts()) : while (have_posts()) : the_post(); $pnum++;?>

    <?php
      $anons_img_url = get_post_meta($post->ID, "anons_img_url", true);
      $anons_img_url = empty($anons_img_url) ? get_bloginfo('stylesheet_directory') . "/images/imgholder.gif" : $anons_img_url;
        
      $anons_img_title = get_post_meta($post->ID, "anons_img_title", true);
      $anons_img_title = empty($anons_img_title) ? "Место для картинки" : $anons_img_title;
    ?>

    <?php if ($pnum == 1) { ?>

      <div class="fpost" onclick="location.href='<?php the_permalink() ?>'">

        <h3 class="entrytitle fpost-title" id="post-<?php the_ID(); ?>">
          <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a>
        </h3>
        
        <div class="entrymeta">
          <?php the_time('j M Y г. ');
          comments_popup_link('', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" /> 1 комментарий', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" />  Комментарии: %');
          edit_post_link(__('Edit This'), '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/edit.gif"  title="" alt="*" /> ');?>
        </div>

        <div class="fpost-body">
          <div class="fpost-body-img">
            <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>" />
          </div>
          <div class="fpost-body-text">
            <?php the_excerpt(); ?>
            <a href="<?php the_permalink() ?>">Узнай больше &raquo;</a>
          </div>
        </div>
      </div>     
     <img src="/images/chernigov2009_468.gif" />
   <?php } elseif ($pnum == 2) { ?>
      <div class="fpost2-wrap">
      <div class="fpost2">

        <h3 class="entrytitle fpost2-title" id="post-<?php the_ID(); ?>">
          <a href="<?php the_permalink() ?>" rel="bookmark" title="Опубликовано <?php the_time('j M Y г. '); ?>" ><?php the_title(); ?></a>
        </h3>
        
        <div class="entrybody fpost2-body">
          <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>" />
          <?php the_excerpt(); ?>
        </div>

      </div>

  <?php } elseif ($pnum == 3) { ?>

      <div class="fpost3">

        <h3 class="entrytitle fpost3-title" id="post-<?php the_ID(); ?>">
          <a href="<?php the_permalink() ?>" rel="bookmark" title="Опубликовано <?php the_time('j M Y г. '); ?>" ><?php the_title(); ?></a>
        </h3>
        
        <div class="entrybody fpost3-body">
          <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>" />
          <?php the_excerpt(); ?>
        </div>

      </div>
      </div>
      
      <div class="adwrap">
        <?php include (TEMPLATEPATH . '/adv-1.php'); ?>
      </div>
        
  <?php } else { ?>

    <div class="home-entry">

      <h3 class="entrytitle home-title" id="post-<?php the_ID(); ?>">
        <span class="home-entry-date"><?php the_time('d.m.y'); ?></span>
        <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a>
      </h3>
      
      <div class="entrybody home-body">
        <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>" />
        <?php the_excerpt(); ?>
      </div>
      
    </div>

  <?php } ?>
  
  <?php endwhile; else: ?>

    <p><?php _e('Извините, записей не обнаружено.'); ?></p>

  <?php endif; ?>
    
  <p><?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?></p>
    
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>

