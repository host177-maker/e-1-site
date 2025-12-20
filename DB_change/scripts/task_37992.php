<?php
/**
 * @author Kuznetsov Danil
 * @date 29.05.2023
 * @see 37992
 */

use Cosmos\Config;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__)))."/public";
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}

/**
 * Создаём свойство инфоблока Оставить свой отзыв
 */

Config::getInstance()->init();
$ibId = Config::getInstance()->getIblockIdByCode('aspro_max_add_review');
$oCIBlockProperty = new CIBlockProperty;
$oCIBlockPropertyEnum = new CIBlockPropertyEnum;
$aIblockProperties = array(
    array(
        "IBLOCK_ID" => $ibId,
        "NAME" => "№ Заказа",
        "CODE" => "AGREEMENT",
        "PROPERTY_TYPE" => "S",
        "IS_REQUIRED" => "Y",
    ),
);
foreach ($aIblockProperties as $aFields) {
    $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
    $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
        echo "\e[1;33m Свойство \"".$aFields["NAME"]."\" уже существует \e[0m\n";
    } else {
        if ($iPropertyId = $oCIBlockProperty->Add($aFields)) {
            echo "\e[1;32m Успешно создано свойство \"".$aFields["NAME"]."\" \e[0m\n";

        } else {
            echo "\e[1;31m Не удалось создать свойство \"".$aFields["NAME"]."\" \e[0m\n";
            echo "\e[1;31m Error: ".$oCIBlockProperty->LAST_ERROR." \e[0m\n";
        }
    }
}