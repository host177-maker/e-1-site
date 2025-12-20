<?php

namespace E_1\EventHandler;

class OnSuccesCatalogImport1C {

    public static function discountRatioReindex($arParams, $ABS_FILE_NAME) {
        $aCosmosConfigIblock = \Cosmos\Config::getInstance()->getParam("IBLOCK");
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "PROPERTY_WHITHOUT_DISCOUNT_RATIO", "PROPERTY_PROTSENT_SKIDKI");
        $arFilter = array("IBLOCK_ID" => IntVal($aCosmosConfigIblock["1c_catalog"]["ID"]));
        $res = \CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            if ($arFields['PROPERTY_PROTSENT_SKIDKI_VALUE']) {
                $ratio = (1 / (100 - $arFields['PROPERTY_PROTSENT_SKIDKI_VALUE'])) * 100;
                \CIBlockElement::SetPropertyValuesEx($arFields['ID'], $aCosmosConfigIblock["1c_catalog"]["ID"], array('WHITHOUT_DISCOUNT_RATIO' => $ratio));
            }
        }
    }

    public static function copyXMLFile($arParams, $ABS_FILE_NAME) {
        $destination = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR  . 'upload' . DIRECTORY_SEPARATOR. pathinfo($ABS_FILE_NAME, PATHINFO_BASENAME);

        try {
            copy($ABS_FILE_NAME, $destination);
        } catch (\Throwable $th) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR. 'copyXML.log', $th->getMessage());
        }
    }
}
