<?php get_header(); ?>

<div id="content">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php if ($post->post_type === 'photo') { ?>
            <div class="entry photo">
                <?php $current_user = wp_get_current_user(); ?>
                <?php $thumbImage = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'photo'); ?>
                <?php $fullImage = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'contest'); ?>
                <?php if ($thumbImage) { ?>
                    <div class="photo-preview loading">
                        <?php if ( has_post_format( 'video' )) { ?>
                            <div class="video-content">
                                <?php the_content(); ?>
                            </div>
                        <?php } else { ?>
                            <img src="<?php echo $fullImage[0]; ?>" alt="" />
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php ivideo_post_nav(); ?>
                <div class="info">
                    <p><strong>Название:</strong> <?php the_title(); ?></p>
                    <?php if (is_user_logged_in() && current_user_can('manage_options') ) { ?>
                        <p><strong>ФИО:</strong> <?php echo get_post_meta($post->ID, 'fio', true); ?></p>
                        <p><strong>Город:</strong> <?php echo get_post_meta($post->ID, 'city', true); ?></p>
                        <p><strong>Email:</strong> <?php echo get_post_meta($post->ID, 'email', true); ?></p>
                        <p><strong>Телефон:</strong> <?php echo get_post_meta($post->ID, 'phone', true); ?></p>
                    <?php } ?>
                </div>
                <div class="rait">
                    <h3>Оцените работу:</h3>
                    <?php if(function_exists('the_ratings')) { the_ratings(); } ?>
                </div>
                <div class="comments">
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
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#wpadminbar').hide();
                });
            </script>
		<?php } else if ($post->post_type === 'ads') { ?>
			<div class="entry">
                <?php $thumbImage = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'photo'); ?>
                <?php $fullImage = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
                <?php if ($thumbImage) { ?>
                    <div class="photo-preview loading">
                        <img src="<?php echo $thumbImage[0]; ?>" alt="" />
                        <a class="zoom" data-lightbox="image" href="<?php echo $fullImage[0]; ?>">&nbsp;</a>
                    </div>
                <?php } ?>
                <div class="info">
                    <p><strong>Название:</strong> <?php the_title(); ?></p>
                    <p><strong>Автор:</strong> <?php echo get_post_meta($post->ID, 'ads_author', true); ?></p>
					<p><strong>Email:</strong> <?php echo get_post_meta($post->ID, 'ads_email', true); ?></p>
					<p><strong>Телефон:</strong> <?php echo get_post_meta($post->ID, 'ads_phone', true); ?></p>
					<p><strong>Город:</strong> <?php echo get_field_label('ads_city', $post->ID, get_post_meta($post->ID, 'ads_city', true)); ?></p>
                </div>
                <div class="comments">
                    <h3>Описание:</h3>
                    <?php echo apply_filters('the_content', $post->post_content); ?>
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
                        <?php disqus_embed('ivideoforumivideoforum'); ?>
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
        <?php } else { ?>
            <div class="entry">
                <h3 class="entrytitle" id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>
                <div class="entrymeta">
                    <?php
                        echo('&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/at.gif"  title="" alt="*" />&nbsp;'); the_time('j M Y г. ');
                        echo('&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/category.gif"  title="" alt="*" />&nbsp;'); the_category(', ');
                        the_tags(' <img src="' . get_stylesheet_directory_uri() . '/images/tags.gif"  title="" alt="*" />  ', ', ');
                        edit_post_link(__('Edit This'), '&nbsp;<img src="' . get_stylesheet_directory_uri() . '/images/edit.gif"  title="" alt="*" /> ');
                    ?>
                </div>

                <div class="entrybody">
                    <?php the_content(__('Читать дальше &raquo;'));?>
                    <?php wp_link_pages('before=<div class="page-links">Страницы: &after=</div>'); ?>
                </div>

                <div class="entrymeta2">
                    <table>
                        <?php if (get_post_meta($post->ID, "source_url", true)) : ?>
                            <?php
                                echo("<tr><td>Источник:</td><td><a href='" . get_post_meta($post->ID, "source_url", true) . "'>" .
                                (get_post_meta($post->ID, "source_title", true) ? get_post_meta($post->ID, "source_title", true) : get_post_meta($post->ID, "source_url", true)) . "</a></td></tr>");
                            ?>
                        <?php endif; ?>
                        <tr>
                            <td>Постоянный адрес статьи:</td>
                            <td><input style="width: 300px;" value="<?php the_permalink() ?>" onclick="this.select();" type="text" readonly></td>
                        </tr>
                        <tr>
                            <td>Адрес для трекбэка:</td>
                            <td><input style="width: 300px;" value="<?php trackback_url(); ?>" onclick="this.select();" type="text" readonly></td>
                        </tr>
                    </table>
                </div>
                <?php trackback_rdf(); ?>
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
                    <?php disqus_embed('ivideoforumivideoforum'); ?>
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
        <?php } ?>

    <?php endwhile; else: ?>
        <p><?php _e('Извините, записей не обнаружено.'); ?></p>
    <?php endif; ?>
    <p><?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?></p>
  
</div>
<?php if ($post->post_type !== 'photo') { ?>
    <?php get_sidebar(); ?>
<?php } ?>

<?php get_footer(); ?>
