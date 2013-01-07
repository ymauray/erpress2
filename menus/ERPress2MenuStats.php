<?php

class ERPress2MenuStats extends WPFPage {
	
	function __construct($plugin) {
		parent::__construct($plugin, array('detail'));
	}
	
	function default_page() {
		$statsdb = new wpdb('apachelogs', 'apachelogs', 'apachelogs', 'localhost');
?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div>
<h2>Stats</h2>
<?php
		$this->display_table(
				array(
						'data' => $statsdb->get_results('select today.episode, ifnull(today.count, 0) as today, ifnull(today.supertotal, 0) as supertotaltoday, ifnull(yesterday.count, 0) as yesterday, ifnull(yesterday.supertotal, 0) as supertotalyesterday, ifnull(twodaysago.count, 0) as twodaysago, ifnull(twodaysago.supertotal, 0) as supertotaltwodaysago, ifnull(threedaysago.count, 0) as threedaysago, ifnull(threedaysago.supertotal, 0) as supertotalthreedaysago from (select *, count(*) as count, sum(total) as supertotal from stats group by episode) today left join (select *, count(*) as count, sum(total) as supertotal from stats where timestamp_date < date(now()) group by episode) yesterday on yesterday.episode = today.episode left join (select *, count(*) as count, sum(total) as supertotal from stats where timestamp_date < date_sub(date(now()), interval 1 day) group by episode) twodaysago on twodaysago.episode = today.episode left join (select *, count(*) as count, sum(total) as supertotal from stats where timestamp_date < date_sub(date(now()), interval 2 day) group by episode) threedaysago on threedaysago.episode = today.episode having today != yesterday or yesterday != twodaysago order by today.episode asc'),
						'params' => array(
								'pagination' => false,
								'data_type' => 'rows',
								'row_builder' => array($this, 'row_builder')
						)
				),
				'episode',
				array(
						'episode' => array(
								'label' => ERPress2::__('Episode')
						),
						'firsttwoweeks' => array(
								'label' => ERPress2::__('First 2 weeks')
						),
						'twodaysago' => array(
								'label' => ERPress2::__('2 days ago')
						),
						'yesterday' => array(
								'label' => ERPress2::__('Yesterday')
						),
						'today' => array(
								'label' => ERPress2::__('Today')
						),
						'total' => array(
								'label' => ERPress2::__('Total')
						)
				)
		);
?>
</div>
<?php
	}
	
	function row_builder($row) {
		global $wpdb;
		
		$statsdb = new wpdb('apachelogs', 'apachelogs', 'apachelogs', 'localhost');
		
		$episode = $wpdb->get_row($wpdb->prepare('select * from ' . ERPress2::$episodes_table . ' where name = %s', $row->episode));
		if ($episode != null) {
			$sql = $statsdb->prepare('select *, count(*) as count from stats where episode = %s and timestamp_date >= %s and timestamp_date < date_add(%s, interval 14 day) group by episode', $episode->name, $episode->publication, $episode->publication);
			ERPress2::_log('** '. $sql . ' **');
			$r = $statsdb->get_row($sql);
			$row->firsttwoweeks = $r->count;
		}
		else {
			$row->firsttwoweeks = 'no data';
		}

		//$row->total = '<a href="' . $this->action_link('detail', array('range' => 'all')) . '"' . $row->today . '</a>';
		$row->total = '<a href="' . $this->admin_link(null, 'detail', array('episode' => $row->episode, 'range' => 'all')) . '">' . $row->today . '</a> (' . $row->supertotaltoday . ')';
		$row->today = '<a href="' . $this->admin_link(null, 'detail', array('episode' => $row->episode, 'range' => 'today')) . '">' . '+' . ($row->today - $row->yesterday) . '</a> (' . ($row->supertotaltoday - $row->supertotalyesterday) . ')';
		$row->yesterday = '<a href="' . $this->admin_link(null, 'detail', array('episode' => $row->episode, 'range' => 'yesterday')) . '">' . '+' . ($row->yesterday - $row->twodaysago) . '</a> (' . ($row->supertotalyesterday - $row->supertotaltwodaysago) . ')';
		$row->twodaysago = '<a href="' . $this->admin_link(null, 'detail', array('episode' => $row->episode, 'range' => 'twodaysago')) . '">' . '+' . ($row->twodaysago - $row->threedaysago) . '</a> (' . ($row->supertotaltwodaysago - $row->supertotalthreedaysago) . ')';
	}
	
	function detail() {
		$statsdb = new wpdb('apachelogs', 'apachelogs', 'apachelogs', 'localhost');
?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div>
<h2>Détail</h2>
<?php
		$sql = $statsdb->prepare('select remote_host, city, region, countrycode, country, timestamp_date, timestamp_time from stats where episode = %s', $_REQUEST['episode']);
		if (isset($_REQUEST['range'])) {
			if ($_REQUEST['range'] == 'today') {
				$sql .= $statsdb->prepare(' and timestamp_date >= date(now())');
			}
			elseif ($_REQUEST['range'] == 'yesterday') {
				$sql .= $statsdb->prepare(' and timestamp_date >= date_sub(date(now()), interval 1 day) and timestamp_date < date(now())');
			}
			elseif ($_REQUEST['range'] == 'twodaysago') {
				$sql .= $statsdb->prepare(' and timestamp_date >= date_sub(date(now()), interval 2 day) and timestamp_date < date_sub(date(now()), interval 1 day)');
			}
		}
		$sql .= ' order by time_stamp desc';
		$this->display_table(
				array(
						'data' => $statsdb->get_results($sql),
						'params' => array(
								'pagination' => false,
								'data_type' => 'rows'
						)
				),
				'remote_host',
				array(
						'city' => array(
								'label' => ERPress2::__('City')
						),
						'region' => array(
								'label' => ERPress2::__('Region')
						),
						'countrycode' => array(
								'label' => ERPress2::__('Country code')
						),
						'country' => array(
								'label' => ERPress2::__('Country')
						),
						'remote_host' => array(
								'label' => ERPress2::__('Remote host')
						),
						'timestamp_date' => array(
								'label' => ERPress2::__('Date')
						),
						'timestamp_time' => array(
								'label' => ERPress2::__('Time')
						)
				)
		);
?>
</div>
<?php 
	}
}
?>
