<?php
namespace Absteam;

use \Bitrix\Main\Config\Option;
use \Bitrix\IBlock\CIBlockElement;

/**
 * Класс использутся для пересчета цен после обновления каталога (из 1С)
 */
class PriceUpdater {
    const catalogIB = 48; // ID ИБ каталога
    const offersIB = 49; // ID ИБ торговых предложений
    public static $arDiscountsOptim = array();
    public static $arCatalogPrices = array();
    public static $arPriceTypes = array();
    public static $iDiscountOptimIblockId = null;
    public static $iBasePriceId = null;
    public static $arPriceGroups = [235, 236, 237, 238];
    public static $iPriceDontDelete = null;

    /**
     * "Гланвый" метод - производит выборку элементов каталога и пересчет их цен
     */
    public static function Update($calcType = 'byDate') {
        global $arRegion;
        
        if(\CModule::IncludeModule("iblock") && \CModule::IncludeModule("catalog")){
            self::$iDiscountOptimIblockId = \Cosmos\Config::getInstance()->getIblockIdByCode('discount_optim');
            self::$iBasePriceId           = Option::get('e1.site.settings', 'E1_SS_BASE_PRICE', '1', 's1');

            $catalogIblock = \Bitrix\Iblock\Iblock::wakeUp(self::catalogIB);
            $catalogClass  = $catalogIblock->getEntityDataClass();

            $offersIblock = \Bitrix\Iblock\Iblock::wakeUp(self::offersIB);
            $skuClass     = $offersIblock->getEntityDataClass();

            $arFilter =  ["ACTIVE" => "Y"];
            $now = new \DateTime();
            if($calcType === 'byDate') $arFilter = array_merge($arFilter, ['=NEED_TO_RECALC_PRICE.VALUE' => 1]);

            $catalog = $catalogClass::getList([
                'select' => [
                    "ID",
                    "IBLOCK_ID",
                    "IBLOCK_SECTION_ID",
                    'PROTSENT_SKIDKI.VALUE',
                    'SERIYA_SHKAFA.VALUE',
                    'NEED_TO_RECALC_PRICE.VALUE'
                ],
                'filter' => $arFilter,
            ])->fetchAll();

            // echo "\e[1;32m[" . date("d.m.Y H:i:s") . "] выбрано " . count($catalog) . " элементов каталога" . " \e[0m\n";
            // ob_end_flush();

            $sku = [];
            foreach ($catalog as $key => $element) {
                $offers    = $skuClass::getList([
                    'select' => [
                        'ID',
                        'IBLOCK_ID',
                        'NAME',
                        'CML2_LINK.VALUE',
                        'BASIC_CONFIGURATION.VALUE',
                        'BASE_COST_LAYOUT.VALUE',
                    ],
                    'filter' => [
                        "ACTIVE"          => "Y",
                        'CML2_LINK.VALUE' => $element['ID']
                    ],
                ])->fetchAll();
                $sku[$key] = $offers;
            }

            self::GetCatalogPricesCodes();

            if (!empty($arRegion['LIST_PRICES'])) {
                self::$arPriceGroups = array();
                foreach ($arRegion['LIST_PRICES'] as $sLPKey => $arListPrice) {
                    self::$arPriceGroups[] = $arListPrice['ID'];
                }
            }

            foreach (self::$arPriceGroups as $priceGroup) {
                $dbPriceType                     = \CCatalogGroup::GetList(
                    array("SORT" => "ASC"),
                    array("ID" => $priceGroup)
                );
                self::$arPriceTypes[$priceGroup] = $dbPriceType->Fetch();
            }

            $i = 1;
            foreach ($catalog as $key => $element) {
                foreach ($sku[$key] as $offer) {
                    if (is_int($i / 1000)) {
                        echo "\e[1;32m[" . date("d.m.Y H:i:s") . "] обработано $i элементов" . " \e[0m\n";
                        ob_end_flush();
                    }
                    $arResult = self::GetPriceByOfferId($offer, $element);
                    if(!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])) {
                            $arPriceUpdateFields = array(
                                "PRODUCT_ID" => $arResult['OFFER']['ID'],
                                "CATALOG_GROUP_ID" => $arResult['BASE_PRICE_ID'],
                                "PRICE" => $arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
                                "CURRENCY" => $arResult['OFFER']['MIN_PRICE']['CURRENCY']
                            );

                            $rsPriceUpdate = \Bitrix\Catalog\Model\Price::getList([
                                "filter" => [
                                    "PRODUCT_ID" => $arResult['OFFER']['ID'],
                                    "CATALOG_GROUP_ID" => $arResult['BASE_PRICE_ID']
                                ]
                            ]);

                            if ($arPriceUpdate = $rsPriceUpdate->fetch()) {
                                $obPriceUpdateResult = \Bitrix\Catalog\Model\Price::update($arPriceUpdate["ID"], $arPriceUpdateFields);
                                if ($obPriceUpdateResult->isSuccess()) {
                                    self::$iPriceDontDelete = $obPriceUpdateResult->getId();
                                } 
                            } else {
                                $obPriceUpdateResult = \Bitrix\Catalog\Model\Price::add($arPriceUpdateFields);
                                if ($obPriceUpdateResult->isSuccess()) {
                                    self::$iPriceDontDelete = $obPriceUpdateResult->getId();
                                }
                            }
                            \CIBlockElement::SetPropertyValues($offer['ID'], self::offersIB, 0, 'NEED_TO_RECALC_PRICE');
                    }
                    $i++;
                }
                \CIBlockElement::SetPropertyValues($element['ID'], self::catalogIB, 0, 'NEED_TO_RECALC_PRICE');

            }
            
            // $arResult = self::GetPriceByOfferId($sku[0][0], $catalog[0]);
            // print_r($arResult);
            // ob_end_flush();
        }
    }

    /**
     * Получает цены для конкретного SKU
     */
    public static function GetPriceByOfferId(&$rsOffer, &$arItem) {
        global $arRegion, $USER;
        $arResult = array();

        if (!empty(self::$iBasePriceId)) {

            $rsOffer['PROPERTIES']['CML2_LINK']['VALUE'] = intval($rsOffer['IBLOCK_ELEMENTS_ELEMENT_OFFERS_CML2_LINK_VALUE']);

            if (!empty($rsOffer['PROPERTIES']['CML2_LINK']['VALUE'])) {
                $arItem['PROPERTIES']['PROTSENT_SKIDKI']['VALUE']  = intval($arItem['IBLOCK_ELEMENTS_ELEMENT_CATALOG_PROTSENT_SKIDKI_VALUE']) ?? false;
                $arItem['PROPERTIES']['SERIYA_SHKAFA']['VALUE']    = $arItem['IBLOCK_ELEMENTS_ELEMENT_CATALOG_SERIYA_SHKAFA_VALUE'] ?? false;
                $arItem['PROPERTIES']['BASE_COST_LAYOUT']['VALUE'] = $rsOffer['IBLOCK_ELEMENTS_ELEMENT_OFFERS_BASE_COST_LAYOUT_VALUE'] ?? false;

                $rsGetPrice = \Bitrix\Catalog\Model\Price::getList([
                    "filter" => [
                        "PRODUCT_ID"       => $rsOffer['ID'],
                        "CATALOG_GROUP_ID" => self::$arPriceGroups
                    ]
                ]);

                while ($arGetPrice = $rsGetPrice->fetch()) {
                    if (!empty($arGetPrice['ID'])) {
                        if ($arPriceType = self::$arPriceTypes[$arGetPrice['CATALOG_GROUP_ID']]) {
                            if (!empty($arPriceType['NAME'])) {
                                $arDiscounts = \CCatalogDiscount::GetDiscountByProduct($arGetPrice['PRODUCT_ID'], $USER->GetUserGroupArray(), "N");
                                if (is_array($arDiscounts) && sizeof($arDiscounts) > 0) {
                                    $arGetPrice['DISCOUNT_PRICE'] = \CCatalogProduct::CountPriceWithDiscount($arGetPrice['PRICE'], $arGetPrice['CURRENCY'], $arDiscounts);
                                    $arGetPrice['DISCOUNT']       = $arGetPrice['PRICE'] - $arGetPrice['DISCOUNT_PRICE'];
                                    $arGetPrice['PERCENT']        = round((($arGetPrice['DISCOUNT'] / $arGetPrice['PRICE']) * 100), 2);
                                }

                                if ($arGetPrice['DISCOUNT_PRICE'] == 0 && $arGetPrice['PRICE'] != 0) {
                                    $arGetPrice['DISCOUNT_PRICE'] = $arGetPrice['PRICE'];
                                }

                                $rsOffer["PRICES"][$arPriceType['NAME']] = array(
                                    "VALUE"                 => $arGetPrice['PRICE'],
                                    "DISCOUNT_VALUE"        => $arGetPrice['DISCOUNT_PRICE'],
                                    "CURRENCY"              => $arGetPrice['CURRENCY'],
                                    "PRINT_VALUE"           => CurrencyFormat($arGetPrice['PRICE'], $arGetPrice['CURRENCY']),
                                    "PRINT_DISCOUNT_VALUE"  => CurrencyFormat($arGetPrice['DISCOUNT_PRICE'], $arGetPrice['CURRENCY']),
                                    "DISCOUNT_DIFF"         => $arGetPrice['DISCOUNT'],
                                    "PRINT_DISCOUNT_DIFF"   => CurrencyFormat($arGetPrice['DISCOUNT'], $arGetPrice['CURRENCY']),
                                    "DISCOUNT_DIFF_PERCENT" => $arGetPrice['PERCENT'],
                                    "CAN_ACCESS"            => "Y"
                                );
                            }
                        }
                    }
                }

                // var_dump($rsOffer["PRICES"]);
                self::ChangeCatalogItemPrice($arItem, $rsOffer);
                // var_dump($rsOffer["PRICES"]);

                $arResult['ELEMENT']       = $arItem;
                $arResult['OFFER']         = $rsOffer;
                $arResult['BASE_PRICE_ID'] = self::$iBasePriceId;
            }
        }
        return $arResult;
    }

    public static function RoundCatalogItemPrice(&$price, $esta = false) {
        if ($esta) {
            $price = round($price / 100) * 100 - 10;
        } else {
            $price = round($price / 100) * 100;
        }
    }

    /**
     * Получает скидки для серии Оптим
     */
    public static function GetDiscountsOptim() {
        if (empty(self::$arDiscountsOptim)) {
            $arSelect = array("ID", "NAME", "PROPERTY_SKIDKA", "PROPERTY_SKIDKA_RAZDELY", "PROPERTY_SKIDKA_TOVARY", "PROPERTY_OFF_DECOR_SKIDKA");
            $arFilter = array("IBLOCK_ID" => IntVal(self::$iDiscountOptimIblockId), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
            $res      = \CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
            while ($arFields = $res->GetNext()) {
                $item_id    = $arFields['PROPERTY_SKIDKA_TOVARY_VALUE'];
                $section_id = $arFields['PROPERTY_SKIDKA_RAZDELY_VALUE'];
                $discount   = abs($arFields['PROPERTY_SKIDKA_VALUE']);
                $decor_off  = $arFields['PROPERTY_OFF_DECOR_SKIDKA_VALUE'];

                // записываем наибольшую не пустую скидку
                if (!empty($item_id) && self::$arDiscountsOptim['ITEMS'][$item_id]['DISCOUNT'] < $discount) {
                    self::$arDiscountsOptim['ITEMS'][$item_id]['DISCOUNT']           = $discount;
                    self::$arDiscountsOptim['ITEMS'][$item_id]['OFF_DECOR_DISCOUNT'] = $decor_off;
                }
                if (!empty($section_id) && self::$arDiscountsOptim['SECTIONS'][$section_id]['DISCOUNT'] < $discount) {
                    self::$arDiscountsOptim['SECTIONS'][$section_id]['DISCOUNT']           = $discount;
                    self::$arDiscountsOptim['SECTIONS'][$section_id]['OFF_DECOR_DISCOUNT'] = $decor_off;
                }
            }
        }
    }

    /**
     * Модификатор цен (скидок) для серии Оптим
     */
    public static function ChangePriceOptim(&$arItem, &$arOffer) {
        $search_item = [];
        // ищем среди элементов
        if (!empty(self::$arDiscountsOptim['ITEMS'][$arItem['ID']]['DISCOUNT'])) {
            $search_item = self::$arDiscountsOptim['ITEMS'][$arItem['ID']];
        }
        // ищем среди разделов, если не нашли среди элементов или скидка раздела больше
        if (
            !empty(self::$arDiscountsOptim['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['DISCOUNT'])
            && (empty($search_item) || self::$arDiscountsOptim['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['DISCOUNT'] > $search_item['DISCOUNT'])
        ) {
            $search_item = self::$arDiscountsOptim['SECTIONS'][$arItem['IBLOCK_SECTION_ID']];
        }

        if ($search_item) {
            // отключаем декоративную скидку
            if ($search_item['OFF_DECOR_DISCOUNT'] == "Y") {
                $arItem['PROPERTIES']['PROTSENT_SKIDKI']['VALUE']  = 0;
                $arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'] = 0;
                //записываем дальше, чтобы при пересчете с правилом корзины учесть отключение декоративной скидки
                $arOffer['PROPERTIES']['OFF_DECOR_DISCOUNT']['VALUE'] = $arItem['PROPERTIES']['OFF_DECOR_DISCOUNT']['VALUE'] = 'Y';
            }
            // подставляем настоящую скидку
            foreach ($arOffer["PRICES"] as $key => &$value) {
                $value['DISCOUNT_DIFF_PERCENT'] += $search_item['DISCOUNT'];
            }
        }
    }

    /**
     * Производите пересчет цены элемента SKU с учетом всех скидок и т.д.
     */
    public static function ChangeCatalogItemPrice(&$arItem, &$arOffer) {
        global $arRegion, $USER;

        if (!empty($arItem['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])) {
            $arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'] = $arItem['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'];
        }

        if (intval($arItem['PROPERTIES']['SERIYA_SHKAFA']['VALUE']) == 510) {

            // получаем скидки на Оптиму из инфоблока
            self::GetDiscountsOptim();
            // измняем цены для Оптимы
            self::ChangePriceOptim($arItem, $arOffer);

            $iDiscountValue = 0;
            $bPriceIncrease = true;

            $sResultPriceCode    = 'BASE';
            $iDiscountPriceValue = $iPriceValue = 0;
            foreach ($arOffer["PRICES"] as $key => $value) {
                if ($value['DISCOUNT_DIFF_PERCENT'] != 0) {
                    $iDiscountValue = intval($value['DISCOUNT_DIFF_PERCENT']);
                }

                if ($key == self::$arCatalogPrices['DISCOUNT_PRICE_CODE']) {
                    $iDiscountPriceValue = $value['VALUE'];
                    $sResultPriceCode    = $key;
                }
                if ($key == self::$arCatalogPrices['PRICE_CODE']) {
                    $iPriceValue = $value['VALUE'];
                }
            }

            $iCalcDiscountPriceValue = ($iDiscountPriceValue + $arOffer['PROPERTIES']['BASE_COST_LAYOUT']['VALUE'])
                * $arRegion['PROPERTY_REGION_TAG_COEFFICIENT_VALUE'];

            self::RoundCatalogItemPrice($iCalcDiscountPriceValue);

            $iDiscountPriceValue = $iCalcDiscountPriceValue;
            $iPriceValue         = $iDiscountPriceValue;
            if (!empty($arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])) {
                if ($bPriceIncrease) {
                    $iPriceValue = $iDiscountPriceValue * 100 / (100 - intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"]));
                } else {
                    $iDiscountPriceValue = $iPriceValue * (100 - intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"])) / 100;
                }
            }

            if ($iDiscountValue != 0) {
                $iDiscountPriceValue = $iDiscountPriceValue * (100 - $iDiscountValue) / 100;
            }

            $iPriceValue         = ceil($iPriceValue);
            $iDiscountPriceValue = ceil($iDiscountPriceValue);

            $arOffer["PRICES"] = array();

            $arOffer["PRICES"][$sResultPriceCode] = array(
                "MIN_PRICE"            => "Y",
                "VALUE"                => $iPriceValue,
                "DISCOUNT_VALUE"       => $iDiscountPriceValue,
                "CURRENCY"             => "RUB",
                "PRINT_VALUE"          => CurrencyFormat($iPriceValue, 'RUB'),
                "PRINT_DISCOUNT_VALUE" => CurrencyFormat($iDiscountPriceValue, 'RUB'),
                "DISCOUNT_DIFF"        => $iPriceValue - $iDiscountPriceValue,
                "PRINT_DISCOUNT_DIFF"  => CurrencyFormat($iPriceValue - $iDiscountPriceValue, 'RUB'),
                "CAN_ACCESS"           => "Y",
                "PROCENT_SALE"         => $iDiscountValue
            );

            $arOffer["MIN_PRICE"] = $arOffer["PRICES"][$sResultPriceCode];
        } else {
            $arPrices = $arOffer["PRICES"];
            if (empty($arDiscountPrices) && count($arPrices) > 1) {
                $sDiscountPriceKey = $sPriceKey = '';
                foreach ($arPrices as $key => $value) {
                    if ($key == self::$arCatalogPrices['DISCOUNT_PRICE_CODE']) {
                        $sDiscountPriceKey = $key;
                    } elseif ($key == self::$arCatalogPrices['PRICE_CODE']) {
                        $sPriceKey = $key;
                    }
                }
                if (!empty($sDiscountPriceKey) && !empty($arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])) {
                    $iDiscountPriceValue = $arPrices[$sDiscountPriceKey]["VALUE"];

                    $arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"]       = $iDiscountPriceValue;
                    $arPrices[$sDiscountPriceKey]["PRINT_DISCOUNT_VALUE"] = $arPrices[$sDiscountPriceKey]["PRINT_VALUE"];

                    $arPrices[$sDiscountPriceKey]["VALUE"]       = ceil($iDiscountPriceValue * 100 / (100 - intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"])));
                    $arPrices[$sDiscountPriceKey]["PRINT_VALUE"] = CurrencyFormat($arPrices[$sDiscountPriceKey]["VALUE"], $arPrices[$sDiscountPriceKey]["CURRENCY"]);

                    if ($arPrices[$sDiscountPriceKey]['DISCOUNT_DIFF_PERCENT'] != 0) {
                        $arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"]       = ceil($arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"] * (100 - intval($arPrices[$sDiscountPriceKey]['DISCOUNT_DIFF_PERCENT'])) / 100);
                        $arPrices[$sDiscountPriceKey]["PRINT_DISCOUNT_VALUE"] = CurrencyFormat($arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"], $arPrices[$sDiscountPriceKey]["CURRENCY"]);
                    }
                    $arPrices[$sDiscountPriceKey]["DISCOUNT_DIFF"]         = ceil($arPrices[$sDiscountPriceKey]["VALUE"] - $arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"]);
                    $arPrices[$sDiscountPriceKey]["PRINT_DISCOUNT_DIFF"]   = CurrencyFormat($arPrices[$sDiscountPriceKey]["DISCOUNT_DIFF"], $arPrices[$sDiscountPriceKey]["CURRENCY"]);
                    $arPrices[$sDiscountPriceKey]["DISCOUNT_DIFF_PERCENT"] = $arPrices[$sDiscountPriceKey]["PERCENT"] = round(($arPrices[$sDiscountPriceKey]["DISCOUNT_DIFF"] / $arPrices[$sDiscountPriceKey]["VALUE"]) * 100, 2);
                    $arOffer["MIN_PRICE"]                      = $arPrices[$sDiscountPriceKey];
                    $arPrices[$sDiscountPriceKey]["MIN_PRICE"] = "Y";
                    unset($arPrices[$sPriceKey]);
                } elseif (!empty($arPrices[$sDiscountPriceKey]["VALUE"]) && $arPrices[$sDiscountPriceKey]['DISCOUNT_DIFF_PERCENT'] != 0) {
                    unset($arPrices[$sPriceKey]);
                    $arOffer["MIN_PRICE"] = $arPrices[$sPriceKey];
                } elseif (!empty($arPrices[$sDiscountPriceKey]["VALUE"]) && !empty($arPrices[$sPriceKey]["VALUE"])) {
                    if (is_array($arPrices[$sDiscountPriceKey]) && is_array($arPrices[$sPriceKey])) {
                        if ($arPrices[$sDiscountPriceKey]["VALUE"] < $arPrices[$sPriceKey]["VALUE"] && $arPrices[$sDiscountPriceKey]["VALUE"] != 0) {
                            $arPrices[$sPriceKey]["DISCOUNT_VALUE"]        = ceil($arPrices[$sDiscountPriceKey]["VALUE"]);
                            $arPrices[$sPriceKey]["PRINT_DISCOUNT_VALUE"]  = $arPrices[$sDiscountPriceKey]["PRINT_VALUE"];
                            $arPrices[$sPriceKey]["DISCOUNT_DIFF"]         = ceil($arPrices[$sPriceKey]["VALUE"]) - ceil($arPrices[$sDiscountPriceKey]["VALUE"]);
                            $arPrices[$sPriceKey]["PRINT_DISCOUNT_DIFF"]   = CurrencyFormat($arPrices[$sPriceKey]["DISCOUNT_DIFF"], $arPrices[$sPriceKey]["CURRENCY"]);
                            $arPrices[$sPriceKey]["DISCOUNT_DIFF_PERCENT"] = $arPrices[$sPriceKey]["PERCENT"] = round(($arPrices[$sPriceKey]["DISCOUNT_DIFF"] / ceil($arPrices[$sPriceKey]["VALUE"])) * 100, 2);
                            $arPrices[$sPriceKey]["MIN_PRICE"]     = "Y";
                            $arPrices[$sPriceKey]["OLD_NAME_LANG"] = $arPrices[$sPriceKey]["NAME_LANG"];
                            $arPrices[$sPriceKey]["NAME_LANG"]     = $arPrices[$sDiscountPriceKey]["NAME_LANG"];
                        }
                        if ($arPrices[$sDiscountPriceKey]["VALUE"] == 0) {
                            $arPrices[$sPriceKey]["MIN_PRICE"] = "Y";
                        }

                        unset($arPrices[$sDiscountPriceKey]);
                    }
                    $arOffer["MIN_PRICE"]   = $arPrices[$sPriceKey];
                    $arOffer["ITEM_PRICES"] = $arPrices;
                }
                $arItem["PRICES"] = $arPrices;
            } elseif (count($arPrices) == 1) {
                $arOffer["MIN_PRICE"] = current($arPrices);

                if (!empty($arOffer["MIN_PRICE"]) && !empty($arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])) {
                    $iDiscountPriceValue                           = $arOffer["MIN_PRICE"]["VALUE"];
                    $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"]        = $iDiscountPriceValue;
                    $arOffer["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"]  = $arOffer["MIN_PRICE"]["PRINT_VALUE"];
                    $arOffer["MIN_PRICE"]["VALUE"]                 = ceil($iDiscountPriceValue * 100 / (100 - intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"])));
                    $arOffer["MIN_PRICE"]["PRINT_VALUE"]           = CurrencyFormat($arOffer["MIN_PRICE"]["VALUE"], $arOffer["MIN_PRICE"]["CURRENCY"]);
                    $arOffer["MIN_PRICE"]["DISCOUNT_DIFF"]         = ceil($arOffer["MIN_PRICE"]["VALUE"] - $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"]);
                    $arOffer["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"]   = CurrencyFormat($arOffer["MIN_PRICE"]["DISCOUNT_DIFF"], $arOffer["MIN_PRICE"]["CURRENCY"]);
                    $arOffer["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"] = $arOffer["MIN_PRICE"]["PERCENT"] = round(($arOffer["MIN_PRICE"]["DISCOUNT_DIFF"] / $arOffer["MIN_PRICE"]["VALUE"]) * 100, 2);
                    $sPriceKey            = key($arPrices);
                    $arPrices[$sPriceKey] = $arOffer["MIN_PRICE"];
                }
            }
            $arOffer["PRICES"] = $arPrices;
        }

        if (count($arOffer["PRICES"]) == 1 && empty($arOffer["MIN_PRICE"])) {
            $arOffer["MIN_PRICE"] = current($arOffer["PRICES"]);
            if (!empty($arOffer["MIN_PRICE"]) && !empty($arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])) {
                $iDiscountPriceValue                          = $arOffer["MIN_PRICE"]["VALUE"];
                $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"]       = $iDiscountPriceValue;
                $arOffer["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"] = $arOffer["MIN_PRICE"]["PRINT_VALUE"];
                $arOffer["MIN_PRICE"]["VALUE"]                = ceil($iDiscountPriceValue * 100 / (100 - intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"])));
                $arOffer["MIN_PRICE"]["PRINT_VALUE"]          = CurrencyFormat($arOffer["MIN_PRICE"]["VALUE"], $arOffer["MIN_PRICE"]["CURRENCY"]);

                $arOffer["MIN_PRICE"]["DISCOUNT_DIFF"]         = ceil($arOffer["MIN_PRICE"]["VALUE"] - $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"]);
                $arOffer["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"]   = CurrencyFormat($arOffer["MIN_PRICE"]["DISCOUNT_DIFF"], $arOffer["MIN_PRICE"]["CURRENCY"]);
                $arOffer["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"] = $arOffer["MIN_PRICE"]["PERCENT"] = round(($arOffer["MIN_PRICE"]["DISCOUNT_DIFF"] / $arOffer["MIN_PRICE"]["VALUE"]) * 100, 2);
            }
        }

        $iMinProductPrice = ($arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE']) ? $arItem['MIN_PRICE']['DISCOUNT_VALUE'] : $arItem['MIN_PRICE']['VALUE'];
        $iMinOfferPrice   = ($arOffer['MIN_PRICE']['DISCOUNT_VALUE'] < $arOffer['MIN_PRICE']['VALUE']) ? $arOffer['MIN_PRICE']['DISCOUNT_VALUE'] : $arOffer['MIN_PRICE']['VALUE'];
        if (($iMinOfferPrice < $iMinProductPrice || empty($iMinProductPrice)) && !empty($iMinOfferPrice)) {
            $arItem['MIN_PRICE']                 = $arOffer['MIN_PRICE'];
            $arItem['OFFER_ID_SELECTED']         = $arOffer['ID'];
            $arItem['BASIC_CONFIGURATION_PRICE'] = $iMinOfferPrice;
        }
    }

    /**
     * Подготалвивает $arRegion для работы ChangeCatalogItemPrice()
     */
    public static function GetCatalogPricesCodes() {
        global $arRegion;
        if (empty($arRegion["LIST_PRICES"]) && \Bitrix\Main\Loader::includeModule("catalog")) {
            self::$arPriceGroups = explode(',', Option::get('e1.site.settings', 'E1_SS_DEFAULT_PRICE'));
            $dbPriceType   = \CCatalogGroup::GetList(
                array("SORT" => "ASC"),
                array("ID" => self::$arPriceGroups)
            );
            while ($arPriceType = $dbPriceType->Fetch()) {
                $arRegion["LIST_PRICES"][$arPriceType["NAME"]] = $arPriceType;
            }
        }

        foreach ($arRegion["LIST_PRICES"] as $key => $arRegionPrice) {
            if (preg_match('/^REG\d_RETAIL_SALE$/', $key) || preg_match('/^Розница.+Скид$/', $key)) {
                self::$arCatalogPrices['DISCOUNT_PRICE_CODE'] = $key;
            } elseif (preg_match('/^REG\d_RETAIL$/', $key) || preg_match('/^Розница.+$/', $key)) {
                self::$arCatalogPrices['PRICE_CODE'] = $key;
            }
        }
    }
}