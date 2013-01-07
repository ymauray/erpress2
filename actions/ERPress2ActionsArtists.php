<?php
class ERPress2ActionsArtists extends WPFActions {
	
	public $id;
	public $name;
	public $website;
	public $facebook;
	public $twitter;
	
	public function edit() {
		global $wpdb;
		
		$wpdb->update(
				ERPress2::$artists_table,
				array(
						'name' => $this->name,
						'website' => $this->website,
						'facebook' => $this->facebook,
						'twitter' => $this->twitter
				),
				array(
						'id' => $this->id
				),
				array('%s', '%s', '%s', '%s'),
				array('%d')
		);
		
		ERPress2::add_message(ERPress2::__('Artist successfully updated'));
		
		$this->redirect_to_referer();
	}
	
	public function add() {
		global $wpdb;
		
		$rows = $wpdb->get_results($wpdb->prepare('select * from ' . ERPress2::$artists_table . ' where soundex(name) like soundex(%s)', $this->name));
		
		if (sizeof($rows) > 0) {
			$error = ERPress2::__('An artist with the same name allready exists : ');
			foreach ($rows as $row) {
				$error .= '<br/>' . $row->name;
			}
			ERPress2::add_error($error);
			//$this->redirect_to_referer();
			//return;
		}
		
		$wpdb->insert(
				ERPress2::$artists_table,
				array(
						'name' => $this->name,
						'website' => $this->website,
						'facebook' => $this->facebook,
						'twitter' => $this->twitter
				),
				array('%s', '%s', '%s', '%s')
		);
		
		ERPress2::add_message(ERPress2::__('Artist successfully added'));
		
		$this->redirect_to_referer();
	}
	
	public function delete() {
		global $wpdb;

		$wpdb->query($wpdb->prepare('delete from ' . ERPress2::$artists_table . ' where id = %d', $_REQUEST['id']));
		$wpdb->query($wpdb->prepare('delete from ' . ERPress2::$albums_table . ' where artist_id = %d', $_REQUEST['id']));
		$wpdb->query($wpdb->prepare('delete from ' . ERPress2::$tracks_table . ' where artist_id = %d', $_REQUEST['id']));
		
		ERPress2::add_message(ERPress2::__('Artist successfully deleted'));
		
		$this->redirect_to_referer();
	}
}
?>
