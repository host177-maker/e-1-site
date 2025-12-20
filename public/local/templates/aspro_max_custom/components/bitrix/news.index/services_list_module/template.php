<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? $this->setFrameMode(true); ?>
<?
use \Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc; ?>
<?
global $arRegion;
$aCosmosConfigIblock = \Cosmos\Config::getInstance()->getParam("IBLOCK");
$showElements = true;
$showAllInSlide = isset($arParams["SHOW_ALL_IN_SLIDE"]) && $arParams["SHOW_ALL_IN_SLIDE"] === 'Y';

$displayCount = isset($arParams["COUNT_SERVICES_IN_ANNOUNCE"]) ? (int)$arParams["COUNT_SERVICES_IN_ANNOUNCE"] : 0;
$count_in_basket = isset($arParams['SERVICES_IN_BASKET']) && is_array($arParams['SERVICES_IN_BASKET']) ? count($arParams['SERVICES_IN_BASKET']) : 0;
if ($count_in_basket > 0) {
    $displayCount = max($displayCount, $count_in_basket);
}
$noMoreElements = $displayCount && is_array($arResult["ITEMS"]) && $displayCount >= count($arResult["ITEMS"]);
if (!$showAllInSlide && $noMoreElements) {
    $showElements = false;
}

$bShowOldPrice = $arParams["SHOW_OLD_PRICE"] !== 'N';
$isShowAddService = $arRegion['PROPERTY_SHOW_PRODUCT_BUILDING_VALUE'] == 'Y' ? false : true;
?>
<? foreach($arResult["IBLOCKS"] as $arIBlock):?>
    <? if ($arIBlock["ITEMS"]) : ?>
        <?
        $basketUrl = str_replace('#SITE_DIR#', SITE_DIR, \Bitrix\Main\Config\Option::get(CMax::moduleID, "BASKET_PAGE_URL", "#SITE_DIR#basket/", SITE_ID));
        $bCompact = isset($arParams["COMPACT_MODE"]) && $arParams["COMPACT_MODE"] === 'Y';
        ?>
        <div class="content_wrapper_block <?= $templateName; ?> services_in_product <?= ($bCompact ? 'services_compact' : '') ?>">
            <div class="services-items">
                <?
                $counter = 1;
                if ($count_in_basket > 0) {
                    $count_to_display = $displayCount - $count_in_basket >= 0 ? $displayCount - $count_in_basket : 0;
                } else {
                    $count_to_display = $displayCount;
                }
                $basket = Bitrix\Sale\Basket::loadItemsForFUser(Bitrix\Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
                $basketItems = $basket->getBasketItems();
                ?>
                <? foreach ($arIBlock["ITEMS"] as $key => $arItem) { ?>
                    <? $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

                    $item_id = $arItem["ID"];
                    $strMeasure = '';

                    $str_postfix = (isset($arParams["PLACE_ID"]) ? '_' . $arParams["PLACE_ID"] : '');
                    $arItem["strMainID"] = $this->GetEditAreaId($arItem['ID']) . "_serv" . $str_postfix;
                    $arItemIDs = CMax::GetItemsIDs($arItem);

                    $services_in_basket = isset($arParams['SERVICES_IN_BASKET'][$arItem['ID']]) && is_array($arParams['SERVICES_IN_BASKET'][$arItem['ID']]) && count($arParams['SERVICES_IN_BASKET'][$arItem['ID']]) > 0;
                    $servicesAssembly = $arParams['SERVICES_IN_BASKET_ASSEMBLY'];

                    $elementName = ((isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arItem['NAME']);
                    ?>
                    <div class="services-item<? if ((int) $aCosmosConfigIblock["offers"]["ID"] == (int) $arItem['IBLOCK_ID']) {?>-module<?}?> <? if((int) $aCosmosConfigIblock["aspro_max_services"]["ID"] == (int) $arItem['IBLOCK_ID'] && $arItem['PROPERTIES']['SERVICE_PRICE']['VALUE'] != 'PRICE_UM'){?>services-item-sborka<?}?> bordered rounded3 <?= ($services_in_basket ? 'services_on' : '') ?> <?= (!$services_in_basket && $showAllInSlide && $counter > $count_to_display ? 'hide_service' : 'show_service') ?> <?= ($services_in_basket && $showAllInSlide ? 'order_top_service' : '') ?>" data-item_id="<?= $arItem["ID"] ?>" data-item-code="<?= $arItem['PROPERTIES']['SERVICE_PRICE']['VALUE']; ?>" data-item="<?= $arItem["ID"] ?>" data-iblockid="<?= ((int) $aCosmosConfigIblock["offers"]["ID"] == (int) $arItem['IBLOCK_ID']) ? (int) $aCosmosConfigIblock["aspro_max_services"]["ID"] : $arItem["IBLOCK_ID"] ?>" data-quantity="1">
                        <? if ((int) $aCosmosConfigIblock["offers"]["ID"] == (int) $arItem['IBLOCK_ID']) {?>
                            <div class="basket-item-block-image basket-image-module-service">
                                <a href="<?= ($arItem["DETAIL_PAGE_URL"]); ?>" class="basket-item-image-link">
                                    <img data-lazyload="" class="basket-item-image ls-is-cached lazyloaded" alt="<?= $elementName; ?>" src="<?= $arItem["DETAIL_PICTURE"]["SRC"] ?>" data-src="<?= $arItem["DETAIL_PICTURE"]["SRC"] ?>">
                                    <? if ($arItem["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"] > 0) {?>
                                        <div class="basket-item-label-ring basket-item-label-small  basket-item-label-bottom basket-item-label-right">
                                            -<?=$arItem["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"]?>%
                                        </div>
                                    <? } ?>
                                </a>
                            </div>
                        <? } ?>
                        <?
                        $moduleOnBasket = false;
                        foreach ($basketItems as $item) {
                            if ($item->getProductId() == $arParams['ASSEMBLY_MODULE_PRODUCT_ID']) {
                                $moduleOnBasket = true;
                            }
                        }
                        ?>
                        <div class="services-item__wrapper colored_theme_block_text" id="<?= $this->GetEditAreaId($arItem['ID']); ?>_list_services">
                            <div class="services-item__inner flexbox flexbox--row">
                                <div class="services-item__info item_info">
                                    <div class="switch_block onoff filter">
                                        <input type="checkbox" name="buy_switch_services" id="<?= $arItem["strMainID"] . '_switch' ?>" <?= ($servicesAssembly ? 'checked' : '') ?>>
                                        <label for="<?= $arItem["strMainID"] . '_switch' ?>"> &nbsp;</label>
                                    </div>
                                    <div class="services-item__title">
                                        <? if ((int) $aCosmosConfigIblock["offers"]["ID"] == (int) $arItem['IBLOCK_ID']) {?>
                                            <h2 class="basket-item-info-name">
                                                <a href="<?= ($arItem["DETAIL_PAGE_URL"]); ?>" class="basket-item-info-name-link">
                                                    <span data-entity="basket-item-name"><?= $elementName; ?></span>
                                                </a>
                                            </h2>
                                        <? } else {?>
                                            <?= $elementName;?>
                                        <? } ?>
                                    </div>

                                    <div class="services-item__plus">+</div>

                                    <div class="services-item__cost cost prices clearfix">
                                        <?
                                        $arParams['SHOW_POPUP_PRICE'] = 'Y';
                                        $arParams['ONLY_POPUP_PRICE'] = 'Y';
                                        ?>
                                        <? if (!$services_in_basket) : ?>
                                            <? if ($arItem["PRICES"]) { ?>
                                                <? $min_price_id = 0; ?>
                                                <? \Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arItem["PRICES"], $strMeasure, $min_price_id, 'Y'); ?>
                                            <? } ?>
                                        <? else : ?>
                                            <div class="price_matrix_wrapper ">
                                                <div class="prices-wrapper">
                                                    <div class="price font-bold font_mxs">
                                                        <span class="values_wrapper">
                                                            <span class="price_value"><?= $arParams['SERVICES_IN_BASKET'][$arItem['ID']]['SUM_FORMATED'] ?></span>
                                                        </span>
                                                    </div>
                                                    <? if ($bShowOldPrice && $arParams['SERVICES_IN_BASKET'][$arItem['ID']]['NEED_SHOW_OLD_SUM'] === 'Y') : ?>
                                                        <div class="price discount">
                                                            <span class="values_wrapper font_xs muted">
                                                                <span class="price_value"><?= $arParams['SERVICES_IN_BASKET'][$arItem['ID']]['SUM_FULL_PRICE_FORMATED'] ?></span>
                                                            </span>
                                                        </div>
                                                    <? endif; ?>
                                                </div>
                                            </div>
                                        <? endif; ?>
                                    </div>

                                    <div class="services-item__buy">
                                        <?
                                        $arAddToBasketData = array();
                                        $totalCount = 999;
                                        $arItem["CAN_BUY"] = true;
                                        $arParams['PRODUCT_PROPERTIES'] = array('BUY_PRODUCT_PROP');
                                        $arParams["PARTIAL_PRODUCT_PROPERTIES"] = 'Y';

                                        if ($services_in_basket) {
                                            $arParams["EXACT_QUANTITY"] = $arParams['SERVICES_IN_BASKET'][$arItem['ID']]['QUANTITY'];
                                        } else {
                                            $arParams["EXACT_QUANTITY"] = 0;
                                        }

                                        $arAddToBasketData = CMax::GetAddToBasketArray($arItem, $totalCount, $arParams["DEFAULT_COUNT"], $basketUrl, true, $arItemIDs["ALL_ITEM_IDS"], '', $arParams);
                                        ?>
                                    </div>
                                </div>
                                <? //prices was tut
                                ?>
                            </div>
                        </div>
                        <? if ((int) $aCosmosConfigIblock["offers"]["ID"] == (int) $arItem['IBLOCK_ID']) {?>
                            <div class="basket-item-block-properties">
                            <?if($arParams['ASSEMBLY_CHECKED']){?>
                                    <?
                                    //скрытие сделано чтобы включать и отключать чек бокс
                                    /**/?>
                                    <div class="assembly-module" style="display: none;" data-id="<?=$arParams['ASSEMBLY_ID_OFFER']?>">
                                        <div class="services-item__inner flexbox flexbox--row">
                                            <div class="services-item__info item_info">
                                                <div class="switch_block onoff filter">
                                                    <input type="checkbox" name="buy_switch_services<?=rand();?>" checked="">
                                                    <label for="bx_4009546559_135_serv_page_basket_switch"> &nbsp;</label>
                                                </div>
                                                <div class="services-item__title">
                                                    Сборка аксессуара
                                                </div>

                                                <div class="services-item__plus">+</div>

                                                <div class="services-item__cost cost prices clearfix" style="width: 157px; max-width: 153px;">
                                                    <div class="price_matrix_wrapper ">
                                                        <div class="prices-wrapper">
                                                            <div class="price font-bold font_mxs">
                                                                <span class="values_wrapper">
                                                                    <span class="price_value"><?= $arParams['ASSEMBLY_MODULE']["PRICE_FORMATED"]?></span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="services-item__buy">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?/**/?>
                                <? }?>
                                <div class="basket-wrap-service-module">
                                    <? if (!empty($arItem["PROPERTIES"]["HEIGHT"]["VALUE"])) {?>
                                        <div class="basket-item-property basket-item-property-scu-text" data-entity="basket-item-sku-block">
                                            <div class="basket-item-property-name"><?= $arItem ["PROPERTIES"]["HEIGHT"]["NAME"]?></div>
                                            <div class="basket-item-property-value">
                                                <ul class="basket-item-scu-list">
                                                    <li class="basket-item-scu-item selected" title="<?= $arItem ["PROPERTIES"]["HEIGHT"]["VALUE"]?>" data-entity="basket-item-sku-field" data-initial="true" data-value-id="<?= $arItem ["PROPERTIES"]["HEIGHT"]["VALUE"]?>" data-sku-name="<?= $arItem ["PROPERTIES"]["HEIGHT"]["VALUE"]?>" data-property="HEIGHT">
                                                        <span class="basket-item-scu-item-inner"><?= $arItem ["PROPERTIES"]["HEIGHT"]["VALUE"]?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    <? } ?>
                                    <? if (!empty($arItem["PROPERTIES"]["WIDTH"]["VALUE"])) {?>
                                        <div class="basket-item-property basket-item-property-scu-text" data-entity="basket-item-sku-block">
                                            <div class="basket-item-property-name"><?= $arItem["PROPERTIES"]["WIDTH"]["NAME"]?></div>
                                            <div class="basket-item-property-value">
                                                <ul class="basket-item-scu-list">
                                                    <li class="basket-item-scu-item selected" title="<?= $arItem["PROPERTIES"]["WIDTH"]["VALUE"]?>" data-entity="basket-item-sku-field" data-initial="true" data-value-id="<?= $arItem["PROPERTIES"]["WIDTH"]["VALUE"]?>" data-sku-name="<?= $arItem["PROPERTIES"]["WIDTH"]["VALUE"]?>" data-property="WIDTH">
                                                        <span class="basket-item-scu-item-inner"><?= $arItem["PROPERTIES"]["WIDTH"]["VALUE"]?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    <? } ?>
                                    <? if (!empty($arItem["PROPERTIES"]["DEPTH"]["VALUE"])) {?>
                                        <div class="basket-item-property basket-item-property-scu-text" data-entity="basket-item-sku-block">
                                            <div class="basket-item-property-name"><?= $arItem["PROPERTIES"]["DEPTH"]["NAME"]?></div>
                                            <div class="basket-item-property-value">
                                                <ul class="basket-item-scu-list">
                                                    <li class="basket-item-scu-item selected" title="<?= $arItem["PROPERTIES"]["DEPTH"]["VALUE"]?>" data-entity="basket-item-sku-field" data-initial="true" data-value-id="<?= $arItem["PROPERTIES"]["DEPTH"]["VALUE"]?>" data-sku-name="<?= $arItem["PROPERTIES"]["DEPTH"]["VALUE"]?>" data-property="DEPTH">
                                                        <span class="basket-item-scu-item-inner"><?= $arItem["PROPERTIES"]["DEPTH"]["VALUE"]?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    <? } ?>
                                    
                                </div>
                                <div class="basket-delete-module">
                                        <div class="">
                                            <span class="" data-entity="">
                                                <i class="svg  svg-inline-closes" aria-hidden="true"><svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 0C10.8358 0 12.3507 1.336 12.5722 3.06252L17.325 3.0625C17.6978 3.0625 18 3.35631 18 3.71875C18 4.08119 17.6978 4.375 17.325 4.375H16.101L15.0081 18.8988C14.952 19.6445 14.8569 19.9101 14.6931 20.1763C14.5294 20.4425 14.3015 20.6484 14.0165 20.7878C13.7315 20.9272 13.4521 21 12.683 21H5.31701C4.54794 21 4.26855 20.9272 3.98352 20.7878C3.6985 20.6484 3.47063 20.4425 3.30685 20.1763C3.14308 19.9101 3.04798 19.6445 2.99186 18.8988L1.89855 4.375H0.675C0.302208 4.375 0 4.08119 0 3.71875C0 3.35631 0.302208 3.0625 0.675 3.0625L5.42785 3.06252C5.64928 1.336 7.16416 0 9 0ZM14.7474 4.375H3.25215L4.33826 18.803C4.37545 19.2971 4.40316 19.4014 4.46541 19.5026C4.49763 19.5549 4.53405 19.5878 4.59012 19.6153C4.6917 19.6649 4.79383 19.685 5.22607 19.6873L12.683 19.6875C13.1925 19.6875 13.3015 19.6682 13.4099 19.6153C13.4659 19.5878 13.5024 19.5549 13.5346 19.5026C13.5968 19.4014 13.6246 19.2971 13.6617 18.803L14.7474 4.375ZM9 7C9.37279 7 9.675 7.29381 9.675 7.65625V15.5312C9.675 15.8937 9.37279 16.1875 9 16.1875C8.62721 16.1875 8.325 15.8937 8.325 15.5312V7.65625C8.325 7.29381 8.62721 7 9 7ZM12.1874 7.00101C12.5597 7.02111 12.8446 7.33077 12.824 7.69265L12.374 15.5677C12.3533 15.9295 12.0348 16.2066 11.6626 16.1865C11.2903 16.1664 11.0054 15.8567 11.026 15.4948L11.476 7.61985C11.4967 7.25797 11.8152 6.98091 12.1874 7.00101ZM5.81256 7.00101C6.18478 6.98091 6.50328 7.25797 6.52396 7.61985L6.97396 15.4948C6.99464 15.8567 6.70966 16.1664 6.33744 16.1865C5.96522 16.2066 5.64672 15.9295 5.62604 15.5677L5.17604 7.69265C5.15536 7.33077 5.44034 7.02111 5.81256 7.00101ZM9 1.3125C7.91147 1.3125 7.00348 2.06402 6.79501 3.0625H11.205C10.9965 2.06402 10.0885 1.3125 9 1.3125Z" fill="#60C339"></path></svg></i>            </span>
                                        </div>
                                    </div>
                            <? if ($isShowAddService) {?>
                                <?
                                    $arResultBasket['ID'] = $arItem["ID"];
                                    $arResultBasket['ELEMENT'] = CIBlockElement::GetByID(
                                        (int)$arResultBasket['ID']
                                        )->Fetch();
                                    if (empty($arResultBasket['ELEMENT']["IBLOCK_SECTION_ID"])) {
                                        $mxResult = CCatalogSku::GetProductInfo(
                                            $arResultBasket['ID']
                                        );
                                        if (is_array($mxResult))
                                        {
                                            $arResultBasket['ELEMENT']["IBLOCK_SECTION_ID"] = CIBlockElement::GetByID(
                                                (int)$mxResult['ID']
                                                )->Fetch()["IBLOCK_SECTION_ID"];
                                            $arResultBasket['ID'] = $mxResult['ID'];
                                        }
                                    }
                                    $priceModuleAssembly = (!empty(\Bitrix\Main\Config\Option::set('e1.site.settings', 'E1_SS_PRICE_MODULE_ASSEMBLY', ''))) ? \Bitrix\Main\Config\Option::set('e1.site.settings', 'E1_SS_PRICE_MODULE_ASSEMBLY', '') : "PRICE_UM";//по умолчанию цена идет от углового модуля, в конфиге можно задать свою PRICE которую подставить можно
                                    $arResult["BASKET_SERVICES"] =	\COrwoFunctions::getServices($arResultBasket)[$priceModuleAssembly];
                                    if (!empty($arResult["BASKET_SERVICES"])) {
                                        $arResult["BASKET_SERVICES_VAL"][$arItem["ID"]] = $arResult["BASKET_SERVICES"];
                                    }
                                ?>
                            <? } ?>
                        </div>

                    <? } ?>
                    </div>
                    <?/*<hr>*/?>
                    <?
                    if (!$services_in_basket)
                        $counter++;
                    ?>
                <? } ?>
            </div>
            <? $needMoreServices = (int)$arResult['NAV_RESULT']->NavPageCount > 1; ?>
            <? if ($arParams["SHOW_BUTTON_ALL"] === 'Y' && $needMoreServices) : ?>
                <div class="more-services-link"><span class="choise colored_theme_text_with_hover font_sxs dotted" data-block=".js-scroll-services"><?= Loc::getMessage('ALL_BUY_SERVICES'); ?></span></div>
            <? endif; ?>

            <? if ($arParams["SHOW_ALL_IN_SLIDE"] === 'Y' && !$noMoreElements) : ?>
                <div class="more-services-slide" data-open="<?= Loc::getMessage('ALL_BUY_SERVICES') ?>" data-close="<?= Loc::getMessage('HIDE_BUY_SERVICES') ?>"><span class="colored_theme_text_with_hover font_sxs dotted"><?= Loc::getMessage('ALL_BUY_SERVICES'); ?></span></div>
            <? endif; ?>
        </div>
    <? endif; ?>
<? endforeach;?>