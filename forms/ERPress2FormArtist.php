<?php
class ERPress2FormArtist extends WPFForm {
	
	public $id;
	public $name;
	public $website;
	public $facebook;
	public $twitter;

	public function init_fields() {
		
		$this->set_submit_label($this->if_edit(ERPress2::__('Update artist'), ERPress2::__('Add artist')));
		$this->set_action($this->if_edit('erpress2-artist-edit', 'erpress2-artist-add'));
		
		if ($this->is_edit) {
			$this->add_field(array('type' => 'hidden', 'property' => 'id'));
		}
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Name'), 'property' => 'name'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Website'), 'property' => 'website'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Facebook'), 'property' => 'facebook'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Twitter'), 'property' => 'twitter'));
	}
}
?>
