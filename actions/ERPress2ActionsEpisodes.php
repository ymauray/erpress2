<?php
class ERPress2ActionsEpisodes extends WPFActions {
	
	public $id;
	public $name;
	public $publication;

	public function archive() {
		global $wpdb;
		
		$wpdb->update(
				ERPress2::$episodes_table,
				array(
						'archive' => true
				),
				array(
						'id' => $_REQUEST['id']
				),
				array('%d'),
				array('%d')
		);
		
		ERPress2::add_message(ERPress2::__('Episode successfully archived.'));
		
		$this->redirect_to_referer();
	}
	
	public function delete() {
		global $wpdb;
		
		$row = $wpdb->get_row($wpdb->prepare('select count(*) as count from ' . ERPress2::$tracks_table . ' where episode_id = %d', $_REQUEST['id']));
		
		if ($row->count != 0) {
			ERPress2::add_error(ERPress2::__('Impossible to delete an episode with tracks attached.'));
			$this->redirect_to_referer();
			return;
		}
		
		$wpdb->query($wpdb->prepare('delete from ' . ERPress2::$episodes_table . ' where id = %d', $_REQUEST['id']));
		
		ERPress2::add_message(ERPress2::__('Episode successfully deleted.'));
		
		$this->redirect_to_referer();
	}
	
	public function add() {
		global $wpdb;
		
		$wpdb->insert(
				ERPress2::$episodes_table,
				array(
						'name' => $this->name,
						'publication' => $this->publication
				),
				array('%s', '%s')
		);
		
		ERPress2::add_message(ERPress2::__('Episode successfully added.'));
		
		$this->redirect_to_referer();
	}
	
	public function edit() {
		global $wpdb;
		
		$wpdb->update(
				ERPress2::$episodes_table,
				array(
						'name' => $this->name,
						'publication' => $this->publication
				),
				array(
						'id' => $this->id
				),
				array('%s', '%s'),
				array('%d')
		);
		
		ERPress2::add_message(ERPress2::__('Episode successfully updated.'));
		
		$this->redirect_to_referer();
	}
}
?>
