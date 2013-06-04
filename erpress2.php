<?php
/*
Plugin Name: erpress2
Plugin URI: http://www.euterpia-radio.fr/plugins/erpress2/
Description: Plugin to manage show notes.
Version: 0.1.alpha
Author: Yannick
Author URI: http://www.euterpia-radio.fr/
Contributors:
Yannick

Credits:

Copyright 2012 - Euterpia Radio

License: GPL (http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt)
*/

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

require_once(WP_PLUGIN_DIR . '/WPF/WPF.php');

require('menus/ERPress2MenuArtists.php');
require('menus/ERPress2MenuAlbums.php');
require('menus/ERPress2MenuShows.php');
require('menus/ERPress2MenuSummary.php');
require('menus/ERPress2MenuTracks.php');
require('menus/ERPress2MenuStats.php');
require('menus/ERPress2MenuCombined.php');

require('forms/ERPress2FormSummary.php');
require('forms/ERPress2FormArtist.php');
require('forms/ERPress2FormAlbum.php');
require('forms/ERPress2FormEpisode.php');

require('actions/ERPress2ActionsSummary.php');
require('actions/ERPress2ActionsArtists.php');
require('actions/ERPress2ActionsAlbums.php');
require('actions/ERPress2ActionsEpisodes.php');

class ERPress2 extends WPFPlugin {
	
	const PLUGIN_ID = 'erpress2';
	
	public static $artists_table;
	public static $albums_table;
	public static $episodes_table;
	public static $sources_table;
	public static $tracks_table;
	
	static function __($string) {
		return __($string, self::PLUGIN_ID);
	}

	static function _e($string) {
		_e($string, self::PLUGIN_ID);
	}
	
	/**
	 * Constructor.
	 */
	function __construct() {
		global $wpdb;
		
		parent::__construct(self::PLUGIN_ID);
		
		ERPress2::$artists_table = $wpdb->prefix . 'erpress2_artists';
		ERPress2::$albums_table = $wpdb->prefix . 'erpress2_albums';
		ERPress2::$tracks_table = $wpdb->prefix . 'erpress2_tracks';
		ERPress2::$episodes_table = $wpdb->prefix . 'erpress2_episodes';
		ERPress2::$sources_table = $wpdb->prefix . 'erpress2_sources';
	}

	/**
	 * @return string the file in which the plugin is defined.
	 */
	// @override
	function get_file() {
		return __FILE__;
	}
	
	function install_tables() {
		global $wpdb;
		
		$sql = 'CREATE TABLE ' . self::$artists_table . ' (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(32) CHARACTER SET utf8 NOT NULL,
			website text CHARACTER SET utf8 NOT NULL,
			facebook text CHARACTER SET utf8 NOT NULL,
			twitter varchar(64) CHARACTER SET utf8 NOT NULL,
			PRIMARY KEY  (id)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8';
		
		dbDelta($sql);
		
		$sql = 'CREATE TABLE ' . self::$albums_table . ' (
			id int(11) NOT NULL AUTO_INCREMENT,
			artist_id int(11) NOT NULL,
			title varchar(128) CHARACTER SET utf8 NOT NULL,
			buy_link text CHARACTER SET utf8 NOT NULL,
			month int(11) NULL,
			year int(11) NOT NULL,
			source_id int(11) NOT NULL,
			source_link text CHARACTER SET utf8 NOT NULL,
			PRIMARY KEY  (id)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8';
		
		dbDelta($sql);
		
		$sql = 'CREATE TABLE ' . self::$tracks_table . ' (
			id int(11) NOT NULL AUTO_INCREMENT,
			episode_id int(11) NOT NULL,
			artist_id int(11) NOT NULL,
			album_id int(11) NOT NULL,
			title varchar(128) CHARACTER SET utf8 NOT NULL,
			position int(11) NOT NULL,
			PRIMARY KEY  (id)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8';
		
		dbDelta($sql);

		$sql = 'CREATE TABLE ' . self::$episodes_table . ' (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(32) CHARACTER SET utf8 NOT NULL,
			publication date NOT NULL,
			archive tinyint(1) NOT NULL DEFAULT "0",
			PRIMARY KEY  (id)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8';
		
		dbDelta($sql);
		
		$sql = 'CREATE TABLE ' . self::$sources_table . ' (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(32) CHARACTER SET utf8 NOT NULL,
			website text CHARACTER SET utf8 NOT NULL,
			PRIMARY KEY  (id)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8';
		
		dbDelta($sql);
		
		$count = $wpdb->get_var($wpdb->prepare('select count(*) from ' . self::$sources_table));
		
		if ($count == 0) {
			$wpdb->insert(self::$sources_table, array('name' => 'CyberPR', 'website' => 'http://www.cyberpr.biz'));
			$wpdb->insert(self::$sources_table, array('name' => 'musicSUBMIT', 'website' => 'http://www.musicsubmit.com'));
			$wpdb->insert(self::$sources_table, array('name' => 'Jamendo', 'website' => 'http://www.jamendo.com/fr'));
			$wpdb->insert(self::$sources_table, array('name' => 'PureVolume', 'website' => 'http://www.purevolume.com'));
			$wpdb->insert(self::$sources_table, array('name' => 'Music Alley', 'website' => 'http://www.musicalley.com'));
			$wpdb->insert(self::$sources_table, array('name' => 'Reverb Nation', 'website' => 'http://www.reverbnation.com'));
			$wpdb->insert(self::$sources_table, array('name' => 'Deuce', 'website' => 'http://www.deucemp.com'));
			$wpdb->insert(self::$sources_table, array('name' => 'mail', 'website' => 'mailto:info@euterpia-radio.fr'));
			$wpdb->insert(self::$sources_table, array('name' => 'myspace', 'website' => 'http://www.myspace.com'));
			$wpdb->insert(self::$sources_table, array('name' => 'AirPlay Direct', 'website' => 'http://www.airplaydirect.com'));
			$wpdb->insert(self::$sources_table, array('name' => 'MusicClout', 'website' => 'http://www.musicclout.com/'));
			$wpdb->insert(self::$sources_table, array('name' => 'Musik and Film', 'website' => 'http://www.musikandfilm.com/'));
			$wpdb->insert(self::$sources_table, array('name' => 'Radio Mavens', 'website' => 'http://getready4radio.com/'));
		}
	}
	
	public function register_actions() {
		WPFActions::register_actions($this, 'erpress2-track', 'ERPress2ActionsSummary', array('edit', 'add', 'delete'));
		WPFActions::register_actions($this, 'erpress2-artist', 'ERPress2ActionsArtists', array('edit', 'add', 'delete'));
		WPFActions::register_actions($this, 'erpress2-album', 'ERPress2ActionsAlbums', array('edit', 'add', 'delete'));
		WPFActions::register_actions($this, 'erpress2-episode', 'ERPress2ActionsEpisodes', array('edit', 'add', 'delete', 'archive'));
		WPFActions::register_actions($this, 'erpress2-combined', 'ERPress2ActionsCombined', array('add'));
		add_action('wp_ajax_artist_combo_action', array('ERPress2ActionsSummary', 'artist_combo_action'));
		add_action('wp_ajax_album_combo_action', array('ERPress2ActionsSummary', 'album_combo_action'));
	}
	
	/**
	 * This action is used to add extra submenus and menu options to the admin
	 * panel's menu structure. It runs after the basic admin panel menu
	 * structure is in place.
	 */
	// @override
	public function create_admin_menu() {
		$menu = new WPFMenu();
		$menu_page = $menu->create_menu_page($this, self::__('ERPress2'), 'read');
		$menu_page->add_submenu_page(self::__('Summary'), 'summary', 'ERPress2MenuSummary');
		$menu_page->add_submenu_page(self::__('Artists'), 'artists', 'ERPress2MenuArtists');
		$menu_page->add_submenu_page(self::__('Albums'), 'albums', 'ERPress2MenuAlbums');
		$menu_page->add_submenu_page(self::__('Tracks'), 'tracks', 'ERPress2MenuTracks');
		$menu_page->add_submenu_page(self::__('Shows'), 'shows', 'ERPress2MenuShows');
		$menu_page->add_submenu_page(self::__('Stats'), 'stats', 'ERPress2MenuStats');
		$menu_page->add_submenu_page(self::__('Add track'), 'combined', 'ERPress2MenuCombined');		
		return $menu;
	}

	public function get_settings_config($settings) {
		$settings->group = 'erpress2-settings-group';
		$settings->name = 'erpress2-settings';
		$settings->menuSlug = 'erpress2-plugin';
		$settings->menuTitle = self::__('ERPress2');
		$settings->renderCallback = array($this, 'settings_render_form');

		$section = $this->create_settings_section('erpress2-settings-amped-section', self::__('AMPed API settings'), array($this, 'settings_amped_section'));

		$section->fields[] = $this->create_settings_field('erpress2-amped-url', 'API URL', function() {
			$settings = (array) get_option('erpress2-settings');
			$url = esc_attr(isset($settings['url']) ? $settings['url'] : '');
	    	echo "<input type='text' name='erpress2-settings[url]' value='$url' size='60'/>";
		});
		$section->fields[] = $this->create_settings_field('erpress2-amped-pubkey', 'Public key', function() {
			$settings = (array) get_option('erpress2-settings');
			$pubkey = esc_attr(isset($settings['pubkey']) ? $settings['pubkey'] : '');
			echo "<input type='text' name='erpress2-settings[pubkey]' value='$pubkey' size='60'/>";
		});
		$section->fields[] = $this->create_settings_field('erpress2-amped-privkey', 'Private key', function() {
			$settings = (array) get_option('erpress2-settings');
			$privkey = esc_attr(isset($settings['privkey']) ? $settings['privkey'] : '');
			echo "<input type='text' name='erpress2-settings[privkey]' value='$privkey' size='60'/>";
		});

		$settings->sections[] = $section;
	}

	public function settings_amped_section() {

	}

	public function settings_render_form() {
		?>
<div class="wrap">
    <h2>ERPress2 options</h2>
    <form action="options.php" method="POST">
        <?php settings_fields('erpress2-settings-group'); ?>
        <?php do_settings_sections('erpress2-plugin'); ?>
        <?php submit_button(); ?>
    </form>
</div>
<?php
	}
}

new ERPress2();

?>
