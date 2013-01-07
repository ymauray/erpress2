<?php
class ERPress2ActionsSummary extends WPFActions {

	public $id;
	public $title;
	public $position;
	public $episode_id;
	public $artist_id;
	public $album_id;
	public $duplicate;
	public $source_id;
	public $source_link;
	
	function edit() {
		global $wpdb;
		
		if ($this->duplicate == false) {
			$wpdb->update(
					ERPress2::$tracks_table,
					array(
							'title' => $this->title,
							'episode_id' => $this->episode_id,
							'position' => $this->position,
							'source_id' => $this->source_id,
							'source_link' => $this->source_link
					),
					array(
							'id' => $this->id
					),
					array('%s', '%d', '%d', '%d', '%s'),
					array('%d')
			);
			ERPress2::add_message(ERPress2::__('Track successfully updated'));
		}
		else {
			$wpdb->insert(
					ERPress2::$tracks_table,
					array(
							'title' => $this->title,
							'episode_id' => $this->episode_id,
							'position' => $this->position,
							'artist_id' => $this->artist_id,
							'album_id' => $this->album_id,
							'source_id' => $this->source_id,
							'source_link' => $this->source_link
					),
					array('%s', '%d', '%d', '%d', '%d', '%d', '%s')
			);
			ERPress2::add_message(ERPress2::__('Track successfully duplicated'));
		}
		
		
		$this->redirect_to_referer();
	}
	
	function delete() {
		global $wpdb;
		
		$wpdb->query($wpdb->prepare('delete from ' . ERPress2::$tracks_table . ' where id = %d', $_REQUEST['id']));
		
		ERPress2::add_message(ERPress2::__('Track successfully deleted'));
		
		$this->redirect_to_referer();
	}

	function add() {
		global $wpdb;
		
		$wpdb->insert(
				ERPress2::$tracks_table,
				array(
						'title' => $this->title,
						'episode_id' => $this->episode_id,
						'position' => $this->position,
						'artist_id' => $this->artist_id,
						'album_id' => $this->album_id,
						'source_id' => $this->source_id,
						'source_link' => $this->source_link
				),
				array('%s', '%d', '%d', '%d', '%d', '%d', '%s')
		);
		
		ERPress2::add_message(ERPress2::__('Track successfully added'));
		
		$this->redirect_to_referer();
	}
	
	function artist_combo_action() {
		global $wpdb;
		
		$rows = $wpdb->get_results($wpdb->prepare('select id, title from ' . ERPress2::$albums_table . ' where artist_id = %d', $_REQUEST['artist_id']));
		echo json_encode($rows);
		die();
	}
	
	function album_combo_action() {
		global $wpdb;
		
		$row = $wpdb->get_row($wpdb->prepare('select source_id, source_link from ' . ERPress2::$albums_table . ' where id = %d', $_REQUEST['album_id']));
		if ($row == null) {
			echo '{"source_id": "0", "source_link": ""}';
		}
		else {
			echo json_encode($row);
		}
		die();
	}
}
?>
