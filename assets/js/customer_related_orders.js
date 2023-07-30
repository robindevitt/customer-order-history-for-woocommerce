jQuery('#related-order-pagination').on('change', function (e) {
	var pagination = {
		email: jQuery(this).attr('data-email'),
		page: jQuery(this).val(),
	};

	jQuery('#customer_related_orders tbody').empty();
	jQuery('#customer_related_orders tbody').append('<tr><td class="alignleft">Loading related orders...</td></tr>');

	jQuery.ajax({

		url: relatedorders.ajax_url,
		type: 'post',
		data: {
			'action': 'related_order_for_customers',
			'nonce': relatedorders.security,
			'data': pagination,
		},
		success: function (data) {
			jQuery('#customer_related_orders tbody').empty();
			jQuery('#customer_related_orders tbody').append(data);
		}
	})
});
