<?php

/**
 * @author Samoluk Alexey
 * @date 11.07.2022
 * @see 33564
 */

define("NOT_CHECK_PERMISSIONS", true);

use Cosmos\Config;
use Bitrix\Main\Loader;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!Loader::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}


/**
 * Создаём свойство инфоблока
 */
Config::getInstance()->init();
$aCosmosConfigIblock = Config::getInstance()->getParam("IBLOCK");
$oCIBlockProperty = new CIBlockProperty;
$aIblockProperties = array(
    array(
        "IBLOCK_ID" => $aCosmosConfigIblock["1c_catalog"]["ID"],
        "NAME" => "Коэф цены без скидки",
        "CODE" => "WHITHOUT_DISCOUNT_RATIO",
        "PROPERTY_TYPE" => "N",
        "SORT" => '35',
    ),
);
foreach ($aIblockProperties as $aFields) {
    $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
    $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
        echo "\e[1;33m Свойство \"".$aFields["NAME"]."\" уже существует \e[0m\n";
    } else {
        if ($oCIBlockProperty->Add($aFields)) {
            echo "\e[1;32m Успешно создано свойство \"".$aFields["NAME"]."\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось создать свойство \"".$aFields["NAME"]."\" \e[0m\n";
            echo "\e[1;31m Error: ".$oCIBlockProperty->LAST_ERROR." \e[0m\n";
        }
    }
}

\E_1\EventHandler\OnSuccesCatalogImport1C::discountRatioReindex();