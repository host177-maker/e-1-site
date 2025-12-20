<?php
/**
 * @author Shmakov Fedot
 * @date 06.10.2023
 * @see 39759
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
        "IBLOCK_ID"             => Config::getInstance()->getIblockIdByCode('aspro_max_vacancy'),
        "NAME"                  => "Город (из регионов) ",
        "CODE"                  => "CITY_REGION",
        "SORT"                  => "300",
        //"USER_TYPE" => "list_link",
        "MULTIPLE"              => "N",
        "HINT"                  => "Привязка к городу в инфоблоке Регионы.",
        "PROPERTY_TYPE"         => "E",
        "LINK_IBLOCK_TYPE_ID"   => "aspro_max_regionality",
        "LINK_IBLOCK_ID"        => Config::getInstance()->getIblockIdByCode('aspro_max_regions')
    ],
];
foreach ($aIblockProperties as $aFields) {
    $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
    $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
    if ($aDbRes = $oDbRes->fetch()) {
        /*if ($oCIBlockProperty->Update($aDbRes["ID"], $aFields)) {
            echo "\e[1;32m Успешно обновлено свойство \"" . $aFields["CODE"] . "\" \e[0m\n";
        } else {
            echo "\e[1;31m Не удалось обновить свойство \"" . $aFields["CODE"] . "\" \e[0m\n";
            echo "\e[1;31m Error: " . $oCIBlockProperty->LAST_ERROR . " \e[0m\n";
        }*/
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

function get_cityID($cityName = '') {
    $arResult = '';
    if (!empty($cityName)) {
            $arSelect = Array("ID", "IBLOCK_ID", "NAME");
            $arFilter = Array("IBLOCK_ID"=> \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), "NAME" => $cityName, "ACTIVE_DATE"=> "Y", "ACTIVE"=>"Y");
            $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            if ($arRegionCity = $res->Fetch()){
                $arResult = $arRegionCity;
            }
    }
    return $arResult;

}
//получим введенный город у вакансий и привяжем к регионам
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "CODE", 'PROPERTY_CITY', 'PROPERTY_CITY', 'PROPERTY_CITY_REGION');
$arFilter = Array("IBLOCK_ID"=> \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_vacancy'), "ACTIVE_DATE"=> "Y", "ACTIVE"=>"Y", "!PROPERTY_CITY_VALUE" => '');
$res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
$arVacanciesUpdateCityRegion = [];
while ($arVacancies = $res->Fetch()){
    if (empty($arVacancies["PROPERTY_CITY_REGION_VALUE"]) && !empty($arVacancies["PROPERTY_CITY_VALUE"])) {
        $resultCity = get_cityID($arVacancies["PROPERTY_CITY_VALUE"]);
        if (!empty($resultCity["ID"]))
        $arVacanciesUpdateCityRegion[] = [
            'ID' => $arVacancies["ID"],
            'IBLOCK_ID' => $arVacancies["IBLOCK_ID"],
            'CODE' => $arVacancies["CODE"],
            'NAME' => $arVacancies["NAME"],
            'PROPERTY_VALUES' => array(
                'CITY_REGION' => $resultCity["ID"]
            ),
        ];
    }
}
//обновляем привязку к региону вакансий
foreach ($arVacanciesUpdateCityRegion as $fields) {
    $filter = array('IBLOCK_ID' => $fields['IBLOCK_ID'], 'CODE' => $fields['CODE']);
    $select = array('IBLOCK_ID', 'ID');
    $result = CIBlockElement::GetList(array(), $filter, false, false, $select);
    if ($row = $result->fetch()) {
        if (true) {
            CIBlockElement::SetPropertyValuesEx($row['ID'], $row['IBLOCK_ID'], $fields['PROPERTY_VALUES']);
            echo "\e[1;32m Успешно обновлен элемент \"" . $fields['NAME'] . "\" \e[0m\n";
        }
    } else {
        echo "\e[1;31m Не удалось обновить элемент \"" . $fields['NAME'] . "\" \e[0m\n";
        echo "\e[1;31m Error: " . $CIBlockElement->LAST_ERROR . " \e[0m\n";
    }
}