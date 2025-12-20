<?
use Bitrix\Main\Loader;
class COrwoFindOfferUserField
{
    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => "orwo_find_offer_user_field",
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => 'Привязка к элементам инф. блоков с автозаполнением',
            "BASE_TYPE" => "int",
        );
    }

    public function GetDBColumnType($arUserField)
    {
        global $DB;
        switch(strtolower($DB->type))
        {
            case "mysql":
                return "int(18)";
            case "oracle":
                return "number(18)";
            case "mssql":
                return "int";
        }
        return "int";
    }


    function PrepareSettings($arUserField)
    {
        $iblock_id = intval($arUserField["SETTINGS"]["IBLOCK_ID"]);
        if($iblock_id <= 0)
            $iblock_id = "";
        $element_id = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
        if($element_id <= 0)
            $element_id = "";

        $active_filter = $arUserField["SETTINGS"]["ACTIVE_FILTER"] === "Y"? "Y": "N";

        $intMaxWidth = (int)(isset($arUserField["SETTINGS"]['MAX_WIDTH']) ? $arUserField["SETTINGS"]['MAX_WIDTH'] : 0);
        if ($intMaxWidth <= 0) $intMaxWidth = 0;

        $intMinHeight = (int)(isset($arUserField["SETTINGS"]['MIN_HEIGHT']) ? $arUserField["SETTINGS"]['MIN_HEIGHT'] : 0);
        if ($intMinHeight <= 0) $intMinHeight = 24;

        $intMaxHeight = (int)(isset($arUserField["SETTINGS"]['MAX_HEIGHT']) ? $arUserField["SETTINGS"]['MAX_HEIGHT'] : 0);
        if ($intMaxHeight <= 0) $intMaxHeight = 1000;

        $strBannedSymbols = trim(isset($arUserField["SETTINGS"]['BAN_SYM']) ? $arUserField["SETTINGS"]['BAN_SYM'] : ',;');
        $strBannedSymbols = str_replace(' ','',$strBannedSymbols);
        if (strpos($strBannedSymbols,',') === false)
            $strBannedSymbols .= ',';
        if (strpos($strBannedSymbols,';') === false)
            $strBannedSymbols .= ';';

        $strOtherReplaceSymbol = '';
        $strReplaceSymbol = (isset($arUserField["SETTINGS"]['REP_SYM']) ? $arUserField["SETTINGS"]['REP_SYM'] : ' ');
        if ($strReplaceSymbol == 'other')
        {
            $strOtherReplaceSymbol = (isset($arUserField["SETTINGS"]['OTHER_REP_SYM']) ? substr($arUserField["SETTINGS"]['OTHER_REP_SYM'],0,1) : '');
            if ((',' == $strOtherReplaceSymbol) || (';' == $strOtherReplaceSymbol))
                $strOtherReplaceSymbol = '';
            if (('' == $strOtherReplaceSymbol) || in_array($strOtherReplaceSymbol, static::GetReplaceSymList()))
            {
                $strReplaceSymbol = $strOtherReplaceSymbol;
                $strOtherReplaceSymbol = '';
            }
        }
        if ('' == $strReplaceSymbol)
        {
            $strReplaceSymbol = ' ';
            $strOtherReplaceSymbol = '';
        }


        return array(
            "IBLOCK_ID" => $iblock_id,
            "DEFAULT_VALUE" => $element_id,
            "ACTIVE_FILTER" => $active_filter,
            'MAX_WIDTH' => $intMaxWidth,
            'MIN_HEIGHT' => $intMinHeight,
            'MAX_HEIGHT' => $intMaxHeight,
            'BAN_SYM' => $strBannedSymbols,
            'REP_SYM' => $strReplaceSymbol,
            'OTHER_REP_SYM' => $strOtherReplaceSymbol,
        );
    }

    function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        $result = '';

        $arUserField["SETTINGS"] = self::PrepareSettings($arUserField);

        if($bVarsFromForm)
            $iblock_id = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_ID"];
        elseif(is_array($arUserField["SETTINGS"]))
            $iblock_id = $arUserField["SETTINGS"]["IBLOCK_ID"];
        else
            $iblock_id = "";


        if(Loader::includeModule('iblock'))
        {
            $result .= '
			<tr>
				<td>Инфоблок:</td>
				<td>
					'.GetIBlockDropDownList($iblock_id, $arHtmlControl["NAME"].'[IBLOCK_TYPE_ID]', $arHtmlControl["NAME"].'[IBLOCK_ID]', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"').'
				</td>
			</tr>
			';

            $result .= '
			<tr>
				<td>Максимальная ширина поля ввода в пикселах (0 - не ограничивать):</td>
				<td>
					<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_WIDTH]" value="'.(int)$arUserField["SETTINGS"]['MAX_WIDTH'].'">&nbsp;px
				</td>
			</tr>
			';

            $result .= '
			<tr>
				<td>Минимальная высота поля ввода в пикселах, если свойство множественное:</td>
				<td>
					<input type="text" name="'.$arHtmlControl["NAME"].'[MIN_HEIGHT]" value="'.(int)$arUserField["SETTINGS"]['MIN_HEIGHT'].'">&nbsp;px
				</td>
			</tr>
			';

            $result .= '
			<tr>
				<td>Максимальная высота поля ввода в пикселах, если свойство множественное:</td>
				<td>
					<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_HEIGHT]" value="'.(int)$arUserField["SETTINGS"]['MAX_HEIGHT'].'">&nbsp;px
				</td>
			</tr>
			';

            $result .= '
			<tr>
				<td>Заменяемые при показе символы:</td>
				<td>
					<input type="text" name="'.$arHtmlControl["NAME"].'[BAN_SYM]" value="'.htmlspecialcharsbx($arUserField["SETTINGS"]['BAN_SYM']).'">&nbsp;px
				</td>
			</tr>
			';

             $result .= '
			<tr>
				<td>Символ, который заменит при показе запрещенные символы:</td>
				<td>
					'.SelectBoxFromArray($arHtmlControl["NAME"].'[REP_SYM]', static::GetReplaceSymList(true),htmlspecialcharsbx($arUserField["SETTINGS"]['REP_SYM'])).'&nbsp;<input type="text" name="'.$arHtmlControl["NAME"].'[OTHER_REP_SYM]" size="1" maxlength="1" value="'.$arUserField["SETTINGS"]['OTHER_REP_SYM'].'">
				</td>
			</tr>
			';
        }
        else
        {
            $result .= '
			<tr>
				<td>Инфоблок:</td>
				<td>
					<input type="text" size="6" name="'.$arHtmlControl["NAME"].'[IBLOCK_ID]" value="'.htmlspecialcharsbx($value).'">
				</td>
			</tr>
			';
        }

        if($bVarsFromForm)
            $ACTIVE_FILTER = $GLOBALS[$arHtmlControl["NAME"]]["ACTIVE_FILTER"] === "Y"? "Y": "N";
        elseif(is_array($arUserField))
            $ACTIVE_FILTER = $arUserField["SETTINGS"]["ACTIVE_FILTER"] === "Y"? "Y": "N";
        else
            $ACTIVE_FILTER = "N";

        if($bVarsFromForm)
            $value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"];
        elseif(is_array($arUserField))
            $value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
        else
            $value = "";

            $result .= '
			<tr>
				<td>Значение по умолчанию:</td>
				<td>
					<input type="text" size="8" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" value="'.htmlspecialcharsbx($value).'">
				</td>
			</tr>
			';


        $result .= '
		<tr>
			<td>Показывать только активные элементы:</td>
			<td>
				<input type="checkbox" name="'.$arHtmlControl["NAME"].'[ACTIVE_FILTER]" value="Y" '.($ACTIVE_FILTER=="Y"? 'checked="checked"': '').'>
			</td>
		</tr>
		';

        return $result;
    }

    protected static function GetReplaceSymList($boolFull = false)
    {
        $boolFull = ($boolFull === true);
        if ($boolFull)
        {
            return array(
                'REFERENCE' => array(
                    'Пробел',
                    '#',
                    '*',
                    '_',
                    'other',

                ),
                'REFERENCE_ID' => array(
                    ' ',
                    '#',
                    '*',
                    '_',
                    'other',
                ),
            );
        }
        return array(' ', '#', '*','_');
    }

    public static function GetValueForAutoComplete($arProperty, $arValue, $arBanSym = "", $arRepSym = "")
    {
        $strResult = '';
        $mxResult = static::GetPropertyValue($arProperty,$arValue);
        if (is_array($mxResult))
        {
            $strResult = htmlspecialcharsbx(str_replace($arBanSym,$arRepSym,$mxResult['~NAME'])).' ['.$mxResult['ID'].']';
        }
        return $strResult;
    }

    protected static function GetPropertyValue($arProperty, $arValue)
    {
        $mxResult = false;
        if ((int)$arValue['VALUE'] > 0)
        {
            $mxResult = static::GetLinkElement($arValue['VALUE'],$arProperty['SETTINGS']['IBLOCK_ID']);
        }
        return $mxResult;
    }

    protected static function GetLinkElement($intElementID, $intIBlockID)
    {
        static $cache = array();

        $intIBlockID = (int)$intIBlockID;
        if ($intIBlockID <= 0)
            $intIBlockID = 0;
        $intElementID = (int)$intElementID;
        if ($intElementID <= 0)
            return false;
        if (!isset($cache[$intElementID]) && Loader::includeModule('iblock'))
        {
            $arFilter = array();
            if ($intIBlockID > 0)
                $arFilter['IBLOCK_ID'] = $intIBlockID;
            $arFilter['ID'] = $intElementID;
            $arFilter['SHOW_HISTORY'] = 'Y';
            $rsElements = \CIBlockElement::GetList(array(), $arFilter, false, false, array('IBLOCK_ID','ID','NAME'));
            if ($arElement = $rsElements->GetNext())
            {
                $arResult = array(
                    'ID' => $arElement['ID'],
                    'NAME' => $arElement['NAME'],
                    '~NAME' => $arElement['~NAME'],
                    'IBLOCK_ID' => $arElement['IBLOCK_ID'],
                );
                $cache[$intElementID] = $arResult;
            }
            else
            {
                $cache[$intElementID] = false;
            }
        }
        return $cache[$intElementID];
    }


    public static function GetValueForAutoCompleteMulti($arProperty, $arValues, $arBanSym = "", $arRepSym = "")
    {
        $arResult = false;

        if (is_array($arValues['VALUE']))
        {
            foreach ($arValues['VALUE'] as $intPropertyValueID => $arOneValue)
            {
                if (!is_array($arOneValue))
                {
                    $strTmp = $arOneValue;
                    $arOneValue = array(
                        'VALUE' => $strTmp,
                    );
                }
                $mxResult = static::GetPropertyValue($arProperty,$arOneValue);
                if (is_array($mxResult))
                {
                    $arResult[$intPropertyValueID] = htmlspecialcharsbx(str_replace($arBanSym,$arRepSym,$mxResult['~NAME'])).' ['.$mxResult['ID'].']';
                }
            }
        }
        return $arResult;
    }

    protected static function GetSymbols($arSettings)
    {
        $strBanSym = $arSettings['BAN_SYM'];
        $strRepSym = ('other' == $arSettings['REP_SYM'] ? $arSettings['OTHER_REP_SYM'] : $arSettings['REP_SYM']);
        $arBanSym = str_split($strBanSym,1);
        $arRepSym = array_fill(0,sizeof($arBanSym),$strRepSym);
        $arResult = array(
            'BAN_SYM' => $arBanSym,
            'REP_SYM' => $arRepSym,
            'BAN_SYM_STRING' => $strBanSym,
            'REP_SYM_STRING' => $strRepSym,
        );
        return $arResult;
    }


    function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        if(($arUserField["ENTITY_VALUE_ID"]<1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);

        $fixIBlock = $arUserField['SETTINGS']['IBLOCK_ID'] > 0;
        $arSymbols = static::GetSymbols($arUserField['SETTINGS']);

        global $APPLICATION;


        ob_start();
        $control_id = $APPLICATION->IncludeComponent(
            "bitrix:main.lookup.input",
            "iblockedit",
            array(
                "CONTROL_ID" => preg_replace(
                    "/[^a-zA-Z0-9_]/i",
                    "x",
                    str_replace('[]', '', $arHtmlControl["NAME"]).'_'.mt_rand(0, 10000)
                ),
                "INPUT_NAME" => $arHtmlControl["NAME"],
                "INPUT_NAME_STRING" => "inp_".str_replace('[]', '', $arHtmlControl["NAME"]),
                "INPUT_VALUE_STRING" => htmlspecialcharsback(static::GetValueForAutoComplete(
                    $arUserField,
                    $arHtmlControl,
                    $arSymbols['BAN_SYM'],
                    $arSymbols['REP_SYM']
                )),
                "START_TEXT" => 'Введите значение',
                "MULTIPLE" => $arUserField['MULTIPLE'],
                "MAX_WIDTH" => $arUserField['SETTINGS']['MAX_WIDTH'],
                "MIN_HEIGHT" => $arUserField['SETTINGS']['MIN_HEIGHT'],
                "MAX_HEIGHT" => $arUserField['SETTINGS']['MAX_HEIGHT'],
                "IBLOCK_ID" => $arUserField['SETTINGS']['IBLOCK_ID'],
                'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
                'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
                'REP_SYM' => $arSymbols['REP_SYM_STRING'],
                'FILTER' => 'Y'
            ), null, array("HIDE_ICONS" => "Y")
        );
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
    {
        if(($arUserField["ENTITY_VALUE_ID"]<1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = array(intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]));
        elseif(!is_array($arHtmlControl["VALUE"]))
            $arHtmlControl["VALUE"] = array();


        $fixIBlock = $arUserField['SETTINGS']['IBLOCK_ID'] > 0;
        $arSymbols = static::GetSymbols($arUserField['SETTINGS']);

        $mxResultValue = static::GetValueForAutoCompleteMulti($arUserField,$arHtmlControl,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
        $strResultValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode("\n",$mxResultValue)) : '');

        global $APPLICATION;

        ob_start();
        $control_id = $APPLICATION->IncludeComponent(
            "bitrix:main.lookup.input",
            "iblockedit",
            array(
                "CONTROL_ID" => preg_replace(
                    "/[^a-zA-Z0-9_]/i",
                    "x",
                    str_replace('[]', '', $arHtmlControl["NAME"]).'_'.mt_rand(0, 10000)
                ),
                "INPUT_NAME" => $arHtmlControl["NAME"],
                "INPUT_NAME_STRING" => "inp_".str_replace('[]', '', $arHtmlControl["NAME"]),
                "INPUT_VALUE_STRING" => $strResultValue,
                "START_TEXT" => 'Введите значение',
                "MULTIPLE" => $arUserField['MULTIPLE'],
                "MAX_WIDTH" => $arUserField['SETTINGS']['MAX_WIDTH'],
                "MIN_HEIGHT" => $arUserField['SETTINGS']['MIN_HEIGHT'],
                "MAX_HEIGHT" => $arUserField['SETTINGS']['MAX_HEIGHT'],
                "IBLOCK_ID" => $arUserField['SETTINGS']['IBLOCK_ID'],
                'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
                'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
                'REP_SYM' => $arSymbols['REP_SYM_STRING'],
                'FILTER' => 'Y'
            ), null, array("HIDE_ICONS" => "Y")
        );
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }


    function GetFilterHTML($arUserField, $arHtmlControl)
    {
        $result = '<input type="text" name="'.$arHtmlControl["NAME"].'[]" size="47" value="'.$arHtmlControl["VALUE"].'" class="adm-input">';
        return $result;
    }

    function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        $arSymbols = static::GetSymbols($arUserField['SETTINGS']);
        if( $arUserField['MULTIPLE'] == 'Y' ){

            $mxResultValue = static::GetValueForAutoCompleteMulti($arUserField,$arHtmlControl,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
            $strResultValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode('<br>',$mxResultValue)) : '');
        }else{
            $strResultValue = htmlspecialcharsback(static::GetValueForAutoComplete(
                $arUserField,
                $arHtmlControl,
                $arSymbols['BAN_SYM'],
                $arSymbols['REP_SYM']
            ));

        }

        return $strResultValue;
    }

    function GetAdminListViewHTMLMulty($arUserField, $arHtmlControl)
    {
        $arSymbols = static::GetSymbols($arUserField['SETTINGS']);
        if( $arUserField['MULTIPLE'] == 'Y' ){

            $mxResultValue = static::GetValueForAutoCompleteMulti($arUserField,$arHtmlControl,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
            $strResultValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode('<br>',$mxResultValue)) : '');
        }else{
            $strResultValue = htmlspecialcharsback(static::GetValueForAutoComplete(
                $arUserField,
                $arHtmlControl,
                $arSymbols['BAN_SYM'],
                $arSymbols['REP_SYM']
            ));

        }

        return $strResultValue;
    }

    function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {

        $arSymbols = static::GetSymbols($arUserField['SETTINGS']);
        if( $arUserField['MULTIPLE'] == 'Y' ){

            $mxResultValue = static::GetValueForAutoCompleteMulti($arUserField,$arHtmlControl,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
            $strResultValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode('<br>',$mxResultValue)) : '');
        }else{
            $strResultValue = htmlspecialcharsback(static::GetValueForAutoComplete(
                $arUserField,
                $arHtmlControl,
                $arSymbols['BAN_SYM'],
                $arSymbols['REP_SYM']
            ));

        }

        return $strResultValue;
    }

    function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
    {

        $arSymbols = static::GetSymbols($arUserField['SETTINGS']);
        if( $arUserField['MULTIPLE'] == 'Y' ){

            $mxResultValue = static::GetValueForAutoCompleteMulti($arUserField,$arHtmlControl,$arSymbols['BAN_SYM'],$arSymbols['REP_SYM']);
            $strResultValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode('<br>',$mxResultValue)) : '');
        }else{
            $strResultValue = htmlspecialcharsback(static::GetValueForAutoComplete(
                $arUserField,
                $arHtmlControl,
                $arSymbols['BAN_SYM'],
                $arSymbols['REP_SYM']
            ));

        }

        return $strResultValue;
    }

    function CheckFields($arUserField, $value)
    {
        $aMsg = array();
        return $aMsg;
    }

    function OnSearchIndex($arUserField)
    {
        $res = '';

        if(is_array($arUserField["VALUE"]))
            $val = $arUserField["VALUE"];
        else
            $val = array($arUserField["VALUE"]);

        $val = array_filter($val, "strlen");
        if(count($val) && Loader::includeModule('iblock'))
        {
            $ob = new \CIBlockElement;
            $rs = $ob->GetList(array(), array(
                "=ID" => $val
            ), false, false, array("NAME"));

            while($ar = $rs->Fetch())
                $res .= $ar["NAME"]."\r\n";
        }

        return $res;
    }
}