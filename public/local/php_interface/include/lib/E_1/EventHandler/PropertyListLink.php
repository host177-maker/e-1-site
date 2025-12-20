<?php
namespace E_1\EventHandler;

class PropertyListLink
{
    static function GetIBlockPropertyDescription() {

        return array(
            "PROPERTY_TYPE" => "E",
            "USER_TYPE" => "list_link",
            "DESCRIPTION" => "Привязка к свойству тип список",
            'GetPropertyFieldHtml' => array('\E_1\EventHandler\PropertyListLink', 'GetPropertyFieldHtml'),
        );
    }

    static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) {

        $arFilter = Array(
            "ACTIVE" => "Y",
            "IBLOCK_ID" => $arProperty['LINK_IBLOCK_ID'],
        );
        $aFilter = array("IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"], "CODE" => $arProperty["CODE"]);
        $oDbResProperty = \CIBlockProperty::GetList(array(), $aFilter);
        
        $select = "<select name='{$strHTMLControlName["VALUE"]}'><option value=''>-- Выберите значение --</option>";
        while ($arItem = $oDbResProperty->Fetch()) {
            $aFields["ENUM"]["PROPERTY_ID"] = $arItem["ID"];
            $aFilter = array("PROPERTY_ID" => $aFields["ENUM"]["PROPERTY_ID"]);
            $oDbRes = \CIBlockPropertyEnum::GetList(array(), $aFilter);
            while ($aDbRes = $oDbRes->fetch()) {
                $selected = ($value["VALUE"] == $aDbRes['ID']) ? "selected=selected" : "";
                $select .= "<option {$selected} value='{$aDbRes['ID']}'>{$aDbRes['VALUE']}</option>";
            }
        }
        $select .= "</select>";

        $html = "<p> {$select}  Описание: <input value='{$value["DESCRIPTION"]}' type='text' size='6' name='{$strHTMLControlName["DESCRIPTION"]}'></p>";
        return $html;
    }

}