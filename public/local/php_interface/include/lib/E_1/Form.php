<?php
namespace E_1;

class Form {

    public static function GetDropDownField($FIELD_NAME, $arDropDown, $VALUE, $PARAM="")
	{
		if ($PARAM == '') $PARAM = " class=\"inputselect\" ";
		return static::SelectBoxFromArray("form_dropdown_".$FIELD_NAME, $arDropDown, $VALUE, "", $PARAM);
	}

    /**
 * Returns HTML "select" from array data
 */
static function SelectBoxFromArray(
	$strBoxName,
	$db_array,
	$strSelectedVal = "",
	$strDetText = "",
	$field1="class='typeselect'",
	$go = false,
	$form="form1"
	)
{
	$boxName = htmlspecialcharsbx($strBoxName);
	if($go)
	{
		$funName = preg_replace("/[^a-z0-9_]/i", "", $strBoxName);
		$jsName = \CUtil::JSEscape($strBoxName);

		$strReturnBox = "<script type=\"text/javascript\">\n".
			"function ".$funName."LinkUp()\n".
			"{var number = document.".$form."['".$jsName."'].selectedIndex;\n".
			"if(document.".$form."['".$jsName."'].options[number].value!=\"0\"){ \n".
			"document.".$form."['".$jsName."_SELECTED'].value=\"yes\";\n".
			"document.".$form.".submit();\n".
			"}}\n".
			"</script>\n";
		$strReturnBox .= '<input type="hidden" name="'.$boxName.'_SELECTED" id="'.$boxName.'_SELECTED" value="">';
		$strReturnBox .= '<select '.$field1.' name="'.$boxName.'" id="'.$boxName.'" onchange="'.$funName.'LinkUp()" class="typeselect">';
	}
	else
	{
		$strReturnBox = '<select '.$field1.' name="'.$boxName.'" id="'.$boxName.'">';
	}

	if(isset($db_array["reference"]) && is_array($db_array["reference"]))
		$ref = $db_array["reference"];
	elseif(isset($db_array["REFERENCE"]) && is_array($db_array["REFERENCE"]))
		$ref = $db_array["REFERENCE"];
	else
		$ref = array();

	if(isset($db_array["reference_id"]) && is_array($db_array["reference_id"]))
		$ref_id = $db_array["reference_id"];
	elseif(isset($db_array["REFERENCE_ID"]) && is_array($db_array["REFERENCE_ID"]))
		$ref_id = $db_array["REFERENCE_ID"];
	else
		$ref_id = array();

	if($strDetText <> '')
		$strReturnBox .= '<option value="">'.$strDetText.'</option>';
	foreach($ref as $i => $val)
	{
		$strReturnBox .= '<option';
		if(strcasecmp($ref_id[$i], $strSelectedVal) == 0)
			$strReturnBox .= ' selected';
            if ($db_array["param"][$i] == 'NOSELECT') {
                $strReturnBox .= ' value="">'.htmlspecialcharsbx($val).'</option>';
            } elseif ($db_array["param"][$i] == 'TOPIC_ONE' || $db_array["param"][$i] == 'TOPIC_TWO') {
				$strReturnBox .= ' data-param="'.($db_array["param"][$i]).'" value="'.htmlspecialcharsbx($ref_id[$i]).'">'.htmlspecialcharsbx($val).'</option>';
			} else {
                $strReturnBox .= ' value="'.htmlspecialcharsbx($ref_id[$i]).'">'.htmlspecialcharsbx($val).'</option>';
            }
	}
	return $strReturnBox.'</select>';
}

}