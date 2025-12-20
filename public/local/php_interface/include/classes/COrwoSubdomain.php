<?
class COrwoSubdomain {
	public static function GetCurrentCityValues(){
		global $arRegion;
		if(empty($arRegion)){
			$bIncludedIblockModule = (\Bitrix\Main\Loader::includeModule("iblock"));
			$bIncludedAsproModule = (\Bitrix\Main\Loader::includeModule("aspro.max"));
			$iRegionsIblock = \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_REGIONS_IBLOCK');

			if($bIncludedIblockModule && $bIncludedAsproModule && !empty($iRegionsIblock)){
				$arRegionItemFilter = [
					'IBLOCK_ID' => $iRegionsIblock,
				    'ACTIVE' => 'Y',
				    'ACTIVE_DATE' => 'Y',
				    'PROPERTY_DEFAULT_VALUE' => 'Y'
				];

				$arRegionItemSelect = ['ID', 'NAME', 'IBLOCK_ID', 'DETAIL_PAGE_URL', 'PREVIEW_TEXT', 'IBLOCK_SECTION_ID', 'PROPERTY_LINK_REGION', 'PROPERTY_REGION_NAME_DECLINE_PP', 'PROPERTY_REGION_NAME_DECLINE_RP', 'PROPERTY_REGION_NAME_DECLINE_TP', 'PROPERTY_PHONES', 'PROPERTY_PHONES', 'PROPERTY_DEFAULT_VALUE', 'PROPERTY_REGION_TAG_COEFFICIENT'];


				$arRegions = CMaxCache::CIblockElement_GetList(array("CACHE" => array("TAG" => \CMaxCache::GetIBlockCacheTag($iRegionsIblock))), $arRegionItemFilter, false, false, $arRegionItemSelect);

				if(!empty($arRegions[0])){
					$arRegion = $arRegions[0];
				}
			}
		}

		define("CITY", $arRegion["NAME"]);
		define("IN_CITY", 'в ' . $arRegion["PROPERTY_REGION_NAME_DECLINE_PP_VALUE"]);
		define("FOR_CITY", $arRegion["PROPERTY_REGION_NAME_DECLINE_RP_VALUE"]);
		define("TO_CITY", $arRegion["PROPERTY_REGION_NAME_DECLINE_PP_VALUE"]);
		define("CITY_NAME_PP", $arRegion["PROPERTY_REGION_NAME_DECLINE_PP_VALUE"]);
		define("CITY_NAME_RP", $arRegion["PROPERTY_REGION_NAME_DECLINE_RP_VALUE"]);
		define("CITY_NAME_TP", $arRegion["PROPERTY_REGION_NAME_DECLINE_TP_VALUE"]);
		define("PHONE", $arRegion['PHONES'][0]);
		define("CITY_PREFIX", str_replace(".e-1", "", $arRegion['PROPERTY_MAIN_DOMAIN_VALUE']));
	}

	public static function SetMeta(){
		global $APPLICATION;
		$subDomainBrowserTitle = $APPLICATION->GetPageProperty("title");

		if(!empty($subDomainBrowserTitle)){
			$subDomainBrowserTitle = self::makeCurrentCityText($subDomainBrowserTitle);
			$APPLICATION->SetPageProperty("title", htmlspecialcharsBack($subDomainBrowserTitle));
		}
		$subDomainPageDescription = $APPLICATION->GetPageProperty("description");
		if(!empty($subDomainPageDescription)){
			$subDomainPageDescription = self::makeCurrentCityText($subDomainPageDescription);
			
			$APPLICATION->SetPageProperty("description", htmlspecialcharsBack($subDomainPageDescription));
		}
	}

	public static function makeCurrentCityText($sTextToReplace){
		global $bTextIsReplaced;
		$bTextIsReplaced = false;
		$sTextToReplace = str_replace(array("Самара", "Самаре"), array("{CITY}", "{TO_CITY}"), $sTextToReplace);
		
		$subDomainSearchText = array("{CITY}", "{IN_CITY}", "{FOR_CITY}", "{TO_CITY}");
		$subDomainReplaceText = array(CITY, IN_CITY, FOR_CITY, TO_CITY);
		$subDomainReplaceLowerText = array(mb_strtolower(CITY), mb_strtolower(IN_CITY), mb_strtolower(FOR_CITY));
		$subDomainPattern = '/(.*)({CITY}|{IN_CITY}|{FOR_CITY}|{TO_CITY})(.*)/';
		if(preg_match($subDomainPattern, $sTextToReplace)){
			$bTextIsReplaced = true;
			$sTextToReplace = str_replace($subDomainSearchText, $subDomainReplaceText, $sTextToReplace);
		}
		return $sTextToReplace;
	}

	public static function OnEndBufferContent(&$content)
	{
	    $content = self::makeCurrentCityText($content);
	    return $content;
	}

}
