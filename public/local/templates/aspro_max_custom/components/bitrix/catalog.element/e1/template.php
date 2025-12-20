<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? $this->setFrameMode(true); ?>
<?

use \Bitrix\Main\Localization\Loc,
	\E_1\Quantities;

$arParams["SHOW_DISCOUNT_PERCENT"] = "Y";

?>

<? ob_start(); ?>
<div class="basket_props_block" id="bx_basket_div_<?= $arResult["ID"]; ?>" style="display: none;" data-region-id="<?= $GLOBALS['arRegion']['ID']; ?>">
	<? if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])) {
		foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo) { ?>
			<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>">
		<? if (isset($arResult['PRODUCT_PROPERTIES'][$propID]))
				unset($arResult['PRODUCT_PROPERTIES'][$propID]);
		}
	}
	$arResult["EMPTY_PROPS_JS"] = "Y";
	$emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
	if (!$emptyProductProperties) {
		$arResult["EMPTY_PROPS_JS"] = "N"; ?>
		<div class="wrapper">
			<table>
				<? foreach ($arResult['PRODUCT_PROPERTIES'] as $propID => $propInfo) { ?>
					<tr>
						<td><? echo $arResult['PROPERTIES'][$propID]['NAME']; ?></td>
						<td>
							<? if ('L' == $arResult['PROPERTIES'][$propID]['PROPERTY_TYPE'] && 'C' == $arResult['PROPERTIES'][$propID]['LIST_TYPE']) {
								foreach ($propInfo['VALUES'] as $valueID => $value) { ?>
									<label>
										<input type="radio" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"checked"' : ''); ?>><? echo $value; ?>
									</label>
								<? }
							} else { ?>
								<select name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]">
									<? foreach ($propInfo['VALUES'] as $valueID => $value) { ?>
										<option value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"selected"' : ''); ?>><? echo $value; ?></option>
									<? } ?>
								</select>
							<? } ?>
						</td>
					</tr>
				<? } ?>
			</table>
		</div>
	<? } ?>
</div>

<?
$currencyList = '';
if (!empty($arResult['CURRENCIES'])) {
	$templateLibrary[] = 'currency';
	$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}


$templateData = array(
	'TEMPLATE_LIBRARY' => $templateLibrary,
	'CURRENCIES' => $currencyList,
	'STORES' => array(
		"USE_STORE_PHONE" => $arParams["USE_STORE_PHONE"],
		"SCHEDULE" => $arParams["SCHEDULE"],
		"USE_MIN_AMOUNT" => $arParams["USE_MIN_AMOUNT"],
		"MIN_AMOUNT" => $arParams["MIN_AMOUNT"],
		"ELEMENT_ID" => $arResult["ID"],
		"STORE_PATH"  =>  $arParams["STORE_PATH"],
		"MAIN_TITLE"  =>  $arParams["MAIN_TITLE"],
		"MAX_AMOUNT" => $arParams["MAX_AMOUNT"],
		"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
		"SHOW_EMPTY_STORE" => $arParams['SHOW_EMPTY_STORE'],
		"SHOW_GENERAL_STORE_INFORMATION" => $arParams['SHOW_GENERAL_STORE_INFORMATION'],
		"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
		"USER_FIELDS" => $arParams['USER_FIELDS'],
		"FIELDS" => $arParams['FIELDS'],
		"STORES_FILTER_ORDER" => $arParams['STORES_FILTER_ORDER'],
		"STORES_FILTER" => $arParams['STORES_FILTER'],
		"STORES" => $arParams['STORES'] = array_diff($arParams['STORES'], array('')),
		"SET_ITEMS" => $arResult["SET_ITEMS"],
	),
	'OFFERS_INFO' => array(
		'OFFERS' => $arResult['OFFERS'],
		'OFFER_GROUP' => $arResult['OFFER_GROUP'],
		'OFFERS_IBLOCK' => $arResult['OFFERS_IBLOCK'],
	),
	'LINK_SALES' => $arResult['STOCK'],
	'LINK_SERVICES' => $arResult['SERVICES'],
	'LINK_NEWS' => $arResult['NEWS'],
	'LINK_TIZERS' => $arParams['SECTION_TIZERS'],
	'LINK_REVIEWS' => $arResult['LINK_REVIEWS'],
	'LINK_BLOG' => $arResult['BLOG'],
	'LINK_STAFF' => $arResult['LINK_STAFF'],
	'LINK_VACANCY' => $arResult['LINK_VACANCY'],
	'CATALOG_SETS' => array(
		'SET_ITEMS_QUANTITY' => $arResult["SET_ITEMS_QUANTITY"],
		'SET_ITEMS' => $arResult["SET_ITEMS"]
	),
	'VIDEO' => $arResult['VIDEO'],
	'OUR_WORK' => (!empty($arResult['PROPERTIES']['SERIYA_SHKAFA']['VALUE']) && !empty($arResult['PROPERTIES']['TIP_SHKAFA']['VALUE'])),
	'ASSOCIATED' => $arResult['ASSOCIATED'],
	'EXPANDABLES' => $arResult['EXPANDABLES'],
	'REVIEWS_COUNT' => is_array($arResult['PROPERTIES']['BLOG_COMMENTS_CNT']['VALUE']) && !empty($arResult['PROPERTIES']['BLOG_COMMENTS_CNT']['VALUE']) ? count($arResult['PROPERTIES']['BLOG_COMMENTS_CNT']['VALUE']) : 0,
);
unset($currencyList, $templateLibrary);

if ($arResult["PROPERTIES"]["YM_ELEMENT_ID"] && $arResult["PROPERTIES"]["YM_ELEMENT_ID"]["VALUE"])
	$templateData["YM_ELEMENT_ID"] = $arResult["PROPERTIES"]["YM_ELEMENT_ID"]["VALUE"];

$arSkuTemplate = array();
if (!empty($arResult['SKU_PROPS']))

	$arSkuTemplate = CMaxCustom::GetSKUPropsArray($arResult['SKU_PROPS'], $arResult["SKU_IBLOCK_ID"], "list", $arParams["OFFER_HIDE_NAME_PROPS"], "N", array(), $arParams['OFFER_SHOW_PREVIEW_PICTURE_PROPS']);

$strMainID = $this->GetEditAreaId($arResult['ID']);
$strObName = 'ob' . preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);

$arResult["strMainID"] = $this->GetEditAreaId($arResult['ID']);
$arItemIDs = CMax::GetItemsIDs($arResult, "Y");
$templateData['TOTAL_COUNT'] = $totalCount = CMax::GetTotalCount($arResult, $arParams);

$useStoreClick = ($arParams["USE_STORE"] == "Y" && $arResult["STORES_COUNT"] && $arResult['CATALOG_TYPE'] != CCatalogProduct::TYPE_SET && (!$arResult["OFFERS"] || ($arResult["OFFERS"] && $arParams["TYPE_SKU"] != "N")) ? "Y" : "N");

$templateData['ID_OFFER_GROUP'] = $arItemIDs['ALL_ITEM_IDS']['OFFER_GROUP'];

$arParams["BASKET_ITEMS"] = ($arParams["BASKET_ITEMS"] ? $arParams["BASKET_ITEMS"] : array());
$useStores = $arParams["USE_STORE"] == "Y" && $arResult["STORES_COUNT"] && $arQuantityData["RIGHTS"]["SHOW_QUANTITY"] && $arResult['CATALOG_TYPE'] != CCatalogProduct::TYPE_SET;
$templateData['STORES']['USE_STORES'] = $useStores;

$showCustomOffer = (($arResult['OFFERS'] && $arParams["TYPE_SKU"] != "N") ? true : false);
if ($showCustomOffer)
	$templateData['JS_OBJ'] = $strObName;

$strMeasure = '';
$arAddToBasketData = array();

$templateData['STR_ID'] = $strObName;
$item_id = $arResult["ID"];

$currentSKUID = $currentSKUIBlock = '';

$bUseSkuProps = ($arResult["OFFERS"] && !empty($arResult['OFFERS_PROP']));

if ($arResult["OFFERS"]) {
	$strMeasure = $arResult["MIN_PRICE"]["CATALOG_MEASURE_NAME"];
	$templateData["STORES"]["OFFERS"] = "Y";
	foreach ($arResult["OFFERS"] as $arOffer) {
		$templateData["STORES"]["OFFERS_ID"][] = $arOffer["ID"];
	}
} else {
	if (($arParams["SHOW_MEASURE"] == "Y") && ($arResult["CATALOG_MEASURE"])) {
		$arMeasure = CCatalogMeasure::getList(array(), array("ID" => $arResult["CATALOG_MEASURE"]), false, false, array())->GetNext();
		$strMeasure = $arMeasure["SYMBOL_RUS"];
	}
	$arParams["RESTRICTION"] = $arResult["RESTRICTION"];
	$arAddToBasketData = CMax::GetAddToBasketArray($arResult, $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], true, $arItemIDs["ALL_ITEM_IDS"], 'btn-lg', $arParams);
}
$arOfferProps = implode(';', $arParams['OFFERS_CART_PROPERTIES']);

// save item viewed
$arFirstPhoto = reset($arResult['MORE_PHOTO']);
$arItemPrices = $arResult['MIN_PRICE'];
if (isset($arResult['PRICE_MATRIX']) && $arResult['PRICE_MATRIX']) {
	$rangSelected = $arResult['ITEM_QUANTITY_RANGE_SELECTED'];
	$priceSelected = $arResult['ITEM_PRICE_SELECTED'];
	if (isset($arResult['FIX_PRICE_MATRIX']) && $arResult['FIX_PRICE_MATRIX']) {
		$rangSelected = $arResult['FIX_PRICE_MATRIX']['RANGE_SELECT'];
		$priceSelected = $arResult['FIX_PRICE_MATRIX']['PRICE_SELECT'];
	}
	$arItemPrices = $arResult['ITEM_PRICES'][$priceSelected];
	$arItemPrices['VALUE'] = $arItemPrices['BASE_PRICE'];
	$arItemPrices['PRINT_VALUE'] = \Aspro\Functions\CAsproMaxItem::getCurrentPrice('BASE_PRICE', $arItemPrices);
	$arItemPrices['DISCOUNT_VALUE'] = $arItemPrices['PRICE'];
	$arItemPrices['PRINT_DISCOUNT_VALUE'] = \Aspro\Functions\CAsproMaxItem::getCurrentPrice('PRICE', $arItemPrices);
}
$arViewedData = array(
	'PRODUCT_ID' => $arResult['ID'],
	'IBLOCK_ID' => $arResult['IBLOCK_ID'],
	'NAME' => $arResult['NAME'],
	'DETAIL_PAGE_URL' => $arResult['DETAIL_PAGE_URL'],
	'PICTURE_ID' => $arResult['PREVIEW_PICTURE'] ? $arResult['PREVIEW_PICTURE']['ID'] : ($arFirstPhoto ? $arFirstPhoto['ID'] : false),
	'CATALOG_MEASURE_NAME' => $arResult['CATALOG_MEASURE_NAME'],
	'MIN_PRICE' => $arItemPrices,
	'CAN_BUY' => $arResult['CAN_BUY'] ? 'Y' : 'N',
	'IS_OFFER' => 'N',
	'WITH_OFFERS' => $arResult['OFFERS'] ? 'Y' : 'N',
);
$actualItem = $arResult["OFFERS"] ? (isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]) ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']] : reset($arResult['OFFERS'])) : $arResult;
if ($arResult["OFFERS"] && $arParams["TYPE_SKU"] == "N")
	unset($templateData['STORES']);
//сделал чтобы брался текущий OFFER_ID
if (!empty($_REQUEST['oid'])) {
	$arResult['RES']['OFFER_ID'] = $_REQUEST['oid'];
} else {
	$arResult['RES']['OFFER_ID'] = empty($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']) ? $arResult['ID'] : $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'];
}
//$templateData['QUANTITY_DATA'] = $arQuantityData = CMax::GetQuantityArray($totalCount, $arItemIDs["ALL_ITEM_IDS"], $useStoreClick);
if ($arResult['RES']['OFFER_ID']) {
	$templateData['QUANTITY_DATA'] = $arQuantityData = Quantities::GetProductQuantity($arResult['RES']['OFFER_ID']);
} else {
	$templateData['QUANTITY_DATA'] = $arQuantityData = Quantities::GetProductQuantity($arResult['ID']);
}
$iCountProps = count($arResult['DISPLAY_PROPERTIES']); ?>

<? if ($arResult["OFFERS"] && $arParams["TYPE_SKU"] == "N") : ?>
	<? $templateData['OFFERS_INFO']['OFFERS_MORE'] = true; ?>
	<?
	$showSkUName = ((in_array('NAME', $arParams['OFFERS_FIELD_CODE'])));
	$showSkUImages = false;
	if (((in_array('PREVIEW_PICTURE', $arParams['OFFERS_FIELD_CODE']) || in_array('DETAIL_PICTURE', $arParams['OFFERS_FIELD_CODE'])))) {
		foreach ($arResult["OFFERS"] as $key => $arSKU) {
			if ($arSKU['PREVIEW_PICTURE'] || $arSKU['DETAIL_PICTURE']) {
				$showSkUImages = true;
				break;
			}
		}
	} ?>

	<? //list offers TYPE_2
	?>
	<? if ($arResult["OFFERS"] && $arParams["TYPE_SKU"] !== "TYPE_1") : ?>
		<? $this->SetViewTarget('PRODUCT_OFFERS_INFO'); ?>
		<div class="list-offers ajax_load">
			<div class="bx_sku_props" style="display:none;">
				<? $arSkuKeysProp = '';
				$propSKU = $arParams["OFFERS_CART_PROPERTIES"];
				if ($propSKU) {
					$arSkuKeysProp = base64_encode(serialize(array_keys($propSKU)));
				} ?>
				<input type="hidden" value="<?= $arSkuKeysProp; ?>" />
			</div>
			<div class="table-view flexbox flexbox--row">
				<? foreach ($arResult["OFFERS"] as $key => $arSKU) : ?>
					<?
					if ($arResult["PROPERTIES"]["CML2_BASE_UNIT"]["VALUE"])
						$sMeasure = $arResult["PROPERTIES"]["CML2_BASE_UNIT"]["VALUE"];
					else
						$sMeasure = GetMessage("MEASURE_DEFAULT");

					$skutotalCount = $arSKU['TOTAL_COUNT'];
					$arskuQuantityData = CMax::GetQuantityArray($skutotalCount);

					$arSKU["IBLOCK_ID"] = $arResult["IBLOCK_ID"];
					$arSKU["IS_OFFER"] = "Y";
					$arskuAddToBasketData = $arSKU['ADD_TO_BASKET_DATA'];
					$arskuAddToBasketData["HTML"] = str_replace('data-item', 'data-props="' . $arOfferProps . '" data-item', $arskuAddToBasketData["HTML"]);
					?>
					<div class="table-view__item item bordered box-shadow main_item_wrapper <?= ($useStores ? "table-view__item--has-stores" : ""); ?>">
						<div class="table-view__item-wrapper item_info catalog-adaptive flexbox flexbox--row">
							<? if ($showSkUImages) : ?>
								<? //image-block
								?>
								<div class="item-foto">
									<div class="item-foto__picture">
										<? \Aspro\Functions\CAsproMaxItem::showImg($arParams, $arSKU, false, false); ?>
									</div>
									<div class="adaptive">
										<? \Aspro\Functions\CAsproMaxItem::showDelayCompareBtn($arParams, $arSKU, $arskuAddToBasketData, $skutotalCount, '', 'block', true, false, '_small'); ?>
									</div>
								</div>
							<? endif; ?>

							<? //text-block
							?>
							<div class="item-info">
								<div class="item-title font_sm"><?= $arSKU['NAME'] ?></div>
								<div class="quantity_block_wrapper">
									<? if ($arQuantityData["RIGHTS"]["SHOW_QUANTITY"]) : ?>
										<?= $arskuQuantityData["HTML"]; ?>
									<? endif; ?>
									<? if ($arSKU['PROPERTIES']['ARTICLE']['VALUE']) : ?>
										<div class="font_sxs muted article">
											<span class="name"><?= Loc::getMessage('ARTICLE_COMPACT'); ?></span><span class="value"><?= $arSKU['PROPERTIES']['ARTICLE']['VALUE']; ?></span>
										</div>
									<? endif; ?>
								</div>
								<? if ($arResult["SKU_PROPERTIES"]) : ?>
									<div class="properties list">
										<div class="properties__container properties props_list">
											<? foreach ($arResult["SKU_PROPERTIES"] as $key => $arProp) { ?>
												<? if (!$arProp["IS_EMPTY"] && $key != 'ARTICLE') : ?>
													<div class="properties__item properties__item--compact ">
														<div class="properties__title muted properties__item--inline char_name font_sxs">
															<? if ($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y") : ?><div class="hint"><span class="icon"><i>?</i></span>
																	<div class="tooltip"><?= $arProp["HINT"] ?></div>
																</div><? endif; ?>
															<span class="props_item"><?= $arProp["NAME"] ?>:</span>
														</div>
														<div class="properties__value darken properties__item--inline char_value font_xs">
															<? if ($arResult["TMP_OFFERS_PROP"][$arProp["CODE"]]) {
																echo $arResult["TMP_OFFERS_PROP"][$arProp["CODE"]]["VALUES"][$arSKU["TREE"]["PROP_" . $arProp["ID"]]]["NAME"]; ?>
															<? } else {
																if (is_array($arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"])) {
																	echo implode("/", $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"]);
																} else {
																	if ($arSKU["PROPERTIES"][$arProp["CODE"]]["USER_TYPE"] == "directory" && isset($arSKU["PROPERTIES"][$arProp["CODE"]]["USER_TYPE_SETTINGS"]["TABLE_NAME"])) {
																		$rsData = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => $arSKU["PROPERTIES"][$arProp["CODE"]]["USER_TYPE_SETTINGS"]["TABLE_NAME"])));
																		if ($arData = $rsData->fetch()) {
																			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arData);
																			$entityDataClass = $entity->getDataClass();
																			$arFilter = array(
																				'limit' => 1,
																				'filter' => array(
																					'=UF_XML_ID' => $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"]
																				)
																			);
																			$arValue = $entityDataClass::getList($arFilter)->fetch();
																			if (isset($arValue["UF_NAME"]) && $arValue["UF_NAME"]) {
																				echo $arValue["UF_NAME"];
																			} else {
																				echo $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"];
																			}
																		}
																	} else {
																		echo $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"];
																	}
																}
															} ?>
														</div>
													</div>
												<? endif; ?>
											<? } ?>
										</div>
									</div>
								<? endif; ?>
							</div>

							<div class="item-actions flexbox flexbox--row">
								<? //prices-block
								?>
								<div class="item-price">
									<div class="cost prices clearfix">
										<? if (isset($arSKU['PRICE_MATRIX']) && $arSKU['PRICE_MATRIX']) : // USE_PRICE_COUNT
										?>
											<? if (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arSKU['ITEM_PRICE_MODE'] == 'Q' || (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') != 'Y' && $arSKU['ITEM_PRICE_MODE'] != 'Q' && count($arSKU['PRICE_MATRIX']['COLS']) <= 1)) : ?>
												<?= CMax::showPriceRangeTop($arSKU, $arParams, Loc::getMessage("CATALOG_ECONOMY")); ?>
											<? endif; ?>
											<? if (count($arSKU['PRICE_MATRIX']['ROWS']) > 1 || count($arSKU['PRICE_MATRIX']['COLS']) > 1) : ?>
												<?= CMax::showPriceMatrix($arSKU, $arParams, $sMeasure, $arskuAddToBasketData); ?>
											<? endif; ?>
										<? elseif ($arSKU["PRICES"]) : ?>
											<?
											\Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arSKU["PRICES"], $sMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));
											?>
										<? endif; ?>
									</div>

									<div class="basket_props_block" id="bx_basket_div_<?= $arSKU["ID"]; ?>" style="display: none;">
										<? if (!empty($arSKU['PRODUCT_PROPERTIES_FILL'])) {
											foreach ($arSKU['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo) { ?>
												<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>">
											<? if (isset($arSKU['PRODUCT_PROPERTIES'][$propID]))
													unset($arSKU['PRODUCT_PROPERTIES'][$propID]);
											}
										}
										$arSKU["EMPTY_PROPS_JS"] = "Y";
										$emptyProductProperties = empty($arSKU['PRODUCT_PROPERTIES']);
										if (!$emptyProductProperties) {
											$arSKU["EMPTY_PROPS_JS"] = "N"; ?>
											<div class="wrapper">
												<table>
													<? foreach ($arSKU['PRODUCT_PROPERTIES'] as $propID => $propInfo) { ?>
														<tr>
															<td><? echo $arSKU['PROPERTIES'][$propID]['NAME']; ?></td>
															<td>
																<? if ('L' == $arSKU['PROPERTIES'][$propID]['PROPERTY_TYPE']	&& 'C' == $arSKU['PROPERTIES'][$propID]['LIST_TYPE']) {
																	foreach ($propInfo['VALUES'] as $valueID => $value) { ?>
																		<label>
																			<input type="radio" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"checked"' : ''); ?>><? echo $value; ?>
																		</label>
																	<? }
																} else { ?>
																	<select name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]"><?
																		foreach ($propInfo['VALUES'] as $valueID => $value) { ?>
																			<option value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"selected"' : ''); ?>><? echo $value; ?></option>
																		<? } ?>
																	</select>
																<? } ?>
															</td>
														</tr>
													<? } ?>
												</table>
											</div>
										<?
										} ?>
									</div>
								</div>

								<? //buttons-block
								?>
								<div class="item-buttons item_<?= $arSKU["ID"] ?>">
									<div class="counter_wrapp list clearfix n-mb small-block">
										<? if ($arskuAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] && !count($arSKU["OFFERS"]) && $arskuAddToBasketData["ACTION"] == "ADD" && $arskuAddToBasketData["CAN_BUY"]) : ?>
											<?= \Aspro\Functions\CAsproMax::showItemCounter($arskuAddToBasketData, $arSKU["ID"], $arSKUIDs, $arParams, '', '', true, true); ?>
										<? endif; ?>
										<div class="button_block <?= (in_array($arSKU["ID"], $arParams["BASKET_ITEMS"]) || $arskuAddToBasketData["ACTION"] == "ORDER" || ($arskuAddToBasketData["ACTION"] == 'MORE' || !$arskuAddToBasketData["CAN_BUY"]) || !$arskuAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] ? "wide" : ""); ?>">
											<!--noindex-->
											<?= $arskuAddToBasketData["HTML"] ?>
											<!--/noindex-->
										</div>
									</div>
									<?= \Aspro\Functions\CAsproMax::showItemOCB($arskuAddToBasketData, $arSKU, $arParams); ?>

									<? //delivery calculate
									?>
									<? if (
										$arskuAddToBasketData["ACTION"] == "ADD" &&
										$arskuAddToBasketData["CAN_BUY"]
									) : ?>
										<?= \Aspro\Functions\CAsproMax::showCalculateDeliveryBlock($arSKU['ID'], $arParams, $arParams['TYPE_SKU'] !== 'TYPE_1'); ?>
									<? endif; ?>

									<?
									if (isset($arSKU['PRICE_MATRIX']) && $arSKU['PRICE_MATRIX']) // USE_PRICE_COUNT
									{ ?>
										<? if ($arSKU['ITEM_PRICE_MODE'] == 'Q' && count($arSKU['PRICE_MATRIX']['ROWS']) > 1) : ?>
											<? $arOnlyItemJSParams = array(
												"ITEM_PRICES" => $arSKU["ITEM_PRICES"],
												"ITEM_PRICE_MODE" => $arSKU["ITEM_PRICE_MODE"],
												"ITEM_QUANTITY_RANGES" => $arSKU["ITEM_QUANTITY_RANGES"],
												"MIN_QUANTITY_BUY" => $arskuAddToBasketData["MIN_QUANTITY_BUY"],
												"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
												"ID" => $this->GetEditAreaId($arSKU["ID"]),
											) ?>
											<? if (!$arParams['AJAX_JS']) : ?>
												<script type="text/javascript" data-type="ajax">
													var ob<? echo $this->GetEditAreaId($arSKU["ID"]); ?>el = new JCCatalogSectionOnlyElement(<? echo CUtil::PhpToJSObject($arOnlyItemJSParams, false, true); ?>);
												</script>
											<? endif; ?>
										<? endif; ?>
									<? } ?>
								</div>
							</div>

							<? //icons-block
							?>
							<? if ($arResult['ICONS_SIZE']) : ?>
								<div class="item-icons s_<?= $arResult['ICONS_SIZE'] ?>">
									<? \Aspro\Functions\CAsproMaxItem::showDelayCompareBtn($arParams, $arSKU, $arskuAddToBasketData, $skutotalCount, '', 'list static icons', false, false, '_small', $currentSKUID, $currentSKUIBlock); ?>
								</div>
							<? endif; ?>

							<? //stores icon
							?>
							<? if ($useStores) : ?>
								<div class="stores-icons">
									<div class="like_icons list static icons">
										<div>
											<span class="btn btn_xs btn-transparent"><?= CMax::showIconSvg("cat_icons", SITE_TEMPLATE_PATH . "/images/svg/catalog/arrow_sku.svg", "", "", true, false); ?></span>
										</div>
									</div>
								</div>
							<? endif; ?>
						</div>
						<div class="offer-stores" style="display: none;">
							<? $APPLICATION->IncludeComponent(
								"bitrix:catalog.store.amount",
								"main",
								array(
									"PER_PAGE" => "10",
									"USE_STORE_PHONE" => $arParams["USE_STORE_PHONE"],
									"SCHEDULE" => $arParams["SCHEDULE"],
									"USE_MIN_AMOUNT" => $arParams["USE_MIN_AMOUNT"],
									"MIN_AMOUNT" => $arParams["MIN_AMOUNT"],
									"ELEMENT_ID" => $arSKU["ID"],
									"STORE_PATH"  =>  $arParams["STORE_PATH"],
									"MAIN_TITLE"  =>  $arParams["MAIN_TITLE"],
									"MAX_AMOUNT" => $arParams["MAX_AMOUNT"],
									"SHOW_EMPTY_STORE" => $arParams['SHOW_EMPTY_STORE'],
									"SHOW_GENERAL_STORE_INFORMATION" => $arParams['SHOW_GENERAL_STORE_INFORMATION'],
									"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
									"USER_FIELDS" => $arParams['USER_FIELDS'],
									"FIELDS" => $arParams['FIELDS'],
									"STORES" => $arParams['STORES'],
									"CACHE_TYPE" => "A",
									"SET_ITEMS" => $arResult["SET_ITEMS"],
								),
								$component
							); ?>
						</div>
					</div>
				<? endforeach; ?>
			</div>
		</div>
		<? $this->EndViewTarget(); ?>
	<? endif; ?>
<? endif; ?>
<? //top info
?>
<div class="product-info-wrapper">
	<div class="product-info <?= (!$showCustomOffer ? "noffer" : ""); ?> product-info--type2" id="<?= $arItemIDs["strMainID"]; ?>">
		<script type="text/javascript">
			setViewedProduct(<?= $arResult['ID'] ?>, <?= CUtil::PhpToJSObject($arViewedData, false) ?>);
		</script>

		<? //meta
		?>
		<meta itemprop="name" content="<?= $name = strip_tags(!empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arResult['NAME']) ?>" />
		<link itemprop="url" href="<?= $arResult['DETAIL_PAGE_URL'] ?>" />
		<meta itemprop="category" content="<?= $arResult['CATEGORY_PATH'] ?>" />
		<meta itemprop="description" content="<?= (strlen(strip_tags($arResult['PREVIEW_TEXT'])) ? strip_tags($arResult['PREVIEW_TEXT']) : (strlen(strip_tags($arResult['DETAIL_TEXT'])) ? strip_tags($arResult['DETAIL_TEXT']) : $name)) ?>" />
		<meta itemprop="sku" content="<?= $arResult['ID']; ?>" />

		<div class="product-info-body flexbox flexbox--row">
			<? //main gallery
			?>
			<? $bVertical = ($arParams['GALLERY_THUMB_POSITION'] == 'vertical'); ?>
			<div class="product-detail-gallery swipeignore">
				<div class="product-detail-gallery-body<? if (!empty($arResult['DISPLAY_PROPERTIES']['IN_INTERIOR']['VALUE'])) : ?> padding<? endif; ?>">
					<div class="product-detail-gallery__container<?= ($bVertical ? ' product-detail-gallery__container--vertical' : ''); ?>">
						<? reset($arResult['MORE_PHOTO']);
						$countPhoto = count($arResult["MORE_PHOTO"]);
						$arFirstPhoto = ($arParams['ADD_DETAIL_TO_SLIDER'] != 'Y' ? $arResult['PREVIEW_PICTURE'] : current($arResult['MORE_PHOTO']));
						$viewImgType = $arParams["DETAIL_PICTURE_MODE"];
						$bMagnifier = ($viewImgType == "MAGNIFIER"); ?>
						<? $bIsOneImage = $bMagnifier; ?>

						<? \Aspro\Functions\CAsproMaxItem::showStickers($arParams, $arResult, true, "product-info-headnote__stickers1"); ?>

						<? //files
						?>
						<? $instr_prop = ($arParams["DETAIL_DOCS_PROP"] ? $arParams["DETAIL_DOCS_PROP"] : "INSTRUCTIONS"); ?>
						<?
						$bItemFiles = isset($arResult["PROPERTIES"][$instr_prop]) && (is_array($arResult["PROPERTIES"][$instr_prop]["VALUE"]) && count($arResult["PROPERTIES"][$instr_prop]["VALUE"]));
						$bSectionFiles = isset($arResult["SECTION_FULL"]["UF_FILES"]) && count($arResult["SECTION_FULL"]["UF_FILES"]);
						?>
						<? if ($bItemFiles || $bSectionFiles) { ?>
						<?
							$arFiles = array();
							if ($arResult["PROPERTIES"][$instr_prop]["VALUE"]) {
								$arFiles = $arResult["PROPERTIES"][$instr_prop]["VALUE"];
							} else {
								$arFiles = $arResult["SECTION_FULL"]["UF_FILES"];
							}
							if (is_array($arFiles)) {
								foreach ($arFiles as $key => $value) {
									if (!intval($value)) {
										unset($arFiles[$key]);
									}
								}
							}
						} ?>
						<? //files
						?>

						<div class="product-info-tab-links">
							<? if (count($arResult['VIDEO']) > 0) : ?>
								<div class="product-info-tab-link" data-tab="video"></div>
							<? endif; ?>
							<? if (count($arResult["MORE_PHOTO"]) > 0) : ?>
								<div class="product-info-tab-link hidden" data-tab="photo"></div>
							<? endif; ?>
							<? if (count($arFiles ?: []) > 0) : ?>
								<div class="product-info-tab-link" data-tab="files"></div>
							<? endif; ?>
							<? //start new icons
							?>
							<? if (!empty($templateData['AR'])) : ?>
								<!--								<div class="product-info-tab-link" data-tab="ar"></div>-->
							<? endif; ?>
							<? if (!empty($templateData['3D'])) : ?>
								<!--								<div class="product-info-tab-link" data-tab="3d"></div>-->
							<? endif; ?>
							<? //end new icons
							?>
						</div>

						<? if ($arResult["OFFERS"] && $arResult["FIRST_SKU_PICTURE"]) : ?>
							<link class="first_sku_picture" href="<?= $arResult["FIRST_SKU_PICTURE"]["src"]; ?>" />
						<? endif; ?>

						<link href="<?= ($arFirstPhoto["BIG"]["src"] ? $arFirstPhoto["BIG"]["src"] : $arFirstPhoto["SRC"]); ?>" itemprop="image" />
						<?/*<div class="product-detail-gallery__slider<?if(!$bMagnifier):?> owl-carousel owl-theme big owl-bg-nav short-nav<?else:?> hidden-xs<?endif;?> <?=$arParams['PICTURE_RATIO'];?>" data-plugin-options='{"items": "1", "dots": true, "nav": true, "relatedTo": ".product-detail-gallery__slider.thmb", "loop": false}'>*/ ?>
						<div class="product-detail-gallery__slider owl-carousel owl-theme big owl-bg-nav short-nav <?= $arParams['PICTURE_RATIO']; ?>" data-plugin-options='{"items": "1", "dots": true, "nav": true, "relatedTo": ".product-detail-gallery__slider.thmb", "loop": false}'>
							<? if ($showCustomOffer && !empty($arResult['OFFERS_PROP'])) { ?>
								<? $alt = $arFirstPhoto["ALT"];
								$title = $arFirstPhoto["TITLE"]; ?>
								<div id="photo-sku" class="product-detail-gallery__item product-detail-gallery__item--middle text-center">
									<? if ($arFirstPhoto["BIG"]["src"]) : ?>
										<a href="<?= ($viewImgType == "POPUP" ? $arFirstPhoto["BIG"]["src"] : "javascript:void(0)"); ?>" <?= ($bIsOneImage ? '' : 'data-fancybox="gallery"') ?> class="product-detail-gallery__link <?= ($viewImgType == "POPUP" ? "popup_link fancy" : "line_link"); ?>" title="<?= $title; ?>">
											<img id="<? echo $arItemIDs["ALL_ITEM_IDS"]['PICT']; ?>" class="lazy product-detail-gallery__picture <?= ($viewImgType == "MAGNIFIER" ? "zoom_picture" : ""); ?>" data-src="<?= ($arFirstPhoto["SMALL"]["src"] ? $arFirstPhoto["SMALL"]["src"] : $arFirstPhoto["SRC"]) ?>" src="<?= \Aspro\Functions\CAsproMax::showBlankImg(($arFirstPhoto["SMALL"]["src"] ? $arFirstPhoto["SMALL"]["src"] : $arFirstPhoto["SRC"])) ?>" <?= ($viewImgType == "MAGNIFIER" ? 'data-xoriginal="' . $arFirstPhoto["BIG"]["src"] . '" data-xpreview="' . $arFirstPhoto["THUMB"]["src"] . '"' : ""); ?> alt="<?= $alt; ?>" title="<?= $title; ?>" />
										</a>
									<? else : ?>
										<img id="<? echo $arItemIDs["ALL_ITEM_IDS"]['PICT']; ?>" class="lazy product-detail-gallery__picture one" data-src="<?= \Aspro\Functions\CAsproMax::showBlankImg($arFirstPhoto["SRC"]) ?>" src="<?= $arFirstPhoto["SRC"] ?>" alt="<?= $alt; ?>" title="<?= $title; ?>" />
									<? endif; ?>
								</div>
								<? } else {
								if ($arResult["MORE_PHOTO"]) { ?>
									<? foreach ($arResult["MORE_PHOTO"] as $i => $arImage) {
										if ($i && $bMagnifier) : ?>
											<? continue; ?>
										<? endif; ?>
										<? $isEmpty = ($arImage["SMALL"]["src"] ? false : true); ?>
										<?
										$alt = $arImage["ALT"];
										$title = $arImage["TITLE"];
										?>
										<div id="photo-<?= $i ?>" class="product-detail-gallery__item product-detail-gallery__item--middle text-center">
											<? if (!$isEmpty) { ?>
												<a href="<?= ($viewImgType == "POPUP" ? $arImage["BIG"]["src"] : "javascript:void(0)"); ?>" <?= ($bIsOneImage ? '' : 'data-fancybox="gallery"') ?> class="product-detail-gallery__link <?= ($viewImgType == "POPUP" ? "popup_link fancy" : "line_link"); ?> " title="<?= $title; ?>">
													<img class="lazy product-detail-gallery__picture <?= ($viewImgType == "MAGNIFIER" ? "zoom_picture" : ""); ?>" data-src="<?= $arImage["SMALL"]["src"] ?>" src="<?= \Aspro\Functions\CAsproMax::showBlankImg($arImage["SMALL"]["src"]) ?>" <?= ($viewImgType == "MAGNIFIER" ? 'data-xoriginal="' . $arImage["BIG"]["src"] . '" data-xpreview="' . $arImage["THUMB"]["src"] . '"' : ""); ?> alt="<?= $alt; ?>" title="<?= $title; ?>" />
												</a>
											<? } else { ?>
												<img class="lazy product-detail-gallery__picture" src="<?= \Aspro\Functions\CAsproMax::showBlankImg($arImage["SRC"]) ?>" data-src="<?= $arImage["SRC"] ?>" alt="<?= $alt; ?>" title="<?= $title; ?>" />
											<? } ?>
										</div>
									<? } ?>
							<? }
							} ?>
						</div>
						<?/*if($bMagnifier):?>
							<div class="product-detail-gallery__slider owl-carousel owl-theme big visible-xs <?=$arParams['PICTURE_RATIO'];?>" data-plugin-options='{"items": "1", "dots": true, "nav": true, "loop": false}'>
								<?if($showCustomOffer && !empty($arResult['OFFERS_PROP'])){?>
									<?$alt=$arFirstPhoto["ALT"];
									$title=$arFirstPhoto["TITLE"];?>
									<div id="photo-sku" class="product-detail-gallery__item product-detail-gallery__item--big text-center">
										<?if($arFirstPhoto["BIG"]["src"]):?>
											<a href="<?=$arFirstPhoto["BIG"]["src"];?>" data-fancybox="gallery" class="product-detail-gallery__link popup_link fancy" title="<?=$title;?>">
												<img id="<? echo $arItemIDs["ALL_ITEM_IDS"]['PICT']; ?>" class="lazy product-detail-gallery__picture" data-src="<?=$arFirstPhoto["SMALL"]["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arFirstPhoto["SMALL"]["src"])?>" alt="<?=$alt;?>" title="<?=$title;?>"<?//=(!$i ? ' itemprop="image"' : '')?>/>
											</a>
										<?else:?>
											<img id="<? echo $arItemIDs["ALL_ITEM_IDS"]['PICT']; ?>" class="lazy product-detail-gallery__picture" data-src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arFirstPhoto["SRC"])?>" src="<?=$arFirstPhoto["SRC"]?>" alt="<?=$alt;?>" title="<?=$title;?>" />
										<?endif;?>
									</div>
								<?}else{
									if($arResult["MORE_PHOTO"]){?>
										<?foreach($arResult["MORE_PHOTO"] as $i => $arImage){?>
											<?$isEmpty=($arImage["SMALL"]["src"] ? false : true );?>
											<?
											$alt=$arImage["ALT"];
											$title=$arImage["TITLE"];
											?>
											<div id="photo-<?=$i?>" class="product-detail-gallery__item product-detail-gallery__item--big text-center">
												<?if(!$isEmpty){?>
													<a href="<?=$arImage["BIG"]["src"];?>" data-fancybox="gallery" class="product-detail-gallery__link popup_link fancy" title="<?=$title;?>">
														<img class="lazy product-detail-gallery__picture" data-src="<?=$arImage["SMALL"]["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImage["SMALL"]["src"])?>" alt="<?=$alt;?>" title="<?=$title;?>"<?//=(!$i ? ' itemprop="image"' : '')?>/>
													</a>
												<?}else{?>
													<img class="lazy product-detail-gallery__picture" data-src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImage["SRC"])?>" src="<?=$arImage["SRC"]?>" alt="<?=$alt;?>" title="<?=$title;?>" />
												<?}?>
											</div>
										<?}?>
									<?}
								}?>
							</div>
						<?endif;*/ ?>
						<div class="product-detail-gallery__thmb-container text-center">
							<div class="product-detail-gallery__thmb-inner<?= ($bVertical ? ' vertical' : ''); ?>">
								<? if (!$showCustomOffer || empty($arResult['OFFERS_PROP'])) : ?>
									<? if ($countPhoto  > 1) : ?>
										<div class="product-detail-gallery__slider owl-carousel owl-theme thmb<?= ($bVertical ? ' product-detail-gallery__slider--vertical' : ''); ?><?= ($countPhoto > 3 ? ' m-photo' : ''); ?>" data-size="<?= $countPhoto; ?>" data-plugin-options='{"items": "4", "nav": true, "loop": false, "clickTo": ".product-detail-gallery__slider.big", "dots": false, "autoWidth": true, "margin": 10<? //if($bVertical):?>, "mouseDrag": false, "pullDrag": false<? //endif;?>
											<? if ($bMagnifier) : ?>, "magnifier": true<? endif; ?>}' style="max-width:<?= ceil((($countPhoto <= 4 ? $countPhoto : 4) * 70) - 10) ?>px;">
											<? if ($arResult["MORE_PHOTO"]) : ?>
												<? foreach ($arResult["MORE_PHOTO"] as $i => $arImage) : ?>
													<div id="photo-<?= $i ?>" class="product-detail-gallery__item text-center  product-detail-gallery__item--thmb" data-big="<?= $arImage["BIG"]["src"]; ?>">
														<? if (!$isEmpty) { ?>
															<img class="lazy product-detail-gallery__picture" data-src="<?= $arImage["SMALL"]["src"] ?>" src="<?= \Aspro\Functions\CAsproMax::showBlankImg($arImage["SMALL"]["src"]) ?>" alt="<?= $alt; ?>" title="<?= $title; ?>" />
														<? } else { ?>
															<img class="lazy product-detail-gallery__picture" data-src="<?= \Aspro\Functions\CAsproMax::showBlankImg($arImage["SRC"]) ?>" src="<?= $arImage["SRC"] ?>" alt="<?= $alt; ?>" title="<?= $title; ?>" />
														<? } ?>
													</div>
												<? endforeach; ?>
											<? endif; ?>
										</div>
									<? endif; ?>
									<? if ($arResult['PROPERTIES']['POPUP_VIDEO']['VALUE']) : ?>
										<div class="video-block popup_video <?= ($countPhoto > 3 ? 'fromtop' : ''); ?> sm"><a class="various video_link image dark_link" href="<?= $arResult['PROPERTIES']['POPUP_VIDEO']['VALUE']; ?>" title="<?= Loc::getMessage("VIDEO") ?>"><span class="play text-upper font_xs"><?= Loc::getMessage("VIDEO") ?></span></a></div>
									<? endif; ?>
								<? else : ?>
									<div class="product-detail-gallery__slider owl-carousel owl-theme thmb<?= ($bVertical ? ' product-detail-gallery__slider--vertical' : ''); ?>" data-size="<?= $countPhoto; ?>" data-plugin-options='{"items": "4", "nav": true, "loop": false, "clickTo": ".product-detail-gallery__slider.big", "dots": false, "autoWidth": true, "margin": 10<? //if($bVertical):?>, "mouseDrag": false, "pullDrag": false<? //endif;?>
										<?/*if($bMagnifier):?>, "magnifier": true<?endif;*/ ?>}' style="max-width:<?= ceil((($countPhoto <= 4 ? $countPhoto : 4) * 70) - 10) ?>px;">
									</div>
								<? endif; ?>
							</div>
						</div>
					</div>

					<? if (!empty($arResult['DISPLAY_PROPERTIES']['IN_INTERIOR']['VALUE'])) : ?>
						<div class="model-3d">
							<img src="/images/3d-model.png">
							<a target="_blank" href="<?= $arResult['DISPLAY_PROPERTIES']['IN_INTERIOR']['VALUE']; ?>">
								<span>Посмотреть в интерьере</span>
							</a>
						</div>
					<? endif; ?>

					<? //dop text
					?>
					<div class="text-additional hidden">
						<? $path = SITE_DIR . "include/element_detail_text.php" ?>
						<div class="price_txt muted777 font_sxs<?= ((CMax::checkContentFile($path) ? ' filed' : '')); ?>">
							<? $APPLICATION->IncludeFile($path, array(), array("MODE" => "html",  "NAME" => GetMessage('CT_BCE_CATALOG_DOP_DESCR'))); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="product-main">
				<div class="product-main-body">
					<? //CMax::ShowPageType('page_title');
					?>
					<?
                    $elementName = ((isset($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
                        ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
                        : $arResult['NAME']);
                    ?>
					<h1 id="pagetitle"><?= $elementName . ' ' . IN_CITY ?></h1><? //$APPLICATION->ShowViewContent('more_text_title');?>

					<? if ($arResult["RESTRICTION"]) : ?>
						<div class="item-restriction _detail">Покупка возможна только при условии приобретения шкафа.</div>
					<? endif; ?>

					<? if ($arResult["MORE_PHOTO"]) : ?>
						<div class="summary-wrapper">
							<div class="product-scrolled-summary">
								<div class="img" id="summary-img">
									<img src="<?= $arFirstPhoto['SAFE_SRC']; ?>" alt="">
								</div>
								<div class="configuration-block flexbox flexbox--row hidden">
									<?/*
									<div class="configuration-change">
										<p><span class="dotted"><?=GetMessage('CONFIGURATION_CHANGE_TITLE');?></span></p>
									</div>
									*/ ?>
									<div class="configuration-info">
										<div class="configuration-info-body">
										</div>
									</div>
								</div>
							</div>
						</div>
					<? endif; ?>


					<div class="product-info-headnote clearfix">
						<div class="flexbox flexbox--row align-items-center justify-content-between flex-wrap">
							<? //delay|compare
							?>
							<div class="col-auto">
								<div class="product-info-headnote__toolbar">
									<? \Aspro\Functions\CAsproMaxItem::showDelayCompareBtn($arParams, $arResult, $arAddToBasketData, $totalCount, $bUseSkuProps, 'list static sm', false, false, '_small', $currentSKUID, $currentSKUIBlock); ?>
								</div>
							</div>
							<? //article,rating,brand
							?>
							<div class="col-auto">
								<div class="product-info-headnote__inner">
									<? $isArticle = (strlen($arResult["CML2_ARTICLE"]["VALUE"]) || ($arResult['SHOW_OFFERS_PROPS'] && $showCustomOffer)); ?>

									<? if ($isArticle) : ?>
										<div class="product-info-headnote__article">
											<div class="article muted font_xs" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue" <? if ($arResult['SHOW_OFFERS_PROPS']) { ?>id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_ARTICLE_DIV'] ?>" style="display: none;" <? } ?>>
												<span class="article__title" itemprop="name"><?= $arResult["CML2_ARTICLE"]["NAME"]; ?>:</span>
												<span class="article__value" itemprop="value"><?= $arResult["CML2_ARTICLE"]["VALUE"] ?></span>
											</div>
										</div>
									<? endif; ?>
									<? if ($arResult["BRAND_ITEM"]) { ?>
										<div class="product-info-headnote__brand">
											<div class="brand">
												<meta itemprop="brand" content="<?= $arResult["BRAND_ITEM"]["NAME"] ?>" />
												<? if (!$arResult["BRAND_ITEM"]["IMAGE"]) : ?>
													<a href="<?= $arResult["BRAND_ITEM"]["DETAIL_PAGE_URL"] ?>" class="brand__link dark_link"><?= $arResult["BRAND_ITEM"]["NAME"] ?></a>
												<? else : ?>
													<a class="brand__picture" href="<?= $arResult["BRAND_ITEM"]["DETAIL_PAGE_URL"] ?>">
														<img src="<?= $arResult["BRAND_ITEM"]["IMAGE"]["src"] ?>" alt="<?= $arResult["BRAND_ITEM"]["IMAGE"]["ALT"] ?>" title="<?= $arResult["BRAND_ITEM"]["IMAGE"]["TITLE"] ?>" />
													</a>
												<? endif; ?>
											</div>
										</div>
									<? } ?>
								</div>
							</div>

							<?= $arQuantityData["HTML"] ?>

							<? if ($arParams["SHOW_RATING"] == "Y") : ?>
								<div class="services-block__rating">
									<? include 'include/rating.php'; ?>
								</div>
							<? endif; ?>
						</div>
					</div>

					<? //buttons,props,sales
					?>

					<? //discount,buy|order|subscribe
					?>
					<div class="product-action">
						<div class="info_item">
							<div class="middle-info-wrapper main_item_wrapper">
								<div class="shadowed-block">
									<? $frame = $this->createFrame()->begin(''); ?>

									<? //composite fix for offers
									?>
									<? if ($arResult["OFFERS"] && $showCustomOffer) : ?>
										<? $arItemJSParams = CMax::GetSKUJSParams($arResult, $arParams, $arResult, "Y"); ?>
										<? if (!$arParams['AJAX_JS']) : ?>
											<script async type="text/javascript" src="//event.getblue.io/js/blue-tag.min.js" async="true" data-type="ajax"></script>
											<script type="text/javascript" data-type="ajax" async>
												var <? echo $arItemIDs["strObName"]; ?> = new JCCatalogElement(<? echo CUtil::PhpToJSObject($arItemJSParams, false, true); ?>);
											</script>
										<? endif; ?>
									<? endif; ?>
									<? //dicount timer
									?>
									<? if ($arParams["SHOW_DISCOUNT_TIME"] == "Y") { ?>
										<? $arUserGroups = $USER->GetUserGroupArray(); ?>
										<? $arDiscount = [] ?>
										<? if ($arParams['SHOW_DISCOUNT_TIME_EACH_SKU'] != 'Y' || ($arParams['SHOW_DISCOUNT_TIME_EACH_SKU'] == 'Y' && (!$arResult['OFFERS'] || ($arResult['OFFERS'] && $arParams['TYPE_SKU'] != 'TYPE_1')))) : ?>
											<? \Aspro\Functions\CAsproMax::showDiscountCounter($totalCount, $arDiscount, $arQuantityData, $arResult, $strMeasure, 'compact red', $item_id); ?>
										<? else : ?>
											<? if ($arResult['JS_OFFERS']) {
												foreach ($arResult['JS_OFFERS'] as $keyOffer => $arTmpOffer2) {
													$active_to = '';
													$arDiscounts = CCatalogDiscount::GetDiscountByProduct($arTmpOffer2['ID'], $arUserGroups, "N", array(), SITE_ID);
													if ($arDiscounts) {
														foreach ($arDiscounts as $arDiscountOffer) {
															if ($arDiscountOffer['ACTIVE_TO']) {
																$active_to = $arDiscountOffer['ACTIVE_TO'];
																break;
															}
														}
													}
													$arResult['JS_OFFERS'][$keyOffer]['DISCOUNT_ACTIVE'] = $active_to;
												}
											} ?>
											<? \Aspro\Functions\CAsproMax::showDiscountCounter($totalCount, $arDiscount, $arQuantityData, $arResult, $strMeasure, 'compact red', $item_id); ?>
										<? endif; ?>
									<? } ?>
									<div class="prices_block">
										
										<? //prices
										?>
										<? $arParams["SHOW_DISCOUNT_PERCENT"] = "N"; ?>
										<div class="cost prices detail flexbox flexbox--row align-items-start">
											<div class="prices-body">
												<? if ($arResult["OFFERS"]) : ?>
													<?= \Aspro\Functions\CAsproMaxItem::showItemPricesDefault($arParams); ?>
													<div class="js_price_wrapper">
														<? if ($arCurrentSKU) : ?>
															<? $item_id = $arCurrentSKU["ID"];
															$arCurrentSKU['PRICE_MATRIX'] = $arCurrentSKU['PRICE_MATRIX_RAW'];
															$arCurrentSKU['CATALOG_MEASURE_NAME'] = $arCurrentSKU['MEASURE'];
															if (isset($arCurrentSKU['PRICE_MATRIX']) && $arCurrentSKU['PRICE_MATRIX']) : // USE_PRICE_COUNT
															?>
																<? if ($arCurrentSKU['ITEM_PRICE_MODE'] == 'Q' && count($arCurrentSKU['PRICE_MATRIX']['ROWS']) > 1) : ?>
																	<?= CMax::showPriceRangeTop($arCurrentSKU, $arParams, Loc::getMessage("CATALOG_ECONOMY")); ?>
																<? endif; ?>
																<?= CMax::showPriceMatrix($arCurrentSKU, $arParams, $strMeasure, $arAddToBasketData); ?>
															<? else : ?>
																<? \Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arCurrentSKU["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y")); ?>
															<? endif; ?>
														<? else : ?>
															<? \Aspro\Functions\CAsproMaxSku::showItemPrices($arParams, $arResult, $item_id, $min_price_id, $arItemIDs, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y")); ?>
														<? endif; ?>
													</div>
												<? else : ?>
													<? if (isset($arResult['PRICE_MATRIX']) && $arResult['PRICE_MATRIX']) : // USE_PRICE_COUNT
													?>
														<? if (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arResult['ITEM_PRICE_MODE'] == 'Q' || (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') != 'Y' && $arResult['ITEM_PRICE_MODE'] != 'Q' && count($arResult['PRICE_MATRIX']['COLS']) <= 1)) : ?>
															<?= CMax::showPriceRangeTop($arResult, $arParams, Loc::getMessage("CATALOG_ECONOMY")); ?>
														<? endif; ?>
														<? if (count($arResult['PRICE_MATRIX']['ROWS']) > 1 || count($arResult['PRICE_MATRIX']['COLS']) > 1) : ?>
															<?= CMax::showPriceMatrix($arResult, $arParams, $strMeasure, $arAddToBasketData); ?>
														<? endif; ?>
														<div class="" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
															<meta itemprop="price" content="<?= ($arResult['MIN_PRICE']['DISCOUNT_VALUE'] ? $arResult['MIN_PRICE']['DISCOUNT_VALUE'] : $arResult['MIN_PRICE']['VALUE']) ?>" />
															<meta itemprop="priceCurrency" content="<?= $arResult['MIN_PRICE']['CURRENCY'] ?>" />
															<link itemprop="availability" href="http://schema.org/<?= ($arResult['PRICE_MATRIX']['AVAILABLE'] == 'Y' ? 'InStock' : 'OutOfStock') ?>" />
															<?
															if ($arDiscount["ACTIVE_TO"]) { ?>
																<meta itemprop="priceValidUntil" content="<?= date("Y-m-d", MakeTimeStamp($arDiscount["ACTIVE_TO"])) ?>" />
															<? } ?>
															<link itemprop="url" href="<?= $arResult["DETAIL_PAGE_URL"] ?>" />
														</div>
													<? elseif ($arResult["PRICES"]) : ?>
														<? \Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arResult["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y")); ?>
													<? endif; ?>
												<? endif; ?>
												<?php
												$widgetPrice = array_values($arResult["PRICES"]);
												?>
												<div class="split-div"></div>
												<style>
													.ya-pay-widget {
														width: 100% !important;
														margin: 4px 0 -15px 0 !important;
														min-height: 100px !important;
													}
												</style>

											</div>

											<? $frame->end(); ?>

											<? $frame = $this->createFrame()->begin(''); ?>

											<? if ($arRegion["PROPERTY_SHOW_PRODUCT_BUILDING_VALUE"] != 'Y') : ?>
												<div class="services-block flexbox flexbox--row">
													<? //services
													?>
													<div class="js-services"></div>

													<? if ($arParams["SHOW_CHEAPER_FORM"] == "Y") : ?>
														<div class="cheaper_form muted777 font_sxs">
															<?= CMax::showIconSvg("cheaper", SITE_TEMPLATE_PATH . '/images/svg/catalog/cheaper.svg', '', '', true, false); ?>
															<span class="animate-load dotted" data-event="jqm" data-param-form_id="CHEAPER" data-name="cheaper" data-autoload-product_name="<?= CMax::formatJsName($arResult["NAME"]); ?>" data-autoload-product_id="<?= $arResult["ID"]; ?>"><?= ($arParams["CHEAPER_FORM_NAME"] ? $arParams["CHEAPER_FORM_NAME"] : Loc::getMessage("CHEAPER")); ?></span>
														</div>
													<? endif; ?>
												</div>
											<? endif; ?>
										</div>
										<? //for product wo POPUP_PRICE in fixed header
										?>
										<? if (!$arParams['SHOW_POPUP_PRICE'] && !$arResult["OFFERS"]) : ?>
											<script async>
												<? if (isset($arResult['PRICE_MATRIX']) && $arResult['PRICE_MATRIX']) : // USE_PRICE_COUNT
												?>
													<? $priceHtml = CMax::showPriceMatrix($arResult, $arParams, $strMeasure, $arAddToBasketData); ?>
													<? $countPricesMatrix = count($arResult['PRICE_MATRIX']['MATRIX']) ?>
													<? $countPricesRows = count($arResult['PRICE_MATRIX']['ROWS']) ?>
													<? $countPrices = ($countPricesMatrix > $countPricesRows ? $countPricesMatrix : $countPricesRows) ?>
													BX.message({
														ASPRO_ITEM_PRICE_MATRIX: <?= CUtil::PhpToJSObject($priceHtml, false, true); ?>
													})
												<? elseif ($arResult["PRICES"]) : ?>
													<? $priceHtml = \Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arResult["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"), false, true); ?>
													<? $countPrices = count($arResult['PRICES']) ?>
													BX.message({
														ASPRO_ITEM_PRICE: <?= CUtil::PhpToJSObject($priceHtml, false, true); ?>
													})
												<? endif; ?>
												BX.message({
													ASPRO_ITEM_POPUP_PRICE: 'Y',
													ASPRO_ITEM_PRICES: <?= $countPrices; ?>
												})
											</script>
										<? endif; ?>
										<? //stock
										?>
										<?/*<div class="quantity_block_wrapper">
													<?//=$arQuantityData["HTML"];?>
												</div>*/ ?>
									</div>

									<?
									global $arRegion;
									?>

									<div class="configuration-block flexbox flexbox--row hidden hide-on-scroll">
										<?/*
												<div class="configuration-change">
													<p><span class="dotted"><?=GetMessage('CONFIGURATION_CHANGE_TITLE');?></span></p>
												</div>
												*/ ?>

										<div class="configuration-info">
											<p class="configuration-info-title">
												<span><?= GetMessage('CONFIGURATION_INFO_TITLE'); ?>
													<!-- <i class="fa fa-angle-down" aria-hidden="true"></i> -->
												</span>
											</p>
											<div class="configuration-info-body">

											</div>
										</div>
									</div>

									<div class="config-btn">
									</div>


									<? //buttons
									?>
									<div class="buy_block catalog_block">
										<? if (!$arResult["OFFERS"]) : ?>
											<script async>
												$(document).ready(function() {
													$('.catalog_detail input[data-sid="PRODUCT_NAME"]').attr('value', $('h1').text());
												});
											</script>
											<div class="counter_wrapp big clearfix">
												<? //if(($arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] && $arAddToBasketData["ACTION"] == "ADD") && $arAddToBasketData["CAN_BUY"]):
												?>
												<?= \Aspro\Functions\CAsproMax::showItemCounter($arAddToBasketData, $arResult["ID"], $arItemIDs, $arParams, 'md', '', true, true); ?>
												<? //endif;
												?>

												<div id="<? echo $arItemIDs["ALL_ITEM_IDS"]['BASKET_ACTIONS']; ?>" class="button_block <?= (($arAddToBasketData["ACTION"] == "ORDER" /*&& !$arResult["CAN_BUY"]*/) || !$arAddToBasketData["CAN_BUY"] || !$arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] || ($arAddToBasketData["ACTION"] == "SUBSCRIBE" && $arResult["CATALOG_SUBSCRIBE"] == "Y")  ? "wide" : ""); ?>">
													<!--noindex-->
													<?= $arAddToBasketData["HTML"] ?>
													<!--/noindex-->
												</div>
											</div>
											<? if (isset($arResult['PRICE_MATRIX']) && $arResult['PRICE_MATRIX']) // USE_PRICE_COUNT
											{ ?>
												<? if ($arResult['ITEM_PRICE_MODE'] == 'Q' && count($arResult['PRICE_MATRIX']['ROWS']) > 1) : ?>
													<? $arOnlyItemJSParams = array(
														"ITEM_PRICES" => $arResult["ITEM_PRICES"],
														"ITEM_PRICE_MODE" => $arResult["ITEM_PRICE_MODE"],
														"ITEM_QUANTITY_RANGES" => $arResult["ITEM_QUANTITY_RANGES"],
														"MIN_QUANTITY_BUY" => $arAddToBasketData["MIN_QUANTITY_BUY"],
														"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
														"ID" => $arItemIDs["strMainID"],
													) ?>
													<? if (!$arParams['AJAX_JS']) : ?><script type="text/javascript" data-type="ajax">
															var <? echo $arItemIDs["strObName"]; ?>el = new JCCatalogOnlyElement(<? echo CUtil::PhpToJSObject($arOnlyItemJSParams, false, true); ?>);
														</script><? endif; ?>
												<? endif; ?>
											<? } ?>
											<? if ($arAddToBasketData["ACTION"] !== "NOTHING") : ?>
												<?= \Aspro\Functions\CAsproMax::showItemOCB($arAddToBasketData, $arResult, $arParams, false, ' round-ignore '); ?>
											<? endif; ?>
										<? elseif ($arResult["OFFERS"] && $arParams['TYPE_SKU'] == 'TYPE_1') : ?>
											<div class="offer_buy_block buys_wrapp flexbox flexbox--row" style="display:none;">
												<div class="counter_wrapp clearfix"></div>
											</div>
										<? elseif ($arResult["OFFERS"] && $arParams['TYPE_SKU'] != 'TYPE_1') : ?>
											<span class="btn btn-default btn-lg slide_offer type_block"><i></i><span><?= \Bitrix\Main\Config\Option::get("aspro.max", "EXPRESSION_READ_MORE_OFFERS_DEFAULT", GetMessage("MORE_TEXT_BOTTOM")); ?></span></span>
										<? endif; ?>
									</div>

									<div class="installment_block hidden">
										<? $APPLICATION->IncludeComponent(
											"bitrix:main.include",
											"",
											array(
												"AREA_FILE_SHOW" => "file",
												"AREA_FILE_SUFFIX" => "inc",
												"EDIT_TEMPLATE" => "",
												"PATH" => $templateFolder . "/include/installment-info.php"
											)
										); ?>
									</div>
									<? $frame->end(); ?>

									<?/*services
										<div class="js-services"></div>*/ ?>
								</div>

							</div>

							<? //delivery calculate
							?>

							<? if ($arParams['SHOW_SEND_GIFT'] != 'N') : ?>
								<? $sCurrentPage = (CMain::IsHTTPS()) ? "https://" : "http://";
								$sCurrentPage .= $_SERVER["HTTP_HOST"];
								$sCurrentPage .= $APPLICATION->GetCurPage(); ?>
								<div class="text-form muted ncolor">
									<div class="price_txt muted777 font_sxs ">
										<?= CMax::showIconSvg("info_big pull-left", SITE_TEMPLATE_PATH . '/images/svg/catalog/iwantgift.svg', '', '', true, false); ?>
										<div class="text-form-info">
											<span><span class="animate-load dotted" data-event="jqm" data-param-form_id="SEND_GIFT" data-name="send_gift" data-autoload-product_name="<?= CMax::formatJsName($arResult["NAME"]); ?>" data-autoload-product_link="<?= $sCurrentPage; ?>" data-autoload-product_id="<?= $arResult["ID"]; ?>"><?= ($arParams["SEND_GIFT_FORM_NAME"] ? $arParams["SEND_GIFT_FORM_NAME"] : GetMessage("SEND_GIFT_FORM")); ?></span></span>
										</div>
									</div>
								</div>
							<? endif; ?>

							<? //help text
							?>
							<? if ($arResult['HELP_TEXT']) : ?>
								<div class="text-form">
									<div class="price_txt muted777 font_sxs muted ncolor">
										<?= CMax::showIconSvg("info_big pull-left", SITE_TEMPLATE_PATH . '/images/svg/catalog/info_big.svg', '', '', true, false); ?>
										<div class="text-form-info">
											<? if (!$arResult['HELP_TEXT_FILE']) : ?>
												<?= $arResult['HELP_TEXT']; ?>
											<? else : ?>
												<? $APPLICATION->IncludeComponent(
													"bitrix:main.include",
													"",
													array(
														"AREA_FILE_SHOW" => "page",
														"AREA_FILE_SUFFIX" => "help_text",
														"EDIT_TEMPLATE" => ""
													)
												); ?>
											<? endif; ?>
										</div>
									</div>
								</div>
							<? endif; ?>

							<? //brand
							?>
							<? $this->SetViewTarget('PRODUCT_SIDE_INFO', 900); ?>
							<? if ($arResult['BRAND_ITEM']) : ?>
								<div class="brand-detail">
									<div class="brand-detail-info bordered rounded3">
										<? if ($arResult['BRAND_ITEM']["IMAGE"]) : ?>
											<div class="brand-detail-info__image"><a href="<?= $arResult['BRAND_ITEM']["DETAIL_PAGE_URL"]; ?>"><img src="<?= $arResult['BRAND_ITEM']["IMAGE"]["src"]; ?>" alt="<?= $arResult['BRAND_ITEM']["NAME"]; ?>" title="<?= $arResult['BRAND_ITEM']["NAME"]; ?>" itemprop="image"></a></div>
										<? endif; ?>
										<div class="brand-detail-info__preview">
											<? if ($arResult['BRAND_ITEM']["PREVIEW_TEXT"]) : ?>
												<div class="text muted777 font_xs"><?= $arResult['BRAND_ITEM']["PREVIEW_TEXT"]; ?></div>
											<? endif; ?>
											<? if ($arResult['SECTION']) : ?>
												<div class="link font_xs"><a href="<?= $arResult['BRAND_ITEM']['CATALOG_PAGE_URL'] ?>" target="_blank"><?= GetMessage("ITEMS_BY_SECTION") ?></a></div>
											<? endif; ?>
											<div class="link font_xs"><a href="<?= $arResult['BRAND_ITEM']["DETAIL_PAGE_URL"]; ?>" target="_blank"><?= GetMessage("ITEMS_BY_BRAND", array("#BRAND#" => $arResult['BRAND_ITEM']["NAME"])) ?></a></div>
										</div>
									</div>
								</div>
							<? endif; ?>
							<? $this->EndViewTarget(); ?>
						</div>
					</div>

					<div class="product-delivery-info"></div>

				</div>
			</div>
		</div>

		<? $bPriceCount = ($arParams['USE_PRICE_COUNT'] == 'Y'); ?>
		<? if ($arResult['OFFERS']) : ?>
			<span itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer" style="display:none;">
				<meta itemprop="offerCount" content="<?= count($arResult['OFFERS']) ?>" />
				<meta itemprop="lowPrice" content="<?= ($arResult['MIN_PRICE']['DISCOUNT_VALUE'] ? $arResult['MIN_PRICE']['DISCOUNT_VALUE'] : $arResult['MIN_PRICE']['VALUE']) ?>" />
				<meta itemprop="highPrice" content="<?= ($arResult['MAX_PRICE']['DISCOUNT_VALUE'] ? $arResult['MAX_PRICE']['DISCOUNT_VALUE'] : $arResult['MAX_PRICE']['VALUE']) ?>" />
				<meta itemprop="priceCurrency" content="<?= $arResult['MIN_PRICE']['CURRENCY'] ?>" />
				<? foreach ($arResult['OFFERS'] as $arOffer) : ?>
					<? $currentOffersList = array(); ?>
					<? foreach ($arOffer['TREE'] as $propName => $skuId) : ?>
						<? $propId = (int)substr($propName, 5); ?>
						<? foreach ($arResult['SKU_PROPS'] as $prop) : ?>
							<? if ($prop['ID'] == $propId) : ?>
								<? foreach ($prop['VALUES'] as $propId => $propValue) : ?>
									<? if ($propId == $skuId) : ?>
										<? $currentOffersList[] = $propValue['NAME']; ?>
										<? break; ?>
									<? endif; ?>
								<? endforeach; ?>
							<? endif; ?>
						<? endforeach; ?>
					<? endforeach; ?>
					<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
						<meta itemprop="sku" content="<?= implode('/', $currentOffersList) ?>" />
						<a href="<?= $arOffer['DETAIL_PAGE_URL'] ?>" itemprop="url"></a>
						<meta itemprop="price" content="<?= ($arOffer['MIN_PRICE']['DISCOUNT_VALUE']) ? $arOffer['MIN_PRICE']['DISCOUNT_VALUE'] : $arOffer['MIN_PRICE']['VALUE'] ?>" />
						<meta itemprop="priceCurrency" content="<?= $arOffer['MIN_PRICE']['CURRENCY'] ?>" />
						<link itemprop="availability" href="http://schema.org/<?= ($arOffer['CAN_BUY'] ? 'InStock' : 'OutOfStock') ?>" />
						<?
						if ($arDiscount["ACTIVE_TO"]) { ?>
							<meta itemprop="priceValidUntil" content="<?= date("Y-m-d", MakeTimeStamp($arDiscount["ACTIVE_TO"])) ?>" />
						<? } ?>
						<link itemprop="url" href="<?= $arResult["DETAIL_PAGE_URL"] ?>" />
					</span>
				<? endforeach; ?>
			</span>
			<? unset($arOffer, $currentOffersList); ?>
		<? else : ?>
			<? if (!$bPriceCount) : ?>
				<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
					<meta itemprop="price" content="<?= ($arResult['MIN_PRICE']['DISCOUNT_VALUE'] ? $arResult['MIN_PRICE']['DISCOUNT_VALUE'] : $arResult['MIN_PRICE']['VALUE']) ?>" />
					<meta itemprop="priceCurrency" content="<?= $arResult['MIN_PRICE']['CURRENCY'] ?>" />
					<link itemprop="availability" href="http://schema.org/<?= ($arResult['MIN_PRICE']['CAN_BUY'] ? 'InStock' : 'OutOfStock') ?>" />
					<?
					if ($arDiscount["ACTIVE_TO"]) { ?>
						<meta itemprop="priceValidUntil" content="<?= date("Y-m-d", MakeTimeStamp($arDiscount["ACTIVE_TO"])) ?>" />
					<? } ?>
					<link itemprop="url" href="<?= $arResult["DETAIL_PAGE_URL"] ?>" />
				</span>
			<? endif; ?>
		<? endif; ?>

		<script type="text/javascript">
			BX.message({
				QUANTITY_AVAILIABLE: '<? echo COption::GetOptionString("aspro.max", "EXPRESSION_FOR_EXISTS", GetMessage("EXPRESSION_FOR_EXISTS_DEFAULT"), SITE_ID); ?>',
				QUANTITY_NOT_AVAILIABLE: '<? echo COption::GetOptionString("aspro.max", "EXPRESSION_FOR_NOTEXISTS", GetMessage("EXPRESSION_FOR_NOTEXISTS"), SITE_ID); ?>',
				ADD_ERROR_BASKET: '<? echo GetMessage("ADD_ERROR_BASKET"); ?>',
				ADD_ERROR_COMPARE: '<? echo GetMessage("ADD_ERROR_COMPARE"); ?>',
				ONE_CLICK_BUY: '<? echo GetMessage("ONE_CLICK_BUY"); ?>',
				MORE_TEXT_BOTTOM: '<?= \Bitrix\Main\Config\Option::get("aspro.max", "EXPRESSION_READ_MORE_OFFERS_DEFAULT", GetMessage("MORE_TEXT_BOTTOM")); ?>',
				TYPE_SKU: '<? echo $arParams['TYPE_SKU']; ?>',
				HAS_SKU_PROPS: '<? echo ($arResult['OFFERS_PROP'] ? 'Y' : 'N'); ?>',
				SITE_ID: '<? echo SITE_ID; ?>'
			})
		</script>
	</div>
</div>




<? $bShowPropsBlock = ($arResult["OFFERS"] && $showCustomOffer) || ($arResult["SIZE_PATH"]) || (($arResult['DISPLAY_PROPERTIES'] || $arResult['DISPLAY_PROPERTIES_OFFERS']) && $arParams['VISIBLE_PROP_COUNT'] > 0); ?>
<? if ($bShowPropsBlock) : ?>
	<div class="product-chars-wrapper">
		<div class="product-chars">
			<div class="product-chars-inner">
				<!-- <p class="product-chars-title"><?/*=GetMessage("SLIDING_WARDROBE_PARAMETERS_TITLE");*/ ?></p> -->

				<div class="product-chars-body">
					<? //sales
					?>
					<div class="js-sales"></div>
					<? //offers tree props
					?>
					<? if ($arResult["OFFERS"] && $showCustomOffer) : ?>
						<? $frame = $this->createFrame()->begin(''); ?>
						<div class="buy_block offer-props-wrapper">
							<div class="sku_props">
								<? if (!empty($arResult['OFFERS_PROP'])) { ?>
									<div class="bx_catalog_item_scu wrapper_sku" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['PROP_DIV']; ?>">
										<? foreach ($arSkuTemplate as $code => $strTemplate) {
											if (!isset($arResult['OFFERS_PROP'][$code]) && $code != 'DIMENSIONS')
												continue;
											echo str_replace('#ITEM#_prop_', $arItemIDs["ALL_ITEM_IDS"]['PROP'], $strTemplate);
										} ?>
									</div>
								<? } ?>
							</div>
						</div>
						<? $frame->end(); ?>
					<? endif; ?>
					<? if ($arResult["SIZE_PATH"]) : ?>
						<div class="table_sizes muted777 font_xs">
							<span>
								<?= CMax::showIconSvg("cheaper", SITE_TEMPLATE_PATH . '/images/svg/catalog/sizestable.svg', '', '', true, false); ?>
								<span class="animate-load dotted" data-event="jqm" data-param-form_id="TABLES_SIZE" data-param-url="<?= $arResult["SIZE_PATH"]; ?>" data-name="TABLES_SIZE"><?= GetMessage("TABLES_SIZE"); ?></span>
							</span>
						</div>
					<? endif; ?>

					<? //props
					?>
					<? $bShowMoreLink = ($iCountProps > $arParams['VISIBLE_PROP_COUNT']); ?>
					<?
					$GLOBALS['arBuyEquipmentFilter']['PROPERTY_SERIYA_AKSECCUAR_VALUE'] = $arResult["SERIYA_SHKAFA"];
					?>
					<? if ($arResult["IBLOCK_SECTION_ID"] != 354) : ?>
						<div class="product-param-choose-block equipment-block" data-block="equipment" data-file="<?= $APPLICATION->GetCurPage() ?>"></div>
					<? endif; ?>


					<? if ($templateData['EXPANDABLES']) : ?>
						<?
						$bShowExpandablesTab = \Aspro\Functions\CAsproMax::checkAvailable($templateData['EXPANDABLES'], array('REGION'), $arParams);
						?>
						<? if ($bShowExpandablesTab) : ?>
							<? $bNavTabs = false; ?>
							<div class="product-param-choose-block expandables-block" data-block="expandables" style="display:none;" data-file="<?= $APPLICATION->GetCurPage() ?>">
								
							</div>
						<? endif; ?>
					<? endif; ?>
				</div>
			</div>
		</div>
	</div>
<? endif; ?>


<div class="big-button-wrapper">
	<button id="show-conf">ИЗМЕНИТЬ КОНФИГУРАЦИЮ</button>
</div>


<? //detail description
?>
<? // вывод описания из настроек
?>
<?
$boolSeriyaDescription = false;
if (!empty($arResult['PROPERTIES']['SERIYA_SHKAFA']["VALUE_ENUM_ID"])) {
	if (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_SYSTEM_SERIYA_' . $arResult['PROPERTIES']['SERIYA_SHKAFA']["VALUE_ENUM_ID"]))) {
		$boolSeriyaDescription = true;
	}
}
?>
<? if ($arResult['DETAIL_TEXT'] || $boolSeriyaDescription) : ?>
	<? $templateData['DETAIL_TEXT'] = true; ?>
	<? $this->SetViewTarget('PRODUCT_DETAIL_TEXT_INFO'); ?>
	<? if (!empty($arResult['DETAIL_TEXT'])) : ?>
		<div class="content" itemprop="description">
			<?= $arResult['DETAIL_TEXT']; ?>
		</div>
	<? elseif (\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_SYSTEM_SERIYA_' . $arResult['PROPERTIES']['SERIYA_SHKAFA']["VALUE_ENUM_ID"], 'N') != "N") : ?>
		<div class="content" itemprop="description">
			<?= htmlspecialchars_decode(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_SYSTEM_SERIYA_' . $arResult['PROPERTIES']['SERIYA_SHKAFA']["VALUE_ENUM_ID"], 'N')); ?>
		</div>
	<? else :?>
		<div class="content" itemprop="description"></div>
	<? endif; ?>
	<? $this->EndViewTarget(); ?>
<? endif; ?>

<? //additional gallery
?>
<? if ($arResult['ADDITIONAL_GALLERY']) : ?>
	<? $bShowSmallGallery = $arParams['ADDITIONAL_GALLERY_TYPE'] === 'SMALL'; ?>
	<? $templateData['ADDITIONAL_GALLERY'] = true; ?>
	<? $templateData['ADDITIONAL_GALLERY_COUNT'] = 0; ?>
	<? $templateData['PRODUCT_ADDITIONAL_GALLERY'] = $arResult['ADDITIONAL_GALLERY']; ?>
	<? foreach ($arResult['ADDITIONAL_GALLERY'] as $i => $arPhoto) {
		$templateData['ADDITIONAL_GALLERY_COUNT']++;
	} ?>
	
<? endif; ?>

<? //custom tab
?>
<? if ($arParams['SHOW_ADDITIONAL_TAB'] == 'Y') : ?>
	<? $this->SetViewTarget('PRODUCT_CUSTOM_TAB_INFO'); ?>
	<? $APPLICATION->IncludeFile(SITE_DIR . "include/additional_products_description.php", array(), array("MODE" => "html", "NAME" => GetMessage('CT_BCE_CATALOG_ADDITIONAL_DESCRIPTION'))); ?>
	<? $this->EndViewTarget(); ?>
<? endif; ?>

<? //instructions
?>
<? if ($arResult['PROPERTIES']['SERIYA_SHKAFA']['VALUE'] && $arResult['PROPERTIES']['TIP_SHKAFA']['VALUE']) : ?>
	<? $templateData['INSTRUCTIONS'] = true; ?>
	<? $this->SetViewTarget('PRODUCT_INSTRUCTIONS'); ?>
	<?php
	global $arrInstructionFilter;
	//$arrInstructionFilter['ID'] = $arResult["PROPERTIES"]["INSTRUCTIONS"]["VALUE"];
	$arSeriya = \Cosmos\Content::getIblockPropertyEnumListToArray('SERIYA_SHKAFA');
	$arTypeShkaf = \Cosmos\Content::getIblockPropertyEnumListToArray('TIP_SHKAFA');
	$arrInstructionFilter = [
		'LOGIC' => 'AND',
		['PROPERTY_TIP_SHKAFA' => $arTypeShkaf[$arResult["TIP_SHKAFA"]]],
		['PROPERTY_SERIYA_SHKAFA' =>  $arSeriya[$arResult["SERIYA_SHKAFA"]]],
	];


	$APPLICATION->IncludeComponent(
		"bitrix:news.list",
		"instructions",
		array(
			"CACHE_TIME" => "36000000",
			"CACHE_TYPE" => "A",
			"IBLOCK_ID" => \Cosmos\Config::getInstance()->getIblockIdByCode('instructions'),
			"IBLOCK_TYPE" => "content",
			"NEWS_COUNT" => "999",
			"FIELD_CODE" => array("NAME", "PREVIEW_TEXT", "DETAIL_PICTURE", ""),
			"PROPERTY_CODE" => array("SORT_MAIN"),
			"SET_BROWSER_TITLE" => "N",
			"SET_LAST_MODIFIED" => "N",
			"SET_META_DESCRIPTION" => "N",
			"SET_META_KEYWORDS" => "N",
			"SET_TITLE" => "N",
			"SORT_BY1" => "SORT",
			"SORT_BY2" => "ID",
			"SORT_ORDER1" => "ASC",
			"SORT_ORDER2" => "ASC",
			"FILTER_NAME" => "arrInstructionFilter",
			"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
			"ADD_SECTIONS_CHAIN" => "N",
		)
	);
	?>
	<? $this->EndViewTarget(); ?>
<? endif; ?>

<? //props content
?>
<? if (($arResult['DISPLAY_PROPERTIES'] || $arResult['DISPLAY_PROPERTIES_OFFERS']) && ($iCountProps > $arParams['VISIBLE_PROP_COUNT'])) : ?>
	<? $templateData['CHARACTERISTICS'] = true; ?>
	<? $this->SetViewTarget('PRODUCT_PROPS_INFO'); ?>
	<? $strGrupperType = $arParams["GRUPPER_PROPS"]; ?>
	<? if ($strGrupperType == "GRUPPER") : ?>
		<div class="char_block bordered rounded3 js-scrolled">
			<? $APPLICATION->IncludeComponent(
				"redsign:grupper.list",
				"",
				array(
					"CACHE_TIME" => "3600000",
					"CACHE_TYPE" => "A",
					"COMPOSITE_FRAME_MODE" => "A",
					"COMPOSITE_FRAME_TYPE" => "AUTO",
					"DISPLAY_PROPERTIES" => $arResult["GROUPS_PROPS"]
				),
				$component,
				array('HIDE_ICONS' => 'Y')
			); ?>
			<table class="props_list" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>"></table>
		</div>
	<? elseif ($strGrupperType == "WEBDEBUG") : ?>
		<div class="char_block bordered rounded3 js-scrolled">
			<? $APPLICATION->IncludeComponent(
				"webdebug:propsorter",
				"linear",
				array(
					"IBLOCK_TYPE" => $arResult['IBLOCK_TYPE'],
					"IBLOCK_ID" => $arResult['IBLOCK_ID'],
					"PROPERTIES" => $arResult['GROUPS_PROPS'],
					"EXCLUDE_PROPERTIES" => array(),
					"WARNING_IF_EMPTY" => "N",
					"WARNING_IF_EMPTY_TEXT" => "",
					"NOGROUP_SHOW" => "Y",
					"NOGROUP_NAME" => "",
					"MULTIPLE_SEPARATOR" => ", "
				),
				$component,
				array('HIDE_ICONS' => 'Y')
			); ?>
			<table class="props_list" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>"></table>
		</div>
	<? elseif ($strGrupperType == "YENISITE_GRUPPER") : ?>
		<div class="char_block bordered rounded3 js-scrolled">
			<? $APPLICATION->IncludeComponent(
				'yenisite:ipep.props_groups',
				'',
				array(
					'DISPLAY_PROPERTIES' => $arResult['GROUPS_PROPS'],
					'IBLOCK_ID' => $arParams['IBLOCK_ID']
				),
				$component,
				array('HIDE_ICONS' => 'Y')
			) ?>
			<table class="props_list colored_char" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>"></table>
		</div>
	<? else : ?>
		<? if ($arParams["PROPERTIES_DISPLAY_TYPE"] != "TABLE") : ?>
			<div class="props_block js-scrolled clearfix flexbox row" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>">
				<? foreach ($arResult["DISPLAY_PROPERTIES"] as $propCode => $arProp) : ?>
					<div class="char col-lg-3 col-md-4 col-xs-6 bordered" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
						<div class="char_name">
							<div class="props_item muted <? if ($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y") { ?>whint<? } ?>">
								<span itemprop="name"><?= $arProp["NAME"] ?></span>
							</div>
							<? if ($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y") : ?><div class="hint"><span class="icon"><i>?</i></span>
									<div class="tooltip"><?= $arProp["HINT"] ?></div>
								</div><? endif; ?>
						</div>
						<div class="char_value darken" itemprop="value">
							<? if (count($arProp["DISPLAY_VALUE"]) > 1) : ?>
								<?= implode(', ', $arProp["DISPLAY_VALUE"]); ?>
							<? else : ?>
								<?= $arProp["DISPLAY_VALUE"]; ?>
							<? endif; ?>
						</div>
					</div>
				<? endforeach; ?>
			</div>
		<? else : ?>
			<div class="char_block bordered rounded3 js-scrolled">
				<table class="props_list nbg">
					<? foreach ($arResult["DISPLAY_PROPERTIES"] as $arProp) : ?>
						<tr itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<td class="char_name">
								<div class="props_item <? if ($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y") { ?>whint<? } ?>">
									<span itemprop="name"><?= $arProp["NAME"] ?></span>
									<? if ($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y") : ?><div class="hint"><span class="icon"><i>?</i></span>
											<div class="tooltip"><?= $arProp["HINT"] ?></div>
										</div><? endif; ?>
								</div>
							</td>
							<td class="char_value">
								<span itemprop="value">
									<? if (is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1) : ?>
										<?= implode(', ', $arProp["DISPLAY_VALUE"]); ?>
									<? else : ?>
										<?= $arProp["DISPLAY_VALUE"]; ?>
									<? endif; ?>
								</span>
							</td>
						</tr>
					<? endforeach; ?>
				</table>
				<table class="props_list nbg" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>"></table>
			</div>
		<? endif; ?>
	<? endif; ?>
	<? $this->EndViewTarget(); ?>
<? endif; ?>
<? if ($arParams["SHOW_HOW_BUY"] == "Y") : ?>
	<? $this->SetViewTarget('PRODUCT_HOW_BUY_INFO'); ?>
	<? $APPLICATION->IncludeFile(SITE_DIR . "include/tab_catalog_detail_howbuy.php", array(), array("MODE" => "html", "NAME" => GetMessage('TITLE_HOW_BUY'))); ?>
	<? $this->EndViewTarget(); ?>
<? endif; ?>

<? if ($arParams["SHOW_PAYMENT"] == "Y") : ?>
	<? $this->SetViewTarget('PRODUCT_PAYMENT_INFO'); ?>
	<? $APPLICATION->IncludeFile(SITE_DIR . "include/tab_catalog_detail_payment.php", array(), array("MODE" => "html", "NAME" => GetMessage('TITLE_PAYMENT'))); ?>
	<? $this->EndViewTarget(); ?>
<? endif; ?>

<? if ($arParams["SHOW_DELIVERY"] == "Y") : ?>
	<? $this->SetViewTarget('PRODUCT_DELIVERY_INFO'); ?>
	<? $APPLICATION->IncludeFile(SITE_DIR . "include/tab_catalog_detail_delivery.php", array(), array("MODE" => "html", "NAME" => GetMessage('TITLE_DELIVERY'))); ?>
	<? $this->EndViewTarget(); ?>
<? endif; ?>

<? if ($arResult['VIDEO']) : ?>
	<? $this->SetViewTarget('PRODUCT_VIDEO_INFO'); ?>
	<div class="hidden_print">
		<div class="video_block row">
			<? if (count($arResult['VIDEO']) > 1) : ?>
				<? foreach ($arResult['VIDEO'] as $v => $value) : ?>
					<div class="col-sm-6 video_block_item">
						<?= str_replace('src=', 'width="660" height="457" src=', str_replace(array('width', 'height'), array('data-width', 'data-height'), $value)); ?>
					</div>
				<? endforeach; ?>
			<? else : ?>
				<div class="col-md-12"><?= $arResult['VIDEO'][0] ?></div>
			<? endif; ?>
		</div>
	</div>
	<? $this->EndViewTarget(); ?>
<? endif; ?>

<? //files
?>
<? if ($bItemFiles || $bSectionFiles) : ?>
	<?
	$templateData['FILES'] = $arFiles;
	if ($arFiles) : ?>
		<? $this->SetViewTarget('PRODUCT_FILES_INFO'); ?>
		<div class="char_block rounded3 bordered files_block">
			<div class="row flexbox">
				<? foreach ($arFiles as $arItem) : ?>
					<div class="col-xs-12">
						<? $arFile = CMax::GetFileInfo($arItem); ?>
						<div class="file_type clearfix <?= $arFile["TYPE"]; ?> flexbox flexbox--row">
							<?/*<i class="icon"></i>*/ ?>
							<div class="icon"><?= $arFile["TYPE"]; ?></div>
							<div class="description">
								<a target="_blank" href="<?= $arFile["SRC"]; ?>" class="dark_link"><?= $arFile["DESCRIPTION"]; ?></a>
								<span class="size font_xs muted">
									<?= $arFile["FILE_SIZE_FORMAT"]; ?>
								</span>
							</div>
							<div class="load"><i class="fas fa-arrow-alt-circle-down"></i><a href="<?= $arFile["SRC"]; ?>"><?= GetMessage('CATALOG_DOWNLOAD'); ?></a></div>
						</div>
					</div>
				<? endforeach; ?>
			</div>
		</div>
		<? $this->EndViewTarget(); ?>
	<? endif; ?>
<? endif; ?>

<? $this->SetViewTarget('PRODUCT_GIFT_INFO'); ?>
<div class="gifts">
	<? if ($arResult['CATALOG'] && $arParams['USE_GIFTS_DETAIL'] == 'Y' && \Bitrix\Main\ModuleManager::isModuleInstalled("sale")) {
		$APPLICATION->IncludeComponent("bitrix:sale.gift.product", "main", array(
			"USE_REGION" => $arParams['USE_REGION'] !== 'N' ? 'Y' : 'N',
			"STORES" => $arParams['STORES'],
			"SHOW_UNABLE_SKU_PROPS" => $arParams["SHOW_UNABLE_SKU_PROPS"],
			'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
			'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
			'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
			'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
			'SUBSCRIBE_URL_TEMPLATE' => $arResult['~SUBSCRIBE_URL_TEMPLATE'],
			'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
			"OFFER_HIDE_NAME_PROPS" => $arParams["OFFER_HIDE_NAME_PROPS"],

			"SHOW_DISCOUNT_PERCENT" => "N",
			"SHOW_OLD_PRICE" => $arParams['GIFTS_SHOW_OLD_PRICE'],
			"PAGE_ELEMENT_COUNT" => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
			"LINE_ELEMENT_COUNT" => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
			"HIDE_BLOCK_TITLE" => $arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE'],
			"BLOCK_TITLE" => $arParams['GIFTS_DETAIL_BLOCK_TITLE'],
			"TEXT_LABEL_GIFT" => $arParams['GIFTS_DETAIL_TEXT_LABEL_GIFT'],
			"SHOW_NAME" => $arParams['GIFTS_SHOW_NAME'],
			"SHOW_IMAGE" => $arParams['GIFTS_SHOW_IMAGE'],
			"MESS_BTN_BUY" => $arParams['GIFTS_MESS_BTN_BUY'],

			"SHOW_PRODUCTS_{$arParams['IBLOCK_ID']}" => "Y",
			"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
			"PRODUCT_SUBSCRIPTION" => $arParams["PRODUCT_SUBSCRIPTION"],
			"MESS_BTN_DETAIL" => $arParams["MESS_BTN_DETAIL"],
			"MESS_BTN_SUBSCRIBE" => $arParams["MESS_BTN_SUBSCRIBE"],
			"TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
			"PRICE_CODE" => $arParams["PRICE_CODE"],
			"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
			"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
			"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
			"CURRENCY_ID" => $arParams["CURRENCY_ID"],
			"BASKET_URL" => $arParams["BASKET_URL"],
			"ADD_PROPERTIES_TO_BASKET" => $arParams["ADD_PROPERTIES_TO_BASKET"],
			"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
			"PARTIAL_PRODUCT_PROPERTIES" => $arParams["PARTIAL_PRODUCT_PROPERTIES"],
			"USE_PRODUCT_QUANTITY" => 'N',
			"OFFER_TREE_PROPS_{$arResult['OFFERS_IBLOCK']}" => $arParams['OFFER_TREE_PROPS'],
			"CART_PROPERTIES_{$arResult['OFFERS_IBLOCK']}" => $arParams['OFFERS_CART_PROPERTIES'],
			"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
			"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"SHOW_DISCOUNT_TIME" => "N",
			"SHOW_ONE_CLICK_BUY" => "N",
			"SHOW_DISCOUNT_PERCENT_NUMBER" => "N",
			"SALE_STIKER" => $arParams["SALE_STIKER"],
			"STIKERS_PROP" => $arParams["STIKERS_PROP"],
			"SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
			"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
			"DISPLAY_TYPE" => "block",
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"DISPLAY_COMPARE" => $arParams["DISPLAY_COMPARE"],
			"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
			"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
			"TYPE_SKU" => "Y",

			"POTENTIAL_PRODUCT_TO_BUY" => array(
				'ID' => isset($arResult['ID']) ? $arResult['ID'] : null,
				'MODULE' => isset($arResult['MODULE']) ? $arResult['MODULE'] : 'catalog',
				'PRODUCT_PROVIDER_CLASS' => isset($arResult['PRODUCT_PROVIDER_CLASS']) ? $arResult['PRODUCT_PROVIDER_CLASS'] : 'CCatalogProductProvider',
				'QUANTITY' => isset($arResult['QUANTITY']) ? $arResult['QUANTITY'] : null,
				'IBLOCK_ID' => isset($arResult['IBLOCK_ID']) ? $arResult['IBLOCK_ID'] : null,

				'PRIMARY_OFFER_ID' => isset($arResult['OFFERS'][0]['ID']) ? $arResult['OFFERS'][0]['ID'] : null,
				'SECTION' => array(
					'ID' => isset($arResult['SECTION']['ID']) ? $arResult['SECTION']['ID'] : null,
					'IBLOCK_ID' => isset($arResult['SECTION']['IBLOCK_ID']) ? $arResult['SECTION']['IBLOCK_ID'] : null,
					'LEFT_MARGIN' => isset($arResult['SECTION']['LEFT_MARGIN']) ? $arResult['SECTION']['LEFT_MARGIN'] : null,
					'RIGHT_MARGIN' => isset($arResult['SECTION']['RIGHT_MARGIN']) ? $arResult['SECTION']['RIGHT_MARGIN'] : null,
				),
			)
		), $component, array("HIDE_ICONS" => "Y"));
	}
	if ($arResult['CATALOG'] && $arParams['USE_GIFTS_MAIN_PR_SECTION_LIST'] == 'Y' && \Bitrix\Main\ModuleManager::isModuleInstalled("sale")) {
		$APPLICATION->IncludeComponent(
			"bitrix:sale.gift.main.products",
			"main",
			array(
				"USE_REGION" => $arParams['USE_REGION'] !== 'N' ? 'Y' : 'N',
				"STORES" => $arParams['STORES'],
				"SHOW_UNABLE_SKU_PROPS" => $arParams["SHOW_UNABLE_SKU_PROPS"],
				"PAGE_ELEMENT_COUNT" => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'],
				"BLOCK_TITLE" => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'],

				"OFFERS_FIELD_CODE" => $arParams["OFFERS_FIELD_CODE"],
				"OFFERS_PROPERTY_CODE" => $arParams["OFFERS_PROPERTY_CODE"],

				"AJAX_MODE" => $arParams["AJAX_MODE"],
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],

				"ELEMENT_SORT_FIELD" => 'ID',
				"ELEMENT_SORT_ORDER" => 'DESC',
				//"ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
				//"ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
				"FILTER_NAME" => 'searchFilter',
				"SECTION_URL" => $arParams["SECTION_URL"],
				"DETAIL_URL" => $arParams["DETAIL_URL"],
				"BASKET_URL" => $arParams["BASKET_URL"],
				"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
				"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
				"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],

				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"SHOW_ONE_CLICK_BUY" => "N",
				"CACHE_TIME" => $arParams["CACHE_TIME"],

				"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
				"SET_TITLE" => $arParams["SET_TITLE"],
				"PROPERTY_CODE" => "",
				"PRICE_CODE" => $arParams["PRICE_CODE"],
				"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
				"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],

				"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
				"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
				"CURRENCY_ID" => $arParams["CURRENCY_ID"],
				"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
				"TEMPLATE_THEME" => (isset($arParams["TEMPLATE_THEME"]) ? $arParams["TEMPLATE_THEME"] : ""),

				"ADD_PICT_PROP" => (isset($arParams["ADD_PICT_PROP"]) ? $arParams["ADD_PICT_PROP"] : ""),

				"LABEL_PROP" => (isset($arParams["LABEL_PROP"]) ? $arParams["LABEL_PROP"] : ""),
				"OFFER_ADD_PICT_PROP" => (isset($arParams["OFFER_ADD_PICT_PROP"]) ? $arParams["OFFER_ADD_PICT_PROP"] : ""),
				"OFFER_TREE_PROPS" => (isset($arParams["OFFER_TREE_PROPS"]) ? $arParams["OFFER_TREE_PROPS"] : ""),
				"SHOW_DISCOUNT_PERCENT" => "N",
				"SHOW_OLD_PRICE" => (isset($arParams["SHOW_OLD_PRICE"]) ? $arParams["SHOW_OLD_PRICE"] : ""),
				"MESS_BTN_BUY" => (isset($arParams["MESS_BTN_BUY"]) ? $arParams["MESS_BTN_BUY"] : ""),
				"MESS_BTN_ADD_TO_BASKET" => (isset($arParams["MESS_BTN_ADD_TO_BASKET"]) ? $arParams["MESS_BTN_ADD_TO_BASKET"] : ""),
				"MESS_BTN_DETAIL" => (isset($arParams["MESS_BTN_DETAIL"]) ? $arParams["MESS_BTN_DETAIL"] : ""),
				"MESS_NOT_AVAILABLE" => (isset($arParams["MESS_NOT_AVAILABLE"]) ? $arParams["MESS_NOT_AVAILABLE"] : ""),
				'ADD_TO_BASKET_ACTION' => (isset($arParams["ADD_TO_BASKET_ACTION"]) ? $arParams["ADD_TO_BASKET_ACTION"] : ""),
				'SHOW_CLOSE_POPUP' => (isset($arParams["SHOW_CLOSE_POPUP"]) ? $arParams["SHOW_CLOSE_POPUP"] : ""),
				'DISPLAY_COMPARE' => (isset($arParams['DISPLAY_COMPARE']) ? $arParams['DISPLAY_COMPARE'] : ''),
				'COMPARE_PATH' => (isset($arParams['COMPARE_PATH']) ? $arParams['COMPARE_PATH'] : ''),
				"SHOW_DISCOUNT_TIME" => "N",
				"SHOW_DISCOUNT_PERCENT_NUMBER" => "N",
				"SALE_STIKER" => $arParams["SALE_STIKER"],
				"STIKERS_PROP" => $arParams["STIKERS_PROP"],
				"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
				"DISPLAY_TYPE" => "block",
				"SHOW_RATING" => $arParams["SHOW_RATING"],
				"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
				"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
			)
				+ array(
					'OFFER_ID' => empty($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']) ? $arResult['ID'] : $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'],
					'SECTION_ID' => $arResult['SECTION']['ID'],
					'ELEMENT_ID' => $arResult['ID'],
				),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}

	?>
</div>
<? $this->EndViewTarget(); ?>

<div class="mobile-summary">
	<div class="mobile-summary-content">
		<? if ($arResult["MORE_PHOTO"]) : ?>
			<div class="img" id="mobile-summary-img">
				<img src="<?= $arFirstPhoto['SAFE_SRC']; ?>" alt="">
			</div>
		<? endif; ?>
		<div class="title"><?= $elementName ?></div>
		<div class="text">
			<div class="price_buy">
				<div class="price"></div>
				<button class="button" id="mobile-summary-buy">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
						<path d="M1 1H1.49682C2.44171 1 3.25768 1.66127 3.45343 2.58566L3.75294 4M3.75294 4L5.23529 11H14.971C15.8027 11 16.5476 10.4853 16.8417 9.70741L18.4881 5.35371C18.7355 4.69954 18.2521 4 17.5527 4H3.75294ZM9.46154 11H4.5C3.67157 11 3 11.6716 3 12.5C3 13.3284 3.67157 14 4.5 14H17M5.5 19C4.67157 19 4 18.3284 4 17.5C4 16.6716 4.67157 16 5.5 16C6.32843 16 7 16.6716 7 17.5C7 18.3284 6.32843 19 5.5 19ZM14.5 19C13.6716 19 13 18.3284 13 17.5C13 16.6716 13.6716 16 14.5 16C15.3284 16 16 16.6716 16 17.5C16 18.3284 15.3284 19 14.5 19Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>
		</div>
	</div>
</div>

<div class="overlay-conf">
	<div class="overlay-content">
		<div class="overlay-title">
			<div id="close-conf">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<path d="M4 12H20M4 12L10 18M4 12L10 6" stroke="#999999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</div>
			<span>ИЗМЕНИТЬ КОНФИГУРАЦИЮ</span>
		</div>
		<div class="overlay-header">
			<? if ($arResult["MORE_PHOTO"]) : ?>
				<div class="img" id="overlay-img">
					<img src="<?= $arFirstPhoto['SAFE_SRC']; ?>" alt="">
				</div>
			<? endif; ?>
			<div class="text">
				<h4><?= $elementName ?></h4>
				<div class="price_buy">
					<div class="price"></div>
					<button class="button" id="overlay-buy">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
							<path d="M1 1H1.49682C2.44171 1 3.25768 1.66127 3.45343 2.58566L3.75294 4M3.75294 4L5.23529 11H14.971C15.8027 11 16.5476 10.4853 16.8417 9.70741L18.4881 5.35371C18.7355 4.69954 18.2521 4 17.5527 4H3.75294ZM9.46154 11H4.5C3.67157 11 3 11.6716 3 12.5C3 13.3284 3.67157 14 4.5 14H17M5.5 19C4.67157 19 4 18.3284 4 17.5C4 16.6716 4.67157 16 5.5 16C6.32843 16 7 16.6716 7 17.5C7 18.3284 6.32843 19 5.5 19ZM14.5 19C13.6716 19 13 18.3284 13 17.5C13 16.6716 13.6716 16 14.5 16C15.3284 16 16 16.6716 16 17.5C16 18.3284 15.3284 19 14.5 19Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
						</svg>
						<svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" style="fill:#fff;">
							<path data-name="Rounded Rectangle 906 copy 3" class="cls-1" d="M1005.97,4556.22l-1.01,4.02a0.031,0.031,0,0,0-.01.02,0.87,0.87,0,0,1-.14.29,0.423,0.423,0,0,1-.05.07,0.7,0.7,0,0,1-.2.18,0.359,0.359,0,0,1-.1.07,0.656,0.656,0,0,1-.21.08,1.127,1.127,0,0,1-.18.03,0.185,0.185,0,0,1-.07.02H993c-0.03,0-.056-0.02-0.086-0.02a1.137,1.137,0,0,1-.184-0.04,0.779,0.779,0,0,1-.207-0.08c-0.031-.02-0.059-0.04-0.088-0.06a0.879,0.879,0,0,1-.223-0.22s-0.007-.01-0.011-0.01a1,1,0,0,1-.172-0.43l-1.541-6.14H988a1,1,0,1,1,0-2h3.188a0.3,0.3,0,0,1,.092.02,0.964,0.964,0,0,1,.923.76l1.561,6.22h9.447l0.82-3.25a1,1,0,0,1,1.21-.73A0.982,0.982,0,0,1,1005.97,4556.22Zm-7.267.47c0,0.01,0,.01,0,0.01a1,1,0,0,1-1.414,0l-2.016-2.03a0.982,0.982,0,0,1,0-1.4,1,1,0,0,1,1.414,0l1.305,1.31,4.3-4.3a1,1,0,0,1,1.41,0,1.008,1.008,0,0,1,0,1.42ZM995,4562a3,3,0,1,1-3,3A3,3,0,0,1,995,4562Zm0,4a1,1,0,1,0-1-1A1,1,0,0,0,995,4566Zm7-4a3,3,0,1,1-3,3A3,3,0,0,1,1002,4562Zm0,4a1,1,0,1,0-1-1A1,1,0,0,0,1002,4566Z" transform="translate(-987 -4550)" style="fill:#fff;"></path>
						</svg>
					</button>
				</div>
			</div>
		</div>
		<div class="overlay-content-inner">
			<ol class="overlay-conf-selector">
				<li class="overlay-conf-selector-btn" data-toshow="dimensions">
					Габариты (ВхШхГ, мм)
				</li>
				<li class="overlay-conf-selector-btn" data-toshow="color_ref">
					Цвет корпуса
				</li>
				<li class="overlay-conf-selector-btn" data-toshow="housing_layout">
					Компоновка корпуса
				</li>
				<li class="overlay-conf-selector-btn" data-toshow="tsvet_profilya">
					Цвет профиля
				</li>
				<!-- <li class="overlay-conf-selector-btn" data-toshow="equipment">
					Аксессуары
				</li> -->
			</ol>
		</div>
		<div class="overlay-footer">
			<div id="step-back">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<path d="M15 18L9 12L15 6.00001" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
				<span>Назад</span>
			</div>
			<div id="step-forward">
				<span>Далее</span>
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<path d="M9 5.99999L15 12L9 18" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</div>
			<div id="step-buy">
				<div class="step-buy-inner">
					Купить
				</div>
				<div class="step-buy-inner">
					<svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" style="fill:#fff;">
						<path data-name="Rounded Rectangle 906 copy 3" class="cls-1" d="M1005.97,4556.22l-1.01,4.02a0.031,0.031,0,0,0-.01.02,0.87,0.87,0,0,1-.14.29,0.423,0.423,0,0,1-.05.07,0.7,0.7,0,0,1-.2.18,0.359,0.359,0,0,1-.1.07,0.656,0.656,0,0,1-.21.08,1.127,1.127,0,0,1-.18.03,0.185,0.185,0,0,1-.07.02H993c-0.03,0-.056-0.02-0.086-0.02a1.137,1.137,0,0,1-.184-0.04,0.779,0.779,0,0,1-.207-0.08c-0.031-.02-0.059-0.04-0.088-0.06a0.879,0.879,0,0,1-.223-0.22s-0.007-.01-0.011-0.01a1,1,0,0,1-.172-0.43l-1.541-6.14H988a1,1,0,1,1,0-2h3.188a0.3,0.3,0,0,1,.092.02,0.964,0.964,0,0,1,.923.76l1.561,6.22h9.447l0.82-3.25a1,1,0,0,1,1.21-.73A0.982,0.982,0,0,1,1005.97,4556.22Zm-7.267.47c0,0.01,0,.01,0,0.01a1,1,0,0,1-1.414,0l-2.016-2.03a0.982,0.982,0,0,1,0-1.4,1,1,0,0,1,1.414,0l1.305,1.31,4.3-4.3a1,1,0,0,1,1.41,0,1.008,1.008,0,0,1,0,1.42ZM995,4562a3,3,0,1,1-3,3A3,3,0,0,1,995,4562Zm0,4a1,1,0,1,0-1-1A1,1,0,0,0,995,4566Zm7-4a3,3,0,1,1-3,3A3,3,0,0,1,1002,4562Zm0,4a1,1,0,1,0-1-1A1,1,0,0,0,1002,4566Z" transform="translate(-987 -4550)" style="fill:#fff;"></path>
					</svg>
					В коризине
				</div>
			</div>
		</div>
	</div>
</div>

<? if ($arParams['AJAX_JS']) : ?>
	<div id="template_js_place"></div>
	<script async>
		$.post(
			window.location.pathname, {
				'get_template_js': "y"
			},
			function(html) {
				$(html).find('script[data-type="ajax"]').each(function(index, el) {
					$("#template_js_place").after(el);
				});
			}
		);
	</script>
<? endif; ?>

<? if ($arParams['CLEAR_TEMPLATE_DATA_OFFERS']) {
	if (!empty($templateData['OFFERS_INFO']['OFFERS'])) {
		$templateData['PRODUCT_ADDITIONAL_GALLERY'] = [];
	}
	$templateData['OFFERS_INFO']['OFFERS'] = [];
} ?>

<?
$sTemplateContent = ob_get_contents();
ob_end_clean();
$arTemplateGapsSearch = array(
	'/\>[^\S ]+/s',     // strip whitespaces after tags, except space
	'/[^\S ]+\</s',     // strip whitespaces before tags, except space
	'/(\s)+/s',         // shorten multiple whitespace sequences
	'/<!--(.|\s)*?-->/' // Remove HTML comments
);
$arTemplateGapsReplace = array(
	'>',
	'<',
	'\\1',
	''
);
$sTemplateContent = preg_replace($arTemplateGapsSearch, $arTemplateGapsReplace, $sTemplateContent);
echo $sTemplateContent;
?>
<? if ($arResult["RESTRICTION"]) : ?>
	<style>
		.wrapp-one-click {
			display: none !important;
		}

		.counter_wrapp,
		.button_block {
			min-width: 100% !important;
		}
	</style>
	<script>
		BX.addCustomEvent('onCompleteAction', function(data, el) {
			if (data.action == 'loadForm' && $(el).hasClass('to-cart')) {
				$.ajax({
					type: "POST",
					url: arAsproOptions['SITE_DIR'] + "ajax/checkRestriction.php",
					data: {
						section: <?= $arResult["SECTION"]['ID'] ?>
					},
					dataType: "json",
					success: function(data) {
						if (data.show) {
							showBasketError('Покупка возможна только при условии приобретения шкафа.', 'Внимание');
						}
					}
				});
			}
		})
	</script>
<? endif; ?>

<?php
// подключаем Яндекс Пэй 
// Bitrix\Main\Page\Asset::getInstance()->addString('<script src="https://pay.yandex.ru/sdk/v1/pay.js?v=' . time() . '" async></script>');
?>
<script src="https://pay.yandex.ru/sdk/v1/pay.js" async></script>