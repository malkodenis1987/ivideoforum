<?php
/*
Plugin Name: Auto_more
Plugin URI: http://blog.portal.kharkov.ua/
Description: Автоматическая вставка &lt;!--more--&gt; Если у поста заполнено поле "цитата" (excerpt), будет выведено оно. Плагин требует наличия mbstring расширения php.
Author: Yuri 'Bela' Belotitski
Version: 3.1.1 mb_string
Author URI: http://www.portal.khakrov.ua/
*/

function auto_more($posts) {

 if (is_single() || is_page()) return $posts;

 $offset = 256;

 $breakpoints = array ("<p" => 0, "</p>" => 4, "<br" => 0, "\x0D\x0A\x0D\x0A" => 0, "\x0A\x0A" => 0,
      "<table" => 0, "</table" => 8, "<ul" => 0, "</ul>" => 5, "<h" => 0 , "</h" => 5 );

 for ($i=0;$i<count($posts);$i++) {
	if ($posts[$i]->post_excerpt) {
		$posts[$i]->post_content = $posts[$i]->post_excerpt."\n<!--more-->";
	}
	elseif ((strpos($posts[$i]->post_content, '<!--more') === false)
	 and (strpos($posts[$i]->post_content, '<!--nomore') === false))  {

	      $p = mb_strlen($posts[$i]->post_content,"UTF-8") ;
	      if ($p > $offset) {
	      	foreach ($breakpoints as $brp => $o2) {
				if ($p1 = mb_strpos(mb_strtolower($posts[$i]->post_content,"UTF-8"),$brp,$offset,"UTF-8")) {
					if ($p > $p1 + $o2) $p = $p1 + $o2;
				}
			}
			if ($p < mb_strlen($posts[$i]->post_content,"UTF-8")) {
				$posts[$i]->post_content = force_balance_tags(mb_substr($posts[$i]->post_content,0,$p,"UTF-8"))."\n<!--more-->";
				}
         }     
         }
  }
  return $posts;
}

add_filter('the_posts', 'auto_more', 1);?>
