<?php
/**
 * @author Shmakov Fedot
 * @date 01.06.2023
 * @see 38137
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
        "IBLOCK_ID"             => Config::getInstance()->getIblockIdByCode('instructions'),
        "NAME"                  => "Привязка к серии ",
        "CODE"                  => "SERIYA_SHKAFA",
        "SORT"                  => "300",
        "USER_TYPE" => "list_link",
        "MULTIPLE"              => "N",
        "HINT"                  => "Привязка к серии в инфоблоке Каталог.",
        "PROPERTY_TYPE"         => "E",
        "LINK_IBLOCK_TYPE_ID"   => "1c_catalog",
        "LINK_IBLOCK_ID"        => Config::getInstance()->getIblockIdByCode('1c_catalog')
    ],
    [
        "IBLOCK_ID"             => Config::getInstance()->getIblockIdByCode('instructions'),
        "NAME"                  => "Привязка к типу ",
        "CODE"                  => "TIP_SHKAFA",
        "USER_TYPE" => "list_link",
        "SORT"                  => "300",
        "MULTIPLE"              => "N",
        "HINT"                  => "Привязка к типу в инфоблоке Каталог.",
        "PROPERTY_TYPE"         => "E",
        "LINK_IBLOCK_TYPE_ID"   => "1c_catalog",
        "LINK_IBLOCK_ID"        => Config::getInstance()->getIblockIdByCode('1c_catalog')
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
