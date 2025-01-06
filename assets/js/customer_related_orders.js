jQuery('#customer-related-orders-pagination').on('change', function (e) {
	var pagination = {
		email: jQuery(this).attr('data-email'),
		page: jQuery(this).val(),
		current: jQuery(this).attr('data-current'),
	};

	jQuery('#customer_related_orders tbody').empty();
	jQuery('<span class="loading_customer_orders"> ' + CustomerRelatedOrders.fetching_text + ' </span> ').insertBefore('#customer-related-orders-pagination');

	jQuery.ajax({

		url: CustomerRelatedOrders.ajax_url,
		type: 'post',
		data: {
			'action': 'customer_related_orders',
			'nonce': CustomerRelatedOrders.security,
			'data': pagination,
		},
		success: function (data) {
			jQuery('#customer_related_orders tbody').empty();
			jQuery('#customer_related_orders tbody').append(data);
			jQuery('.loading_customer_orders').remove();
		}
	})
});
