<?php

require_once(ABSPATH . 'wp-includes/class-IXR.php');

class AMPedClient extends IXR_Client {

	function AMPedClient() {
		$settings = (array) get_option('erpress2-settings');
		$url = $settings['url'];

		parent::IXR_Client($url);
	}

	function query() {
		$settings = (array) get_option('erpress2-settings');
		$pubkey = $settings['pubkey'];
		$privkey = $settings['privkey'];

		$o = new stdClass();
		$o->apiKey = $pubkey;
		$o->timestamp = new IXR_Date(time());
		$o->apiSig = md5($o->timestamp->getIso() . '|' . $privkey);

		$args = func_get_args();
		$method = array_shift($args);

		foreach ($args as $arg) {
			if (is_object($arg)) {
				$vars = get_object_vars($arg);
				foreach ($vars as $key => $value) {
					$o->$key = $value;
				}
			}
		}

		parent::query($method, $o);
	}
}

?>
