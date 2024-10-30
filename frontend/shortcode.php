<?php

defined('ABSPATH') or die('Hnng~');

function magforest_featured_image_shortcode($atts = array(), $content = null, $tag = '') {
	$atts = array_change_key_case((array)$atts, CASE_LOWER);
	if(!isset($atts['id'])) {
		// no ID in attrs, try to guess it.
		if( !get_post_meta( get_the_ID(), 'magforest_id', true ) ) {
			return "Magforest: product id not specified in shortcode";
		} else {
			$id = get_post_meta( get_the_ID(), 'magforest_id', true ) ;
		}
	} else
		$id = $atts['id'];
	$product  = magforest_get_product(intval($id));
	if(!$product) {
		return "Magforest: bad product id";
	}
	
	$img_url = esc_attr( $product->info->thumbnail );
	return '<img src="'.$img_url.'" alt="Preview" />';
}
add_shortcode('magforest_image', 'magforest_featured_image_shortcode');

function magforest_download_link_shortcode($atts = array(), $content = null, $tag = '') {
	if(!$content) $content = "<img src=\"".esc_attr(magforest_get_asset_url("download-now-button.png"))."\" alt=\"Download now on Magforest\" />";
	$link = get_post_meta(  get_the_ID(), 'magforest_link', true );

	if(!$link) {
		if(!isset($atts['id'])) {
			// no ID in attrs, try use cached link
			$id = get_post_meta(  get_the_ID(), 'magforest_id', true ) ;
			if( !$id ) {
				return "Magforest: product id not specified in shortcode";
			}
		} else {
			$id = $atts['id'];
		}
	
		return '<a href="'.esc_attr(magforest_get_affiliate_link( magforest_link_from_id($id) )).'">'.$content.'</a>';
	} else {
		return '<a href="'.esc_attr(magforest_get_affiliate_link( $link )).'">'.$content.'</a>';
	}
}

add_shortcode('magforest_link', 'magforest_download_link_shortcode');


function magforest_print_link_shortcode($atts = array(), $content = null, $tag = '') {
	if(!$content) $content = "<img src=\"".esc_attr(magforest_get_asset_url("orderprintedbutton.png"))."\" alt=\"Order printed on Magforest\" />";
	if(!isset($atts['id'])) {
		// no ID in attrs, try to guess it.
		if( !get_post_meta( get_the_ID(), 'magforest_id', true ) ) {
			return "Magforest: product id not specified in shortcode";
		} else {
			$id = get_post_meta(  get_the_ID(), 'magforest_id', true ) ;
		}
	} else
		$id = $atts['id'];
	
	return '<a href="'.esc_attr( magforest_get_affiliate_print_link($id) ).'">'.$content.'</a>';
}

add_shortcode('magforest_print', 'magforest_print_link_shortcode');