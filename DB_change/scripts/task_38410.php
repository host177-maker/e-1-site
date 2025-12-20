<?php

/**
 * @author Shmakov Fedot
 * @date 21.06.2023
 * @see 38410
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
        "ENTITY_ID" => "BLOG_COMMENT",
        "FIELD_NAME" => "UF_BLOG_ORDER_ID",
        "USER_TYPE_ID" => "string",
        "TEXT" => "ID Заказа",
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

