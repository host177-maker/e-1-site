<?php
/**
 * @author Shmakov Fedot
 * @date 14.04.2022
 * @see 21249
 */
use Cosmos\Config;
$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}

   /**
     * Создаём свойства инфоблока "Каталога товаров"
     */
    Config::getInstance()->init();
    $aCosmosConfigIblock = 48;
    $oCIBlockProperty = new CIBlockProperty;
    $aIblockProperties = array(
        array(
            "IBLOCK_ID" =>  $aCosmosConfigIblock,
            "NAME" => "Ссылка QR код",
            "CODE" => "LINK_QR_CODE",
            "PROPERTY_TYPE" => "S",
            "SORT" => 100,
        ),
        array(
            "IBLOCK_ID" => $aCosmosConfigIblock,
            "NAME" => "Ссылка 3D модель",
            "CODE" => "LINK_3D_MODEL",
            "PROPERTY_TYPE" => "S",
            "SORT" => 200,
        ),
    );
    foreach ($aIblockProperties as $aFields) {
        $aFilter = array("IBLOCK_ID" => $aFields["IBLOCK_ID"], "CODE" => $aFields["CODE"]);
        $oDbRes = CIBlockProperty::GetList(array(), $aFilter);
        if ($aDbRes = $oDbRes->fetch()) {
            echo "\e[1;33m Свойство \"".$aFields["NAME"]."\" уже существует \e[0m\n";
        } else {
            if ($oCIBlockProperty->Add($aFields)) {
                echo "\e[1;32m Успешно создано свойство \"".$aFields["NAME"]."\" \e[0m\n";
            } else {
                echo "\e[1;31m Не удалось создать свойство \"".$aFields["NAME"]."\" \e[0m\n";
                echo "\e[1;31m Error: ".$oCIBlockProperty->LAST_ERROR." \e[0m\n";
            }
        }
    }

    $el = new CIBlockElement;
$aElements['PROPERTY_VALUES'] = [
            'LINK_QR_CODE' =>array(
               "VALUE" => "https://ar.elarbis.com/E1/express_3doors_1800x2200x600_dsp_mirror_dsp_oak_milk.html", 
            ),
            'LINK_3D_MODEL' =>array(
               "VALUE" => "https://webgl.elarbis.com/viewer/index.html?vendor=E1&model=express_3doors_1800x2200x600_dsp_mirror_dsp_shimo_light", 
            ),      
];
$aFilter = array("IBLOCK_ID" => $aCosmosConfigIblock, "CODE" => "layt_2_kh_dvernyy_fasad_dsp_zerkalo");
$aSelect = array("IBLOCK_ID", "ID");
$oDbRes = CIBlockElement::GetList(array(), $aFilter, false, false, $aSelect);
if ($aDbRes = $oDbRes->fetch()) {
    $element_id = $aDbRes["ID"];
}
if(1){
    CIBlockElement::SetPropertyValuesEx($element_id, $aFilter["IBLOCK_ID"], $aElements['PROPERTY_VALUES']);
    echo "\e[1;32m Успешно обновлено  свойство  \e[0m\n";
}else echo "\e[1;31m Не удалось обновить свойство  \e[0m\n";
