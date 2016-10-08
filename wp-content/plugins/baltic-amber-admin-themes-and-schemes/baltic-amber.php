<?php
/*
Plugin Name: Baltic Amber Admin Themes and Schemes
Plugin URI: http://konstruktors.com/blog/wordpress-plugins
Description: Baltic Amber Admin Themes for your dashboard. To <strong>enable</strong> any of the new themes, go to <a href="profile.php">your profile</a>. Other configuration options are available under <a href="users.php?page=baltic-amber.php">Baltic Amber Colour Settings</a>.
Author: Kaspars Dambis
Version: 1.43
Author URI: http://konstruktors.com/blog/
*/

require_once("ColorChip.class.php");

class akBalticAmber {
	var $options_name = 'balticamber';
	var $plugin_path = '';
	
	var $ambercolours = array(
		'spring' => array(
					'title' => 'Spring',
					'theme' => 'C5ED70', 
					'editor' => 'C6D9E9'),
		'gaizins' => array(
					'title' => 'Gaiziņš',
					'theme' => '72B3E7', 
					'editor' => 'C6D9E9'),
		'jurmala' => array(
					'title' => 'Jūrmala',
					'theme' => 'EDE687', 
					'editor' => 'C6D9E9'),
		'liepaja' => array(
					'title' => 'Liepāja',
					'theme' => 'FF9900', 
					'editor' => 'C6D9E9'),
		'earlymorning' => array(
					'title' => 'Early Morning',
					'theme' => 'FEF101', 
					'editor' => 'C6D9E9'),
		'asphalt' => array(
					'title' => 'Asphalt',
					'theme' => 'A5B7BE', 
					'editor' => 'C6D9E9'),
		'caca' => array(
					'title' => 'Caca',
					'theme' => 'ECC6FF', 
					'editor' => 'C6D9E9'),
		'caca2' => array(
					'title' => 'Caca 2',
					'theme' => 'FF91FF', 
					'editor' => 'C6D9E9')
	);
	
	var $amberrules = array(
		// replacing colours in colours-random.css
		'000001' => array('comment' => 'lightest color', 
							'from' => 'theme'),
		'000002' => array('comment' => 'a bit darker for header border', 
							'from' => 'theme', 
							'shift' => -10),
		'000003' => array('comment' => 'editor background colour', 
							'from' => 'editor'),
		'000004' => array('comment' => 'dashboard top bar background', 
							'from' => 'theme', 
							'shift' => 0,
							'saturation' => -22),
	);


	var $freshcolours = array(
		'header' => 'DFED92',
		'highlight' => '993300',
		'mixture' => '990033'
	);
			
	var $freshrules = array(
		// key colours can be found in konstruktors.css -- these are replacement configurations
		'2583ad' => array('comment' => 'main navigation font color', 
							'from' => 'header', 
							'shift' => -50,
							'saturation' => 70),
		'cee1ef' => array('comment' => 'editor toolbar/buttons on background - a bit darker than header', 
							'from' => 'header', 
							'shift' => -10,
							'saturation' => 10),
		'c6d9e9' => array('comment' => 'main nav bottom border, a bit darker than header color', 
							'from' => 'header', 
							'shift' => -10,
							'saturation' => 0),
		'e4f2fd' => array('comment' => 'header background', 
							'from' => 'header'),
		'd55e21' => array('comment' => 'orange highlight', 
							'from' => 'highlight'),
		'd8531d' => array('comment' => 'a bit darker than higlight, main nav hover color', 
							'from' => 'highlight', 
							'shift' => -20,
							'saturation' => 0),							
		'd55e20' => array('comment' => 'active text', 
							'from' => 'header', 
							'shift' => 100,
							'saturation' => 90),							
		'80b5d0' => array('comment' => 'button border color', 
							'from' => 'header', 
							'shift' => -30,
							'saturation' => 0),
		'2683ae' => array('comment' => 'editors top right highlight color', 
							'from' => 'highlight', 
							'shift' => 0,
							'saturation' => 0),
		'328ab2' => array('comment' => 'button hover border color', 
							'from' => 'highlight', 
							'shift' => -20,
							'saturation' => 90),						
		'eaf3fa' => array('comment' => 'very light item row background', 
							'from' => 'header', 
							'shift' => 0,
							'saturation' => -7),
		'd6e8f5' => array('comment' => 'dashboard front header backgrounds', 
							'from' => 'header', 
							'shift' => -5,
							'saturation' => -20)
	);

	
	function akBalticAmber($type = false, $theme = false) {
		$this->plugin_path = get_bloginfo('wpurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/';
		
		if ($type == 'random' || $type == 'myscheme') {
			$this->replace_fresh($type);
		} elseif ($theme !== false) {
			$this->themeCSS($theme);
		} else {
			add_action('admin_init', array($this, 'ak_admin_colour'));
			add_action('admin_menu', array($this, 'add_settings_page'));
			add_action('admin_head', array($this, 'add_admin_css'), 90);
			add_action('admin_print_scripts', array($this, 'add_admin_head'));
			
			// also realign the tags, categories in add new post panel?
			$plugin_options = get_option($this->options_name);
			
			if ($plugin_options['realign-cats-tags'] == 'on') {
				add_action('admin_head', array($this, 'add_tag_category_columns'), 9);
			}
			if ($plugin_options['enable-maxwidth'] == 'on') {
				add_action('admin_head', array($this, 'add_maxwidth'), 99);
			}			
		}
	}

	function add_admin_head() {
		if ($_GET['page'] !== basename(__FILE__)) return;
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquerydimensions', $this->plugin_path . 'js/jquery.dimensions.js');
		wp_enqueue_script('jqueryfarbtastic', $this->plugin_path . 'js/farbtastic.js');
		wp_enqueue_script('balticamberjs', $this->plugin_path . 'js/baltic-amber-js.js');
	}

	function add_admin_css() {
		echo '<link type="text/css" rel="stylesheet" href="' . $this->plugin_path . 'baltic-amber-admin.css" />' . "\n";
	}	
	
	function add_ie6_css() {
		echo '<!--[if IE 6]><link type="text/css" rel="stylesheet" href="' . $this->plugin_path . 'amber-core-ie6.css" /><![endif]–>' . "\n";
	}	

	function add_maxwidth() {
		$plugin_options = get_option($this->options_name);
		$newmaxwidth = trim(strip_tags($plugin_options['maxwidth']));
		if (!empty($newmaxwidth)) {
			echo '<style type="text/css">.wrap, .updated, .error { max-width:' . $newmaxwidth . "!important; } * html #wpbody, * html body.minwidth { width:' . $newmaxwidth . '; }</style>\n";
		}
	}	
	
	function add_tag_category_columns() {
		echo '<link type="text/css" rel="stylesheet" href="' . $this->plugin_path . 'category-tag-columns.css" />' . "\n";
	}
	
	function add_settings_page() {
		add_users_page('Baltic Amber Colours', 'Baltic Amber Settings', 10, basename(__FILE__), array($this, 'print_admin_settings'));
	}
	
	
	function ak_admin_colour() {

		wp_admin_css_color(
			'konstruktors', 
			__('Random Color Scheme'), 
			$this->plugin_path . 'baltic-amber.php?schemetype=random', 
			array('#ffffff') 
		);
		
		wp_admin_css_color(
			'bacolourscheme', 
			__('My Color Scheme'), 
			$this->plugin_path . 'baltic-amber.php?schemetype=myscheme', 
			array('#ffffff') 
		);
		
		$themecount = count($this->ambercolours);
		foreach ($this->ambercolours as $theme => $themedetails) {
			wp_admin_css_color(
				$theme, 
				__($themedetails['title']), 
				$this->plugin_path . 'baltic-amber.php?ambertheme=' . $theme, 
				array('#'.$themedetails['theme'], '#'.$themedetails['editor']) 
			);
		}
	
		wp_admin_css_color(
			'randomcolour', 
			__('Random Color Theme'),
			$this->plugin_path . 'baltic-amber.php?ambertheme=random', 
			array('#333')
		);
	
		wp_admin_css_color(
			'mycustomtheme', 
			__('My Color Theme'), 
			$this->plugin_path . 'baltic-amber.php?ambertheme=custom', 
			array('#333') 
		);
				
	}
	
	function modify_base_colours($newbase, $type = 'scheme') {
	
		if ($type == 'scheme') {
			// replace default colour scheme
			$bc = $newbase;
			$to_replace = $this->freshrules;
			$replace_what = array_keys($this->freshrules);
		} elseif ($type == 'theme') {
			// replace one of the default amber themes
			$bc = $newbase;
			$to_replace = $this->amberrules;
			$replace_what = array_keys($this->amberrules);
		} else {
			return;
		}
		
		foreach($to_replace as $color => $rules) {	
			// get substitute colour to deal with
			$get_base = $bc[$rules['from']];
			// get darkness/lightness adjustmets
			$shift_amount = $rules['shift'];
			// get saturation adjustments
			$saturation = $rules['saturation'];
			// print $shift_amount . '+' . $saturation. '/';
			$turncolours = new ColorChip($get_base, null, null, CC_HEX);
			
			if (!empty($saturation)) { $turncolours->adjSaturation($saturation); }
			if (!empty($shift_amount)) { $turncolours->adjValue($shift_amount); }			

			$out[$color] = $turncolours->hex;
		}
		
		return $out;
	}
		

	function replace_fresh($type = 'random') {
		// replace -- replace with random colours by default
		
		$filename = dirname(__FILE__) . '/konstruktors.css';
		$plugin_options = get_option($this->options_name);
		
		if ($handle = fopen($filename, "r")) {
			$contents = fread($handle, filesize($filename));
			fclose($handle);
		} else { 
			return; 
		}
		
		foreach ($this->freshrules as $color => $rule) {
			$will_replace[] = '/' . $color . '/';
		}
		
		if ($type == 'myscheme') {
			$basecolours = $this->check_user_scheme_colours($plugin_options['colours']['fresh-header'], $plugin_options['colours']['fresh-highlight']);
		} else {
			$basecolours = $this->check_user_scheme_colours();
    	}
		
		$replace_with = array_values($this->modify_base_colours($basecolours, 'scheme'));	
		// print_r($replace_with);
		$out = preg_replace($will_replace, $replace_with, $contents);
		
		if ($type == 'random') {
			if (!empty($plugin_options['howoften'])) {
				// is random color and has refresh rate set
				$maxage = abs($plugin_options['howoften']);
			} else {
				// is random, but no refresh rate -- generate always new.
				$maxage = 0;
			}
		} else {
			$maxage = 600000;
		}
		
		header('Content-type: text/css');
		header('Pragma: private');
		header('Cache-Control: max-age=' . $maxage . ', must-revalidate');
		if ($maxage !== 0) {
			header("Expires: Mon, 26 Jul 2020 05:30:00 GMT");
		}
		print $out; 
	}


	function themeCSS($theme = 'random') {
		$filename = dirname(__FILE__) . '/colours-random.css';
		
		if ($handle = fopen($filename, "r")) {
			$contents = fread($handle, filesize($filename));
			fclose($handle);
		} else { 
			return; 
		}
		
		$find = preg_match_all('/[0-9]{6}/', $contents, $matches);
		$foundplaces = array_values(array_flip(array_flip($matches[0])));
		foreach ($foundplaces as $foundplace) {
			$foundpatterns[] = '/' . $foundplace . '/';
		}
	
		// get custom theme settings
		$plugin_options = get_option($this->options_name);
		
		if ($theme == 'custom') {
			$newbase = $this->check_user_theme_colours($plugin_options['colours']['basecolour'], $plugin_options['colours']['editorcolour']);
			$colours = array_values($this->modify_base_colours($newbase, 'theme'));
		} elseif ($theme == 'random') {
			$newbase = $this->check_user_theme_colours();
			$colours = array_values($this->modify_base_colours($newbase, 'theme'));
		} else {
			$colours = array_values($this->modify_base_colours($this->ambercolours[$theme], 'theme'));	
		}
		
		$out = preg_replace($foundpatterns, $colours, $contents); 
		
		if ($theme == 'random') {
			if (!empty($plugin_options['howoften'])) {
				// is random color and has refresh rate set
				$maxage = abs($plugin_options['howoften']);
			} else {
				// is random, but no refresh rate -- generate always new.
				$maxage = 0;
			}
		} else {
			$maxage = 600000;
		}
		
		header('Content-type: text/css');
		header('Pragma: private');
		header('Cache-Control: max-age=' . $maxage . ', must-revalidate');
		if ($maxage !== 0) {
			header("Expires: Mon, 26 Jul 2020 05:30:00 GMT");
		}
		print $out; 
	}	


	function check_user_theme_colours($basecolour = '', $editorcolour = '') {
	
		if (strlen($editorcolour) !== 6 || strlen($basecolour) !== 6) {
			if (strlen($basecolour) !== 6) {
				$turncolours = new ColorChip($this->ak_rand_hex_full(), null, null, CC_HEX);
				// set saturation and value so that it is doesn't hurt eyes
				$turncolours->setHsv($turncolours->h, 30, 90);
			} else {
				$turncolours = new ColorChip($basecolour, null, null, CC_HEX);
				// set saturation and value so that it is doesn't hurt eyes
			}
			
			// prepare a complimentary color
			$complimentary = $turncolours->getComplementary();
			$complimentary->setHsv($complimentary->h, 10, 100);
			$complimentary->adjValue(-20);

			$colours['theme'] = $turncolours->hex;
			$colours['editor'] = $complimentary->hex;
		} else {
			$colours['theme'] = $basecolour;
			$colours['editor'] = $editorcolour;
		}
		
		return $colours; 		
	}
	

	function check_user_scheme_colours($header = '', $highligh = '') {
		
		$randomcolor = $this->ak_rand_hex_full();
		$saveheader = $header;
		$savehighligh = $highligh;
		
		if (strlen($header) !== 6 || strlen($highligh) !== 6) {
			if (strlen($header) !== 6) {
				$turncolours = new ColorChip($randomcolor, null, null, CC_HEX);
				$turncolours->setHsv($turncolours->h, 100, 80);
				$makeheader = $turncolours->clonenew();
				$makeheader->setHsv($turncolours->h, 15, 95);
				$header = $makeheader->hex;
			} else {
				$turncolours = new ColorChip($header, null, null, CC_HEX);
				$header = $turncolours->hex;
			}
			
			if (strlen($highligh) !== 6) {
				$get_comp = $turncolours->getComplementary();
				$get_comp->setHsv($get_comp->h, 100, 80);
				$highligh = $get_comp->hex;
			} else {
				$fromhiglight = new ColorChip($highligh, null, null, CC_HEX);
				$highligh = $fromhiglight->hex;
				if (strlen($saveheader) !== 6) {
					$get_header = $fromhiglight->getComplementary();
					$get_header->setHsv($get_header->h, 20, 93);
					$header = $get_header->hex;
				}
			}
			
			$turncolours->adjHue(-120);
			$turncolours->setHsv($turncolours->h, 100, 80);
			$mixture = $turncolours->hex; 

			$colours = array(
				'header' => $header,
				'highlight' => $highligh,
				'mixture' => $mixture
			);
			
		} else {
			$turncolours = new ColorChip($header, null, null, CC_HEX);
			$turncolours->adjHue(-120);
			$mixture = $turncolours->hex;
			
			$colours = array(
				'header' => $header,
				'highlight' => $highligh,
				'mixture' => $mixture
			);		
		}
		
		
		
		return $colours; 		
	}
	
	
	function print_admin_settings() {
		global $_wp_admin_css_colors;
		
		if($_POST['ba_options_submitted'] == 'y') {
			if (is_array($_POST['ba']['colours'])) {
				foreach($_POST['ba']['colours'] as $key => $value) {
					// strip non-alpha
					$_POST['ba']['colours'][$key] = preg_replace('/[^a-zA-Z0-9s]/', '', $value);
				}
			}
			update_option($this->options_name, $_POST['ba']);
			$ifupdated = '<div id="message" class="updated fade"><p><strong>' . __('Options saved.') . '</strong></p></div>';
		}
		
		$plugin_options = get_option($this->options_name);
		
		// for those who are updating from 1.1
		if (!is_array($plugin_options['colours'])) {
			$plugin_options['colours']['fresh-header'] = $plugin_options['fresh-header'];
			$plugin_options['colours']['fresh-highlight'] = $plugin_options['fresh-highlight'];
			$plugin_options['colours']['basecolour'] = $plugin_options['basecolour'];
			$plugin_options['colours']['editorcolour'] = $plugin_options['editorcolour'];
			
			$ifupdated = '<div id="message" class="updated fade"><p><strong>' . __('Plugin was successfully updated from version 1.1') . '</strong></p></div>';
		}
		
		if (!empty($_wp_admin_css_colors) && function_exists('get_user_option')) { 
			// get current theme used by the author
			$current_color = get_user_option('admin_color');
			$color_name = $_wp_admin_css_colors[$current_color]->name;
			$themeinuse = '<p class="update ba-themeinuse">' . __('Theme currently in use:') . ' <strong>' . $color_name . '</strong> &mdash; <a href="profile.php">' . __('change') . '</a></p>';
		} else {
			$themeinuse = false;
		}
				
		$out = $ifupdated 
			. '<div class="wrap ba-settings"><form method="post" action="' . str_replace('%7E', '~', $_SERVER['REQUEST_URI']). '">' 
			. wp_nonce_field('update-options')
			. '<h2>' . __('Baltic Amber Colour Theme Settings') . '</h2>';
	 	
		$out .= $this->plugin_credits() 
			. '<div class="introduction">' 
			. $themeinuse
			. '<p>' . __("<strong>Important:</strong> to see the changes, don't forget to hit <code>Refresh</code> or press <code>F5</code> after you have saved the new settings.")
			. ' ' . __('Good places to find hexdecimal colour values are <a href="http://colormixers.com/mixers/cmr/">ColorMixers</a> and <a href="http://www.colourlovers.com/blog/2007/06/30/ultimate-html-color-hex-code-list/">colourlovers.com</a>.')
			. '</p></div>';
		
		$out .= '<div class="bb-tbase"><div id="picker" style="float:right; "></div>';
		
		$out .= '<div class="bb-tbase ba-cat-tag-option"><h3>' . __('Interface Element Realignment') . '</h3>'
			. $this->make_realign_options($plugin_options)
			. $this->make_checked_inputfield('Set maximum content width', 'maxwidth', 'Type of values accepted: <code>70em</code>, <code>100%</code>, <code>80%</code>, <code>none</code>', $plugin_options)
			. '</div>';

		$out .= '<div class="ba-scheme-options"><h3>' . __('My Colour <strong>Scheme</strong>') . '</h3>'
			. '<div><p>' . __('Changes only the colour scheme leaving the layout untouched.') . '</p>'
			. '<p>' . __('Select the hex colour values') . ':</p>'
			. $this->make_colorfield('Header', 'fresh-header', 'for the header; very light colour shade suggested', $plugin_options)
			. $this->make_colorfield('Highlight', 'fresh-highlight', 'for the frontpage highlight; dark shade suggested', $plugin_options)
			. '</div></div>';
		
		$out .= '<div class="ba-theme-options"><h3>' . __('My Colour <strong>Theme</strong>') . '</h3>'
			. '<div><p>' . __('These colours will be applied together with a few layout modifications only if you choose <strong>My Colour Theme</strong> in <a href="profile.php">Your Profile</a>. ') . '</p>'
			. '<p>' . __('Select the hex colour values') . ':</p>'
			. $this->make_colorfield('Base colour', 'basecolour', 'for the background of the main navigation bar', $plugin_options)
			. $this->make_colorfield('Editor colour', 'editorcolour', 'for the background of the compose screen', $plugin_options)
			. '</div></div>';
	
		$out .= '<div class="ba-random-options"><h3>' . __('<strong>Random Colour</strong> Settings') . '</h3>'
			. '<div><p>' . __('This will be used only if you choose <strong>Random Colour Theme</strong> or <strong>Random Color Scheme</strong> as your Admin Color Scheme in <a href="profile.php">Your Profile</a>.') . '</p>'
			. $this->make_simple_inputfield('How often to choose a new random colour?', 'howoften', 'in seconds, for example 1min. = 60sec., 1h = 3600sec.', $plugin_options) 
			. '</div></div>';
					
		$out .= $this->make_submit_button() 
			. '<input type="hidden" name="ba_options_submitted" value="y"></form>';
			
		$out .= '</div></div>';
		
		print $out;
		
	}
	
	function make_submit_button() {
		return '<p class="submit"><input type="submit" name="Submit" value="' . __('Save') . '" /></p>';
	}

	function make_colorfield($label, $fieldname, $tip = false, $options = array(), $maxlength = 7) {
		if ($tip) $tip = '<small>(' . __($tip) . ')</small>';
		if (!empty($options['colours'][$fieldname])) { 
			$options['colours'][$fieldname] = '#' . $options['colours'][$fieldname];
		}
		
		
		$out = '<div class="ba-colourinput"><label>' . __($label) . ': ' 
			. '<input value="' . $options['colours'][$fieldname] . '" type="text" name="ba[colours][' . $fieldname . ']" maxlength="' . $maxlength . '" size="7" class="colorwell" /></label> '
			. $tip . '</div>';

		return $out;
	}

		function make_simple_inputfield($label, $fieldname, $tip = false, $options = array()) {
			if ($tip) $tip = '<small>(' . __($tip) . ')</small>';
			
			$out = '<div><label>' . __($label) . ': ' 
				. '<input type="text" name="ba[' . $fieldname . ']" value="'. $options[$fieldname] .'" size="8" /></label> ' 
				. $tip . '</div>';
	
			return $out;
		}
		
		function make_checked_inputfield($label, $fieldname, $tip = false, $options = array()) {
			if ($tip) $tip = '<small>(' . __($tip) . ')</small>';
			$doenable = $options['enable-' . $fieldname];
		
			if (!empty($doenable)) {
				$value = 1; $checked = 'checked="checked"';
			} else {
				$value = 0; $checked = '';
			}			
			$out = '<div><label>'
				. '<input type="checkbox" id="option-' . $fieldname . '" name="ba[enable-' . $fieldname . ']" '. $checked .' /> '
				. __($label) . ': ' 
				. '</label><input type="text" name="ba[' . $fieldname . ']" value="'. $options[$fieldname] .'" size="8" /> ' 
				. $tip . '</div>';
	
			return $out;
		}		
	
	function make_realign_options($options = array()) {
		$realign = $options['realign-cats-tags'];
		
		if (!empty($realign)) {
			$value = 1; $checked = 'checked="checked"';
		} else {
			$value = 0; $checked = '';
		}
		
		$out = '<div><input type="checkbox" id="realign-cats-tags" name="ba[realign-cats-tags]" '. $checked .' /> ' 
			. '<label for="realign-cats-tags">' . __('Realign <em>Category</em> and <em>Tag</em> selection as columns in the <em>New Post</em> panel.') . '</label></div>';
			
		return $out;
	}		
	
	function plugin_credits() {
		$paypal = ' <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=kaspars%40konstruktors%2ecom&item_name=Baltic%20Amber%20Themes%20WordPress%20Plugin&no_shipping=1&no_note=1&tax=0&currency_code=EUR&lc=LV&bn=PP%2dDonationsBF&charset=UTF%2d8"><img src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" alt="PayPal - The safer, easier way to pay online!"></a><img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">';

		$out = '<div class="ba-credits">' 
			. '<div class="ba-developed"><p>' . __('Plugin developed by') . ' <a href="http://konstruktors.com/blog">Kaspars</a>. '
			. __('You can find my blog at <a href="http://konstruktors.com/blog/">konstruktors.com/blog</a>.</p>')
			. '<p>' . __('If you find this plugin useful, show your support and ') 
			.  $paypal . ' Thank you!</p></div></div>';
		
		return $out;
	}

	function ak_rand_hex_full($number = 1, $lowerlimit = 0, $upperlimit = 255) {
		// taken from http://php.net/dechex
	    if ($number > 1) {
	    	for ($count = 0; $count < $number; $count++) { 	
		    	$red = dechex(rand($lowerlimit, $upperlimit));
		    	$green = dechex(rand($lowerlimit, $upperlimit));
		    	$blue =  dechex(rand($lowerlimit, $upperlimit));
				$out[$count] = $red . $green . $blue;
			}
		} else {
		    	$red = dechex(rand($lowerlimit, $upperlimit));
		    	$green = dechex(rand($lowerlimit, $upperlimit));
		    	$blue =  dechex(rand($lowerlimit, $upperlimit));
				$out = $red . $green . $blue;
		}
		
		return $out;
	}

}


if (defined('ABSPATH')) require_once(ABSPATH . 'wp-config.php');
	else require_once(dirname(__FILE__) . "/../../../wp-config.php");

if (isset($_GET['schemetype'])) {
	new akBalticAmber(stripslashes($_GET['schemetype']), false);
} elseif (isset($_GET['ambertheme'])) {
	new akBalticAmber(false, stripslashes($_GET['ambertheme']));
} else {
	new akBalticAmber();
}

?>
