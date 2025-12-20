$(document).ready(function(){
	$(".equipment-block li").on("click", function(){
		$(this).toggleClass('active');
		var price = 0;
		$(".equipment-block li").each(function(index, el) {
			if($(this).is('.active')){
				price += parseInt($(el).attr('data-price'));
			}
		});

		var productPrice = $('.product-view .prices_block .js_price_wrapper .price').attr('data-value');

		$('.product-view .prices_block .price:not(.discount) .price_value').html((Number(productPrice) + Number(price)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " "));

		$('.product-view .equipment-block .equipment-block-summ-cost span').html('+ ' + price + ' ₽');
	});
});