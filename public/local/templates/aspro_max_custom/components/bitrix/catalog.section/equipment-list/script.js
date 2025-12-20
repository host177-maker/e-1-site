$(document).ready(function () {
	$('.bx-catalog-element .correction_margin').hide();
	$(".equipment-block li").on("click", function () {
		$(this).toggleClass('active');
		var price = 0;
		$(".equipment-block li").each(function (index, el) {
			if ($(this).is('.active')) {
				price += parseInt($(el).attr('data-price'));
			}
		});

		console.log('productPrice', 'systemForScript', price);//для нахождения скрипта

		let priceBlock = $('.product-view .prices_block .prices-body .price:not(.discount)[data-value]');
		let totalPrice = parseInt(priceBlock.data('value')) + price;
		priceBlock.find('.price_value').html(Number(totalPrice).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " "));

		$('.product-view .equipment-block .equipment-block-summ-cost span').html('+ ' + price + ' ₽');
		// блок с аксессуаром показываем
		if ($(this).is('.active')) {
			$('.bx-catalog-element .correction_margin.product' + $(this).attr('data-product')).show();
			if ($('.custom_service_chekbox-detail').length > 0) $('.custom_service_chekbox-detail').show();
		} else {
			$('.bx-catalog-element .correction_margin.product' + $(this).attr('data-product')).hide();
			if ($('.custom_service_chekbox-detail').length > 0) $('.custom_service_chekbox-detail').hide();
		}
	});
});