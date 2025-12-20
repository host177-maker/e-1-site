<?php
/**
 * @author Kirill Volkov
 * @date 28.03.2023
 * @see 37064
 */

use Cosmos\Config;

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$aCosmosConfigIblock = Config::getInstance()->getParam("IBLOCK");

if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}

/**
 * Создаём инфоблок
 */
$oCIBlock = new CIBlock;
$aIblocks = array(
    array(
        "IBLOCK_TYPE_ID" => "aspro_max_catalog",
        "LID" => 's1',
        "CODE" => "products_quantity",
        "NAME" => "Количество товаров",
        "SORT" => "100",
        "LIST_MODE" => "S",
        "LIST_PAGE_URL" => "",
        "SECTION_PAGE_URL" => "",
        "DETAIL_PAGE_URL" => "",
        "INDEX_SECTION" => "N",
        "INDEX_ELEMENT" => "N",
        "WORKFLOW" => "N",
        "GROUP_ID" => array(2 => "R")
    )
);
foreach ($aIblocks as $aFields) {
    if ($aCosmosConfigIblock[$aFields["CODE"]]["ID"]) {
        echo "\e[1;33m Инфоблок \"".$aFields["NAME"]."\" уже существует \e[0m\n";
    } else {
        if ($oCIBlock->Add($aFields)) {
            echo "\e[1;32m Успешно создан инфоблок \"".$aFields["NAME"]."\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось создать инфоблок \"".$aFields["NAME"]."\" \e[0m\n";
            echo "\e[1;31m Error: ".$oCIBlock->LAST_ERROR." \e[0m\n";
        }
    }
}

/**
 * Создаём свойство инфоблока
 */
Config::getInstance()->init();
$aCosmosConfigIblock = Config::getInstance()->getParam("IBLOCK");
$oCIBlockProperty = new CIBlockProperty;
$oCIBlockPropertyEnum = new CIBlockPropertyEnum;
$aIblockProperties = array(
    [
        "IBLOCK_ID"             => $aCosmosConfigIblock["products_quantity"]["ID"],
        "NAME"                  => "Товары",
        "CODE"                  => "PRODUCTS",
        "SORT"                  => "300",
        "MULTIPLE"              => "N",
        "PROPERTY_TYPE"         => "E",
        "LINK_IBLOCK_TYPE_ID"   => "1c_catalog",
        "LINK_IBLOCK_ID"        => $aCosmosConfigIblock["offers"]["ID"]
    ],
    [
        "IBLOCK_ID"             => $aCosmosConfigIblock["products_quantity"]["ID"],
        "NAME"                  => "Количество товара по городам",
        "CODE"                  => "CITY_COUNT",
        "SORT"                  => "400",
        "MULTIPLE"              => "Y",
        "PROPERTY_TYPE"         => "E",
        "USER_TYPE"             => "CustomComposition",
        "LINK_IBLOCK_TYPE_ID"   => "aspro_max_regionality",
        "LINK_IBLOCK_ID"        => $aCosmosConfigIblock["aspro_max_regions"]["ID"]
    ],
);
foreach ($aIblockProperties as $aFields) {
    $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
    $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
        echo "\e[1;33m Свойство \"".$aFields["NAME"]."\" уже существует \e[0m\n";
    } else {
        if ($iPropertyId = $oCIBlockProperty->Add($aFields)) {
            echo "\e[1;32m Успешно создано свойство \"".$aFields["NAME"]."\" \e[0m\n";

            if (isset($aFields["ITEMS"]) && !empty($aFields["ITEMS"])) {
                foreach ($aFields["ITEMS"] as $aEnumFields) {
                    $aEnumFields["PROPERTY_ID"] = $iPropertyId;
                    if ($oCIBlockPropertyEnum->Add($aEnumFields)) {
                        echo "\e[1;32m Успешно создано значение \"".$aEnumFields["VALUE"]."\" свойства \"".$aFields["NAME"]."\" \e[0m\n";
                    } else {
                        echo "\e[1;31m Не удалось создать значение \"".$aEnumFields["VALUE"]."\" свойства \"".$aFields["NAME"]."\" \e[0m\n";
                        echo "\e[1;31m Error: ".$oCIBlockPropertyEnum->LAST_ERROR." \e[0m\n";
                    }
                }
            }
        } else {
            echo "\e[1;31m Не удалось создать свойство \"".$aFields["NAME"]."\" \e[0m\n";
            echo "\e[1;31m Error: ".$oCIBlockProperty->LAST_ERROR." \e[0m\n";
        }
    }
}

