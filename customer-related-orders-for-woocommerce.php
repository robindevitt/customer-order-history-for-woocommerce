<?php
/**
 * Plugin Name:       Customer Related Orders for WooCommerce
 * Plugin URI:        https://github.com/robindevitt/related-orders-for-woocommerce
 * Description:       When viewing orders you can now view related orders to the customer.
 * Version:           1.0.0
 * Author:            Robin Devitt
 * Author URI:        https://robindevitt.co.za/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       customer-related-orders
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * @package CustomerRelatedOrders
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CUSTOMER_RELATED_ORDERS_VERSION', '1.0.0' );

/**
 * Ensure the meta boxes are only called in the admin areas.
 */
if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'customer_related_orders_assets' );
	require_once __DIR__ . '/woocommerce/custom-meta.php';
	require_once __DIR__ . '/woocommerce/general-settings.php';
}

/**
 * Adds the meta box container.
 *
 * @param str $post_type Post type.
 */
function customer_related_orders_meta_box( $post_type ) {
	// Limit meta box to certain post types.
	$post_types = array( 'shop_order' );

	if ( in_array( $post_type, $post_types, true ) ) {
		add_meta_box(
			'customer_related_orders_meta_box',
			__( 'Customer Related Orders', 'customer-related-orders' ),
			'customer_related_orders_meta_box_content',
			$post_type,
			'advanced',
			'high'
		);
	}
}

/**
 * Add plugin related assets.
 */
function customer_related_orders_assets() {
	wp_enqueue_script( 'customer-related-orders', plugins_url( '/assets/js/customer_related_orders.js', __FILE__ ), array( 'jquery' ), CUSTOMER_RELATED_ORDERS_VERSION, true );
	wp_localize_script(
		'customer-related-orders',
		'CustomerRelatedOrders',
		array(
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'security'      => wp_create_nonce( 'customer-related-orders' ),
			'fetching_text' => __( 'Fetching orders...', 'customer-related-orders' ),
		)
	);
}
