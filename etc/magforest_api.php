<?php

defined('ABSPATH') or die('Hnng~');

function magforest_root_api_url($endpoint) {
	return "https://www.magforest.com/edd-api/".$endpoint;
}

function magforest_get($endpoint, $params = false) {

	$url = magforest_call_url_to_get($endpoint, $params);
	$ch = curl_init( $url );
	
    
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if($params) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	}

	//var_dump($url); die();
	
	$response = curl_exec($ch);
	//var_dump($response);
	curl_close($ch);
	
	// This will remove unwanted characters.
	// Check http://www.php.net/chr for details
	for ($i = 0; $i <= 31; ++$i) { 
	    $response = str_replace(chr($i), "", $response); 
	}
	$response = str_replace(chr(127), "", $response);
	
	// This is the most common part
	// Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
	// here we detect it and we remove it, basically it's the first 3 characters 
	if (0 === strpos(bin2hex($response), 'efbbbf')) {
	   $response = substr($response, 3);
	}

	return json_decode(($response));
}

function magforest_call_url_to_get($endpoint, $params = false) {
	$url = magforest_root_api_url($endpoint);
	
	//if($params)$url .= "?".http_build_query($params, '','&');
	
	return $url;
}

function magforest_find_dead($ids) {
	return magforest_get('truesync', array('check'=>implode(',',$ids)))->dead;
}

/*
function magforest_call_url_to_get_all($search = '', $tag = '', $category = '', $publisher = '', $exclude = '', $include='') {
	return magforest_call_url_to_get('products',array('roll_all'=>'1', 's'=>$search, 'tag'=>$tag, 'category'=>$category, 'author'=>$publisher, 'exclude'=>$exclude, 'include'=>$include));
}
*/

function magforest_get_all_products($search = '', $tag = '', $category = '', $publisher = '', $exclude = '', $include='') {
	return magforest_get('products',array('roll_all'=>'1', 's'=>$search, 'tag'=>$tag, 'category'=>$category, 'author'=>$publisher, 'exclude'=>$exclude, 'include'=>$include))->products;
}

function magforest_get_products($page = 1, $search = '') {
	return magforest_get('products', array('page' => $page, 's' => $search))->products;
}

function magforest_get_count($search = '') {
	return magforest_get('products',array('s' => $search))->count;
}

function magforest_get_meta_values( $key = '', $type = 'post' ) {

    global $wpdb;

    if( empty( $key ) )
        return;

    $r = $wpdb->get_col( $wpdb->prepare( "
        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s' 
        AND p.post_type = '%s'
        
    ", $key,  $type ) );

    return $r;
}

function magforest_get_have_ids() {
	return magforest_get_meta_values( 'magforest_id' );
}

global $mf_tag_cache;
$mf_tag_cache  =array();
function magforest_get_tags() {
	global $mf_tag_cache;
	if(!$mf_tag_cache) {
		$mf_tag_cache = magforest_get('tags')->tags;
	}
	return $mf_tag_cache;
}

function magforest_tag_with_id($id) {
	$tags = magforest_get_tags();
	foreach($tags as $tag) {
		if($tag->term_id == $id) return $tag;
	}
	return false;
}

global $mf_publisher_cache;
$mf_publisher_cache  =array();
function magforest_get_publishers() {
	global $mf_publisher_cache;
	if(!$mf_publisher_cache) {
		$mf_publisher_cache = magforest_get('publishers')->publishers;
	}
	return $mf_publisher_cache;
}

function magforest_publisher_with_id($id) {
	$pubs = magforest_get_publishers();
	foreach($pubs as $pub) {
		if($pub->ID == $id) return $pub;
	}
	return false;
}

global $mf_categories_cache;
$mf_categories_cache  =array();
function magforest_get_categories() {
	global $mf_categories_cache;
	if(!$mf_categories_cache) {
		$mf_categories_cache = magforest_get('categories')->categories;
	}
	return $mf_categories_cache;
}

function magforest_category_with_id($id) {
	$cats = magforest_get_categories();
	foreach($cats as $cat) {
		if($cat->term_id == $id) return $cat;
	}
	return false;
}

global $mf_product_cache;
$mf_product_cache = array();

function magforest_get_product($id) {
	global $mf_product_cache;
	if(!$mf_product_cache[$id])  $mf_product_cache[$id] = magforest_get('products',  array('product'=>$id))->products[0];
	return $mf_product_cache[$id];
}

function magforest_link_from_slug($slug) {
	return "https://www.magforest.com/downloads/".trim($slug);
}

function magforest_link_from_id($id) {
	return "https://www.magforest.com/?post_type=download&p=".intval($id);
}

function magforest_attach_file( $image_url, $post_id ) {
	$upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = uniqid() . basename($image_url);
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
	$title = get_the_title($post_id);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => $title,
        'post_content' => $title,
		'post_excerpt' => $title,
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
	update_post_meta($attach_id, '_wp_attachment_image_alt', $title);
    return $attach_id;
}

function magforest_set_featured_image( $image_url, $post_id  ){
    $attach_id = magforest_attach_file( $image_url, $post_id );
    $res2= set_post_thumbnail( $post_id, $attach_id );
    return $attach_id;
}


function magforest_template_content($product) {
	$ret = get_option('magforest_template');
	$ret = str_replace('{DESCRIPTION}', $product->info->content, $ret);
	$ret = str_replace('{DOWNLOAD_LINK}', '[magforest_link id="{PRODUCT_ID}"]'."<img src=\"".esc_attr(magforest_get_asset_url("download-now-button.png"))."\" alt=\"Download now on Magforest\" />"."[/magforest_link]", $ret);
	if($product->printable) {
		$ret = str_replace('{PRINT_LINK}', '[magforest_print id="{PRODUCT_ID}"]'."<img src=\"".esc_attr(magforest_get_asset_url("orderprintedbutton.png"))."\" alt=\"Order printed version on Magforest\" />"."[/magforest_print]", $ret);
	} else {
		$ret = str_replace('{PRINT_LINK}', '', $ret);
	}
	$ret = str_replace('{PRODUCT_ID}', $product->info->id, $ret);
	return $ret;
}
