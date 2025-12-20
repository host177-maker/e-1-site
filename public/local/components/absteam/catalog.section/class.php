<?

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Error;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock;
use \Bitrix\Iblock\Component\ElementList;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;
use \Bitrix\Catalog;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CIntranetToolbar $INTRANET_TOOLBAR
 */

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('iblock')) {
    ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
    return;
}

class AbsteamCatalogSectionComponent extends ElementList
{
    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->setExtendedMode(false)->setMultiIblockMode(false)->setPaginationMode(true);
        $this->setSeparateLoading(true);
    }

    public function onPrepareComponentParams($params)
    {
        $params = parent::onPrepareComponentParams($params);
        $params['IBLOCK_TYPE'] = isset($params['IBLOCK_TYPE']) ? trim($params['IBLOCK_TYPE']) : '';

        if ((int)$params['SECTION_ID'] > 0 && (int)$params['SECTION_ID'] . '' != $params['SECTION_ID'] && Loader::includeModule('iblock')) {
            $this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_SECTION_NOT_FOUND'), self::ERROR_404));
            return $params;
        }

        $params['SECTION_ID_VARIABLE'] = (isset($params['SECTION_ID_VARIABLE']) ? trim($params['SECTION_ID_VARIABLE']) : '');
        if ($params['SECTION_ID_VARIABLE'] == '' || !preg_match(self::PARAM_TITLE_MASK, $params['SECTION_ID_VARIABLE']))
            $params['SECTION_ID_VARIABLE'] = 'SECTION_ID';

        $params['SHOW_ALL_WO_SECTION'] = isset($params['SHOW_ALL_WO_SECTION']) && $params['SHOW_ALL_WO_SECTION'] === 'Y';
        $params['USE_MAIN_ELEMENT_SECTION'] = isset($params['USE_MAIN_ELEMENT_SECTION']) && $params['USE_MAIN_ELEMENT_SECTION'] === 'Y';
        $params['SECTIONS_CHAIN_START_FROM'] = isset($params['SECTIONS_CHAIN_START_FROM']) ? (int)$params['SECTIONS_CHAIN_START_FROM'] : 0;

        $params['BACKGROUND_IMAGE'] = isset($params['BACKGROUND_IMAGE']) ? trim($params['BACKGROUND_IMAGE']) : '';
        if ($params['BACKGROUND_IMAGE'] === '-') {
            $params['BACKGROUND_IMAGE'] = '';
        }

        // compatibility for bigData case with zero initial elements
        if ($params['PAGE_ELEMENT_COUNT'] <= 0 && !isset($params['PRODUCT_ROW_VARIANTS'])) {
            $params['PAGE_ELEMENT_COUNT'] = 20;
        }

        $params['CUSTOM_CURRENT_PAGE'] = isset($params['CUSTOM_CURRENT_PAGE']) ? trim($params['CUSTOM_CURRENT_PAGE']) : '';

        $params['COMPATIBLE_MODE'] = (isset($params['COMPATIBLE_MODE']) && $params['COMPATIBLE_MODE'] === 'N' ? 'N' : 'Y');
        if ($params['COMPATIBLE_MODE'] === 'N') {
            $params['DISABLE_INIT_JS_IN_COMPONENT'] = 'Y';
            $params['OFFERS_LIMIT'] = 0;
        }

        $this->setCompatibleMode($params['COMPATIBLE_MODE'] === 'Y');

        $params['DISABLE_INIT_JS_IN_COMPONENT'] = isset($params['DISABLE_INIT_JS_IN_COMPONENT']) && $params['DISABLE_INIT_JS_IN_COMPONENT'] === 'Y' ? 'Y' : 'N';

        if ($params['DISABLE_INIT_JS_IN_COMPONENT'] !== 'Y') {
            CJSCore::Init(array('popup'));
        }

        return $params;
    }

    protected function processResultData()
    {
        if ($this->initSectionResult()) {
            $this->initSectionProperties();
            parent::processResultData();
        }
    }

    protected function initSectionResult()
    {
        $success = true;
        $selectFields = array();

        if (!empty($this->arParams['SECTION_USER_FIELDS']) && is_array($this->arParams['SECTION_USER_FIELDS'])) {
            foreach ($this->arParams['SECTION_USER_FIELDS'] as $field) {
                if (is_string($field) && preg_match('/^UF_/', $field)) {
                    $selectFields[] = $field;
                }
            }
        }

        if (preg_match('/^UF_/', $this->arParams['META_KEYWORDS'])) {
            $selectFields[] = $this->arParams['META_KEYWORDS'];
        }

        if (preg_match('/^UF_/', $this->arParams['META_DESCRIPTION'])) {
            $selectFields[] = $this->arParams['META_DESCRIPTION'];
        }

        if (preg_match('/^UF_/', $this->arParams['BROWSER_TITLE'])) {
            $selectFields[] = $this->arParams['BROWSER_TITLE'];
        }

        if (preg_match('/^UF_/', $this->arParams['BACKGROUND_IMAGE'])) {
            $selectFields[] = $this->arParams['BACKGROUND_IMAGE'];
        }

        $filterFields = array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'IBLOCK_ACTIVE' => 'Y',
            'ACTIVE' => 'Y',
            'GLOBAL_ACTIVE' => 'Y',
        );

        // Hidden tricky parameter USED to display linked
        // by default it is not set

        if ($this->arParams['BY_LINK'] === 'Y') {
            $sectionResult = array(
                'ID' => 0,
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            );
        } elseif ($this->arParams['SECTION_ID'] > 0) {
            $filterFields['ID'] = $this->arParams['SECTION_ID'];
            $sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
            $sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
            $sectionResult = $sectionIterator->GetNext();
        } elseif ($this->arParams['SECTION_CODE'] <> '' || is_array($this->arParams['SECTION_CODE'])) {
            $filterFields['=CODE'] = $this->arParams['SECTION_CODE'];
            $sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
            $sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
            while ($section = $sectionIterator->GetNext()) {
                $sectionResult['ID'][] = $section['ID'];
            }
            $sectionResult['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        } elseif ($this->arParams['SECTION_CODE_PATH'] <> '') {
            $sectionId = CIBlockFindTools::GetSectionIDByCodePath($this->arParams['IBLOCK_ID'], $this->arParams['SECTION_CODE_PATH']);
            if ($sectionId) {
                $filterFields['ID'] = $sectionId;
                $sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
                $sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
                $sectionResult = $sectionIterator->GetNext();
            }
        }  else    // Root section (no section filter)
        {
            $sectionResult = array(
                'ID' => 0,
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            );
        }

        if (empty($sectionResult)) {
            $success = false;
            $this->abortResultCache();
            $this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_SECTION_NOT_FOUND'), self::ERROR_404));
        } else {
            $this->arResult = array_merge($this->arResult, $sectionResult);
            if ($this->arResult['ID'] > 0 && $this->arParams['ADD_SECTIONS_CHAIN']) {
                $this->arResult['PATH'] = array();
                $pathIterator = CIBlockSection::GetNavChain(
                    $this->arResult['IBLOCK_ID'],
                    $this->arResult['ID'],
                    array(
                        'ID', 'CODE', 'XML_ID', 'EXTERNAL_ID', 'IBLOCK_ID',
                        'IBLOCK_SECTION_ID', 'SORT', 'NAME', 'ACTIVE',
                        'DEPTH_LEVEL', 'SECTION_PAGE_URL'
                    )
                );
                $pathIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
                while ($path = $pathIterator->GetNext()) {
                    $ipropValues = new Iblock\InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $path['ID']);
                    $path['IPROPERTY_VALUES'] = $ipropValues->getValues();
                    $this->arResult['PATH'][] = $path;
                }

                if ($this->arParams['SECTIONS_CHAIN_START_FROM'] > 0) {
                    $this->arResult['PATH'] = array_slice($this->arResult['PATH'], $this->arParams['SECTIONS_CHAIN_START_FROM']);
                }
            }
        }

        return $success;
    }

    protected function initSectionProperties()
    {
        $arResult =& $this->arResult;

        $arResult['IPROPERTY_VALUES'] = array();
        if ($arResult['ID'] > 0) {
            $ipropValues = new Iblock\InheritedProperty\SectionValues($arResult['IBLOCK_ID'], $arResult['ID']);
            $arResult['IPROPERTY_VALUES'] = $ipropValues->getValues();
        }

        Iblock\Component\Tools::getFieldImageData(
            $arResult,
            array('PICTURE', 'DETAIL_PICTURE'),
            Iblock\Component\Tools::IPROPERTY_ENTITY_SECTION,
            'IPROPERTY_VALUES'
        );

        $arResult['BACKGROUND_IMAGE'] = false;
        if ($this->arParams['BACKGROUND_IMAGE'] != '' && !empty($arResult[$this->arParams['BACKGROUND_IMAGE']])) {
            $arResult['BACKGROUND_IMAGE'] = CFile::GetFileArray($arResult[$this->arParams['BACKGROUND_IMAGE']]);
        }
    }

    protected function initCatalogInfo()
    {
        parent::initCatalogInfo();
        $useCatalogButtons = array();
        if (
            !empty($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
            && is_array($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
        ) {
            $catalogType = $this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']]['CATALOG_TYPE'];
            if ($catalogType == CCatalogSku::TYPE_CATALOG || $catalogType == CCatalogSku::TYPE_FULL) {
                $useCatalogButtons['add_product'] = true;
            }

            if ($catalogType == CCatalogSku::TYPE_PRODUCT || $catalogType == CCatalogSku::TYPE_FULL) {
                $useCatalogButtons['add_sku'] = true;
            }
            unset($catalogType);
        }

        $this->storage['USE_CATALOG_BUTTONS'] = $useCatalogButtons;
    }

    protected function getCacheKeys()
    {
        return array(
            'ID',
            'NAV_CACHED_DATA',
            'NAV_STRING',
            $this->arParams['META_KEYWORDS'],
            $this->arParams['META_DESCRIPTION'],
            $this->arParams['BROWSER_TITLE'],
            $this->arParams['BACKGROUND_IMAGE'],
            'NAME',
            'PATH',
            'IBLOCK_SECTION_ID',
            'IPROPERTY_VALUES',
            'ITEMS_TIMESTAMP_X',
            'BACKGROUND_IMAGE',
            'USE_CATALOG_BUTTONS'
        );
    }

    protected function getFilter()
    {
        $filterFields = parent::getFilter();

        if ($this->getAction() === 'bigDataLoad') {
            return $filterFields;
        }

        $filterFields['INCLUDE_SUBSECTIONS'] = $this->arParams['INCLUDE_SUBSECTIONS'] === 'N' ? 'N' : 'Y';

        if ($this->arParams['INCLUDE_SUBSECTIONS'] === 'A') {
            $filterFields['SECTION_GLOBAL_ACTIVE'] = 'Y';
        }

        if ($this->arParams['BY_LINK'] !== 'Y') {
            if ($this->arResult['ID']) {
                $filterFields['SECTION_ID'] = $this->arResult['ID'];
            } elseif (!$this->arParams['SHOW_ALL_WO_SECTION']) {
                $filterFields['SECTION_ID'] = 0;
            } else {
                unset($filterFields['INCLUDE_SUBSECTIONS']);
                unset($filterFields['SECTION_GLOBAL_ACTIVE']);
            }
        }

        return $filterFields;
    }

    protected function makeOutputResult()
    {
        parent::makeOutputResult();
        $this->arResult['USE_CATALOG_BUTTONS'] = $this->storage['USE_CATALOG_BUTTONS'];
    }

    protected function initialLoadAction()
    {
        parent::initialLoadAction();

        if (!$this->hasErrors()) {
            $this->initAdminIconsPanel();
            $this->setTemplateCachedData($this->arResult['NAV_CACHED_DATA']);
            $this->initMetaData();
        }
    }

    protected function initAdminIconsPanel()
    {
        global $APPLICATION, $INTRANET_TOOLBAR, $USER;

        if (!$USER->IsAuthorized()) {
            return;
        }

        $arResult =& $this->arResult;

        if (
            $APPLICATION->GetShowIncludeAreas()
            || (is_object($INTRANET_TOOLBAR) && $this->arParams['INTRANET_TOOLBAR'] !== 'N')
            || $this->arParams['SET_TITLE']
            || isset($arResult[$this->arParams['BROWSER_TITLE']])
        ) {
            if (Loader::includeModule('iblock')) {
                $urlDeleteSectionButton = '';

                if ($arResult['IBLOCK_SECTION_ID'] > 0) {
                    $sectionIterator = CIBlockSection::GetList(
                        array(),
                        array('=ID' => $arResult['IBLOCK_SECTION_ID']),
                        false,
                        array('SECTION_PAGE_URL')
                    );
                    $sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
                    $section = $sectionIterator->GetNext();
                    $urlDeleteSectionButton = $section['SECTION_PAGE_URL'];
                }

                if (empty($urlDeleteSectionButton)) {
                    $urlTemplate = CIBlock::GetArrayByID($this->arParams['IBLOCK_ID'], 'LIST_PAGE_URL');
                    $iblock = CIBlock::GetArrayByID($this->arParams['IBLOCK_ID']);
                    $iblock['IBLOCK_CODE'] = $iblock['CODE'];
                    $urlDeleteSectionButton = CIBlock::ReplaceDetailUrl($urlTemplate, $iblock, true, false);
                }

                $returnUrl = array(
                    'add_section' => (
                    $this->arParams['SECTION_URL'] <> '' ? $this->arParams['SECTION_URL'] : CIBlock::GetArrayByID($this->arParams['IBLOCK_ID'], 'SECTION_PAGE_URL')
                    ),
                    'delete_section' => $urlDeleteSectionButton,
                );
                $buttonParams = array(
                    'RETURN_URL' => $returnUrl,
                    'CATALOG' => true
                );

                if (isset($arResult['USE_CATALOG_BUTTONS'])) {
                    $buttonParams['USE_CATALOG_BUTTONS'] = $arResult['USE_CATALOG_BUTTONS'];
                }

                $buttons = CIBlock::GetPanelButtons(
                    $this->arParams['IBLOCK_ID'],
                    0,
                    $arResult['ID'],
                    $buttonParams
                );
                unset($buttonParams);

                if ($APPLICATION->GetShowIncludeAreas()) {
                    $this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));
                }

                if (
                    is_array($buttons['intranet'])
                    && is_object($INTRANET_TOOLBAR)
                    && $this->arParams['INTRANET_TOOLBAR'] !== 'N'
                ) {
                    Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');

                    foreach ($buttons['intranet'] as $button) {
                        $INTRANET_TOOLBAR->AddButton($button);
                    }
                }

                if ($this->arParams['SET_TITLE'] || isset($arResult[$this->arParams['BROWSER_TITLE']])) {
                    $this->storage['TITLE_OPTIONS'] = array(
                        'ADMIN_EDIT_LINK' => $buttons['submenu']['edit_section']['ACTION'],
                        'PUBLIC_EDIT_LINK' => $buttons['edit']['edit_section']['ACTION'],
                        'COMPONENT_NAME' => $this->getName(),
                    );
                }
            }
        }
    }

    protected function initMetaData()
    {
        global $APPLICATION;

        if ($this->arParams['SET_TITLE']) {
            if ($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] != '') {
                $APPLICATION->SetTitle($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $this->storage['TITLE_OPTIONS']);
            } elseif (isset($this->arResult['NAME'])) {
                $APPLICATION->SetTitle($this->arResult['NAME'], $this->storage['TITLE_OPTIONS']);
            }
        }

        if ($this->arParams['SET_BROWSER_TITLE'] === 'Y') {
            $browserTitle = Main\Type\Collection::firstNotEmpty(
                $this->arResult, $this->arParams['BROWSER_TITLE'],
                $this->arResult['IPROPERTY_VALUES'], 'SECTION_META_TITLE'
            );
            if (is_array($browserTitle)) {
                $APPLICATION->SetPageProperty('title', implode(' ', $browserTitle), $this->storage['TITLE_OPTIONS']);
            } elseif ($browserTitle != '') {
                $APPLICATION->SetPageProperty('title', $browserTitle, $this->storage['TITLE_OPTIONS']);
            }
        }

        if ($this->arParams['SET_META_KEYWORDS'] === 'Y') {
            $metaKeywords = Main\Type\Collection::firstNotEmpty(
                $this->arResult, $this->arParams['META_KEYWORDS'],
                $this->arResult['IPROPERTY_VALUES'], 'SECTION_META_KEYWORDS'
            );
            if (is_array($metaKeywords)) {
                $APPLICATION->SetPageProperty('keywords', implode(' ', $metaKeywords), $this->storage['TITLE_OPTIONS']);
            } elseif ($metaKeywords != '') {
                $APPLICATION->SetPageProperty('keywords', $metaKeywords, $this->storage['TITLE_OPTIONS']);
            }
        }

        if ($this->arParams['SET_META_DESCRIPTION'] === 'Y') {
            $metaDescription = Main\Type\Collection::firstNotEmpty(
                $this->arResult, $this->arParams['META_DESCRIPTION'],
                $this->arResult['IPROPERTY_VALUES'], 'SECTION_META_DESCRIPTION'
            );
            if (is_array($metaDescription)) {
                $APPLICATION->SetPageProperty('description', implode(' ', $metaDescription), $this->storage['TITLE_OPTIONS']);
            } elseif ($metaDescription != '') {
                $APPLICATION->SetPageProperty('description', $metaDescription, $this->storage['TITLE_OPTIONS']);
            }
        }

        if (!empty($this->arResult['BACKGROUND_IMAGE']) && is_array($this->arResult['BACKGROUND_IMAGE'])) {
            $APPLICATION->SetPageProperty(
                'backgroundImage',
                'style="background-image: url(\'' . \CHTTP::urnEncode($this->arResult['BACKGROUND_IMAGE']['SRC'], 'UTF-8') . '\')"'
            );
        }

        if ($this->arParams['ADD_SECTIONS_CHAIN'] && is_array($this->arResult['PATH'])) {
            foreach ($this->arResult['PATH'] as $path) {
                if ($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] != '') {
                    $APPLICATION->AddChainItem($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $path['~SECTION_PAGE_URL']);
                } else {
                    $APPLICATION->AddChainItem($path['NAME'], $path['~SECTION_PAGE_URL']);
                }
            }
        }

        if ($this->arParams['SET_LAST_MODIFIED'] && $this->arResult['ITEMS_TIMESTAMP_X']) {
            Main\Context::getCurrent()->getResponse()->setLastModified($this->arResult['ITEMS_TIMESTAMP_X']);
        }
    }

    protected function getElementList($iblockId, $products)
    {
        $elementIterator = parent::getElementList($iblockId, $products);

        if (
            !empty($elementIterator)
            && $this->arParams['BY_LINK'] !== 'Y'
            && !$this->arParams['SHOW_ALL_WO_SECTION']
            && !$this->arParams['USE_MAIN_ELEMENT_SECTION']
        ) {
            $elementIterator->SetSectionContext($this->arResult);
        }

        return $elementIterator;
    }

    protected function processElement(array &$element)
    {
        if ($this->arResult['ID']) {
            $element['IBLOCK_SECTION_ID'] = $this->arResult['ID'];
        }

        parent::processElement($element);
        $this->checkLastModified($element);
    }

    protected function checkLastModified($element)
    {
        if ($this->arParams['SET_LAST_MODIFIED']) {
            $time = DateTime::createFromUserTime($element['TIMESTAMP_X']);
            if (
                !isset($this->arResult['ITEMS_TIMESTAMP_X'])
                || $time->getTimestamp() > $this->arResult['ITEMS_TIMESTAMP_X']->getTimestamp()
            ) {
                $this->arResult['ITEMS_TIMESTAMP_X'] = $time;
            }
        }
    }

    protected function initElementList()
    {
        parent::initElementList();

        // compatibility for old components
        if ($this->isEnableCompatible() && empty($this->arResult['NAV_RESULT'])) {
            $this->initNavString(\CIBlockElement::GetList(
                array(),
                array_merge($this->globalFilter, $this->filterFields + array('IBLOCK_ID' => $this->arParams['IBLOCK_ID'])),
                false,
                array('nTopCount' => 1),
                array('ID')
            ));
            $this->arResult['NAV_RESULT']->NavNum = Main\Security\Random::getString(6);
        }

        $this->storage['sections'] = array();

        if (!empty($this->elements) && is_array($this->elements)) {
            foreach ($this->elements as &$element) {
                $this->modifyItemPath($element);
            }
        }
    }

    protected function modifyItemPath(&$element)
    {
        $sections =& $this->storage['sections'];

        if ($this->arParams['BY_LINK'] === 'Y') {
            if (!isset($sections[$element['IBLOCK_SECTION_ID']])) {
                $sections[$element['IBLOCK_SECTION_ID']] = array();
                $pathIterator = CIBlockSection::GetNavChain(
                    $element['IBLOCK_ID'],
                    $element['IBLOCK_SECTION_ID'],
                    array(
                        'ID', 'CODE', 'XML_ID', 'EXTERNAL_ID', 'IBLOCK_ID',
                        'IBLOCK_SECTION_ID', 'SORT', 'NAME', 'ACTIVE',
                        'DEPTH_LEVEL', 'SECTION_PAGE_URL'
                    )
                );
                $pathIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
                while ($path = $pathIterator->GetNext()) {
                    $sections[$element['IBLOCK_SECTION_ID']][] = $path;
                }
            }

            $element['SECTION']['PATH'] = $sections[$element['IBLOCK_SECTION_ID']];
        } else {
            $element['SECTION']['PATH'] = array();
        }
    }

    public function executeComponent()
    {

        $this->checkModules();

        if ($this->hasErrors())
        {
            return $this->processErrors();
        }

        $action = $this->prepareAction();
        $this->setAction($action);
        $this->doAction();

        if ($this->hasErrors())
        {
            return $this->processErrors();
        }

//        $this->loadItems();
//
//        $this->includeComponentTemplate();

        return isset($this->arResult['ID']) ? $this->arResult['ID'] : false;
    }

    protected function loadItems()
    {
        $this->arParams['PRICE_CODE'][] = 'BASE';
        $priceGroups = Capsule::table('b_catalog_group')->whereIn('NAME', $this->arParams['PRICE_CODE'] + ['BASE'])->get();

        $nav = new \Bitrix\Main\UI\PageNavigation("nav-more-news");
        // инициализация навигации
        $nav->allowAllRecords(true)
            // количество
            ->setPageSize($this->arParams['PAGE_ELEMENT_COUNT'])
            // метод инициализирует навигацию от URI
            ->initFromUri();

        $arFilters = $GLOBALS[$this->arParams['FILTER_NAME']];

        $preparedFilter = [];

        foreach ($arFilters as $key => $value) {
            if (strpos($key, 'PROPERTY_') === 0 || strpos($key, '=PROPERTY_') === 0) {
                $key = str_replace(['=PROPERTY_', 'PROPERTY_'], '', $key);
                $preparedFilter[$key]['operator'] = '=';
                $preparedFilter[$key]['value'] = $value;
            } elseif (strpos($key, '>=PROPERTY_') === 0) {
                $key = str_replace(['>=PROPERTY_'], '', $key);
                $preparedFilter[$key]['operator'] = '>=';
                $preparedFilter[$key]['value'] = $value;
            } elseif (strpos($key, '>PROPERTY_') === 0) {
                $key = str_replace(['>PROPERTY_'], '', $key);
                $preparedFilter[$key]['operator'] = '>';
                $preparedFilter[$key]['value'] = $value;
            } elseif (strpos($key, '>=PROPERTY_') === 0) {
                $key = str_replace(['<=PROPERTY_'], '', $key);
                $preparedFilter[$key]['operator'] = '<';
                $preparedFilter[$key]['value'] = $value;
            } elseif (strpos($key, '>PROPERTY_') === 0) {
                $key = str_replace(['<PROPERTY_'], '', $key);
                $preparedFilter[$key]['operator'] = '<';
                $preparedFilter[$key]['value'] = $value;
            }
        }

        $basePrice = $this->arParams['BASE_PRICE'];

        $cItems = Capsule::table('b_iblock_element')
            ->select([
                'b_iblock_element.ID',
                'NAME',
                'PREVIEW_PICTURE'
            ])
            ->where('IBLOCK_ID', $this->arParams['IBLOCK_ID'])
            ->where('IBLOCK_SECTION_ID', $this->arParams['SECTION_ID'])
            ->limit($nav->getLimit())
            ->offset($nav->getOffset());

        if (!empty($preparedFilter)) {
            $cItems
                ->addSelect(Capsule::raw('COUNT(b_iblock_element_property.IBLOCK_ELEMENT_ID) as cnt'))
                ->leftJoin('b_iblock_element_property', 'b_iblock_element.ID', '=', 'b_iblock_element_property.IBLOCK_ELEMENT_ID')
                ->having('cnt', '>=', count($preparedFilter))
                ->groupBy([
                    'b_iblock_element.ID', 'NAME', 'PREVIEW_PICTURE'
                ]);

            $cItems->where(function ($group) use ($preparedFilter) {
                foreach ($preparedFilter as $key => $value) {
                    $group->orWhere(function ($q) use ($key, $value) {
                        $q->where('IBLOCK_PROPERTY_ID', $key);
                        if (is_array($value)) {
                            $q->whereIn('VALUE', $value['value']);
                        } else {
                            $q->where('VALUE', $value['operator'], $value['value']);
                        }
                    });
                }
            });
        }

        $cItems = $cItems->get();

        $arItems = array_map(function ($item) {
            $item = (array)$item;
            $url = CIBlock::ReplaceDetailUrlEx($this->arParams['DETAIL_URL'], [
                'ID' => $item['ID'],
                'IBLOCK_SECTION_ID' => $this->arParams['SECTION_ID']
            ], false, 'E');
            $item['DETAIL_PAGE_URL'] = $url;
            $item['PREVIEW_PICTURE'] = \CFile::GetFileArray($item['PREVIEW_PICTURE']);
            return $item;
        }, $cItems->toArray());

        $offers = Capsule::table('b_iblock_element')
            ->select('b_iblock_element.ID', 'NAME', Capsule::raw('VALUE_NUM AS ELEMENT_ID'))
            ->leftJoin('b_iblock_element_property', 'b_iblock_element.ID', '=', 'b_iblock_element_property.IBLOCK_ELEMENT_ID')
            ->where('b_iblock_element_property.IBLOCK_PROPERTY_ID', 890)
            ->whereIn('VALUE_NUM', $cItems->pluck('ID')->values())->get();

        $prices = Capsule::table('b_catalog_price')
            ->whereIn('PRODUCT_ID', $offers->pluck('ID')->values()->toArray())
            ->whereIn('CATALOG_GROUP_ID', $priceGroups->pluck('ID')->values()->toArray())
            ->get();

        $aPriceGroups = $priceGroups->keyBy('ID')->toArray();

        $prices = $prices->groupBy('PRODUCT_ID')->toArray();

        $arItems = array_map(function ($item) use ($offers, $prices, $aPriceGroups) {
            $minPrice = [];
            $minOffer = [];
            $offers->where('ELEMENT_ID', $item['ID'])->map(function ($offer) use ($prices, &$minPrice, &$minOffer, $aPriceGroups) {
                $offer = (array)$offer;
                $offerMinPrice = [];
                $offer['CLR_PRICES'] = array_map(function ($price) use (&$minPrice, &$minOffer, &$offerMinPrice, $offer) {
                    $price = (array)$price;

                    if (empty($minPrice) || $minPrice['PRICE'] > $price['PRICE']) {
                        $minPrice = $price;
                        $minOffer = $offer;
                    }
                    if (empty($offerMinPrice) || $offerMinPrice['PRICE'] > $price['PRICE'])
                        $offerMinPrice = $price;

                    return (array)$price;
                }, $prices[$offer['ID']]);


                foreach ($offer['CLR_PRICES'] as $CLR_PRICE){
                    $offer['PRICES'][$aPriceGroups[$CLR_PRICE['CATALOG_GROUP_ID']]->NAME] = $CLR_PRICE;
                }

                $offer['MIN_PRICE'] = $offerMinPrice;
                return $offer;
            })->values()->toArray();

            $item['OFFERS'][] = $minOffer;
            $item['MIN_PRICE'] = $minPrice;
            return $item;
        }, $arItems);

        $properties = Capsule::table('b_iblock_element_property')
            ->select('IBLOCK_PROPERTY_ID', 'CODE', 'VALUE', 'IBLOCK_ELEMENT_ID', 'MULTIPLE', 'PROPERTY_TYPE')
            ->leftJoin('b_iblock_property', 'b_iblock_element_property.IBLOCK_PROPERTY_ID', '=', 'b_iblock_property.ID')
            ->whereIn('IBLOCK_ELEMENT_ID', $cItems->pluck('ID')->values()->toArray())
            ->get();

        $properties = $properties->groupBy('IBLOCK_ELEMENT_ID');

        $enumProps = Capsule::table('b_iblock_property_enum')->get()->keyBy('ID')->toArray();

        $arItems = array_map(function ($item) use ($properties, $enumProps) {
            foreach ($properties[$item['ID']] as $property){
                $property = (array)$property;
                $val = $property['PROPERTY_TYPE'] == 'L' ?$enumProps[$property['VALUE']]->VALUE : $property['VALUE'];
                if($property['MULTIPLE'] == 'Y'){
                    $item['PROPERTIES'][$property['CODE']]['VALUE'][] = $val;
                }else{
                    $item['PROPERTIES'][$property['CODE']]['VALUE'] = $val;
                }
            }
            COrwoFunctions::ChangeCatalogItemPrice($item, $item['OFFERS'][0], $this->arParams);
            return $item;
        }, $arItems);


        $this->arResult['ITEMS'] = $arItems;

        $this->arResult['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
    }


    protected function getIblockOffers($iblockId)
    {
        $offers = array();
        $iblockParams = $this->storage['IBLOCK_PARAMS'][$iblockId];

        $enableCompatible = $this->isEnableCompatible();

        if (
            $this->useCatalog
            && $this->offerIblockExist($iblockId)
            && !empty($this->productWithOffers[$iblockId])
        )
        {
            $catalog = $this->storage['CATALOGS'][$iblockId];

            $productProperty = 'PROPERTY_'.$catalog['SKU_PROPERTY_ID'];
            $productPropertyValue = $productProperty.'_VALUE';

            $offersFilter = $this->getOffersFilter($catalog['IBLOCK_ID']);
            $offersFilter[$productProperty] = $this->productWithOffers[$iblockId];

            $offersSelect = array(
                'ID' => 1,
                'IBLOCK_ID' => 1,
                $productProperty => 1,
                'PREVIEW_PICTURE' => 1,
                'DETAIL_PICTURE' => 1,
            );

            if ($this->arParams['SHOW_SKU_DESCRIPTION'] === 'Y')
            {
                $offersSelect['PREVIEW_TEXT'] = 1;
                $offersSelect['DETAIL_TEXT'] = 1;
                $offersSelect['PREVIEW_TEXT_TYPE'] = 1;
                $offersSelect['DETAIL_TEXT_TYPE'] = 1;
            }

            if (!empty($iblockParams['OFFERS_FIELD_CODE']))
            {
                foreach ($iblockParams['OFFERS_FIELD_CODE'] as $code)
                    $offersSelect[$code] = 1;
                unset($code);
            }

            $offersSelect = $this->getProductSelect($iblockId, array_keys($offersSelect));

            $getListParams = $this->prepareQueryFields($offersSelect, $offersFilter, $this->getOffersSort());
            $offersSelect = $getListParams['SELECT'];
            $offersFilter = $getListParams['FILTER'];
            $offersOrder = $getListParams['ORDER'];
            unset($getListParams);

            $checkFields = array();
            foreach (array_keys($offersOrder) as $code)
            {
                $code = mb_strtoupper($code);
                if ($code == 'ID' || $code == 'AVAILABLE')
                    continue;
                $checkFields[] = $code;
            }
            unset($code);

            $productFields = $this->getProductFields($iblockId);
            $translateFields = $this->getCompatibleProductFields();

            $offersId = array();
            $offersCount = array();

            $this->arParams['PRICE_CODE'][] = 'BASE';
            $priceGroups = Capsule::table('b_catalog_group')->whereIn('NAME', $this->arParams['PRICE_CODE'] + ['BASE'])->get();
            $offers = Capsule::table('b_iblock_element')
            ->select('b_iblock_element.ID', 'NAME', 'SORT', Capsule::raw('VALUE_NUM AS ELEMENT_ID'), Capsule::raw('VALUE AS EL_ID'))
            ->leftJoin('b_iblock_element_property', 'b_iblock_element.ID', '=', 'b_iblock_element_property.IBLOCK_ELEMENT_ID')
            ->where('b_iblock_element_property.IBLOCK_PROPERTY_ID', $catalog['SKU_PROPERTY_ID'])
            ->where('ACTIVE', 'Y')
            ->whereIn('VALUE_NUM', $this->productWithOffers[$iblockId])
            ->orderBy('b_iblock_element.SORT', 'asc')
            ->get();

            $colorOffers = Capsule::table('b_iblock_element')
                ->select('b_iblock_element.ID', 'NAME', 'SORT', Capsule::raw('VALUE_NUM AS ELEMENT_ID'), Capsule::raw('VALUE AS COLOR'),)
                ->leftJoin('b_iblock_element_property', 'b_iblock_element.ID', '=', 'b_iblock_element_property.IBLOCK_ELEMENT_ID')
                ->where('b_iblock_element_property.IBLOCK_PROPERTY_ID', 907)
                ->where('ACTIVE', 'Y')
                ->whereIn('b_iblock_element.ID', $offers->pluck('ID')->values()->toArray())
                ->orderBy('b_iblock_element.SORT', 'asc')
                ->get();


            $aColorOffers = [];
            foreach ($colorOffers as $colorOffer){
                $aColorOffers[$colorOffer->ID] = $colorOffer->COLOR;
            }

            $mergeCollect = $offers->map(function($offer) use ($aColorOffers, $priceGroups) {
                if(empty($aColorOffers[$offer->ID])) {
                    return $offer;
                }

                $offer->COLOR = $aColorOffers[$offer->ID];
                    
                $arPrice = \CCatalogProduct::GetOptimalPrice($offer->ID, 1, [], 'N', [], 's1');
                $offer->PRICES = $arPrice['DISCOUNT_PRICE'];

                return $offer;
            });

            $mergeCollect = $mergeCollect->groupBy(['EL_ID', 'COLOR']);

            $offerIds = [];
            $mergeCollect->each(function($products) use (&$offerIds) {
                $products->each(function($colors) use (&$offerIds){
                    $id = $this->getMinPriceOrSort($colors->toArray());
                    if(is_numeric($id)) {
                        $offerIds[] = $id;
                    }
                });
            });

            $offers = $offers->groupBy('EL_ID')->toArray();

            $offersFilter['=ID'] = $offerIds;

            $iterator = \CIBlockElement::GetList(
                $offersOrder,
                $offersFilter,
                false,
                false,
                $offersSelect
            );

            while($row = $iterator->GetNext())
            {
                $row['ID'] = (int)$row['ID'];
                $row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
                $productId = (int)$row[$productPropertyValue];

                if ($productId <= 0)
                    continue;

                if ($enableCompatible && $this->arParams['OFFERS_LIMIT'] > 0)
                {
                    $offersCount[$productId]++;
                    if($offersCount[$productId] > $this->arParams['OFFERS_LIMIT'])
                        continue;
                }

                $row['SORT_HASH'] = 'ID';
                if (!empty($checkFields))
                {
                    $checkValues = '';
                    foreach ($checkFields as $code)
                        $checkValues .= (isset($row[$code]) ? $row[$code] : '').'|';
                    unset($code);
                    if ($checkValues != '')
                        $row['SORT_HASH'] = md5($checkValues);
                    unset($checkValues);
                }
                $row['LINK_ELEMENT_ID'] = $productId;
                $row['PROPERTIES'] = array();
                $row['DISPLAY_PROPERTIES'] = array();

                $row['PRODUCT'] = array(
                    'TYPE' => (int)$row['~TYPE'],
                    'AVAILABLE' => $row['~AVAILABLE'],
                    'BUNDLE' => $row['~BUNDLE'],
                    'QUANTITY' => $row['~QUANTITY'],
                    'QUANTITY_TRACE' => $row['~QUANTITY_TRACE'],
                    'CAN_BUY_ZERO' => $row['~CAN_BUY_ZERO'],
                    'MEASURE' => (int)$row['~MEASURE'],
                    'SUBSCRIBE' => $row['~SUBSCRIBE'],
                    'VAT_ID' => (int)$row['~VAT_ID'],
                    'VAT_RATE' => 0,
                    'VAT_INCLUDED' => $row['~VAT_INCLUDED'],
                    'WEIGHT' => (float)$row['~WEIGHT'],
                    'WIDTH' => (float)$row['~WIDTH'],
                    'LENGTH' => (float)$row['~LENGTH'],
                    'HEIGHT' => (float)$row['~HEIGHT'],
                    'PAYMENT_TYPE' => $row['~PAYMENT_TYPE'],
                    'RECUR_SCHEME_TYPE' => $row['~RECUR_SCHEME_TYPE'],
                    'RECUR_SCHEME_LENGTH' => (int)$row['~RECUR_SCHEME_LENGTH'],
                    'TRIAL_PRICE_ID' => (int)$row['~TRIAL_PRICE_ID']
                );

                $vatId = 0;
                $vatRate = 0;
                if ($row['PRODUCT']['VAT_ID'] > 0)
                    $vatId = $row['PRODUCT']['VAT_ID'];
                elseif ($this->storage['IBLOCKS_VAT'][$catalog['IBLOCK_ID']] > 0)
                    $vatId = $this->storage['IBLOCKS_VAT'][$catalog['IBLOCK_ID']];
                if ($vatId > 0 && isset($this->storage['VATS'][$vatId]))
                    $vatRate = $this->storage['VATS'][$vatId];
                $row['PRODUCT']['VAT_RATE'] = $vatRate;
                unset($vatRate, $vatId);

                if ($enableCompatible)
                {
                    foreach ($translateFields as $currentKey => $oldKey)
                        $row[$oldKey] = $row[$currentKey];
                    unset($currentKey, $oldKey);
                    $row['~CATALOG_VAT'] = $row['PRODUCT']['VAT_RATE'];
                    $row['CATALOG_VAT'] = $row['PRODUCT']['VAT_RATE'];
                }
                else
                {
                    // temporary (compatibility custom templates)
                    $row['~CATALOG_TYPE'] = $row['PRODUCT']['TYPE'];
                    $row['CATALOG_TYPE'] = $row['PRODUCT']['TYPE'];
                    $row['~CATALOG_QUANTITY'] = $row['PRODUCT']['QUANTITY'];
                    $row['CATALOG_QUANTITY'] = $row['PRODUCT']['QUANTITY'];
                    $row['~CATALOG_QUANTITY_TRACE'] = $row['PRODUCT']['QUANTITY_TRACE'];
                    $row['CATALOG_QUANTITY_TRACE'] = $row['PRODUCT']['QUANTITY_TRACE'];
                    $row['~CATALOG_CAN_BUY_ZERO'] = $row['PRODUCT']['CAN_BUY_ZERO'];
                    $row['CATALOG_CAN_BUY_ZERO'] = $row['PRODUCT']['CAN_BUY_ZERO'];
                    $row['~CATALOG_SUBSCRIBE'] = $row['PRODUCT']['SUBSCRIBE'];
                    $row['CATALOG_SUBSCRIBE'] = $row['PRODUCT']['SUBSCRIBE'];
                }

                foreach ($productFields as $field)
                    unset($row[$field], $row['~'.$field]);
                unset($field);

                CModule::IncludeModule('catalog');

                if ($row['PRODUCT']['TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_OFFER)
                    $this->calculatePrices[$row['ID']] = $row['ID'];

                $row['ITEM_PRICE_MODE'] = null;
                $row['ITEM_PRICES'] = array();
                $row['ITEM_QUANTITY_RANGES'] = array();
                $row['ITEM_MEASURE_RATIOS'] = array();
                $row['ITEM_MEASURE'] = array();
                $row['ITEM_MEASURE_RATIO_SELECTED'] = null;
                $row['ITEM_QUANTITY_RANGE_SELECTED'] = null;
                $row['ITEM_PRICE_SELECTED'] = null;
                $row['CHECK_QUANTITY'] = $this->isNeedCheckQuantity($row['PRODUCT']);

                if ($row['PRODUCT']['MEASURE'] > 0)
                {
                    $row['ITEM_MEASURE'] = array(
                        'ID' => $row['PRODUCT']['MEASURE'],
                        'TITLE' => '',
                        '~TITLE' => ''
                    );
                }
                else
                {
                    $row['ITEM_MEASURE'] = array(
                        'ID' => null,
                        'TITLE' => $this->storage['DEFAULT_MEASURE']['SYMBOL_RUS'],
                        '~TITLE' => $this->storage['DEFAULT_MEASURE']['~SYMBOL_RUS']
                    );
                }
                if ($enableCompatible)
                {
                    $row['CATALOG_MEASURE'] = $row['ITEM_MEASURE']['ID'];
                    $row['CATALOG_MEASURE_NAME'] = $row['ITEM_MEASURE']['TITLE'];
                    $row['~CATALOG_MEASURE_NAME'] = $row['ITEM_MEASURE']['~TITLE'];
                }

                $row['PROPERTIES'] = array();
                $row['DISPLAY_PROPERTIES'] = array();

                Iblock\Component\Tools::getFieldImageData(
                    $row,
                    array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
                    Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
                    ''
                );

                $offersId[$row['ID']] = $row['ID'];
                $offers[$row['ID']] = $row;
            }
            unset($row, $iterator);

            if (!empty($offersId))
            {
                $loadPropertyCodes = $iblockParams['OFFERS_PROPERTY_CODE'];
                if (Iblock\Model\PropertyFeature::isEnabledFeatures())
                    $loadPropertyCodes = array_merge($loadPropertyCodes, $iblockParams['OFFERS_TREE_PROPS']);

                $propertyList = $this->getPropertyList($catalog['IBLOCK_ID'], $loadPropertyCodes);
                unset($loadPropertyCodes);

                if (!empty($propertyList) || $this->useDiscountCache)
                {
                    \CIBlockElement::GetPropertyValuesArray($offers, $catalog['IBLOCK_ID'], $offersFilter);
                    foreach ($offers as &$row)
                    {
                        if ($this->useDiscountCache)
                        {
                            if ($this->storage['USE_SALE_DISCOUNTS'])
                                Catalog\Discount\DiscountManager::setProductPropertiesCache($row['ID'], $row["PROPERTIES"]);
                            else
                                \CCatalogDiscount::SetProductPropertiesCache($row['ID'], $row["PROPERTIES"]);
                        }

                        if (!empty($propertyList))
                        {
                            foreach ($propertyList as $pid)
                            {
                                if (!isset($row["PROPERTIES"][$pid]))
                                    continue;
                                $prop = &$row["PROPERTIES"][$pid];
                                $boolArr = is_array($prop["VALUE"]);
                                if (
                                    ($boolArr && !empty($prop["VALUE"])) ||
                                    (!$boolArr && (string)$prop["VALUE"] !== '')
                                )
                                {
                                    $row["DISPLAY_PROPERTIES"][$pid] = \CIBlockFormatProperties::GetDisplayValue($row, $prop, "catalog_out");
                                }
                                unset($boolArr, $prop);
                            }
                            unset($pid);
                        }
                    }
                    unset($row);
                }

                if ($this->useDiscountCache)
                {
                    if ($this->storage['USE_SALE_DISCOUNTS'])
                    {
                        Catalog\Discount\DiscountManager::preloadPriceData($offersId, $this->storage['PRICES_ALLOW']);
                        Catalog\Discount\DiscountManager::preloadProductDataToExtendOrder($offersId, $this->getUserGroups());
                    }
                    else
                    {
                        \CCatalogDiscount::SetProductSectionsCache($offersId);
                        \CCatalogDiscount::SetDiscountProductCache($offersId, array('IBLOCK_ID' => $catalog['IBLOCK_ID'], 'GET_BY_ID' => 'Y'));
                    }
                }
            }
            unset($offersId);
        }

        return $offers;
    }


    /**
     * Возвращает ID SKU с минимальной ценой
     * @param array $colors
     * @return int
     */
    protected function getMinPrice(array $colors): int
    {
       $minOffer = [];
       foreach($colors as $color) {
           if(isset($minOffer[$color->EL_ID])) {
                if($minOffer[$color->EL_ID]->PRICES > $color->PRICES) {
                    $minOffer[$color->EL_ID] = $color;
                }
           }
           else {
               $minOffer[$color->EL_ID] = $color;
           }
       }
       if(count($minOffer)) {
           return current($minOffer)->ID;
       }
    }

    /**
     * Возвращает ID SKU с минимальной ценой или SORT 
     * @param array $colors
     * @return int
     */
    protected function getMinPriceOrSort(array $colors): int
    {
       $minOffer = [];
       foreach($colors as $color) {
           if(isset($minOffer[$color->EL_ID])) {
                if($minOffer[$color->EL_ID]->SORT > $color->SORT) {
                    $minOffer[$color->EL_ID] = $color;
                } elseif ($minOffer[$color->EL_ID]->PRICES > $color->PRICES && $minOffer[$color->EL_ID]->SORT >= $color->SORT) {
                    $minOffer[$color->EL_ID] = $color;
                }
           }
           else {
               $minOffer[$color->EL_ID] = $color;
           }
       }
       if(count($minOffer)) {
           return current($minOffer)->ID;
       }
    }
}