<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе
 * установки. Необязательно использовать веб-интерфейс, можно
 * скопировать файл в "wp-config.php" и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define('DB_NAME', 'gather');

/** Имя пользователя MySQL */
define('DB_USER', 'root');

/** Пароль к базе данных MySQL */
define('DB_PASSWORD', 'apollon13');

/** Имя сервера MySQL */
define('DB_HOST', 'localhost');

/** Кодировка базы данных для создания таблиц. */
define('DB_CHARSET', 'utf8mb4');

/** Схема сопоставления. Не меняйте, если не уверены. */
define('DB_COLLATE', '');

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу.
 * Можно сгенерировать их с помощью {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными. Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'R7!b+Y;a?{,r(Jm&Xbs*,<;Je)r~AWTv,+gF8CE?>|yyF|p0]e7f@HGd<6dW[S)5');
define('SECURE_AUTH_KEY',  'moP!?Du/<st/;qavk-yIuM63m|j-I@R=%YtOy<)J*IAi#~LW(j@D&Mu$y3|uQsT+');
define('LOGGED_IN_KEY',    '@j/>e4ZJjs<6s+$P;L+iOH`nXq]H?}/.N=n45_hd2UUQ JD;sFIlyhu|g:!my |w');
define('NONCE_KEY',        'J#FrLUX{bJ@7-gDDUc>,y-x_FCD{:@A^-E*2iqE)?h(AZ.,zpNImW=duz~_r;J;;');
define('AUTH_SALT',        '!U~Cu^G^|Qdi`y$Y!4g+v>tg|zRk:Na1]p,p2$F*8r}3fYaz|`/i[O|h3l{pi;#+');
define('SECURE_AUTH_SALT', '(82$9*z;AQ>tvneswUkDc:|uDm3+9={`J~hXLf#o7, x$q/e,Lb5V$wp#xj4BHNd');
define('LOGGED_IN_SALT',   'pi+<0}tR2)XTWtj^-N_+dZu3t_q:s0H><Ef RmIZWMB(f-FF*M.l@Z_E6[S/i_kH');
define('NONCE_SALT',       '9[c9l!Br&V9B.h</Fsfdc7-:wQ$*a|59O$ON?rZA|bVW/6_X]@O,{7+6&V(o<@.t');

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix  = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 * 
 * Информацию о других отладочных константах можно найти в Кодексе.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Инициализирует переменные WordPress и подключает файлы. */
require_once(ABSPATH . 'wp-settings.php');
