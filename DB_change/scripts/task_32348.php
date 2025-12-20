<?php

/**
 * @author Shmakov Fedot
 * @date 05.05.2022
 * @see 32348
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
        "IBLOCK_ID" => Config::getInstance()->getIblockIdByCode('aspro_max_regions'),
        "NAME" => "Привязка к цене Угловой модуль ( код XML_ID является кодом цены ТП)",
        "CODE" => "REGION_GROUP_CORNER_MODULE",
        "PROPERTY_TYPE" => "L",
        "ITEMS" => [
            [
                "VALUE" => "Цена Сибирь",
                "XML_ID" => "TSENA_UGLOVOY_MODUL_SIBIR",
            ],
            [
                "VALUE" => "Цена Москва",
                "XML_ID" => "PRICE_UM",
            ],
        ],
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
//сибирь
$code = ['barnaul', 'belovo', 'ekaterinburg', 'kamensk-uralskiy', 'Kemerovo', 'leninsk-kuznetskiy', 'magnitogorsk', 'mezhdurechensk', 'nizhniy-tagil', 'novokuznetske-', 'novosibirsk', 'omsk', 'perm', 'prokopevsk', 'seversk', 'sterlitamak', 'tolyatti', 'tomsk', 'tyumen', 'ulyanovsk', 'ufa', 'chelyabinsk'];
$aFilter = array("IBLOCK_ID" =>  Config::getInstance()->getIblockIdByCode('aspro_max_regions'), "ACTIVE" => "", false, false, array());
$oDbResElement = CIBlockElement::GetList(array(), $aFilter);
while ($aDbResElement = $oDbResElement->fetch()) {
    if (in_array($aDbResElement["CODE"], $code)) {
        $PROP_CODE = 'REGION_GROUP_CORNER_MODULE';
        $PROP_VALUE = 'Цена Сибирь';
        // найдем код значения св-ва типа "Список"
        $dbPropVals = CIBlockProperty::GetPropertyEnum($PROP_CODE, [], ["IBLOCK_ID"=>$aDbResElement["IBLOCK_ID"], "VALUE"=>$PROP_VALUE]);
        $arPropVal = $dbPropVals->fetch();

        $aFilter = array("IBLOCK_ID" =>  $aDbResElement["IBLOCK_ID"], "CODE" => $aDbResElement["CODE"],'PROPERTY_'.$PROP_CODE,);
        $arSelect = [
            'ID',
            'NAME',
            'PROPERTY_'.$PROP_CODE,
        ];
        $oDbRes = CIBlockElement::GetList(array(), $aFilter, false, false, $arSelect);
        if ($aDbRes = $oDbRes->fetch()) {
            if(!$aDbRes['PROPERTY_'.$PROP_CODE.'_VALUE'] && $arPropVal['ID']) {
                CIBlockElement::SetPropertyValuesEx(intval($aDbRes["ID"]), intval($aDbResElement["IBLOCK_ID"]),[$PROP_CODE=> intval($arPropVal['ID'])] );
                echo "\e[1;32m Успешно обновлено  значение  свойства \"" . $aDbRes["NAME"] . "\" \e[0m\n";
            } else {
                echo "\e[1;31m Не удалось создать значение  свойства \"" . $aDbRes["NAME"] . "\" \e[0m\n";
            }
        }

    } else {
        $PROP_CODE = 'REGION_GROUP_CORNER_MODULE';
        $PROP_VALUE = 'Цена Москва';

        $dbPropVals = CIBlockProperty::GetPropertyEnum($PROP_CODE, [], ["IBLOCK_ID"=>$aDbResElement["IBLOCK_ID"], "VALUE"=>$PROP_VALUE]);
        $arPropVal = $dbPropVals->fetch();

        $aFilter = array("IBLOCK_ID" =>  $aDbResElement["IBLOCK_ID"], "CODE" => $aDbResElement["CODE"],'PROPERTY_'.$PROP_CODE,);
        $arSelect = [
            'ID',
            'NAME',
            'PROPERTY_'.$PROP_CODE,
        ];
        $oDbRes = CIBlockElement::GetList(array(), $aFilter, false, false, $arSelect);
        if ($aDbRes = $oDbRes->fetch()) {
            if(!$aDbRes['PROPERTY_'.$PROP_CODE.'_VALUE'] && $arPropVal['ID']) {
                CIBlockElement::SetPropertyValuesEx(intval($aDbRes["ID"]), intval($aDbResElement["IBLOCK_ID"]),[$PROP_CODE=> intval($arPropVal['ID'])] );
                echo "\e[1;32m Успешно обновлено  значение свойства \"" . $aDbRes["NAME"] . "\" \e[0m\n";
            } else {
                echo "\e[1;31m Не удалось создать значение свойства \"" . $aDbRes["NAME"] . "\" \e[0m\n";
            }
        }
    }
}
