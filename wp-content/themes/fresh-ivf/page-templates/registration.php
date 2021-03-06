<?php
/**
 * Template Name: Мой кабинет - Регистрация
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

<div id="content">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="entry">
        <h3 class="entrytitle" id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>
        <div class="entrymeta-single"></div>
        <div id="subpages">
            <form id="registration" action="<?php echo get_bloginfo('template_url'); ?>/includes/handlers/user-registration.php" method="post" class="ajax">
                <input type="hidden" name="return_url" value="<?php the_permalink(); ?>" />
                <?php if (isset($_GET['success']) && $_GET['success'] === '1') { ?>
                    <div class="success">
                        <p>Вы удачно зарегистрировались!</p>
                    </div>
                    <p>Для подтверждения аккаунта, перейдите по ссылке, отправленной на указаный email. Теперь вы можете <a href="<?php echo get_permalink(get_page_id('login')); ?>">войти</a> на сайт.</p>
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
                        <p>
                            <label for="f_name">Имя:<span class="required">*</span></label>
                            <input type="text" name="f_name" id="f_name" value="<?php echo $_SESSION['submitted_data']['f_name']; ?>" />
                        </p>
                        <p>
                            <label for="l_name">Фамилия:<span class="required">*</span></label>
                            <input type="text" name="l_name" id="l_name" value="<?php echo $_SESSION['submitted_data']['l_name']; ?>" />
                        </p>
                        <p>
                            <label for="email">Email:<span class="required">*</span></label>
                            <input type="text" name="email" id="email" value="<?php echo $_SESSION['submitted_data']['email']; ?>" />
                        </p>
                        <p>
                            <label for="pass1">Пароль:</label>
                            <input type="password" autocomplete="off" value="" size="16" id="pass1" name="pass1">
                        </p>
                        <p>
                            <label for="pass2">Подтвердите пароль:</label>
                            <input type="password" autocomplete="off" value="" size="16" id="pass2" name="pass2">
                        </p>
                        <p>
                            <button type="submit">Регистрация</button>
                        </p>
                    </div>
                <?php } ?>
            </form>
        </div>

    </div>

    <?php endwhile; else: ?>
    <p>
        <?php _e('Извините, не найдено записей.'); ?>
    </p>
    <?php endif; ?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>