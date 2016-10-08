<?php
/**
 Plugin Name: WP-SimpleViewer
 Plugin URI: http://www.simpleviewer.net/simpleviewer/support/wp-simpleviewer/
 Description: Create SimpleViewer galleries with WordPress.
 Author: SimpleViewer Inc.
 Version: 2.3.2.1
 Author URI: http://www.simpleviewer.net/
 Text Domain: simpleviewer
 */

/**
 * SimpleViewer Plugin Class
 */
class SimpleViewer {

	var $version = '2.3.2.1';

	/**
	 * Initalize the plugin by registering the hooks
	 */
	function __construct() {

		// Register hooks
		add_action('admin_init', array(&$this, 'add_settings'));
		add_action('admin_menu', array(&$this, 'add_menus'));
		add_action('admin_head', array(&$this, 'add_script_configs'));
		add_action('admin_enqueue_scripts', array(&$this, 'add_scripts_admin'));

		// Include js file
		add_action('wp_enqueue_scripts', array(&$this, 'include_scripts'));

		// Register shortcode
		add_shortcode('simpleviewer', array(&$this, 'shortcode_handler'));

		if (current_user_can('edit_pages') || current_user_can('edit_posts')) {
			// Add button to write post screen
			$this->add_buttons();

			// Add Quicktag
			global $wp_version;
			if (version_compare($wp_version, '3.3', '>=')) {
				add_action('admin_print_footer_scripts', array(&$this, 'add_quicktags'));
			} else {
				add_action('edit_form_advanced', array(&$this, 'add_quicktags'));
				add_action('edit_page_form', array(&$this, 'add_quicktags'));
			}

			// Use the save_post action to do something with the data entered
			add_action('save_post', array(&$this, 'save_postdata'));
		}
	}

	/**
	 * Add settings
	 *
	 * @return void
	 */
	function add_settings() {
		register_setting('simpleviewer', 'simpleviwer_options');
	}

	/**
	 * Register settings page
	 *
	 * @return void
	 */
	function add_menus() {
		add_menu_page('WP-SimpleViewer', 'SimpleViewer', 'edit_posts', 'sv_manage_gallery', array(&$this, 'edit_galleries_page'), plugins_url('img/favicon.png', __FILE__));
		add_submenu_page('sv_manage_gallery', 'WP-SimpleViewer - Manage Galleries', 'Manage Galleries', 'edit_posts', 'sv_manage_gallery', array(&$this, 'edit_galleries_page'));
		add_submenu_page('sv_manage_gallery', 'WP-SimpleViewer - Help', 'Help', 'edit_posts', 'sv_help', array(&$this, 'help_page'));
	}

	/**
	 * Display help page
	 *
	 * @return void
	 */
	function help_page() {
?>
		<div class="wrap">
			<h2><img src="<?php echo plugins_url('img/icon_trans_35x26.png', __FILE__); ?>" width="35" height="26" alt="logo" />&nbsp;WP-SimpleViewer - Help</h2>
			<p>
				<a href = "http://www.simpleviewer.net/simpleviewer/support/wp-simpleviewer/">Get support and view WP-SimpleViewer documentation.</a>
			</p>
		</div>
<?php
	}

	/**
	 * Add footer links. Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
	 *
	 * @return void
	 */
	function add_footer_links() {
		$plugin_data = get_plugin_data(__FILE__);
		printf('%1$s Plugin | Version %2$s | By %3$s<br>', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
	}

	/**
	 * Break browser cache of TinyMCE
	 *
	 * @param string tinymce version
	 * @return string tinymce version
	 */
	function tinymce_version($version) {
		return $version . '-sv' . $this->version;
	}

	/**
	 * Load custom TinyMCE plugin
	 *
	 * @param array plugins
	 * @return array plugins
	 */
	function add_tinymce_plugin($plugins) {
		$plugins['simpleviewer'] = plugins_url('tinymce/editor_plugin.js', __FILE__);
		return $plugins;
	}

	/**
	 * Add TinyMCE button
	 *
	 * @param array buttons
	 * @return array buttons
	 */
	function add_tinymce_button($buttons) {
		array_push($buttons, 'separator', 'simpleviewer');
		return $buttons;
	}

	/**
	 * Add button to write post screen
	 *
	 * @return
	 */
	function add_buttons() {
		if (!current_user_can('edit_pages') && !current_user_can('edit_posts')) {
			return;
		}
		if (get_user_option('rich_editing') === 'true') {
			add_filter('tiny_mce_version', array(&$this, 'tinymce_version'), 0);
			add_filter('mce_external_plugins', array(&$this, 'add_tinymce_plugin'), 0);
			add_filter('mce_buttons', array(&$this, 'add_tinymce_button'), 0);
		}
	}

	/**
	 * Add a button to the quicktag view
	 *
	 * @return void
	 */
	function add_quicktags() {
		global $current_screen;
		$current_screen_post_type = $current_screen->post_type;

		if ($current_screen_post_type === 'attachment') {
			return;
		}

		if ($current_screen_post_type === 'page' && !current_user_can('edit_pages')) {
			return;
		}

		if ($current_screen_post_type === 'post' && !current_user_can('edit_posts')) {
			return;
		}

		global $wp_version;
		if (version_compare($wp_version, '3.3', '>=')) {
?>
			<script type="text/javascript">
				// <![CDATA[
				function addGallery() {
					SV.Gallery.embed.apply(SV.Gallery);
					return false;
				}
				if (typeof QTags !== 'undefined') {
					QTags.addButton('sv_button', 'Add SimpleViewer Gallery', addGallery, null, null, 'Add a SimpleViewer Gallery to your <?php echo $current_screen_post_type; ?>');
				}
				// ]]>
			</script>
<?php
		} else {
			$button_html = '<input type="button" class="sv_button" onclick="SV.Gallery.embed.apply(SV.Gallery); return false;" title="Add a SimpleViewer Gallery to your ' . $current_screen_post_type . '" value="Add SimpleViewer Gallery" />';
?>
			<script type="text/javascript">
				// <![CDATA[
				(function() {
					if (typeof jQuery === 'undefined') {
						return;
					}
					jQuery(document).ready(function() {
						jQuery("#ed_toolbar").append('<?php echo $button_html; ?>');
					});
				}());
				// ]]>
			</script>
<?php
		}
	}

	/**
	 * Get reset values
	 *
	 * @return array reset values
	 */
	function get_reset_values() {
		$reset_values = array();

		$reset_values['title'] = 'SimpleViewer Gallery';
		$reset_values['useFlickr'] = 'false';
		$reset_values['flickrUserName'] = '';
		$reset_values['flickrTags'] = '';
		$reset_values['galleryStyle'] = 'COMPACT';
		$reset_values['thumbPosition'] = 'BOTTOM';
		$reset_values['frameWidth'] = '20';
		$reset_values['maxImageWidth'] = '800';
		$reset_values['maxImageHeight'] = '600';
		$reset_values['textColor'] = '0xffffff';
		$reset_values['frameColor'] = '0xffffff';
		$reset_values['showOpenButton'] = 'true';
		$reset_values['showFullscreenButton'] = 'true';
		$reset_values['thumbRows'] = '1';
		$reset_values['thumbColumns'] = '5';
		$reset_values['e_g_width'] = '100%';
		$reset_values['e_g_height'] = '600px';
		$reset_values['e_bgColor'] = '222222';
		$reset_values['e_useFlash'] = 'true';
		$reset_values['e_library'] = 'media';
		$reset_values['e_featuredImage'] = 'true';
		$reset_values['e_nextgenGalleryId'] = '';
		$reset_values['e_picasaUserId'] = '';
		$reset_values['e_picasaAlbumName'] = '';
		$reset_values['proOptions'] = '';

		return $reset_values;
	}

	/**
	 * Get default values
	 *
	 * @return array default values
	 */
	function get_default_values() {
		$default_values = $this->get_reset_values();
		$default_filename = plugin_dir_path(__FILE__) . 'default.xml';
		if (file_exists($default_filename)) {

			$dom_doc = new DOMDocument();
			$dom_doc->load($default_filename);

			$settings_tags = $dom_doc->getElementsByTagName('simpleviewergallery');
			$settings_tag = $settings_tags->item(0);

			if ($settings_tag->hasAttributes()) {
				$default_values = array();
				foreach ($settings_tag->attributes as $attribute) {
					$name = $attribute->nodeName;
					$value = $attribute->nodeValue;
					$default_values[$name] = $value;
				}
			}
		}
		return $default_values;
	}

	/**
	 * Get Pro Options
	 *
	 * @param simplexmlelement custom values
	 * @return string pro options
	 */
	function get_pro_options($custom_values) {
		$pro_options = '';
		foreach ($custom_values as $key=>$value) {
			switch (strtolower($key)) {
				case 'title':
				case 'useflickr':
				case 'flickrusername':
				case 'flickrtags':
				case 'gallerystyle':
				case 'thumbposition':
				case 'framewidth':
				case 'maximagewidth':
				case 'maximageheight':
				case 'textcolor':
				case 'framecolor':
				case 'showopenbutton':
				case 'showfullscreenbutton':
				case 'thumbrows':
				case 'thumbcolumns':
				case 'languagecode':
				case 'languagelist':
				case 'e_g_width':
				case 'e_g_height':
				case 'e_bgcolor':
				case 'e_useflash':
				case 'e_library':
				case 'e_featuredimage':
				case 'e_nextgengalleryid':
				case 'e_picasauserid':
				case 'e_picasaalbumname':
				case 'postid':
					break;
				default:
					$pro_options .= $key . '="' . $value . '"' . "\n";
					break;
			}
		}
		return $pro_options;
	}

	/**
	 * Get post id from XML File
	 *
	 * @param string gallery filename
	 * @return string post id
	 */
	function get_post_id($gallery_filename) {
		$post_id = 0;
		if (file_exists($gallery_filename)) {

			$dom_doc = new DOMDocument();
			$dom_doc->load($gallery_filename);

			$settings_tags = $dom_doc->getElementsByTagName('simpleviewergallery');
			$settings_tag = $settings_tags->item(0);

			$post_id = $settings_tag->hasAttribute('postID') ? $settings_tag->getAttribute('postID') : 0;
		}
		return $post_id;
	}

	/**
	 * Get the directory path where XML files are stored
	 *
	 * @return string gallery path
	 */
	function get_gallery_path() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/';
	}

	/**
	 * Get the list of XML files
	 *
	 * @param string gallery path
	 * @return array galleries
	 */
	function get_all_galleries($gallery_path) {
		$galleries = array();
		$handler = opendir($gallery_path);
		while ($file = readdir($handler)) {
			if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
				$galleries[] = $file;
			}
		}
		closedir($handler);
		return $galleries;
	}

	/**
	 * Sort galleries
	 *
	 * @param integer value gallery
	 * @param integer value gallery
	 * @return integer value gallery
	 */
	function sort_galleries($a, $b) {
		$a1 = intval(basename($a, '.xml'));
		$b1 = intval(basename($b, '.xml'));
		if ($a1 === $b1) {
			return 0;
		}
		return ($a1 > $b1) ? -1 : 1;
	}

	/**
	 * Return a properly formatted hex color string
	 *
	 * @access private
	 * @param string hex color string
	 * @param integer required length of hex color string in characters
	 * @return string properly formatted hex color string
	 */
	 function clean_hex($hex, $length = 6) {
		 $hex = strtolower($hex);
		 $hex = ltrim($hex, '#');
		 $hex = str_replace('0x', '', $hex);
		 return '0x' . str_pad(dechex(hexdec(substr(trim($hex), 0, $length))), $length, '0', STR_PAD_LEFT);
	 }

	/**
	 * Edit and save gallery
	 *
	 * @return void
	 */
	function edit_gallery($custom_values) {
		$gallery_path = $this->get_gallery_path();
		$gallery_id = $custom_values['sv-gallery-id'];
		$gallery_filename = $gallery_path . $gallery_id . '.xml';

		$post_id = $this->get_post_id($gallery_filename);

		$this->build_gallery($gallery_filename, $custom_values);
		$this->update_post_id($gallery_filename, $post_id);
	}

	/**
	* Build gallery
	*
	* @param string gallery filename
	* @return void
	*/
	function build_gallery($gallery_filename, $custom_values) {

		$dom_doc = new DOMDocument();
		$dom_doc->formatOutput = true;
		$settings_tag = $dom_doc->createElement('simpleviewergallery');

		$sv_values = array();
		$sv_values['title'] = trim(strip_tags(stripslashes($custom_values['title']), '<a><b><i><u><font><br><br />'));
		if ($custom_values['e_library'] === 'media' || $custom_values['e_library'] === 'nextgen' || $custom_values['e_library'] === 'picasa') {
			$sv_values['useFlickr'] = 'false';
			$sv_values['flickrUserName'] = '';
			$sv_values['flickrTags'] = '';
		}
		if ($custom_values['e_library'] === 'flickr') {
			$sv_values['useFlickr'] = 'true';
			$sv_values['flickrUserName'] = $custom_values['flickrUserName'];
			$sv_values['flickrTags'] = $custom_values['flickrTags'];
		}
		$sv_values['galleryStyle'] = $custom_values['galleryStyle'];
		$sv_values['thumbPosition'] = $custom_values['thumbPosition'];
		$sv_values['frameWidth'] = max(0, $custom_values['frameWidth']);
		$sv_values['maxImageWidth'] = max(0, $custom_values['maxImageWidth']);
		$sv_values['maxImageHeight'] = max(0, $custom_values['maxImageHeight']);
		$sv_values['textColor'] = $this->clean_hex($custom_values['textColor'], 6);
		$sv_values['frameColor'] = $this->clean_hex($custom_values['frameColor'], 6);
		$sv_values['showOpenButton'] = isset($custom_values['showOpenButton']) ? 'true' : 'false';
		$sv_values['showFullscreenButton'] = isset($custom_values['showFullscreenButton']) ? 'true' : 'false';
		$sv_values['thumbRows'] = max(0, $custom_values['thumbRows']);
		$sv_values['thumbColumns'] = max(0, $custom_values['thumbColumns']);
		$sv_values['e_g_width'] = $custom_values['e_g_width'];
		$sv_values['e_g_height'] = $custom_values['e_g_height'];
		$background_color = str_replace('0x', '', $this->clean_hex($custom_values['e_bgColor'], 6));
		if (isset($custom_values['background-transparent']) && $custom_values['background-transparent'] === 'true') {
			$background_color = 'transparent';
		}
		$sv_values['e_bgColor'] = $background_color;
		$sv_values['e_useFlash'] = isset($custom_values['e_useFlash']) ? 'true' : 'false';
		$sv_values['e_library'] = $custom_values['e_library'];
		$sv_values['e_featuredImage'] = '';
		if ($custom_values['e_library'] === 'media') {
			$sv_values['e_featuredImage'] = isset($custom_values['e_featuredImage']) ? 'true' : 'false';
		}
		$sv_values['e_nextgenGalleryId'] = $custom_values['e_library'] === 'nextgen' ? $custom_values['e_nextgenGalleryId'] : '';
		$sv_values['e_picasaUserId'] = $custom_values['e_library'] === 'picasa' ? $custom_values['e_picasaUserId'] : '';
		$sv_values['e_picasaAlbumName'] = $custom_values['e_library'] === 'picasa' ? $custom_values['e_picasaAlbumName'] : '';

		foreach ($sv_values as $key=>$value) {
			$settings_tag->setAttribute($key, $value);
		}

		$pro_options = explode("\n", $custom_values['proOptions']);
		foreach ($pro_options as $pro_option) {
			$attrs = explode('=', trim($pro_option));
			if (count($attrs) === 2) {
				$key = trim($attrs[0]);
				$key = str_replace(' ', '', $key);
				$value = trim(stripslashes($attrs[1]));
				$value = str_replace("'", '', $value);
				$value = str_replace('"', '', $value);
				$value = str_replace('”', '', $value);
				$value = str_replace('“', '', $value);
				$settings_tag->setAttribute($key, $value);
			}
		}

		$dom_doc->appendChild($settings_tag);
		$dom_doc->save($gallery_filename);
	}

	/**
	 * When the post is saved
	 *
	 * @param string post id
	 * @return
	 */
	function save_postdata($post_id) {

		if (isset($_POST['post_type']) && $_POST['post_type'] === 'page' && !current_user_can('edit_page', $post_id)) {
			return;
		}

		if (isset($_POST['post_type']) && $_POST['post_type'] === 'post' && !current_user_can('edit_post', $post_id)) {
			return;
		}

		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return;
		}

		$sv_term_id = get_post_meta($post_id, '_sv_term_id', true);
		if ($sv_term_id === '') {
			update_post_meta($post_id, '_sv_term_id', 'update');
			return;
		}

		$pattern = '/\\[simpleviewer gallery_id="([0-9]+)"\\]/';
		$post_record = get_post($post_id);
		$content = $post_record->post_content;
		$matches = array();

		preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

		if (count($matches) > 0) {
			for ($i = 0; $i < count($matches); $i++) {
				$gallery_filename = $this->get_gallery_path() . $matches[$i][1] . '.xml';
				if (file_exists($gallery_filename)) {
					$this->update_post_id($gallery_filename, $post_id);
				}
			}
		}
	}

	/**
	 * Update post id
	 *
	 * @param string gallery filename
	 * @param string post id
	 * @return void
	 */
	function update_post_id($gallery_filename, $post_id) {
		if (file_exists($gallery_filename)) {

			$dom_doc = new DOMDocument();
			$dom_doc->preserveWhiteSpace = false;
			$dom_doc->formatOutput = true;
			$dom_doc->load($gallery_filename);

			$settings_tags = $dom_doc->getElementsByTagName('simpleviewergallery');
			$settings_tag = $settings_tags->item(0);

			$settings_tag->setAttribute('postID', $post_id);
			$dom_doc->save($gallery_filename);
		}
	}

	/**
	 * Add script configs
	 *
	 * @return void
	 */
	function add_script_configs() {
		global $current_screen;
		$current_screen_post_type = $current_screen->post_type;
?>
		<script type="text/javascript" charset="utf-8">
			// <![CDATA[
			if (typeof SV !== 'undefined' && typeof SV.Gallery !== 'undefined') {
				var svPostType = '<?php echo $current_screen_post_type; ?>';
				SV.Gallery.configUrl = "<?php echo plugins_url('sv-config.php', __FILE__); ?>";
			}
			// ]]>
		</script>
<?php
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	function add_scripts_admin($hook) {
		wp_register_script('sv_script_generate', plugins_url('js/generate.js', __FILE__), array('jquery', 'thickbox'), $this->version);
		wp_enqueue_script('sv_script_generate');
		wp_enqueue_style('thickbox');
		$pattern = '/sv_manage_gallery/';
		if (!preg_match($pattern, $hook)) {
			return;
		}
		wp_register_script('sv_script_edit', plugins_url('js/edit.js', __FILE__), array('jquery'), $this->version);
		wp_enqueue_script('sv_script_edit');
		wp_register_style('sv_style_edit', plugins_url('css/edit.css', __FILE__), array(), $this->version);
		wp_enqueue_style('sv_style_edit');
	}

	/**
	 * Dipslay edit galleries page
	 *
	 * @return void
	 */
	function edit_galleries_page() {
?>
		<div class="wrap">
			<h2><img src="<?php echo plugins_url('img/icon_trans_35x26.png', __FILE__); ?>" width="35" height="26" alt="logo" />&nbsp;WP-SimpleViewer - Manage Galleries</h2>
<?php
			if (isset($_GET['sv-action']) && $_GET['sv-action'] !== '') {
				switch ($_GET['sv-action']) {
					case 'edit-gallery':
						$gallery_id = $_GET['sv-gallery-id'];
						$this->display_edit_gallery_form($gallery_id);
						break;
					case 'gallery-edited':
						if (!check_admin_referer('sv-edit', 'sv-edit-nonce')) {
							break;
						}
						$gallery_id = $_POST['sv-gallery-id'];
						$this->edit_gallery($_POST);
						echo '<div class="updated"><p>Gallery Id ' . $gallery_id . ' successfully edited.</p></div>';
						$this->display_gallery_table();
						break;
					case 'delete-gallery':
						$gallery_id = $_GET['sv-gallery-id'];
						$gallery_filename = $this->get_gallery_path() . $gallery_id . '.xml';
						if (file_exists($gallery_filename)) {
							unlink($gallery_filename);
							echo '<div class="updated"><p>Gallery Id ' . $gallery_id . ' successfully deleted.</p></div>';
						} else {
							echo '<div class="updated"><p>Gallery Id ' . $gallery_id . ' has already been deleted.</p></div>';
						}
						$this->display_gallery_table();
						break;
					case 'delete-all-data':
						$gallery_path = $this->get_gallery_path();
						$galleries = $this->get_all_galleries($gallery_path);
						$galleries_zero = count($galleries) === 0;
						$galleries_deleted = false;
						if (!$galleries_zero) {
							foreach ($galleries as $gallery) {
								$gallery_filename = $gallery_path . $gallery;
								if (file_exists($gallery_filename)) {
									unlink($gallery_filename);
								}
							}
							$galleries = $this->get_all_galleries($gallery_path);
							$galleries_deleted = count($galleries) === 0;
						}
						$options = get_option('simpleviwer_options', array());
						$options_zero = empty($options);
						$options_deleted = false;
						if (($galleries_zero || $galleries_deleted) && !$options_zero) {
							$options_deleted = delete_option('simpleviwer_options');
						}
						if ($galleries_zero && $options_zero) {
							echo '<div class="updated"><p>There are no galleries or options to be deleted.</p></div>';
						}
						if ($galleries_zero && !$options_zero && $options_deleted) {
							echo '<div class="updated"><p>There are no galleries to be deleted but all options have been deleted.</p></div>';
						}
						if ($galleries_zero && !$options_zero && !$options_deleted) {
							echo '<div class="updated"><p>There are no galleries to be deleted and all options cannot be deleted.</p></div>';
						}
						if (!$galleries_zero && $galleries_deleted && $options_zero) {
							echo '<div class="updated"><p>All galleries have been deleted but there are no options to be deleted.</p></div>';
						}
						if (!$galleries_zero && $galleries_deleted && !$options_zero && $options_deleted) {
							echo '<div class="updated"><p>All galleries and options have been deleted.</p></div>';
						}
						if (!$galleries_zero && $galleries_deleted && !$options_zero && !$options_deleted) {
							echo '<div class="updated"><p>All galleries have been deleted but all options cannot be deleted.</p></div>';
						}
						if (!$galleries_zero && !$galleries_deleted) {
							echo '<div class="updated"><p>All galleries cannot be deleted.</p></div>';
						}
						$this->display_gallery_table();
						break;
					case 'set-defaults':
						$this->display_set_defaults_form();
						break;
					case 'defaults-set':
						if (!check_admin_referer('sv-set', 'sv-set-nonce')) {
							break;
						}
						$default_filename = plugin_dir_path(__FILE__) . 'default.xml';
						$this->build_gallery($default_filename, $_POST);
						echo '<div class="updated"><p>Default values of gallery configuration options successfully set.</p></div>';
						$this->display_gallery_table();
						break;
					case 'reset-defaults':
						$default_filename = plugin_dir_path(__FILE__) . 'default.xml';
						$reset_values = $this->get_reset_values();
						$this->build_gallery($default_filename, $reset_values);
						echo '<div class="updated"><p>Default values of gallery configuration options successfully reset to original values.</p></div>';
						$this->display_gallery_table();
						break;
				}
			} else {
				$this->display_gallery_table();
			}
?>
		</div>
<?php
		add_action('in_admin_footer', array(&$this, 'add_footer_links'));
	}

	/**
	 * Build the gallery table based on file names
	 *
	 * @return void
	 */
	function display_gallery_table() {
		$galleries = $this->get_all_galleries($this->get_gallery_path());
		usort($galleries, array(&$this, 'sort_galleries'));
?>
		<div id="buttons-header">
			<form class="sv-delete" id="delete-header" action="<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php'; ?>" method="get" style="display: inline;" title="Delete all galleries and options.">
				<input class="button" id="delete" name="delete" type="submit" value="Delete All Data" />
				<input type="hidden" id="page" name="page" value="sv_manage_gallery" />
				<input type="hidden" id="sv-action" name="sv-action" value="delete-all-data" />
			</form>
			<input type="button" class="button" id="set-header" name="set-header" value="Set Defaults" onclick="location.href='<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php?page=sv_manage_gallery&sv-action=set-defaults'; ?>'" style="display: inline;" title="Set default values for gallery configuration options." />
			<form class="sv-reset" id="reset-header" action="<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php'; ?>" method="get" style="display: inline;" title="Reset default values of gallery configuration options to original values.">
				<input class="button" id="reset" name="reset" type="submit" value="Reset Defaults" />
				<input type="hidden" id="page" name="page" value="sv_manage_gallery" />
				<input type="hidden" id="sv-action" name="sv-action" value="reset-defaults" />
			</form>
		</div>

		<br />

		<table class="widefat">

			<thead>
				<tr>
					<th>Gallery Id</th>
					<th>Last Modified</th>
					<th>Page/Post Title</th>
					<th>Gallery Title</th>
					<th>View Page/Post</th>
					<th>Edit Gallery</th>
					<th>Delete Gallery</th>
				</tr>
			</thead>

			<tbody>
<?php
			if (count($galleries) > 0) {
				foreach ($galleries as $gallery) {
					$gallery_id = pathinfo($gallery, PATHINFO_FILENAME);
					$gallery_filename = $this->get_gallery_path() . $gallery;
					if (file_exists($gallery_filename)) {
?>
						<tr>
							<td><?php echo $gallery_id; ?></td>
							<td><?php echo date ("F d Y H:i:s", filemtime($gallery_filename)); ?></td>
							<td>
<?php
								$post_id = $this->get_post_id($gallery_filename);
								$post_record = get_post($post_id);
								if (!is_null($post_record)) {
									echo get_the_title($post_id);
								} else {
									echo '<i>Page/post does not exist.</i>';
								}
?>
							</td>
<?php
							$dom_doc = new DOMDocument();
							$dom_doc->load($gallery_filename);
							$settings_tags = $dom_doc->getElementsByTagName('simpleviewergallery');
							$settings_tag = $settings_tags->item(0);
							$gallery_title = $settings_tag->hasAttribute('title') ? $settings_tag->getAttribute('title') : '';
?>
							<td><?php echo htmlspecialchars($gallery_title); ?></td>
							<td>
<?php
								if (!is_null($post_record)) {
									echo '<a href="' . get_permalink($post_id) . '">View ' . ucwords(get_post_type($post_id)) . '</a>';
								} else {
									echo '<i>Page/post does not exist.</i>';
								}
?>
							</td>
							<td><a href="<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php?page=sv_manage_gallery&sv-action=edit-gallery&sv-gallery-id=' . $gallery_id; ?>">Edit Gallery</a></td>
							<td><a class="sv-delete-gallery" href="<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php?page=sv_manage_gallery&sv-action=delete-gallery&sv-gallery-id=' . $gallery_id; ?>">Delete Gallery</a></td>
						</tr>
<?php
					}
				}
			} else {
?>
				<tr>
					<td>No galleries found.</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
<?php
			}
?>
			</tbody>

			<tfoot>
				<tr>
					<th>Gallery Id</th>
					<th>Last Modified</th>
					<th>Page/Post Title</th>
					<th>Gallery Title</th>
					<th>View Page/Post</th>
					<th>Edit Gallery</th>
					<th>Delete Gallery</th>
				</tr>
			</tfoot>

		</table>

		<br />

		<div id="buttons-footer">
			<form class="sv-delete" id="delete-footer" action="<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php'; ?>" method="get" style="display: inline;" title="Delete all galleries and options.">
				<input class="button" id="delete" name="delete" type="submit" value="Delete All Data" />
				<input type="hidden" id="page" name="page" value="sv_manage_gallery" />
				<input type="hidden" id="sv-action" name="sv-action" value="delete-all-data" />
			</form>
			<input type="button" class="button" id="set-footer" name="set-footer" value="Set Defaults" onclick="location.href='<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php?page=sv_manage_gallery&sv-action=set-defaults'; ?>'" style="display: inline;" title="Set default values for gallery configuration options." />
			<form class="sv-reset" id="reset-footer" action="<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php'; ?>" method="get" style="display: inline;" title="Reset default values of gallery configuration options to original values." />
				<input class="button" id="reset" name="reset" type="submit" value="Reset Defaults" />
				<input type="hidden" id="page" name="page" value="sv_manage_gallery" />
				<input type="hidden" id="sv-action" name="sv-action" value="reset-defaults" />
			</form>
		</div>
<?php
	}

	/**
	 * Display edit gallery form
	 *
	 * @return void
	 */
	function display_edit_gallery_form($gallery_id) {
		$custom_values = $this->get_default_values();
		$gallery_filename = $this->get_gallery_path() . $gallery_id . '.xml';
		if (file_exists($gallery_filename)) {

			$dom_doc = new DOMDocument();
			$dom_doc->load($gallery_filename);

			$settings_tags = $dom_doc->getElementsByTagName('simpleviewergallery');
			$settings_tag = $settings_tags->item(0);

			if ($settings_tag->hasAttributes()) {
				$custom_values = array();
				foreach ($settings_tag->attributes as $attribute) {
					$name = $attribute->nodeName;
					$value = $attribute->nodeValue;
					$custom_values[$name] = $value;
				}
			}
		}
		$pro_options = $this->get_pro_options($custom_values);
?>
		<h3>Edit SimpleViewer Gallery Id <?php echo $gallery_id; ?></h3>
		<form id="build-form-edit" action="<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php?page=sv_manage_gallery&sv-action=gallery-edited'; ?>" method="post">

			<input type="hidden" id="sv-gallery-id" name="sv-gallery-id" value="<?php echo $gallery_id; ?>" />
<?php
			include plugin_dir_path(__FILE__) . 'fieldset.php';
?>
			<div class="col1">
				<input type="submit" class="button" id="edit" name="edit" value="Save" />
				<input type="button" class="button" id="do-not-edit" name="do-not-edit" value="Cancel" onclick="location.href='<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php?page=sv_manage_gallery'; ?>'" />
			</div>
<?php
			wp_nonce_field('sv-edit', 'sv-edit-nonce');
?>
		</form>
<?php
	}

	/**
	 * Display set defaults form
	 *
	 * @return void
	 */
	function display_set_defaults_form() {
		$custom_values = $this->get_default_values();
		$pro_options = $this->get_pro_options($custom_values);
?>
		<h3>Set Default Values</h3>
		<form id="build-form-set" action="<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php?page=sv_manage_gallery&sv-action=defaults-set'; ?>" method="post">
<?php
			include plugin_dir_path(__FILE__) . 'fieldset.php';
?>
			<div class="col1">
				<input type="submit" class="button" id="set" name="set" value="Set" />
				<input type="button" class="button" id="do-not-set" name="do-not-set" value="Cancel" onclick="location.href='<?php echo get_bloginfo('wpurl') . '/wp-admin/admin.php?page=sv_manage_gallery'; ?>'" />
			</div>
<?php
			wp_nonce_field('sv-set', 'sv-set-nonce');
?>
		</form>
<?php
	}

	/**
	 * Include the common script
	 *
	 * @return void
	 */
	function include_scripts() {
		if (!is_admin()) {
			wp_register_script('sv_core', plugins_url('svcore/js/simpleviewer.js', __FILE__), array(), $this->version);
			wp_enqueue_script('sv_core');
		}
	}

	/**
	 * Shortcode handler
	 *
	 * @param array attributes
	 * @return string embed code
	 */
	function shortcode_handler($atts) {
		extract(shortcode_atts(array('gallery_id'=>0), $atts));

		if ($gallery_id !== 0) {

			$gallery_filename = $this->get_gallery_path() . $gallery_id . '.xml';

			if (file_exists($gallery_filename)) {

				$reset_values = $this->get_reset_values();

				$dom_doc = new DOMDocument();
				$dom_doc->load($gallery_filename);

				$settings_tags = $dom_doc->getElementsByTagName('simpleviewergallery');
				$settings_tag = $settings_tags->item(0);

				$gallery_width = $settings_tag->hasAttribute('e_g_width') ? $settings_tag->getAttribute('e_g_width') : $reset_values['e_g_width'];

				$gallery_height = $settings_tag->hasAttribute('e_g_height') ? $settings_tag->getAttribute('e_g_height') : $reset_values['e_g_height'];

				$background_color = $settings_tag->hasAttribute('e_bgColor') ? $settings_tag->getAttribute('e_bgColor') : $reset_values['e_bgColor'];

				$use_flash = $settings_tag->hasAttribute('e_useFlash') ? $settings_tag->getAttribute('e_useFlash') : $reset_values['e_useFlash'];

				$gallery_url = plugins_url('gallery.php?gallery_id=' . $gallery_id, __FILE__);

				if (is_ssl()) {
					$gallery_url = str_replace('http://', 'https://', $gallery_url);
				}

				return <<<EOF
<!--START SIMPLEVIEWER EMBED-->
<script type="text/javascript">
	var flashvars$gallery_id = {};
	flashvars$gallery_id.galleryURL = "$gallery_url";
	simpleviewer.ready(function () {
		simpleviewer.load("sv-container$gallery_id", "$gallery_width", "$gallery_height", "$background_color", $use_flash, flashvars$gallery_id);
	});
</script>
<div id="sv-container$gallery_id"></div>
<!--END SIMPLEVIEWER EMBED-->
EOF;
			} else {
				return '<div><p>SimpleViewer Gallery Id ' . $gallery_id . ' has been deleted.</p></div>';
			}
		} else {
			return '<div><p>SimpleViewer Gallery Id cannot be found.</p></div>';
		}
	}
}

/**
 * Main
 *
 * @return void
 */
function SimpleViewer() {
	global $SimpleViewer;
	$SimpleViewer = new SimpleViewer();
}

add_action('init', 'SimpleViewer');

/**
 * Check plugin dependency
 *
 * @return void
 */
function sv_check_dependency() {

	// Check PHP version
	if (version_compare(phpversion(), '5.2', '<')) {
		sv_display_error_message('<strong>WP-SimpleViewer</strong> requires PHP v5.2 or later.', E_USER_ERROR);
	}

	// Check if DOM extention is enabled
	if (!class_exists('DOMDocument')) {
		sv_display_error_message('<strong>WP-SimpleViewer</strong> requires the DOM extention to be enabled.', E_USER_ERROR);
	}

	// Check WordPress version
	global $wp_version;
	if (version_compare($wp_version, '2.8', '<')) {
		sv_display_error_message('<strong>WP-SimpleViewer</strong> requires WordPress v2.8 or later.', E_USER_ERROR);
	}

	// Find the path to the WordPress uploads folder
	$upload_dir = wp_upload_dir();
	$gallery_path = $upload_dir['basedir'] . '/';

	clearstatcache();
	
	// Create uploads folder and assign full access permissions
	if (!file_exists($gallery_path))
	{
		$old = umask(0);
		if (!@mkdir($gallery_path, 0777, true)) {
			sv_display_error_message('<strong>WP-SimpleViewer</strong> cannot create the <strong>wp-content/uploads</strong> folder. Please do this manually and assign full access permissions (777) to it.', E_USER_ERROR);
		}
		@umask($old);
		if ($old !== umask()) {
			sv_display_error_message('<strong>WP-SimpleViewer</strong> cannot cannot change back the umask after creating the <strong>wp-content/uploads</strong> folder.', E_USER_ERROR);
		}
	} else {
		if (substr(sprintf('%o', fileperms($gallery_path)), -4) !== 0777) {
			$old = umask(0);
			if (!@chmod($gallery_path, 0777)) {
				sv_display_error_message('<strong>WP-SimpleViewer</strong> cannot assign full access permissions (777) to the <strong>wp-content/uploads</strong> folder. Please do this manually.<br>Plugin <strong>deactivated</strong>.', E_USER_ERROR);
			}
			@umask($old);
			if ($old !== umask()) {
				sv_display_error_message('<strong>WP-SimpleViewer</strong> cannot cannot change back the umask after assigning full access permissions (777) to the <strong>wp-content/uploads</strong> folder.', E_USER_ERROR);
			}
		}
	}
}

/**
 * Display error message
 *
 * @param string error message
 * @param integer error type
 */
function sv_display_error_message($error_msg, $error_type) {
	if(isset($_GET['action']) && $_GET['action'] === 'error_scrape') {
		echo $error_msg;
		exit;
    } else {
		trigger_error($error_msg, $error_type);
    }
}

register_activation_hook(__FILE__, 'sv_check_dependency');

?>
