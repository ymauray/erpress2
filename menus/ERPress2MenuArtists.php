<?php
class ERPress2MenuArtists extends WPFPage {
	
	function __construct($plugin) {
		parent::__construct($plugin, array('add', 'edit'));
	}
	
	function default_page() {
		global $wpdb;
		
		if (isset($_REQUEST['s'])) {
			$data = $wpdb->prepare('select * from ' . ERPress2::$artists_table . ' where name like %s order by name', '%' . $_REQUEST['s'] . '%');
		}
		else {
			$data = 'select * from ' . ERPress2::$artists_table . ' order by name';
		}
		
		$this->page_header(ERPress2::__('Artists'), array('label' => ERPress2::__('Add'), 'link' => $this->admin_link(null, 'add')));
		$this->store_uri();
		$this->display_table(
				$data, 
				'id', 
				array(
						'name' => array(
								'label' => ERPress2::__('Name'),
								'menu' => array(
										'edit' => array(
												'label' => ERPress2::__('Edit')
										),
										'delete' => array(
												'label' => ERPress2::__('Delete'),
												'action' => 'erpress2-artist-delete'
										)
								)
						),
						'website' => array(
								'label' => ERPress2::__('Website')
						),
						'facebook' => array(
								'label' => ERPress2::__('Facebook')
						),
						'twitter' => array(
								'label' => ERPress2::__('Twitter')
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
	
	public function edit() {
		global $wpdb;
		
		$row = $wpdb->get_row($wpdb->prepare('select * from ' . ERPress2::$artists_table . ' where id = %d', $_REQUEST['id']));
		
		$this->page_header(ERPress2::__('Edit an artist'));
		
		$form = new ERPress2FormArtist($row);
		$form->render();
		
		$this->page_footer();
	}
	
	public function add() {

		$this->page_header(ERPress2::__('Add an artist'));
		
		$form = new ERPress2FormArtist();
		$form->render();
		
		$this->page_footer();
	}
}
?>
