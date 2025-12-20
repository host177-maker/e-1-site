<?php
/**
 * @author Shmakov Fedot
 * @date 12.04.2022
 * @see 32482
 */

use Cosmos\Config;

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}
$idIBlock = 49;
$ib = new CIBlock;
$arFields = Array(
  "CODE" => 'offers',
  );
  if ($ib->Update($idIBlock, $arFields)) {
    echo "\e[1;32m Успешно обновлено поле инфоблока \"" . $aFields["NAME"] . "\" \e[0m\n";
  } else {
    echo "\e[1;31m Не удалось обновить инфоблок \"" . $aFields["NAME"] . "\" \e[0m\n";
    echo "\e[1;31m Error: " . $ib->LAST_ERROR . " \e[0m\n";
  }


/**
 * Создаём свойства инфоблоков
 */
Config::getInstance()->init();

$oCIBlockProperty = new CIBlockProperty;
$oCIBlockPropertyEnum = new CIBlockPropertyEnum;
$aIblockProperties = [
    [
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('offers'),
        "CODE" => "IS_HOME_OFFERS",
        "NAME" => "Выводить на главную ТП",
        "PROPERTY_TYPE" => "L",
        "LIST_TYPE" => "C",
        "ITEMS" => [
            [
                "VALUE" => "Да",
                "XML_ID" => "Y",
                "SORT" => 100,
            ],
        ],
    ],
];

foreach ($aIblockProperties as $aFields) {
    $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
    $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
        echo "\e[1;33m Свойство \"" . $aFields["NAME"] . "\" уже существует \e[0m\n";
    } else {
        if ($iPropertyId = $oCIBlockProperty->Add($aFields)) {
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


$aFilter = array("IBLOCK_ID" =>  Config::getInstance()->getIblockIdByCode('offers'), "CODE" => 'layt_2_kh_dvernyy_fasad_dsp_zerkalo_venge_shkaf_kupe_lite_2_kh_dvernyy_800kh2120kh595_');
    $oDbRes = CIBlockElement::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
         if (1) {
            CIBlockElement::SetPropertyValuesEx($aDbRes["ID"], false, array( ["IS_HOME_OFFERS"]["VALUE"] => "Да"));
            echo "\e[1;32m Успешно обновлено  значение \"" . $aDbRes["VALUE"] . "\" свойства \"" . $aDbRes["NAME"] . "\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось создать значение \"" . $aDbRes["VALUE"] . "\" свойства \"" . $aDbRes["NAME"] . "\" \e[0m\n";
        }
    }