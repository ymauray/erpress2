<?php
class ERPress2ActionsAlbums extends WPFActions {

	public $id;
	public $artist_id;
	public $title;
	public $buy_link;
	public $month;
	public $year;
	public $source_id;
	public $source_link;
	
	public function edit() {
		global $wpdb;
		
		$wpdb->update(
				ERPress2::$albums_table,
				array(
						'artist_id' => $this->artist_id,
						'title' => $this->title,
						'buy_link' => $this->buy_link,
						'month' => $this->month,
						'year' => $this->year,
						'source_id' => $this->source_id,
						'source_link' => $this->source_link
				),
				array(
						'id' => $this->id
				),
				array('%d', '%s', '%s', '%d', '%d', '%d', '%s'),
				array('%d')
		);
		
		ERPress2::add_message(ERPress2::__('Album successfully updated.'));
		
		$this->redirect_to_referer();
	}
	
	public function add() {
		global $wpdb;
		
		$rows = $wpdb->get_results($wpdb->prepare('select * from ' . ERPress2::$albums_table. ' where artist_id = %d and soundex(title) like soundex(%s)', $this->artist_id, $this->title));
		
		if (sizeof($rows) > 0) {
			$error = ERPress2::__('An album with the same title allready exists for this artist : ');
			foreach ($rows as $row) {
				$error .= '<br/>' . $row->title;
			}
			ERPress2::add_error($error);
			$this->redirect_to_referer();
			return;
		}
		
		
		
		$wpdb->insert(
				ERPress2::$albums_table,
				array(
						'artist_id' => $this->artist_id,
						'title' => $this->title,
						'buy_link' => $this->buy_link,
						'month' => $this->month,
						'year' => $this->year,
						'source_id' => $this->source_id,
						'source_link' => $this->source_link
				),
				array('%d', '%s', '%s', '%d', '%d', '%d', '%s')
		);
		
		ERPress2::add_message(ERPress2::__('Album successfully added.'));
		
		$this->redirect_to_referer();
	}
	
	public function delete() {
		global $wpdb;
		
		$wpdb->query($wpdb->prepare('delete from ' . ERPress2::$albums_table . ' where id = %d', $_REQUEST['id']));
		
		ERPress2::add_message(ERPress2::__('Ablum successfully deleted'));
		
		$this->redirect_to_referer();
	}
	
}
?>
