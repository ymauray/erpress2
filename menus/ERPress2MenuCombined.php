<?php

class ERPress2MenuCombined extends WPFPage {
	
	function __construct($plugin) {
		parent::__construct($plugin, array('edit', 'add', 'shownotes'));
	}
	
	function default_page() {
		global $wpdb;

		$this->page_header(ERPress2::__('Add a track'));

		$form = new ERPress2FormCombined();
		$form->render('erpress2_add_track_');

		$this->page_footer();
	}
}

class ERPress2FormCombined extends WPFForm {

	public $artist_id;
	public $artist_name;
	public $website;
	public $facebook;
	public $twitter;

	public $album_id;
	public $album_title;
	public $buy_link;
	public $month;
	public $year;
	public $source_id;
	public $source_link;

	public $track_title;
	public $episode_id;
	public $position;

	public function init_fields() {

		global $wpdb;
		
		$this->set_submit_label(ERPress2::__('Add a track'));
		$this->set_action('erpress2-combined-add');

		$rows = $wpdb->get_results('select ar.* from ' . ERPress2::$artists_table . ' ar order by ar.name');
		foreach ($rows as $row) {
			$artists[] = array('label' => $row->name, 'value' => $row->id);
		}
		
		$rows = $wpdb->get_results('select s.* from ' . ERPress2::$sources_table. ' s order by s.name');
		foreach ($rows as $row) {
			$sources[] = array('label' => $row->name, 'value' => $row->id);
		}

		for ($i = 1; $i <= 10; $i++) {
			$positions[] = array('label' => $i, 'value' => $i);
		}
		$positions[] = array('label' => 'Flashback', 'value' => 11);
		
		$rows = $wpdb->get_results('select e.*, z.total from ' . ERPress2::$episodes_table . ' e left join (select episode_id, count(*) as total from ' . ERPress2::$tracks_table . ' group by episode_id) z on z.episode_id = e.id where e.archive = false');
		foreach ($rows as $row) {
			$episodes[] = array('label' => ($row->name . ' (' . $row->total . ')'), 'value' => $row->id);
		}
		
		$this->add_field(array('type' => 'section', 'label' => ERPress2::__('Artist')));
		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Existing artist'), 'property' => 'artist_id', 'options' => $artists));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Name'), 'property' => 'artist_name'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Website'), 'property' => 'website'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Facebook'), 'property' => 'facebook'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Twitter'), 'property' => 'twitter'));

		$this->add_field(array('type' => 'section', 'label' => ERPress2::__('Album')));
		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Existing album'), 'property' => 'album_id', 'options' => array(), 'readonly' => true));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Title'), 'property' => 'album_title'));
		$this->add_field(array('type' => 'textarea', 'label' => ERPress2::__('Buy link'), 'property' => 'buy_link'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Pub. month'), 'property' => 'month'));
		$this->add_field(array('type' => 'text', 'label' => ERPress2::__('Pub. year'), 'property' => 'year'));
		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Source'), 'property' => 'source_id', 'options' => $sources));
		$this->add_field(array('type' => 'textarea', 'label' => ERPress2::__('Source link'), 'property' => 'source_link'));

		$this->add_field(array('type' => 'section', 'label' => ERPress2::__('Track')));		
		$this->add_field(array('type' => 'text', 'property' => 'track_title', 'label' => ERPress2::__('Title')));
		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Episode'), 'property' => 'episode_id', 'options' => $episodes));
		$this->add_field(array('type' => 'combo', 'label' => ERPress2::__('Position'), 'property' => 'position', 'options' => $positions));

		$this->set_submit_label(ERPress2::__('Add a track'));
	}

}

/**
* 
*/
class ERPress2ActionsCombined extends WPFActions
{
	public $artist_id;
	public $artist_name;
	public $website;
	public $facebook;
	public $twitter;

	public $album_id;
	public $album_title;
	public $buy_link;
	public $month;
	public $year;
	public $source_id;
	public $source_link;

	public $track_title;
	public $episode_id;
	public $position;

	public function add() {
		global $wpdb;

		if ($this->artist_id == '') {
			$rows = $wpdb->get_results($wpdb->prepare('select * from ' . ERPress2::$artists_table . ' where soundex(name) like soundex(%s)', $this->artist_name));
			if (sizeof($rows) > 0) {
				$error = ERPress2::__('An artist with the same name already exists : ');
				foreach ($rows as $row) {
					$error .= '<br/>' . $row->name;
				}
				ERPress2::add_error($error);
			}

			$wpdb->insert(
					ERPress2::$artists_table,
					array(
							'name' => $this->artist_name,
							'website' => $this->website,
							'facebook' => $this->facebook,
							'twitter' => $this->twitter
					),
					array('%s', '%s', '%s', '%s')
			);

			$this->artist_id = $wpdb->insert_id;

			ERPress2::add_message(ERPress2::__('Artist successfully added'));
		}

		if ($this->album_id == '') {
			$rows = $wpdb->get_results($wpdb->prepare('select * from ' . ERPress2::$albums_table. ' where artist_id = %d and soundex(title) like soundex(%s)', $this->artist_id, $this->album_title));
			
			if (sizeof($rows) > 0) {
				$error = ERPress2::__('An album with the same title already exists for this artist : ');
				foreach ($rows as $row) {
					$error .= '<br/>' . $row->title;
				}
				ERPress2::add_error($error);
			}
			
			$wpdb->insert(
					ERPress2::$albums_table,
					array(
							'artist_id' => $this->artist_id,
							'title' => $this->album_title,
							'buy_link' => $this->buy_link,
							'month' => $this->month,
							'year' => $this->year,
							'source_id' => $this->source_id,
							'source_link' => $this->source_link
					),
					array('%d', '%s', '%s', '%d', '%d', '%d', '%s')
			);

			$this->album_id = $wpdb->insert_id;
			
			ERPress2::add_message(ERPress2::__('Album successfully added.'));
		}

		$wpdb->insert(
			ERPress2::$tracks_table,
			array(
					'title' => $this->track_title,
					'episode_id' => $this->episode_id,
					'position' => $this->position,
					'artist_id' => $this->artist_id,
					'album_id' => $this->album_id
			),
			array('%s', '%d', '%d', '%d', '%d')
		);
		
		ERPress2::add_message(ERPress2::__('Track successfully added'));

		$this->redirect_to_referer();
	}
}
?>
