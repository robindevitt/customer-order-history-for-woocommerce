jQuery('#customer-order-history-pagination').on('change', function (e) {
	var pagination = {
		email: jQuery(this).attr('data-email'),
		page: jQuery(this).val(),
	};

	jQuery('#customer_order_history tbody').empty();
	jQuery('<span class="loading_customer_orders"> ' + customerOrderHistory.fetching_text + ' </span> ').insertBefore('#customer-order-history-pagination');

	jQuery.ajax({

		url: customerOrderHistory.ajax_url,
		type: 'post',
		data: {
			'action': 'customer_order_history',
			'nonce': customerOrderHistory.security,
			'data': pagination,
		},
		success: function (data) {
			jQuery('#customer_order_history tbody').empty();
			jQuery('#customer_order_history tbody').append(data);
			jQuery('.loading_customer_orders').remove();
		}
	})
});
