<?php
/**
 * Template Name: Объявления - Редактировать
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
    <script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
    <script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="entry">
        <h3 class="entrytitle" id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>
        <div class="entrymeta-single"></div>

        <div id="subpages">
            <h3>Редактировать объявление</h3>
            <?php if (is_user_logged_in()) { ?>
                <form action="<?php bloginfo('template_directory'); ?>/includes/handlers/edit-ads.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="return_url" value="<?php the_permalink(); ?>" />
                    <input type="hidden" name="author" value="<?php echo $current_user->ID; ?>" />
                    <input type="hidden" name="ID" value="<?php echo $_GET['ads_id']; ?>" />
                    <?php if (isset($_GET['success']) && $_GET['success'] === '1') { ?>
                        <div class="success">
                            <p>Объявление изменено.</p>
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
                    <?php $add = get_post($_GET['ads_id']); ?>
                    <div class="form-content">
                        <table>
                            <tr>
                                <td><label for="title">Название:</label></td>
                                <td><input type="text" class="" name="title" id="title" value="<?php echo $add->post_title; ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="fio">ФИО:</label></td>
                                <td><input type="text" class="" name="fio" id="fio" value="<?php echo get_post_meta($add->ID, 'ads_author', true); ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="city">Город:</label></td>
                                <td>
                                    <?php $cities = get_cities(); ?>
                                    <?php if ($cities) { ?>
                                        <select name="city" id="city">
                                            <?php foreach ($cities as $key => $city) { ?>
                                                <option <?php if ((int)$key === (int)get_post_meta($add->ID, 'ads_city', true)) { ?>selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $city; ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="category">Категория:</label></td>
                                <td>
                                    <?php $termList = wp_get_post_terms($add->ID, 'ads_cat'); ?>
                                    <?php $cats = get_ads_cats(); ?>
                                    <?php if ($cats) { ?>
                                        <select name="category" id="category">
                                            <?php foreach ($cats as $cat) { ?>
                                                <option <?php if ((int)$cat->term_id === (int)$termList[0]->term_id) { ?>selected="selected"<?php } ?> value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="email">Email:</label></td>
                                <td><input type="text" class="" name="email" id="email" value="<?php echo get_post_meta($add->ID, 'ads_email', true); ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="phone">Телефон:</label></td>
                                <td><input type="text" class="" name="phone" id="phone" value="<?php echo get_post_meta($add->ID, 'ads_phone', true); ?>" /></td>
                            </tr>
                            <tr class="wys">
                                <td><label for="content">Описание:</label></td>
                                <td>
                                    <textarea id="desc" name="content" cols="10" rows="5"><?php echo apply_filters('the_content', $add->post_content); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="content">Краткое описание:</label></td>
                                <td>
                                    <input maxlength="180" type="text" class="long" name="short_desc" id="short_desc" value="<?php echo $add->post_excerpt; ?>" />
                                    <span class="note">(максимум 180 символов)</span>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="content">Цена(грн):</label></td>
                                <td><input type="text" class="" name="price" id="price" value="<?php echo get_post_meta($add->ID, 'ads_price', true); ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="photo">Фото:</label></td>
                                <td>
                                    <?php $thumbImage = wp_get_attachment_image_src(get_post_thumbnail_id($add->ID), 'small-thumbnail'); ?>
                                    <?php if ($thumbImage) { ?>
                                        <img class="small-thumb" src="<?php echo $thumbImage[0]; ?>" alt="" />
                                    <?php } ?>
                                    <input type="file" class="" name="photo" id="photo" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><button type="submit">Изменить</button></td>
                            </tr>
                        </table>
                    </div>
                </form>
            <?php } else { ?>
                <p>Для изменения объявления <a href=""></a>войдите на сайт.</p>
            <?php } ?>
        </div>

    </div>

    <?php endwhile; else: ?>
    <p>
        <?php _e('Извините, не найдено записей.'); ?>
    </p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>