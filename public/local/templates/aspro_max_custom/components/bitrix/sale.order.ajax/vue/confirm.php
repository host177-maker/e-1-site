<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

if ($arParams["SET_TITLE"] == "Y") {
	$APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));
}
Asset::getInstance()->addString('<script src="https://blank.abc-cred.ru:8798/js/blank-modal.js" type="text/javascript"></script>');
?>

<? if (!empty($_REQUEST["ORDER_ID"])) : ?>
	<? $order =  \Bitrix\Sale\Order::load($_REQUEST["ORDER_ID"]);
	$paymentCollection = $order->getPaymentCollection();
	foreach ($paymentCollection as $payment) {
		$sum = $payment->getSum(); // сумма к оплате
		$isPaid = $payment->isPaid(); // true, если оплачена
		$isReturned = $payment->isReturn(); // true, если возвращена

		$ps = $payment->getPaySystem(); // платежная система (объект Sale\PaySystem\Service)
		$psID = $payment->getPaymentSystemId(); // ID платежной системы
		$psName = $payment->getPaymentSystemName(); // название платежной системы
		$isInnerPs = $payment->isInner(); // true, если это оплата с внутреннего счета
	}
	$arResult['ORDER'] = $order->getFields();
	$arResult["ORDER"]["IS_ALLOW_PAY"] = $order->isAllowPay();
	$propertyCollection = $order->getPropertyCollection();
	$sUserPhone = '';
	foreach ($propertyCollection as $property) {
		if ($property->getField('CODE') == "PHONE") $sUserPhone = $property->getValue();
	}
	?>

	<?
	//Массивы, переменные для передачи в FB
	$arrFbItems = array();
	$productPriceSumm = 0;
	//Заберем данные заказа
	$dbBasketItems = \CSaleBasket::GetList(
		array("NAME" => "ASC", "ID" => "ASC"),
		array("LID" => SITE_ID, "ORDER_ID" => $_REQUEST["ORDER_ID"]),
		false,
		false,
		array("PRODUCT_ID", "NAME", "PRICE", "BASE_PRICE", "QUANTITY")
	);
	while ($arItem = $dbBasketItems->Fetch()) {
		if ($arItem["BASE_PRICE"] != 0) {
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
					'full_ptice' => ((float)$arItem["PRICE"] * $arItem["QUANTITY"]),
				);
			} else {
				$productInfo = array(
					'id' => $arItem["PRODUCT_ID"],
					'product_id' => $arItem["PRODUCT_ID"],
					'quantity' => $arItem["QUANTITY"],
					'name' => $arItem["NAME"],
					'price' => $arItem["PRICE"],
					'full_ptice' => ((float)$arItem["PRICE"] * $arItem["QUANTITY"]),
				);
			}
			$productPriceSumm = (int)$productPriceSumm + ((int)$arItem['PRICE'] * (int)$arItem['QUANTITY']);
			//$productObj = json_encode($productInfo);
			$arrFbItems[] = $productInfo;
			$arrSlon[]    = array('id' => $arItem['PRODUCT_ID'], 'price' => $arItem['PRICE'], 'quantity' => $arItem['QUANTITY']);
		}
	}
	//Массивы, переменные для передачи в abc-cred
	$arrCreditItems = array();
	foreach ($arrFbItems as $item) {
		for ($i = 0; $i < $item["quantity"]; $i++) {
			$creditItem = array(
				'name'   => htmlspecialchars($item["name"]),
				'amount' => (float)$item["price"],
			);
			$arrCreditItems[] = $creditItem;
		}
	}

	$qrCode = '';
	if ($order->isAllowPay()) {
		foreach ($paymentCollection as $payment) {
			$arResult["PAYMENT"][$payment->getId()] = $payment->getFieldValues();
		}
		$arResult['PAY_SYSTEM']['ACTION_FILE'] = \Bitrix\Sale\PaySystem\Manager::getList(array(
			'filter'  => array(
				'ACTIVE' => 'Y',
				'ID' => $payment->getPaymentSystemId()
			)

		))->Fetch()["ACTION_FILE"];
	}

	if ($arResult['PAY_SYSTEM']['ACTION_FILE'] == 'kupilegko_payment') {
		$query = parse_url($arResult['PAY_SYSTEM']['PAYMENT_URL'])['query'];
		parse_str($query, $params);

		$qrCode = \Cosmos\Content::getPaymentQrCode($arResult['ORDER']['ID'], $params['mdOrder']);
	}
	?>
	<table class="sale_order_full_table">
		<tr>
			<td>
				<?= Loc::getMessage("SOA_ORDER_SUC", array(
					"#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"]->toUserTime()->format('d.m.Y H:i'),
					"#ORDER_ID#" => $arResult["ORDER"]["ACCOUNT_NUMBER"]
				)) ?>
				<? if (!empty($arResult['ORDER']["PAYMENT_ID"])) : ?>
					<?= Loc::getMessage("SOA_PAYMENT_SUC", array(
						"#PAYMENT_ID#" => $arResult['PAYMENT'][$arResult['ORDER']["PAYMENT_ID"]]['ACCOUNT_NUMBER']
					)) ?>
				<? endif ?>
				<? if ($arParams['NO_PERSONAL'] !== 'Y') : ?>
					<br /><br />
					<?= Loc::getMessage('SOA_ORDER_SUC1', ['#LINK#' => $arParams['PATH_TO_PERSONAL']]) ?>
				<? endif; ?>
			</td>
		</tr>
	</table>

	<?
	if ($arResult["ORDER"]["IS_ALLOW_PAY"]) {
		if (!empty($arResult["PAYMENT"])) {
			foreach ($paymentCollection as $payment) {
				if (!$payment->isPaid() && !$order->getPaymentCollection()->isEmpty()) {
					if ($payment) {

						$paySystemService = \Bitrix\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
						if (!empty($paySystemService)) {
							$arPaySysAction = $paySystemService->getFieldsValues();
							if ($paySystemService->getField('NEW_WINDOW') === 'N' || $paySystemService->getField('ID') ==  \Bitrix\Sale\PaySystem\Manager::getInnerPaySystemId()) {
								$context = \Bitrix\Main\Application::getInstance()->getContext();
								$initResult = $paySystemService->initiatePay($payment, $context->getRequest(),  \Bitrix\Sale\PaySystem\BaseServiceHandler::STRING);
								if ($initResult->isSuccess())
									$arPaySysAction['BUFFERED_OUTPUT'] = $initResult->getTemplate(); // получаем форму оплаты из обработчика
								else
									$arPaySysAction["ERROR"] = $initResult->getErrorMessages();
							}
						}
						$arPaySystem = $payment->getPaySystem();
						if ($arPaySystem) {
							if ((int)$arPaySystem->getField("ID") == 10) {?>
							
								<br /><br />
								<table class="sale_order_full_table sale_order_full_table--small">
									<tr>
										<td class="finservice-block">
											<div class="ps_logo ps_logo--mini">
												<div class="pay_name"><?= Loc::getMessage("SOA_PAY") ?></div>
											</div>
											<div id="blank-modal"></div>
										</td>
									</tr>
								</table>

							<? } else if ((int)$arPaySystem->getField("ID") == 13) { ?>
								<table class="sale_order_full_table">
									<tr>
										<td class="ps_logo">
											<div class="pay_name"><?= Loc::getMessage("SOA_PAY") ?></div>
											<div class="paysystem_name">
												<yandex-pay-badge merchant-id="5ef67561-d34e-45e2-bb25-8320a4653548" type="cashback" amount="<?=$sum?>" size="l" theme="light" align="left" color="primary" />
											</div>
											<br />
											<? if ($arPaySysAction['BUFFERED_OUTPUT']) : ?>
												<?= $arPaySysAction["BUFFERED_OUTPUT"] ?>
											<? endif ?>
										</td>
									</tr>
								</table>

							<? } else if ((int)$arPaySystem->getField("ID") == 16) { ?>
							<table class="sale_order_full_table">
								<tr>
									<td class="ps_logo">
										<div class="pay_name"><?= Loc::getMessage("SOA_PAY") ?></div>
										<br />
										<? if ($arPaySysAction['BUFFERED_OUTPUT']) : ?>
											<?= $arPaySysAction["BUFFERED_OUTPUT"] ?>
										<? endif ?>
									</td>
								</tr>
							</table>

							<? } else { ?>
								<br /><br />

								<table class="sale_order_full_table">
									<tr>
										<td class="ps_logo">
											<div class="pay_name"><?= Loc::getMessage("SOA_PAY") ?></div>
											<?= CFile::ShowImage($arPaySystem->getField("LOGOTIP"), 100, 100, "border=0\" style=\"width:100px\"", "", false) ?>
											<div class="paysystem_name"><?= $arPaySystem->getField("NAME") ?></div>
											<br />

											<?php if ($qrCode) : ?>
												<div class="order-payment__qr">
													<div class="notice">Для мгновенной оплаты, без ввода данных карты наведите камеру своего телефона на QR код</div>
													<?php
													echo '<img src="data:image/png;base64,' . $qrCode . '" />';
													?>
												</div>
											<?php endif; ?>
										</td>
									</tr>
									<tr>
										<td>
											<? if ($arPaySystem->getField("ACTION_FILE") <> '' && $arPaySystem->getField("NEW_WINDOW") == "Y" && $arPaySystem->getField("IS_CASH") != "Y") : ?>
												<?
												$orderAccountNumber = urlencode(urlencode($_REQUEST["ORDER_ID"]));
												$paymentAccountNumber = $arResult["PAYMENT"][$payment->getId()]["ACCOUNT_NUMBER"];
												?>
												<script>
													window.open('<?= $arParams["PATH_TO_PAYMENT"] ?>?ORDER_ID=<?= $orderAccountNumber ?>&PAYMENT_ID=<?= $paymentAccountNumber ?>');
												</script>
												<?= Loc::getMessage("SOA_PAY_LINK", array("#LINK#" => $arParams["PATH_TO_PAYMENT"] . "?ORDER_ID=" . $orderAccountNumber . "&PAYMENT_ID=" . $paymentAccountNumber)) ?>
												<? if (CSalePdf::isPdfAvailable() && $arPaySystem->getField("CAN_PRINT_CHECK") == "Y") : ?>
													<br />
													<?= Loc::getMessage("SOA_PAY_PDF", array("#LINK#" => $arParams["PATH_TO_PAYMENT"] . "?ORDER_ID=" . $orderAccountNumber . "&pdf=1&DOWNLOAD=Y")) ?>
												<? endif ?>
											<? else : ?>
												<? if ($arPaySysAction['BUFFERED_OUTPUT']) : ?>
													<?= $arPaySysAction["BUFFERED_OUTPUT"] ?>
												<? endif ?>
											<? endif ?>
										</td>
									</tr>
								</table>

							<?
							}
						} else {
							?>
							<span style="color:red;"><?= Loc::getMessage("SOA_ORDER_PS_ERROR") ?></span>
						<?
						}
					} else {
						?>
						<span style="color:red;"><?= Loc::getMessage("SOA_ORDER_PS_ERROR") ?></span>
		<?
					}
				}
			}
		}
	} else {
		?>
		<br /><strong><?= $arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR'] ?></strong>
	<?
	}
	?>
	<? // $oredrEcomData = \CUtil::PhpToJSObject($arResult['ORDER_ECOM_DATA']); 
	?>
	<script>
		window.productPageView = true;
		$(document).ready(function() {
			var arrFbItems = [];
			var itemsIds = [];
			var arrVkItems = [];
			var getblueItems = '';
			var arrSlonItems = [];
			<? foreach ($arrFbItems as $k => $item) { ?>
				var fbItem = {
					//id: '<?= $item["product_id"] ?>',//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					id: '<?= $item["id"] ?>',
					quantity: <?= $item["quantity"] ?>,
				}
				arrFbItems.push(fbItem);
				var vkItem = {
					//"id": '<?= $item["product_id"] ?>'//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					id: '<?= $item["id"] ?>',
				};
				arrVkItems.push(vkItem);
				//var item_id = '<?= $item["product_id"] ?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
				var item_id = '<?= $item["id"] ?>';
				itemsIds.push(item_id);
				<? if ($k > 0) { ?>
					//getblueItems += ',<?= $item["product_id"] ?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					getblueItems += ',<?= $item["id"] ?>';
				<? } else { ?>
					//getblueItems += '<?= $item["product_id"] ?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					getblueItems += '<?= $item["id"] ?>';
				<? } ?>

				var slonItem = {
					//id: '<?= $item["product_id"] ?>',//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
					id: '<?= $item["id"] ?>',
					quantity: <?= $item["quantity"] ?>,
					price: <?= $item['price']; ?>
				};
				arrSlonItems.push(slonItem);

			<? } ?>
			console.log('placed_an_order');
			dataLayer.push({
				'event': 'eventTarget',
				'eventCategory': 'target',
				'eventAction': 'placed_an_order',
				'eventLabel': '',
				'price': <?= $productPriceSumm; ?>
			});
			//закомментил фейсбук, ломает скрипт
			// fbq('track', 'Purchase', {
			// 	value: <?= $productPriceSumm; ?>,
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
				totalvalue: '<?= $productPriceSumm; ?>'
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
			window.blue_q.push({
				event: "setCampaignId",
				value: "00817ED8-9D92-C9B7-14D7CD0015C22D53"
			}, {
				event: "setProductId",
				value: getblueItems
			}, {
				event: "setTransactionTotal",
				value: "<?= $productPriceSumm; ?>"
			}, {
				event: "setTransactionId",
				value: "<?= $arResult["ORDER"]["ID"]; ?>"
			}, {
				event: "setPageType",
				value: "conversion"
			});

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
			} else if (productPriceSum > 35000 && productPriceSum <= 45000) {
				slonItemCommission['id'] = '005';
			} else {
				slonItemCommission['id'] = '006';
			}
			arrSlonItems.push(JSON.parse('<?=json_encode($arrSlon)?>'));
			arrSlonItems.push(slonItemCommission);

			window.gdeslon_q = window.gdeslon_q || [];
			window.gdeslon_q.push({
				page_type: "thanks",
				merchant_id: "100062",
				order_id: "<?= $arResult["ORDER"]["ID"] . '_' . $sUserPhone . '_' . time(); ?>",
				products: arrSlonItems,
				deduplication: gdeSlonUtm(),
				<? if (!empty($GLOBALS['USER']->GetID())) : ?>user_id: "<?= $GLOBALS['USER']->GetID(); ?>"
			<? endif; ?>
			});
			// CoMagic
			//			console.log('comagic');
			//			Comagic.trackEvent('target', 'placed_an_order');

			var arrFinserviceItems = [];
			<? foreach ($arrCreditItems as $k => $item) { ?>
				var finserviceItem = {
					name: '<?= $item["name"] ?>',
					amount: '<?= $item["amount"] ?>',
				}
				arrFinserviceItems.push(finserviceItem);
			<? } ?>

			if (typeof blankModal !== 'undefined') {
				blankModal.init('blank-modal');
			}
			//переместил ниже, было периодически VK undefined
			//VK страница транзакции
			window.vkAsyncInit = function() {
				if (typeof window.VK !== 'undefined') {
					window.VK.Retargeting.Init('VK-RTRG-333568-aT9Ur');
					const eventParams = {
						"products": arrVkItems,
						"total_price": "<?= $productPriceSumm; ?>"
					};
					window.VK.Retargeting.ProductEvent(2842, "purchase", eventParams);
				}
			}
			pathwidget = 'e1south';
			sessionId = '72cdd45a-3dc3-48be-be9e-7b5b3c899e65';
			goods = JSON.stringify(arrFinserviceItems);
			orderNumber = '<?= $arResult["ORDER"]["ID"]; ?>';
			imageSource = '';
			imageMaxWidth = '150px';
			let paymentLink = $('.alfabank__payment-link');

			if (paymentLink.length) {
				setTimeout(function() {
					//window.location = paymentLink.attr('href');
				}, 5000);
			}

			//ecommerce event purchase
			<?/*if($arResult['ORDER_ECOM_DATA']){*/ ?>
			/*
			            ecommerceOnOrderSuccess(<?php /*= $oredrEcomData*/ ?>);
			            */
			<?/*}*/ ?>

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
	<?endif;*/ ?>

<? else : ?>

	<b><?= Loc::getMessage("SOA_ERROR_ORDER") ?></b>
	<br /><br />

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?= Loc::getMessage("SOA_ERROR_ORDER_LOST", ["#ORDER_ID#" => htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"])]) ?>
				<?= Loc::getMessage("SOA_ERROR_ORDER_LOST1") ?>
			</td>
		</tr>
	</table>

<? endif ?>