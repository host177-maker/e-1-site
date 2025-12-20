<?php

namespace Absteam\Admin;

use \Absteam\IblockProperty;
use \Bitrix\Iblock\PropertyIndex;

class ProductsAdmin
{
    public static function SetLabelSkidka(array $ids, bool $onoff) {
        $labelsProp = 'HIT'; //fixme унести в конфиг

        $labelDiscountValue = 'Скидка'; // Считается что мы не знаем XML_ID для этого енума, берём по значению.
        $iblockId = E1_IBLOCK_CATALOG;

        if (!\CModule::IncludeModule("iblock")) {
            throw new \Exception('Модуль инфоблоков не установлен');
        }

        if (!$ids) {
            return;
        }

        $arDiscountEnumId = IblockProperty::getEnumIDByValue($labelsProp, $iblockId, $labelDiscountValue);
        if (!$arDiscountEnumId) {
            throw new \Exception('Внутренняя ошибка');
        }

        //собираем текущие значения
        $arCurrVals = [];
        $res = \CIBlockElement::GetList([],
            ['IBLOCK_ID' => $iblockId, 'ID' => $ids],
            false, false,
            ['ID', "PROPERTY_$labelsProp"]
        );
        while ($ar = $res->Fetch()) {
            $enum = $ar["PROPERTY_{$labelsProp}_ENUM_ID"];
            if (is_array($enum)) {
                throw new \Exception('Not realized'); //TODO - не актуально, но мало ли что
            }
            $arCurrVals[$ar['ID']][$enum] = 1;
        }

        //изменяем в соответствии с $onoff
        $needResetCache = false;
        foreach ($arCurrVals as $prodId => $arEnums) {
            if ((!$onoff && !isset($arEnums[$arDiscountEnumId]))
                || ($onoff && isset($arEnums[$arDiscountEnumId]))
            ) {
                continue;
            } //ничего делать не нужно

            if ($onoff) {
                $arEnums[$arDiscountEnumId] = 1;
            } else {
                unset($arEnums[$arDiscountEnumId]);
            }

            $newVal = array_keys($arEnums);
            if(!$newVal) {
                $newVal = false;
            }
            //записываем в свойство
            \CIBlockElement::SetPropertyValuesEx($prodId, $iblockId, [$labelsProp => $newVal]);
            PropertyIndex\Manager::updateElementIndex($iblockId, $prodId);  //переиндексируем конкретны элемент
            $needResetCache = true;
        }

        if($needResetCache){
/*          //пересоздание индекса
            PropertyIndex\Manager::DeleteIndex($iblockId);
            PropertyIndex\Manager::markAsInvalid($iblockId);
            $index = Bitrix\Iblock\PropertyIndex\Manager::createIndexer($iblockId);
            $index->startIndex();
            $res = $index->continueIndex();
            $index->endIndex();
            PropertyIndex\Manager::checkAdminNotification();
*/
            \CBitrixComponent::clearComponentCache("bitrix:catalog.smart.filter");
            \CIBlock::clearIBlockTagCache($iblockId);
        }
    }

}