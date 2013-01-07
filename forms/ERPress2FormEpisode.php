<?php
class ERPress2FormEpisode extends WPFForm {
	
	public $id;
	public $name;
	public $publication;
	public $archive;

	public function init_fields() {
		
		$this->set_action($this->if_edit('erpress2-episode-edit', 'erpress2-episode-add'));
		$this->set_submit_label($this->if_edit(ERPress2::__('Update episode'), ERPress2::__('Add episode')));
		
		if ($this->is_edit) {
			$this->add_field(array('type' => 'hidden', 'property' => 'id'));
		}
		$this->add_field(array('type' => 'text', 'property' => 'name', 'label' => ERPress2::__('Show name')));
		$this->add_field(array('type' => 'text', 'property' => 'publication', 'label' => ERPress2::__('Publication')));
		
	}
}
?>
