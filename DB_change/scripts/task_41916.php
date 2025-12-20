<?php

/**
 * @author Shmakov Fedot
 * @date 12.01.2024
 * @see 41916
 */

define("NOT_CHECK_PERMISSIONS", true);

use Cosmos\Config;
use Bitrix\Main\Loader;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__)))."/public";
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/**
 * Создаём раздел инфоблока
 */
Cosmos\Config::getInstance()->init();
$aCosmosConfigIblock = Cosmos\Config::getInstance()->getParam("IBLOCK");
$oCIBlockSection = new CIBlockSection;
$aTranslitParams = array(
    "max_len" => 255,
    "change_case" => "L",
    "replace_space" => "_",
    "replace_other" => "_",
    "delete_repeat_replace" => true,
    "safe_chars" => "",
);
$aIblockSections = array(
    array(
        "IBLOCK_ID" => $aCosmosConfigIblock["aspro_max_add_review"]["ID"],
        "NAME" => "Просмотренные",
    ),
    [
        "NAME" => "Не просмотренные",
        "IBLOCK_ID" => $aCosmosConfigIblock["aspro_max_add_review"]["ID"],
    ],
);
foreach ($aIblockSections as $aFields) {
    $aFields["CODE"] = CUtil::translit($aFields["NAME"], "ru", $aTranslitParams);

    $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
    $aSelect = array("IBLOCK_ID", "ID");
    $oDbRes = CIBlockSection::GetList(array(), $aFilter, false, $aSelect);
    if ($aDbRes = $oDbRes->fetch()) {
        echo "\e[1;33m Раздел \"".$aFields["NAME"]."\" уже существует \e[0m\n";
    } else {
        if ($oCIBlockSection->Add($aFields)) {
            echo "\e[1;32m Успешно создан раздел \"".$aFields["NAME"]."\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось создать раздел \"".$aFields["NAME"]."\" \e[0m\n";
            echo "\e[1;31m Error: ".$oCIBlockSection->LAST_ERROR." \e[0m\n";
        }
    }
}


