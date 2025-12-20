<?php

/**
 * @author Shmakov Fedot
 * @date 18.07.2022
 * @see 33602
 */

define("NOT_CHECK_PERMISSIONS", true);

use Cosmos\Config;
use Bitrix\Main\Loader;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__)))."/public";
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arFields = Array(
    "ENTITY_ID" => "IBLOCK_48_SECTION",
    "FIELD_NAME" => "UF_IS_TAG",
    "USER_TYPE_ID" => "boolean",
    "EDIT_FORM_LABEL" => Array("ru"=>"Выводить раздел как тег", "en"=>"Display section as tag"),
    "MULTIPLE" => "N",
    "SETTINGS" => array(
        "DISPLAY" => "LIST",
        "LIST_HEIGHT" => 5,
        //"IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('aspro_allcorp2_staff'),
        "DEFAULT_VALUE" => "0",
        //"ACTIVE_FILTER" => "N",
    ),
);

$obUserField  = new CUserTypeEntity;
if (1) {
    $obUserField->Add($arFields);
    echo "\e[1;32m Успешно создано значение \e[0m\n";
} else {
    echo "\e[1;31m Не удалось создать значение  \e[0m\n";
}