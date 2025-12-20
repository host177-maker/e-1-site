<?php
/**
 * @author Shmakov Fedot
 * @date 25.04.2022
 * @see 34307
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
 * Создаем свойства инфоблоков
 */
Config::getInstance()->init();
$oCIBlockProperty = new CIBlockProperty;
$oCIBlockPropertyEnum = new CIBlockPropertyEnum;
$aIblockProperties = [
    [
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('1c_catalog'),
        'NAME' => "Цена товара",
        'PROPERTY_TYPE' => "S",
        "CODE" => "PRICE_PRODUCT",
        "WITH_DESCRIPTION" => "Y",
        "MULTIPLE" => "Y"

    ],
    [
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('1c_catalog'),
        'NAME' => "Скидка товара",
        'PROPERTY_TYPE' => "S",
        "CODE" => "SALE_PRICE_PRODUCT",
        "WITH_DESCRIPTION" => "Y",
        "MULTIPLE" => "Y"
    ],
];
foreach ($aIblockProperties as $aFields) {
    $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
    $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
        if ($oCIBlockProperty->Update($aDbRes["ID"], $aFields)) {
            echo "\e[1;32m Успешно обновлено свойство \"" . $aFields["CODE"] . "\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось обновить свойство \"" . $aFields["CODE"] . "\" \e[0m\n";
            echo "\e[1;31m Error: " . $oCIBlockProperty->LAST_ERROR . " \e[0m\n";
        }

    } else {
        if ($iPropertyId = $oCIBlockProperty->Add($aFields)) {
            $arProperty[$aFields['property_code']] = $iPropertyId;
            echo "\e[1;32m Успешно создано свойство \"" . $aFields["NAME"] . "\" \e[0m\n";

            if (isset($aFields["ITEMS"]) && !empty($aFields["ITEMS"])) {
                foreach ($aFields["ITEMS"] as $aEnumFields) {
                    $aEnumFields["PROPERTY_ID"] = $iPropertyId;
                    if ($oCIBlockPropertyEnum->Add($aEnumFields)) {
                        echo "\e[1;32m Успешно создано значение \"" . $aEnumFields["VALUE"] . "\" свойства \"" . $aFields["NAME"] . "\" \e[0m\n";
                    } else {
                        echo "\e[1;31m Не удалось создать значение \"" . $aEnumFields["VALUE"] . "\" свойства \"" . $aFields["NAME"] . "\" \e[0m\n";
                        echo "\e[1;31m Error: " . $oCIBlockPropertyEnum->LAST_ERROR . " \e[0m\n";
                    }
                }
            }
        } else {
            echo "\e[1;31m Не удалось создать свойство \"" . $aFields["NAME"] . "\" \e[0m\n";
            echo "\e[1;31m Error: " . $oCIBlockProperty->LAST_ERROR . " \e[0m\n";
        }
    }
}