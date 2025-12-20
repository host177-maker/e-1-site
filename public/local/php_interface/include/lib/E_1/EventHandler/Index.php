<?php

namespace E_1\EventHandler;

use Bitrix\Main\Loader;
use Cosmos\IblockProperty;

if (!\CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}

class Index
{
    

    static function BeforeIndex($arFields)
    {

        if ($arFields['MODULE_ID'] == 'iblock') {

            if ($arFields['PARAM2'] == \Cosmos\Config::getInstance()->getIblockIdByCode('offers')) {
                static::checkCatalog($arFields);
            }
        }

        return $arFields;
    }

    public static function checkCatalog(&$arFields)
    {
        Loader::includeModule('iblock');
        $arProp = array("ID");
        $arProduct = \CCatalogSku::GetProductInfo(
            (int) $arFields['ITEM_ID']
        );
        if (is_array($arProduct))
        {
            //echo 'ID товара = '.$arProduct['ID'];
            $resElementActive = \CIBlockElement::GetList([], ['IBLOCK_ID' =>$arProduct['IBLOCK_ID'], 'ID' =>$arProduct['ID']], false, array("nPageSize" => 1), ["ID", "ACTIVE"])->Fetch()['ACTIVE'];
            if ($resElementActive === "N") {
                $arFields["TITLE"] = '';
                $arFields["BODY"] = '';
                $arFields["TAGS"] = '';
                $arFields = [];
            }
        }
        return $arFields;
    }
}
