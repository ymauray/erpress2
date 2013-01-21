<?php
class ERPress2MenuAlbums extends WPFPage {
	
	function __construct($plugin) {
		parent::__construct($plugin, array('edit', 'add'));
	}
	
	function default_page() {
		global $wpdb;
		
		$sql = 'select al.*, ar.name as artist, s.name as source from ' . ERPress2::$albums_table . ' al, ' . ERPress2::$artists_table . ' ar, ' . ERPress2::$sources_table . ' s where ar.id = al.artist_id and s.id = al.source_id';
		if (isset($_REQUEST['s'])) {
			 $sql .= ' and (al.title like %s or ar.name like %s)';
		}
		if (isset($_REQUEST['orderby'])) {
			if ($_REQUEST['orderby'] == 'title') {
				$sql .= ' order by al.title ' . $_REQUEST['order'] . ', ar.name asc';
			}
			else {
				$sql .= ' order by ar.name ' . $_REQUEST['order'] . ', al.title asc';
			}
		}
		else {
			$sql .= ' order by ar.name asc, al.title asc';
		}
		
		if (isset($_REQUEST['s'])) {
			$s = '%' . $_REQUEST['s'] . '%';
			$data = $wpdb->prepare($sql, $s, $s);
		}
		else {
			$data = $sql;
		}
		
		$this->page_header(ERPress2::__('Albums'), array('label' => ERPress2::__('Add an album'), 'link' => $this->admin_link(null, 'add')));
		
		$this->store_uri();
		$this->display_table(
				$data,
				'id',
				array(
						'title' => array(
								'label' => ERPress2::__('Name'),
								'sortable' => true,
								'menu' => array($this, 'render_menu')
						),
						'artist' => array(
								'label' => ERPress2::__('Artist'),
								'sortable' => true
						),
						'source_link' => array(
								'label' => ERPress2::__('Source link'),
								'renderer' => array($this, 'render_source_link')
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
	
	function render_menu($row) {
		global $wpdb;
		
		$row = $wpdb->get_row($wpdb->prepare('select count(*) as count from ' . ERPress2::$tracks_table . ' where album_id = %d', $row->id));
		
		$menu['edit'] = array(
				'label' => ERPress2::__('Edit')
		);
		
		if ($row->count == 0) {
			$menu['delete'] = array(
					'label' => ERPress2::__('Delete'),
					'action' => 'erpress2-album-delete'
			);
		}
		
		return $menu;
	}
	
	function render_source_link($value, $row) {
		return '<a href="' . $row->source_link . '">' . $row->source . '</a>';
	}
	
	function edit() {
		global $wpdb;
		
		$this->page_header(ERPress2::__('Edit an album'));
		
		$data = $wpdb->get_row($wpdb->prepare('select al.* from ' . ERPress2::$albums_table . ' al where al.id = %d', $_REQUEST['id']));
		$form = new ERPress2FormAlbum($data);
		$form->render();
		
		$this->page_footer();
	}
	
	function add() {
		$this->page_header(ERPress2::__('Add an album'));
		
		$form = new ERPress2FormAlbum();
		$form->render();
		
		$this->page_footer();
	}
}
?>
