<?php
function wppc_getTemplate($tpl_name='template',$tpl_data=array())
{
	$template_file = WP_PLUGIN_DIR . '/wp-photocontest/skins/'.CONTESTS_SKIN.'/'.$tpl_name.'.tpl';
	if (file_exists($template_file))
	{
		ob_start();
		include($template_file);
		$template_content = ob_get_clean();
	}
	else
	{
		return("<pre>Missing template file. Please read the readme file!</pre><br><br>[WP-PHOTOCONTEST CONTENT]");
	}
	return $template_content;
}

function wppc_file_upload_error_message($error_code) {
    switch ($error_code) { 
        case UPLOAD_ERR_INI_SIZE: 
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; 
        case UPLOAD_ERR_FORM_SIZE: 
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; 
        case UPLOAD_ERR_PARTIAL: 
            return 'The uploaded file was only partially uploaded'; 
        case UPLOAD_ERR_NO_FILE: 
            return 'No file was uploaded'; 
        case UPLOAD_ERR_NO_TMP_DIR: 
            return 'Missing a temporary folder'; 
        case UPLOAD_ERR_CANT_WRITE: 
            return 'Failed to write file to disk'; 
        case UPLOAD_ERR_EXTENSION: 
            return 'File upload stopped by extension'; 
        default: 
            return 'Unknown upload error'; 
    } 
} 


function wppc_printDatabaseError($wpdb=false)
{
	if (!$wpdb)
	{
		global $wpdb;
	}
	$wpdb->show_errors();
	ob_start();
	$wpdb->print_error();
	$db_error = ob_get_contents();
	ob_end_clean();	
	$wpdb->hide_errors();
	
	$db_error = str_replace("<code>","<br/><code>",$db_error);

	return $db_error;
}

function wppc_checkAlphaNum($val=-1,$alt_val=NULL)
{

	if (ctype_alnum($val) )
	{
		return (string) $val;
	}
	
	return (string) $alt_val;
}

function wppc_checkInteger($val=-1,$alt_val=NULL)
{
	if ( (is_numeric($val)) &&($val>0) )
	{
		return (int) $val;
	}
	
	return (int) $alt_val;

}

function wppc_checkOptions($val='',$check_array=array(),$alt_val=NULL)
{
	if (is_array($check_array))
	{
		if (in_array($val,$check_array))
		{
			return (string) $val;
		}
	}
	
	return (string) $alt_val;
	
}

function wppc_checkValidDate($val,$alt_val=NULL)
{
	if ($val)
	{
		list ($year,$month,$day) = explode("-",$val);
		if (checkdate($month,$day,$year))
		{
			return (string) $val;
		}	
	}
	return (string) $alt_val;	
}

function wppc_checkString($val='',$check_array=array(),$alt_val=NULL)
{
	if (is_array($check_array))
	{
		$val = wp_kses($val, $check_array);
	}
	else
	{
		$val = wp_kses($val,array()); 
	}
	
	if ($val)
	{
		return (string) $val;
	}
	
	return (string) $alt_val;
	
}

function wppc_initRand ()
{
    static $randCalled = FALSE;
    if (!$randCalled)
    {
        srand((double) microtime() * 1000000);
        $randCalled = TRUE;
    }
}

function wppc_randNum ($low, $high)
{
    wppc_initRand();
    $rNum = rand($low, $high);
    return $rNum;
}

function wppc_strNormalizeName ($contest_name) {
	$result=$contest_name;
	$_charToRemove = array('[', ']', '"', '(', ')', '?', '!', '\'');
	$_charToReplace =  array(  ' ',':','\.', ',','&','%','£','=','à','è','é','ì','ò','ù','À','Á','Â','Ä','Å','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','à','á','â','ã','ä','å','è','é','ê','ë','ì','í','î','ï','ò','ó','ô','õ','ö','ù','ú','û','ü');
	$_charReplacement =  array('_', '',  '',  '','_','_','_','_','a','e','e','i','o','u','a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','a','a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u');
	
	if (function_exists('mb_ereg_replace'))
	{
		// first we remove special chars
		foreach ($_charToRemove as $key => $value)
		{
			$result = mb_ereg_replace( preg_quote($_charToRemove[$key]), ''  ,$result);
		}
		//than we replace avoid chars for a filecontest_name.
		foreach ($_charToReplace as $key => $value)
		{
			$result = mb_ereg_replace( $_charToReplace[$key], $_charReplacement[$key]  ,$result);
		}
		
	}
	else
	{
		foreach ($_charToRemove as $key => $value)
		{
			$result = preg_replace("/".preg_quote($_charToRemove[$key])."/", ''  ,$result);
		}
		//than we replace avoid chars for a filecontest_name.
		foreach ($_charToReplace as $key => $value)
		{
			$result = preg_replace("/".$_charToReplace[$key]."/", $_charReplacement[$key]  ,$result);
		}
	}	
	
	return strtolower($result);
}

function wppc_isRightDir ($contest_name) {
	$_avoidDirName = array('lib', 'calendar.js', 'calendar-it.js', 'photocontest.php', 'photocontest-manager.php', 'skins', CONTESTS_PATH);
 
	foreach ($_avoidDirName as $k => $v)
	{
		if (preg_match("/^".$_avoidDirName[$k]."$/", $contest_name))
			return 0;
	}
	return 1;
}
		
function wppc_calDate ($date) {
	$date = str_replace(" 00:00:00","",$date);
	return $date; 
}

function wppc_niceDate ($date) {
	$date = str_replace(" 00:00:00","",$date);
	list($year,$month,$day) = explode("-",$date);
	return $day."/".$month."/".$year; 
}

	
function wppc_niceDateTime ($date,$method='both') {
	$date_array = explode(" ",$date);
	$date = $date_array[0];
	$time = $date_array[1];
	list($year,$month,$day) = explode("-",$date);
	if ($method == 'time')
	{
		return $time; 
	}
	elseif ($method == 'date')
	{
		return $day."/".$month."/".$year;
	}
	else
	{
		return $day."/".$month."/".$year. " ".$time; 
	}
}

/**
* sort a multi Array (on a given key)
 *
 * ---- no long desc ----
 *
 * @access		public
 * @author		Frank van der Stad
 * @copyright	vanderStad.nl 2006
 * @param array $array 		the array to sort
 * @param string $key_sort 	key to use to sort
 * @param string $dir_sort 	sorting ascending of descending				
 * @return array $target
*/				 
function wppc_multiArraySort($array, $key_sort, $dir_sort="DESC") { // start function
	$n = 0;
	$key_sorta = explode(",", $key_sort);
	$keys = array_keys($array[0]);
	// sets the $key_sort vars to the first
	for($m=0; $m < count($key_sorta); $m++){
			$nkeys[$m] = trim($key_sorta[$m]);
	}
	$n += count($key_sorta);    // counter used inside loop
	// this loop is used for gathering the rest of the
	// key's up and putting them into the $nkeys array
	for($i=0; $i < count($keys); $i++){ // start loop
			// quick check to see if key is already used.
			if(!in_array($keys[$i], $key_sorta)){
					// set the key into $nkeys array
					$nkeys[$n] = $keys[$i];
					// add 1 to the internal counter
					$n += "1";
			} // end if check
	} // end loop

	// this loop is used to group the first array [$array]
	// into it's usual clumps
	for($u=0;$u<count($array); $u++){ // start loop #1
			// set array into var, for easier access.
			$arr = $array[$u];
			// this loop is used for setting all the new keys
			// and values into the new order
			for($s=0; $s<count($nkeys); $s++){
					// set key from $nkeys into $k to be passed into multidimensional array
					$k = $nkeys[$s];
					// sets up new multidimensional array with new key ordering
					$output[$u][$k] = $array[$u][$k];
			} // end loop #2
	} // end loop #1
	if ($dir_sort == "ASC") {
			sort($output);// sort
	} else {
			rsort($output);// sort
	}
	return $output;// return sorted array
} // end function


function photocontest_scripts_and_styles() {
	/*
	$myScriptUrl = WP_PLUGIN_URL . '/wp-photocontest/js/swfobject2.js';
	$myScriptFile = WP_PLUGIN_DIR . '/wp-photocontest/js/swfobject2.js';
	if ( file_exists($myScriptFile) ) {
		wp_register_script('swfobject-script', $myScriptUrl);
		wp_enqueue_script( 'swfobject-script');	
	}
	*/
	
	$myScriptUrl = WP_PLUGIN_URL . '/wp-photocontest/js/photocontest-admin.js';
	$myScriptFile = WP_PLUGIN_DIR . '/wp-photocontest/js/photocontest-admin.js';
	if ( file_exists($myScriptFile) ) {
		wp_register_script('wp-photocontest-admin-script', $myScriptUrl);
		wp_enqueue_script( 'wp-photocontest-admin-script');	
	}
	
	$myScriptUrl = WP_PLUGIN_URL . '/wp-photocontest/js/calendar.js';
	$myScriptFile = WP_PLUGIN_DIR . '/wp-photocontest/js/calendar.js';
	if ( file_exists($myScriptFile) ) {
		wp_register_script('wp-photocontest-calendar-script', $myScriptUrl);
		wp_enqueue_script( 'wp-photocontest-calendar-script');	
	}
	
	$myScriptUrl = WP_PLUGIN_URL . '/wp-photocontest/js/calendar-eng.js';
	$myScriptFile = WP_PLUGIN_DIR . '/wp-photocontest/js/calendar-eng.js';
	if ( file_exists($myScriptFile) ) {
		wp_register_script('wp-photocontest-calendar-eng-script', $myScriptUrl);
		wp_enqueue_script( 'wp-photocontest-calendar-eng-script');	
	}
	
	$myStyleUrl = WP_PLUGIN_URL . '/wp-photocontest/wp-photocontest.css';
	$myStyleFile = WP_PLUGIN_DIR . '/wp-photocontest/wp-photocontest.css';
	if ( file_exists($myStyleFile) ) {
		wp_register_style('wp-photocontest-style', $myStyleUrl);
		wp_enqueue_style( 'wp-photocontest-style');	
	}
}

function photocontest_sub_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('PhotoContest', 'wp-photocontest'), __('PhotoContest', 'wp-photocontest'), 'manage_photocontests', 'wp-photocontest/photocontest-manager.php', '', plugins_url('wp-photocontest/images/photocontest.png'));
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('wp-photocontest/photocontest-manager.php', __('Manage PhotoContest', 'wp-photocontest'), __('View PhotoContest', 'wp-photocontest'), 'manage_photocontest', 'wp-photocontest/photocontest-manager.php');
		add_submenu_page('wp-photocontest/photocontest-manager.php', __('Add PhotoContest', 'wp-photocontest'), __('Add PhotoContest', 'wp-photocontest'), 'manage_photocontest', 'wp-photocontest/photocontest-add.php');		
		add_submenu_page('wp-photocontest/photocontest-manager.php', __('Update settings', 'wp-photocontest'), __('Update settings', 'wp-photocontest'), 'manage_photocontest', 'wp-photocontest/photocontest-settings.php');		
	}
}

// layout for admin page
function photocontest_menu()
{
    global $wpdb;
    include WP_PLUGIN_DIR.'/photocontest-manager.php';
}

function wppc_level_reduction( $max, $item ) {
	if ( preg_match( '/^level_(10|[0-9])$/i', $item, $matches ) ) {
		$level = intval( $matches[1] );
		return max( $max, $level );
	} else {
		return $max;
	}
}

?>
