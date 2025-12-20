<?php
/**
 * @author Shmakov Fedot
 * @date 22.05.2023
 * @see 37889
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
 * Создаём инфоблоки
 */
$oCIBlock = new CIBlock;
$aIblocks = [
    [
        "IBLOCK_TYPE_ID" => "aspro_max_content",
        "LID" => "s1",
        "CODE" => "photogallery_e1",
        "NAME" => "Фотогалерея наших работ",
        "LIST_PAGE_URL" => "",
        "SECTION_PAGE_URL" => "",
        "DETAIL_PAGE_URL" => "",
        "INDEX_ELEMENT" => "N",
        "INDEX_SECTION" => "N",
        "GROUP_ID" => [2 => "R"],
    ],
];
foreach ($aIblocks as $aFields) {
    $oDbRes = CIBlock::getList([], ['TYPE' => $aFields['IBLOCK_TYPE_ID'], 'SITE_ID' => $aFields['LID'], 'CODE' => $aFields['CODE']]);
    if ($aIblock = $oDbRes->fetch()) {
        echo "\e[1;33m Инфоблок \"" . $aFields["NAME"] . "\" уже существует \e[0m\n";
    } else {
        if ($oCIBlock->Add($aFields)) {
            echo "\e[1;32m Успешно создан инфоблок \"" . $aFields["NAME"] . "\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось создать инфоблок \"" . $aFields["NAME"] . "\" \e[0m\n";
            echo "\e[1;31m Error: " . $oCIBlock->LAST_ERROR . " \e[0m\n";
        }
    }
}



/**
 * Создаем свойства инфоблоков
 */
Config::getInstance()->init();
$oCIBlockProperty = new CIBlockProperty;
$oCIBlockPropertyEnum = new CIBlockPropertyEnum;
$aIblockProperties = [
    [
        "IBLOCK_ID"             => Config::getInstance()->getIblockIdByCode('photogallery_e1'),
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
        "IBLOCK_ID"             => Config::getInstance()->getIblockIdByCode('photogallery_e1'),
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
    [
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('photogallery_e1'),
        "NAME" => "Картинки",
        "CODE" => "FILE",
        "SORT" => 500,
        "PROPERTY_TYPE" => "F",
        "REQUIRED" => "Y",
        "IS_REQUIRED" => "Y",
        "MULTIPLE"              => "Y",
        "WITH_DESCRIPTION" => "Y"
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
