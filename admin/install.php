<?php

defined('ABSPATH') or die('Hnng~');

function magforest_install() {
/*	global $wp_rewrite; 
	$wp_rewrite->flush_rules();
	update_option('magforest_flushed',false);*/
	if(!get_option('magforest_template')) {
		update_option('magforest_template',"{DESCRIPTION}\r\n<br />[gallery]\r\n<br />\r\n<center>{DOWNLOAD_LINK}<br />{PRINT_LINK}</center>\r\n<br />");
	}

	if(!(get_option('magforest_true_sync_interval', null) !== null)) {
		update_option('magforest_true_sync_interval','daily');
	}
}