<?php
class ERPress2MenuTracks extends WPFPage {
	
	function __construct($plugin) {
		parent::__construct($plugin, array());
	}
	
	function default_page() {
		global $wpdb;
		
		$this->page_header(ERPress2::__('Tracks'));
		
		$s = '%';
		if (isset($_REQUEST['s'])) {
			$s .= $_REQUEST['s'];
		}
		$s .= '%';
		$sql = 'select tr.*, ar.name as artist, ep.name as episode from ' . ERPress2::$tracks_table . ' tr, ' . ERPress2::$artists_table . ' ar, ' . ERPress2::$albums_table . ' al, ' . ERPress2::$episodes_table . ' ep where ar.id = tr.artist_id and al.id = tr.album_id and ep.id = tr.episode_id';
		if ($s != '%%') {
			$sql .= ' and (ar.name like %s or tr.title like %s)';
		}
		$sql .= ' order by artist, title asc';
		$data = $wpdb->prepare($sql, $s, $s);
		
		$this->store_uri();
		$this->display_table(
				$data, 
				'id',
				array(
						'artist' => array(
								'label' => ERPress2::__('Artist')
						),
						'title' => array(
								'label' => ERPress2::__('Title')
						),
						'episode' => array(
								'label' => ERPress2::__('Episode')
						)
				),
				array(
						'search_box' => array(
								'label' => ERPress2::__('Search')
						)
				)
		);
		$this->page_footer();
	}
}
?>
