<?
set_time_limit(0);
ignore_user_abort(true);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
ini_set('memory_limit', '2048M');
$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__FILE__)) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main\Config\Option,
    \Bitrix\Main\Web\Json,
    \Bitrix\Highloadblock\HighloadBlockTable,
    \Bitrix\Main\Context;

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

$arProp = array("ID", "XML_ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "IBLOCK_SECTION_ID");
$aCosmosConfigIblock = \Cosmos\Config::getInstance()->getParam('IBLOCK');
$strPathDir = dirname(dirname(__FILE__)) . "/scripts";

$sale_currency = 'RUB';
$arFilterCatalog = array(
    "IBLOCK_ID" => $aCosmosConfigIblock["1c_catalog"]["ID"]
);
//получаем количество элементов
$cnt = CIBlockElement::GetList(false, $arFilterCatalog, array('IBLOCK_ID'))->Fetch()['CNT'];
echo "\e[1;32m[" . date("d.m.Y H:i:s") . " Количество элементов в инфоблоке - " . $cnt . " \e[0m\n";
$arProp = array("ID", "XML_ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "IBLOCK_SECTION_ID",);

$arPriceGroups = array();
//получаем цены по умолчанию
$arPriceGroups = explode(',', Option::get('e1.site.settings', 'E1_SS_DEFAULT_PRICE', '235,236', 's1'));

//получаем все типы цен
$dbPriceType = CCatalogGroup::GetList(
    array("SORT" => "ASC"),
    array()
);
while ($arPriceType = $dbPriceType->Fetch()) {
    $arPriceGroups[] = $arPriceType['ID'];
    $arPriceGroupsName[$arPriceType['ID']] = $arPriceType['NAME'];
}

$arFilterIdElement = array("IBLOCK_ID" => $aCosmosConfigIblock["1c_catalog"]["ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", ">=ID" => 1);
$resElementEnd = CIBlockElement::GetList(['ID' => 'DESC'], $arFilterIdElement, false, array("nPageSize" => 500), $arProp)->Fetch()['ID'];
echo "\e[1;32m[" . date("d.m.Y H:i:s") . " ID последнего элемента инфоблока - " . $resElementEnd . " \e[0m\n";
$arFilterIdElement = array("IBLOCK_ID" => $aCosmosConfigIblock["1c_catalog"]["ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", ">=ID" => 1);
//получаем id 1-го элемента
$resElementStart = CIBlockElement::GetList(['ID' => 'ASC'], $arFilterIdElement, false, array("nPageSize" => 500), $arProp)->Fetch()['ID'];

function recursive()
{
    global $USER, $aCosmosConfigIblock, $sale_currency, $arProp, $strPathDir, $arPriceGroups, $resElementStart, $arPriceGroupsName;

    if (file_exists($strPathDir . '/updatePriceForFilter.log')) {
        $count = file_get_contents($strPathDir . '/updatePriceForFilter.log');
        $count = intval($count);
    } else {
        $count = (int) $resElementStart;
    }

    $arFilterElementCatalog = array(
        "IBLOCK_ID" => $aCosmosConfigIblock["1c_catalog"]["ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y",
        ">=ID" =>$count
        //"ID" =>  array(82963) //117154  #114191
    );

    $resDb = CIBlockElement::GetList(['ID' => 'ASC'], $arFilterElementCatalog, false, array("nPageSize" => 30), $arProp);
    $arInfo = CCatalogSKU::GetInfoByProductIBlock($aCosmosConfigIblock["1c_catalog"]["ID"]);
    $arOffersPrice = [];
    while ($arElement = $resDb->Fetch()) {
        $arResult = array();
        $intIdElement = intval($arElement["ID"]);

        // Ищем все торговые предложения
        $rsOffers = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_' . $arInfo['SKU_PROPERTY_ID'] => $arElement["ID"]), false, array("nPageSize" => 2000), array('PROPERTY_BASIC_CONFIGURATION', 'ID'));
        while ($arOffer = $rsOffers->Fetch()) {
            $arOffer['PROPERTIES']['CML2_LINK']['VALUE'] = $arElement["ID"];
            $arOffer['PROPERTIES']['BASIC_CONFIGURATION']['VALUE'] = $arOffer["PROPERTY_BASIC_CONFIGURATION_VALUE"];
            $offers[] = $arOffer;
        }

        foreach ($offers as $offer) {
            $dbElement = \CIBlockElement::GetByID($offer['PROPERTIES']['CML2_LINK']['VALUE']);
            if ($obElement = $dbElement->GetNextElement()) {
                $rsElement = $obElement->GetFields();
                $rsElement['PROPERTIES']['PROTSENT_SKIDKI'] = $obElement->GetProperty('PROTSENT_SKIDKI');
                $rsElement['PROPERTIES']['SERIYA_SHKAFA'] = $obElement->GetProperty('SERIYA_SHKAFA');
                $rsElement['PROPERTIES']['BASE_COST_LAYOUT'] = $obElement->GetProperty('BASE_COST_LAYOUT');
            }

            $rsGetPrice = \Bitrix\Catalog\Model\Price::getList([
                "filter" => [
                    "PRODUCT_ID" => $offer['ID'],
                    "CATALOG_GROUP_ID" => $arPriceGroups
                ]
            ]);

            $rsOffer = [];

            while ($arGetPrice = $rsGetPrice->fetch()) {
                $arDiscounts = CCatalogDiscount::GetDiscountByProduct($arGetPrice['PRODUCT_ID'], $USER->GetUserGroupArray(), "N");
                if (is_array($arDiscounts) && sizeof($arDiscounts) > 0) {
                    $arGetPrice['DISCOUNT_PRICE'] = CCatalogProduct::CountPriceWithDiscount($arGetPrice['PRICE'], $arGetPrice['CURRENCY'], $arDiscounts);
                    $arGetPrice['DISCOUNT'] = $arGetPrice['PRICE'] - $arGetPrice['DISCOUNT_PRICE'];
                    $arGetPrice['PERCENT'] = ($arGetPrice['DISCOUNT'] / $arGetPrice['PRICE']) * 100;
                }

                if ($arGetPrice['DISCOUNT_PRICE'] == 0 && $arGetPrice['PRICE'] != 0) {
                    $arGetPrice['DISCOUNT_PRICE'] = $arGetPrice['PRICE'];
                }

                $rsOffer["PRICES"][$arPriceGroupsName[$arGetPrice['CATALOG_GROUP_ID']]] = array(
                    "VALUE" => $arGetPrice['PRICE'],
                    "DISCOUNT_VALUE" => $arGetPrice['DISCOUNT_PRICE'],
                    "CURRENCY" => $arGetPrice['CURRENCY'],
                    "PRINT_VALUE" => CurrencyFormat($arGetPrice['PRICE'], $arGetPrice['CURRENCY']),
                    "PRINT_DISCOUNT_VALUE" => CurrencyFormat($arGetPrice['DISCOUNT_PRICE'], $arGetPrice['CURRENCY']),
                    "DISCOUNT_DIFF" => $arGetPrice['DISCOUNT'],
                    "PRINT_DISCOUNT_DIFF" => CurrencyFormat($arGetPrice['DISCOUNT'], $arGetPrice['CURRENCY']),
                    "DISCOUNT_DIFF_PERCENT" => $arGetPrice['PERCENT'],
                    "CAN_ACCESS" => "Y"
                );

            }

            \COrwoFunctions::ChangeCatalogItemPrice($rsElement, $rsOffer, []);
            if (!empty($rsOffer["PRICES"]["Розница Москва Скид"]["DISCOUNT_VALUE"]))
                $arOffersPrice['MSK'][] = (int) $rsOffer["PRICES"]["Розница Москва Скид"]["DISCOUNT_VALUE"];
            if (!empty($rsOffer["PRICES"]["Розница Сибирь Скид"]["DISCOUNT_VALUE"]))
                $arOffersPrice['NSK'][] = (int) $rsOffer["PRICES"]["Розница Сибирь Скид"]["DISCOUNT_VALUE"];
        }

        $arPropertyValuesPrice = [];
        $arPropertyValuesPrice["MAXIMUM_PRICE"] = ['VALUE' => max($arOffersPrice['MSK'])];
        $arPropertyValuesPrice["MAXIMUM_PRICE_SIB"] = ['VALUE' => max($arOffersPrice['NSK'])];
        $arPropertyValuesPrice["MINIMUM_PRICE"] = ['VALUE' => ($arOffersPrice['MSK'][0])];
        $arPropertyValuesPrice["MINIMUM_PRICE_SIB"] = ['VALUE' => ($arOffersPrice['NSK'][0])];
        try {
            //обновляем
            \CIBlockElement::SetPropertyValuesEx($intIdElement, $aCosmosConfigIblock["1c_catalog"]["ID"], $arPropertyValuesPrice);
            echo "\e[1;32m Успешно обновлен элемент \"" . $arElement["NAME"] . ' ID=' . $arElement["ID"] . "\" \e[0m\n";
        } catch (Exception $e) {
            echo "\e[1;31m Не удалось обновить элемент \"" . $arElement["NAME"] . "\" \e[0m\n";
            echo "\e[1;31m Ошибка: " . $e->getMessage() . " \e[0m\n";
        }
    }
    unset($arPropertyValuesPrice, $arElement, $arResult, $offers, $offer, $rsElement, $rsOffer, $arGetPrice, $arPrice, $dbElement, $obElement, $resDb, $arOffersPrice);

    $count = $intIdElement;
    //пишем во временный файл количество, а в начале считываем, и так проходим по всем элементам с шагом 50 элементов
    file_put_contents($strPathDir . '/updatePriceForFilter.log', $count);

    sleep(3);
    return (int) $count;
}
//recursive();die('Завершение');
while(1) {
    $count = recursive();
    if ($count < (int) $resElementEnd) {
        recursive();
    } else {
        echo "\e[1;32m Скрипт выполнен \e[0m\n";
        unlink($strPathDir . '/updatePriceForFilter.log');
        break;
    }
}

echo "\e[1;32m Скрипт завершен \e[0m\n";
