<?php
class ERPress2FormAlbum extends WPFForm {
	
	public $id;
	public $artist_id;
	public $title;
	public $buy_link;
	public $month;
	public $year;
	public $source_id;
	public $source_link;

	public function init_fields() {
		global $wpdb;
		
		$rows = $wpdb->get_results($wpdb->prepare('select ar.* from ' . ERPress2::$artists_table . ' ar order by ar.name'));
		foreach ($rows as $row) {
			$artists[] = array('label' => $row->name, 'value' => $row->id);
		}
		
		$rows = $wpdb->get_results($wpdb->prepare('select s.* from ' . ERPress2::$sources_table. ' s order by s.name'));
		foreach ($rows as $row) {
			$sources[] = array('label' => $row->name, 'value' => $row->id);
		}
		
		$this->set_action($this->if_edit('erpress2-album-edit', 'erpress2-album-add'));
		$this->set_submit_label($this->if_edit(ERPress2::__('Update album'), ERPress2::__('Add album')));
		if ($this->is_edit) {
			$this->add_field(array('type' => 'hidden', 'property' => 'id'));
		}
		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Artist'), 'property' => 'artist_id', 'options' => $artists));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Title'), 'property' => 'title'));
		$this->add_field(array('type' => 'textarea', 'label' => ERPress2::__('Buy link'), 'property' => 'buy_link'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Pub. month'), 'property' => 'month'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Pub. year'), 'property' => 'year'));
		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Source'), 'property' => 'source_id', 'options' => $sources));
		$this->add_field(array('type' => 'textarea', 'label' => ERPress2::__('Source link'), 'property' => 'source_link'));
	}
}
?>
