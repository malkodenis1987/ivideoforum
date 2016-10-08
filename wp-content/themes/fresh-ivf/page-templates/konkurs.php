<?php
/**
 * Template Name: Конкурс
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
                    <!-- Загрузка работы -->
                    <div class="column uploader">
                        <div id="subpages">
                            <h3>Загрузить свою работу</h3>
                            <?php if (is_user_logged_in()) { ?>
                                <form action="<?php bloginfo('template_directory'); ?>/includes/handlers/add-photo.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="return_url" value="<?php the_permalink(); ?>" />
                                    <input type="hidden" name="author" value="<?php echo $current_user->ID; ?>" />
                                    <?php if (isset($_GET['success']) && $_GET['success'] === '1') { ?>
                                    <div class="success">
                                        <p>Работа добавлена. Она будет размещена на сайте как только администратор её подтвердит.</p>
                                    </div>
                                    <?php } ?>
                                    <?php if (isset($_GET['error_code'])) { ?>
                                    <div class="error">
                                        <?php
                                        global $formErrors;
                                        $errorsCodes = explode(',', $_GET['error_code']);
                                        foreach ($errorsCodes as $errorCode)
                                        {
                                            echo '<p>' . $formErrors[$errorCode] . '</p>';
                                        }
                                        ?>
                                    </div>
                                    <?php } ?>
                                    <?php if ( !isset($_GET) || $_GET['success'] !== '1' || isset($_GET['error_code'])) { ?>
                                    <div class="form-content">
                                        <table>
                                            <tr>
                                                <td><label for="title">Название:</label></td>
                                                <td><input type="text" class="" name="title" id="title" /></td>
                                            </tr>
                                            <tr>
                                                <td><label for="fio">ФИО:</label></td>
                                                <td><input type="text" class="" name="fio" id="fio" /></td>
                                            </tr>
                                            <tr>
                                                <td><label for="city">Город:</label></td>
                                                <td><input type="text" class="" name="city" id="city" /></td>
                                            </tr>
                                            <tr>
                                                <td><label for="email">Email:</label></td>
                                                <td><input type="text" class="" name="email" id="email" /></td>
                                            </tr>
                                            <tr>
                                                <td><label for="phone">Телефон:</label></td>
                                                <td><input type="text" class="" name="phone" id="phone" /></td>
                                            </tr>
                                            <tr>
                                                <td><label for="photo">Фото:</label></td>
                                                <td><input type="file" class="" name="photo" id="photo" /></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><button type="submit">Добавить</button></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php } ?>
                                </form>
                            <?php } else { ?>
                                <p>Для размещения работы <a href="<?php echo get_permalink(get_page_id('login')); ?>">войдите</a> на сайт.</p>
                            <?php } ?>
                        </div>
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
					'meta_key'          => 'ratings_average',
                    'orderby'           => $orderby,
                    'paged'             => $paged,
					'order'             => 'DESC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'post_format',
                            'field' => 'slug',
                            'terms' => 'post-format-video',
                            'operator' => 'NOT IN'
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