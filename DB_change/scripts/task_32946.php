<?php
/**
 * @author Shmakov Fedot
 * @date 25.04.2022
 * @see 32946
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
        "CODE" => "messengers",
        "NAME" => "Мессенджеры",
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
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('messengers'),
        'NAME' => "Названиe мессенджера",
        'PROPERTY_TYPE' => "S",
        "CODE" => "NAME",
        "REQUIRED" => "Y",
        "IS_REQUIRED" => "Y",
    ],
    [
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('messengers'),
        'NAME' => "Ссылка на мессенджер",
        'PROPERTY_TYPE' => "S",
        "CODE" => "LINK",
        "REQUIRED" => "Y",
        "IS_REQUIRED" => "Y",
    ],
    [
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('messengers'),
        "NAME" => "Иконка мессенджера Header",
        "CODE" => "FILE",
        "SORT" => 500,
        "PROPERTY_TYPE" => "F",
        "REQUIRED" => "Y",
        "IS_REQUIRED" => "Y",
        "WITH_DESCRIPTION" => "Y"
    ],
    [
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('messengers'),
        "NAME" => "Иконка мессенджера Footer",
        "CODE" => "FILE_FOOTER",
        "SORT" => 500,
        "PROPERTY_TYPE" => "F",
        "REQUIRED" => "Y",
        "IS_REQUIRED" => "Y",
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

$el = new CIBlockElement;
$arParams = array("replace_space"=>"-","replace_other"=>"-");

$arFile = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/images/icons/telegram.png");
$arFileFooter = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/images/icons/telegram_footer.png");
$arFile1 = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/images/icons/services_telegram.png");
$arFileFooter1 = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/images/icons/services_telegram_footer.png");
$arFile2 = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/images/icons/whatsup.png");
$arFileFooter2 = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/images/icons/whatsup_footer.png");
$arFile3 = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/images/icons/viber_header.png");
$arFileFooter3 = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/images/icons/viber_footer.png");

$aElements = [
    [
        'NAME' => 'Чат телеграмм',
        'IBLOCK_ID' => Config::getInstance()->getIblockIdByCode('messengers'),
        'ACTIVE' => 'Y',
        'PROPERTY_VALUES' => [
            'NAME' => 'Чат телеграмм',
            'LINK' => 'https://telegram.org',
            'FILE' =>Array("VALUE"=>$arFile, "DESCRIPTION" => "Чат телеграмм"),
            'FILE_FOOTER' =>Array("VALUE"=>$arFileFooter, "DESCRIPTION" => "Чат телеграмм"),
        ],
    ],
    [
        'NAME' => 'Сервис телеграмм',
        'IBLOCK_ID' => Config::getInstance()->getIblockIdByCode('messengers'),
        'ACTIVE' => 'Y',
        'PROPERTY_VALUES' => [
            'NAME' => 'Сервис телеграмм',
            'LINK' => 'https://telegram.org',
            'FILE' =>Array("VALUE"=>$arFile1, "DESCRIPTION" => "Сервис телеграмм"),
            'FILE_FOOTER' =>Array("VALUE"=>$arFileFooter1, "DESCRIPTION" => "Сервис телеграмм"),
        ],
    ],
    [
        'NAME' => 'Ватсап',
        'IBLOCK_ID' => Config::getInstance()->getIblockIdByCode('messengers'),
        'ACTIVE' => 'Y',
        'PROPERTY_VALUES' => [
            'NAME' => 'Ватсап',
            'LINK' => 'https://web.whatsapp.com/',
            'FILE' =>Array("VALUE"=>$arFile2, "DESCRIPTION" => "Ватсап"),
            'FILE_FOOTER' =>Array("VALUE"=>$arFileFooter2, "DESCRIPTION" => "Ватсап"),
        ],
    ],
    [
        'NAME' => 'Вайбер',
        'IBLOCK_ID' => Config::getInstance()->getIblockIdByCode('messengers'),
        'ACTIVE' => 'Y',
        'PROPERTY_VALUES' => [
            'NAME' => 'Вайбер',
            'LINK' => 'https://www.viber.com/ru/',
            'FILE' =>Array("VALUE"=>$arFile3, "DESCRIPTION" => "Вайбер"),
            'FILE_FOOTER' =>Array("VALUE"=>$arFileFooter3, "DESCRIPTION" => "Вайбер"),
        ],
    ],
];

foreach ($aElements as $key => $aElement) {
    $aElement['CODE'] = Cutil::translit($aElement['NAME'],"ru",$arParams);
    $aElement['SORT'] = '1'.$key;
    $aElement['IBLOCK_ID'] = Config::getInstance()->getIblockIdByCode('messengers');
    if ($el->Add($aElement)) {
        echo "\e[1;32m Успешно создан элемент \"" . $aElement["NAME"] . "\" \e[0m\n";
    } else {
        echo "\e[1;31m Не удалось создать элемент \"" . $aElement["NAME"] . "\"\e[0m\n";
        echo "\e[1;31m Error: " . $el->LAST_ERROR . " \e[0m\n";
    }
}