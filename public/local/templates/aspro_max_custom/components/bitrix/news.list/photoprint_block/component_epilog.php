<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$sectionName = '';

if($arResult["SECTION"]["PATH"]){
	if(count($arResult["SECTION"]["PATH"]) == 1){
		$sectionName = $arResult["SECTION"]["PATH"][0]["NAME"];
	}elseif(count($arResult["SECTION"]["PATH"]) == 2){
		$sectionName = $arResult["SECTION"]["PATH"][1]["NAME"];
	}	
	$APPLICATION->SetTitle($sectionName);
}

?>
