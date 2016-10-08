<?php if ($post->post_type !== 'photo') { ?>
    <br />
    <div id="bottom-adwert">
        <?php include (TEMPLATEPATH . '/adv-bottom.php'); ?>
    </div>

</div>

<div id="footer">
    <div id="latest-photos" class="clearfix">
        <?php
            $args = array(
                'post_type'         => 'photo',
                'posts_per_page'    => 10
            );
            $photos = query_posts($args);
        ?>
        <?php if ($photos) { ?>
            <h5>Новые свадебные фотографии</h5>
            <br />
            <ul>
                <?php foreach ($photos as $photo) { ?>
                <?php $thumbImage = wp_get_attachment_image_src(get_post_thumbnail_id($photo->ID), 'thumbnail'); ?>
                <?php $fullImage = wp_get_attachment_image_src(get_post_thumbnail_id($photo->ID), 'full'); ?>
                <?php if ($thumbImage) { ?>
                    <li>
                        <a href="<?php echo get_permalink(get_page_id('contest')); ?>"><img src="<?php echo $thumbImage[0]; ?>" alt="" /></a>
                    </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        <?php } ?>
    </div>
    <br />
  	<p> Copyright &copy;&nbsp;2006-2013 <a href="http://www.ivideoforum.org/">iVideoForum</a> | Разработка Виктор Бурре</p>
    <p>&nbsp;</p>
    <p>
		<!-- Yandex.Metrika informer -->
		<a href="http://metrika.yandex.ru/stat/?id=22808863&amp;from=informer"
		target="_blank" rel="nofollow"><img src="//bs.yandex.ru/informer/22808863/3_1_FFFFFFFF_EFEFEFFF_0_pageviews"
		style="width:88px; height:31px; border:0;" alt="Яндекс.Метрика" title="Яндекс.Метрика: данные за сегодня (просмотры, визиты и уникальные посетители)" onclick="try{Ya.Metrika.informer({i:this,id:22808863,lang:'ru'});return false}catch(e){}"/></a>
		<!-- /Yandex.Metrika informer -->

		<!-- Yandex.Metrika counter -->
		<script type="text/javascript">
		(function (d, w, c) {
			(w[c] = w[c] || []).push(function() {
				try {
					w.yaCounter22808863 = new Ya.Metrika({id:22808863,
							clickmap:true,
							trackLinks:true,
							accurateTrackBounce:true});
				} catch(e) { }
			});

			var n = d.getElementsByTagName("script")[0],
				s = d.createElement("script"),
				f = function () { n.parentNode.insertBefore(s, n); };
			s.type = "text/javascript";
			s.async = true;
			s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

			if (w.opera == "[object Opera]") {
				d.addEventListener("DOMContentLoaded", f, false);
			} else { f(); }
		})(document, window, "yandex_metrika_callbacks");
		</script>
		<noscript><div><img src="//mc.yandex.ru/watch/22808863" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
		<!-- /Yandex.Metrika counter -->
	</p>
	
</div>
<!-- BEGIN JIVOSITE CODE {literal} -->
<script type='text/javascript'>
    (function(){ var widget_id = '90678';
        var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);})();
</script>
<!-- {/literal} END JIVOSITE CODE -->

<?php } ?>

</body>

</html>