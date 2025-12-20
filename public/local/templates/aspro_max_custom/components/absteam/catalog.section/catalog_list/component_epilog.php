<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */
use Bitrix\Main\Loader;
if (isset($templateData['TEMPLATE_LIBRARY']) && !empty($templateData['TEMPLATE_LIBRARY'])){
	$loadCurrency = false;
	if (!empty($templateData['CURRENCIES']))
		$loadCurrency = Loader::includeModule('currency');
	CJSCore::Init($templateData['TEMPLATE_LIBRARY']);
	if ($loadCurrency){?>
	<script type="text/javascript">
		BX.Currency.setCurrencies(<? echo $templateData['CURRENCIES']; ?>);
	</script>
	<?}
}?>

<script>
	$(document).ready(function (){
		//FB добавление товара в корзину
		$('.list_item_wrapp .main_item_wrapper').on('click', '.button_block > span.to-cart', function(){
			dataLayer.push({
				'event': 'eventTarget',
				'eventCategory': 'target',
				'eventAction': 'adding_to_the_shopping_cart',
				'eventLabel': '',
			});
			var itemId = $(this).closest('.list_item_wrapp').find('.fast_view_button > span').data('param-id');
			var offerId = $(this).data('item');
			var itemPrice = $(this).data('value');
			/*fbq('track', 'AddToCart', {
				value: itemPrice,
				currency: 'RUB',
				contents: [
					{
						id: itemId,//поменять на offerId, если в фиде будут передаваться id торговых предложений
						quantity: 1
					}
				],
			});*/
			//mail.ru, добавление товара в корзину
			var _tmr = _tmr || [];
			_tmr.push({
				id: '2828139',
				type: 'itemView',
				productid: itemId,//поменять на offerId, если в фиде будут передаваться id торговых предложений
				pagetype: 'cart',
				list: '1',
				totalvalue: ''
			});
		});
		//mail.ru, страница категории
		<?if($APPLICATION->GetCurPage() != '/') {?>
			var _tmr = _tmr || [];
			_tmr.push({
				id: '2828139',
				type: 'itemView',
				productid: '',
				pagetype: 'category',
				list: '1',
				totalvalue: ''
			});
			//mgid страница категории
			/*(function() {
				var d = document, w = window;
				w.MgSensorData = w.MgSensorData || [];
				w.MgSensorData.push({
					cid:280113,
					lng:"us",
					nosafari:true,
					eid: "0",
					partner: 578829,
					project: "a.mgid.com",
					goods:[0]
				});
				var l = "a.mgid.com";
				var n = d.getElementsByTagName("script")[0];
				var s = d.createElement("script");
				s.type = "text/javascript";
				s.async = true;
				var dt = !Date.now?new Date().valueOf():Date.now();
				s.src = "//" + l + "/mgsensor.js?d=" + dt;
				n.parentNode.insertBefore(s, n);
			})();*/
		<?}?>
	});
</script>