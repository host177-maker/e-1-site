<?
CModule::AddAutoloadClasses(
	'aspro.max',
	array(
		'CMaxCache' => 'classes/general/CMaxCache.php',
		'CMax' => 'classes/general/CMax.php',
		'CMaxTools' => 'classes/general/CMaxTools.php',
		'CMaxEvents' => 'classes/general/CMaxEvents.php',
		'CMaxRegionality' => 'classes/general/CMaxRegionality.php',
		'CMaxCondition' => 'classes/general/CMaxCondition.php',
		'CInstargramMax' => 'classes/general/CInstargramMax.php',
		'Aspro\Solution\CAsproMarketing' => 'classes/general/CAsproMarketing.php',
		'Aspro\Functions\CAsproMaxSku' => 'lib/functions/CAsproMaxSku.php',
		'Aspro\Functions\CAsproMaxItem' => 'lib/functions/CAsproMaxItem.php',
		'Aspro\Functions\CAsproMax' => 'lib/functions/CAsproMax.php',
		'Aspro\Functions\CAsproMaxCustom' => 'lib/functions/CAsproMaxCustom.php',
		'Aspro\Functions\CAsproMaxReCaptcha' => 'lib/functions/CAsproMaxReCaptcha.php',
		'Aspro\Functions\CAsproMaxCRM' => 'lib/functions/CAsproMaxCRM.php',
		'Aspro\Max\ShareBasketTable' => 'lib/sharebasket.php',
		'Aspro\Max\ShareBasketItemTable' => 'lib/sharebasketitem.php',
		'Aspro\Max\GS' => 'lib/gs.php',
		'Aspro\Max\Iconset' => 'lib/iconset.php',
		'Aspro\Max\SearchQuery' => 'lib/searchquery.php',
		'Aspro\Max\PhoneAuth' => 'lib/phoneauth.php',
		'Aspro\Max\PWA' => 'lib/pwa.php',
		'Aspro\Max\CrossSales' => 'lib/crosssales.php',
		'Aspro\Max\MarketingPopup' => 'lib/marketingpopup.php',
		'Aspro\Max\Property\ListStores' => 'lib/property/liststores.php',
		'Aspro\Max\Property\ListPrices' => 'lib/property/listprices.php',
		'Aspro\Max\Property\ListLocations' => 'lib/property/listlocations.php',
		'Aspro\Max\Property\CustomFilter' => 'lib/property/customfilter.php',
		'Aspro\Max\Property\CustomFilter\CondCtrl' => 'lib/property/customfilter/condctrl.php',
		'Aspro\Max\Property\Service' => 'lib/property/service.php',
		'Aspro\Max\Property\YaDirectQuery' => 'lib/property/yadirectquery.php',
		'Aspro\Max\Property\IBInherited' => 'lib/property/ibinherited.php',
		'Aspro\Max\Property\ListUsersGroups' => 'lib/property/listusersgroups.php',
		'Aspro\Max\Property\ListWebForms' => 'lib/property/listwebforms.php',
		'Aspro\Max\Property\RegionPhone' => 'lib/property/regionphone.php',
		'Aspro\Max\Property\ModalConditions' => 'lib/property/modalconditions.php',
		'Aspro\Max\Property\ModalConditions\CondModal' => 'lib/property/modalconditions/condmodal.php',
		'Aspro\Max\Property\ModalConditions\ConditionType' => 'lib/property/conditiontype.php',
		'Aspro\Max\Smartseo\General\Smartseo' => '/classes/smartseo/general/Smartseo.php',
		'Aspro\Max\Smartseo\General\SmartseoEngine' => '/classes/smartseo/general/SmartseoEngine.php',
		'Aspro\Max\Smartseo\General\SmartseoEventHandler' => '/classes/smartseo/general/SmartseoEventHandler.php',
		'Aspro\Max\Smartseo\General\SmartseoInstall' => '/classes/smartseo/general/SmartseoInstall.php',
		'Aspro\Max\Smartseo\General\SmartseoNoindex' => '/classes/smartseo/general/SmartseoNoindex.php',
		'Aspro\Max\Smartseo\Models\SmartseoFilterRuleTable' => '/lib/smartseo/models/smartseofilterrule.php',
		'Aspro\Max\Smartseo\Models\SmartseoFilterSectionTable' => '/lib/smartseo/models/smartseofiltersection.php',
		'Aspro\Max\Smartseo\Models\SmartseoFilterIblockSectionsTable' => '/lib/smartseo/models/smartseofilteriblocksections.php',
		'Aspro\Max\Smartseo\Models\SmartseoSeoTemplateTable' => '/lib/smartseo/models/smartseoseotemplate.php',
		'Aspro\Max\Smartseo\Models\SmartseoFilterConditionTable' => '/lib/smartseo/models/smartseofiltercondition.php',
		'Aspro\Max\Smartseo\Models\SmartseoFilterConditionUrlTable' => '/lib/smartseo/models/smartseofilterconditionurl.php',
		'Aspro\Max\Smartseo\Models\SmartseoSettingTable' => '/lib/smartseo/models/smartseosetting.php',
		'Aspro\Max\Smartseo\Models\SmartseoSitemapTable' => '/lib/smartseo/models/smartseositemap.php',
		'Aspro\Max\Smartseo\Models\SmartseoFilterSitemapTable' => '/lib/smartseo/models/smartseofiltersitemap.php',
		'Aspro\Max\Smartseo\Models\SmartseoFilterTagTable' => '/lib/smartseo/models/smartseofiltertag.php',
		'Aspro\Max\Smartseo\Models\SmartseoFilterSearchTable' => '/lib/smartseo/models/smartseofiltersearchtable.php',
		'Aspro\Max\Smartseo\Models\SmartseoSeoTextTable' => '/lib/smartseo/models/smartseoseotexttable.php',
		'Aspro\Max\Smartseo\Models\SmartseoSeoTextIblockSectionsTable' => '/lib/smartseo/models/smartseoseotextiblocksectionstable.php',
		'Aspro\Max\Smartseo\Models\SmartseoSeoTextPropertyTable' => '/lib/smartseo/models/smartseoseotextpropertytable.php',
		'Aspro\Max\Smartseo\Models\SmartseoNoindexUrlTable' => '/lib/smartseo/models/smartseonoindexurltable.php',
    'Aspro\Max\Smartseo\Models\SmartseoNoindexIblockSectionsTable' => '/lib/smartseo/models/smartseonoindexiblocksectionstable.php',
    'Aspro\Max\Smartseo\Models\SmartseoNoindexConditionTable' => '/lib/smartseo/models/smartseonoindexconditiontable.php',
    'Aspro\Max\Smartseo\Models\SmartseoNoindexRuleTable' => '/lib/smartseo/models/smartseonoindexruletable.php',
		'Aspro\Max\Smartseo\Models\FieldLangTable' => '/lib/smartseo/models/fieldlang.php',
		'Aspro\Max\Smartseo\Admin\Helper' => '/classes/smartseo/admin/Helper.php',
		'Aspro\Max\Smartseo\Admin\FrontController' => '/classes/smartseo/admin/FrontController.php',
		'Aspro\Max\Smartseo\Admin\App\View' => '/classes/smartseo/admin/app/View.php',
		'Aspro\Max\Smartseo\Admin\App\Controller' => '/classes/smartseo/admin/app/Controller.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterNavChainController' => '/classes/smartseo/admin/controllers/FilterNavChainController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterRulesController' => '/classes/smartseo/admin/controllers/FilterRulesController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterSectionController' => '/classes/smartseo/admin/controllers/FilterSectionController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterRuleDetailController' => '/classes/smartseo/admin/controllers/FilterRuleDetailController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterConditionController' => '/classes/smartseo/admin/controllers/FilterConditionController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterSitemapController' => '/classes/smartseo/admin/controllers/FilterSitemapController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterUrlController' => '/classes/smartseo/admin/controllers/FilterUrlController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterTagController' => '/classes/smartseo/admin/controllers/FilterTagController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\FilterSearchController' => '/classes/smartseo/admin/controllers/FilterSearchController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\SettingController' => '/classes/smartseo/admin/controllers/SettingController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\SitemapController' => '/classes/smartseo/admin/controllers/SitemapController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\SitemapDetailController' => '/classes/smartseo/admin/controllers/SitemapDetailController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\CustomEntityFilterController' => '/classes/smartseo/admin/controllers/CustomEntityFilterController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\SeoTextController' => '/classes/smartseo/admin/controllers/SeoTextController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\SeoTextSectionController' => '/classes/smartseo/admin/controllers/SeoTextSectionController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\SeoTextElementController' => '/classes/smartseo/admin/controllers/SeoTextElementController.php',
		'Aspro\Max\Smartseo\Admin\Controllers\NoindexRulesController' => '/classes/smartseo/admin/controllers/NoindexRulesController.php',
    'Aspro\Max\Smartseo\Admin\Controllers\NoindexRuleDetailController' => '/classes/smartseo/admin/controllers/NoindexRuleDetailController.php',
    'Aspro\Max\Smartseo\Admin\Controllers\NoindexConditionController' => '/classes/smartseo/admin/controllers/NoindexConditionController.php',
		'Aspro\Max\Smartseo\Admin\Grids\InnerGrid' => '/classes/smartseo/admin/grids/InnerGrid.php',
		'Aspro\Max\Smartseo\Admin\Grids\FilterRuleConditionGrid' => '/classes/smartseo/admin/grids/FilterRuleConditionGrid.php',
		'Aspro\Max\Smartseo\Admin\Grids\FilterRuleUrlGrid' => '/classes/smartseo/admin/grids/FilterRuleUrlGrid.php',
		'Aspro\Max\Smartseo\Admin\Grids\FilterRuleSitemapGrid' => '/classes/smartseo/admin/grids/FilterRuleSitemapGrid.php',
		'Aspro\Max\Smartseo\Admin\Grids\FilterRuleTagGrid' => '/classes/smartseo/admin/grids/FilterRuleTagGrid.php',
		'Aspro\Max\Smartseo\Admin\Grids\FilterRuleSearchGrid' => '/classes/smartseo/admin/grids/FilterRuleSearchGrid.php',
		'Aspro\Max\Smartseo\Admin\Grids\SitemapConditionGrid' => '/classes/smartseo/admin/grids/SitemapConditionGrid.php',
		'Aspro\Max\Smartseo\Admin\Grids\NoindexRuleConditionGrid' => '/classes/smartseo/admin/grids/NoindexRuleConditionGrid.php',
		'Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity' => '/classes/smartseo/admin/traits/BitrixCoreEntity.php',
		'Aspro\Max\Smartseo\Admin\Traits\FilterChainSectionTree' => '/classes/smartseo/admin/traits/FilterChainSectionTree.php',
		'Aspro\Max\Smartseo\Admin\UI\AbstractAdminUI' => '/classes/smartseo/admin/ui/AbstractAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\FilterRulesAdminUI' => '/classes/smartseo/admin/ui/FilterRulesAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\FilterRuleConditionAdminUI' => '/classes/smartseo/admin/ui/FilterRuleConditionAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\FilterRuleSitemapAdminUI' => '/classes/smartseo/admin/ui/FilterRuleSitemapAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\FilterRuleTagAdminUI' => '/classes/smartseo/admin/ui/FilterRuleTagAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\FilterRuleSearchAdminUI' => '/classes/smartseo/admin/ui/FilterRuleSearchAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\SitemapAdminUI' => '/classes/smartseo/admin/ui/SitemapAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\SeoTextAdminUI' => '/classes/smartseo/admin/ui/SeoTextAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\NoindexRuleAdminUI' => '/classes/smartseo/admin/ui/NoindexRuleAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\FilterRuleUrlAdminUI' => '/classes/smartseo/admin/ui/FilterRuleUrlAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\SitemapConditionAdminUI' => '/classes/smartseo/admin/ui/SitemapConditionAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\NoindexRuleConditionAdminUI' => '/classes/smartseo/admin/ui/NoindexRuleConditionAdminUI.php',
		'Aspro\Max\Smartseo\Admin\UI\SeoPropertyMenuUI' => '/classes/smartseo/admin/ui/SeoPropertyMenuUI.php',
		'Aspro\Max\Smartseo\Admin\UI\UrlPropertyMenuUI' => '/classes/smartseo/admin/ui/UrlPropertyMenuUI.php',
		'Aspro\Max\Smartseo\Admin\UI\FilterSearchSeoPropertyMenuUI' => '/classes/smartseo/admin/ui/FilterSearchSeoPropertyMenuUI.php',
		'Aspro\Max\Smartseo\Admin\UI\SettingMenuUI' => '/classes/smartseo/admin/ui/SettingMenuUI.php',
		'Aspro\Max\Smartseo\Admin\UI\SeoTextPropertyMenuUI' => '/classes/smartseo/admin/ui/SeoTextPropertyMenuUI.php',
		'Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo' => '/classes/smartseo/admin/settings/SettingSmartseo.php',
		'Aspro\Max\Smartseo\Admin\Actions\Replication' => '/classes/smartseo/admin/actions/Replication.php',
		'Aspro\Max\Smartseo\Entity\FilterRule' => '/classes/smartseo/entity/FilterRule.php',
		'Aspro\Max\Smartseo\Entity\FilterRules' => '/classes/smartseo/entity/FilterRules.php',
		'Aspro\Max\Smartseo\Entity\FilterSection' => '/classes/smartseo/entity/FilterSection.php',
		'Aspro\Max\Smartseo\Entity\FilterSections' => '/classes/smartseo/entity/FilterSections.php',
		'Aspro\Max\Smartseo\Entity\FilterIblockSection' => '/classes/smartseo/entity/FilterIblockSection.php',
		'Aspro\Max\Smartseo\Entity\FilterIblockSections' => '/classes/smartseo/entity/FilterIblockSections.php',
		'Aspro\Max\Smartseo\Entity\SeoTemplate' => '/classes/smartseo/entity/SeoTemplate.php',
		'Aspro\Max\Smartseo\Entity\SeoTemplates' => '/classes/smartseo/entity/SeoTemplates.php',
		'Aspro\Max\Smartseo\Entity\FilterCondition' => '/classes/smartseo/entity/FilterCondition.php',
		'Aspro\Max\Smartseo\Entity\FilterConditionUrl' => '/classes/smartseo/entity/FilterConditionUrl.php',
		'Aspro\Max\Smartseo\Entity\FilterConditionUrls' => '/classes/smartseo/entity/FilterConditionUrls.php',
		'Aspro\Max\Smartseo\Template\Entity\FilterRule' => '/lib/smartseo/template/entity/FilterRule.php',
		'Aspro\Max\Smartseo\Template\Entity\FilterRuleCondition' => '/lib/smartseo/template/entity/FilterRuleCondition.php',
		'Aspro\Max\Smartseo\Template\Entity\FilterRuleIblockSections' => '/lib/smartseo/template/entity/FilterRuleIblockSections.php',
		'Aspro\Max\Smartseo\Template\Entity\FilterRuleConditionProperty' => '/lib/smartseo/template/entity/FilterRuleConditionProperty.php',
		'Aspro\Max\Smartseo\Template\Entity\FilterRuleConditionPrice' => '/lib/smartseo/template/entity/FilterRuleConditionPrice.php',
		'Aspro\Max\Smartseo\Template\Entity\FilterRuleUrl' => '/lib/smartseo/template/entity/FilterRuleUrl.php',
		'Aspro\Max\Smartseo\Template\Entity\FilterRuleUrlProperty' => '/lib/smartseo/template/entity/FilterRuleUrlProperty.php',
		'Aspro\Max\Smartseo\Template\Entity\SeoText' => '/lib/smartseo/template/entity/SeoText.php',
		'Aspro\Max\Smartseo\Template\Entity\SeoTextIblockSections' => '/lib/smartseo/template/entity/SeoTextIblockSections.php',
		'Aspro\Max\Smartseo\Template\Entity\SeoTextElementProperties' => '/lib/smartseo/template/entity/SeoTextElementProperties.php',
		'Aspro\Max\Smartseo\Template\Functions\Fabric' => '/lib/smartseo/template/functions/Fabric.php',
		'Aspro\Max\Smartseo\Template\Functions\FunctionMorphology' => '/lib/smartseo/template/functions/FunctionMorphology.php',
    'Aspro\Max\Smartseo\Template\Functions\FunctionUpperFirst' => '/lib/smartseo/template/functions/FunctionUpperFirst.php',
		'Aspro\Max\Smartseo\Condition\ConditionTree' => '/lib/smartseo/condition/ConditionTree.php',
    'Aspro\Max\Smartseo\Condition\ConditionQuery' => '/lib/smartseo/condition/ConditionQuery.php',
    'Aspro\Max\Smartseo\Condition\ConditionResult' => '/lib/smartseo/condition/ConditionResult.php',
		'Aspro\Max\Smartseo\Condition\Controls\BuildControlsInterface' => '/lib/smartseo/condition/controls/BuildControlsInterface.php',
		'Aspro\Max\Smartseo\Condition\Controls\GroupBuildControls' => '/lib/smartseo/condition/controls/GroupBuildControls.php',
    'Aspro\Max\Smartseo\Condition\Controls\GroupDefaultBuildControls' => '/lib/smartseo/condition/controls/GroupDefaultBuildControls.php',
		'Aspro\Max\Smartseo\Condition\Controls\IblockBuildControls' => '/lib/smartseo/condition/controls/IblockBuildControls.php',
		'Aspro\Max\Smartseo\Condition\Controls\IblockPropertyBuildControls' => '/lib/smartseo/condition/controls/IblockPropertyBuildControls.php',
		'Aspro\Max\Smartseo\Condition\Controls\CatalogGroupBuildControls' => '/lib/smartseo/condition/controls/CatalogGroupBuildControls.php',
		'Aspro\Max\Smartseo\Condition\Controls\Group2BuildControls' => '/lib/smartseo/condition/controls/Group2BuildControls.php',
		'Aspro\Max\Smartseo\LazyLoader\ElementProperty\ElementPropertyUserField' => '/lib/smartseo/lazy_loader/element_property.php',
		'Aspro\Max\Smartseo\LazyLoader\ElementProperty\ElementPropertyEnum' => '/lib/smartseo/lazy_loader/element_property.php',
		'Aspro\Max\Smartseo\LazyLoader\ElementProperty\ElementPropertyElement' => '/lib/smartseo/lazy_loader/element_property.php',
		'Aspro\Max\Smartseo\LazyLoader\ElementProperty\ElementPropertySection' => '/lib/smartseo/lazy_loader/element_property.php',
		'Aspro\Max\Smartseo\Generator\UrlGenerator' => '/lib/smartseo/generator/UrlGenerator.php',
		'Aspro\Max\Smartseo\Generator\Handlers\AbstractUrlHandler' => '/lib/smartseo/generator/handlers/AbstractUrlHandler.php',
		'Aspro\Max\Smartseo\Generator\Handlers\SiteUrlHandler' => '/lib/smartseo/generator/handlers/SiteUrlHandler.php',
		'Aspro\Max\Smartseo\Generator\Handlers\SectionUrlHandler' => '/lib/smartseo/generator/handlers/SectionUrlHandler.php',
		'Aspro\Max\Smartseo\Generator\Handlers\PropertyUrlHandler' => '/lib/smartseo/generator/handlers/PropertyUrlHandler.php',
		'Aspro\Max\Smartseo\Generator\Handlers\IblockUrlHandler' => '/lib/smartseo/generator/handlers/IblockUrlHandler.php',
		'Aspro\Max\Smartseo\Seo\SitemapFile' => '/lib/smartseo/seo/SitemapFile.php',
		'Aspro\Max\Smartseo\Seo\SitemapIndex' => '/lib/smartseo/seo/SitemapIndex.php',
		'Aspro\Max\Smartseo\Seo\RobotsFile' => '/lib/smartseo/seo/RobotsFile.php',
		'Aspro\Max\Smartseo\Engines\Engine' => '/classes/smartseo/engines/Engine.php',
		'Aspro\Max\Smartseo\Engines\SearchEngine' => '/classes/smartseo/engines/SearchEngine.php',
		'Aspro\Max\Smartseo\Engines\SitemapEngine' => '/classes/smartseo/engines/SitemapEngine.php',
		'Aspro\Max\Smartseo\Engines\UrlEngine' => '/classes/smartseo/engines/UrlEngine.php',
		'Aspro\Max\Smartseo\Engines\UrlNoindexEngine' => '/classes/smartseo/engines/UrlNoindexEngine.php',
		'Aspro\Max\Smartseo\Engines\SeoTextEngine' => '/classes/smartseo/engines/SeoTextEngine.php',
    'Aspro\Max\Smartseo\Engines\SeoTextElementEngine' => '/classes/smartseo/engines/SeoTextElementEngine.php',
		'Aspro\Max\Smartseo\Morphy\Morphology' => '/lib/smartseo/morphy/Morphology.php',
	)
);

/* test events */

/*AddEventHandler('aspro.max', 'OnAsproRegionalityAddSelectFieldsAndProps', 'OnAsproRegionalityAddSelectFieldsAndPropsHandler'); // regionality
function OnAsproRegionalityAddSelectFieldsAndPropsHandler(&$arSelect){
	if($arSelect)
	{
		// $arSelect[] = 'PROPERTY_TEST';
	}
}*/

/*AddEventHandler('aspro.max', 'OnAsproRegionalityGetElements', 'OnAsproRegionalityGetElementsHandler'); // regionality
function OnAsproRegionalityGetElementsHandler(&$arItems){
	if($arItems)
	{
		print_r($arItems);
		foreach($arItems as $key => $arItem)
		{
			$arItems[$key]['TEST'] = CUSTOM_VALUE;
		}
	}
}*/

// AddEventHandler('aspro.max', 'OnAsproShowPriceMatrix', array('\Aspro\Functions\CAsproMax', 'OnAsproShowPriceMatrixHandler'));
// function - CMax::showPriceMatrix

// AddEventHandler('aspro.max', 'OnAsproShowPriceRangeTop', array('\Aspro\Functions\CAsproMax', 'OnAsproShowPriceRangeTopHandler'));
// function - CMax::showPriceRangeTop

// AddEventHandler('aspro.max', 'OnAsproItemShowItemPrices', array('\Aspro\Functions\CAsproMax', 'OnAsproItemShowItemPricesHandler'));
// function - \Aspro\Functions\CAsproMaxItem::showItemPrices

// AddEventHandler('aspro.max', 'OnAsproSkuShowItemPrices', array('\Aspro\Functions\CAsproMax', 'OnAsproSkuShowItemPricesHandler'));
// function - \Aspro\Functions\CAsproMaxSku::showItemPrices

// AddEventHandler('aspro.max', 'OnAsproGetTotalQuantity', array('\Aspro\Functions\CAsproMax', 'OnAsproGetTotalQuantityHandler'));
// function - CMax::GetTotalCount

// AddEventHandler('aspro.max', 'OnAsproGetTotalQuantityBlock', array('\Aspro\Functions\CAsproMax', 'OnAsproGetTotalQuantityBlockHandler'));
// function - CMax::GetQuantityArray

// AddEventHandler('aspro.max', 'OnAsproGetBuyBlockElement', array('\Aspro\Functions\CAsproMax', 'OnAsproGetBuyBlockElementHandler'));
// function - CMax::GetAddToBasketArray

?>
