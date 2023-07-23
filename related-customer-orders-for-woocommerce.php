<?php
/**
 * Plugin Name:       Related Customer Orders for WooCommerce
 * Plugin URI:        https://github.com/robindevitt/related-orders-for-woocommerce
 * Description:       When viewing orders you can now view related orders to the customer.
 * Version:           1.0.0
 * Author:            Code For Coffee
 * Author URI:        https://codeforcoffee.co.za/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       related-customer-orders-woocommerce
 * Domain Path:       /languages
 *
 * @package RelatedOrdersForWooCommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is active
 */
if ( 
	! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) 
	&& ! array_key_exists( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins', array() ) ) ) 
) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	add_action( 'admin_notices', 'rcow__woocommerce_disabled_notice' );
	return;
}

/**
 * Error message to display if Woocommerce isn't active.
 */
function rcow__woocommerce_disabled_notice() {
	echo '<div class="error"><p>';
		echo sprintf(
			/* translators: %s: Download link for WooCommerce */
			esc_html__(
				'<strong>Related Customer Orders for WooCommerce</strong> requires WooCommerce to be installed and actived. Please activate WooCommerce and if you need to download WooCommerce, you can %s.',
				'related-customer-orders-woocommerce'
			),
			'<a href="https://wordpress.org/plugins/woocommerce">' . esc_html__( 'download it here', 'related-customer-orders-woocommerce' ) . '</a>'
		);
	echo '</p></div>';
}
