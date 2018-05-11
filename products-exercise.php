<?php 
/*
Plugin Name: Products exercise
Description: Testowa wtyczka
Version: 1.0
Author: Dominik Pres
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) or die( 'Bye' );


// Register custom post
function custom_post_type_products()
{
	register_post_type( 'products', array(
		'labels' => array(
			'name' => 'Products',
			'singular_name' => 'Product',
			),
		'public' => true,
		'menu_position' => 2,
		'supports' => array( 'title', 'editor' ),
		'taxonomies'  => array( 'category' ),
		'menu_icon' => 'dashicons-cart',
	));
}
add_action( 'init', 'custom_post_type_products' );


// Add custom metabox
function price_custom_box_html($post)
{	
	$value = get_post_meta($post->ID, '_price_meta_key', true); // Read value for price_field
    ?>
    <input name="price_field" type="number" min="0" step="0.01" placeholder="Give me your price..." value="<?= $value; ?>">
    <?php
}
function price_add_custom_box()
{
	add_meta_box(
		'price_box_id',
		'Price Box', // Title
		'price_custom_box_html',  // Content callback
		'products' // Post type
	);
}
add_action('add_meta_boxes', 'price_add_custom_box');


// Save meta box value
function price_save_postdata($post_id)
{
    if (array_key_exists('price_field', $_POST)) {
        update_post_meta(
            $post_id,
            '_price_meta_key',
            (float)$_POST['price_field']
        );
    }
}
add_action('save_post', 'price_save_postdata');


// Manage columns
function my_edit_products_columns( $columns )
{
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => 'Name',
		'price' => 'Price'
	);
	return $columns;
}
add_filter( 'manage_edit-products_columns', 'my_edit_products_columns' ) ;


// Sort by name and price
function my_products_sortable_columns( $columns )
{
	$columns['price'] = 'price';
	return $columns;
}
add_filter( 'manage_edit-products_sortable_columns', 'my_products_sortable_columns' );


// Sort correctly
add_action( 'pre_get_posts', 'mycpt_custom_orderby' );
function mycpt_custom_orderby( $query )
{
	if ( !is_admin() ) return;
		$orderby = $query->get( 'orderby');
	if ( 'price' == $orderby ) {
		$query->set( 'meta_key', '_price_meta_key' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}


// Values for Products columns on Post list
function my_manage_products_columns( $column, $post_id )
{
	global $post;
	switch( $column ) {
		case 'price' :
			$price = get_post_meta( $post_id, '_price_meta_key', true );
			echo empty($price) ? '0.00' : $price . "(". gettype($price) .")" ;
			break;
	}
}
add_action( 'manage_products_posts_custom_column', 'my_manage_products_columns', 10, 2 );
?>