<?php
/**
 * Plugin Name:       Customer Related Orders for WooCommerce
 * Plugin URI:        https://github.com/robindevitt/related-orders-for-woocommerce
 * Description:       When viewing orders you can now view related orders to the customer.
 * Version:           1.0.0
 * Author:            Code For Coffee
 * Author URI:        https://codeforcoffee.co.za/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       customer-related-orders-woocommerce
 * Domain Path:       /languages
 *
 * @package CustomerRelatedOrdersForWooCommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CUSTOMER_RELATED_ORDER_VERSION', '1.0.0' );

/**
 * Check if WooCommerce is active
 */
if ( 
	! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) 
	&& ! array_key_exists( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins', array() ) ) ) 
) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	add_action( 'admin_notices', 'crow__woocommerce_disabled_notice' );
	return;
}

/**
 * Error message to display if Woocommerce isn't active.
 */
function crow__woocommerce_disabled_notice() {
	echo '<div class="error"><p>';
		echo sprintf(
			/* translators: %s: Download link for WooCommerce */
			esc_html__(
				'<strong>Customer Related Orders for WooCommerce</strong> requires WooCommerce to be installed and actived. Please activate WooCommerce and if you need to download WooCommerce, you can %s.',
				'customer-related-orders-woocommerce'
			),
			'<a href="https://wordpress.org/plugins/woocommerce">' . esc_html__( 'download it here', 'customer-related-orders-woocommerce' ) . '</a>'
		);
	echo '</p></div>';
}

/**
 * Ensure the meta boxes are only called in the admin areas.
 */
if ( is_admin() ) {
	add_action( 'add_meta_boxes', 'related_customer_orders_meta_box' );
	add_action( 'admin_enqueue_scripts', 'related_customer_orders_assets' );
}

/**
 * Adds the meta box container.
 */
function related_customer_orders_meta_box( $post_type ) {
	// Limit meta box to certain post types.
	$post_types = array( 'shop_order' );

	if ( in_array( $post_type, $post_types ) ) {
		add_meta_box(
			'customer_related_orders_meta_box',
			__( 'Customer Related Orders', 'customer-related-orders-woocommerce' ),
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
function related_customer_orders_assets() {
	wp_enqueue_style( 'customer-related-orders-style', plugins_url( '/assets/css/customer_related_orders.min.css', __FILE__ ), array(), CUSTOMER_RELATED_ORDER_VERSION );
}

/**
 * Render Meta Box content.
 *
 * @param WP_Post $post The post object.
 */
function customer_related_orders_meta_box_content( $post ) {
	// Get an instance of the WC_Order Object.
    $order = wc_get_order( $post->ID );

	// Get orders from people named John that were paid in the year 2016.
	$orders = wc_get_orders( array(
		'billing_email' => $order->get_billing_email(),
		'exclude'       => array( $order->get_id() ),
		'limit'         => 3, // Get all orders.
		'paginate'      => true,
		'offset'        => 3
	) );

	var_dump( $orders );

	$order_count = ( isset( $orders->total ) ? $orders->total : count( $orders ) );

	// When there are no related orders, show the message and return early.
	if ( $order_count === 0 ){
		echo '<p>' . esc_html__( 'The billing email for this order, has no related orders.', 'customer-related-orders-woocommerce' ) . '</p>';
		return;
	}

	$order_html = '';

	$order_html .= '<div class="customer_related_orders">';

		$order_html .= '<table>';
			
			$order_html .= '<thead>';
				$order_html .= '<tr>';
					$order_html .= '<th>' . esc_html__( 'View', 'customer-related-orders-woocommerce' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Order Number', 'customer-related-orders-woocommerce' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Date', 'customer-related-orders-woocommerce' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Status', 'customer-related-orders-woocommerce' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Total', 'customer-related-orders-woocommerce' ) . ' </th>';
				$order_html .= '</tr>';
			$order_html .= '</thead>';

			$order_html .= '<tbody>';

			// Loop through the orders related to the customer.
			foreach ( $orders->orders as $order ) {
				$order_html .= '<tr>';
					$order_html .= '<td><a title="' . esc_html__( 'View order', 'customer-related-orders-woocommerce' ) . '" href=""><span class="dashicons dashicons-welcome-view-site"></span></a>'; 
					$order_html .= '<td>' . $order->get_id() . '</td>';
					$order_html .= '<td>' . $order->get_date_created()->format ('j F, Y') . '</td>';
					$order_html .= '<td><mark class="order-status status-' . esc_attr( $order->get_status() ) . '"><span>' . wc_get_order_status_name( $order->get_status() ) . '</span></mark></td>';
					$order_html .= '<td>' . $order->get_formatted_order_total() . '</td>';
				$order_html .= '</tr>';
			}
				
			$order_html .= '</tbody>';
		$order_html .= '</table>';

	$order_html .= '</div>';

	echo $order_html;

}
