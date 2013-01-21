<?php
class ERPress2MenuShows extends WPFPage {
	
	function __construct($plugin) {
		parent::__construct($plugin, array('add', 'edit'));
	}
	
	function default_page() {
		global $wpdb;
		
		$sql = 'select e.* from ' . ERPress2::$episodes_table . ' e order by publication desc';
		$data = $sql;
		
		$this->page_header(ERPress2::__('Shows'), array('label' => ERPress2::__('Add'), 'link' => $this->admin_link(null, 'add')));
		
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
												'action' => 'erpress2-episode-delete'
										)
								)
						),
						'publication' => array(
								'label' => ERPress2::__('Publication'),
								'renderer' => ':date'
						)
				)
		);
		$this->page_footer();
	}
	
	function add() {
		global $wpdb;
		
		$this->page_header(ERPress2::__('Add an episode'));
		$data->publication = $wpdb->get_var('select date_format(date_add(max(publication), interval 7 day), "%Y-%m-%d") from ' . ERPress2::$episodes_table);
		$form = new ERPress2FormEpisode($data, false);
		$form->render();
		$this->page_footer();
	}
	
	function edit() {
		global $wpdb;
		
		$data = $wpdb->get_row($wpdb->prepare('select * from ' . ERPress2::$episodes_table . ' where id = %d', $_REQUEST['id']));
		$this->page_header(ERPress2::__('Edit an episode'));
		$form = new ERPress2FormEpisode($data);
		$form->render();
		$this->page_footer();
	}
}
?>
