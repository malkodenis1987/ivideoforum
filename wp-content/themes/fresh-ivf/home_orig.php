<?php get_header(); ?>

<div id="content">

  <?php //include("wp-content/adsense.txt");?>

  <?php /*if (is_home()) { query_posts('&category_name=news'); }*/ ?>
  <?php if (is_home()) { query_posts($query_string.'&category_name=news'); } ?>

  <?php $pnum = 0 ?>

  <?php if (have_posts()) : while (have_posts()) : the_post(); $pnum++;?>

    <?php
      $anons_img_url = get_post_meta($post->ID, "anons_img_url", true);
      $anons_img_url = empty($anons_img_url) ? "http://webdev.thefreehoster.com/wp-content/themes/fresh-ivf/images/imgholder.gif" : $anons_img_url;
        
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
          //comments_popup_link('', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" /> 1 комментарий', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" />  Комментарии: %');
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
          <!--<?php the_content('Читать полностью &raquo;');?>-->
        </div>

      </div>

  <?php } elseif ($pnum == 2) { ?>
      <div class="fpost2-wrap">
      <div class="fpost2">

        <h3 class="entrytitle fpost2-title" id="post-<?php the_ID(); ?>">
          <a href="<?php the_permalink() ?>" rel="bookmark" title="Опубликовано <?php the_time('j M Y г. '); ?>" ><?php the_title(); ?></a>
        </h3>
        
        <!--<div class="entrymeta">
          <?php the_time('j M Y г. ');
          comments_popup_link('', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" /> 1 комментарий', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" />  Комментарии: %');
          edit_post_link(__('Edit This'), '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/edit.gif"  title="" alt="*" /> ');?>
        </div>-->

        <div class="entrybody fpost2-body">
          <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>" />
          <?php the_excerpt(); ?>
          <!--<?php the_content('Читать полностью &raquo;');?>-->
        </div>

      </div>

  <?php } elseif ($pnum == 3) { ?>

      <div class="fpost3">

        <h3 class="entrytitle fpost3-title" id="post-<?php the_ID(); ?>">
          <a href="<?php the_permalink() ?>" rel="bookmark" title="Опубликовано <?php the_time('j M Y г. '); ?>" ><?php the_title(); ?></a>
        </h3>
        
        <!--<div class="entrymeta">
          <?php the_time('j M Y г. ');
          comments_popup_link('', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" /> 1 комментарий', '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/comments.gif"  alt="*" />  Комментарии: %');
          edit_post_link(__('Edit This'), '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/edit.gif"  title="" alt="*" /> ');?>
        </div>-->

        <div class="entrybody fpost3-body">
          <img src="<?php echo($anons_img_url); ?>" alt="<?php echo($anons_img_title); ?>" />
          <?php the_excerpt(); ?>
          <!--<?php the_content('Читать полностью &raquo;');?>-->
        </div>

      </div>
      </div>
<!-- ПОДПИСКА -->
      <div>
<!-- SmartResponder.ru subscribe form code (begin) -->
<style type="text/css">/*<![CDATA[*/.sr-box table {width: auto !important;}#elem_table_element_header{width:502px !important;}.sr-box img {max-width: none;}.sr-box br {display:none;}.sr-box p {display:none;}.sr-box {display: block !important;position: relative;width: 250px;}.sr-box-list table{border:0 !important}.sr-box-list td{padding:0px !important; border:0 !important}.sr-box-list{clear: both;display: block;list-style: none outside none !important;position: relative;margin: 0!important;padding: 0!important;}.sr-box-list li {list-style: none outside none !important;margin: 0;background-image: none !important; background-color: #FFFFFF;color: black;display: block;font-family: Arial;font-size: 12px;height: 60px;padding: 2px 25px;}.sr-element {height: 60px !important;line-height: 60px !important;}.sr-box-list li label {height: 15px;line-height: 15px;position: relative;z-index: 10;display: block;margin-bottom: 5px;overflow: hidden;}.sr-box-list input[type="text"] {font-family: Arial;font-size: 12px;height: 33px;margin-bottom: 20px;padding: 5px 10px;position: relative;width: 100%;z-index: 10; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; -ms-box-sizing:border-box; box-sizing:border-box;}.sr-box-list input[type="submit"] {white-space:normal;cursor:pointer}.sr-box-list select{width:100%}.sr-slider-captcha{position: relative;border: none 0;margin-top: -60px;z-index: 10;height: 33px;width: 100%;}#cnt{height:60px;}#cnt img {margin-bottom: -6px;}/*]]>*/</style>
				<script type="text/javascript" src="https://imgs.smartresponder.ru/forms/additional_files/smart-script.js"></script>
<script type="text/javascript">
                            _sr(function() {
                                if(_sr('form[name="SR_form_13337_29"]').find('input[name="script_path_13337_29"]').length && (document.charset || document.characterSet).indexOf('1251') != -1) {
                                    _sr('input[name="subscribe"]').attr('onclick', 'return false;'),
                                    _sr.ajax({
                                        dataType: 'jsonp',
                                        data: { form_id : '13337_29', remote_charset : (document.charset || document.characterSet), file : _sr('input[name="script_path_13337_29"]').val(), phase : 'charset_rebuild'},
                                        url: _sr('form[name="SR_form_13337_29"]').prop('action').replace('subscribe', 'user/forms_generator'),
                                        success: callback_charset_13337_29
                                    });
                                } else {
                                    _sr.getScript(_sr('input[name="script_url_13337_29"]').val());
                                }
                            });
                            function callback_charset_13337_29(data) {
                                console.log(data.message)
                                if(data.status == 'success') {
                                    _sr('input[name="subscribe"]').attr('onclick', '');
                                        _sr.getScript(_sr('input[name="script_url_13337_29"]').val())
                                }
                            }
                        </script>
<form style="width: 500px;" class="sr-box" method="post" action="https://smartresponder.ru/subscribe.html" target="_blank" name="SR_form_13337_29">
<ul class="sr-box-list"><li class="sr-13337_29" class="" style="text-align: center; background-color: rgb(255, 255, 255); border-radius: 0px 0px 0px 0px; border: 0px solid rgb(0, 0, 0); position: relative; left: 0px; top: 0px; height: auto;">     <label style="height: auto; line-height: 25px; margin-top: 15px; font-size: 18px; color: rgb(84, 93, 103); font-family: arial; font-weight: bold; font-style: normal;" class="">Присоединиться к сообществу </label><input style="font-family: Arial; color: rgb(0, 0, 0); font-size: 12px; font-style: normal; font-weight: normal; background-color: rgb(255, 255, 255); border: medium none;" value="" name="element_header" type="hidden"></li><li class="sr-13337_29" style="border-radius: 0px 0px 0px 0px; height: 50px; text-align: center; background-color: rgb(255, 255, 255);">     
<label class="remove_labels" style="font-family: arial; color: rgb(0, 0, 0); font-size: 12px; font-style: normal; font-weight: normal; display: none;"></label>
<input style="margin-top: 15px; background-image: none; font-family: arial; color: rgb(200, 200, 200); font-size: 12px; font-style: normal; font-weight: normal; background-color: rgb(255, 255, 255); border: 1px solid rgb(200, 200, 200); border-radius: 0px 0px 0px 0px; height: 35px;" name="field_name_first" class="sr-required" value="Ваше имя" type="text">
</li><li class="sr-13337_29" style="text-align: center; height: 50px; background-color: rgb(255, 255, 255); border-radius: 0px 0px 0px 0px;">   <label class="remove_labels" style="font-family: arial; color: rgb(0, 0, 0); font-size: 12px; font-style: normal; font-weight: normal; display: none;"></label><input value="Фамилия:" style="margin-top: 15px; background-image: none; font-family: arial; color: rgb(200, 200, 200); font-size: 12px; font-style: normal; font-weight: normal; background-color: rgb(255, 255, 255); border: 1px solid rgb(200, 200, 200); border-radius: 0px 0px 0px 0px; height: 35px;" name="field_name_last" type="text"></li><li class="sr-13337_29" style="border-radius: 0px 0px 0px 0px; height: 50px; text-align: center; background-color: rgb(255, 255, 255);">       
<label class="remove_labels" style="font-family: arial; color: rgb(0, 0, 0); font-size: 12px; font-style: normal; font-weight: normal; display: none;"></label>
<input style="margin-top: 15px; background-image: none; font-family: arial; color: rgb(200, 200, 200); font-size: 12px; font-style: normal; font-weight: normal; background-color: rgb(255, 255, 255); border: 2px solid rgb(200, 200, 200); border-radius: 0px 0px 0px 0px; height: 35px;" name="field_email" class="sr-required" value="Ваш email-адрес" type="text">
</li><li class="sr-13337_29" style="text-align: center; height: 50px; background-color: rgb(255, 255, 255); border-radius: 0px 0px 0px 0px;">        <label class="remove_labels" style="font-family: arial; color: rgb(0, 0, 0); font-size: 12px; font-style: normal; font-weight: normal; display: none;"></label><input value="Ваш город:" style="margin-top: 15px; background-image: none; font-family: arial; color: rgb(200, 200, 200); font-size: 12px; font-style: normal; font-weight: normal; background-color: rgb(255, 255, 255); border: 1px solid rgb(200, 200, 200); border-radius: 0px 0px 0px 0px; height: 35px;" name="field_city" type="text"></li><li class="sr-13337_29" style="border-radius: 0px 0px 0px 0px; text-align: center; background-color: rgb(255, 255, 255); border: 0px none; height: 65px;">         <table id="elem_table_subscribe" style="display: inline-table; border-collapse: separate; margin-top: 12px;" cellpadding="0" cellspacing="0" border="0"><tbody><tr><td style="background: url('https://imgs.smartresponder.ru/forms/users/167566/parts/subscribe/383592/left.png') no-repeat scroll left center transparent; width: 17px; height: 35px;" id="elem_left_subscribe" valign="middle"></td><td id="elem_container_subscribe" style="vertical-align: middle;"><input style="background: url('https://imgs.smartresponder.ru/forms/users/167566/parts/subscribe/383592/bg.png') repeat scroll left center transparent; font-family: arial; color: rgb(255, 255, 255); font-size: 14px; font-style: normal; font-weight: bold; border: 0px solid rgb(240, 240, 240); margin: 0px; padding: 0px 12px; height: 35px; width: 100%;" name="subscribe" value="Следить за новостями" type="submit"></td><td style="background: url('https://imgs.smartresponder.ru/forms/users/167566/parts/subscribe/383592/right.png') no-repeat scroll left center transparent; width: 17px; height: 35px;" id="elem_right_subscribe"></td></tr></tbody></table></li></ul>
<input name="uid" value="167566" type="hidden">
<input name="did[]" value="191287" type="hidden"><input name="tid" value="0" type="hidden"><input name="lang" value="ru" type="hidden"><input value="/167001-168000/167566/sr-js-13337_31.js" name="script_path_13337_31" type="hidden"><input value="https://imgs.smartresponder.ru/forms/private/167001-168000/167566/sr-js-13337_31.js" name="script_url_13337_31" type="hidden"><input value="/167001-168000/167566/sr-js-13337_31.js" name="script_path_13337_31" type="hidden"><input value="https://imgs.smartresponder.ru/forms/private/167001-168000/167566/sr-js-13337_31.js" name="script_url_13337_31" type="hidden"><input value="/167001-168000/167566/sr-js-13337_67.js" name="script_path_13337_67" type="hidden"><input value="https://imgs.smartresponder.ru/forms/private/167001-168000/167566/sr-js-13337_67.js" name="script_url_13337_67" type="hidden"><input value="/167001-168000/167566/sr-js-13337_29.js" name="script_path_13337_29" type="hidden"><input value="https://imgs.smartresponder.ru/forms/private/167001-168000/167566/sr-js-13337_29.js" name="script_url_13337_29" type="hidden"></form>
<!-- SmartResponder.ru subscribe form code (end) -->
      </div>
<!-- // ПОДПИСКА -->

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