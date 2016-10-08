<div class="sidebar-wrap">
  
  <?php if (is_category('news') && !is_single()) { ?>
  
  <div id="superpost">
	 
    <div id="superpost-adwert">
     <?php include (TEMPLATEPATH . '/adv-superpost.php'); ?>
    </div>
	
    <?php query_posts('category_name=superpost&showposts=1'); ?>
	
    <?php while (have_posts()) : the_post(); ?>
      <?php the_content("Узнай больше &raquo;");?>
      <p><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title() ?></a></p>
    <?php endwhile; ?>
    
  </div>

  <?php } elseif (is_single() || is_category() || is_page() || is_tag() || is_archive()) { ?>
  <div id="sidebar-adwert">
    <?php // include (TEMPLATEPATH . '/adv-sidebar.php');// Google AD ?>
  </div>
  
  <?php }	?>
  
  <div id="sidebar">

    <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(1) ) : ?>
      <h2>Рубрики</h2>
        <ul>
          <?php wp_list_cats(); ?>
        </ul>

      <h2>Архивы</h2>
        <ul>
          <?php wp_get_archives('type=monthly'); ?>
        </ul>
       
      <h2>Подпишись!</h2>
        <ul id="feed">
          <li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php _e('Syndicate this site using RSS'); ?>">RSS Articles</a></li>
        </ul>

    <?php endif; ?>

    <div id="searchdiv">
    	<h2>Поиск</h2>
        <form id="searchform" method="get" action="./index.php">
          <input type="text" name="s" id="s" size="15"/>
          <input name="sbutt" type="submit" value="Найти" alt="Submit"  />
        </form>
    </div>
    
    <div id="latestpost">
    	<h2>Последние статьи</h2>
      <ul>
      <?php
      global $post;
      $myposts = get_posts('numberposts=5&offset=0&category=4');
      foreach($myposts as $post) : ?>
        <li><a href="<?php the_permalink(); ?>" title="Опубликовано <?php the_time('d.m.y'); ?>"><?php the_title(); ?></a></li>
      <?php endforeach; ?>
				<li><a href="/category/articles/">Все статьи</a></li>
      </ul>
    </div>
    
  </div>

  <?php include (TEMPLATEPATH . '/sidebar-right.php'); ?>

  <div class="clear"></div>

  <div id="buttons">
    <h2>Поддержите наш сайт</h2>
    <p>Мы будем признательны вам, если вы сможете разместить на своих ресурсах кнопку со ссылкой на наш сайт. Просто скопируйте код из формы и вставьте в исходный код ваших страниц.</p>
    <div style="width: 100px; height: 66px; float: left; margin-bottom: 10px;">
<!-- <a href="http://ivideoforum.org/" target="_blank" title="Форум видеомейкеров и фотографов">Форум видеомейкеров и фотографов</a> -->
    </div>
    <div style="width: 320px; height: 66px; float: left;">
 
      <form action="" method="post">
      <textarea name="textarea" readonly style="width: 320px; height: 66px; vertical-align: top;" onclick="this.select();"> <a href="http://ivideoforum.org/" target="_blank" title="Форум видеомейкеров и фотографов">Форум видеомейкеров и фотографов</a></textarea>
      </form>
    </div>
    <p><br clear="all" />Если вы хотите поддержать нас, разместив наш баннер, <a href="http://www.ivideoforum.org/about/promotion/" title="Промо-материалы">нажмите здесь</a> для получения кода.</p>
  </div>

<div align="center">
<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?17"></script>
<!-- VK Widget -->
<div id="vk_groups" style="float: left;padding-left:20px;"></div>
<script type="text/javascript">
VK.Widgets.Group("vk_groups", {mode: 0, width: "200"}, 20401422);
</script>

<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2FIvideoforum&amp;width=220&amp;height=290&amp;colorscheme=light&amp;show_faces=true&amp;border_color&amp;stream=false&amp;header=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:220px; height:290px;" allowTransparency="true"></iframe>

</div>
<div align="center">

<a href="http://www.nevesta.cn.ua/" target="_blank" rel="nofollow">Свадьба в Чернигове</a>
<br />
<a href="http://easy-step.ru/" target="_blank" rel="nofollow">Стедикам производство и продажа</a>
</div>
  <div class="sidebar-bottom"></div>

</div>
