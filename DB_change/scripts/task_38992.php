<?php
/**
 * @author Shmakov Fedot
 * @date 28.07.2023
 * @see 38992
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
 * Создаём тип инфоблока
 */
$oCIBlockType = new CIBlockType;
$aIblockTypes = array(
    array(
        "ID" => "discounts_custom",
        "SORT" => "200",
        "SECTIONS" => "N",
        "LANG" => array(
            "ru" => array(
                "NAME" => "Скидки кастомные в корзине",
                "SECTION_NAME" => "",
                "ELEMENT_NAME" => "Скидка",
            ),
            "en" => array(
                "NAME" => "Discounts",
                "SECTION_NAME" => "",
                "ELEMENT_NAME" => "Discount",
            )
        )
    )
);
foreach ($aIblockTypes as $aFields) {
    $aFilter = array("ID" => $aFields["ID"]);
    $oDbRes = CIBlockType::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
        echo "\e[1;33m Тип инфоблоков \"".$aDbRes["ID"]."\" уже существует \e[0m\n";
    } else {
        if ($oCIBlockType->Add($aFields)) {
            echo "\x1b[1;32m Успешно создан тип инфоблоков \"".$aFields["ID"]."\" \x1b[0m\n";
        } else {
            echo "\x1b[1;31m Не удалось создать тип инфоблоков \"".$aFields["ID"]."\" \x1b[0m\n";
            echo "\x1b[1;31m Error: ".$oCIBlockType->LAST_ERROR." \x1b[0m\n";
        }
    }
}

/**
 * Создаём инфоблок
 */
$oCIBlock = new CIBlock;
$aIblocks = array(
    array(
        "IBLOCK_TYPE_ID" => "discounts_custom",
        "LID" => 's1',
        "CODE" => "discount_custom",
        "NAME" => "Скидки кастомные в корзине",
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
        "IBLOCK_ID"             => $aCosmosConfigIblock["discount_custom"]["ID"],
        "NAME"                  => "Скидка (%)",
        "CODE"                  => "SKIDKA",
        "SORT"                  => "100",
        "IS_REQUIRED"           => "Y",
        "PROPERTY_TYPE"         => "N"
    ],
    [
        "IBLOCK_ID"             => $aCosmosConfigIblock["discount_custom"]["ID"],
        "NAME"                  => "Товары",
        "CODE"                  => "SKIDKA_TOVARY",
        "SORT"                  => "300",
        "MULTIPLE"              => "Y",
        "HINT"                  => "Если выбран один или несколько товаров, то скидка распространяется на них независимо от выбранных разделов.",
        "PROPERTY_TYPE"         => "E",
        "LINK_IBLOCK_TYPE_ID"   => "1c_catalog",
        "LINK_IBLOCK_ID"        => $aCosmosConfigIblock["1c_catalog"]["ID"]
    ],
    [
        "IBLOCK_ID"             => $aCosmosConfigIblock["discount_custom"]["ID"],
        "NAME"                  => "Количество товаров",
        "CODE"                  => "COUNT",
        "SORT"                  => "100",
        "IS_REQUIRED"           => "Y",
        "PROPERTY_TYPE"         => "N"
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

