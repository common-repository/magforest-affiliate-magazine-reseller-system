<?php

defined('ABSPATH') or die('Hnng~');


function magforest_get_affiliate_id() {
	return trim(get_option('magforest_affiliate_id'));
}

function magforest_get_affiliate_link($url) {
	$url = trailingslashit($url);
	$ref = magforest_get_affiliate_id();
	if(strlen($ref) == 0) { return $url; }
	return trim($url).(strpos($url, "?") !== false ? "&" : "?").'ref='.$ref;
}

function magforest_get_affiliate_print_link($magazine_id) {
	return 'https://www.magforest.com/print-mag.php?id='.intval($magazine_id).'&refer='.urlencode(magforest_get_affiliate_id());
}