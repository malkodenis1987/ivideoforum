<?php
/**
 * Template Name: Мой кабинет - Вход
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
if ( (isset($_GET['action'])) && ($_GET['action'] === 'approving') ) {
    update_user_meta($_GET['user_id'], 'approved', '1');
}
session_start();
get_header(); ?>

<div id="content">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="entry">
        <h3 class="entrytitle" id="post-<?php the_ID(); ?>"><?php the_title(); ?></h3>
        <div class="entrymeta-single"></div>
        <div id="subpages">
            <form id="registration" action="<?php echo get_bloginfo('template_url'); ?>/includes/handlers/user-login.php" method="post" class="ajax">
                <input type="hidden" name="return_url" value="<?php the_permalink(); ?>" />
                <?php if (isset($_GET['success']) && $_GET['success'] === '1') { ?>
                    <div class="success">
                        <p>Вы удачно вошли!</p>
                    </div>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){
                            setTimeout(function(){
                                window.location.href = "<?php echo bloginfo('url'); ?>";
                            }, 2000);
                        });
                    </script>
                <?php } ?>
                <?php if ( (isset($_GET['action'])) && ($_GET['action'] === 'approving') ) { ?>
                    <div class="success">
                        <p>Ваш аккаунт активирован!</p>
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
                        <div class="row">
                            <label for="name">Email пользователя</label>
                            <input type="text" id="name" name="name" value="<?php echo $_SESSION['submitted_data']['name']; ?>" />
                        </div>
                        <div class="row">
                            <label for="pass">Пароль</label>
                            <input type="password" id="pass" name="pass" />
                            <p><a class="forgot-pass" href="<?php echo get_permalink(get_page_id('forgot-password')); ?>">Забыли пароль?</a></p>
                        </div>
                        <div class="row">
                            <button type="submit">Войти</button>
                            <p>Еще не зарегистрированы? <a href="<?php echo get_permalink(get_page_id('registration')); ?>">Зарегистрироваться.</a></p>
                        </div>
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