<?php
/**
 * Template Name: Объявления
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

get_header(); ?>
<?php $current_user = wp_get_current_user(); ?>
<div id="content" class="full-width">
    <div class="entry">
        <?php if (have_posts()) { ?>
            <?php while (have_posts()) { ?>
                <?php the_post(); ?>
                <p class="ads-nav">
                    <a class="add-ads" href="<?php echo get_permalink(get_page_id('ads-add')); ?>">[+] добавить объявление</a>
                    <?php if (is_user_logged_in()) { ?> | <a class="add-ads" href="<?php the_permalink(); ?>?user_id=<?php echo $current_user->ID; ?>">Мои объявления<?php } ?></a>
                </p>
                <h3 class="entrytitle" id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>
            <?php } ?>
        <?php } ?>
        <?php $currentUser   = $_GET['user_id']; ?>
        <?php if ( !is_user_logged_in() && !isset($_GET['user_id'])) { ?>
            <div class="entrymeta-single">
                <?php $cats = get_ads_cats(); ?>
                <?php $cities = get_cities(); ?>
                <?php $currentCats   = $_GET['cat']; ?>
                <?php $currentCities = $_GET['city']; ?>
                <?php if ($cats) { ?>
                    <ul class="ads-cats clearfix">
                        <?php foreach ($cats as $cat) { ?>
                            <li class="<?php if (in_array($cat->term_id, $currentCats)) echo 'current'; ?>">
                                <a href="<?php the_permalink(); ?>?cat[]=<?php echo $cat->term_id; ?>">
                                    <span class="pic">
                                        <img src="<?php echo $cat->description; ?>" alt="" />
                                    </span>
                                    <?php echo $cat->name; ?>
                                </a>
                            </li>
                        <?php } ?>
                        </ul>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (0) { ?>
        <div id="subpages">
            <form class="filters" action="<?php the_permalink(); ?>" method="get">
                <div class="form-content">
                    <h3>Фильтры:</h3>
                    <table>
                        <tr>
                            <td>
                                <h5>Категории</h5>
                                <?php if ($cats) { ?>
                                    <?php foreach ($cats as $cat) { ?>
                                    <p>
                                        <input type="checkbox" name="cat[]" id="cat-<?php echo $cat->term_id; ?>" value="<?php echo $cat->term_id; ?>" <?php if (in_array($cat->term_id, $currentCats)) echo 'checked="checked"'; ?>/>
                                        <label for="cat-<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></label>
                                    </p>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <td>
                                <h5>Города</h5>
                                <?php if ($cities) { ?>
                                    <?php $i = 0; ?>
                                    <div class="column">
                                        <?php foreach ($cities as $key => $city) { ?>
                                            <p>
                                                <input type="checkbox" name="city[]" id="city-<?php echo $key; ?>" value="<?php echo $key; ?>" <?php if (in_array($key, $currentCities)) echo 'checked="checked"'; ?>/>
                                                <label for="city-<?php echo $key; ?>"><?php echo $city; ?></label>
                                            </p>
                                            <?php $i++; ?>
                                            <?php if ($i > 4) { ?>
                                                </div><div class="column">
                                                <?php $i = 0; ?>
                                            <?php } ?>
                                        <?php } ?>

                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <button type="submit">Показать</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
        <?php } ?>

        <div class="entrybody">
            <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
				$args = array(
                    'post_type'      => 'ads',
                    'posts_per_page' => 20,
                    'paged'          => $paged
                );
                if ($currentCats)
                {
                    $termAll = array();
                    foreach ($currentCats as $cat)
                    {
                        $term = get_term($cat, 'ads_cat');
                        array_push($termAll, $term->slug);
                    }
                    $args['ads_cat'] = implode(',', $termAll);
                }
                if ($currentCities)
                {
                    $args['meta_key'] = 'ads_city';
                    $args['meta_value'] = $currentCities;
                }
                if ($currentUser)
                {
                    $args['author'] = $currentUser;
                }
                $ads = query_posts($args);
            ?>
            <?php if ($ads) { ?>
                <div id="ads" class="clearfix">
                    <ul>
                        <?php foreach ($ads as $add) { ?>
                            <?php $thumbImage = wp_get_attachment_image_src(get_post_thumbnail_id($add->ID), 'thumbnail'); ?>
                            <?php $fullImage = wp_get_attachment_image_src(get_post_thumbnail_id($add->ID), 'full'); ?>
                            <?php if ($thumbImage) { ?>
                                <li class="clearfix">
									<div class="thumb">
										<div class="pic">
                                            <a href="<?php echo get_permalink($add->ID); ?>"><img src="<?php echo $thumbImage[0]; ?>" alt="" /></a>
                                        </div>
										<a class="zoom" href="<?php echo $fullImage[0]; ?>">&nbsp;</a>
									</div>
									<div class="text">
										<h4><?php echo $add->post_title; ?></h4>
										<div class="details">
											<p><strong>Автор:</strong> <?php echo get_post_meta($add->ID, 'ads_author', true); ?></p>
                                            <?php if (is_user_logged_in() && current_user_can('manage_options') ) { ?>
                                                <p><strong>Email:</strong> <?php echo get_post_meta($add->ID, 'ads_email', true); ?></p>
											    <p><strong>Телефон:</strong> <?php echo get_post_meta($add->ID, 'ads_phone', true); ?></p>
											    <p><strong>Город:</strong> <?php echo get_field_label('ads_city', $add->ID, get_post_meta($add->ID, 'ads_city', true)); ?></p>
                                            <?php } ?>
                                            <p><strong>Цена:</strong> <?php echo get_post_meta($add->ID, 'ads_price', true); ?> грн.</p>
                                            <p class="excerpt"><strong>Краткое описание:</strong> <span><?php echo $add->post_excerpt; ?>...</span></p>
										</div>
                                        <?php if ( is_user_logged_in() && isset($_GET['user_id'])) { ?>
                                            <p class="more"><a href="<?php echo get_permalink(get_page_id('ads-edit')); ?>?user_id=<?php echo $_GET['user_id']; ?>&ads_id=<?php echo $add->ID; ?>">редактировать</a></p>
                                        <?php } else { ?>
                                            <p class="more"><a href="<?php echo get_permalink($add->ID); ?>">детали</a></p>
                                        <?php } ?>
									</div>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            <?php } else { ?>
                <p>Объявлений по Вашему запросу не найдено.</p>
            <?php } ?>
            <?php wp_pagenavi(); ?>
            <?php wp_reset_query(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>