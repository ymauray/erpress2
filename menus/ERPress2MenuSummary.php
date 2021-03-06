<?php

class ERPress2MenuSummary extends WPFPage {
	
	function __construct($plugin) {
		parent::__construct($plugin, array('edit', 'add', 'shownotes', 'submit'));
	}
	
	function default_page() {
		global $wpdb;
		
		$this->page_header(ERPress2::__('Upcoming shows'), array('label' => ERPress2::__('Add a track'), 'link' => $this->admin_link(null, 'add')));
		
		$rows = $wpdb->get_results('select e.* from ' . ERPress2::$episodes_table . ' e where e.archive = false order by publication asc');
		$this->store_uri();
		foreach ($rows as $row) {
			$count_row = $wpdb->get_row($wpdb->prepare('select count(*) as count from ' . ERPress2::$tracks_table . ' where episode_id = %d', $row->id));
			$data = $wpdb->prepare('select t.*, ar.name as artist, ar.website, al.title as album, al.month, al.year, al.source_link as al_source_link, ifnull(s2.name, s.name) as source, "" as previous from ' . ERPress2::$tracks_table . ' t left join ' . ERPress2::$albums_table . ' al on al.id = t.album_id left join ' . ERPress2::$sources_table . ' s on s.id = al.source_id left join ' . ERPress2::$sources_table . ' s2 on s2.id = t.source_id, ' . ERPress2::$artists_table . ' ar where t.episode_id = %d and ar.id = t.artist_id order by t.position asc', $row->id);
			echo '<h3>' . $row->name . ' - ' . $row->publication;
			echo '<a href="' . $this->admin_link(null, 'shownotes', array('id' => $row->id)) . '" class="add-new-h2">' . ERPress2::__('Show notes') . '</a>';
			if ($count_row->count >= 8) {
				echo '<a href="' . $this->action_link('erpress2-episode-archive', array('id' => $row->id)) . '" class="add-new-h2">' . ERPress2::__('Archive this episode') . '</a>';
			}
			echo '</h3>';
			$this->display_table(
					$data, 
					'id', 
					array(
							'position' => array(
									'label' => ERPress2::__('Position'),
									'renderer' => array($this, 'render_position'),
									'menu' => array(
											'edit' => array(
													'label' => ERPress2::__('Edit')
											),
											'submit' => array(
												'label' => ERPress2::__('Submit to AMPed')
											),
											'delete' => array(
													'label' => ERPress2::__('Delete'),
													'action' => 'erpress2-track-delete',
											)
									)
							),
							'artist' => array(
									'label' => ERPress2::__('Artist'),
									'renderer' => array($this, 'render_artist')
							),
							'title' => array(
									'label' => ERPress2::__('Title')
							),
							'album' => array(
									'label' => ERPress2::__('Album'),
									'renderer' => array($this, 'render_album')
							),
							'year' => array(
									'label' => ERPress2::__('Publication'),
									'renderer' => array($this, 'render_publication_date')
							),
							'source' => array(
									'label' => ERPress2::__('Source'),
									'renderer' => array($this, 'render_source')
							),
							'previous' => array(
									'label' => ERpress2::__('Previous'),
									'renderer' => array($this, 'render_previous')
							)
					)
			);
		}
		
		$this->page_footer();
	}
	
	function render_position($value, $row) {
		return ($value == 11) ? "Flashback" : $value;
	}

	function render_artist($value, $row) {
		return '<a href="' . $row->website . '">' . $value . '</a>';
	}
	
	function render_album($value, $row) {
		return '<a href="' . $this->admin_link('albums', 'edit', array('id' => $row->album_id)) . '">' . $value . '</a>';
	}
	
	function render_publication_date($value, $row) {
		if (isset($row->month)) {
			return $row->month . ' / ' . $value;
		}
		else {
			return ($value <> 0) ? $value : '';
		}
	}
	
	function render_source($value, $row) {
		$link = $row->source_link;
		if ($link == '') $link = $row->al_source_link;
		return '<a href="' . $link . '">' . $value . '</a>';
	}

	function render_previous($value, $row) {
		global $wpdb;
		$sql = $wpdb->prepare('select e.*, t.title as track_title from ' . ERPress2::$episodes_table . ' e, ' . ERPress2::$tracks_table . ' t where t.artist_id = %d and e.id = t.episode_id and e.id <> %d order by publication asc', $row->artist_id, $row->episode_id);
		$previous = $wpdb->get_results($sql);
		$html = '';
		foreach($previous as $p) {
			$item = $p->name . ' (' . $p->publication . ')<br/>' . $p->track_title;
			if (!$p->archive) {
				$item = '<i><b>' . $item . '</b></i>';
			}
			$html .= '<p style="margin-bottom: 1px;">' . $item . '</p>';
		}	
		return $html;
	}
	
	function edit() {
		global $wpdb;
		
		$row = $wpdb->get_row($wpdb->prepare('select t.*, ar.name as artist, al.title as album, al.month, al.year, al.source_id as al_source_id, al.source_link as al_source_link from ' . ERPress2::$tracks_table . ' t left join ' . ERPress2::$albums_table . ' al on al.id = t.album_id, ' . ERPress2::$artists_table . ' ar where t.id = %d and ar.id = t.artist_id order by t.position asc', $_REQUEST['id']));

		if (($row->source_link == '') && ($row->al_source_link != '')) {
			$row->source_link = $row->al_source_link;
			$row->source_id = $row->al_source_id;
		}
		$this->page_header(ERPress2::__('Edit track'));
		$form = new ERPress2FormSummary($row);
		$form->render();
		$this->page_footer();
	}

	function submit() {
		global $wpdb;

		$query = $wpdb->prepare('select a.name as artist, t.title, a.website, a.twitter, a.facebook, e.name as episode_name from ' . ERPress2::$tracks_table . ' t, ' . ERPress2::$artists_table . ' a, ' . ERPress2::$episodes_table . ' e where t.id = %d and a.id = t.artist_id and e.id = t.episode_id', $_REQUEST['id']);
		$row = $wpdb->get_row($query);

		$this->page_header(ERPress2::__('Submit to AMPed'));
		$settings = (array) get_option('erpress2-settings');
		if (!isset($settings) || ($settings == null) || !isset($settings['url']) || !isset($settings['pubkey']) || !isset($settings['privkey'])) {
			echo 'The AMPed API settings were not found.';
		}
		else {
			$form = new ERPress2FormSubmitToAMPed($row);
			$form->render();
		}
		$this->page_footer();
	}
	
	function add() {
		$this->page_header(ERPress2::__('Add a track'));
		$form = new ERPress2FormSummary(null);
		$form->render('erpress2_add_track_');
		$this->page_footer();
	}
	
	function shownotes() {
		global $wpdb;
		
		$titres = $wpdb->get_results($wpdb->prepare('select t.*, ar.name as artist, ar.website, ar.twitter, ar.facebook, al.title as album, al.source_id, al.buy_link, al.year from ' . ERPress2::$tracks_table . ' t left join ' . ERPress2::$albums_table . ' al on al.id = t.album_id, ' . ERPress2::$artists_table . ' ar where t.episode_id = %d and ar.id = t.artist_id order by t.position asc', $_REQUEST['id']));
?>
<pre>
====================================================================================
<?php 
		$first = true;
		foreach($titres as $titre) {
			if (!$first) echo ', ';
			echo $titre->artist . ' : ' . $titre->title;
			$first = false;
		}
		echo "\n";
?>
====================================================================================
&lt;p&gt;&lt;/p&gt;
&lt;ul&gt;
<?php 
		$sources = Array();
		foreach($titres as $titre) {
			if ($titre->position == 11) break;
			if ($titre->website != '') {
				$artist = '&lt;a href="' . $titre->website . '"&gt;' . $titre->artist . '&lt;/a&gt;';
			}
			else {
				$artist = $titre->artist;
			}
			
			if ($titre->album != '') {
				$lien = '&lt;span style="font-style: oblique;"&gt;' . $titre->album . '&lt;/span&gt;';
				$sources[$titre->source_id] = true;
				if ($titre->buy_link != null) {
					$titre->buy_link = trim($titre->buy_link);
					if (substr($titre->buy_link, 0, 7) == "<a href") {
						//$lien = '&lt;a style="font-style: oblique;" ' . str_replace("&", "&amp;", str_replace('border="0" alt', 'alt', substr($titre->buy_link, 3)));
						$lien = '&lt;a style="font-style: oblique;" ' . str_replace("<", "&lt;", str_replace('border="0" alt', 'alt', substr($titre->buy_link, 3)));
					}
					else {
						$lien = '&lt;a style="font-style: oblique;" href="' . $titre->buy_link . '"&gt;' . $titre->album . '&lt;/a&gt;';
					}
				}
				if ($titre->year != '') {
					$lien .= ', ' . $titre->year;
				}
			}
			else {
				$lien = '';
			}
?>
	&lt;li&gt;<?php echo $artist; ?> - <?php echo $titre->title; ?><?php if ($lien != '') { echo ' (' . $lien . ')'; } ?>&lt;/li&gt;
<?php 
	}
?>
&lt;/ul&gt;

<?php
	foreach($titres as $titre) {
		if ($titre->position == 11) {
                        if ($titre->website != '') {
                                $artist = '&lt;a href="' . $titre->website . '"&gt;' . $titre->artist . '&lt;/a&gt;';
                        }
                        else {
                                $artist = $titre->artist;
                        }

                        if ($titre->album != '') {
                                $lien = '&lt;span style="font-style: oblique;"&gt;' . $titre->album . '&lt;/span&gt;';
                                $sources[$titre->source_id] = true;
                                if ($titre->buy_link != null) {
                                        $titre->buy_link = trim($titre->buy_link);
                                        if (substr($titre->buy_link, 0, 7) == "<a href") {
                                                //$lien = '&lt;a style="font-style: oblique;" ' . str_replace("&", "&amp;", str_replace('border="0" alt', 'alt', substr($titre->buy_link, 3)));
                                                $lien = '&lt;a style="font-style: oblique;" ' . str_replace("<", "&lt;", str_replace('border="0" alt', 'alt', substr($titre->buy_link, 3)));
                                        }
                                        else {
                                                $lien = '&lt;a style="font-style: oblique;" href="' . $titre->buy_link . '"&gt;' . $titre->album . '&lt;/a&gt;';
                                        }
                                }
                                if (($titre->year != '') && ($titre->year > 0)) {
                                        $lien .= ', ' . $titre->year;
                                }
                        }
                        else {
                                $lien = '';
                        }
?>
&lt;b&gt;Flashback&lt;/b&gt; : <?php echo $artist; ?> - <?php echo $titre->title; ?><?php if ($lien != '') { echo ' (' . $lien . ')'; } ?>
<?php
		}
	}
?>


&lt;p&gt;Contacts : Par mail à &lt;b&gt;info @ euterpia-radio . fr&lt;/b&gt;, &lt;a href="http://twitter.com/euterpiaradio"&gt;@euterpiaradio&lt;/a&gt; sur twitter, ou notre page &lt;a href="http://www.facebook.com/euterpiaradio"&gt;Facebook&lt;/a&gt;&lt;/p&gt;

&lt;p&gt;Sources : <?php
	$sql = 'select * from ' . ERPress2::$sources_table . ' where id in (';
	$first = true;
	foreach ($sources as $key => $value) { $sql .= ($first ? '' : ', ') . $key; $first = false; }
	$sql .= ') order by name asc';
	$srcs = $wpdb->get_results($sql);
	$first = true;
	foreach ($srcs as $src) {
		echo ($first ? '' : ', ') . '&lt;a href="' . $src->website . '"&gt;' . $src->name . '&lt;/a&gt;';
		$first = false;
	}
?>&lt;/p&gt;

&lt;p&gt;Fermeture musicale : PeerGynt Lobogris, &lt;a href="http://www.jamendo.com/fr/track/662845/consecuences-of-the-choice"&gt;Consecuences of the Choice&lt;/a&gt;&lt;/p&gt;
&lt;p&gt;Introduction et fermeture musicale : Josh Woodward, &lt;a href="http://www.jamendo.com/fr/track/761858/cheapskate-romantic-instrumental-version"&gt;Cheapskate Romantic&lt;/a&gt;&lt;/p&gt;

====================================================================================
<?php 
		$first = true;
		foreach($titres as $titre) {
			if (!$first) echo ',';
			echo $titre->artist;
			$first = false;
		}
		echo "\n";
?>
====================================================================================
<?php
	$first = true;
	foreach($titres as $titre) {
		if ($titre->twitter != '') {
			if (!$first) echo ' ';
			$first = false;
			echo $titre->twitter;
		}
	}
	echo "\n";
?>
====================================================================================
<?php
	$first = true;
	echo 'update wp_erpress2_artists set mm = now() where twitter in (';
	foreach($titres as $titre) {
		if ($titre->twitter != '') {
			if (!$first) echo ', ';
			$first = false;
			echo '"' . $titre->twitter . '"';
		}
	}
	echo ")\n";
?>
====================================================================================
<?php
	$first = true;
	foreach($titres as $titre) {
		if ($titre->facebook != '') {
			if (!$first) echo "\n";
			$first = false;
			echo $titre->artist . ' : <a href="' . $titre->facebook . '">' . $titre->facebook . '</a>';
		}
	}
	echo "\n";
	?>
====================================================================================
<?php
	$sources = $wpdb->get_results($wpdb->prepare('select distinct(s.id), s.name from ' . ERPress2::$tracks_table . ' t, ' . ERPress2::$albums_table . ' al, ' . ERPress2::$sources_table . ' s where t.episode_id = %d and al.id = t.album_id and s.id = al.source_id', $_REQUEST['id']));
	foreach ($sources as $source) {
		echo $source->name . ' : ';
		$titres = $wpdb->get_results($wpdb->prepare('select ar.name as artist, t.title from ' . ERPress2::$tracks_table . ' t, ' . ERPress2::$albums_table . ' al, ' . ERPress2::$artists_table . ' ar where al.source_id = %d and t.album_id = al.id and t.episode_id = %d and ar.id = t.artist_id', $source->id, $_REQUEST['id']));
		$first = true;
		foreach ($titres as $titre) {
			if (!$first) echo ', ';
			echo $titre->artist . ' - ' . $titre->title;
			$first = false;
		}
		echo "\n";
	}
?>
</pre>
<?php 
	}
}

