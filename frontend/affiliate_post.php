<?php

defined('ABSPATH') or die('Hnng');


function magforest_affiliate_post_register()
{
    register_post_type('magforest_affiliate',
                       array(
                           'labels'      => array(
                               'name'          => __('Affiliate Mags'),
                               'singular_name' => __('Affiliate Mag'),
                           ),
                           'public'      => true,
                           'has_archive' => true,
                           'show_in_menu'=> false,
                           /*'capabilities' => array(
						    'create_posts' => 'do_not_allow', // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )			
						    'delete_post'=>'allow',
						    'edit_posts'=>'allow',
						    'publish_posts'=>'allow'
						  ),*/
						  'taxonomies' => array('post_tag','post_category'),
                           'publicly_queryable' => true,
                           'query_var' => true,
                           'rewrite'=>array('slug'=>'magforest_affiliate'),
                           'supports' => array('thumbnail', 'title', 'editor')
                       )
    );
}
add_action('init', 'magforest_affiliate_post_register');

function magforest_affiliate_redirect()
{
	$queried_post_type = get_query_var('post_type');
	  if ( is_single() && 'magforest_affiliate' ==  $queried_post_type ) {
	    
        wp_redirect ( magforest_get_affiliate_link( get_post_meta(get_the_id(), 'magforest_link', true) ) ) ;
        exit();
	  }
 
}
add_action( 'template_redirect', 'magforest_affiliate_redirect' );



function add_magforest_posts_to_main_page( $query ) {
  if ( !is_page() ) {
	   $pt = $query->get('post_type');
	   if($query->get('tax_query')) {
		   // Codex: If 'tax_query' is set for a query, the default value becomes 'any';
	   } else if(is_string($pt) && strlen($pt) > 0) {
		   $sas = $pt;
		   $pt = array($pt,'magforest_affiliate');
	   } else if(is_array($pt)) {
		   array_push($pt, 'magforest_affiliate');
	   } else {
		   // wtf?
		   $pt = array('post','magforest_affiliate');
	   }
	   $query->set( 'post_type', $pt );
  }
  return $query;
}
add_action( 'pre_get_posts', 'add_magforest_posts_to_main_page' );