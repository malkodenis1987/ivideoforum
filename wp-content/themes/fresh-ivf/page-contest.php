<?php get_header(); ?>

<div id="content">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  <div class="entry">
    <h3 class="entrytitle" id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>
    <div class="entrymeta-single">
    </div>

    <?php $children = wp_list_pages('title_li=&child_of='.$post->ID.'&echo=0'); ?>

        <div id="subpages">
	      <?php $back = show_page_parent() ? show_page_parent_links() : ""; ?>
					<?php if ($back) { ?>
          	<p>&larr; <? echo $back; ?></p>
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
  <?php comments_template(); // Get wp-comments.php template ?>
  <?php endwhile; else: ?>
  <p>
    <?php _e('Извините, не найдено записей.'); ?>
  </p>
  <?php endif; ?>
  <p>
    <?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>
  </p>
</div>

<?php get_footer(); ?>
