<?php

/**
 * @author Baljinimaev Artem
 * @date 21.07.2023
 * @see 38730
 */

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

use Cosmos\Config;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}

Config::getInstance()->init();
$aCosmosConfigIblock = Config::getInstance()->getParam('IBLOCK');

$oCIBlockProperty = new CIBlockProperty;
$oCIBlockPropertyEnum = new CIBlockPropertyEnum;
$aIblockProperties = array(
    array(
        "IBLOCK_ID" => $aCosmosConfigIblock["aspro_max_regions"]["ID"],
        "NAME" => "Не выводить сборку у товара",
        "ACTIVE" => "Y",
        "CODE" => "SHOW_PRODUCT_BUILDING",
        "PROPERTY_TYPE" => "L",
        "LIST_TYPE" => "C",
        "SORT" => "500",
        "ITEMS" => array(
            array(
                "VALUE" => "Y",
                "XML_ID" => "Y",
            ),
        ),
    ),
);
foreach ($aIblockProperties as $aFields) {
    $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
    $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
        echo "\e[1;33m Свойство \"" . $aFields["NAME"] . "\" уже существует \e[0m\n";
    } else {
        if ($iPropertyId = $oCIBlockProperty->Add($aFields)) {
            echo "\e[1;32m Успешно создано свойство \"" . $aFields["NAME"] . "\" \e[0m\n";
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
            echo "\e[1;31m Не удалось создать свойство \"" . $aFields["NAME"] . "\" \e[0m\n";
            echo "\e[1;31m Error: " . $oCIBlockProperty->LAST_ERROR . " \e[0m\n";
        }
    }
}
