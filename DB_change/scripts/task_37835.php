<?php

/**
 * @author Volkov Kirill
 * @date 11.05.2023
 * @see 37835
 */

define("NOT_CHECK_PERMISSIONS", true);

use Cosmos\Config as Cosmos_Config;
use Bitrix\Main\Loader;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__)))."/public";
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/**
 * Создаём пользовательское свойство
 */
Cosmos_Config::getInstance()->init();
$aCosmosConfigIblock = Cosmos_Config::getInstance()->getParam("IBLOCK");
$oCUserTypeEntity = new CUserTypeEntity();
$aUserProperties = array(
    array(
        "ENTITY_ID" => "IBLOCK_48_SECTION",
        "FIELD_NAME" => "UF_IS_FILE",
        "USER_TYPE_ID" => "boolean",
        "MULTIPLE" => "N",
        "TEXT" => "Выводить раздел как ссылку на файл",
        "SETTINGS" => array(
            "DISPLAY" => "LIST",
            "LIST_HEIGHT" => 5,
            "DEFAULT_VALUE" => "0",
        ),
    ),
    array(
        "ENTITY_ID" => "IBLOCK_48_SECTION",
        "FIELD_NAME" => "UF_FILE",
        "USER_TYPE_ID" => "file",
        "MULTIPLE" => "N",
        "TEXT" => "Файл при нажатии на раздел",
        "SETTINGS" => array(
            "DISPLAY" => "LIST",
            "LIST_HEIGHT" => 5,
            "DEFAULT_VALUE" => "",
        ),
    ),

);
foreach ($aUserProperties as $aFields) {
    $aFilter = array("ENTITY_ID" => $aFields["ENTITY_ID"], "FIELD_NAME" => $aFields["FIELD_NAME"]);
    $oDbRes = CUserTypeEntity::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->Fetch()) {
        echo "\e[1;33m Пользовательское свойство \"".$aFields["FIELD_NAME"]."\" уже существует \e[0m\n";
    } else {
        $aFields["EDIT_FORM_LABEL"] = array("ru" => $aFields["TEXT"], "en" => $aFields["TEXT"]);
        $aFields["LIST_COLUMN_LABEL"] = array("ru" => $aFields["TEXT"], "en" => $aFields["TEXT"]);
        $aFields["LIST_FILTER_LABEL"] = array("ru" => $aFields["TEXT"], "en" => $aFields["TEXT"]);
        $aFields["ERROR_MESSAGE"] = array("ru" => $aFields["TEXT"], "en" => $aFields["TEXT"]);
        $aFields["HELP_MESSAGE"] = array("ru" => $aFields["TEXT"], "en" => $aFields["TEXT"]);
        unset($aFields["TEXT"]);

        if ($oCUserTypeEntity->Add($aFields)) {
            echo "\e[1;32m Успешно создано пользовательское свойство \"".$aFields["FIELD_NAME"]."\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось создать пользовательское свойство \"".$aFields["FIELD_NAME"]."\" \e[0m\n";
            if ($oException = $APPLICATION->GetException()) {
                echo "\e[1;31m Error: ".$oException->GetString()." \e[0m\n";
            }
        }
    }
}