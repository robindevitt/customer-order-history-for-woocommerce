<?php
/**
 * Custom meta boxes for Orders.
 *
 * @package CustomerRelatedOrders
 */

add_action( 'wp_ajax_customer_related_orders', 'customer_related_orders_ajax_request' );
add_action( 'wp_ajax_nopriv_customer_related_orders', 'customer_related_orders_ajax_request' );

/**
 * Render Meta Box content.
 *
 * @param WP_Post $order The order object.
 */
function customer_related_orders_meta_box_content( $order ) {

	// Get orders from people named John that were paid in the year 2016.
	$orders = customer_related_orders_retrieve( true, $order->get_billing_email(), 0, $order->get_id() );

	$order_count = ( isset( $orders->total ) ? $orders->total : count( $orders ) );

	// When there are no related orders, show the message and return early.
	if ( 0 === $order_count ) {
		echo '<p>' . esc_html__( 'The billing email for this order, has no related orders.', 'customer-related-orders' ) . '</p>';
		return;
	}

	$order_html = '';

	$order_html .= '<div>';

		$order_html .= '<table id="customer_related_orders" class="wp-list-table widefat fixed striped table-view-excerpt posts">';

			$order_html .= '<thead>';

				$order_html .= '<tr>';

					$order_html .= '<th>' . esc_html__( 'Order', 'customer-related-orders' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Date', 'customer-related-orders' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Status', 'customer-related-orders' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Total', 'customer-related-orders' ) . ' </th>';

				$order_html .= '</tr>';

			$order_html .= '</thead>';

			$order_html .= '<tbody>';

			// Loop through the orders related to the customer.
			$order_html .= customer_related_orders_render_html( $orders );

			$order_html .= '</tbody>';

		$order_html .= '</table>';

		// Output pagination links.
		if ( $orders->max_num_pages > 0 ) { // phpcs:ignore
			$order_html .= '<div class="order-pagination tablenav bottom">';

			$order_html .= '<div class="alignright">';

			$order_html .= '<select id="customer-related-orders-pagination" class="" data-email="' . sanitize_email( esc_attr( $order->get_billing_email() ) ) . '" data-exclude="' . esc_attr( $order->get_id() ) . '" name="page">';

			for ( $i = 1; $i <= $orders->max_num_pages; $i++ ) { // phpcs:ignore
				$order_html .= '<option value="' . esc_attr( $i - 1 ) . '">' . esc_html__( 'Page', 'customer-related-orders' ) . ' ' . $i . '</option>';
				} // phpcs:ignore

			$order_html .= '</select>';

			$order_html .= ' of ' . $orders->max_num_pages;

			if ( isset( $orders->total ) ) { // phpcs:ignore
				$order_html .= '  |  <span> ' . esc_html__( 'Related Orders', 'customer-related-orders' ) . ': ' . $orders->total . '</span>';
				} // phpcs:ignore

			$order_html .= '</div>'; // Close of div alignright.

			$order_html .= '</div>'; // Close of class order-pagination.
		} // phpcs:ignore

	$order_html .= '</div>'; // Close of ID customer_related_orders.

	echo wp_kses( $order_html, customer_related_orders_allowed_html_tags() );
}

/**
 * Function to retrieve the pagination count.
 */
function customer_related_orders_pagination() {
	$pagination = get_option( 'customer_related_pagination', '' );

	// Return the default value as 10.
	if ( empty( $pagination ) ) {
		return 10;
	}

	// If the value is not empty and it's a negative number- return -1.
	if ( ! empty( $pagination ) && $pagination < 0 ) {
		return -1;
	}

	// Return the pagination value.
	return $pagination;
}

/**
 * Customer Related Orders Ajax Request.
 */
function customer_related_orders_ajax_request() {
	// If the action is customer_related_orders and data is not emoty then continue.
	if ( isset( $_POST['nonce'] ) || wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'customer-related-orders' ) ) {
		if ( isset( $_POST['action'] ) && 'customer_related_orders' === $_POST['action'] && ! empty( $_POST['data'] ) && isset( $_POST['data']['email'] ) ) {

			$post_email   = isset( $_POST['data']['email'] ) ? sanitize_email( wp_unslash( $_POST['data']['email'] ) ) : '';
			$post_page    = isset( $_POST['data']['page'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['page'] ) ) : 1;
			$post_exclude = isset( $_POST['data']['exclude'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['exclude'] ) ) : '';

			$orders = customer_related_orders_retrieve( true, $post_email, $post_page, $post_exclude );
			$html   = customer_related_orders_render_html( $orders );
			echo wp_kses( $html, customer_related_orders_allowed_html_tags() );
		}
	}
	wp_die();
}

/**
 * Customer related orders retrieve orders.
 *
 * @param bool   $allorders True for complete object false for orders only.
 * @param string $email Email address to search..
 * @param int    $offset Set the offset for the order count.
 * @param int    $exclude Exclude the order id.
 */
function customer_related_orders_retrieve( $allorders, $email, $offset, $exclude ) {
	// Get the limit / orders per page.
	$limit        = customer_related_orders_pagination();
	$offset       = isset( $offset ) && is_numeric( $offset ) ? (int) $offset : 0;
	$offset_value = ( $offset > 0 ? ( $offset * $limit ) : 0 );

	$orders = wc_get_orders(
		array(
			'billing_email' => $email,
			'limit'         => $limit,
			'paginate'      => true,
			'offset'        => $offset_value,
			'exclude'       => array( $exclude ),
		)
	);
	if ( $allorders ) {
		return $orders;
	}
	return $orders->orders;
}

/**
 * Customer related orders render html.
 *
 * @param object $orders Object of orders.
 */
function customer_related_orders_render_html( $orders ) {
	$related_orders_html = '';
	foreach ( $orders->orders as $order ) {
		$related_orders_html     .= '<tr>';
			$related_orders_html .= '<td><a title="' . esc_html__( 'View order', 'customer-related-orders' ) . '" href="' . $order->get_edit_order_url() . '">View Order #' . $order->get_id() . '</a></td>';
			$related_orders_html .= '<td>' . $order->get_date_created()->format( 'j F, Y' ) . '</td>';
			$related_orders_html .= '<td><mark class="order-status status-' . esc_attr( $order->get_status() ) . '"><span>' . wc_get_order_status_name( $order->get_status() ) . '</span></mark></td>';
			$related_orders_html .= '<td>' . $order->get_formatted_order_total() . '</td>';
		$related_orders_html     .= '</tr>';
	}
	return $related_orders_html;
}

/**
 * Setup allowed HTML tags for wp_kses.
 */
function customer_related_orders_allowed_html_tags() {
	return array(
		'span'   => array(
			'class' => true,
		),
		'div'    => array(
			'class' => true,
		),
		'select' => array(
			'class'        => true,
			'id'           => true,
			'name'         => true,
			'data-email'   => true,
			'data-exclude' => true,
		),
		'option' => array(
			'value' => true,
		),
		'table'  => array(
			'id'    => true,
			'class' => true,
		),
		'thead'  => array(),
		'tr'     => array(),
		'th'     => array(),
		'td'     => array(
			'class'   => true,
			'colspan' => true,
		),
		'del'    => array(),
		'a'      => array(
			'href'  => true,
			'title' => true,
		),
		'mark'   => array(
			'class' => true,
		),
		'input'  => array(
			'type',
			'name',
			'value',
		),
	);
}
