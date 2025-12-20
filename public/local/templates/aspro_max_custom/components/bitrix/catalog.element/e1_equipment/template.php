<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);

$templateLibrary = array('popup', 'fx');
$currencyList = '';

if (!empty($arResult['CURRENCIES'])) {
    $templateLibrary[] = 'currency';
    $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$templateData = array(
    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
    'TEMPLATE_LIBRARY' => $templateLibrary,
    'CURRENCIES' => $currencyList,
    'ITEM' => array(
        'ID' => $arResult['ID'],
        'IBLOCK_ID' => $arResult['IBLOCK_ID'],
        'OFFERS_SELECTED' => $arResult['OFFERS_SELECTED'],
        'JS_OFFERS' => $arResult['JS_OFFERS']
    )
);
unset($currencyList, $templateLibrary);

$mainId = $this->GetEditAreaId($arResult['ID']);
$itemIds = array(
    'ID' => $mainId,
    'DISCOUNT_PERCENT_ID' => $mainId . '_dsc_pict',
    'STICKER_ID' => $mainId . '_sticker',
    'BIG_SLIDER_ID' => $mainId . '_big_slider',
    'BIG_IMG_CONT_ID' => $mainId . '_bigimg_cont',
    'SLIDER_CONT_ID' => $mainId . '_slider_cont',
    'OLD_PRICE_ID' => $mainId . '_old_price',
    'PRICE_ID' => $mainId . '_price',
    'DISCOUNT_PRICE_ID' => $mainId . '_price_discount',
    'PRICE_TOTAL' => $mainId . '_price_total',
    'SLIDER_CONT_OF_ID' => $mainId . '_slider_cont_',
    'QUANTITY_ID' => $mainId . '_quantity',
    'QUANTITY_DOWN_ID' => $mainId . '_quant_down',
    'QUANTITY_UP_ID' => $mainId . '_quant_up',
    'QUANTITY_MEASURE' => $mainId . '_quant_measure',
    'QUANTITY_LIMIT' => $mainId . '_quant_limit',
    'BUY_LINK' => $mainId . '_buy_link',
    'ADD_BASKET_LINK' => $mainId . '_add_basket_link',
    'BASKET_ACTIONS_ID' => $mainId . '_basket_actions',
    'NOT_AVAILABLE_MESS' => $mainId . '_not_avail',
    'COMPARE_LINK' => $mainId . '_compare_link',
    'TREE_ID' => $mainId . '_skudiv',
    'DISPLAY_PROP_DIV' => $mainId . '_sku_prop',
    'DESCRIPTION_ID' => $mainId . '_description',
    'DISPLAY_MAIN_PROP_DIV' => $mainId . '_main_sku_prop',
    'OFFER_GROUP' => $mainId . '_set_group_',
    'BASKET_PROP_DIV' => $mainId . '_basket_prop',
    'SUBSCRIBE_LINK' => $mainId . '_subscribe',
    'TABS_ID' => $mainId . '_tabs',
    'TAB_CONTAINERS_ID' => $mainId . '_tab_containers',
    'SMALL_CARD_PANEL_ID' => $mainId . '_small_card_panel',
    'TABS_PANEL_ID' => $mainId . '_tabs_panel'
);
$obName = $templateData['JS_OBJ'] = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
    : $arResult['NAME'];
$title = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
    : $arResult['NAME'];
$alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
    : $arResult['NAME'];

$haveOffers = !empty($arResult['OFFERS']);
if ($haveOffers) {
    $actualItem = isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']])
        ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]
        : reset($arResult['OFFERS']);
    $showSliderControls = false;

    foreach ($arResult['OFFERS'] as $offer) {
        if ($offer['MORE_PHOTO_COUNT'] > 1) {
            $showSliderControls = true;
            break;
        }
    }
} else {
    $actualItem = $arResult;
    $showSliderControls = $arResult['MORE_PHOTO_COUNT'] > 1;
}

$skuProps = array();
$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
$measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$showDiscount = $price['PERCENT'] > 0;
$arResultBasket['ID'] = $arParams['ELEMENT_ID'];//$arItem["ID"];
$arResultBasket['ELEMENT'] = CIBlockElement::GetByID(
    (int)$arResultBasket['ID']
)->Fetch();
if (empty($arResultBasket['ELEMENT']["IBLOCK_SECTION_ID"])) {
    $mxResult = CCatalogSku::GetProductInfo(
        $arResultBasket['ID']
    );
    if (is_array($mxResult)) {
        $arResultBasket['ELEMENT']["IBLOCK_SECTION_ID"] = CIBlockElement::GetByID(
            (int)$mxResult['ID']
        )->Fetch()["IBLOCK_SECTION_ID"];
        $arResultBasket['ID'] = $mxResult['ID'];
    }
}
$priceModuleAssembly = (!empty(\Bitrix\Main\Config\Option::set('e1.site.settings', 'E1_SS_PRICE_MODULE_ASSEMBLY', ''))) ? \Bitrix\Main\Config\Option::set('e1.site.settings', 'E1_SS_PRICE_MODULE_ASSEMBLY', '') : "PRICE_UM";//по умолчанию цена идет от углового модуля, в конфиге можно задать свою PRICE которую подставить можно
$arResult['SERVICE_MODULE_IBLOCK_ID'] = \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_services');
$arResult["BASKET_SERVICES"] = \COrwoFunctions::getServices($arResultBasket)[$priceModuleAssembly];
if (!empty($arResult["BASKET_SERVICES"])) {
    $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] = $arResult["BASKET_SERVICES"];
    $product_properties_services = $arRewriteFields = array();
    $product_properties_services = CIBlockPriceTools::CheckProductProperties(
        $arResult['SERVICE_MODULE_IBLOCK_ID'],
        $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]],
        array("BUY_PRODUCT_PROP"),
        array("BUY_PRODUCT_PROP" => htmlspecialcharsEx($arResultBasket['ID'])),
        'Y'
    );
    $product_properties_services = is_array($product_properties_services) ? $product_properties_services : array();
    $product_properties_services[] = array("NAME" => 'link_id', "CODE" => 'ASPRO_BUY_PRODUCT_ID', 'VALUE' => htmlspecialcharsEx($arResultBasket['ID']));

    $arSelect = array("ID", "PROPERTY_PRICE");
    $arFilter = array("IBLOCK_ID" => $arResult['SERVICE_MODULE_IBLOCK_ID'], "ID" => $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]);
    $res = CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 1), $arSelect);
    if ($service = $res->Fetch()) { ?>
        <?
        define('ASSEMBLY_PRICE', 1499);
        define('URGENT_ASSEMBLY_PRICE', 2499);
        $arResult['SERVICE_MODULE_PRICE'] = ceil($price["RATIO_BASE_PRICE"] * str_replace('%', '', $service['PROPERTY_PRICE_VALUE']) / 100);
        if ($arResult['SERVICE_MODULE_PRICE'] < ASSEMBLY_PRICE) {
            $arResult['SERVICE_MODULE_PRICE'] = ASSEMBLY_PRICE;
        }

        $arResult['SERVICE_MODULE_PRICE_PERCENT'] = str_replace('%', '', $service['PROPERTY_PRICE_VALUE']);

        ?>
        <div class="service-module-price-assembly" data-price="<?= $arResult['SERVICE_MODULE_PRICE'] ?>"
             data-service-percent="<?= $arResult['SERVICE_MODULE_PRICE_PERCENT'] ?>"></div>
        <?
        $arResult['SERVICE_MODULE_PRICE_URGENT_PERCENT'] = 20;

        $arResult['SERVICE_MODULE_PRICE'] = ceil($price["RATIO_BASE_PRICE"] * str_replace('%', '', $arResult['SERVICE_MODULE_PRICE_URGENT_PERCENT']) / 100);
        if ($arResult['SERVICE_MODULE_PRICE_URGENT'] < URGENT_ASSEMBLY_PRICE) {
            $arResult['SERVICE_MODULE_PRICE_URGENT'] = URGENT_ASSEMBLY_PRICE;
        }

        ?>
        <div class="service-module-urgent-price-assembly"
             data-price="<?= $arResult['SERVICE_MODULE_PRICE_URGENT_PERCENT'] ?>"></div>
    <?
    }
}
if ($arParams['SHOW_SKU_DESCRIPTION'] === 'Y') {
    $skuDescription = false;
    foreach ($arResult['OFFERS'] as $offer) {
        if ($offer['DETAIL_TEXT'] != '' || $offer['PREVIEW_TEXT'] != '') {
            $skuDescription = true;
            break;
        }
    }
    $showDescription = $skuDescription || !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
} else {
    $showDescription = !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
}
$showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
$buyButtonClassName = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-primary' : 'btn-link';
$showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
$showButtonClassName = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-primary' : 'btn-link';
$showSubscribe = $arParams['PRODUCT_SUBSCRIPTION'] === 'Y' && ($arResult['PRODUCT']['SUBSCRIBE'] === 'Y' || $haveOffers);

$arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
$arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCE_CATALOG_ADD');
$arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');
$arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
$arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
$arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
$arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
$arParams['MESS_COMMENTS_TAB'] = $arParams['MESS_COMMENTS_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_COMMENTS_TAB');
$arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
$arParams['MESS_RELATIVE_QUANTITY_MANY'] = $arParams['MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_FEW'] = $arParams['MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');

$positionClassMap = array(
    'left' => 'product-item-label-left',
    'center' => 'product-item-label-center',
    'right' => 'product-item-label-right',
    'bottom' => 'product-item-label-bottom',
    'middle' => 'product-item-label-middle',
    'top' => 'product-item-label-top'
);

$discountPositionClass = 'product-item-label-big';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION'])) {
    foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
        $discountPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

$labelPositionClass = 'product-item-label-big';
if (!empty($arParams['LABEL_PROP_POSITION'])) {
    foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
        $labelPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-' . $arParams['TEMPLATE_THEME'] : '';
?>
<? /*<div class="main-catalog-wrapper details">
	<div class="product-container catalog_detail detail element_e1 clearfix">
<div class="product-container catalog_detail detail">*/ ?>
    <div class="bx-catalog-element<?= $themeClass ?>" id="<?= $itemIds['ID'] ?>"
         data-id="<?= $arParams['ELEMENT_ID'] ?>" itemscope itemtype="http://schema.org/Product">
        <?
        /*if ($arParams['DISPLAY_NAME'] === 'Y')
	{
		?>
		<h1 class="mb-3"><?=$name?></h1>
		<?
	}*/
        ?>
        <div class=" correction_margin product<?= $arParams['ELEMENT_ID'] ?>" data-id="<?= $arParams['ELEMENT_ID'] ?>"
             style="display:none">
            <div class="col-md">
                <div class="product-item-detail-slider-container" style="display:none"
                     id="<?= $itemIds['BIG_SLIDER_ID'] ?>">
                    <span class="product-item-detail-slider-close" data-entity="close-popup"></span>
                    <div class="product-item-detail-slider-block
				<?= ($arParams['IMAGE_RESOLUTION'] === '1by1' ? 'product-item-detail-slider-block-square' : '') ?>"
                         data-entity="images-slider-block">
                        <span class="product-item-detail-slider-left" data-entity="slider-control-left"
                              style="display: none;"></span>
                        <span class="product-item-detail-slider-right" data-entity="slider-control-right"
                              style="display: none;"></span>
                        <div class="product-item-label-text <?= $labelPositionClass ?>"
                             id="<?= $itemIds['STICKER_ID'] ?>"
                            <?= (!$arResult['LABEL'] ? 'style="display: none;"' : '') ?>>
                            <?
                            if ($arResult['LABEL'] && !empty($arResult['LABEL_ARRAY_VALUE'])) {
                                foreach ($arResult['LABEL_ARRAY_VALUE'] as $code => $value) {
                                    ?>
                                    <div<?= (!isset($arParams['LABEL_PROP_MOBILE'][$code]) ? ' class="hidden-xs"' : '') ?>>
                                        <span title="<?= $value ?>"><?= $value ?></span>
                                    </div>
                                    <?
                                }
                            }
                            ?>
                        </div>
                        <?
                        if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y') {
                            if ($haveOffers) {
                                ?>
                                <div class="product-item-label-ring <?= $discountPositionClass ?>"
                                     id="<?= $itemIds['DISCOUNT_PERCENT_ID'] ?>"
                                     style="display: none;">
                                </div>
                                <?
                            } else {
                                if ($price['DISCOUNT'] > 0) {
                                    ?>
                                    <div class="product-item-label-ring <?= $discountPositionClass ?>"
                                         id="<?= $itemIds['DISCOUNT_PERCENT_ID'] ?>"
                                         title="<?= -$price['PERCENT'] ?>%">
                                        <span><?= -$price['PERCENT'] ?>%</span>
                                    </div>
                                    <?
                                }
                            }
                        }
                        ?>
                        <div class="product-item-detail-slider-images-container" data-entity="images-container">
                            <?
                            if (!empty($actualItem['MORE_PHOTO'])) {
                                foreach ($actualItem['MORE_PHOTO'] as $key => $photo) {
                                    ?>
                                    <div class="product-item-detail-slider-image<?= ($key == 0 ? ' active' : '') ?>"
                                         data-entity="image" data-id="<?= $photo['ID'] ?>">
                                        <img src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>"
                                             title="<?= $title ?>"<?= ($key == 0 ? ' itemprop="image"' : '') ?>>
                                    </div>
                                    <?
                                }
                            }

                            if ($arParams['SLIDER_PROGRESS'] === 'Y') {
                                ?>
                                <div class="product-item-detail-slider-progress-bar" data-entity="slider-progress-bar"
                                     style="width: 0;"></div>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                    <?
                    if ($showSliderControls) {
                        if ($haveOffers) {
                            foreach ($arResult['OFFERS'] as $keyOffer => $offer) {
                                if (!isset($offer['MORE_PHOTO_COUNT']) || $offer['MORE_PHOTO_COUNT'] <= 0)
                                    continue;

                                $strVisible = $arResult['OFFERS_SELECTED'] == $keyOffer ? '' : 'none';
                                ?>
                                <div class="product-item-detail-slider-controls-block"
                                     id="<?= $itemIds['SLIDER_CONT_OF_ID'] . $offer['ID'] ?>"
                                     style="display: <?= $strVisible ?>;">
                                    <?
                                    foreach ($offer['MORE_PHOTO'] as $keyPhoto => $photo) {
                                        ?>
                                        <div
                                            class="product-item-detail-slider-controls-image<?= ($keyPhoto == 0 ? ' active' : '') ?>"
                                            data-entity="slider-control"
                                            data-value="<?= $offer['ID'] . '_' . $photo['ID'] ?>">
                                            <img src="<?= $photo['SRC'] ?>">
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>
                                <?
                            }
                        } else {
                            ?>
                            <div class="product-item-detail-slider-controls-block"
                                 id="<?= $itemIds['SLIDER_CONT_ID'] ?>">
                                <?
                                if (!empty($actualItem['MORE_PHOTO'])) {
                                    foreach ($actualItem['MORE_PHOTO'] as $key => $photo) {
                                        ?>
                                        <div
                                            class="product-item-detail-slider-controls-image<?= ($key == 0 ? ' active' : '') ?>"
                                            data-entity="slider-control" data-value="<?= $photo['ID'] ?>">
                                            <img src="<?= $photo['SRC'] ?>">
                                        </div>
                                        <?
                                    }
                                }
                                ?>
                            </div>
                            <?
                        }
                    }
                    ?>
                </div>
            </div>
            <?
            $showOffersBlock = $haveOffers && !empty($arResult['OFFERS_PROP']);
            $mainBlockProperties = array_intersect_key($arResult['DISPLAY_PROPERTIES'], $arParams['MAIN_BLOCK_PROPERTY_CODE']);
            $showPropsBlock = !empty($mainBlockProperties) || $arResult['SHOW_OFFERS_PROPS'];
            $showBlockWithOffersAndProps = $showOffersBlock || $showPropsBlock;
            ?>
            <div class="product-chars-inner">
                <div
                    class="<?= ($showBlockWithOffersAndProps ? "col-md-12 col-lg-12 backgroundGray inner" /*"col-md-5 col-lg-6"*/ : "col-md-4"); ?>">
                    <div class="borderGray<? /*row*/ ?>">
                        <? //добавление сборки в модуль (аксессуар)
                        if (!empty($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]])) { ?>
                            <div
                                class="default_service custom_service_chekbox custom_service_chekbox-detail basket-item-property-custom basket-item-property-custom-text"
                                data-test-product="<?= $arResultBasket['ID'] ?>" data-entity="add_services_custom"
                                data-id="<?= ($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) ? $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] : [] ?>"
                                data-price="<?= (!empty($arResult['SERVICE_MODULE_PRICE']) && (int)$arResult['SERVICE_MODULE_PRICE'] > 0) ? $arResult['SERVICE_MODULE_PRICE'] : "" ?>"
                                data-item_id="<?= ($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) ? $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] : [] ?>"
                                data-item-code="<?= $priceModuleAssembly ?>"
                                data-item="<?= ($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) ? $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] : [] ?>"
                                data-iblockid="<?= $arResult['SERVICE_MODULE_IBLOCK_ID'] ?>" data-quantity="1"
                                style="display:none;">
                                <div class="basket-item-property-custom-name">
                                    Cборка аксессуара + <span
                                        class="module-price-accessory"><?= $arResult['SERVICE_MODULE_PRICE'] ?></span>
                                    <span class="price_currency"> ₽</span>
                                </div>
                                <div class="basket-item-property-custom-value" data-column-property-code="add_services"
                                     data-entity="add_services" data-id="<?= $arItem["ID"] ?>">
                                    <a href="javascript:void(0);" data-entity="id_service"
                                       data-href="<?= ($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) ? $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] : [] ?>"></a>
                                    <div
                                        class="content_wrapper_block services_list services_in_product services_compact">
                                        <div class="services-item__info item_info display">
                                            <div>
                                                <div class="switch_block onoff filter">
                                                    <input type="checkbox"
                                                           name="buy_switch_services" <? if ((int)$arParams['ID_ASSEMBLY_MODULE'] === (int)$arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) { ?> checked="" <? } ?>>
                                                    <label> &nbsp;</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="ugrent_service custom_service_chekbox custom_service_chekbox-detail basket-item-property-custom basket-item-property-custom-text"
                                data-test-product="<?= $arResultBasket['ID'] ?>" data-entity="add_services_custom"
                                data-id="<?= ($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) ? $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] : [] ?>"
                                data-price="<?= (!empty($arResult['SERVICE_MODULE_PRICE_URGENT']) && (int)$arResult['SERVICE_MODULE_PRICE_URGENT'] > 0) ? $arResult['SERVICE_MODULE_PRICE_URGENT'] : "" ?>"
                                data-item_id="<?= ($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) ? $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] : [] ?>"
                                data-item-code="<?= $priceModuleAssembly ?>"
                                data-item="<?= ($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) ? $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] : [] ?>"
                                data-iblockid="<?= $arResult['SERVICE_MODULE_IBLOCK_ID'] ?>" data-quantity="1"
                                style="display:none;">
                                <div class="basket-item-property-custom-name">
                                    Срочная сборка аксессуара + <span
                                        class="module-price-accessory"><?= $arResult['SERVICE_MODULE_PRICE_URGENT'] ?></span>
                                    <span class="price_currency"> ₽</span>
                                </div>
                                <div class="basket-item-property-custom-value" data-column-property-code="add_services"
                                     data-entity="add_services" data-id="<?= $arItem["ID"] ?>">
                                    <a href="javascript:void(0);" data-entity="id_service_ugrebt"
                                       data-href="<?= ($arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) ? $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] : [] ?>"></a>
                                    <div
                                        class="content_wrapper_block services_list services_in_product services_compact">
                                        <div class="services-item__info item_info display">
                                            <div>
                                                <div class="switch_block onoff filter">
                                                    <input type="checkbox"
                                                           name="buy_switch_services" <? if ((int)$arParams['ID_ASSEMBLY_MODULE'] === (int)$arResult["BASKET_SERVICES_VAL"][$arItem["ID"]]) { ?> checked="" <? } ?>>
                                                    <label> &nbsp;</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <? } ?>
                        <?
                        if ($showBlockWithOffersAndProps) {
                            ?>
                            <div class="<?/*col-lg-5*/
                            ?>">

                            <script type="text/javascript">
                                if(js_ACCESSORIES_PARAMS === undefined) {
                                    var js_ACCESSORIES_PARAMS = [];
                                } 

                                js_ACCESSORIES_PARAMS[<?=$arResult['ID']?>] = <?= json_encode($arResult['ACCESSORIES_PARAMS']) ?>
                            </script>
                                <?
                                // dump($arResult['SKU_PROPS']);
                                foreach ($arParams['PRODUCT_INFO_BLOCK_ORDER'] as $blockName) {
                                    switch ($blockName) {
                                        case 'sku':
                                            if ($showOffersBlock) {
                                                ?>
                                                <div class="mb-3" id="<?= $itemIds['TREE_ID'] ?>">
                                                    <?
                                                    foreach ($arResult['SKU_PROPS'] as $skuProperty) {
                                                        if (!isset($arResult['OFFERS_PROP'][$skuProperty['CODE']]))
                                                            continue;

                                                        $exludeFromFilter = false;

                                                        if (!empty($arResult['ACCESSORIES_PARAMS']) && (in_array($skuProperty['CODE'], $arResult['ACCESSORIES_PARAMS']) && $skuProperty['CODE'] !== 'COLOR_REF')):?>
                                                        <!-- <style>
                                                            ul li.product-item-scu-item-text-container[type="<?= mb_strtolower($skuProperty['CODE'])?>"], 
                                                            .product-item-scu-container-title[type="<?= mb_strtolower($skuProperty['CODE'])?>"] {
                                                                display: block;
                                                            }
                                                        </style> -->
                                                        <?php $exludeFromFilter = true;?>
                                                        <?php endif;

                                                        $propertyId = $skuProperty['ID'];
                                                        $skuProps[] = array(
                                                            'ID' => $propertyId,
                                                            'SHOW_MODE' => $skuProperty['SHOW_MODE'],
                                                            'VALUES' => $skuProperty['VALUES'],
                                                            'VALUES_COUNT' => $skuProperty['VALUES_COUNT'],
                                                            'FILTERED' => $exludeFromFilter,
                                                        );
                                                        ?>
												<div data-entity="sku-line-block" class="mb-3">
													<div class="product-item-scu-container-title" type="<?= mb_strtolower($skuProperty['CODE'])?>"><?=htmlspecialcharsEx($skuProperty['NAME'])?></div>
													<div class="product-item-scu-container">
														<div class="product-item-scu-block">
															<div class="product-item-scu-list">
																<ul class="product-item-scu-item-list">
																	<?
																	foreach ($skuProperty['VALUES'] as &$value)
																	{
																		$value['NAME'] = htmlspecialcharsbx($value['NAME']);
																		if ($value['NAME'] === '-') continue;
																		if ($skuProperty['SHOW_MODE'] === 'PICT')
																		{
																			?>
																			<li class="product-item-scu-item-color-container" type="<?= mb_strtolower($skuProperty['CODE'])?>" title="<?=$value['NAME']?>"
																				data-treevalue="<?=$propertyId?>_<?=$value['ID']?>"
																				data-onevalue="<?=$value['ID']?>">
																				<div class="product-item-scu-item-color-block">
																					<div class="product-item-scu-item-color" title="<?=$value['NAME']?>"
																						style="background-image: url('<?=$value['PICT']['SRC']?>');">
																					</div>
																				</div>
																			</li>
																			<?
																		}
																		else
																		{
																			?>
																			<li class="product-item-scu-item-text-container" type="<?= mb_strtolower($skuProperty['CODE'])?>" title="<?=$value['NAME']?>"
																				data-treevalue="<?=$propertyId?>_<?=$value['ID']?>"
																				data-onevalue="<?=$value['ID']?>">
																				<div class="product-item-scu-item-text-block">
																					<div class="product-item-scu-item-text" data-ob="<?=$obName?>" data-price="<?=$price['PRINT_RATIO_PRICE']?>"><?=$value['NAME']?></div>
																				</div>
																			</li>
																			<?
																		}
																	}
																	?>
																</ul>
																<div style="clear: both;"></div>
															</div>
														</div>
													</div>
												</div>
												<?
											}
											?>
										</div>
										<?
									}

                                            break;

                                        case 'props':
                                            if ($showPropsBlock) {
                                                ?>
                                                <div class="mb-3">
                                                    <?
                                                    if (!empty($mainBlockProperties)) {
                                                        ?>
                                                        <ul class="product-item-detail-properties">
                                                            <?
                                                            foreach ($mainBlockProperties as $property) {
                                                                ?>
                                                                <li class="product-item-detail-properties-item">
                                                                    <span
                                                                        class="product-item-detail-properties-name text-muted"><?= $property['NAME'] ?></span>
                                                                    <span
                                                                        class="product-item-detail-properties-dots"></span>
                                                                    <span
                                                                        class="product-item-detail-properties-value"><?= (is_array($property['DISPLAY_VALUE'])
                                                                            ? implode(' / ', $property['DISPLAY_VALUE'])
                                                                            : $property['DISPLAY_VALUE']) ?>
													</span>
                                                                </li>
                                                                <?
                                                            }
                                                            ?>
                                                        </ul>
                                                        <?
                                                    }

                                                    if ($arResult['SHOW_OFFERS_PROPS']) {
                                                        ?>
                                                        <ul class="product-item-detail-properties"
                                                            id="<?= $itemIds['DISPLAY_MAIN_PROP_DIV'] ?>"></ul>
                                                        <?
                                                    }
                                                    ?>
                                                </div>
                                                <?
                                            }

                                            break;
                                    }
                                }
                                ?>
                            </div>
                            <?
                        }
                        ?>
                        <div class="<?= ($showBlockWithOffersAndProps ? "col-lg-7" : "col-lg"); ?>">
                            <div class="product-item-detail-pay-block">
                                <?
                                foreach ($arParams['PRODUCT_PAY_BLOCK_ORDER'] as $blockName) {
                                    switch ($blockName) {
                                        case 'rating':
                                            if ($arParams['USE_VOTE_RATING'] === 'Y') {
                                                ?>
                                                <div class="mb-3">
                                                    <?
                                                    $APPLICATION->IncludeComponent(
                                                        'bitrix:iblock.vote',
                                                        'bootstrap_v4',
                                                        array(
                                                            'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
                                                            'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                                                            'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                                                            'ELEMENT_ID' => $arResult['ID'],
                                                            'ELEMENT_CODE' => '',
                                                            'MAX_VOTE' => '5',
                                                            'VOTE_NAMES' => array('1', '2', '3', '4', '5'),
                                                            'SET_STATUS_404' => 'N',
                                                            'DISPLAY_AS_RATING' => $arParams['VOTE_DISPLAY_AS_RATING'],
                                                            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                                            'CACHE_TIME' => $arParams['CACHE_TIME']
                                                        ),
                                                        $component,
                                                        array('HIDE_ICONS' => 'Y')
                                                    );
                                                    ?>
                                                </div>
                                                <?
                                            }

                                            break;

                                        case 'price':
                                            ?>
                                            <div class="mb-3"
                                                 style="display:none"><?//блок скрыл, чтобы цены переключались, он нужен
                                                ?>
                                                <?
                                                if ($arParams['SHOW_OLD_PRICE'] === 'Y') {
                                                    ?>
                                                    <div class="product-item-detail-price-old mb-1"
                                                         id="<?= $itemIds['OLD_PRICE_ID'] ?>"
                                                        <?= ($showDiscount ? '' : 'style="display: none;"') ?>><?= ($showDiscount ? $price['PRINT_RATIO_BASE_PRICE'] : '') ?></div>
                                                    <?
                                                }
                                                ?>

                                                <div class="product-item-detail-price-current mb-1"
                                                     id="<?= $itemIds['PRICE_ID'] ?>"><?= $price['PRINT_RATIO_PRICE'] ?></div>

                                                <?
                                                if ($arParams['SHOW_OLD_PRICE'] === 'Y') {
                                                    ?>
                                                    <div class="product-item-detail-economy-price mb-1"
                                                         id="<?= $itemIds['DISCOUNT_PRICE_ID'] ?>"
                                                        <?= ($showDiscount ? '' : 'style="display: none;"') ?>><?
                                                        if ($showDiscount) {
                                                            echo Loc::getMessage('CT_BCE_CATALOG_ECONOMY_INFO2', array('#ECONOMY#' => $price['PRINT_RATIO_DISCOUNT']));
                                                        }
                                                        ?></div>
                                                    <?
                                                }
                                                ?>
                                            </div>
                                            <?
                                            break;

                                        case 'priceRanges':
                                            if ($arParams['USE_PRICE_COUNT']) {
                                                $showRanges = !$haveOffers && count($actualItem['ITEM_QUANTITY_RANGES']) > 1;
                                                $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';
                                                ?>
                                                <div class="mb-3"
                                                    <?= $showRanges ? '' : 'style="display: none;"' ?>
                                                     data-entity="price-ranges-block">
                                                    <?
                                                    if ($arParams['MESS_PRICE_RANGES_TITLE']) {
                                                        ?>
                                                        <div
                                                            class="product-item-detail-info-container-title text-center">
                                                            <?= $arParams['MESS_PRICE_RANGES_TITLE'] ?>
                                                            <span data-entity="price-ranges-ratio-header">
												(<?= (Loc::getMessage(
                                                                    'CT_BCE_CATALOG_RATIO_PRICE',
                                                                    array('#RATIO#' => ($useRatio ? $measureRatio : '1') . ' ' . $actualItem['ITEM_MEASURE']['TITLE'])
                                                                )) ?>)
											</span>
                                                        </div>
                                                        <?
                                                    }
                                                    ?>
                                                    <ul class="product-item-detail-properties"
                                                        data-entity="price-ranges-body">
                                                        <?
                                                        if ($showRanges) {
                                                            foreach ($actualItem['ITEM_QUANTITY_RANGES'] as $range) {
                                                                if ($range['HASH'] !== 'ZERO-INF') {
                                                                    $itemPrice = false;

                                                                    foreach ($arResult['ITEM_PRICES'] as $itemPrice) {
                                                                        if ($itemPrice['QUANTITY_HASH'] === $range['HASH']) {
                                                                            break;
                                                                        }
                                                                    }

                                                                    if ($itemPrice) {
                                                                        ?>
                                                                        <li class="product-item-detail-properties-item">
																<span
                                                                    class="product-item-detail-properties-name text-muted">
																	<?
                                                                    echo Loc::getMessage(
                                                                            'CT_BCE_CATALOG_RANGE_FROM',
                                                                            array('#FROM#' => $range['SORT_FROM'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE'])
                                                                        ) . ' ';

                                                                    if (is_infinite($range['SORT_TO'])) {
                                                                        echo Loc::getMessage('CT_BCE_CATALOG_RANGE_MORE');
                                                                    } else {
                                                                        echo Loc::getMessage(
                                                                            'CT_BCE_CATALOG_RANGE_TO',
                                                                            array('#TO#' => $range['SORT_TO'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE'])
                                                                        );
                                                                    }
                                                                    ?>
																</span>
                                                                            <span
                                                                                class="product-item-detail-properties-dots"></span>
                                                                            <span
                                                                                class="product-item-detail-properties-value"><?= ($useRatio ? $itemPrice['PRINT_RATIO_PRICE'] : $itemPrice['PRINT_PRICE']) ?></span>
                                                                        </li>
                                                                        <?
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                                <?
                                                unset($showRanges, $useRatio, $itemPrice, $range);
                                            }

                                            break;

                                        case 'quantityLimit':
                                            if ($arParams['SHOW_MAX_QUANTITY'] !== 'N') {
                                                if ($haveOffers) {
                                                    ?>
                                                    <div class="mb-3" id="<?= $itemIds['QUANTITY_LIMIT'] ?>"
                                                         style="display: none;">
                                                        <div
                                                            class="product-item-detail-info-container-title text-center">
                                                            <?= $arParams['MESS_SHOW_MAX_QUANTITY'] ?>:
                                                        </div>
                                                        <span class="product-item-quantity"
                                                              data-entity="quantity-limit-value"></span>
                                                    </div>
                                                    <?
                                                } else {
                                                    if (
                                                        $measureRatio
                                                        && (float)$actualItem['PRODUCT']['QUANTITY'] > 0
                                                        && $actualItem['CHECK_QUANTITY']
                                                    ) {
                                                        ?>
                                                        <div class="mb-3 text-center"
                                                             id="<?= $itemIds['QUANTITY_LIMIT'] ?>">
                                                            <span
                                                                class="product-item-detail-info-container-title"><?= $arParams['MESS_SHOW_MAX_QUANTITY'] ?>:</span>
                                                            <span class="product-item-quantity"
                                                                  data-entity="quantity-limit-value">
													<?
                                                    if ($arParams['SHOW_MAX_QUANTITY'] === 'M') {
                                                        if ((float)$actualItem['PRODUCT']['QUANTITY'] / $measureRatio >= $arParams['RELATIVE_QUANTITY_FACTOR']) {
                                                            echo $arParams['MESS_RELATIVE_QUANTITY_MANY'];
                                                        } else {
                                                            echo $arParams['MESS_RELATIVE_QUANTITY_FEW'];
                                                        }
                                                    } else {
                                                        echo $actualItem['PRODUCT']['QUANTITY'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE'];
                                                    }
                                                    ?>
												</span>
                                                        </div>
                                                        <?
                                                    }
                                                }
                                            }

                                            break;

                                        case 'quantity':
                                            if ($arParams['USE_PRODUCT_QUANTITY']) {
                                                ?>
                                                <div
                                                    class="mb-3" <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                                                    data-entity="quantity-block">
                                                    <?
                                                    if (Loc::getMessage('CATALOG_QUANTITY')) {
                                                        ?>
                                                        <div
                                                            class="product-item-detail-info-container-title text-center"><?= Loc::getMessage('CATALOG_QUANTITY') ?></div>
                                                        <?
                                                    }
                                                    ?>

                                                    <div class="product-item-amount">
                                                        <div class="product-item-amount-field-container">
                                                            <span class="product-item-amount-field-btn-minus no-select"
                                                                  id="<?= $itemIds['QUANTITY_DOWN_ID'] ?>"></span>
                                                            <div class="product-item-amount-field-block">
                                                                <input class="product-item-amount-field"
                                                                       id="<?= $itemIds['QUANTITY_ID'] ?>" type="number"
                                                                       value="<?= $price['MIN_QUANTITY'] ?>">
                                                                <span class="product-item-amount-description-container">
														<span
                                                            id="<?= $itemIds['QUANTITY_MEASURE'] ?>"><?= $actualItem['ITEM_MEASURE']['TITLE'] ?></span>
														<span id="<?= $itemIds['PRICE_TOTAL'] ?>"></span>
													</span>
                                                            </div>
                                                            <span class="product-item-amount-field-btn-plus no-select"
                                                                  id="<?= $itemIds['QUANTITY_UP_ID'] ?>"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?
                                            }

                                            break;

                                        case 'buttons':
                                            ?>
                                            <div data-entity="main-button-container" class="mb-3"
                                                 style="display:none"><?//кнопка работает, просто она не нужна, скрыл
                                                ?>
                                                <div id="<?= $itemIds['BASKET_ACTIONS_ID'] ?>"
                                                     style="display: <?= ($actualItem['CAN_BUY'] ? '' : 'none') ?>;">
                                                    <?
                                                    if ($showAddBtn) {
                                                        ?>
                                                        <div class="mb-3">
                                                            <a class="btn <?= $showButtonClassName ?> product-item-detail-buy-button"
                                                               id="<?= $itemIds['ADD_BASKET_LINK'] ?>"
                                                               href="javascript:void(0);">
                                                                <?= $arParams['MESS_BTN_ADD_TO_BASKET'] ?>
                                                            </a>
                                                        </div>
                                                        <?
                                                    }

                                                    if ($showBuyBtn) {
                                                        ?>
                                                        <div class="mb-3">
                                                            <a class="btn <?= $buyButtonClassName ?> product-item-detail-buy-button"
                                                               id="<?= $itemIds['BUY_LINK'] ?>"
                                                               href="javascript:void(0);">
                                                                <?= $arParams['MESS_BTN_BUY'] ?>
                                                            </a>
                                                        </div>
                                                        <?
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <?
                                            if ($showSubscribe) {
                                                ?>
                                                <div class="mb-3">
                                                    <?
                                                    $APPLICATION->IncludeComponent(
                                                        'bitrix:catalog.product.subscribe',
                                                        '',
                                                        array(
                                                            'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
                                                            'PRODUCT_ID' => $arResult['ID'],
                                                            'BUTTON_ID' => $itemIds['SUBSCRIBE_LINK'],
                                                            'BUTTON_CLASS' => 'btn u-btn-outline-primary product-item-detail-buy-button',
                                                            'DEFAULT_DISPLAY' => !$actualItem['CAN_BUY'],
                                                            'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
                                                        ),
                                                        $component,
                                                        array('HIDE_ICONS' => 'Y')
                                                    );
                                                    ?>
                                                </div>
                                                <?
                                            }
                                            ?>
                                            <div class="mb-3" id="<?= $itemIds['NOT_AVAILABLE_MESS'] ?>"
                                                 style="display: <?= (!$actualItem['CAN_BUY'] ? '' : 'none') ?>;">
                                                <a class="btn btn-primary product-item-detail-buy-button"
                                                   href="javascript:void(0)"
                                                   rel="nofollow"><?= $arParams['MESS_NOT_AVAILABLE'] ?></a>
                                            </div>
                                            <?
                                            break;
                                    }
                                }

                                if ($arParams['DISPLAY_COMPARE']) {
                                    ?>
                                    <div class="product-item-detail-compare-container">
                                        <div class="product-item-detail-compare">
                                            <div class="checkbox">
                                                <label class="m-0" id="<?= $itemIds['COMPARE_LINK'] ?>">
                                                    <input type="checkbox" data-entity="compare-checkbox">
                                                    <span
                                                        data-entity="compare-title"><?= $arParams['MESS_BTN_COMPARE'] ?></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?
        if ($haveOffers) {
            if ($arResult['OFFER_GROUP']) {
                ?>
                <div class="row">
                    <div class="col">
                        <?
                        foreach ($arResult['OFFER_GROUP_VALUES'] as $offerId) {
                            ?>
                            <span id="<?= $itemIds['OFFER_GROUP'] . $offerId ?>" style="display: none;">
							<?
                            $APPLICATION->IncludeComponent(
                                'bitrix:catalog.set.constructor',
                                'bootstrap_v4',
                                array(
                                    'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
                                    'IBLOCK_ID' => $arResult['OFFERS_IBLOCK'],
                                    'ELEMENT_ID' => $offerId,
                                    'PRICE_CODE' => $arParams['PRICE_CODE'],
                                    'BASKET_URL' => $arParams['BASKET_URL'],
                                    'OFFERS_CART_PROPERTIES' => $arParams['OFFERS_CART_PROPERTIES'],
                                    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                    'CACHE_TIME' => $arParams['CACHE_TIME'],
                                    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                    'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
                                    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                    'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                                    'DETAIL_URL' => $arParams['~DETAIL_URL']
                                ),
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            );
                            ?>
						</span>
                            <?
                        }
                        ?>
                    </div>
                </div>
                <?
            }
        } else {
            if ($arResult['MODULES']['catalog'] && $arResult['OFFER_GROUP']) {
                ?>
                <div class="row">
                    <div class="col">
                        <? $APPLICATION->IncludeComponent(
                            'bitrix:catalog.set.constructor',
                            'bootstrap_v4',
                            array(
                                'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
                                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                                'ELEMENT_ID' => $arResult['ID'],
                                'PRICE_CODE' => $arParams['PRICE_CODE'],
                                'BASKET_URL' => $arParams['BASKET_URL'],
                                'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                'CACHE_TIME' => $arParams['CACHE_TIME'],
                                'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
                                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                'CURRENCY_ID' => $arParams['CURRENCY_ID']
                            ),
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        );
                        ?>
                    </div>
                </div>
                <?
            }
        }
        ?>

        <!--Small Card-->
        <div class="p-2 product-item-detail-short-card-fixed d-none d-md-block"
             id="<?= $itemIds['SMALL_CARD_PANEL_ID'] ?>">
            <div class="product-item-detail-short-card-content-container">
                <div class="product-item-detail-short-card-image">
                    <img src="" style="height: 65px;" data-entity="panel-picture">
                </div>
                <div class="product-item-detail-short-title-container" data-entity="panel-title">
                    <div class="product-item-detail-short-title-text"><?= $name ?></div>
                    <?
                    if ($haveOffers) {
                        ?>
                        <div>
                            <div class="product-item-selected-scu-container" data-entity="panel-sku-container">
                                <?
                                $i = 0;

                                foreach ($arResult['SKU_PROPS'] as $skuProperty) {
                                    if (!isset($arResult['OFFERS_PROP'][$skuProperty['CODE']])) {
                                        continue;
                                    }

                                    $propertyId = $skuProperty['ID'];

                                    foreach ($skuProperty['VALUES'] as $value) {
                                        $value['NAME'] = htmlspecialcharsbx($value['NAME']);
                                        if ($skuProperty['SHOW_MODE'] === 'PICT') {
                                            ?>
                                            <div
                                                class="product-item-selected-scu product-item-selected-scu-color selected"
                                                title="<?= $value['NAME'] ?>"
                                                style="background-image: url('<?= $value['PICT']['SRC'] ?>'); display: none;"
                                                data-sku-line="<?= $i ?>"
                                                data-treevalue="<?= $propertyId ?>_<?= $value['ID'] ?>"
                                                data-onevalue="<?= $value['ID'] ?>">
                                            </div>
                                            <?
                                        } else {
                                            ?>
                                            <div
                                                class="product-item-selected-scu product-item-selected-scu-text selected"
                                                title="<?= $value['NAME'] ?>"
                                                style="display: none;"
                                                data-sku-line="<?= $i ?>"
                                                data-treevalue="<?= $propertyId ?>_<?= $value['ID'] ?>"
                                                data-onevalue="<?= $value['ID'] ?>">
                                                <?= $value['NAME'] ?>
                                            </div>
                                            <?
                                        }
                                    }

                                    $i++;
                                }
                                ?>
                            </div>
                        </div>
                        <?
                    }
                    ?>

                </div>
                <div class="product-item-detail-short-card-price">
                    <?
                    if ($arParams['SHOW_OLD_PRICE'] === 'Y') {
                        ?>
                        <div class="product-item-detail-price-old"
                             style="display: <?= ($showDiscount ? '' : 'none') ?>;" data-entity="panel-old-price">
                            <?= ($showDiscount ? $price['PRINT_RATIO_BASE_PRICE'] : '') ?>
                        </div>
                        <?
                    }
                    ?>
                    <div class="product-item-detail-price-current"
                         data-entity="panel-price"><?= $price['PRINT_RATIO_PRICE'] ?></div>
                </div>
                <?
                if ($showAddBtn) {
                    ?>
                    <div class="product-item-detail-short-card-btn"
                         style="display: <?= ($actualItem['CAN_BUY'] ? '' : 'none') ?>;"
                         data-entity="panel-add-button">
                        <a class="btn <?= $showButtonClassName ?> product-item-detail-buy-button"
                           id="<?= $itemIds['ADD_BASKET_LINK'] ?>"
                           href="javascript:void(0);">
                            <?= $arParams['MESS_BTN_ADD_TO_BASKET'] ?>
                        </a>
                    </div>
                    <?
                }

                if ($showBuyBtn) {
                    ?>
                    <div class="product-item-detail-short-card-btn"
                         style="display: <?= ($actualItem['CAN_BUY'] ? '' : 'none') ?>;"
                         data-entity="panel-buy-button">
                        <a class="btn <?= $buyButtonClassName ?> product-item-detail-buy-button"
                           id="<?= $itemIds['BUY_LINK'] ?>"
                           href="javascript:void(0);">
                            <?= $arParams['MESS_BTN_BUY'] ?>
                        </a>
                    </div>
                    <?
                }
                ?>
                <div class="product-item-detail-short-card-btn"
                     style="display: <?= (!$actualItem['CAN_BUY'] ? '' : 'none') ?>;"
                     data-entity="panel-not-available-button">
                    <a class="btn btn-link product-item-detail-buy-button" href="javascript:void(0)"
                       rel="nofollow">
                        <?= $arParams['MESS_NOT_AVAILABLE'] ?>
                    </a>
                </div>
            </div>
        </div>
        <!--Top tabs-->
        <div class="pt-2 pb-0 product-item-detail-tabs-container-fixed d-none d-md-block"
             id="<?= $itemIds['TABS_PANEL_ID'] ?>">
            <ul class="product-item-detail-tabs-list">
                <?
                if ($showDescription) {
                    ?>
                    <li class="product-item-detail-tab active" data-entity="tab" data-value="description">
                        <a href="javascript:void(0);" class="product-item-detail-tab-link">
                            <span><?= $arParams['MESS_DESCRIPTION_TAB'] ?></span>
                        </a>
                    </li>
                    <?
                }

                if (!empty($arResult['DISPLAY_PROPERTIES']) || $arResult['SHOW_OFFERS_PROPS']) {
                    ?>
                    <li class="product-item-detail-tab" data-entity="tab" data-value="properties">
                        <a href="javascript:void(0);" class="product-item-detail-tab-link">
                            <span><?= $arParams['MESS_PROPERTIES_TAB'] ?></span>
                        </a>
                    </li>
                    <?
                }

                if ($arParams['USE_COMMENTS'] === 'Y') {
                    ?>
                    <li class="product-item-detail-tab" data-entity="tab" data-value="comments">
                        <a href="javascript:void(0);" class="product-item-detail-tab-link">
                            <span><?= $arParams['MESS_COMMENTS_TAB'] ?></span>
                        </a>
                    </li>
                    <?
                }
                ?>
            </ul>
        </div>

        <meta itemprop="name" content="<?= $name ?>"/>
        <meta itemprop="category" content="<?= $arResult['CATEGORY_PATH'] ?>"/>
        
        <?
        if ($haveOffers) {
            $offerIds = array();
            $offerCodes = array();

            $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';

            foreach ($arResult['JS_OFFERS'] as $ind => &$jsOffer) {
                $offerIds[] = (int)$jsOffer['ID'];
                $offerCodes[] = $jsOffer['CODE'];

                $fullOffer = $arResult['OFFERS'][$ind];
                $measureName = $fullOffer['ITEM_MEASURE']['TITLE'];

                $strAllProps = '';
                $strMainProps = '';
                $strPriceRangesRatio = '';
                $strPriceRanges = '';

                if ($arResult['SHOW_OFFERS_PROPS']) {
                    if (!empty($jsOffer['DISPLAY_PROPERTIES'])) {
                        foreach ($jsOffer['DISPLAY_PROPERTIES'] as $property) {
                            $current = '<li class="product-item-detail-properties-item">
					<span class="product-item-detail-properties-name">' . $property['NAME'] . '</span>
					<span class="product-item-detail-properties-dots"></span>
					<span class="product-item-detail-properties-value">' . (
                                is_array($property['VALUE'])
                                    ? implode(' / ', $property['VALUE'])
                                    : $property['VALUE']
                                ) . '</span></li>';
                            $strAllProps .= $current;

                            if (isset($arParams['MAIN_BLOCK_OFFERS_PROPERTY_CODE'][$property['CODE']])) {
                                $strMainProps .= $current;
                            }
                        }

                        unset($current);
                    }
                }

                if ($arParams['USE_PRICE_COUNT'] && count($jsOffer['ITEM_QUANTITY_RANGES']) > 1) {
                    $strPriceRangesRatio = '(' . Loc::getMessage(
                            'CT_BCE_CATALOG_RATIO_PRICE',
                            array('#RATIO#' => ($useRatio
                                    ? $fullOffer['ITEM_MEASURE_RATIOS'][$fullOffer['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']
                                    : '1'
                                ) . ' ' . $measureName)
                        ) . ')';

                    foreach ($jsOffer['ITEM_QUANTITY_RANGES'] as $range) {
                        if ($range['HASH'] !== 'ZERO-INF') {
                            $itemPrice = false;

                            foreach ($jsOffer['ITEM_PRICES'] as $itemPrice) {
                                if ($itemPrice['QUANTITY_HASH'] === $range['HASH']) {
                                    break;
                                }
                            }

                            if ($itemPrice) {
                                $strPriceRanges .= '<dt>' . Loc::getMessage(
                                        'CT_BCE_CATALOG_RANGE_FROM',
                                        array('#FROM#' => $range['SORT_FROM'] . ' ' . $measureName)
                                    ) . ' ';

                                if (is_infinite($range['SORT_TO'])) {
                                    $strPriceRanges .= Loc::getMessage('CT_BCE_CATALOG_RANGE_MORE');
                                } else {
                                    $strPriceRanges .= Loc::getMessage(
                                        'CT_BCE_CATALOG_RANGE_TO',
                                        array('#TO#' => $range['SORT_TO'] . ' ' . $measureName)
                                    );
                                }

                                $strPriceRanges .= '</dt><dd>' . ($useRatio ? $itemPrice['PRINT_RATIO_PRICE'] : $itemPrice['PRINT_PRICE']) . '</dd>';
                            }
                        }
                    }

                    unset($range, $itemPrice);
                }

                $jsOffer['DISPLAY_PROPERTIES'] = $strAllProps;
                $jsOffer['DISPLAY_PROPERTIES_MAIN_BLOCK'] = $strMainProps;
                $jsOffer['PRICE_RANGES_RATIO_HTML'] = $strPriceRangesRatio;
                $jsOffer['PRICE_RANGES_HTML'] = $strPriceRanges;
            }

            $templateData['OFFER_IDS'] = $offerIds;
            $templateData['OFFER_CODES'] = $offerCodes;
            unset($jsOffer, $strAllProps, $strMainProps, $strPriceRanges, $strPriceRangesRatio, $useRatio);

            $jsParams = array(
                'CONFIG' => array(
                    'USE_CATALOG' => $arResult['CATALOG'],
                    'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
                    'SHOW_PRICE' => true,
                    'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
                    'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
                    'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                    'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
                    'SHOW_SKU_PROPS' => $arResult['SHOW_OFFERS_PROPS'],
                    'OFFER_GROUP' => $arResult['OFFER_GROUP'],
                    'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
                    'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
                    'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
                    'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
                    'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
                    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
                    'USE_STICKERS' => true,
                    'USE_SUBSCRIBE' => $showSubscribe,
                    'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
                    'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
                    'ALT' => $alt,
                    'TITLE' => $title,
                    'MAGNIFIER_ZOOM_PERCENT' => 200,
                    'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
                    'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
                    'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                        ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                        : null,
                    'SHOW_SKU_DESCRIPTION' => $arParams['SHOW_SKU_DESCRIPTION'],
                    'DISPLAY_PREVIEW_TEXT_MODE' => $arParams['DISPLAY_PREVIEW_TEXT_MODE']
                ),
                'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
                'VISUAL' => $itemIds,
                'DEFAULT_PICTURE' => array(
                    'PREVIEW_PICTURE' => $arResult['DEFAULT_PICTURE'],
                    'DETAIL_PICTURE' => $arResult['DEFAULT_PICTURE']
                ),
                'PRODUCT' => array(
                    'ID' => $arResult['ID'],
                    'ACTIVE' => $arResult['ACTIVE'],
                    'NAME' => $arResult['~NAME'],
                    'CATEGORY' => $arResult['CATEGORY_PATH'],
                    'DETAIL_TEXT' => $arResult['DETAIL_TEXT'],
                    'DETAIL_TEXT_TYPE' => $arResult['DETAIL_TEXT_TYPE'],
                    'PREVIEW_TEXT' => $arResult['PREVIEW_TEXT'],
                    'PREVIEW_TEXT_TYPE' => $arResult['PREVIEW_TEXT_TYPE']
                ),
                'BASKET' => array(
                    'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
                    'BASKET_URL' => $arParams['BASKET_URL'],
                    'SKU_PROPS' => $arResult['OFFERS_PROP_CODES'],
                    'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
                    'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
                ),
                'OFFERS' => $arResult['JS_OFFERS'],
                'OFFER_SELECTED' => $arResult['OFFERS_SELECTED'],
                'TREE_PROPS' => $skuProps
            );
        } else {
            $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
            if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !$emptyProductProperties) {
                ?>
                <div id="<?= $itemIds['BASKET_PROP_DIV'] ?>" style="display: none;">
                    <?
                    if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])) {
                        foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propId => $propInfo) {
                            ?>
                            <input type="hidden" name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]"
                                   value="<?= htmlspecialcharsbx($propInfo['ID']) ?>">
                            <?
                            unset($arResult['PRODUCT_PROPERTIES'][$propId]);
                        }
                    }

                    $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
                    if (!$emptyProductProperties) {
                        ?>
                        <table>
                            <?
                            foreach ($arResult['PRODUCT_PROPERTIES'] as $propId => $propInfo) {
                                ?>
                                <tr>
                                    <td><?= $arResult['PROPERTIES'][$propId]['NAME'] ?></td>
                                    <td>
                                        <?
                                        if (
                                            $arResult['PROPERTIES'][$propId]['PROPERTY_TYPE'] === 'L'
                                            && $arResult['PROPERTIES'][$propId]['LIST_TYPE'] === 'C'
                                        ) {
                                            foreach ($propInfo['VALUES'] as $valueId => $value) {
                                                ?>
                                                <label>
                                                    <input type="radio"
                                                           name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]"
                                                           value="<?= $valueId ?>" <?= ($valueId == $propInfo['SELECTED'] ? '"checked"' : '') ?>>
                                                    <?= $value ?>
                                                </label>
                                                <br>
                                                <?
                                            }
                                        } else {
                                            ?>
                                            <select name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]">
                                                <?
                                                foreach ($propInfo['VALUES'] as $valueId => $value) {
                                                    ?>
                                                    <option
                                                        value="<?= $valueId ?>" <?= ($valueId == $propInfo['SELECTED'] ? '"selected"' : '') ?>>
                                                        <?= $value ?>
                                                    </option>
                                                    <?
                                                }
                                                ?>
                                            </select>
                                            <?
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?
                            }
                            ?>
                        </table>
                        <?
                    }
                    ?>
                </div>
                <?
            }

            $jsParams = array(
                'CONFIG' => array(
                    'USE_CATALOG' => $arResult['CATALOG'],
                    'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
                    'SHOW_PRICE' => !empty($arResult['ITEM_PRICES']),
                    'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
                    'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
                    'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                    'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
                    'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
                    'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
                    'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
                    'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
                    'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
                    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
                    'USE_STICKERS' => true,
                    'USE_SUBSCRIBE' => $showSubscribe,
                    'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
                    'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
                    'ALT' => $alt,
                    'TITLE' => $title,
                    'MAGNIFIER_ZOOM_PERCENT' => 200,
                    'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
                    'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
                    'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                        ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                        : null
                ),
                'VISUAL' => $itemIds,
                'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
                'PRODUCT' => array(
                    'ID' => $arResult['ID'],
                    'ACTIVE' => $arResult['ACTIVE'],
                    'PICT' => reset($arResult['MORE_PHOTO']),
                    'NAME' => $arResult['~NAME'],
                    'SUBSCRIPTION' => true,
                    'ITEM_PRICE_MODE' => $arResult['ITEM_PRICE_MODE'],
                    'ITEM_PRICES' => $arResult['ITEM_PRICES'],
                    'ITEM_PRICE_SELECTED' => $arResult['ITEM_PRICE_SELECTED'],
                    'ITEM_QUANTITY_RANGES' => $arResult['ITEM_QUANTITY_RANGES'],
                    'ITEM_QUANTITY_RANGE_SELECTED' => $arResult['ITEM_QUANTITY_RANGE_SELECTED'],
                    'ITEM_MEASURE_RATIOS' => $arResult['ITEM_MEASURE_RATIOS'],
                    'ITEM_MEASURE_RATIO_SELECTED' => $arResult['ITEM_MEASURE_RATIO_SELECTED'],
                    'SLIDER_COUNT' => $arResult['MORE_PHOTO_COUNT'],
                    'SLIDER' => $arResult['MORE_PHOTO'],
                    'CAN_BUY' => $arResult['CAN_BUY'],
                    'CHECK_QUANTITY' => $arResult['CHECK_QUANTITY'],
                    'QUANTITY_FLOAT' => is_float($arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
                    'MAX_QUANTITY' => $arResult['PRODUCT']['QUANTITY'],
                    'STEP_QUANTITY' => $arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
                    'CATEGORY' => $arResult['CATEGORY_PATH']
                ),
                'BASKET' => array(
                    'ADD_PROPS' => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
                    'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
                    'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
                    'EMPTY_PROPS' => $emptyProductProperties,
                    'BASKET_URL' => $arParams['BASKET_URL'],
                    'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
                    'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
                )
            );
            unset($emptyProductProperties);
        }

        if ($arParams['DISPLAY_COMPARE']) {
            $jsParams['COMPARE'] = array(
                'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
                'COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
                'COMPARE_PATH' => $arParams['COMPARE_PATH']
            );
        }
        ?>
    </div>

<? //закрытие тегов?>
<? /*</div>
</div>
</div>*/ ?>
    <script>
        BX.message({
            ECONOMY_INFO_MESSAGE: '<?=GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2')?>',
            TITLE_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR')?>',
            TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS')?>',
            BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR')?>',
            BTN_SEND_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS')?>',
            BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
            BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE')?>',
            BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
            TITLE_SUCCESSFUL: '<?=GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK')?>',
            COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK')?>',
            COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
            COMPARE_TITLE: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE')?>',
            BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
            PRODUCT_GIFT_LABEL: '<?=GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL')?>',
            PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX')?>',
            RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
            RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
            SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
        });

        (function (window) {
            'use strict';
            if (window.JCCatalogElementEquipment_<?=$arParams['ELEMENT_ID']?>)
                return;

            var BasketButton = function (params) {
                BasketButton.superclass.constructor.apply(this, arguments);
                this.buttonNode = BX.create('SPAN', {
                    props: {className: 'btn btn-primary btn-buy btn-sm', id: this.id},
                    style: typeof params.style === 'object' ? params.style : {},
                    text: params.text,
                    events: this.contextEvents
                });

                if (BX.browser.IsIE()) {
                    this.buttonNode.setAttribute('hideFocus', 'hidefocus');
                }
            };
            BX.extend(BasketButton, BX.PopupWindowButton);

            window.JCCatalogElementEquipment_<?=$arParams['ELEMENT_ID']?> = function (arParams) {
                this.productType = 0;

                this.config = {
                    useCatalog: true,
                    showQuantity: true,
                    showPrice: true,
                    showAbsent: true,
                    showOldPrice: false,
                    showPercent: false,
                    showSkuProps: false,
                    showOfferGroup: false,
                    useCompare: false,
                    useStickers: false,
                    useSubscribe: false,
                    usePopup: false,
                    useMagnifier: false,
                    usePriceRanges: false,
                    basketAction: ['BUY'],
                    showClosePopup: false,
                    templateTheme: '',
                    showSlider: false,
                    sliderInterval: 5000,
                    useEnhancedEcommerce: false,
                    dataLayerName: 'dataLayer',
                    brandProperty: false,
                    alt: '',
                    title: '',
                    productID: <?=$arParams['ELEMENT_ID']?>,
                    magnifierZoomPercent: 200
                };

                this.checkQuantity = false;
                this.maxQuantity = 0;
                this.minQuantity = 0;
                this.stepQuantity = 1;
                this.isDblQuantity = false;
                this.canBuy = true;
                this.isGift = false;
                this.canSubscription = true;
                this.currentIsSet = false;
                this.updateViewedCount = false;

                this.currentPriceMode = '';
                this.currentPrices = [];
                this.currentPriceSelected = 0;
                this.currentQuantityRanges = [];
                this.currentQuantityRangeSelected = 0;

                this.precision = 6;
                this.precisionFactor = Math.pow(10, this.precision);

                this.visual = {};
                this.basketMode = '';
                this.product = {
                    checkQuantity: false,
                    maxQuantity: 0,
                    stepQuantity: 1,
                    startQuantity: 1,
                    isDblQuantity: false,
                    canBuy: true,
                    canSubscription: true,
                    name: '',
                    pict: {},
                    id: 0,
                    addUrl: '',
                    buyUrl: '',
                    slider: {},
                    sliderCount: 0,
                    useSlider: false,
                    sliderPict: []
                };
                this.mess = {};

                this.basketData = {
                    useProps: false,
                    emptyProps: false,
                    quantity: 'quantity',
                    props: 'prop',
                    basketUrl: '',
                    sku_props: '',
                    sku_props_var: 'basket_props',
                    add_url: '',
                    buy_url: ''
                };
                this.compareData = {
                    compareUrl: '',
                    compareDeleteUrl: '',
                    comparePath: ''
                };

                this.defaultPict = {
                    preview: null,
                    detail: null
                };

                this.offers = [];
                this.offerNum = 0;
                this.treeProps = [];
                this.selectedValues = {};

                this.mouseTimer = null;
                this.isTouchDevice = BX.hasClass(document.documentElement, 'bx-touch');
                this.touch = null;
                this.slider = {
                    interval: null,
                    progress: null,
                    paused: null,
                    controls: []
                };

                this.quantityDelay = null;
                this.quantityTimer = null;

                this.obProduct = null;
                this.obQuantity = null;
                this.obQuantityUp = null;
                this.obQuantityDown = null;
                this.obPrice = {
                    price: null,
                    full: null,
                    discount: null,
                    percent: null,
                    total: null
                };
                this.obTree = null;
                this.obPriceRanges = null;
                this.obBuyBtn = null;
                this.obAddToBasketBtn = null;
                this.obBasketActions = null;
                this.obNotAvail = null;
                this.obSubscribe = null;
                this.obSkuProps = null;
                this.obDescription = null;
                this.obMainSkuProps = null;
                this.obBigSlider = null;
                this.obMeasure = null;
                this.obQuantityLimit = {
                    all: null,
                    value: null
                };
                this.obCompare = null;
                this.obTabsPanel = null;

                this.node = {};
                // top panel small card
                this.smallCardNodes = {};

                this.magnify = {
                    enabled: false,
                    obBigImg: null,
                    obBigSlider: null,
                    height: 0,
                    width: 0,
                    timer: 0
                };
                this.currentImg = {
                    id: 0,
                    src: '',
                    width: 0,
                    height: 0
                };
                this.viewedCounter = {
                    path: '/bitrix/components/bitrix/catalog.element/ajax.php',
                    params: {
                        AJAX: 'Y',
                        SITE_ID: '',
                        PRODUCT_ID: 0,
                        PARENT_ID: 0
                    }
                };

                this.obPopupWin = null;
                this.basketUrl = '';
                this.basketParams = {};

                this.errorCode = 0;

                if (typeof arParams === 'object') {
                    this.params = arParams;
                    this.initConfig();

                    if (this.params.MESS) {
                        this.mess = this.params.MESS;
                    }

                    switch (this.productType) {
                        case 0: // no catalog
                        case 1: // product
                        case 2: // set
                            this.initProductData();
                            break;
                        case 3: // sku
                            this.initOffersData();
                            break;
                        default:
                            this.errorCode = -1;
                    }

                    this.initBasketData();
                    this.initCompareData();
                }

                if (this.errorCode === 0) {
                    BX.ready(BX.delegate(this.init, this));
                }

                this.params = {};

                BX.addCustomEvent('onSaleProductIsGift', BX.delegate(this.onSaleProductIsGift, this));
                BX.addCustomEvent('onSaleProductIsNotGift', BX.delegate(this.onSaleProductIsNotGift, this));
            };

            window.JCCatalogElementEquipment_<?=$arParams['ELEMENT_ID']?>.prototype = {
                getEntity: function (parent, entity, additionalFilter) {
                    if (!parent || !entity)
                        return null;

                    additionalFilter = additionalFilter || '';

                    return parent.querySelector(additionalFilter + '[data-entity="' + entity + '"]');
                },

                getEntities: function (parent, entity, additionalFilter) {
                    if (!parent || !entity)
                        return {length: 0};

                    additionalFilter = additionalFilter || '';

                    return parent.querySelectorAll(additionalFilter + '[data-entity="' + entity + '"]');
                },

                onSaleProductIsGift: function (productId, offerId) {
                    if (offerId && this.offers && this.offers[this.offerNum].ID == offerId) {
                        this.setGift();
                    }
                },

                onSaleProductIsNotGift: function (productId, offerId) {
                    if (offerId && this.offers && this.offers[this.offerNum].ID == offerId) {
                        this.restoreSticker();
                        this.isGift = false;
                        this.setPrice();
                    }
                },

                reloadGiftInfo: function () {
                    if (this.productType === 3) {
                        this.checkQuantity = true;
                        this.maxQuantity = 1;

                        this.setPrice();
                        this.redrawSticker({text: BX.message('PRODUCT_GIFT_LABEL')});
                    }
                },

                setGift: function () {
                    if (this.productType === 3) {
                        // sku
                        this.isGift = true;
                    }

                    if (this.productType === 1 || this.productType === 2) {
                        // simple
                        this.isGift = true;
                    }

                    if (this.productType === 0) {
                        this.isGift = false;
                    }

                    this.reloadGiftInfo();
                },

                setOffer: function (offerNum) {
                    this.offerNum = parseInt(offerNum);
                    this.setCurrent();
                },

                init: function () {
                    var i = 0,
                        j = 0,
                        treeItems = null;

                    this.obProduct = BX(this.visual.ID);
                    if (!this.obProduct) {
                        this.errorCode = -1;
                    }

                    this.obBigSlider = BX(this.visual.BIG_SLIDER_ID);
                    this.node.imageContainer = this.getEntity(this.obProduct, 'images-container');
                    this.node.imageSliderBlock = this.getEntity(this.obProduct, 'images-slider-block');
                    this.node.sliderProgressBar = this.getEntity(this.obProduct, 'slider-progress-bar');
                    this.node.sliderControlLeft = this.getEntity(this.obBigSlider, 'slider-control-left');
                    this.node.sliderControlRight = this.getEntity(this.obBigSlider, 'slider-control-right');

                    if (!this.obBigSlider || !this.node.imageContainer || !this.node.imageContainer) {
                        this.errorCode = -2;
                    }

                    if (this.config.showPrice) {
                        this.obPrice.price = BX(this.visual.PRICE_ID);
                        if (!this.obPrice.price && this.config.useCatalog) {
                            this.errorCode = -16;
                        } else {
                            this.obPrice.total = BX(this.visual.PRICE_TOTAL);

                            if (this.config.showOldPrice) {
                                this.obPrice.full = BX(this.visual.OLD_PRICE_ID);
                                this.obPrice.discount = BX(this.visual.DISCOUNT_PRICE_ID);

                                if (!this.obPrice.full || !this.obPrice.discount) {
                                    this.config.showOldPrice = false;
                                }
                            }

                            if (this.config.showPercent) {
                                this.obPrice.percent = BX(this.visual.DISCOUNT_PERCENT_ID);
                                if (!this.obPrice.percent) {
                                    this.config.showPercent = false;
                                }
                            }
                        }

                        this.obBasketActions = BX(this.visual.BASKET_ACTIONS_ID);
                        if (this.obBasketActions) {
                            if (BX.util.in_array('BUY', this.config.basketAction)) {
                                this.obBuyBtn = BX(this.visual.BUY_LINK);
                            }

                            if (BX.util.in_array('ADD', this.config.basketAction)) {
                                this.obAddToBasketBtn = BX(this.visual.ADD_BASKET_LINK);
                            }
                        }
                        this.obNotAvail = BX(this.visual.NOT_AVAILABLE_MESS);
                    }

                    if (this.config.showQuantity) {
                        this.obQuantity = BX(this.visual.QUANTITY_ID);
                        this.node.quantity = this.getEntity(this.obProduct, 'quantity-block');
                        if (this.visual.QUANTITY_UP_ID) {
                            this.obQuantityUp = BX(this.visual.QUANTITY_UP_ID);
                        }

                        if (this.visual.QUANTITY_DOWN_ID) {
                            this.obQuantityDown = BX(this.visual.QUANTITY_DOWN_ID);
                        }
                    }

                    if (this.productType === 3) {
                        if (this.visual.TREE_ID) {
                            this.obTree = BX(this.visual.TREE_ID);
                            if (!this.obTree) {
                                this.errorCode = -256;
                            }
                        }

                        if (this.visual.QUANTITY_MEASURE) {
                            this.obMeasure = BX(this.visual.QUANTITY_MEASURE);
                        }

                        if (this.visual.QUANTITY_LIMIT && this.config.showMaxQuantity !== 'N') {
                            this.obQuantityLimit.all = BX(this.visual.QUANTITY_LIMIT);
                            if (this.obQuantityLimit.all) {
                                this.obQuantityLimit.value = this.getEntity(this.obQuantityLimit.all, 'quantity-limit-value');
                                if (!this.obQuantityLimit.value) {
                                    this.obQuantityLimit.all = null;
                                }
                            }
                        }

                        if (this.config.usePriceRanges) {
                            this.obPriceRanges = this.getEntity(this.obProduct, 'price-ranges-block');
                        }
                    }

                    if (this.config.showSkuProps) {
                        this.obSkuProps = BX(this.visual.DISPLAY_PROP_DIV);
                        this.obMainSkuProps = BX(this.visual.DISPLAY_MAIN_PROP_DIV);
                    }

                    if (this.config.showSkuDescription === 'Y') {
                        this.obDescription = BX(this.visual.DESCRIPTION_ID);
                    }

                    if (this.config.useCompare) {
                        this.obCompare = BX(this.visual.COMPARE_LINK);
                    }

                    if (this.config.useSubscribe) {
                        this.obSubscribe = BX(this.visual.SUBSCRIBE_LINK);
                    }

                    this.obTabs = BX(this.visual.TABS_ID);
                    this.obTabContainers = BX(this.visual.TAB_CONTAINERS_ID);
                    this.obTabsPanel = BX(this.visual.TABS_PANEL_ID);

                    this.smallCardNodes.panel = BX(this.visual.SMALL_CARD_PANEL_ID);
                    if (this.smallCardNodes.panel) {
                        this.smallCardNodes.picture = this.getEntity(this.smallCardNodes.panel, 'panel-picture');
                        this.smallCardNodes.title = this.getEntity(this.smallCardNodes.panel, 'panel-title');
                        this.smallCardNodes.price = this.getEntity(this.smallCardNodes.panel, 'panel-price');
                        this.smallCardNodes.sku = this.getEntity(this.smallCardNodes.panel, 'panel-sku-container');
                        this.smallCardNodes.oldPrice = this.getEntity(this.smallCardNodes.panel, 'panel-old-price');
                        this.smallCardNodes.buyButton = this.getEntity(this.smallCardNodes.panel, 'panel-buy-button');
                        this.smallCardNodes.addButton = this.getEntity(this.smallCardNodes.panel, 'panel-add-button');
                        this.smallCardNodes.notAvailableButton = this.getEntity(this.smallCardNodes.panel, 'panel-not-available-button');
                        this.smallCardNodes.aligner = this.getEntity(this.obProduct, 'main-button-container');
                    }

                    this.initPopup();
                    this.initTabs();

                    if (this.smallCardNodes.panel) {
                        this.smallCardNodes.picture && BX.bind(this.smallCardNodes.picture.parentNode, 'click', BX.proxy(this.scrollToProduct, this));
                        this.smallCardNodes.title && BX.bind(this.smallCardNodes.title, 'click', BX.proxy(this.scrollToProduct, this));
                        this.smallCardNodes.sku && BX.bind(this.smallCardNodes.sku, 'click', BX.proxy(this.scrollToProduct, this));
                    }

                    if (this.obTabsPanel || this.smallCardNodes.panel) {
                        this.checkTopPanels();
                        BX.bind(window, 'scroll', BX.proxy(this.checkTopPanels, this));
                    }

                    if (this.errorCode === 0) {
                        // product slider events
                        if (this.config.showSlider && !this.isTouchDevice) {
                            BX.bind(this.obBigSlider, 'mouseenter', BX.proxy(this.stopSlider, this));
                            BX.bind(this.obBigSlider, 'mouseleave', BX.proxy(this.cycleSlider, this));
                        }

                        if (this.isTouchDevice) {
                            BX.bind(this.node.imageContainer, 'touchstart', BX.proxy(this.touchStartEvent, this));
                            BX.bind(this.node.imageContainer, 'touchend', BX.proxy(this.touchEndEvent, this));
                            BX.bind(this.node.imageContainer, 'touchcancel', BX.proxy(this.touchEndEvent, this));
                        }

                        BX.bind(this.node.sliderControlLeft, 'click', BX.proxy(this.slidePrev, this));
                        BX.bind(this.node.sliderControlRight, 'click', BX.proxy(this.slideNext, this));

                        if (this.config.showQuantity) {
                            var startEventName = this.isTouchDevice ? 'touchstart' : 'mousedown';
                            var endEventName = this.isTouchDevice ? 'touchend' : 'mouseup';

                            if (this.obQuantityUp) {
                                BX.bind(this.obQuantityUp, startEventName, BX.proxy(this.startQuantityInterval, this));
                                BX.bind(this.obQuantityUp, endEventName, BX.proxy(this.clearQuantityInterval, this));
                                BX.bind(this.obQuantityUp, 'mouseout', BX.proxy(this.clearQuantityInterval, this));
                                BX.bind(this.obQuantityUp, 'click', BX.delegate(this.quantityUp, this));
                            }

                            if (this.obQuantityDown) {
                                BX.bind(this.obQuantityDown, startEventName, BX.proxy(this.startQuantityInterval, this));
                                BX.bind(this.obQuantityDown, endEventName, BX.proxy(this.clearQuantityInterval, this));
                                BX.bind(this.obQuantityDown, 'mouseout', BX.proxy(this.clearQuantityInterval, this));
                                BX.bind(this.obQuantityDown, 'click', BX.delegate(this.quantityDown, this));
                            }

                            if (this.obQuantity) {
                                BX.bind(this.obQuantity, 'change', BX.delegate(this.quantityChange, this));
                            }
                        }

                        switch (this.productType) {
                            case 0: // no catalog
                            case 1: // product
                            case 2: // set
                                if (this.product.useSlider) {
                                    this.product.slider = {
                                        ID: this.visual.SLIDER_CONT_ID,
                                        CONT: BX(this.visual.SLIDER_CONT_ID),
                                        COUNT: this.product.sliderCount
                                    };
                                    this.product.slider.ITEMS = this.getEntities(this.product.slider.CONT, 'slider-control');
                                    for (j = 0; j < this.product.slider.ITEMS.length; j++) {
                                        BX.bind(this.product.slider.ITEMS[j], 'mouseenter', BX.delegate(this.onSliderControlHover, this));
                                        BX.bind(this.product.slider.ITEMS[j], 'mouseleave', BX.delegate(this.onSliderControlLeave, this));
                                        BX.bind(this.product.slider.ITEMS[j], 'click', BX.delegate(this.selectSliderImg, this));
                                    }

                                    this.setCurrentImg(this.product.sliderPict[0], true, true);
                                    this.checkSliderControls(this.product.sliderCount);

                                    if (this.product.slider.ITEMS.length > 1) {
                                        this.initSlider();
                                    }
                                }

                                this.checkQuantityControls();
                                this.fixFontCheck();
                                this.setAnalyticsDataLayer('showDetail');
                                break;
                            case 3: // sku
                                treeItems = this.obTree.querySelectorAll('li');
                                for (i = 0; i < treeItems.length; i++) {
                                    BX.bind(treeItems[i], 'click', BX.delegate(this.selectOfferProp, this));
                                }

                                for (i = 0; i < this.offers.length; i++) {
                                    this.offers[i].SLIDER_COUNT = parseInt(this.offers[i].SLIDER_COUNT, 10) || 0;

                                    if (this.offers[i].SLIDER_COUNT === 0) {
                                        this.slider.controls[i] = {
                                            ID: '',
                                            COUNT: this.offers[i].SLIDER_COUNT,
                                            ITEMS: []
                                        };
                                    } else {
                                        for (j = 0; j < this.offers[i].SLIDER.length; j++) {
                                            this.offers[i].SLIDER[j].WIDTH = parseInt(this.offers[i].SLIDER[j].WIDTH, 10);
                                            this.offers[i].SLIDER[j].HEIGHT = parseInt(this.offers[i].SLIDER[j].HEIGHT, 10);
                                        }

                                        this.slider.controls[i] = {
                                            ID: this.visual.SLIDER_CONT_OF_ID + this.offers[i].ID,
                                            OFFER_ID: this.offers[i].ID,
                                            CONT: BX(this.visual.SLIDER_CONT_OF_ID + this.offers[i].ID),
                                            COUNT: this.offers[i].SLIDER_COUNT
                                        };

                                        this.slider.controls[i].ITEMS = this.getEntities(this.slider.controls[i].CONT, 'slider-control');
                                        for (j = 0; j < this.slider.controls[i].ITEMS.length; j++) {
                                            BX.bind(this.slider.controls[i].ITEMS[j], 'mouseenter', BX.delegate(this.onSliderControlHover, this));
                                            BX.bind(this.slider.controls[i].ITEMS[j], 'mouseleave', BX.delegate(this.onSliderControlLeave, this));
                                            BX.bind(this.slider.controls[i].ITEMS[j], 'click', BX.delegate(this.selectSliderImg, this));
                                        }
                                    }
                                }

                                this.setCurrent();
                                break;
                        }

                        this.obBuyBtn && BX.bind(this.obBuyBtn, 'click', BX.proxy(this.buyBasket, this));
                        this.smallCardNodes.buyButton && BX.bind(this.smallCardNodes.buyButton, 'click', BX.proxy(this.buyBasket, this));

                        this.obAddToBasketBtn && BX.bind(this.obAddToBasketBtn, 'click', BX.proxy(this.add2Basket, this));
                        this.smallCardNodes.addButton && BX.bind(this.smallCardNodes.addButton, 'click', BX.proxy(this.add2Basket, this));

                        if (this.obCompare) {
                            BX.bind(this.obCompare, 'click', BX.proxy(this.compare, this));
                            BX.addCustomEvent('onCatalogDeleteCompare', BX.proxy(this.checkDeletedCompare, this));
                        }
                    }
                },

                initConfig: function () {
                    if (this.params.PRODUCT_TYPE) {
                        this.productType = parseInt(this.params.PRODUCT_TYPE, 10);
                    }

                    if (this.params.CONFIG.USE_CATALOG !== 'undefined' && BX.type.isBoolean(this.params.CONFIG.USE_CATALOG)) {
                        this.config.useCatalog = this.params.CONFIG.USE_CATALOG;
                    }

                    this.config.showQuantity = this.params.CONFIG.SHOW_QUANTITY;
                    this.config.showPrice = this.params.CONFIG.SHOW_PRICE;
                    this.config.showPercent = this.params.CONFIG.SHOW_DISCOUNT_PERCENT;
                    this.config.showOldPrice = this.params.CONFIG.SHOW_OLD_PRICE;
                    this.config.showSkuProps = this.params.CONFIG.SHOW_SKU_PROPS;
                    this.config.showOfferGroup = this.params.CONFIG.OFFER_GROUP;
                    this.config.useCompare = this.params.CONFIG.DISPLAY_COMPARE;
                    this.config.useStickers = this.params.CONFIG.USE_STICKERS;
                    this.config.useSubscribe = this.params.CONFIG.USE_SUBSCRIBE;
                    this.config.showMaxQuantity = this.params.CONFIG.SHOW_MAX_QUANTITY;
                    this.config.relativeQuantityFactor = parseInt(this.params.CONFIG.RELATIVE_QUANTITY_FACTOR);
                    this.config.usePriceRanges = this.params.CONFIG.USE_PRICE_COUNT;
                    this.config.showSkuDescription = this.params.CONFIG.SHOW_SKU_DESCRIPTION;
                    this.config.displayPreviewTextMode = this.params.CONFIG.DISPLAY_PREVIEW_TEXT_MODE;

                    if (this.params.CONFIG.MAIN_PICTURE_MODE) {
                        this.config.usePopup = BX.util.in_array('POPUP', this.params.CONFIG.MAIN_PICTURE_MODE);
                        this.config.useMagnifier = BX.util.in_array('MAGNIFIER', this.params.CONFIG.MAIN_PICTURE_MODE);
                    }

                    if (this.params.CONFIG.ADD_TO_BASKET_ACTION) {
                        this.config.basketAction = this.params.CONFIG.ADD_TO_BASKET_ACTION;
                    }

                    this.config.showClosePopup = this.params.CONFIG.SHOW_CLOSE_POPUP;
                    this.config.templateTheme = this.params.CONFIG.TEMPLATE_THEME || '';
                    this.config.showSlider = this.params.CONFIG.SHOW_SLIDER === 'Y';

                    if (this.config.showSlider && !this.isTouchDevice) {
                        this.config.sliderInterval = parseInt(this.params.CONFIG.SLIDER_INTERVAL) || 5000;
                    } else {
                        this.config.sliderInterval = false;
                    }

                    this.config.useEnhancedEcommerce = this.params.CONFIG.USE_ENHANCED_ECOMMERCE === 'Y';
                    this.config.dataLayerName = this.params.CONFIG.DATA_LAYER_NAME;
                    this.config.brandProperty = this.params.CONFIG.BRAND_PROPERTY;

                    this.config.alt = this.params.CONFIG.ALT || '';
                    this.config.title = this.params.CONFIG.TITLE || '';

                    this.config.magnifierZoomPercent = parseInt(this.params.CONFIG.MAGNIFIER_ZOOM_PERCENT) || 200;

                    if (!this.params.VISUAL || typeof this.params.VISUAL !== 'object' || !this.params.VISUAL.ID) {
                        this.errorCode = -1;
                        return;
                    }

                    this.visual = this.params.VISUAL;
                },

                initProductData: function () {
                    var j = 0;

                    if (this.params.PRODUCT && typeof this.params.PRODUCT === 'object') {
                        if (this.config.showPrice) {
                            this.currentPriceMode = this.params.PRODUCT.ITEM_PRICE_MODE;
                            this.currentPrices = this.params.PRODUCT.ITEM_PRICES;
                            this.currentPriceSelected = this.params.PRODUCT.ITEM_PRICE_SELECTED;
                            this.currentQuantityRanges = this.params.PRODUCT.ITEM_QUANTITY_RANGES;
                            this.currentQuantityRangeSelected = this.params.PRODUCT.ITEM_QUANTITY_RANGE_SELECTED;
                        }

                        if (this.config.showQuantity) {
                            this.product.checkQuantity = this.params.PRODUCT.CHECK_QUANTITY;
                            this.product.isDblQuantity = this.params.PRODUCT.QUANTITY_FLOAT;

                            if (this.product.checkQuantity) {
                                this.product.maxQuantity = this.product.isDblQuantity ?
                                    parseFloat(this.params.PRODUCT.MAX_QUANTITY) :
                                    parseInt(this.params.PRODUCT.MAX_QUANTITY, 10);
                            }

                            this.product.stepQuantity = this.product.isDblQuantity ?
                                parseFloat(this.params.PRODUCT.STEP_QUANTITY) :
                                parseInt(this.params.PRODUCT.STEP_QUANTITY, 10);
                            this.checkQuantity = this.product.checkQuantity;
                            this.isDblQuantity = this.product.isDblQuantity;
                            this.stepQuantity = this.product.stepQuantity;
                            this.maxQuantity = this.product.maxQuantity;
                            this.minQuantity = this.currentPriceMode === 'Q' ? parseFloat(this.currentPrices[this.currentPriceSelected].MIN_QUANTITY) : this.stepQuantity;

                            if (this.isDblQuantity) {
                                this.stepQuantity = Math.round(this.stepQuantity * this.precisionFactor) / this.precisionFactor;
                            }
                        }

                        this.product.canBuy = this.params.PRODUCT.CAN_BUY;
                        this.canSubscription = this.product.canSubscription = this.params.PRODUCT.SUBSCRIPTION;

                        this.product.name = this.params.PRODUCT.NAME;
                        this.product.pict = this.params.PRODUCT.PICT;
                        this.product.id = this.params.PRODUCT.ID;
                        this.product.category = this.params.PRODUCT.CATEGORY;

                        if (this.params.PRODUCT.ADD_URL) {
                            this.product.addUrl = this.params.PRODUCT.ADD_URL;
                        }

                        if (this.params.PRODUCT.BUY_URL) {
                            this.product.buyUrl = this.params.PRODUCT.BUY_URL;
                        }

                        if (this.params.PRODUCT.SLIDER_COUNT) {
                            this.product.sliderCount = parseInt(this.params.PRODUCT.SLIDER_COUNT, 10) || 0;

                            if (this.product.sliderCount > 0 && this.params.PRODUCT.SLIDER.length) {
                                for (j = 0; j < this.params.PRODUCT.SLIDER.length; j++) {
                                    this.product.useSlider = true;
                                    this.params.PRODUCT.SLIDER[j].WIDTH = parseInt(this.params.PRODUCT.SLIDER[j].WIDTH, 10);
                                    this.params.PRODUCT.SLIDER[j].HEIGHT = parseInt(this.params.PRODUCT.SLIDER[j].HEIGHT, 10);
                                }

                                this.product.sliderPict = this.params.PRODUCT.SLIDER;
                                this.setCurrentImg(this.product.sliderPict[0], false);
                            }
                        }

                        this.currentIsSet = true;
                    } else {
                        this.errorCode = -1;
                    }
                },

                initOffersData: function () {
                    if (this.params.OFFERS && BX.type.isArray(this.params.OFFERS)) {
                        this.offers = this.params.OFFERS;
                        this.offerNum = 0;

                        if (this.params.OFFER_SELECTED) {
                            this.offerNum = parseInt(this.params.OFFER_SELECTED, 10) || 0;
                        }

                        if (this.params.TREE_PROPS) {
                            this.treeProps = this.params.TREE_PROPS;
                        }

                        if (this.params.DEFAULT_PICTURE) {
                            this.defaultPict.preview = this.params.DEFAULT_PICTURE.PREVIEW_PICTURE;
                            this.defaultPict.detail = this.params.DEFAULT_PICTURE.DETAIL_PICTURE;
                        }

                        if (this.params.PRODUCT && typeof this.params.PRODUCT === 'object') {
                            this.product.id = parseInt(this.params.PRODUCT.ID, 10);
                            this.product.name = this.params.PRODUCT.NAME;
                            this.product.category = this.params.PRODUCT.CATEGORY;
                            this.product.detailText = this.params.PRODUCT.DETAIL_TEXT;
                            this.product.detailTextType = this.params.PRODUCT.DETAIL_TEXT_TYPE;
                            this.product.previewText = this.params.PRODUCT.PREVIEW_TEXT;
                            this.product.previewTextType = this.params.PRODUCT.PREVIEW_TEXT_TYPE;
                        }
                    } else {
                        this.errorCode = -1;
                    }
                },

                initBasketData: function () {
                    if (this.params.BASKET && typeof this.params.BASKET === 'object') {
                        if (this.productType === 1 || this.productType === 2) {
                            this.basketData.useProps = this.params.BASKET.ADD_PROPS;
                            this.basketData.emptyProps = this.params.BASKET.EMPTY_PROPS;
                        }

                        if (this.params.BASKET.QUANTITY) {
                            this.basketData.quantity = this.params.BASKET.QUANTITY;
                        }

                        if (this.params.BASKET.PROPS) {
                            this.basketData.props = this.params.BASKET.PROPS;
                        }

                        if (this.params.BASKET.BASKET_URL) {
                            this.basketData.basketUrl = this.params.BASKET.BASKET_URL;
                        }

                        if (this.productType === 3) {
                            if (this.params.BASKET.SKU_PROPS) {
                                this.basketData.sku_props = this.params.BASKET.SKU_PROPS;
                            }
                        }

                        if (this.params.BASKET.ADD_URL_TEMPLATE) {
                            this.basketData.add_url = this.params.BASKET.ADD_URL_TEMPLATE;
                        }

                        if (this.params.BASKET.BUY_URL_TEMPLATE) {
                            this.basketData.buy_url = this.params.BASKET.BUY_URL_TEMPLATE;
                        }

                        if (this.basketData.add_url === '' && this.basketData.buy_url === '') {
                            this.errorCode = -1024;
                        }
                    }
                },

                initCompareData: function () {
                    if (this.config.useCompare) {
                        if (this.params.COMPARE && typeof this.params.COMPARE === 'object') {
                            if (this.params.COMPARE.COMPARE_PATH) {
                                this.compareData.comparePath = this.params.COMPARE.COMPARE_PATH;
                            }

                            if (this.params.COMPARE.COMPARE_URL_TEMPLATE) {
                                this.compareData.compareUrl = this.params.COMPARE.COMPARE_URL_TEMPLATE;
                            } else {
                                this.config.useCompare = false;
                            }

                            if (this.params.COMPARE.COMPARE_DELETE_URL_TEMPLATE) {
                                this.compareData.compareDeleteUrl = this.params.COMPARE.COMPARE_DELETE_URL_TEMPLATE;
                            } else {
                                this.config.useCompare = false;
                            }
                        } else {
                            this.config.useCompare = false;
                        }
                    }
                },

                initSlider: function () {
                    if (this.node.sliderProgressBar) {
                        if (this.slider.progress) {
                            this.resetProgress();
                        } else {
                            this.slider.progress = new BX.easing({
                                transition: BX.easing.transitions.linear,
                                step: BX.delegate(function (state) {
                                    this.node.sliderProgressBar.style.width = state.width / 10 + '%';
                                }, this)
                            });
                        }
                    }

                    this.cycleSlider();
                },

                setAnalyticsDataLayer: function (action) {
                    if (!this.config.useEnhancedEcommerce || !this.config.dataLayerName)
                        return;

                    var item = {},
                        info = {},
                        variants = [],
                        i, k, j, propId, skuId, propValues;

                    switch (this.productType) {
                        case 0: //no catalog
                        case 1: //product
                        case 2: //set
                            item = {
                                'id': this.product.id,
                                'name': this.product.name,
                                'price': this.currentPrices[this.currentPriceSelected] && this.currentPrices[this.currentPriceSelected].PRICE,
                                'category': this.product.category,
                                'brand': BX.type.isArray(this.config.brandProperty) ? this.config.brandProperty.join('/') : this.config.brandProperty
                            };
                            break;
                        case 3: //sku
                            for (i in this.offers[this.offerNum].TREE) {
                                if (this.offers[this.offerNum].TREE.hasOwnProperty(i)) {
                                    propId = i.substring(5);
                                    skuId = this.offers[this.offerNum].TREE[i];

                                    for (k in this.treeProps) {
                                        if (this.treeProps.hasOwnProperty(k) && this.treeProps[k].ID == propId) {
                                            for (j in this.treeProps[k].VALUES) {
                                                propValues = this.treeProps[k].VALUES[j];
                                                if (propValues.ID == skuId) {
                                                    variants.push(propValues.NAME);
                                                    break;
                                                }
                                            }

                                        }
                                    }
                                }
                            }

                            item = {
                                'id': this.offers[this.offerNum].ID,
                                'name': this.offers[this.offerNum].NAME,
                                'price': this.currentPrices[this.currentPriceSelected] && this.currentPrices[this.currentPriceSelected].PRICE,
                                'category': this.product.category,
                                'brand': BX.type.isArray(this.config.brandProperty) ? this.config.brandProperty.join('/') : this.config.brandProperty,
                                'variant': variants.join('/')
                            };
                            break;
                    }

                    switch (action) {
                        case 'showDetail':
                            info = {
                                'event': 'showDetail',
                                'ecommerce': {
                                    'currencyCode': this.currentPrices[this.currentPriceSelected] && this.currentPrices[this.currentPriceSelected].CURRENCY || '',
                                    'detail': {
                                        'products': [{
                                            'name': item.name || '',
                                            'id': item.id || '',
                                            'price': item.price || 0,
                                            'brand': item.brand || '',
                                            'category': item.category || '',
                                            'variant': item.variant || ''
                                        }]
                                    }
                                }
                            };
                            break;
                        case 'addToCart':
                            info = {
                                'event': 'addToCart',
                                'ecommerce': {
                                    'currencyCode': this.currentPrices[this.currentPriceSelected] && this.currentPrices[this.currentPriceSelected].CURRENCY || '',
                                    'add': {
                                        'products': [{
                                            'name': item.name || '',
                                            'id': item.id || '',
                                            'price': item.price || 0,
                                            'brand': item.brand || '',
                                            'category': item.category || '',
                                            'variant': item.variant || '',
                                            'quantity': this.config.showQuantity && this.obQuantity ? this.obQuantity.value : 1
                                        }]
                                    }
                                }
                            };
                            break;
                    }

                    window[this.config.dataLayerName] = window[this.config.dataLayerName] || [];
                    window[this.config.dataLayerName].push(info);
                },

                initTabs: function () {
                    var tabs = this.getEntities(this.obTabs, 'tab'),
                        panelTabs = this.getEntities(this.obTabsPanel, 'tab');

                    var tabValue, targetTab, haveActive = false;

                    if (tabs.length !== panelTabs.length)
                        return;

                    for (var i in tabs) {
                        if (tabs.hasOwnProperty(i) && BX.type.isDomNode(tabs[i])) {
                            tabValue = tabs[i].getAttribute('data-value');
                            if (tabValue) {
                                targetTab = this.obTabContainers.querySelector('[data-value="' + tabValue + '"]');
                                if (BX.type.isDomNode(targetTab)) {
                                    BX.bind(tabs[i], 'click', BX.proxy(this.changeTab, this));
                                    BX.bind(panelTabs[i], 'click', BX.proxy(this.changeTab, this));

                                    if (!haveActive) {
                                        BX.addClass(tabs[i], 'active');
                                        BX.addClass(panelTabs[i], 'active');
                                        BX.show(targetTab);
                                        haveActive = true;
                                    } else {
                                        BX.removeClass(tabs[i], 'active');
                                        BX.removeClass(panelTabs[i], 'active');
                                        BX.hide(targetTab);
                                    }
                                }
                            }
                        }
                    }
                },

                checkTouch: function (event) {
                    if (!event || !event.changedTouches)
                        return false;

                    return event.changedTouches[0].identifier === this.touch.identifier;
                },

                touchStartEvent: function (event) {
                    if (event.touches.length != 1)
                        return;

                    this.touch = event.changedTouches[0];
                },

                touchEndEvent: function (event) {
                    if (!this.checkTouch(event))
                        return;

                    var deltaX = this.touch.pageX - event.changedTouches[0].pageX,
                        deltaY = this.touch.pageY - event.changedTouches[0].pageY;

                    if (Math.abs(deltaX) >= Math.abs(deltaY) + 10) {
                        if (deltaX > 0) {
                            this.slideNext();
                        }

                        if (deltaX < 0) {
                            this.slidePrev();
                        }
                    }
                },

                cycleSlider: function (event) {
                    event || (this.slider.paused = false);

                    this.slider.interval && clearInterval(this.slider.interval);

                    if (this.config.sliderInterval && !this.slider.paused) {
                        if (this.slider.progress) {
                            this.slider.progress.stop();

                            var width = parseInt(this.node.sliderProgressBar.style.width);

                            this.slider.progress.options.duration = this.config.sliderInterval * (100 - width) / 100;
                            this.slider.progress.options.start = {width: width * 10};
                            this.slider.progress.options.finish = {width: 1000};
                            this.slider.progress.options.complete = BX.delegate(function () {
                                this.slider.interval = true;
                                this.slideNext();
                            }, this);
                            this.slider.progress.animate();
                        } else {
                            this.slider.interval = setInterval(BX.proxy(this.slideNext, this), this.config.sliderInterval);
                        }
                    }
                },

                stopSlider: function (event) {
                    event || (this.slider.paused = true);

                    this.slider.interval && (this.slider.interval = clearInterval(this.slider.interval));

                    if (this.slider.progress) {
                        this.slider.progress.stop();

                        var width = parseInt(this.node.sliderProgressBar.style.width);

                        this.slider.progress.options.duration = this.config.sliderInterval * width / 200;
                        this.slider.progress.options.start = {width: width * 10};
                        this.slider.progress.options.finish = {width: 0};
                        this.slider.progress.options.complete = null;
                        this.slider.progress.animate();
                    }
                },

                resetProgress: function () {
                    this.slider.progress && this.slider.progress.stop();
                    this.node.sliderProgressBar.style.width = 0;
                },

                slideNext: function () {
                    return this.slide('next');
                },

                slidePrev: function () {
                    return this.slide('prev');
                },

                slide: function (type) {
                    if (!this.product.slider || !this.product.slider.CONT)
                        return;

                    var active = this.getEntity(this.product.slider.CONT, 'slider-control', '.active'),
                        next = this.getItemForDirection(type, active);

                    BX.removeClass(active, 'active');
                    this.selectSliderImg(next);

                    this.slider.interval && this.cycleSlider();
                },

                getItemForDirection: function (direction, active) {
                    var activeIndex = this.getItemIndex(active),
                        delta = direction === 'prev' ? -1 : 1,
                        itemIndex = (activeIndex + delta) % this.product.slider.COUNT;

                    return this.eq(this.product.slider.ITEMS, itemIndex);
                },

                getItemIndex: function (item) {
                    return BX.util.array_values(this.product.slider.ITEMS).indexOf(item);
                },

                eq: function (obj, i) {
                    var len = obj.length,
                        j = +i + (i < 0 ? len : 0);

                    return j >= 0 && j < len ? obj[j] : {};
                },

                scrollToProduct: function () {
                    var scrollTop = BX.GetWindowScrollPos().scrollTop,
                        containerTop = BX.pos(this.obProduct).top - 30;

                    if (scrollTop > containerTop) {
                        new BX.easing({
                            duration: 500,
                            start: {scroll: scrollTop},
                            finish: {scroll: containerTop},
                            transition: BX.easing.makeEaseOut(BX.easing.transitions.quint),
                            step: BX.delegate(function (state) {
                                window.scrollTo(0, state.scroll);
                            }, this)
                        }).animate();
                    }
                },

                checkTopPanels: function () {
                    var scrollTop = BX.GetWindowScrollPos().scrollTop,
                        targetPos;

                    if (this.smallCardNodes.panel) {
                        targetPos = BX.pos(this.smallCardNodes.aligner).bottom - 50;

                        if (scrollTop > targetPos) {
                            BX.addClass(this.smallCardNodes.panel, 'active');
                        } else if (BX.hasClass(this.smallCardNodes.panel, 'active')) {
                            BX.removeClass(this.smallCardNodes.panel, 'active');
                        }
                    }

                    if (this.obTabsPanel) {
                        targetPos = BX.pos(this.obTabs).top;

                        if (scrollTop + 73 > targetPos) {
                            BX.addClass(this.obTabsPanel, 'active');
                        } else if (BX.hasClass(this.obTabsPanel, 'active')) {
                            BX.removeClass(this.obTabsPanel, 'active');
                        }
                    }
                },

                changeTab: function (event) {
                    BX.PreventDefault(event);

                    var targetTabValue = BX.proxy_context && BX.proxy_context.getAttribute('data-value'),
                        containers, tabs, panelTabs;

                    if (!BX.hasClass(BX.proxy_context, 'active') && targetTabValue) {
                        containers = this.getEntities(this.obTabContainers, 'tab-container');
                        for (var i in containers) {
                            if (containers.hasOwnProperty(i) && BX.type.isDomNode(containers[i])) {
                                if (containers[i].getAttribute('data-value') === targetTabValue) {
                                    BX.show(containers[i]);
                                } else {
                                    BX.hide(containers[i]);
                                }
                            }
                        }

                        tabs = this.getEntities(this.obTabs, 'tab');
                        panelTabs = this.getEntities(this.obTabsPanel, 'tab');

                        for (i in tabs) {
                            if (tabs.hasOwnProperty(i) && BX.type.isDomNode(tabs[i])) {
                                if (tabs[i].getAttribute('data-value') === targetTabValue) {
                                    BX.addClass(tabs[i], 'active');
                                    BX.addClass(panelTabs[i], 'active');
                                } else {
                                    BX.removeClass(tabs[i], 'active');
                                    BX.removeClass(panelTabs[i], 'active');
                                }
                            }
                        }
                    }

                    var scrollTop = BX.GetWindowScrollPos().scrollTop,
                        containerTop = BX.pos(this.obTabContainers).top;

                    if (scrollTop + 150 > containerTop) {
                        new BX.easing({
                            duration: 500,
                            start: {scroll: scrollTop},
                            finish: {scroll: containerTop - 150},
                            transition: BX.easing.makeEaseOut(BX.easing.transitions.quint),
                            step: BX.delegate(function (state) {
                                window.scrollTo(0, state.scroll);
                            }, this)
                        }).animate();
                    }
                },

                initPopup: function () {
                    if (this.config.usePopup) {
                        this.node.imageContainer.style.cursor = 'zoom-in';
                        BX.bind(this.node.imageContainer, 'click', BX.delegate(this.toggleMainPictPopup, this));
                        BX.bind(document, 'keyup', BX.proxy(this.closeByEscape, this));
                        BX.bind(
                            this.getEntity(this.obBigSlider, 'close-popup'),
                            'click',
                            BX.proxy(this.hideMainPictPopup, this)
                        );
                    }
                },

                checkSliderControls: function (count) {
                    var display = count > 1 ? '' : 'none';

                    this.node.sliderControlLeft && (this.node.sliderControlLeft.style.display = display);
                    this.node.sliderControlRight && (this.node.sliderControlRight.style.display = display);
                },

                setCurrentImg: function (img, showImage, showPanelImage) {
                    var images, l;

                    this.currentImg.id = img.ID;
                    this.currentImg.src = img.SRC;
                    this.currentImg.width = img.WIDTH;
                    this.currentImg.height = img.HEIGHT;

                    if (showImage && this.node.imageContainer) {
                        images = this.getEntities(this.node.imageContainer, 'image');
                        l = images.length;
                        while (l--) {
                            if (images[l].getAttribute('data-id') == img.ID) {
                                if (!BX.hasClass(images[l], 'active')) {
                                    this.node.sliderProgressBar && this.resetProgress();
                                }

                                BX.addClass(images[l], 'active');
                            } else if (BX.hasClass(images[l], 'active')) {
                                BX.removeClass(images[l], 'active');
                            }
                        }
                    }

                    if (showPanelImage && this.smallCardNodes.picture) {
                        this.smallCardNodes.picture.setAttribute('src', this.currentImg.src);
                    }

                    if (this.config.useMagnifier && !this.isTouchDevice) {
                        this.setMagnifierParams();

                        if (showImage) {
                            this.disableMagnifier(true);
                        }
                    }
                },

                setMagnifierParams: function () {
                    var images = this.getEntities(this.node.imageContainer, 'image'),
                        l = images.length,
                        current;

                    while (l--) {
                        // disable image title show
                        current = images[l].querySelector('img');
                        current.setAttribute('data-title', current.getAttribute('title') || '');
                        current.removeAttribute('title');

                        if (images[l].getAttribute('data-id') == this.currentImg.id) {
                            BX.unbind(this.currentImg.node, 'mouseover', BX.proxy(this.enableMagnifier, this));

                            this.currentImg.node = current;
                            this.currentImg.node.style.backgroundImage = 'url(\'' + this.currentImg.src + '\')';
                            this.currentImg.node.style.backgroundSize = '100% auto';

                            BX.bind(this.currentImg.node, 'mouseover', BX.proxy(this.enableMagnifier, this));
                        }
                    }
                },

                enableMagnifier: function () {
                    BX.bind(document, 'mousemove', BX.proxy(this.moveMagnifierArea, this));
                },

                disableMagnifier: function (animateSize) {
                    if (!this.magnify.enabled)
                        return;

                    clearTimeout(this.magnify.timer);
                    BX.removeClass(this.obBigSlider, 'magnified');
                    this.magnify.enabled = false;

                    this.currentImg.node.style.backgroundSize = '100% auto';
                    if (animateSize) {
                        // set initial size for css animation
                        this.currentImg.node.style.height = this.magnify.height + 'px';
                        this.currentImg.node.style.width = this.magnify.width + 'px';

                        this.magnify.timer = setTimeout(
                            BX.delegate(function () {
                                this.currentImg.node.src = this.currentImg.src;
                                this.currentImg.node.style.height = '';
                                this.currentImg.node.style.width = '';
                            }, this),
                            250
                        );
                    } else {
                        this.currentImg.node.src = this.currentImg.src;
                        this.currentImg.node.style.height = '';
                        this.currentImg.node.style.width = '';
                    }

                    BX.unbind(document, 'mousemove', BX.proxy(this.moveMagnifierArea, this));
                },

                moveMagnifierArea: function (e) {
                    var posBigImg = BX.pos(this.currentImg.node),
                        currentPos = this.inRect(e, posBigImg);

                    if (this.inBound(posBigImg, currentPos)) {
                        var posPercentX = (currentPos.X / this.currentImg.node.width) * 100,
                            posPercentY = (currentPos.Y / this.currentImg.node.height) * 100,
                            resolution, sliderWidth, w, h, zoomPercent;

                        this.currentImg.node.style.backgroundPosition = posPercentX + '% ' + posPercentY + '%';

                        if (!this.magnify.enabled) {
                            clearTimeout(this.magnify.timer);
                            BX.addClass(this.obBigSlider, 'magnified');

                            // set initial size for css animation
                            this.currentImg.node.style.height = (this.magnify.height = this.currentImg.node.clientHeight) + 'px';
                            this.currentImg.node.style.width = (this.magnify.width = this.currentImg.node.offsetWidth) + 'px';

                            resolution = this.currentImg.width / this.currentImg.height;
                            sliderWidth = this.obBigSlider.offsetWidth;

                            if (sliderWidth > this.currentImg.width && !BX.hasClass(this.obBigSlider, 'popup')) {
                                w = sliderWidth;
                                h = w / resolution;
                                zoomPercent = 100;
                            } else {
                                w = this.currentImg.width;
                                h = this.currentImg.height;
                                zoomPercent = this.config.magnifierZoomPercent > 100 ? this.config.magnifierZoomPercent : 100;
                            }

                            // base64 transparent pixel
                            this.currentImg.node.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQI12P4zwAAAgEBAKrChTYAAAAASUVORK5CYII=';
                            this.currentImg.node.style.backgroundSize = zoomPercent + '% auto';

                            // set target size
                            this.magnify.timer = setTimeout(BX.delegate(function () {
                                    this.currentImg.node.style.height = h + 'px';
                                    this.currentImg.node.style.width = w + 'px';
                                }, this),
                                10
                            );
                        }

                        this.magnify.enabled = true;
                    } else {
                        this.disableMagnifier(true);
                    }
                },

                inBound: function (rect, point) {
                    return (
                        (point.Y >= 0 && rect.height >= point.Y) &&
                        (point.X >= 0 && rect.width >= point.X)
                    );
                },

                inRect: function (e, rect) {
                    var wndSize = BX.GetWindowSize(),
                        currentPos = {
                            X: 0,
                            Y: 0,
                            globalX: 0,
                            globalY: 0
                        };

                    currentPos.globalX = e.clientX + wndSize.scrollLeft;

                    if (e.offsetX && e.offsetX < 0) {
                        currentPos.globalX -= e.offsetX;
                    }

                    currentPos.X = currentPos.globalX - rect.left;
                    currentPos.globalY = e.clientY + wndSize.scrollTop;

                    if (e.offsetY && e.offsetY < 0) {
                        currentPos.globalY -= e.offsetY;
                    }

                    currentPos.Y = currentPos.globalY - rect.top;

                    return currentPos;
                },

                setProductMainPict: function (intPict) {
                    var indexPict = -1,
                        i = 0,
                        j = 0,
                        value = '';

                    if (this.product.sliderCount) {
                        for (j = 0; j < this.product.sliderPict.length; j++) {
                            if (intPict === this.product.sliderPict[j].ID) {
                                indexPict = j;
                                break;
                            }
                        }

                        if (indexPict > -1) {
                            if (this.product.sliderPict[indexPict]) {
                                this.setCurrentImg(this.product.sliderPict[indexPict], true);
                            }

                            for (i = 0; i < this.product.slider.ITEMS.length; i++) {
                                value = this.product.slider.ITEMS[i].getAttribute('data-value');

                                if (value === intPict) {
                                    BX.addClass(this.product.slider.ITEMS[i], 'active');
                                } else if (BX.hasClass(this.product.slider.ITEMS[i], 'active')) {
                                    BX.removeClass(this.product.slider.ITEMS[i], 'active');
                                }
                            }
                        }
                    }
                },

                onSliderControlHover: function () {
                    var target = BX.proxy_context;

                    this.mouseTimer = setTimeout(
                        BX.delegate(function () {
                            this.selectSliderImg(target);
                        }, this),
                        200
                    );
                },

                onSliderControlLeave: function () {
                    clearTimeout(this.mouseTimer);
                    this.mouseTimer = null;
                },

                selectSliderImg: function (target) {
                    var strValue = '',
                        arItem = [];

                    target = BX.type.isDomNode(target) ? target : BX.proxy_context;

                    if (target && target.hasAttribute('data-value')) {
                        strValue = target.getAttribute('data-value');

                        if (strValue.indexOf('_') !== -1) {
                            arItem = strValue.split('_');
                            this.setMainPict(arItem[0], arItem[1]);
                        } else {
                            this.setProductMainPict(strValue);
                        }
                    }
                },

                setMainPict: function (intSlider, intPict, changePanelPict) {
                    var index = -1,
                        indexPict = -1,
                        i,
                        j,
                        value = '',
                        strValue = '';

                    for (i = 0; i < this.offers.length; i++) {
                        if (intSlider === this.offers[i].ID) {
                            index = i;
                            break;
                        }
                    }

                    if (index > -1) {
                        if (this.offers[index].SLIDER_COUNT > 0) {
                            for (j = 0; j < this.offers[index].SLIDER.length; j++) {
                                if (intPict === this.offers[index].SLIDER[j].ID) {
                                    indexPict = j;
                                    break;
                                }
                            }

                            if (indexPict > -1) {
                                if (this.offers[index].SLIDER[indexPict]) {
                                    this.setCurrentImg(this.offers[index].SLIDER[indexPict], true, changePanelPict);
                                }

                                strValue = intSlider + '_' + intPict;

                                for (i = 0; i < this.product.slider.ITEMS.length; i++) {
                                    value = this.product.slider.ITEMS[i].getAttribute('data-value');

                                    if (value === strValue) {
                                        BX.addClass(this.product.slider.ITEMS[i], 'active');
                                    } else if (BX.hasClass(this.product.slider.ITEMS[i], 'active')) {
                                        BX.removeClass(this.product.slider.ITEMS[i], 'active');
                                    }
                                }
                            }
                        }
                    }
                },

                setMainPictFromItem: function (index) {
                    if (this.node.imageContainer) {
                        var boolSet = false,
                            obNewPict = {};

                        if (this.offers[index]) {
                            if (this.offers[index].DETAIL_PICTURE) {
                                obNewPict = this.offers[index].DETAIL_PICTURE;
                                boolSet = true;
                            } else if (this.offers[index].PREVIEW_PICTURE) {
                                obNewPict = this.offers[index].PREVIEW_PICTURE;
                                boolSet = true;
                            }
                        }

                        if (!boolSet) {
                            if (this.defaultPict.detail) {
                                obNewPict = this.defaultPict.detail;
                                boolSet = true;
                            } else if (this.defaultPict.preview) {
                                obNewPict = this.defaultPict.preview;
                                boolSet = true;
                            }
                        }

                        if (boolSet) {
                            this.setCurrentImg(obNewPict, true, true);
                        }
                    }
                },

                toggleMainPictPopup: function () {
                    if (BX.hasClass(this.obBigSlider, 'popup')) {
                        this.hideMainPictPopup();
                    } else {
                        this.showMainPictPopup();
                    }
                },

                showMainPictPopup: function () {
                    this.config.useMagnifier && this.disableMagnifier(false);
                    BX.addClass(this.obBigSlider, 'popup');
                    this.node.imageContainer.style.cursor = '';
                    // remove double scroll bar
                    document.body.style.overflow = 'hidden';
                },

                hideMainPictPopup: function () {
                    this.config.useMagnifier && this.disableMagnifier(false);
                    BX.removeClass(this.obBigSlider, 'popup');
                    this.node.imageContainer.style.cursor = 'zoom-in';
                    // remove double scroll bar
                    document.body.style.overflow = '';
                },

                closeByEscape: function (event) {
                    event = event || window.event;

                    if (event.keyCode == 27) {
                        this.hideMainPictPopup();
                    }
                },

                startQuantityInterval: function () {
                    var target = BX.proxy_context;
                    var func = target.id === this.visual.QUANTITY_DOWN_ID ?
                        BX.proxy(this.quantityDown, this) :
                        BX.proxy(this.quantityUp, this);

                    this.quantityDelay = setTimeout(
                        BX.delegate(function () {
                            this.quantityTimer = setInterval(func, 150);
                        }, this),
                        300
                    );
                },

                clearQuantityInterval: function () {
                    clearTimeout(this.quantityDelay);
                    clearInterval(this.quantityTimer);
                },

                quantityUp: function () {
                    var curValue = 0,
                        boolSet = true;

                    if (this.errorCode === 0 && this.config.showQuantity && this.canBuy && !this.isGift) {
                        curValue = this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10);
                        if (!isNaN(curValue)) {
                            curValue += this.stepQuantity;

                            curValue = this.checkQuantityRange(curValue, 'up');

                            if (this.checkQuantity && curValue > this.maxQuantity) {
                                boolSet = false;
                            }

                            if (boolSet) {
                                if (this.isDblQuantity) {
                                    curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;
                                }

                                this.obQuantity.value = curValue;

                                this.setPrice();
                            }
                        }
                    }
                },

                quantityDown: function () {
                    var curValue = 0,
                        boolSet = true;

                    if (this.errorCode === 0 && this.config.showQuantity && this.canBuy && !this.isGift) {
                        curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
                        if (!isNaN(curValue)) {
                            curValue -= this.stepQuantity;

                            curValue = this.checkQuantityRange(curValue, 'down');

                            if (curValue < this.minQuantity) {
                                boolSet = false;
                            }

                            if (boolSet) {
                                if (this.isDblQuantity) {
                                    curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;
                                }

                                this.obQuantity.value = curValue;

                                this.setPrice();
                            }
                        }
                    }
                },

                quantityChange: function () {
                    var curValue = 0,
                        intCount;

                    if (this.errorCode === 0 && this.config.showQuantity) {
                        if (this.canBuy) {
                            curValue = this.isDblQuantity ? parseFloat(this.obQuantity.value) : Math.round(this.obQuantity.value);
                            if (!isNaN(curValue)) {
                                curValue = this.checkQuantityRange(curValue);

                                if (this.checkQuantity) {
                                    if (curValue > this.maxQuantity) {
                                        curValue = this.maxQuantity;
                                    }
                                }

                                this.checkPriceRange(curValue);

                                intCount = Math.floor(
                                    Math.round(curValue * this.precisionFactor / this.stepQuantity) / this.precisionFactor
                                ) || 1;
                                curValue = (intCount <= 1 ? this.stepQuantity : intCount * this.stepQuantity);
                                curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;

                                if (curValue < this.minQuantity) {
                                    curValue = this.minQuantity;
                                }

                                this.obQuantity.value = curValue;
                            } else {
                                this.obQuantity.value = this.minQuantity;
                            }
                        } else {
                            this.obQuantity.value = this.minQuantity;
                        }

                        this.setPrice();
                    }
                },

                quantitySet: function (index) {
                    var strLimit, resetQuantity;

                    var newOffer = this.offers[index],
                        oldOffer = this.offers[this.offerNum];

                    if (this.errorCode === 0) {
                        this.canBuy = newOffer.CAN_BUY;

                        this.currentPriceMode = newOffer.ITEM_PRICE_MODE;
                        this.currentPrices = newOffer.ITEM_PRICES;
                        this.currentPriceSelected = newOffer.ITEM_PRICE_SELECTED;
                        this.currentQuantityRanges = newOffer.ITEM_QUANTITY_RANGES;
                        this.currentQuantityRangeSelected = newOffer.ITEM_QUANTITY_RANGE_SELECTED;

                        if (this.canBuy) {
                            this.node.quantity && BX.style(this.node.quantity, 'display', '');

                            this.obBasketActions && BX.style(this.obBasketActions, 'display', '');
                            this.smallCardNodes.buyButton && BX.style(this.smallCardNodes.buyButton, 'display', '');
                            this.smallCardNodes.addButton && BX.style(this.smallCardNodes.addButton, 'display', '');

                            this.obNotAvail && BX.style(this.obNotAvail, 'display', 'none');
                            this.smallCardNodes.notAvailableButton && BX.style(this.smallCardNodes.notAvailableButton, 'display', 'none');

                            this.obSubscribe && BX.style(this.obSubscribe, 'display', 'none');
                        } else {
                            this.node.quantity && BX.style(this.node.quantity, 'display', 'none');

                            this.obBasketActions && BX.style(this.obBasketActions, 'display', 'none');
                            this.smallCardNodes.buyButton && BX.style(this.smallCardNodes.buyButton, 'display', 'none');
                            this.smallCardNodes.addButton && BX.style(this.smallCardNodes.addButton, 'display', 'none');

                            this.obNotAvail && BX.style(this.obNotAvail, 'display', '');
                            this.smallCardNodes.notAvailableButton && BX.style(this.smallCardNodes.notAvailableButton, 'display', '');

                            if (this.obSubscribe) {
                                if (newOffer.CATALOG_SUBSCRIBE === 'Y') {
                                    BX.style(this.obSubscribe, 'display', '');
                                    this.obSubscribe.setAttribute('data-item', newOffer.ID);
                                    BX(this.visual.SUBSCRIBE_LINK + '_hidden').click();
                                } else {
                                    BX.style(this.obSubscribe, 'display', 'none');
                                }
                            }
                        }

                        this.isDblQuantity = newOffer.QUANTITY_FLOAT;
                        this.checkQuantity = newOffer.CHECK_QUANTITY;

                        if (this.isDblQuantity) {
                            this.stepQuantity = Math.round(parseFloat(newOffer.STEP_QUANTITY) * this.precisionFactor) / this.precisionFactor;
                            this.maxQuantity = parseFloat(newOffer.MAX_QUANTITY);
                            this.minQuantity = this.currentPriceMode === 'Q' ? parseFloat(this.currentPrices[this.currentPriceSelected].MIN_QUANTITY) : this.stepQuantity;
                        } else {
                            this.stepQuantity = parseInt(newOffer.STEP_QUANTITY, 10);
                            this.maxQuantity = parseInt(newOffer.MAX_QUANTITY, 10);
                            this.minQuantity = this.currentPriceMode === 'Q' ? parseInt(this.currentPrices[this.currentPriceSelected].MIN_QUANTITY) : this.stepQuantity;
                        }

                        if (this.config.showQuantity) {
                            var isDifferentMinQuantity = oldOffer.ITEM_PRICES.length &&
                                oldOffer.ITEM_PRICES[oldOffer.ITEM_PRICE_SELECTED] &&
                                oldOffer.ITEM_PRICES[oldOffer.ITEM_PRICE_SELECTED].MIN_QUANTITY != this.minQuantity;

                            if (this.isDblQuantity) {
                                resetQuantity = Math.round(parseFloat(oldOffer.STEP_QUANTITY) * this.precisionFactor) / this.precisionFactor !== this.stepQuantity ||
                                    isDifferentMinQuantity ||
                                    oldOffer.MEASURE !== newOffer.MEASURE ||
                                    (
                                        this.checkQuantity &&
                                        parseFloat(oldOffer.MAX_QUANTITY) > this.maxQuantity &&
                                        parseFloat(this.obQuantity.value) > this.maxQuantity
                                    );
                            } else {
                                resetQuantity = parseInt(oldOffer.STEP_QUANTITY, 10) !== this.stepQuantity ||
                                    isDifferentMinQuantity ||
                                    oldOffer.MEASURE !== newOffer.MEASURE ||
                                    (
                                        this.checkQuantity &&
                                        parseInt(oldOffer.MAX_QUANTITY, 10) > this.maxQuantity &&
                                        parseInt(this.obQuantity.value, 10) > this.maxQuantity
                                    );
                            }

                            this.obQuantity.disabled = !this.canBuy;

                            if (resetQuantity) {
                                this.obQuantity.value = this.minQuantity;
                            }

                            if (this.obMeasure) {
                                if (newOffer.MEASURE) {
                                    BX.adjust(this.obMeasure, {html: newOffer.MEASURE});
                                } else {
                                    BX.adjust(this.obMeasure, {html: ''});
                                }
                            }
                        }

                        if (this.obQuantityLimit.all) {
                            if (!this.checkQuantity || this.maxQuantity == 0) {
                                BX.adjust(this.obQuantityLimit.value, {html: ''});
                                BX.adjust(this.obQuantityLimit.all, {style: {display: 'none'}});
                            } else {
                                if (this.config.showMaxQuantity === 'M') {
                                    strLimit = (this.maxQuantity / this.stepQuantity >= this.config.relativeQuantityFactor) ?
                                        BX.message('RELATIVE_QUANTITY_MANY') :
                                        BX.message('RELATIVE_QUANTITY_FEW');
                                } else {
                                    strLimit = this.maxQuantity;

                                    if (newOffer.MEASURE) {
                                        strLimit += (' ' + newOffer.MEASURE);
                                    }
                                }

                                BX.adjust(this.obQuantityLimit.value, {html: strLimit});
                                BX.adjust(this.obQuantityLimit.all, {style: {display: ''}});
                            }
                        }

                        if (this.config.usePriceRanges && this.obPriceRanges) {
                            if (
                                this.currentPriceMode === 'Q' &&
                                newOffer.PRICE_RANGES_HTML
                            ) {
                                var rangesBody = this.getEntity(this.obPriceRanges, 'price-ranges-body'),
                                    rangesRatioHeader = this.getEntity(this.obPriceRanges, 'price-ranges-ratio-header');

                                if (rangesBody) {
                                    rangesBody.innerHTML = newOffer.PRICE_RANGES_HTML;
                                }

                                if (rangesRatioHeader) {
                                    rangesRatioHeader.innerHTML = newOffer.PRICE_RANGES_RATIO_HTML;
                                }

                                this.obPriceRanges.style.display = '';
                            } else {
                                this.obPriceRanges.style.display = 'none';
                            }

                        }
                    }
                },

                selectOfferProp: function () {
                    var i = 0,
                        strTreeValue = '',
                        arTreeItem = [],
                        rowItems = null,
                        target = BX.proxy_context,
                        smallCardItem;

                    if (target && target.hasAttribute('data-treevalue')) {
                        if (BX.hasClass(target, 'selected'))
                            return;

                        if (typeof document.activeElement === 'object') {
                            document.activeElement.blur();
                        }

                        strTreeValue = target.getAttribute('data-treevalue');
                        arTreeItem = strTreeValue.split('_');
                        
                        this.searchOfferPropIndex(arTreeItem[0], arTreeItem[1]);
                        rowItems = BX.findChildren(target.parentNode, {tagName: 'li'}, false);

                        if (rowItems && rowItems.length) {
                            for (i = 0; i < rowItems.length; i++) {
                                BX.removeClass(rowItems[i], 'selected');
                                rowItems[i].style.display = 'none';
                            }
                        }

                        BX.addClass(target, 'selected');
                        target.style.display = '';
                        
                        if (this.smallCardNodes.panel) {
                            smallCardItem = this.smallCardNodes.panel.querySelector('[data-treevalue="' + strTreeValue + '"]');
                            if (smallCardItem) {
                                rowItems = this.smallCardNodes.panel.querySelectorAll('[data-sku-line="' + smallCardItem.getAttribute('data-sku-line') + '"]');
                                for (i = 0; i < rowItems.length; i++) {
                                    rowItems[i].style.display = 'none';
                                }

                                smallCardItem.style.display = '';
                            }
                        }
                    }
                },

                searchOfferPropIndex: function (strPropID, strPropValue) {
                    var strName = '',
                        arShowValues = false,
                        arCanBuyValues = [],
                        allValues = [],
                        index = -1,
                        i, j,
                        arFilter = {},
                        tmpFilter = [];

                    for (i = 0; i < this.treeProps.length; i++) {
                        if (this.treeProps[i].ID === strPropID) {
                            index = i;
                            break;
                        }
                    }

                    if (index > -1) {
                        for (i = 0; i < index; i++) {
                            strName = 'PROP_' + this.treeProps[i].ID;
                            arFilter[strName] = this.selectedValues[strName];
                        }

                        strName = 'PROP_' + this.treeProps[index].ID;
                        arFilter[strName] = strPropValue;

                        for (i = index + 1; i < this.treeProps.length; i++) {
                            strName = 'PROP_' + this.treeProps[i].ID;
                            arShowValues = this.getRowValues(arFilter, strName);

                            if (!arShowValues)
                                break;

                            allValues = [];

                            if (this.config.showAbsent) {
                                arCanBuyValues = [];
                                tmpFilter = [];
                                tmpFilter = BX.clone(arFilter, true);

                                for (j = 0; j < arShowValues.length; j++) {
                                    tmpFilter[strName] = arShowValues[j];
                                    allValues[allValues.length] = arShowValues[j];
                                    if (this.getCanBuy(tmpFilter))
                                        arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
                                }
                            } else {
                                arCanBuyValues = arShowValues;
                            }

                            if (this.selectedValues[strName] && BX.util.in_array(this.selectedValues[strName], arCanBuyValues)) {
                                arFilter[strName] = this.selectedValues[strName];
                            } else {
                                if (this.config.showAbsent) {
                                    arFilter[strName] = (arCanBuyValues.length ? arCanBuyValues[0] : allValues[0]);
                                } else {
                                    arFilter[strName] = arCanBuyValues[0];
                                }
                            }

                            this.updateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
                        }

                        this.selectedValues = arFilter;
                        this.changeInfo();
                    }
                },

                updateRow: function (intNumber, activeId, showId, canBuyId) {
                    var i = 0,
                        value = '',
                        isCurrent = false,
                        rowItems = null;

                    var lineContainer = this.getEntities(this.obTree, 'sku-line-block');

                    if (intNumber > -1 && intNumber < lineContainer.length) {
                        rowItems = lineContainer[intNumber].querySelectorAll('li');
                        for (i = 0; i < rowItems.length; i++) {
                            value = rowItems[i].getAttribute('data-onevalue');
                            isCurrent = value === activeId;
                    
                            if (isCurrent) {
                                BX.addClass(rowItems[i], 'selected');
                                rowItems[i].style.display = '';

                            } else {
                                BX.removeClass(rowItems[i], 'selected');
                                rowItems[i].style.display = 'none';
                            }

                            if (BX.util.in_array(value, canBuyId)) {
                                BX.removeClass(rowItems[i], 'notallowed');
                            } else {
                                BX.addClass(rowItems[i], 'notallowed');
                            }

                            // rowItems[i].style.display = BX.util.in_array(value, showId) ? '' : 'none';

                            if (isCurrent) {
                                lineContainer[intNumber].style.display = (value == 0 && canBuyId.length == 1) ? 'none' : '';
                            }
                        }

                        if (this.smallCardNodes.panel) {
                            rowItems = this.smallCardNodes.panel.querySelectorAll('[data-sku-line="' + intNumber + '"]');
                            for (i = 0; i < rowItems.length; i++) {
                                value = rowItems[i].getAttribute('data-onevalue');
                                isCurrent = value === activeId;

                                if (isCurrent) {
                                    rowItems[i].style.display = '';
                                } else {
                                    rowItems[i].style.display = 'none';
                                }

                                if (BX.util.in_array(value, canBuyId)) {
                                    BX.removeClass(rowItems[i], 'notallowed');
                                } else {
                                    BX.addClass(rowItems[i], 'notallowed');
                                }

                                if (isCurrent) {
                                    rowItems[i].style.display = (value == 0 && canBuyId.length == 1) ? 'none' : '';
                                }
                            }
                        }
                    }
                },

                getRowValues: function (arFilter, index) {
                    var arValues = [],
                        i = 0,
                        j = 0,
                        boolSearch = false,
                        boolOneSearch = true;

                    if (arFilter.length === 0) {
                        for (i = 0; i < this.offers.length; i++) {
                            if (!BX.util.in_array(this.offers[i].TREE[index], arValues)) {
                                arValues[arValues.length] = this.offers[i].TREE[index];
                            }
                        }
                        boolSearch = true;
                    } else {
                        for (i = 0; i < this.offers.length; i++) {
                            boolOneSearch = true;

                            for (j in arFilter) {
                                if (arFilter[j] !== this.offers[i].TREE[j]) {
                                    boolOneSearch = false;
                                    break;
                                }
                            }

                            if (boolOneSearch) {
                                if (!BX.util.in_array(this.offers[i].TREE[index], arValues)) {
                                    arValues[arValues.length] = this.offers[i].TREE[index];
                                }

                                boolSearch = true;
                            }
                        }
                    }

                    return (boolSearch ? arValues : false);
                },

                getCanBuy: function (arFilter) {
                    var i,
                        j = 0,
                        boolOneSearch = true,
                        boolSearch = false;

                    for (i = 0; i < this.offers.length; i++) {
                        boolOneSearch = true;

                        for (j in arFilter) {
                            if (arFilter[j] !== this.offers[i].TREE[j]) {
                                boolOneSearch = false;
                                break;
                            }
                        }

                        if (boolOneSearch) {
                            if (this.offers[i].CAN_BUY) {
                                boolSearch = true;
                                break;
                            }
                        }
                    }

                    return boolSearch;
                },

                setCurrent: function () {
                    var i,
                        j = 0,
                        strName = '',
                        arShowValues = false,
                        arCanBuyValues = [],
                        arFilter = {},
                        tmpFilter = [],
                        current = this.offers[this.offerNum].TREE;                        

                    for (i = 0; i < this.treeProps.length; i++) {
                        strName = 'PROP_' + this.treeProps[i].ID;
                        arShowValues = this.getRowValues(arFilter, strName);

                        if (!arShowValues)
                            break;

                        if (BX.util.in_array(current[strName], arShowValues)) {
                            arFilter[strName] = current[strName];
                        } else {
                            arFilter[strName] = arShowValues[0];
                            this.offerNum = 0;
                        }

                        if (this.config.showAbsent) {
                            arCanBuyValues = [];
                            tmpFilter = [];
                            tmpFilter = BX.clone(arFilter, true);

                            for (j = 0; j < arShowValues.length; j++) {
                                tmpFilter[strName] = arShowValues[j];

                                if (this.getCanBuy(tmpFilter)) {
                                    arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
                                }
                            }
                        } else {
                            arCanBuyValues = arShowValues;
                        }

                        this.updateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
                    }

                    this.selectedValues = arFilter;
                    this.changeInfo();
                },

                changeInfo: function () {
                    var index = -1,
                        j = 0,
                        boolOneSearch = true,
                        eventData = {
                            currentId: (this.offerNum > -1 ? this.offers[this.offerNum].ID : 0),
                            newId: 0
                        };

                    var i, offerGroupNode;

                    for (i = 0; i < this.offers.length; i++) {
                        boolOneSearch = true;

                        for (j in this.selectedValues) {
                            if (this.selectedValues[j] !== this.offers[i].TREE[j]) {
                                boolOneSearch = false;
                                break;
                            }
                        }

                        if (boolOneSearch) {
                            index = i;
                            break;
                        }
                    }

                    if (index > -1) {
                        if (index != this.offerNum) {
                            this.isGift = false;
                        }

                        this.drawImages(this.offers[index].SLIDER);
                        this.checkSliderControls(this.offers[index].SLIDER_COUNT);

                        for (i = 0; i < this.offers.length; i++) {
                            if (this.config.showOfferGroup && this.offers[i].OFFER_GROUP) {
                                if (offerGroupNode = BX(this.visual.OFFER_GROUP + this.offers[i].ID)) {
                                    offerGroupNode.style.display = (i == index ? '' : 'none');
                                }
                            }

                            if (this.slider.controls[i].ID) {
                                if (i === index) {
                                    this.product.slider = this.slider.controls[i];
                                    this.slider.controls[i].CONT && BX.show(this.slider.controls[i].CONT);
                                } else {
                                    this.slider.controls[i].CONT && BX.hide(this.slider.controls[i].CONT);
                                }
                            } else if (i === index) {
                                this.product.slider = {};
                            }
                        }

                        if (this.offers[index].SLIDER_COUNT > 0) {
                            this.setMainPict(this.offers[index].ID, this.offers[index].SLIDER[0].ID, true);
                        } else {
                            this.setMainPictFromItem(index);
                        }

                        if (this.offers[index].SLIDER_COUNT > 1) {
                            this.initSlider();
                        } else {
                            this.stopSlider();
                        }

                        if (this.obDescription && this.config.showSkuDescription === 'Y') {
                            this.changeSkuDescription(index);
                        }

                        if (this.config.showSkuProps) {
                            if (this.obSkuProps) {
                                if (!this.offers[index].DISPLAY_PROPERTIES) {
                                    BX.adjust(this.obSkuProps, {style: {display: 'none'}, html: ''});
                                } else {
                                    BX.adjust(this.obSkuProps, {
                                        style: {display: ''},
                                        html: this.offers[index].DISPLAY_PROPERTIES
                                    });
                                }
                            }

                            if (this.obMainSkuProps) {
                                if (!this.offers[index].DISPLAY_PROPERTIES_MAIN_BLOCK) {
                                    BX.adjust(this.obMainSkuProps, {style: {display: 'none'}, html: ''});
                                } else {
                                    BX.adjust(this.obMainSkuProps, {
                                        style: {display: ''},
                                        html: this.offers[index].DISPLAY_PROPERTIES_MAIN_BLOCK
                                    });
                                }
                            }
                        }

                        this.quantitySet(index);
                        this.setPrice();
                        this.setCompared(this.offers[index].COMPARED);

                this.offerNum = index;
                this.fixFontCheck();
                this.setAnalyticsDataLayer('showDetail');
                this.incViewedCounter();
                //блок аксессуары, добавлен id оффера
                eventData.newId = this.offers[this.offerNum].ID;
				//реализовываем логику подобную, когда активен аксессуар, тогда цену ставим в зависимости от оффера
				var price = 0;
				var productPrice = $('.product-view .prices_block .js_price_wrapper .price').attr('data-value');
				$(".equipment-block li[data-product="+ this.config.productID + "]").attr('data-item', eventData.newId);//ставим вместо id товара Id Offerа, чтобы в корзину добавлялся оффер (иначе ошибка)
                $(".equipment-block li").each(function(index, el) {
                    if($(this).is('.active')){
						price += parseInt($(el).attr('data-price'));
                    }
                });
				//вставляем цену
				// $('.product-view .prices_block .prices-body .price:not(.discount) .price_value').html((Number(productPrice) + Number(price)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " "));
				$('.product-view .equipment-block .equipment-block-summ-cost span').html('+ ' + price + ' ₽');
                // only for compatible catalog.store.amount custom templates
                BX.onCustomEvent('onCatalogStoreProductChange', [this.offers[this.offerNum].ID]);
                // new event
                BX.onCustomEvent('onCatalogElementChangeOffer', [eventData]);
                eventData = null;
            }
        },
        changeSkuDescription: function(index) {
            var currentDetailText = '';
            var currentPreviewText = '';
            var currentDescription = '';

                    if (this.offers[index].DETAIL_TEXT !== '') {
                        currentDetailText = this.offers[index].DETAIL_TEXT_TYPE === 'html' ? this.offers[index].DETAIL_TEXT : '<p>' + this.offers[index].DETAIL_TEXT + '</p>';
                    } else if (this.product.detailText !== '') {
                        currentDetailText = this.product.detailTextType === 'html' ? this.product.detailText : '<p>' + this.product.detailText + '</p>';
                    }

                    if (this.offers[index].PREVIEW_TEXT !== '') {
                        currentPreviewText = this.offers[index].PREVIEW_TEXT_TYPE === 'html' ? this.offers[index].PREVIEW_TEXT : '<p>' + this.offers[index].PREVIEW_TEXT + '</p>';
                    } else if (this.product.previewText !== '') {
                        currentPreviewText = this.product.previewTextType === 'html' ? this.product.previewText : '<p>' + this.product.previewText + '</p>';
                    }

                    if (
                        currentPreviewText !== '' &&
                        (
                            this.config.displayPreviewTextMode === 'S' ||
                            (this.config.displayPreviewTextMode === 'E' && currentDetailText)
                        )
                    ) {
                        currentDescription += currentPreviewText;
                    }
                    if (currentDetailText !== '') {
                        currentDescription += currentDetailText;
                    }
                    BX.adjust(this.obDescription, {html: currentDescription});
                },

                drawImages: function (images) {
                    if (!this.node.imageContainer)
                        return;

                    var i, img, entities = this.getEntities(this.node.imageContainer, 'image');
                    for (i in entities) {
                        if (entities.hasOwnProperty(i) && BX.type.isDomNode(entities[i])) {
                            BX.remove(entities[i]);
                        }
                    }

                    for (i = 0; i < images.length; i++) {
                        img = BX.create('IMG', {
                            props: {
                                src: images[i].SRC,
                                alt: this.config.alt,
                                title: this.config.title
                            }
                        });

                        if (i == 0) {
                            img.setAttribute('itemprop', 'image');
                        }

                        this.node.imageContainer.appendChild(
                            BX.create('DIV', {
                                attrs: {
                                    'data-entity': 'image',
                                    'data-id': images[i].ID
                                },
                                props: {
                                    className: 'product-item-detail-slider-image' + (i == 0 ? ' active' : '')
                                },
                                children: [img]
                            })
                        );
                    }
                },

                restoreSticker: function () {
                    if (this.previousStickerText) {
                        this.redrawSticker({text: this.previousStickerText});
                    } else {
                        this.hideSticker();
                    }
                },

                hideSticker: function () {
                    BX.hide(BX(this.visual.STICKER_ID));
                },

                redrawSticker: function (stickerData) {
                    stickerData = stickerData || {};
                    var text = stickerData.text || '';

                    var sticker = BX(this.visual.STICKER_ID);
                    if (!sticker)
                        return;

                    BX.show(sticker);

                    var previousStickerText = sticker.getAttribute('title');
                    if (previousStickerText && previousStickerText != text) {
                        this.previousStickerText = previousStickerText;
                    }

                    BX.adjust(sticker, {text: text, attrs: {title: text}});
                },

                checkQuantityRange: function (quantity, direction) {
                    if (typeof quantity === 'undefined' || this.currentPriceMode !== 'Q') {
                        return quantity;
                    }

                    quantity = parseFloat(quantity);

                    var nearestQuantity = quantity;
                    var range, diffFrom, absDiffFrom, diffTo, absDiffTo, shortestDiff;

                    for (var i in this.currentQuantityRanges) {
                        if (this.currentQuantityRanges.hasOwnProperty(i)) {
                            range = this.currentQuantityRanges[i];

                            if (
                                parseFloat(quantity) >= parseFloat(range.SORT_FROM) &&
                                (
                                    range.SORT_TO === 'INF' ||
                                    parseFloat(quantity) <= parseFloat(range.SORT_TO)
                                )
                            ) {
                                nearestQuantity = quantity;
                                break;
                            } else {
                                diffFrom = parseFloat(range.SORT_FROM) - quantity;
                                absDiffFrom = Math.abs(diffFrom);
                                diffTo = parseFloat(range.SORT_TO) - quantity;
                                absDiffTo = Math.abs(diffTo);

                                if (shortestDiff === undefined || shortestDiff > absDiffFrom) {
                                    if (
                                        direction === undefined ||
                                        (direction === 'up' && diffFrom > 0) ||
                                        (direction === 'down' && diffFrom < 0)
                                    ) {
                                        shortestDiff = absDiffFrom;
                                        nearestQuantity = parseFloat(range.SORT_FROM);
                                    }
                                }

                                if (shortestDiff === undefined || shortestDiff > absDiffTo) {
                                    if (
                                        direction === undefined ||
                                        (direction === 'up' && diffFrom > 0) ||
                                        (direction === 'down' && diffFrom < 0)
                                    ) {
                                        shortestDiff = absDiffTo;
                                        nearestQuantity = parseFloat(range.SORT_TO);
                                    }
                                }
                            }
                        }
                    }

                    return nearestQuantity;
                },

                checkPriceRange: function (quantity) {
                    if (typeof quantity === 'undefined' || this.currentPriceMode !== 'Q') {
                        return;
                    }

                    var range, found = false;

                    for (var i in this.currentQuantityRanges) {
                        if (this.currentQuantityRanges.hasOwnProperty(i)) {
                            range = this.currentQuantityRanges[i];

                            if (
                                parseFloat(quantity) >= parseFloat(range.SORT_FROM) &&
                                (
                                    range.SORT_TO === 'INF' ||
                                    parseFloat(quantity) <= parseFloat(range.SORT_TO)
                                )
                            ) {
                                found = true;
                                this.currentQuantityRangeSelected = range.HASH;
                                break;
                            }
                        }
                    }

                    if (!found && (range = this.getMinPriceRange())) {
                        this.currentQuantityRangeSelected = range.HASH;
                    }

                    for (var k in this.currentPrices) {
                        if (this.currentPrices.hasOwnProperty(k)) {
                            if (this.currentPrices[k].QUANTITY_HASH == this.currentQuantityRangeSelected) {
                                this.currentPriceSelected = k;
                                break;
                            }
                        }
                    }
                },

                getMinPriceRange: function () {
                    var range;

                    for (var i in this.currentQuantityRanges) {
                        if (this.currentQuantityRanges.hasOwnProperty(i)) {
                            if (!range ||
                                parseInt(this.currentQuantityRanges[i].SORT_FROM) < parseInt(range.SORT_FROM)
                            ) {
                                range = this.currentQuantityRanges[i];
                            }
                        }
                    }

                    return range;
                },

                checkQuantityControls: function () {
                    if (!this.obQuantity)
                        return;

                    var reachedTopLimit = this.checkQuantity && parseFloat(this.obQuantity.value) + this.stepQuantity > this.maxQuantity,
                        reachedBottomLimit = parseFloat(this.obQuantity.value) - this.stepQuantity < this.minQuantity;

                    if (reachedTopLimit) {
                        BX.addClass(this.obQuantityUp, 'product-item-amount-field-btn-disabled');
                    } else if (BX.hasClass(this.obQuantityUp, 'product-item-amount-field-btn-disabled')) {
                        BX.removeClass(this.obQuantityUp, 'product-item-amount-field-btn-disabled');
                    }

                    if (reachedBottomLimit) {
                        BX.addClass(this.obQuantityDown, 'product-item-amount-field-btn-disabled');
                    } else if (BX.hasClass(this.obQuantityDown, 'product-item-amount-field-btn-disabled')) {
                        BX.removeClass(this.obQuantityDown, 'product-item-amount-field-btn-disabled');
                    }

                    if (reachedTopLimit && reachedBottomLimit) {
                        this.obQuantity.setAttribute('disabled', 'disabled');
                    } else {
                        this.obQuantity.removeAttribute('disabled');
                    }
                },

                setPrice: function () {
                    var economyInfo = '',
                        price;

                    if (this.obQuantity) {
                        this.checkPriceRange(this.obQuantity.value);
                    }

                    this.checkQuantityControls();

                    price = this.currentPrices[this.currentPriceSelected];

                    if (this.isGift) {
                        price.PRICE = 0;
                        price.DISCOUNT = price.BASE_PRICE;
                        price.PERCENT = 100;
                    }

                    if (this.obPrice.price) {
                        // console.log('setPrice', 'offerID=' + this.offers[this.offerNum].ID, $(this.obPrice, price));
                        //получаем цену продукта и при переключении в аксеccуарах добавляем цену
                        var productPriceCurrent = $('.product-view .prices_block .js_price_wrapper .price').attr('data-value');
                        //установим цену у оффера, чтобы ее вставить при ключении
                        $(".equipment-block").attr('data-price-' + this.config.productID, price.BASE_PRICE);
                        //вставляем здесь и id Оффера, и берем цену ниже
                        $(".equipment-block li[data-product=" + this.config.productID + "]").attr('data-item', this.offers[this.offerNum].ID);
                        $(".equipment-block li[data-item=" + this.offers[this.offerNum].ID + "]").attr('data-price', price.RATIO_PRICE);
                        var servicePriceAssemblyPercent = $('.service-module-price-assembly').attr('data-service-percent');
                        if (Number(servicePriceAssemblyPercent) > 0) {
                            var servicePriceAssembly = servicePriceAssemblyPercent / 100 * price.BASE_PRICE;
                            if (servicePriceAssembly < 1499) servicePriceAssembly = 1499;
                            $(".default_service.custom_service_chekbox").attr('data-price', servicePriceAssembly);
                            $(".default_service .module-price-accessory").html(servicePriceAssembly);
                            $(".equipment-block li[data-item=" + this.offers[this.offerNum].ID + "]").attr('data-price-assembly-mod', servicePriceAssembly);
                        }
                        var servicePriceAssemblyPercentUgrent = $('.service-module-urgent-price-assembly').attr('data-service-percent');
                        if (Number(servicePriceAssemblyPercentUgrent) > 0) {
                            var servicePriceAssemblyUgrent = servicePriceAssemblyPercentUgrent / 100 * price.BASE_PRICE;
                            if (servicePriceAssemblyUgrent < 2499) servicePriceAssemblyUgrent = 2499;
                            $(".ugrent_service.custom_service_chekbox").attr('data-price', servicePriceAssemblyUgrent);
                            $(".ugrent_service  .module-price-accessory").html(servicePriceAssemblyUgrent);
                            $(".equipment-block li[data-item=" + this.offers[this.offerNum].ID + "]").attr('data-price-assembly-mod', servicePriceAssemblyUgrent);
                        }
                        $(".equipment-block li").each(function (index, el) {
                            if ($(this).is('.active')) {
                                $('.equipment-block-body .equipment-block-summ-cost span').html('+ ' + price.BASE_PRICE + ' ₽');
                            }
                        });
                        if (price) {
                            BX.adjust(this.obPrice.price, {html: BX.Currency.currencyFormat(price.RATIO_PRICE, price.CURRENCY, true)});
                            this.smallCardNodes.price && BX.adjust(this.smallCardNodes.price, {
                                html: BX.Currency.currencyFormat(price.RATIO_PRICE, price.CURRENCY, true)
                            });
                        } else {
                            BX.adjust(this.obPrice.price, {html: ''});
                            this.smallCardNodes.price && BX.adjust(this.smallCardNodes.price, {html: ''});
                        }

                        if (price && price.RATIO_PRICE !== price.RATIO_BASE_PRICE) {
                            if (this.config.showOldPrice) {
                                this.obPrice.full && BX.adjust(this.obPrice.full, {
                                    style: {display: ''},
                                    html: BX.Currency.currencyFormat(price.RATIO_BASE_PRICE, price.CURRENCY, true)
                                });
                                this.smallCardNodes.oldPrice && BX.adjust(this.smallCardNodes.oldPrice, {
                                    style: {display: ''},
                                    html: BX.Currency.currencyFormat(price.RATIO_BASE_PRICE, price.CURRENCY, true)
                                });

                                if (this.obPrice.discount) {
                                    economyInfo = BX.message('ECONOMY_INFO_MESSAGE');
                                    economyInfo = economyInfo.replace('#ECONOMY#', BX.Currency.currencyFormat(price.RATIO_DISCOUNT, price.CURRENCY, true));
                                    BX.adjust(this.obPrice.discount, {style: {display: ''}, html: economyInfo});
                                }
                            }

                            if (this.config.showPercent) {
                                this.obPrice.percent && BX.adjust(this.obPrice.percent, {
                                    style: {display: ''},
                                    html: -price.PERCENT + '%'
                                });
                            }
                        } else {
                            if (this.config.showOldPrice) {
                                this.obPrice.full && BX.adjust(this.obPrice.full, {style: {display: 'none'}, html: ''});
                                this.smallCardNodes.oldPrice && BX.adjust(this.smallCardNodes.oldPrice, {
                                    style: {display: 'none'},
                                    html: ''
                                });
                                this.obPrice.discount && BX.adjust(this.obPrice.discount, {
                                    style: {display: 'none'},
                                    html: ''
                                });
                            }

                            if (this.config.showPercent) {
                                this.obPrice.percent && BX.adjust(this.obPrice.percent, {
                                    style: {display: 'none'},
                                    html: ''
                                });
                            }
                        }

                        if (this.obPrice.total) {
                            if (price && this.obQuantity && this.obQuantity.value != this.stepQuantity) {
                                BX.adjust(this.obPrice.total, {
                                    html: BX.message('PRICE_TOTAL_PREFIX') + ' <strong>' +
                                        BX.Currency.currencyFormat(price.PRICE * this.obQuantity.value, price.CURRENCY, true) +
                                        '</strong>',
                                    style: {display: ''}
                                });
                            } else {
                                BX.adjust(this.obPrice.total, {
                                    html: '',
                                    style: {display: 'none'}
                                });
                            }
                        }
                    }
                },

                compare: function (event) {
                    var checkbox = this.obCompare.querySelector('[data-entity="compare-checkbox"]'),
                        target = BX.getEventTarget(event),
                        checked = true;

                    if (checkbox) {
                        checked = target === checkbox ? checkbox.checked : !checkbox.checked;
                    }

                    var url = checked ? this.compareData.compareUrl : this.compareData.compareDeleteUrl,
                        compareLink;

                    if (url) {
                        if (target !== checkbox) {
                            BX.PreventDefault(event);
                            this.setCompared(checked);
                        }

                        switch (this.productType) {
                            case 0: // no catalog
                            case 1: // product
                            case 2: // set
                                compareLink = url.replace('#ID#', this.product.id.toString());
                                break;
                            case 3: // sku
                                compareLink = url.replace('#ID#', this.offers[this.offerNum].ID);
                                break;
                        }

                        BX.ajax({
                            method: 'POST',
                            dataType: checked ? 'json' : 'html',
                            url: compareLink + (compareLink.indexOf('?') !== -1 ? '&' : '?') + 'ajax_action=Y',
                            onsuccess: checked ?
                                BX.proxy(this.compareResult, this) : BX.proxy(this.compareDeleteResult, this)
                        });
                    }
                },

                compareResult: function (result) {
                    var popupContent, popupButtons;

                    if (this.obPopupWin) {
                        this.obPopupWin.close();
                    }

                    if (!BX.type.isPlainObject(result))
                        return;

                    this.initPopupWindow();

                    if (this.offers.length > 0) {
                        this.offers[this.offerNum].COMPARED = result.STATUS === 'OK';
                    }

                    if (result.STATUS === 'OK') {
                        BX.onCustomEvent('OnCompareChange');

                        popupContent = '<div style="width: 100%; margin: 0; text-align: center;"><p>' +
                            BX.message('COMPARE_MESSAGE_OK') +
                            '</p></div>';

                        if (this.config.showClosePopup) {
                            popupButtons = [
                                new BasketButton({
                                    text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
                                    events: {
                                        click: BX.delegate(this.compareRedirect, this)
                                    },
                                    style: {marginRight: '10px'}
                                }),
                                new BasketButton({
                                    text: BX.message('BTN_MESSAGE_CLOSE_POPUP'),
                                    events: {
                                        click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
                                    }
                                })
                            ];
                        } else {
                            popupButtons = [
                                new BasketButton({
                                    text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
                                    events: {
                                        click: BX.delegate(this.compareRedirect, this)
                                    }
                                })
                            ];
                        }
                    } else {
                        popupContent = '<div style="width: 100%; margin: 0; text-align: center;"><p>' +
                            (result.MESSAGE ? result.MESSAGE : BX.message('COMPARE_UNKNOWN_ERROR')) +
                            '</p></div>';
                        popupButtons = [
                            new BasketButton({
                                text: BX.message('BTN_MESSAGE_CLOSE'),
                                events: {
                                    click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
                                }
                            })
                        ];
                    }

                    this.obPopupWin.setTitleBar(BX.message('COMPARE_TITLE'));
                    this.obPopupWin.setContent(popupContent);
                    this.obPopupWin.setButtons(popupButtons);
                    this.obPopupWin.show();
                },

                compareDeleteResult: function () {
                    BX.onCustomEvent('OnCompareChange');

                    if (this.offers && this.offers.length) {
                        this.offers[this.offerNum].COMPARED = false;
                    }
                },

                setCompared: function (state) {
                    if (!this.obCompare)
                        return;

                    var checkbox = this.getEntity(this.obCompare, 'compare-checkbox');
                    if (checkbox) {
                        checkbox.checked = state;
                    }
                },

                setCompareInfo: function (comparedIds) {
                    if (!BX.type.isArray(comparedIds))
                        return;

                    for (var i in this.offers) {
                        if (this.offers.hasOwnProperty(i)) {
                            this.offers[i].COMPARED = BX.util.in_array(this.offers[i].ID, comparedIds);
                        }
                    }
                },

                compareRedirect: function () {
                    if (this.compareData.comparePath) {
                        location.href = this.compareData.comparePath;
                    } else {
                        this.obPopupWin.close();
                    }
                },

                checkDeletedCompare: function (id) {
                    switch (this.productType) {
                        case 0: // no catalog
                        case 1: // product
                        case 2: // set
                            if (this.product.id == id) {
                                this.setCompared(false);
                            }

                            break;
                        case 3: // sku
                            var i = this.offers.length;
                            while (i--) {
                                if (this.offers[i].ID == id) {
                                    this.offers[i].COMPARED = false;

                                    if (this.offerNum == i) {
                                        this.setCompared(false);
                                    }

                                    break;
                                }
                            }
                    }
                },

                initBasketUrl: function () {
                    this.basketUrl = (this.basketMode === 'ADD' ? this.basketData.add_url : this.basketData.buy_url);

                    switch (this.productType) {
                        case 1: // product
                        case 2: // set
                            this.basketUrl = this.basketUrl.replace('#ID#', this.product.id.toString());
                            break;
                        case 3: // sku
                            this.basketUrl = this.basketUrl.replace('#ID#', this.offers[this.offerNum].ID);
                            break;
                    }

                    this.basketParams = {
                        'ajax_basket': 'Y'
                    };

                    if (this.config.showQuantity) {
                        this.basketParams[this.basketData.quantity] = this.obQuantity.value;
                    }

                    if (this.basketData.sku_props) {
                        this.basketParams[this.basketData.sku_props_var] = this.basketData.sku_props;
                    }
                },

                fillBasketProps: function () {
                    if (!this.visual.BASKET_PROP_DIV)
                        return;

                    var
                        i = 0,
                        propCollection = null,
                        foundValues = false,
                        obBasketProps = null;

                    if (this.basketData.useProps && !this.basketData.emptyProps) {
                        if (this.obPopupWin && this.obPopupWin.contentContainer) {
                            obBasketProps = this.obPopupWin.contentContainer;
                        }
                    } else {
                        obBasketProps = BX(this.visual.BASKET_PROP_DIV);
                    }

                    if (obBasketProps) {
                        propCollection = obBasketProps.getElementsByTagName('select');
                        if (propCollection && propCollection.length) {
                            for (i = 0; i < propCollection.length; i++) {
                                if (!propCollection[i].disabled) {
                                    switch (propCollection[i].type.toLowerCase()) {
                                        case 'select-one':
                                            this.basketParams[propCollection[i].name] = propCollection[i].value;
                                            foundValues = true;
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                        }

                        propCollection = obBasketProps.getElementsByTagName('input');
                        if (propCollection && propCollection.length) {
                            for (i = 0; i < propCollection.length; i++) {
                                if (!propCollection[i].disabled) {
                                    switch (propCollection[i].type.toLowerCase()) {
                                        case 'hidden':
                                            this.basketParams[propCollection[i].name] = propCollection[i].value;
                                            foundValues = true;
                                            break;
                                        case 'radio':
                                            if (propCollection[i].checked) {
                                                this.basketParams[propCollection[i].name] = propCollection[i].value;
                                                foundValues = true;
                                            }
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                        }
                    }

                    if (!foundValues) {
                        this.basketParams[this.basketData.props] = [];
                        this.basketParams[this.basketData.props][0] = 0;
                    }
                },

                sendToBasket: function () {
                    if (!this.canBuy)
                        return;

                    this.initBasketUrl();
                    this.fillBasketProps();
                    BX.ajax({
                        method: 'POST',
                        dataType: 'json',
                        url: this.basketUrl,
                        data: this.basketParams,
                        onsuccess: BX.proxy(this.basketResult, this)
                    });
                },

                add2Basket: function () {
                    this.basketMode = 'ADD';
                    this.basket();
                },

                buyBasket: function () {
                    this.basketMode = 'BUY';
                    this.basket();
                },

                basket: function () {
                    var contentBasketProps = '';

                    if (!this.canBuy)
                        return;

                    switch (this.productType) {
                        case 1: // product
                        case 2: // set
                            if (this.basketData.useProps && !this.basketData.emptyProps) {
                                this.initPopupWindow();
                                this.obPopupWin.setTitleBar(BX.message('TITLE_BASKET_PROPS'));

                                if (BX(this.visual.BASKET_PROP_DIV)) {
                                    contentBasketProps = BX(this.visual.BASKET_PROP_DIV).innerHTML;
                                }

                                this.obPopupWin.setContent(contentBasketProps);
                                this.obPopupWin.setButtons([
                                    new BasketButton({
                                        text: BX.message('BTN_SEND_PROPS'),
                                        events: {
                                            click: BX.delegate(this.sendToBasket, this)
                                        }
                                    })
                                ]);
                                this.obPopupWin.show();
                            } else {
                                this.sendToBasket();
                            }
                            break;
                        case 3: // sku
                            this.sendToBasket();
                            break;
                    }
                },

                basketResult: function (arResult) {
                    var popupContent, popupButtons, productPict;

                    if (this.obPopupWin) {
                        this.obPopupWin.close();
                    }

                    if (!BX.type.isPlainObject(arResult))
                        return;

                    if (arResult.STATUS === 'OK') {
                        this.setAnalyticsDataLayer('addToCart');
                    }

                    if (arResult.STATUS === 'OK' && this.basketMode === 'BUY') {
                        this.basketRedirect();
                    } else {
                        this.initPopupWindow();

                        if (arResult.STATUS === 'OK') {
                            BX.onCustomEvent('OnBasketChange');
                            switch (this.productType) {
                                case 1: // product
                                case 2: // set
                                    productPict = this.product.pict.SRC;
                                    break;
                                case 3: // sku
                                    productPict = this.offers[this.offerNum].PREVIEW_PICTURE ?
                                        this.offers[this.offerNum].PREVIEW_PICTURE.SRC :
                                        this.defaultPict.pict.SRC;
                                    break;
                            }

                            popupContent = '<div style="width: 100%; margin: 0; text-align: center;">' +
                                '<img src="' + productPict + '" height="130" style="max-height:130px"><p>' +
                                this.product.name + '</p></div>';

                            if (this.config.showClosePopup) {
                                popupButtons = [
                                    new BasketButton({
                                        text: BX.message('BTN_MESSAGE_BASKET_REDIRECT'),
                                        events: {
                                            click: BX.delegate(this.basketRedirect, this)
                                        },
                                        style: {marginRight: '10px'}
                                    }),
                                    new BasketButton({
                                        text: BX.message('BTN_MESSAGE_CLOSE_POPUP'),
                                        events: {
                                            click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
                                        }
                                    })
                                ];
                            } else {
                                popupButtons = [
                                    new BasketButton({
                                        text: BX.message('BTN_MESSAGE_BASKET_REDIRECT'),
                                        events: {
                                            click: BX.delegate(this.basketRedirect, this)
                                        }
                                    })
                                ];
                            }
                        } else {
                            popupContent = '<div style="width: 100%; margin: 0; text-align: center;"><p>' +
                                (arResult.MESSAGE ? arResult.MESSAGE : BX.message('BASKET_UNKNOWN_ERROR')) +
                                '</p></div>';
                            popupButtons = [
                                new BasketButton({
                                    text: BX.message('BTN_MESSAGE_CLOSE'),
                                    events: {
                                        click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
                                    }
                                })
                            ];
                        }

                        this.obPopupWin.setTitleBar(arResult.STATUS === 'OK' ? BX.message('TITLE_SUCCESSFUL') : BX.message('TITLE_ERROR'));
                        this.obPopupWin.setContent(popupContent);
                        this.obPopupWin.setButtons(popupButtons);
                        this.obPopupWin.show();
                    }
                },

                basketRedirect: function () {
                    location.href = (this.basketData.basketUrl ? this.basketData.basketUrl : BX.message('BASKET_URL'));
                },

                initPopupWindow: function () {
                    if (this.obPopupWin)
                        return;

                    this.obPopupWin = BX.PopupWindowManager.create('CatalogElementBasket_' + this.visual.ID, null, {
                        autoHide: false,
                        offsetLeft: 0,
                        offsetTop: 0,
                        overlay: true,
                        closeByEsc: true,
                        titleBar: true,
                        closeIcon: true,
                        contentColor: 'white',
                        className: this.config.templateTheme ? 'bx-' + this.config.templateTheme : ''
                    });
                },

                incViewedCounter: function () {
                    if (this.currentIsSet && !this.updateViewedCount) {
                        switch (this.productType) {
                            case 1:
                            case 2:
                                this.viewedCounter.params.PRODUCT_ID = this.product.id;
                                this.viewedCounter.params.PARENT_ID = this.product.id;
                                break;
                            case 3:
                                this.viewedCounter.params.PARENT_ID = this.product.id;
                                this.viewedCounter.params.PRODUCT_ID = this.offers[this.offerNum].ID;
                                break;
                            default:
                                return;
                        }

                        this.viewedCounter.params.SITE_ID = BX.message('SITE_ID');
                        this.updateViewedCount = true;
                        BX.ajax.post(
                            this.viewedCounter.path,
                            this.viewedCounter.params,
                            BX.delegate(function () {
                                this.updateViewedCount = false;
                            }, this)
                        );
                    }
                },

                allowViewedCount: function (update) {
                    this.currentIsSet = true;

                    if (update) {
                        this.incViewedCounter();
                    }
                },

                fixFontCheck: function () {
                    if (BX.type.isDomNode(this.obPrice.price)) {
                        BX.FixFontSize && BX.FixFontSize.init({
                            objList: [{
                                node: this.obPrice.price,
                                maxFontSize: 28,
                                smallestValue: false,
                                scaleBy: this.obPrice.price.parentNode
                            }],
                            onAdaptiveResize: true
                        });

                    }
                }
            }
        })(window);

        var <?=$obName?> = new JCCatalogElementEquipment_<?=$arParams['ELEMENT_ID']?>(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
    </script>
<?
unset($actualItem, $itemIds, $jsParams);