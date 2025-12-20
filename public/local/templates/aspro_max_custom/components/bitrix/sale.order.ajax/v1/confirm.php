<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));
}
Asset::getInstance()->addString('<script src="https://blank.abc-cred.ru:8798/js/blank-modal.js" type="text/javascript"></script>');
?>

<? if (!empty($arResult["ORDER"])): ?>
	<?
	//Массивы, переменные для передачи в FB
	$arrFbItems = array();
	$productPriceSumm = 0;
	//Заберем данные заказа
	$dbBasketItems = \CSaleBasket::GetList(
		array("NAME" => "ASC", "ID" => "ASC"),
		array("LID" => SITE_ID, "ORDER_ID" => $arResult["ORDER"]["ID"]),
		false,
		false,
		array("PRODUCT_ID", "NAME", "PRICE", "BASE_PRICE", "QUANTITY")
	);
	while ($arItem = $dbBasketItems->Fetch()) {
		if($arItem["BASE_PRICE"] != 0) {
			$mxResult = CCatalogSku::GetProductInfo(
				$arItem["PRODUCT_ID"]
			);
			if (is_array($mxResult)) {
				$productInfo = array(
					'id' => $arItem["PRODUCT_ID"],
					'product_id' => $mxResult["ID"],
					'quantity' => $arItem["QUANTITY"],
					'name' => $arItem["NAME"],
					'price' => $arItem["PRICE"],
					'full_ptice' => ((float)$arItem["PRICE"]*$arItem["QUANTITY"]),
				);
			} else {
				$productInfo = array(
					'id' => $arItem["PRODUCT_ID"],
					'product_id' => $arItem["PRODUCT_ID"],
					'quantity' => $arItem["QUANTITY"],
					'name' => $arItem["NAME"],
					'price' => $arItem["PRICE"],
					'full_ptice' => ((float)$arItem["PRICE"]*$arItem["QUANTITY"]),
				);
			}
			$productPriceSumm = (int)$productPriceSumm + ((int)$arItem['PRICE'] * (int)$arItem['QUANTITY']);
			//$productObj = json_encode($productInfo);
			$arrFbItems[] = $productInfo;
		}
	}
	//Массивы, переменные для передачи в abc-cred
	$arrCreditItems = array();
	foreach ($arrFbItems as $item) {
		for ($i=0; $i < $item["quantity"]; $i++) { 
			$creditItem = array(
				'name'   => htmlspecialchars($item["name"]),
				'amount' => (float)$item["price"],
			);
			$arrCreditItems[] = $creditItem;
		}
	}

    $qrCode = '';

    if ($arResult['PAY_SYSTEM']['ACTION_FILE'] == 'kupilegko_payment') {
        $query = parse_url($arResult['PAY_SYSTEM']['PAYMENT_URL'])['query'];
        parse_str($query, $params);

        $qrCode = \Cosmos\Content::getPaymentQrCode($arResult['ORDER']['ID'], $params['mdOrder']);
    }
	?>
	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ORDER_SUC", array(
					"#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"]->toUserTime()->format('d.m.Y H:i'),
					"#ORDER_ID#" => $arResult["ORDER"]["ACCOUNT_NUMBER"]
				))?>
				<? if (!empty($arResult['ORDER']["PAYMENT_ID"])): ?>
					<?=Loc::getMessage("SOA_PAYMENT_SUC", array(
						"#PAYMENT_ID#" => $arResult['PAYMENT'][$arResult['ORDER']["PAYMENT_ID"]]['ACCOUNT_NUMBER']
					))?>
				<? endif ?>
				<? if ($arParams['NO_PERSONAL'] !== 'Y'): ?>
					<br /><br />
					<?=Loc::getMessage('SOA_ORDER_SUC1', ['#LINK#' => $arParams['PATH_TO_PERSONAL']])?>
				<? endif; ?>
			</td>
		</tr>
	</table>
	
	<?
	if ($arResult["ORDER"]["IS_ALLOW_PAY"] === 'Y')
	{
		if (!empty($arResult["PAYMENT"]))
		{
			foreach ($arResult["PAYMENT"] as $payment)
			{
				if ($payment["PAID"] != 'Y')
				{
					if (!empty($arResult['PAY_SYSTEM_LIST'])
						&& array_key_exists($payment["PAY_SYSTEM_ID"], $arResult['PAY_SYSTEM_LIST'])
					)
					{
						$arPaySystem = $arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment["ID"]];

						if (empty($arPaySystem["ERROR"]))
						{
							if ($arPaySystem["ID"] == 10) {
							?>
							<br /><br />
							<table class="sale_order_full_table sale_order_full_table--small">
								<tr>
									<td class="finservice-block">
										<div class="ps_logo ps_logo--mini">
											<div class="pay_name"><?=Loc::getMessage("SOA_PAY") ?></div>
										</div>
										<div id="blank-modal"></div>
									</td>
								</tr>
							</table>

							<?} else { ?>
							<br /><br />

							<table class="sale_order_full_table">
								<tr>
									<td class="ps_logo">
										<div class="pay_name"><?=Loc::getMessage("SOA_PAY") ?></div>
										<?=CFile::ShowImage($arPaySystem["LOGOTIP"], 100, 100, "border=0\" style=\"width:100px\"", "", false) ?>
										<div class="paysystem_name"><?=$arPaySystem["NAME"] ?></div>
										<br/>

                                        <?/*php if ($qrCode): ?>
                                            <div class="order-payment__qr">
                                                <div class="notice">Для мгновенной оплаты, без ввода данных карты наведите камеру своего телефона на QR код</div>
                                                <?php
                                                echo '<img src="data:image/png;base64,' . $qrCode . '" />';
                                                ?>
                                            </div>
                                        <?php endif; */?>
									</td>
								</tr>
								<tr>
									<td>
										<? if (strlen($arPaySystem["ACTION_FILE"]) > 0 && $arPaySystem["NEW_WINDOW"] == "Y" && $arPaySystem["IS_CASH"] != "Y"): ?>
											<?
											$orderAccountNumber = urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]));
											$paymentAccountNumber = $payment["ACCOUNT_NUMBER"];
											?>
											<script>
												window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=$orderAccountNumber?>&PAYMENT_ID=<?=$paymentAccountNumber?>');
											</script>
										<?=Loc::getMessage("SOA_PAY_LINK", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&PAYMENT_ID=".$paymentAccountNumber))?>
										<? if (CSalePdf::isPdfAvailable() && $arPaySystem['IS_AFFORD_PDF']): ?>
										<br/>
											<?=Loc::getMessage("SOA_PAY_PDF", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&pdf=1&DOWNLOAD=Y"))?>
										<? endif ?>
										<? else: ?>
											<?=$arPaySystem["BUFFERED_OUTPUT"]?>
										<? endif ?>
									</td>
								</tr>
							</table>

							<?
							}
						}
						else
						{
							?>
							<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
							<?
						}
					}
					else
					{
						?>
						<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
						<?
					}
				}
			}
		}
	}
	else
	{
		?>
		<br /><strong><?=$arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR']?></strong>
		<?
	}
	?>

	<script>
		window.productPageView = true;
		$(document).ready(function(){
			var arrFbItems = [];
			var itemsIds = [];
			var arrVkItems = [];
			var getblueItems = '';
			var arrSlonItems = [];
			<?foreach ($arrFbItems as $k=> $item) {?>
				var fbItem = {
					//id: '<?=$item["product_id"]?>',//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					id: '<?=$item["id"]?>',
					quantity: <?=$item["quantity"]?>,
				}
				arrFbItems.push(fbItem);
				var vkItem = {
					//"id": '<?=$item["product_id"]?>'//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					id: '<?=$item["id"]?>',
				};
				arrVkItems.push(vkItem);
				//var item_id = '<?=$item["product_id"]?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
				var item_id = '<?=$item["id"]?>';
				itemsIds.push(item_id);
				<?if($k>0) {?>
					//getblueItems += ',<?=$item["product_id"]?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					getblueItems += ',<?=$item["id"]?>';
				<?} else {?>
					//getblueItems += '<?=$item["product_id"]?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					getblueItems += '<?=$item["id"]?>';
				<?}?>

				var slonItem = {
					//id: '<?=$item["product_id"]?>',//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					id: '<?=$item["id"]?>',
					quantity: <?=$item["quantity"]?>,
					price: <?=$item['price'];?>
				};
				arrSlonItems.push(slonItem);

			<?}?>
			dataLayer.push({
				'event': 'eventTarget',
				'eventCategory': 'target',
				'eventAction': 'placed_an_order',
				'eventLabel': '',
				'price': <?=$productPriceSumm;?>
			});
			//закомментил фейсбук, ломает скрипт
			// fbq('track', 'Purchase', {
			// 	value: <?=$productPriceSumm;?>,
			// 	currency: 'RUB',
			// 	contents: arrFbItems,
			// });
			//mail.ru страница транзакции
			var _tmr = window._tmr || [];
			_tmr.push({
				id: '2828139',
				type: 'itemView',
				productid: itemsIds,
				pagetype: 'purchase',
				list: '1',
				totalvalue: '<?=$productPriceSumm;?>'
			});
			//mgid страница транзакции
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
					goods: itemsIds
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

			//getblue страница транзакции
			window.blue_q = window.blue_q || [];
			window.blue_q.push(
				{event: "setCampaignId", value: "00817ED8-9D92-C9B7-14D7CD0015C22D53"}
				,{event: "setProductId", value: getblueItems} 
				,{event: "setTransactionTotal", value: "<?=$productPriceSumm;?>"} 
				,{event: "setTransactionId", value: "<?=$arResult["ORDER"]["ID"];?>"} 
				,{event: "setPageType", value: "conversion"}
			);

			// ГдеСлон
			var productPriceSum = <?= $productPriceSumm ?>;

            var slonItemCommission = {
                id: '',
                quantity: 1,
                price: productPriceSum,
            };
			const getParam = (param) => {
				const urlParams = new URL(window.location.toString()).searchParams
					return urlParams.get(param) || ''
				}
			const utm = getParam('utm_source')

            if (productPriceSum < 15000) {
                slonItemCommission['id'] = '001';
            } else if (productPriceSum >= 15000 && productPriceSum <= 25000) {
                slonItemCommission['id'] = '002';
            } else if (productPriceSum > 25000 && productPriceSum <= 30000) {
                slonItemCommission['id'] = '003';
            } else if (productPriceSum > 30000 && productPriceSum <= 35000) {
                slonItemCommission['id'] = '004';
            }  else if (productPriceSum > 35000 && productPriceSum <= 45000) {
                slonItemCommission['id'] = '005';
            }  else {
                slonItemCommission['id'] = '006';
            }

            arrSlonItems.push(slonItemCommission);

			window.gdeslon_q = window.gdeslon_q || [];
			window.gdeslon_q.push({
				page_type: "thanks",
				merchant_id: "100062",
				order_id: "<?=$arResult["ORDER"]["ID"];?>",
				products: arrSlonItems,
				deduplication: utm,
				<?if(!empty($GLOBALS['USER']->GetID())):?>user_id: "<?=$GLOBALS['USER']->GetID();?>"<?endif;?>
			});
			
			console.log('FB страница успешной транзакции');
			console.log(itemsIds);

			// CoMagic
//			console.log('comagic');
//			Comagic.trackEvent('target', 'placed_an_order');

			var arrFinserviceItems = [];
			<?foreach ($arrCreditItems as $k=> $item) {?>
				var finserviceItem = {
					name: '<?=$item["name"]?>',
					amount: '<?=$item["amount"]?>',
				}
				arrFinserviceItems.push(finserviceItem);
			<?}?>

            if (typeof blankModal !== 'undefined') {
                blankModal.init('blank-modal');
            }
			//переместил ниже, было периодически VK undefined
			//VK страница транзакции
			window.vkAsyncInit = function() {
				if (typeof window.VK !== 'undefined') {
					window.VK.Retargeting.Init('VK-RTRG-333568-aT9Ur');
					const eventParams = {
						"products" : arrVkItems,
						"total_price" : "<?=$productPriceSumm;?>"
					};
					window.VK.Retargeting.ProductEvent(2842, "purchase", eventParams);
				}
			}
			pathwidget = 'iplinevich';
			sessionId = 'a8530477-25e0-4c4d-9514-7c163a8ec35f';
			goods = JSON.stringify(arrFinserviceItems);
			orderNumber = '<?=$arResult["ORDER"]["ID"];?>';

            let paymentLink = $('.alfabank__payment-link');

            if (paymentLink.length) {
                setTimeout(function () {
                    //window.location = paymentLink.attr('href');
                }, 5000);
            }
		});
	</script>

	<?/*$arComagicSendData = COrwoFunctions::GetComagicSendData($arResult, $_REQUEST, 'order');?>
	<?if(!empty($arComagicSendData)):?>
		<script>
			$(window).load(function(){
				var comagicSendData = <?=CUtil::PhpToJSObject($arComagicSendData);?>;
				Comagic.addOfflineRequest(comagicSendData);
				console.log('comagicSendData');
				console.log(comagicSendData);
			});
		</script>
	<?endif;*/?>

<? else: ?>

	<b><?=Loc::getMessage("SOA_ERROR_ORDER")?></b>
	<br /><br />

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST", ["#ORDER_ID#" => htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"])])?>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST1")?>
			</td>
		</tr>
	</table>

<? endif ?>