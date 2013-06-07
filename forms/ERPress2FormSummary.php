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
		$options[] = array('label' => 'Flashback', 'value' => 11);
		
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

class ERPress2FormSubmitToAMPed extends WPFForm {

	public $podcast;
	public $show;
	public $artist;
	public $title;
	public $website;
	public $url;
	public $shownotes;
	public $twitter;
	public $facebook;
	public $host;
	public $episode_name;

	public function init_fields() {
		$client = new AMPedClient();

		$fromRock = (stristr($this->episode_name, 'rock') != false);

		$podcasts = array();
		$client->query('amped.myPodcasts');
		$response = $client->getResponse();
		foreach ($response['podcasts'] as $podcast) {
			$podcasts[] = array('label' => $podcast['title'], 'value' => $podcast['id']);
			$ids[(stristr($podcast['title'], 'rock') === false) ? 0:1] = $podcast['id'];
		}
		$this->podcast = $ids[$fromRock ? 1:0];

		$shows = array();
//		$client->debug = true;
		$client->query('amped.upcomingShows');
		$response = $client->getResponse();
		foreach ($response['shows'] as $show) {
			$shows[] = array('label' => 'AMPed #' . $show['number'] . ' (' . $show['host'] . ') (' . sizeof($show['tracks']) . ')', 'value' => $show['number']);
			if ($this->show == NULL) {
				if (sizeof($show['tracks']) == 0) {
					$this->show = $show['number'];
				}
				else {
					$found = false;
					foreach ($show['tracks'] as $track) {
						if ($track['podcast'] == $this->podcast) {
							$found = true;
						}
					}
					if (!$found) {
						$this->show = $show['number'];
					}
				}
			}
		}

		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Podcast'), 'property' => 'podcast', 'options' => $podcasts, 'mandatory' => true));
		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('AMPed show'), 'property' => 'show', 'options' => $shows, 'mandatory' => true));
		$this->add_field(array('type' => 'text', 'property' => 'artist', 'label' => ERPress2::__('Artist'), 'readonly' => true));
		$this->add_field(array('type' => 'text', 'property' => 'title', 'label' => ERPress2::__('Title'), 'readonly' => true));
		$this->add_field(array('type' => 'text', 'property' => 'website', 'label' => ERPress2::__('Website'), 'readonly' => true));
		$this->add_field(array('type' => 'url', 'property' => 'url', 'label' => ERPress2::__('URL'), 'mandatory' => true));
		$this->add_field(array('type' => 'textarea', 'property' => 'notes', 'label' => ERPress2::__('Notes for the site<br/><span class="input-helper"><span class="shownotes-counter"></span> character(s) left</span>'), 'mandatory' => true));
		$this->add_field(array('type' => 'twitter', 'property' => 'twitter', 'label' => ERPress2::__('Twitter')));
		$this->add_field(array('type' => 'url', 'property' => 'facebook', 'label' => ERPress2::__('Facebook')));
		$this->add_field(array('type' => 'textarea', 'property' => 'host', 'label' => ERPress2::__('Notes for the host')));

		$this->set_submit_label(ERPress2::__('Submit track to AMPed'));
		$this->set_action('erpress2-amped-submit');
	}
}

class ERPress2ActionsSubmitToAMPed extends WPFActions {

	public $podcast;
	public $show;
	public $artist;
	public $title;
	public $website;
	public $url;
	public $notes;
	public $twitter;
	public $facebook;
	public $host;

	function submit() {
		$client = new AMPedClient();
		$client->query('amped.submitTrack', $this);
		$response = $client->getResponse();

		if ($response['status'] == 'ok') {
			ERPress2::add_message(ERPress2::__($response['message']));
		}
		else {
			ERPress2::add_error(ERPress2::__($response['message']));
		}
		
		$this->redirect_to_referer();
	}
}
?>
