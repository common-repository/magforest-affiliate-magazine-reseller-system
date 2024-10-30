<?php

defined('ABSPATH') or die('Hnng~');


 
function magforest_cron_add_weekly( $schedules ) {
 	// Adds once weekly to the existing schedules.
 	$schedules['weekly'] = array(
 		'interval' => 604800,
 		'display' => __( 'Once a Week' )
 	);
 	return $schedules;
}
add_filter( 'cron_schedules', 'magforest_cron_add_weekly' );

function magforest_crons() {
	$hook = 'magforest_rescan';
	$crons = _get_cron_array();
	$result = array();
	if ( empty($crons) )
		return $result;
	foreach ( $crons as $timestamp => $cron ) {
		if ( isset( $cron[$hook] ) )
			foreach($cron[$hook] as $mdhash => $schedtask) {
				$schedtask['hash'] = $mdhash;
				$schedtask['ts'] = $timestamp;
				array_push($result, $schedtask);
			}
	}
	return $result;
}

function magforest_get_next_cron_execution($timestamp) {

	if ($timestamp - time() <= 0)
		return ('At next page refresh');

	return 'In '.human_time_diff( current_time('timestamp'), $timestamp ).'<br>'.date("d.m.Y H:i:s", $timestamp);

}

function magforest_clear_all_crons() {
	magforest_true_sync_dequeue();
	$hook = 'magforest_rescan';
    $crons = _get_cron_array();
    if ( empty( $crons ) ) {
        return;
    }
    foreach( $crons as $timestamp => $cron ) {
        if ( ! empty( $cron[$hook] ) )  {
            unset( $crons[$timestamp][$hook] );
        }

        if ( empty( $crons[$timestamp] ) ) {
            unset( $crons[$timestamp] );
        }
    }
    _set_cron_array( $crons );
}

function magforest_cron_task($target_category, $is_draft, $create_tags, $create_cats, $append_tags, $tag, $publisher, $category) {
	$task = array();
	$task['target_category']	= $target_category;
	$task['is_draft']			= $is_draft;
	$task['create_tags']		= $create_tags;
	$task['create_cats']		= $create_cats;
	$task['append_tags']		= $append_tags;
	$task['import_tag']			= $tag;
	$task['import_publisher']	= $publisher;
	$task['import_category']	= $category;
	return $task;
}

function magforest_queue_task($interval, $target_category, $is_draft, $create_tags, $create_cats, $append_tags, $tag, $publisher, $category) {
	$task = magforest_cron_task($target_category, $is_draft, $create_tags, $create_cats, $append_tags, $tag, $publisher, $category);

	wp_schedule_event( time(), $interval, 'magforest_rescan', array($task) );
}

function magforest_dequeue_task($hash) {
	$hook = 'magforest_rescan';
    $crons = _get_cron_array();
    if ( empty( $crons ) ) {
        return;
    }
    foreach( $crons as $timestamp => $cron ) {
    
    	if ( isset( $cron[$hook][$hash] ) ) {
	    	unset( $crons[$timestamp][$hook][$hash] );
    	}
        if ( empty( $cron[$hook] ) )  {
            unset( $crons[$timestamp][$hook] );
        }

        if ( empty( $crons[$timestamp] ) ) {
            unset( $crons[$timestamp] );
        }
    }
    _set_cron_array( $crons );
}

function magforest_do_rescan($task) {
	if (defined('WP_DEBUG') && true === WP_DEBUG) echo "Magforest rescan...";
	// unpack the task object
	$target_category	= $task['target_category'];
	$is_draft			= $task['is_draft'];
	$create_tags		= $task['create_tags'];
	$create_cats		= $task['create_cats'];
	$append_tags		= $task['append_tags'];
	$tag				= $task['import_tag'];
	$publisher			= $task['import_publisher'];
	$category			= $task['import_category'];
	
	// find mags that we have already
	$exclude = join(',', magforest_get_have_ids());
	wp_set_auth_cookie(1);
	wp_set_current_user(1);

	// get new mags to import
	$mags = magforest_get_all_products('',$tag,$category,$publisher,$exclude,'');
	// iterate over mags
	foreach($mags as $product) {
			$cat_str = ''.$target_category;
				if($create_cats) {
					// copy categories
					if (defined('WP_DEBUG') && true === WP_DEBUG) echo "Copy categories...";
					foreach($product->info->category as $src_cat) {
						$category_id = get_cat_ID($src_cat->name);
						if(!$category_id) {
							$category_id = wp_create_category($src_cat->name);
						}
						if(strlen($cat_str) > 0) $cat_str .= ',';
						$cat_str .= $category_id;
					}
					if (defined('WP_DEBUG') && true === WP_DEBUG) echo "$cat_str";
				}
			
			$insert = wp_insert_post(array(
				'post_content'=>magforest_template_content($product),
				'post_title'=>$product->info->title,
				'post_status'=>($is_draft ? 'draft':'publish'),
				'comment_status'=>'closed',
				'post_type'=>(/*$import_as_links ? 'magforest_affiliate' : */'post'),
				'meta_input'=>array(
					'magforest_link'=>magforest_link_from_slug($product->info->slug),
					'magforest_id'=>$product->info->id
				),
				'tax_input' => array( 'category' => $cat_str )
			));
			if($insert) {
				if (defined('WP_DEBUG') && true === WP_DEBUG) echo "Insert ok: ".$product->info->title;
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
				} //*/
				
				
				$tag_str = ''.$append_tags;
				if($create_tags) {
					// copy tags
					foreach($product->info->tags as $src_tag) {
						if(strlen($tag_str) > 0) $tag_str .= ',';
						$tag_str .= $src_tag->slug;
					}
				}
				wp_set_post_tags($insert,$tag_str,true);
				wp_set_post_categories($insert, explode(",", $cat_str), true);
			} else {
				if (defined('WP_DEBUG') && true === WP_DEBUG) { echo "Insert error:"; var_dump($product); }
			}
	}
	

}

add_action('magforest_rescan','magforest_do_rescan');

function magforest_true_sync_schedule() {
	return wp_get_schedule( 'magforest_true_sync' );
}

function magforest_true_sync_next() {
	return wp_next_scheduled('magforest_true_sync');
}

function magforest_true_sync_dequeue() {
	wp_clear_scheduled_hook('magforest_true_sync' );
}

function magforest_true_sync_enqueue($when) {
	wp_schedule_event( time(), $when, 'magforest_true_sync' );
}

function magforest_true_sync_do() {
	// find mags that we have already
	$have =  magforest_get_have_ids();
	
	$dead = magforest_find_dead($have);
	
	
	foreach($dead as $magid) {
		$cascade = get_posts(array(
			'meta_query' => array(
				array(
					'key' => 'magforest_id',
					'value' => $dead
				),
			),
		));
		
		foreach($cascade as $post) {
			wp_trash_post($post->ID);
		}
	}
}
add_action('magforest_true_sync','magforest_true_sync_do');
