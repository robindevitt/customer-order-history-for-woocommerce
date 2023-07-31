<?php
/**
 * Custom meta boxes for Orders.
 *
 * @package CustomerOrderHistory
 */

add_action( 'add_meta_boxes', 'customer_order_history_meta_box' );
add_action( 'wp_ajax_customer_order_history', 'ajax__customer_order_history' );
add_action( 'wp_ajax_nopriv_customer_order_history', 'ajax__customer_order_history' );

/**
 * Render Meta Box content.
 *
 * @param WP_Post $post The post object.
 */
function customer_order_history_meta_box_content( $post ) {
	// Get the order object.
	$order = wc_get_order( $post->ID );

	// Get orders from people named John that were paid in the year 2016.
	$orders = action_retrieve_customer_order_history( true, $order->get_billing_email(), 0 );

	$order_count = ( isset( $orders->total ) ? $orders->total : count( $orders ) );

	// When there are no related orders, show the message and return early.
	if ( 0 === $order_count ) {
		echo '<p>' . esc_html__( 'The billing email for this order, has no related orders.', 'customer-order-history' ) . '</p>';
		return;
	}

	$order_html = '';

	$order_html .= '<div>';

		$order_html .= '<table id="customer_order_history" class="wp-list-table widefat fixed striped table-view-excerpt posts">';

			$order_html .= '<thead>';

				$order_html .= '<tr>';

					$order_html .= '<th>' . esc_html__( 'View', 'customer-order-history' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Order Number', 'customer-order-history' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Date', 'customer-order-history' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Status', 'customer-order-history' ) . ' </th>';
					$order_html .= '<th>' . esc_html__( 'Total', 'customer-order-history' ) . ' </th>';

				$order_html .= '</tr>';

			$order_html .= '</thead>';

			$order_html .= '<tbody>';

			// Loop through the orders related to the customer.
			$order_html .= render_customer_order_history( $post->ID, $orders );

			$order_html .= '</tbody>';

		$order_html .= '</table>';

		// Output pagination links.
	if ( $orders->max_num_pages > 0 ) {
		$order_html .= '<div class="order-pagination tablenav bottom">';

			$order_html .= '<div class="alignright">';

				$order_html .= '<select id="customer-order-history-pagination" class="" data-email="' . esc_attr( $order->get_billing_email() ) . '" name="page">';

					for ( $i = 1; $i <= $orders->max_num_pages; $i++ ) { // phpcs:ignore

			$order_html .= '<option value="' . esc_attr( $i - 1 ) . '">' . esc_html__( 'Page', 'customer-order-history' ) . ' ' . $i . '</option>';

					} // phpcs:ignore

				$order_html .= '</select>';

				$order_html .= ' of ' . $orders->max_num_pages;

				if ( isset( $orders->total ) ) { // phpcs:ignore
			$order_html .= '  |  <span> ' . esc_html__( 'Orders', 'customer-order-history' ) . ': ' . $orders->total . '</span>';
				} // phpcs:ignore

			$order_html .= '</div>'; // Close of div alignright.

		$order_html .= '</div>'; // Close of class order-pagination.
	}

	$order_html .= '</div>'; // Close of ID customer_order_history.

	echo wp_kses(
		$order_html,
		array(
			'span'   => array(
				'class' => true,
			),
			'div'    => array(
				'class' => true,
			),
			'select' => array(
				'class'      => true,
				'id'         => true,
				'name'       => true,
				'data-email' => true,
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
		)
	);

}

/**
 * Function to retrieve the pagination count.
 */
function get_the_pagination_count() {
	$pagination = get_option( 'cro__pagination', '' );

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
 * Ajax relatted function.
 */
function ajax__customer_order_history() {
	// Sanitize and validate the nonce value.
	$nonce = isset( $_POST['nonce'] ) ? wp_unslash( $_POST['nonce'] ) : ''; // phpcs:ignore

	// If the action is customer_order_history and data is not emoty then continue.
	if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $nonce, 'customer-order-history' ) ) {
		if ( isset( $_POST['action'] ) && 'customer_order_history' === $_POST['action'] && ! empty( $_POST['data'] ) && isset( $_POST['data']['email'] ) ) {
			$data = action_retrieve_customer_order_history( true, $_POST['data']['email'], $_POST['data']['page'] ); // phpcs:ignore
			$data = render_customer_order_history( get_queried_object_id(), $data );
			echo wp_kses(
				$data,
				array(
					'span'   => array(
						'class' => true,
					),
					'div'    => array(
						'class' => true,
					),
					'select' => array(
						'class'      => true,
						'id'         => true,
						'name'       => true,
						'data-email' => true,
					),
					'option' => array(
						'value' => true,
					),
					'table'  => array(
						'class' => true,
					),
					'thead'  => array(),
					'tr'     => array(),
					'th'     => array(),
					'td'     => array(
						'class'   => true,
						'colspan' => true,
					),
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
				)
			);
		}
	}
	wp_die();
}

/**
 * Retrieve related orders
 *
 * @param bool   $allorders True for complete object false for orders only.
 * @param string $email Email address to search..
 * @param int    $offset Set the offset for the order count.
 */
function action_retrieve_customer_order_history( $allorders, $email, $offset ) {
	// Get the limit / orders per page.
	$limit  = get_the_pagination_count();
	$orders = wc_get_orders(
		array(
			'billing_email' => $email,
			'limit'         => $limit,
			'paginate'      => true,
			'offset'        => ( $offset > 0 ? ( $offset * $limit ) : 0 ),
		)
	);
	if ( $allorders ) {
		return $orders;
	}
	return $orders->orders;
}

/**
 * Render related orders.
 *
 * @param int    $currentorder Current order number.
 * @param object $orders Object of orders.
 */
function render_customer_order_history( $currentorder, $orders ) {
	$order_history_html = '';
	foreach ( $orders->orders as $order ) {
		$order_history_html     .= '<tr>';
			$order_history_html .= '<td><a title="' . esc_html__( 'View order', 'customer-order-history' ) . '" href=""><span class="dashicons dashicons-welcome-view-site"></span></a>';
			$order_history_html .= '<td>' . $order->get_id() . ( $currentorder === $order->get_id() ? ' - viewing' : '' ) . '</td>';
			$order_history_html .= '<td>' . $order->get_date_created()->format( 'j F, Y' ) . '</td>';
			$order_history_html .= '<td><mark class="order-status status-' . esc_attr( $order->get_status() ) . '"><span>' . wc_get_order_status_name( $order->get_status() ) . '</span></mark></td>';
			$order_history_html .= '<td>' . $order->get_formatted_order_total() . '</td>';
		$order_history_html     .= '</tr>';
	}
	return $order_history_html;
}
