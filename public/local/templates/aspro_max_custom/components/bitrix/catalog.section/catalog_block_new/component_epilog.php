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

<?
$bAddOfferInfo = false;
if(!empty($GLOBALS[$arParams['FILTER_NAME']]['OFFERS'])){
	$bAddOfferInfo = true;
}
if(!empty($GLOBALS[$arParams['FILTER_NAME']]['OFFERS']) && !empty($arResult['ITEMS_IDS'])){
}
?>

<script>
	$(document).ready(function (){
		//FB добавление товара в корзину
		$('.catalog_item_wrapp .footer_button').on('click', '.button_block > span.to-cart', function(){
			dataLayer.push({
				'event': 'eventTarget',
				'eventCategory': 'target',
				'eventAction': 'adding_to_the_shopping_cart',
				'eventLabel': '',
			});
			var itemId = $(this).closest('.catalog_item_wrapp').find('.fast_view_button > span').data('param-id');
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
        if(window.location.pathname != '/'){
            var _tmr = window._tmr || (window._tmr = []);
            _tmr.push({
                id: '2828139',
                type: 'itemView',
                productid: '',
                pagetype: 'category',
                list: '1',
                totalvalue: ''
            });
        }
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

			// ГдеСлон
			window.gdeslon_q = window.gdeslon_q || [];
			window.gdeslon_q.push({
				page_type:  "list",
				merchant_id: "100062",
				category_id: <?=$arResult['ID'];?>,
				<?if(!empty($GLOBALS['USER']->GetID())):?>user_id: "<?=$GLOBALS['USER']->GetID();?>"<?endif;?>
			});
		<?}?>

		<?if(!empty($arResult['SELECTED_OFFERS']) && $bAddOfferInfo):?>
			var arSelectedOffers = <?=CUtil::PhpToJSObject($arResult['SELECTED_OFFERS']);?>;
			for (var i in arSelectedOffers) {
				var offerUrl = arSelectedOffers[i]['DETAIL_PAGE_URL'];
				var itemElem = $('.item.item_block[data-id="' + i + '"]');
				itemElem.find('.item-title a').attr('href', offerUrl);
				itemElem.find('.image_wrapper_block a').attr('href', offerUrl);
			}
		<?endif;?>
	});
</script>