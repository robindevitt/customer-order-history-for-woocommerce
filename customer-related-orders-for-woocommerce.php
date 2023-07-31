<?php
/**
 * Plugin Name:       Customer Order History for WooCommerce
 * Plugin URI:        https://github.com/robindevitt/related-orders-for-woocommerce
 * Description:       When viewing orders you can now view related orders to the customer.
 * Version:           1.0.0
 * Author:            Code For Coffee
 * Author URI:        https://codeforcoffee.co.za/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       customer-order-history
 * Domain Path:       /languages
 *
 * @package CustomerOrderHistory
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CUSTOMER_ORDER_HISTORY_VERSION', '1.0.0' );

/**
 * Check if WooCommerce is active
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true )
	&& ! array_key_exists( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	add_action( 'admin_notices', 'coh__woocommerce_disabled_notice' );
	return;
}

/**
 * Error message to display if Woocommerce isn't active.
 */
function coh__woocommerce_disabled_notice() {
	echo '<div class="error"><p>';
		echo sprintf(
			/* translators: %s: Download link for WooCommerce */
			esc_html__(
				'<strong>Customer Order History for WooCommerce</strong> requires WooCommerce to be installed and actived. Please activate WooCommerce and if you need to download WooCommerce, you can %s.',
				'customer-order-history'
			),
			'<a href="https://wordpress.org/plugins/woocommerce">' . esc_html__( 'download it here', 'customer-order-history' ) . '</a>'
		);
	echo '</p></div>';
}

/**
 * Ensure the meta boxes are only called in the admin areas.
 */
if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'customer_order_history_assets' );
	require_once __DIR__ . '/woocommerce/custom-meta.php';
	require_once __DIR__ . '/woocommerce/general-settings.php';
}

/**
 * Adds the meta box container.
 *
 * @param str $post_type Post type.
 */
function customer_order_history_meta_box( $post_type ) {
	// Limit meta box to certain post types.
	$post_types = array( 'shop_order' );

	if ( in_array( $post_type, $post_types, true ) ) {
		add_meta_box(
			'customer_order_history_meta_box',
			__( 'Customer Order History', 'customer-order-history' ),
			'customer_order_history_meta_box_content',
			$post_type,
			'advanced',
			'high'
		);
	}
}

/**
 * Add plugin related assets.
 */
function customer_order_history_assets() {
	wp_enqueue_script( 'customer-order-history', plugins_url( '/assets/js/customer_order_history.js', __FILE__ ), array( 'jquery' ), CUSTOMER_ORDER_HISTORY_VERSION, true );
	wp_localize_script(
		'customer-order-history',
		'customerOrderHistory',
		array(
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'security'      => wp_create_nonce( 'customer-order-history' ),
			'fetching_text' => __( 'Fetching orders...', 'customer-order-history' ),
		)
	);
}
