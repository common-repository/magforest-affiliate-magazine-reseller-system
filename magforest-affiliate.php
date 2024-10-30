<?php
/*
Plugin Name: Magforest Affiliate
Plugin URI:  http://magforest.com
Description: Earn money by hotlinking to Magforest magazines!
Version:     2.2.4
Author:      Genjitsu Labs for Magforest
Author URI:  http://genjit.su/
*/

defined('ABSPATH') or die('Hnng~');


function magforest_get_asset_url($file) {
	return plugins_url('/assets/'.$file, __FILE__);
}

function magforest_version() {
	return "2.2.4";
}


function magforest_affiliate_init() {
	require_once('admin/cron.php');
	require_once('etc/settings.php');
	require_once('etc/magforest_api.php');
	// require_once('frontend/affiliate_post.php');
	
	    	
		
	require_once('frontend/shortcode.php');
	if( is_admin() ) {
		require_once('admin/warnings.php');
		
		if( ! class_exists( 'WP_List_Table' ) ) {
		    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		require_once('admin/magazine.table.php');
		require_once('admin/cron.table.php');
		
		require_once('admin/pages/importer.php');
		require_once('admin/pages/browse.php');
		require_once('admin/pages/settings.php');
		require_once('admin/pages/bulk.php');
		require_once('admin/pages/auto.php');
		
		require_once('admin/panel.php');
		

		magforest_generate_admin_menus();
	}	
	require_once('admin/install.php');
	register_activation_hook( __FILE__, 'magforest_install' );
	register_deactivation_hook(__FILE__, 'magforest_clear_all_crons');
}

magforest_affiliate_init();