<?php
/*
Plugin Name: Iodized_Salt
Plugin URI: http://blog.portal.khakrov.ua/
Description: Йодированная соль в печенье - примешивание IP клиента к хешам куков. Продотвращает использование ворованных куков с другого IP-адреса.
Author: Yuri 'Bela' Belotitski
Version: 1.0 @ 21.05.2008
Author URI: http://www.portal.khakrov.ua/
*/

add_filter('salt','iodized_salt');
function iodized_salt($key) {
	return md5($key.$_SERVER["REMOTE_ADDR"]);
}


?>