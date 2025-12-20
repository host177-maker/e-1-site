<?php

namespace Absteam;

use \Bitrix\Main\Loader,
    \Bitrix\Iblock\PropertyEnumerationTable,
    \Bitrix\Iblock\PropertyTable;

class IblockProperty
{
    public static array $ENUMS;
    
    public static function prepareEnumList($sPropertyCode, $iblockId = 0) {
        if (isset(self::$ENUMS[$iblockId][$sPropertyCode])) {
            return;
        }

        if (!Loader::includeModule("iblock")) {
            throw new \Exception('Модуль инфоблоков не установлен');
        }

        $arFilter = [];

        if (is_numeric($sPropertyCode)) {
            $arFilter['PROPERTY_ID'] = intval($sPropertyCode);
        } else {
            $propFilter = [
                '=CODE'          => $sPropertyCode,
                '=PROPERTY_TYPE' => 'L',
            ];
            if ($iblockId) {
                $propFilter['IBLOCK_ID'] = $iblockId;
            }
            $arPropIds = [];
            $res = PropertyTable::getList([
                'filter' => $propFilter,
                'select' => array('ID', 'CODE'),
            ]);
            while ($ar = $res->fetch()) {
                $arPropIds[] = $ar['ID'];
            }
            $arFilter['=PROPERTY_ID'] = $arPropIds;
        }

        //if ($iblockId) {
        ///    $arFilter['IBLOCK_ID'] = $iblockId;
        //}
        /*$oResPropertyEnum = \CIBlockPropertyEnum::GetList(
            ["DEF" => "DESC", "SORT" => "ASC",],
            $aFilter
        );
        self::$ENUMS[$iblockId][$sPropertyCode] = array();
        while ($aEnum = $oResPropertyEnum->GetNext()) {
            self::$ENUMS[$iblockId][$sPropertyCode][$aEnum['ID']] = $aEnum;
        }*/

        $res = PropertyEnumerationTable::getList([
            'filter' => $arFilter,
            'order'  => ["DEF" => "DESC", "SORT" => "ASC",],
        ]);
        self::$ENUMS[$iblockId][$sPropertyCode] = array();
        while ($arEnum = $res->fetch()) {
            self::$ENUMS[$iblockId][$sPropertyCode][$arEnum['ID']] = $arEnum;
        }

        //return self::$ENUMS[$iblockId][$sPropertyCode];
        return;
    }

    public static function getEnumList($sPropertyCode, $iblockId = false):array {
        self::prepareEnumList($sPropertyCode, $iblockId);

        return self::$ENUMS[$iblockId][$sPropertyCode];
    }

    public static function getEnumRefByName($sPropertyCode, $iblockId):array {
        $ref = [];
        if (isset(self::$ENUMS[$iblockId][$sPropertyCode])) {
            foreach (self::$ENUMS[$iblockId][$sPropertyCode] as $id => $prop) {
                $ref[$prop['VALUE']] = $id;
            }
        }

        return $ref;
    }

    public static function getEnumIDByValue($sPropertyCode, $iblockId, $value):int {
        self::prepareEnumList($sPropertyCode, $iblockId);
        $ref = self::getEnumRefByName($sPropertyCode, $iblockId);

        return intval($ref[$value]);
    }


}