<?php
class ERPress2FormSummary extends WPFForm {
	
	public $id;
	public $artist;
	public $artist_id;
	public $title;
	public $album;
	public $album_id;
	public $month;
	public $year;
	public $publication;
	public $position;
	public $episode_id;
	public $duplicate;
	public $source_id;
	public $source_link;
	
	function init_fields() {
		global $wpdb;
				
		if (isset($this->month)) {
			$this->publication = $this->month . ' / ' . $this->year; 
		}
		elseif ($this->year > 0) {
			$this->publication = $this->year;
		}
		else {
			$this->publication = '';
		}
		
		for ($i = 1; $i <= 10; $i++) {
			$options[] = array('label' => $i, 'value' => $i);
		}
		
		$rows = $wpdb->get_results($wpdb->prepare('select e.*, z.total from ' . ERPress2::$episodes_table . ' e left join (select episode_id, count(*) as total from ' . ERPress2::$tracks_table . ' group by episode_id) z on z.episode_id = e.id where e.archive = false'));
		foreach ($rows as $row) {
			$episodes[] = array('label' => ($row->name . ' (' . $row->total . ')'), 'value' => $row->id);
		}

		$rows = $wpdb->get_results($wpdb->prepare('select s.* from ' . ERPress2::$sources_table. ' s order by s.name'));
		foreach ($rows as $row) {
			$sources[] = array('label' => $row->name, 'value' => $row->id);
		}

		$this->set_submit_label($this->if_edit(ERPress2::__('Update track'), ERPress2::__('Add track')));
		$this->set_action($this->if_edit('erpress2-track-edit', 'erpress2-track-add'));
		if ($this->is_edit) {
			$this->add_field(array('type' => 'hidden', 'property' => 'id'));
			$this->add_field(array('type' => 'hidden', 'property' => 'artist_id'));
			$this->add_field(array('type' => 'hidden', 'property' => 'album_id'));
			$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Artist'), 'property' => 'artist', 'readonly' => true));
			$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Album'), 'property' => 'album', 'readonly' => true));
			$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Source'), 'property' => 'source_id', 'options' => $sources));
			$this->add_field(array('type' => 'textarea', 'label' => ERPress2::__('Source link'), 'property' => 'source_link'));
			$this->add_field(array('type' => 'text', 'property' => 'title', 'label' => ERPress2::__('Title')));
			$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Episode'), 'property' => 'episode_id', 'options' => $episodes));
			$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Position'), 'property' => 'position', 'options' => $options));
			$this->add_field(array('type' => 'checkbox', 'label' => ERPress2::__('Duplicate'), 'property' => 'duplicate'));
		}
		else {
			$rows = $wpdb->get_results($wpdb->prepare('select id, name from ' . ERPress2::$artists_table . ' order by name asc'));
			foreach ($rows as $row) {
				$artists[] = array('label' => $row->name, 'value' => $row->id);
			}
			$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Artist'), 'property' => 'artist_id', 'options' => $artists));
			$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Album'), 'property' => 'album_id', 'options' => array(), 'readonly' => true));
			$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Source'), 'property' => 'source_id', 'options' => $sources));
			$this->add_field(array('type' => 'textarea', 'label' => ERPress2::__('Source link'), 'property' => 'source_link'));
			$this->add_field(array('type' => 'text', 'property' => 'title', 'label' => ERPress2::__('Title')));
			$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Episode'), 'property' => 'episode_id', 'options' => $episodes));
			$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Position'), 'property' => 'position', 'options' => $options));
		}
	}
}
?>
