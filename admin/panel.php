<?php

defined('ABSPATH') or die('Hnng~');

function magforest_generate_admin_menus() {
	add_action( 'admin_init', 'magforest_panel_action');
	add_action( 'admin_menu', 'magforest_menu' );
	add_action( 'admin_init', 'magforest_register_settings' );
	
}

function magforest_menu() {
	add_menu_page( 'Bulk Import', 'Magforest Affiliate', 'manage_options', 'magforest_bulk', 'magforest_bulk_page','data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xIFRpbnkvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEtdGlueS5kdGQiPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGJhc2VQcm9maWxlPSJ0aW55IiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayINCgkgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIzMjcuMDE0cHgiIGhlaWdodD0iMzExLjk1OXB4IiB2aWV3Qm94PSI4MjMuMzQ5IDE4MS44NTQgMzI3LjAxNCAzMTEuOTU5IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiMyRTJDMjgiIGQ9Ik04NzguMzU0LDQ0MC43ODRsMzIuMTI5LDMxLjIwMmM0LjczNCw0LjYwNCwxNC42NTktMi45NjIsOC44MDQtOC44NzJsLTQwLjkzMy00MS4yOTN2LTE0Ljg5bDMyLjEyOSwzMS4yNDENCgljNC42MDEsNC40NzMsMTQuODMzLTIuNzg5LDguODA0LTguODcxbC00MC45MzMtNDEuMjk0di0xNC44OWwzMi4xMjksMzEuMjQxYzQuNzM0LDQuNjA0LDE0LjY1OS0yLjk2Myw4LjgwNC04Ljg3MWwtNDAuOTMzLTQxLjMwMw0KCXYtMTQuODk5bDMyLjEyOSwzMS4yNDJjNC43MzQsNC42MDQsMTQuNjU5LTIuOTYzLDguODA0LTguODcybC00MC45MzMtNDEuMjkzYy00LjI0Ni00LjI4NS04LjMwMS00LjUwOS0xMi42OTItMC4wNDhsLTQwLjE3OSw0MC43OTgNCgljLTYuNTE1LDYuNjE1LDMuNDQ0LDE0LjMxNyw5LjA5Nyw4Ljc1NmwzMS4wODItMzAuNTgzbDAuMDA5LDE0Ljg3NWwtNDAuMTg4LDQwLjc4NGMtNi41MTUsNi42MTQsMy40NDQsMTQuMzE2LDkuMDk3LDguNzU0DQoJbDMxLjA4Mi0zMC41ODJsMC4wMDksMTQuODlsLTQwLjE4OCw0MC43OTljLTYuNTE1LDYuNjE0LDMuNDQ0LDE0LjMxNiw5LjA5Nyw4Ljc1NWwzMS4wODItMzAuNTgzbDAuMDA5LDE0Ljg5bC00MC4xODgsNDAuNzk5DQoJYy02LjUxNSw2LjYxNCwzLjQ0NCwxNC4zMTYsOS4wOTcsOC43NTRsMzEuMDgyLTMwLjU5MWwwLjAxOCw0Ni45NjdjMCw3Ljk0NCwxMi43MSw4LjA4OCwxMi43MSwwTDg3OC4zNTQsNDQwLjc4NEw4NzguMzU0LDQ0MC43ODR6DQoJIE05OTIuOTg5LDI1Mi43MDFsNDAuOTMxLDQxLjI5M2M1LjY1Nyw1LjcwOC0zLjgzMywxMy43MDUtOC44MDEsOC44NzJsLTMyLjEyOS0zMS4yNDN2MTQuODk5bDQwLjkzMSw0MS4zMDMNCgljNS42NTcsNS43MDgtMy44MzMsMTMuNzA0LTguODAxLDguODcybC0zMi4xMjktMzEuMjQydjE0Ljg5OGw0MC45MzEsNDEuMzAzYzUuNjU3LDUuNzA4LTMuODMzLDEzLjcwNS04LjgwMSw4Ljg3MmwtMzIuMTI5LTMxLjI0Mg0KCXYxNC44OTlsNDAuOTMxLDQxLjMwM2M1LjY1Nyw1LjcwNy0zLjgzMywxMy43MDMtOC44MDEsOC44NzFsLTMyLjEyOS0zMS4yNDF2MTQuODlsNDAuOTMxLDQxLjI5NA0KCWM1LjY1Nyw1LjcwNy0zLjgzMywxMy43MDQtOC44MDEsOC44NzFsLTMyLjEyOS0zMS4yNDF2MTQuODlsNDAuOTMxLDQxLjI5M2M1LjY1Nyw1LjcwOC0zLjgzMywxMy43MDUtOC44MDEsOC44NzJsLTMyLjEyOS0zMS4yMDINCglsMC4wMzQsNDcuMDE1YzAsOC4wODgtMTIuNzExLDcuOTQ0LTEyLjcxMSwwbC0wLjAxNy00Ni45NjdsLTMxLjA4MywzMC41OTFjLTUuNjUxLDUuNTYyLTE1LjYxLTIuMTQtOS4wOTgtOC43NTRsNDAuMTg4LTQwLjc5OQ0KCWwtMC4wMDgtMTQuODlsLTMxLjA4MywzMC41ODNjLTUuNjUxLDUuNTYxLTE1LjYxLTIuMTQyLTkuMDk4LTguNzU2bDQwLjE4OC00MC43OThsLTAuMDA4LTE0Ljg5bC0zMS4wODksMzAuNTgNCgljLTUuNjUsNS41NjItMTUuNjA5LTIuMTQtOS4wOTgtOC43NTRsNDAuMTg4LTQwLjc4NGwtMC4wMDgtMTQuODc1bC0zMS4wODMsMzAuNTgzYy01LjY1LDUuNTYxLTE1LjYwOS0yLjE0MS05LjA5OC04Ljc1Ng0KCWw0MC4xODgtNDAuNzgzbC0wLjAwOC0xNC44NzVsLTMxLjA4MywzMC41ODJjLTUuNjUsNS41NjItMTUuNjA5LTIuMTQtOS4wOTgtOC43NTRsNDAuMTg4LTQwLjc4NGwtMC4wMDgtMTQuODc1bC0zMS4wODMsMzAuNTgzDQoJYy01LjY1LDUuNTYtMTUuNjA5LTIuMTQxLTkuMDk4LTguNzU1bDQwLjE4MS00MC43OTlDOTg0LjQ3NiwyNDguNDA5LDk4OC44MjksMjQ4LjUwNCw5OTIuOTg5LDI1Mi43MDFMOTkyLjk4OSwyNTIuNzAxeg0KCSBNMTEwNy42MjMsMjg2LjUyM2w0MC45MzIsNDEuMzAzYzUuNjU3LDUuNzA4LTMuODMzLDEzLjcwNC04LjgwNCw4Ljg3MmwtMzIuMTI4LTMxLjI0MnYxNC44OThsNDAuOTMyLDQxLjMwMw0KCWM1LjY1Nyw1LjcwOC0zLjgzMywxMy43MDUtOC44MDQsOC44NzJsLTMyLjEyOC0zMS4yNDJ2MTQuODk5bDQwLjkzMiw0MS4zMDNjNS42NTcsNS43MDctMy44MzMsMTMuNzAzLTguODA0LDguODcxbC0zMi4xMjgtMzEuMjQxDQoJdjE0Ljg5bDQwLjkzMiw0MS4yOTNjNS42NTcsNS43MDctMy44MzMsMTMuNzA0LTguODA0LDguODcxbC0zMi4xMjgtMzEuMjQxdjE0Ljg5bDQwLjkzMiw0MS4yOTMNCgljNS45NzIsNi4wMjMtNC4zOTMsMTMuMTYtOC44MDQsOC44NzJsLTMyLjEyOC0zMS4yMDJsMC4wMzUsNDcuMDE1YzAsOC4wODgtMTIuNzEsNy45NDQtMTIuNzEsMGwtMC4wMTktNDYuOTY3bC0zMS4wODIsMzAuNTkxDQoJYy01LjY1MSw1LjU2Mi0xNS42MDktMi4xNC05LjA5Ny04Ljc1NGw0MC4xODgtNDAuNzk5bC0wLjAxLTE0Ljg5bC0zMS4wODIsMzAuNTgzYy01LjY1MSw1LjU2MS0xNS42MDktMi4xNDItOS4wOTctOC43NTUNCglsNDAuMTg4LTQwLjc5OWwtMC4wMS0xNC44OWwtMzEuMDgyLDMwLjU4MmMtNS42NTEsNS41NjItMTUuNjA5LTIuMTQtOS4wOTctOC43NTRsNDAuMTg4LTQwLjc4NGwtMC4wMDktMTQuODc1bC0zMS4wODIsMzAuNTgzDQoJYy01LjY1Miw1LjU2MS0xNS42MDktMi4xNDEtOS4wOTctOC43NTZsNDAuMTg4LTQwLjc4M2wtMC4wMDktMTQuODc1bC0zMS4wODIsMzAuNTgyYy01LjY1Miw1LjU2Mi0xNS42MS0yLjE0LTkuMDk3LTguNzU0DQoJbDQwLjE4OC00MC43ODRsLTAuMDA5LTE0Ljg3NWwtMzEuMDgyLDMwLjU4M2MtNS42NTIsNS41Ni0xNS42MS0yLjE0MS05LjA5OC04Ljc1NWw0MC4xODQtNDAuNzkybC0wLjAwNS0xNC44NjdsLTMxLjA4MiwzMC41ODINCgljLTUuNjUyLDUuNTYxLTE1LjYxLTIuMTQtOS4wOTgtOC43NTVsNDAuMTg0LTQwLjc5MWwtMC4wMDUtMTQuODY3bC0zMS4wODIsMzAuNTgyYy01LjY1Miw1LjU2MS0xNS42MS0yLjE0LTkuMDk4LTguNzU1DQoJbDQwLjE3OC00MC43OThjNC4xODEtNC4yNDQsOC41MzMtNC4xNDksMTIuNjkzLDAuMDQ4bDQwLjkzMiw0MS4yOTRjNS42NTcsNS43MDgtMy44MzIsMTMuNzA1LTguODA0LDguODdsLTMyLjEyOC0zMS4yNHYxNC45MDMNCglsMjAuNDY2LDIwLjY1bDIwLjQ2NiwyMC42NDdjNS42NTcsNS43MDgtMy44MzIsMTMuNzA1LTguODA0LDguODcxbC0zMi4xMjgtMzEuMjQxVjI1Mi43bDIwLjQ2NiwyMC42NTFsMjAuNDY2LDIwLjY0Nw0KCWM1LjY1Nyw1LjcwOC0zLjgzMiwxMy43MDUtOC44MDQsOC44NzJsLTMyLjEyOC0zMS4yNDN2MTQuODk2SDExMDcuNjIzeiIvPg0KPC9zdmc+DQo=' );
	add_submenu_page( 'magforest_bulk', 'Bulk Import', 'Bulk Import', 'manage_options', 'magforest_bulk', 'magforest_bulk_page' );
	add_submenu_page( 'magforest_bulk', 'Scheduled Post Sync', 'Auto Sync', 'manage_options', 'magforest_schedule', 'magforest_automation_page' );
	add_submenu_page( 'magforest_bulk', 'Magforest Magazine Search', 'Search', 'manage_options', 'magforest', 'magforest_browse_page' );
	add_submenu_page( 'magforest_bulk', 'Magforest Affiliate Settings', 'Settings', 'manage_options', 'magforest_setup', 'magforest_setup_page' );
	
	
}

function magforest_register_settings() {
	register_setting( 'magforest_setup', 'magforest_affiliate_id' );
	register_setting( 'magforest_setup', 'magforest_true_sync_interval' );
		register_setting( 'magforest_setup', 'magforest_template' );
}


function magforest_create_template_post($product) {
		$insert = wp_insert_post(array(
			'post_content'=>magforest_template_content($product),
			'post_title'=>$product->info->title,
			'post_status'=>'draft',
			'post_type'=>'post',
			'meta_input'=>array(
				'magforest_link'=>magforest_link_from_slug($product->info->slug),
				'magforest_id'=>$product->info->id
			)
		));
		
		if($insert) {
			$thumb = magforest_set_featured_image($product->info->thumbnail, $insert);
				// if we have a gallery tag, exclude the inserted featured image...
				
				$my_post = array(
				      'ID'           => $insert,
				      'post_content' => str_ireplace("[gallery", "[gallery exclude=\"$thumb\"", magforest_template_content($product))
				  );
				
				// Update the post into the database
				  wp_update_post( $my_post );
				  
			foreach($product->previews as $preview) {
					magforest_attach_file($preview, $insert);
			}
		}
		
		return $insert;
}



function magforest_panel_action() {

	if( $_GET['action'] === 'template' ) {
		magforest_do_template();
	} else  if ($_GET['action'] === 'bulk') {
		magforest_do_bulk();
	} else if ($_GET['action'] === 'bulk_ajax') {
		magforest_do_bulk_ajax();
	}else if ($_GET['action'] === 'mktask') {
		magforest_do_mktask();
	}else if ($_GET['action'] === 'rmtask') {
		magforest_do_rmtask();
	} else if ($_GET['action'] === 'truesync') {
		magforest_do_truesync();
	}
	
}
