<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
?>
<?global $arTheme, $APPLICATION;?>
<?
$bError = false;

/*Prepare params*/
$arParams["ELEMENT_COUNT"] = ($arParams["ELEMENT_COUNT"] ? $arParams["ELEMENT_COUNT"] : 5);
$arParams['FILTER_NAME'] = ($arParams['FILTER_NAME'] ? $arParams['FILTER_NAME'] : 'arFilterWrapper');
/**/

$arParams["COMPONENT_NAME"] = $componentName;
$arParams["TEMPLATE"] = $componentTemplate;

$arParams["IS_AJAX"] = (CMax::checkAjaxRequest() && $arParams['SHOW_FORM'] == 'Y');

$context=\Bitrix\Main\Application::getInstance()->getContext();
$request=$context->getRequest();

/*fix global filter in ajax*/
if($_SESSION['ASPRO_FILTER'][$arParams['FILTER_NAME']])
	$GLOBALS[$arParams['FILTER_NAME']] = $_SESSION['ASPRO_FILTER'][$arParams['FILTER_NAME']];

/**/

$isWebform = strtolower($request['template']) === "webform";

if($arParams['IS_AJAX'] == 'Y')
{
	$APPLICATION->ShowAjaxHead();
}

if(!$bError && \Bitrix\Main\Loader::includeModule('aspro.max'))
{
	if($arParams["IS_AJAX"] != 'Y' && !$isWebform)
	{
		$ob = new Aspro\Max\MarketingPopup();
		$rules = $ob->GetRules();
		if($rules && (isset($rules['ALL']) && $rules['ALL']))
		{
			$arResult = $rules['ALL'];
		}
		$this->IncludeComponentTemplate();
	}
	else
	{
		if($request['iblock_id'] && $request['id'])
		{
			$arFilter = array(
				"IBLOCK_ID" => $request['iblock_id'],
				"ACTIVE"=>"Y",
				"ID" => $request["id"]
			);
			$arSelect = array(
				"ID",
				"NAME",
				"PREVIEW_TEXT",
				"PREVIEW_PICTURE",
				"PROPERTY_BTN1_LINK",
				"PROPERTY_BTN1_TEXT",
				"PROPERTY_BTN1_CLASS",
				"PROPERTY_BTN2_LINK",
				"PROPERTY_BTN2_TEXT",
				"PROPERTY_BTN2_CLASS",
				"PROPERTY_MODAL_TYPE",
				"PROPERTY_POSITION",
				"PROPERTY_HIDE_TITLE",
				"PROPERTY_LINK_WEB_FORM",
			);
			$arResult['ITEM'] = CMaxCache::CIBLockElement_GetList(array(
				'CACHE' => array(
					"TAG" => CMaxCache::GetIBlockCacheTag($request['iblock_id']),
					"MULTI" => "N"
				)),
				$arFilter, 
				false, 
				false, 
				$arSelect
			);

			if($arResult['ITEM'])
			{
				$arResult['ITEM']['BTN1_CLASS_INFO'] = $arResult['ITEM']['BTN2_CLASS_INFO'] = array();
				if($arResult['ITEM']['PROPERTY_BTN1_CLASS_ENUM_ID'])
					$arResult['ITEM']['BTN1_CLASS_INFO'] = CIBlockPropertyEnum::GetByID($arResult['ITEM']['PROPERTY_BTN1_CLASS_ENUM_ID']);
				if($arResult['ITEM']['PROPERTY_BTN2_CLASS_ENUM_ID'])
					$arResult['ITEM']['BTN2_CLASS_INFO'] = CIBlockPropertyEnum::GetByID($arResult['ITEM']['PROPERTY_BTN2_CLASS_ENUM_ID']);
			}
		}
		$this->IncludeComponentTemplate( strtolower($request['template']) );
	}
}
?>