<?php
/**
 * @author Arkhipenko Andrey
 * @date 19.04.2022
 * @see 32566
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
        "CODE" => "exist_templates",
        "NAME" => "Шаблоны наличия",
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
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('exist_templates'),
        "NAME" => "От",
        "CODE" => "FROM",
        "SORT" => 400,
        "PROPERTY_TYPE" => "N",
    ],
    [
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('exist_templates'),
        "NAME" => "До",
        "CODE" => "TO",
        "SORT" => 500,
        "PROPERTY_TYPE" => "N",
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
