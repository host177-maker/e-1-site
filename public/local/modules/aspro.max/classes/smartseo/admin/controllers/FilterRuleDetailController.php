<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Models\SmartseoFilterRuleTable,
    Aspro\Max\Smartseo\Models\SmartseoSeoTemplateTable,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Traits\FilterChainSectionTree,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Aspro\Max\Smartseo\Admin\Helper,
    Aspro\Max\Smartseo\Entity\FilterRule,
    Aspro\Max\Smartseo\Entity\FilterIblockSection,
    Aspro\Max\Smartseo\Entity\SeoTemplate,
    Aspro\Max\Smartseo\Entity\SeoTemplates,
    Aspro\Max\Smartseo\Admin\Settings,
    Aspro\Max\Smartseo\Template,
    Aspro\Max\Smartseo\Admin\UI,
    Aspro\Max\Smartseo\Admin\Grids as GridSmartseo,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

class FilterRuleDetailController extends Controller
{

    const ALIAS_FILTER_RULE = 'FILTER_RULE';
    const ALIAS_SEO_FILTER_RULE = 'SEO_FILTER_RULE';

    use FilterChainSectionTree;

    use BitrixCoreEntity;

    public function getViewFolderName()
    {
        return 'filter_rule_detail';
    }

    public function actionDetail($id = null, $parent_section_id = null, $active_tab = null)
    {
        $dataFilterRule = null;
        if ($id && (!$dataFilterRule = SmartseoFilterRuleTable::getRowById($id))) {
            throw new \Exception('Element not found');
        }

        if($this->listenGridActions($dataFilterRule)) {
            return;
        }

        $dataSeoFilterRule = null;
        if ($dataFilterRule) {
            $dataSeoFilterRule = $this->getDataSeo($dataFilterRule['ID']);
        }

        $this->render('detail', [
            'activeTab' => $active_tab,
            'aliasFilterRule' => self::ALIAS_FILTER_RULE,
            'aliasSeoFilterRule' => self::ALIAS_SEO_FILTER_RULE,
            'dataFilterRule' => $dataFilterRule,
            'dataSeoFilterRule' => $dataSeoFilterRule,
            'parentSectionId' => $dataFilterRule['SECTION_ID']
              ?: $parent_section_id,
            'chainSections' => $this->getChainSections($dataFilterRule['SECTION_ID']
              ?: $parent_section_id),
            'listSites' => $this->getSiteList([
                'SORT' => 'ASC',
            ], [
                'ACTIVE' => 'Y'
            ]),
            'listIblockTypes' => ($dataFilterRule['SITE_ID']
              ? $this->getListIblockTypes($dataFilterRule['SITE_ID'])
              : []),
            'listIblocks' => ($dataFilterRule['SITE_ID'] && $dataFilterRule['IBLOCK_TYPE_ID']
              ? $this->getListIblocks($dataFilterRule['SITE_ID'], $dataFilterRule['IBLOCK_TYPE_ID'])
              : []),
            'listIblockSections' => ($dataFilterRule['IBLOCK_ID']
              ? $this->getListIblockSections($dataFilterRule['IBLOCK_ID'])
              : []),
            'listSections' => $this->getListSections(),
            'gridConditions' => GridSmartseo\FilterRuleConditionGrid::getInstance($dataFilterRule['ID'])->getComponentParams(),
            'gridUrls' => GridSmartseo\FilterRuleUrlGrid::getInstance($dataFilterRule['ID'])->getComponentParams(),
            'gridSitemap' => GridSmartseo\FilterRuleSitemapGrid::getInstance($dataFilterRule['ID'])->getComponentParams(),
            'gridTags' => GridSmartseo\FilterRuleTagGrid::getInstance($dataFilterRule['ID'])->getComponentParams(),
            'gridSearch' => GridSmartseo\FilterRuleSearchGrid::getInstance($dataFilterRule['ID'])->getComponentParams(),
        ]);
    }

    public function actionGetOptionIblockType()
    {
        if (!$siteId = $this->request->get('site_id')) {
            throw new \Exception('Site parameter expected');
        }

        $this->render('partial/option_iblock_type', [
            'listIblockTypes' => $this->getListIblockTypes($siteId),
        ]);
    }

    public function actionGetOptionIblock()
    {
        if (!$siteId = $this->request->get('site_id')) {
            throw new \Exception('Site parameter expected');
        }

        if (!$iblockTypeId = $this->request->get('iblock_type_id')) {
            throw new \Exception('IblockType parameter expected');
        }

        $this->render('partial/option_iblock', [
            'listIblocks' => $this->getListIblocks($siteId, $iblockTypeId),
        ]);
    }

    public function actionGetOptionIblockSections()
    {
        if (!$iblockId = $this->request->get('iblock_id')) {
            throw new \Exception('Iblock parameter expected');
        }

        $this->render('partial/option_iblock_sections', [
            'listIblockSections' => $this->getListIblockSections($iblockId),
        ]);
    }

    public function actionUpdate()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if (!$this->request->get(self::ALIAS_FILTER_RULE) || !is_array($this->request->get(self::ALIAS_FILTER_RULE))) {
            echo Json::encode([
                'result' => false,
                'message' => 'No update data found'
            ]);
            return;
        }

        $data = $this->request->get(self::ALIAS_FILTER_RULE);
        $dataSeoProperty = $this->request->get(self::ALIAS_SEO_FILTER_RULE);

        global $DB;

        try {
            $DB->StartTransaction();

            if (!$data['ID']) {
                $filterRuleId = $this->addFilterRule($data);
            } else {
                $filterRuleId = $this->updateFilterRule($data['ID'], $data);
            }

            if($filterRuleId && $dataSeoProperty) {
                $this->updateSeoTemplates($filterRuleId, $dataSeoProperty);
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $redirectUrl = '';
        if ($this->request->get('action') == 'apply') {
            $redirectUrl = Helper::url('filter_rule_detail/detail', [
                  'id' => $filterRuleId,
                  'active_tab' => $this->request->get('active_tab'),
            ]);
        } else {
            $redirectUrl = Helper::url('filter_rules/list', [
                  'section_id' => $data['SECTION_ID'],
            ]);
        }

        echo Json::encode(array_filter([
            'result' => true,
            'message' => 'Element saved successfully',
            'redirect' => $redirectUrl,
        ]));
    }

    public function actionDelete()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if(!$filterRuleId = $this->request->get('id')) {
            echo Json::encode([
                'result' => false,
                'message' => 'Element ID expected'
            ]);

            return;
        }

        if (!$filterRule = SmartseoFilterRuleTable::getRowById($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        global $DB;

        try {
            $DB->StartTransaction();

            $this->deleteFilterRule($filterRule['ID']);

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        echo Json::encode(array_filter([
            'result' => true,
            'massage' => 'Element deleted successfully',
            'redirect' => Helper::url('filter_rules/list', [
                'section_id' => $filterRule['SECTION_ID'],
            ]),
        ]));
    }

    public function actionCopy()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if(!$filterRuleId = $this->request->get('id')) {
            echo Json::encode([
                'result' => false,
                'message' => 'Element ID expected'
            ]);

            return;
        }

        if (!$filterRule = SmartseoFilterRuleTable::getRowById($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        global $DB;

        try {
            $DB->StartTransaction();

            $this->copyFilterRule($filterRule['ID']);

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        echo Json::encode(array_filter([
            'result' => true,
            'massage' => 'Element copy successfully',
        ]));
    }

    public function actionGetValueName()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!$this->request->get('site_id') || !$this->request->get('iblock_id') || !$this->request->get('iblock_sections')) {
            echo Json::encode([
                'result' => false,
                'message' => 'No update data found'
            ]);
            return;
        }

        $iblockId = $this->request->get('iblock_id');
        $iblockSectionIds = $this->request->get('iblock_sections');

        if (!is_array($iblockSectionIds)) {
            echo Json::encode([
                'result' => false,
                'message' => 'IblockSections array expected'
            ]);
            return;
        }

        try {
            $setting = Settings\SettingSmartseo::getInstance();

            $element = new Template\Entity\FilterRule(0);

            $maxFilterRuleId = SmartseoFilterRuleTable::getMaxID();

            $fields = [
                'ID' => $maxFilterRuleId + 1,
                'IBLOCK_ID' => $iblockId,
                'SECTIONS' => $this->getIblockSectionList([
                    'ID',
                    'CODE',
                    'NAME',
                    'DESCRIPTION',
                ], [
                    'ID' => $iblockSectionIds,
                ])
            ];

            $element->setFields($fields);
            $value = \Bitrix\Main\Text\HtmlFilter::encode(
                \Bitrix\Iblock\Template\Engine::process($element, $setting->getFilterRuleNameTemplate())
            );

            echo Json::encode([
                'result' => true,
                'value' => $value
            ]);
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function actionGetMenuSeoProperty() {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
          || (!$controlId = $this->request->get('control'))) {
            echo Json::encode([
                'result' => false,
                'menu' => [
                    ['TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA2')]
                ]
            ]);

            return;
        }

        if (!SmartseoFilterRuleTable::getByPrimary($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        $filterRule = FilterRule::wakeUp($filterRuleId);
        $filterRule->fill(['IBLOCK_ID', 'IBLOCK_SECTIONS']);

        $iblockId = $filterRule->getIblockId();
        $sectionIds = $filterRule->getIblockSections()->getSectionIdList();

        $menuUiSeoProperty = new UI\SeoPropertyMenuUI();
        $menuUiSeoProperty->setIblockId($iblockId)->setSectionIds($sectionIds);

        echo Json::encode([
            'result' => true,
            'menu' => $menuUiSeoProperty->getMenuItems($controlId, [
                UI\SeoPropertyMenuUI::CATEGORY_IBLOCK,
                UI\SeoPropertyMenuUI::CATEGORY_SECTION,
                UI\SeoPropertyMenuUI::CATEGORY_PARENT,
                UI\SeoPropertyMenuUI::CATEGORY_FUNCTIONS,
            ])
        ]);
    }

    public function actionGetSampleSeoProperty()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
            || (!$template = $this->request->get('template'))) {

            return;
        }

        try {
            $element = new Template\Entity\FilterRule($filterRuleId);
            echo \Bitrix\Main\Text\HtmlFilter::encode(
              \Bitrix\Iblock\Template\Engine::process($element, $template)
            );
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function getDataSeo($filterRuleId)
    {
        $seoTemplates = Smartseo\Models\SmartseoSeoTemplateTable::getDataSeoTemplates([
            Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_RULE => $filterRuleId,
        ]);

        if(!$seoTemplates) {
            return [];
        }

        $element = new Smartseo\Template\Entity\FilterRule($filterRuleId);

        $result = [];
        foreach ($seoTemplates as $property) {
            $result[$property['CODE']] = $property;
            $result[$property['CODE']]['SAMPLE'] = \Bitrix\Main\Text\HtmlFilter::encode(
              \Bitrix\Iblock\Template\Engine::process($element, $property['TEMPLATE'])
            );
        }

        return $result;
    }

    private function getListSections()
    {
        $treeSections = $this->getTreeSections();

        return $this->getRowsByTreeSections($treeSections);
    }

    private function getListIblockTypes($siteId)
    {
        $setting = Settings\SettingSmartseo::getInstance();

        $iblockTypeIds = [];

        $rows = $this->getIblockSiteList([
          ], [
            'SITE_ID' => $siteId,
          ], [
            'IBLOCK_ID',
            'SITE_ID',
            'REF_IBLOCK_TYPE_ID' => 'IBLOCK.IBLOCK_TYPE_ID'
        ]);

        $iblockTypeIds = array_column($rows, 'REF_IBLOCK_TYPE_ID');


        if ($setting->isOnlyCatalog() && $this->isCatalogModule) {
            $rows = $this->getCatalogIblockList([
              ], [
                'PRODUCT_IBLOCK_ID' => 0
              ], [
                'IBLOCK_ID',
                'REF_IBLOCK_TYPE_ID' => 'IBLOCK.IBLOCK_TYPE_ID'
            ]);

            $iblockTypeIds = array_intersect(array_column($rows, 'REF_IBLOCK_TYPE_ID'), $iblockTypeIds);
        }

        return $this->getIblockTypeList([
            ], [
              'ID' => array_unique($iblockTypeIds),
              'LANG_MESSAGE.LANGUAGE_ID' => 'ru',
        ]);
    }

    private function getListIblocks($siteId, $iblockTypeId)
    {
        $setting = Settings\SettingSmartseo::getInstance();

        $rows = $this->getIblockSiteList([
          ], [
            'SITE_ID' => $siteId,
            'REF_IBLOCK_TYPE_ID' => $iblockTypeId,
          ], [
            'IBLOCK_ID',
            'SITE_ID',
            'REF_IBLOCK_TYPE_ID' => 'IBLOCK.IBLOCK_TYPE_ID'
        ]);

        $iblockIds = array_column($rows, 'IBLOCK_ID');

        if ($setting->isOnlyCatalog() && $this->isCatalogModule) {
            $rows = $this->getCatalogIblockList([
              ], [
                'PRODUCT_IBLOCK_ID' => 0,
                'REF_IBLOCK_TYPE_ID' => $iblockTypeId,
              ], [
                'IBLOCK_ID',
                'REF_IBLOCK_TYPE_ID' => 'IBLOCK.IBLOCK_TYPE_ID'
            ]);

            $iblockIds = array_intersect(array_column($rows, 'IBLOCK_ID'), $iblockIds);
        }

        return $this->getIblockList([
            ], [
              'ID' => $iblockIds
            ], [
              'ID',
              'CODE',
              'NAME'
            ]
        );
    }

    private function getListIblockSections($iblockId)
    {
        return $this->getIblockSectionList([
              'LEFT_MARGIN' => 'ASC',
              'SORT' => 'DESC',
              'NAME' => 'ASC'
            ], [
              '=IBLOCK_ID' => $iblockId,
        ]);
    }

    private function addFilterRule(array $data)
    {
        global $USER;
        $userId = $USER->GetID();

        $newFilterRule = new FilterRule();
        $newFilterRule->setActive($data['ACTIVE']);
        $newFilterRule->setName($data['NAME']);
        $newFilterRule->setSectionId($data['SECTION_ID']);
        $newFilterRule->setUrlCloseIndexing($data['URL_CLOSE_INDEXING']);
        $newFilterRule->setUrlStrictCompliance($data['URL_STRICT_COMPLIANCE']);
        $newFilterRule->setSiteId($data['SITE_ID']);
        $newFilterRule->setIblockTypeId($data['IBLOCK_TYPE_ID']);
        if ($data['IBLOCK_ID']) {
            $newFilterRule->setIblockId($data['IBLOCK_ID']);
        }

        $newFilterRule->setIblockIncludeSubsections($data['IBLOCK_INCLUDE_SUBSECTIONS']);

        $newFilterRule->setSort($data['SORT']);

        $newFilterRule->setModifiedBy($userId);
        $newFilterRule->setCreatedBy($userId);

        $result = $newFilterRule->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $newFilterRuleId = $result->getId();

        if ($data['IBLOCK_SECTIONS']) {
            foreach ($data['IBLOCK_SECTIONS'] as $sectionId) {
                $_iblockSection = new FilterIblockSection();
                $_iblockSection->setFilterRuleId($newFilterRuleId);
                $_iblockSection->setSectionId($sectionId);

                $newFilterRule->addToIblockSections($_iblockSection);
            }

            $result = $newFilterRule->save(true);

            if (!$result->isSuccess()) {
                throw new \Exception(implode('<br>', $result->getErrorMessages()));
            }
        }

        return $newFilterRuleId;
    }

    private function updateFilterRule($id, array $data)
    {
        if (!SmartseoFilterRuleTable::getByPrimary($id)) {
            throw new \Exception('Element not found');
        }

        global $USER;
        $userId = $USER->GetID();

        $filterRule = FilterRule::wakeUp($id);
        $filterRule->setActive($data['ACTIVE']);
        $filterRule->setName($data['NAME']);
        $filterRule->setSectionId($data['SECTION_ID']);
        $filterRule->setUrlCloseIndexing($data['URL_CLOSE_INDEXING']);
        $filterRule->setUrlStrictCompliance($data['URL_STRICT_COMPLIANCE']);
        $filterRule->setSiteId($data['SITE_ID']);
        $filterRule->setIblockTypeId($data['IBLOCK_TYPE_ID']);
        $filterRule->setDateChange(new \Bitrix\Main\Type\DateTime());

        if ($data['IBLOCK_ID']) {
            $filterRule->setIblockId($data['IBLOCK_ID']);
        }

        $filterRule->setIblockIncludeSubsections($data['IBLOCK_INCLUDE_SUBSECTIONS']);

        $filterRule->setSort($data['SORT']);

        $filterRule->setModifiedBy($userId);

        $result = $filterRule->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $filterRuleId = $result->getId();

        if ($data['IBLOCK_SECTIONS']) {
            $filterRule->fillIblockSections();

            $currentIblockSections = [];

            foreach ($filterRule->getIblockSections() as $_iblockSection) {
                $currentIblockSections[$_iblockSection->getId()] = $_iblockSection->collectValues();
            }

            $existSectionIds = array_column($currentIblockSections, 'SECTION_ID', 'ID');

            $iblockSectionIdsForDelete = array_keys(array_diff($existSectionIds, $data['IBLOCK_SECTIONS']));

            foreach ($data['IBLOCK_SECTIONS'] as $sectionId) {
                if (in_array($sectionId, $existSectionIds)) {
                    continue;
                }

                $_iblockSection = new FilterIblockSection();
                $_iblockSection->setFilterRuleId($filterRuleId);
                $_iblockSection->setSectionId($sectionId);

                $filterRule->addToIblockSections($_iblockSection);
            }

            $result = $filterRule->save(true);

            if (!$result->isSuccess()) {
                throw new \Exception(implode('<br>', $result->getErrorMessages()));
            }

            if ($iblockSectionIdsForDelete) {
                foreach ($iblockSectionIdsForDelete as $_iblockSectionId) {
                    $_iblockSection = FilterIblockSection::wakeUp($_iblockSectionId);

                    if (!$_iblockSection) {
                        throw new \Exception(
                        "No FilterIblockSection found for deletion, [ID: $_iblockSectionId]");
                    }

                    $result = $_iblockSection->delete();

                    if (!$result->isSuccess()) {
                        throw new \Exception(implode('<br>', $result->getErrorMessages()));
                    }
                }
            }
        }

        return $filterRuleId;
    }

    private function deleteFilterRule($id)
    {
        $result = SmartseoFilterRuleTable::delete($id);

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    private function copyFilterRule($id)
    {
        return false;
    }

    private function addSeoTemplates($filterRuleId, array $data)
    {
        $seoTemplates = new SeoTemplates();

        foreach ($data as $code => $value) {
            if(!$value) {
                continue;
            }

            $newSeoTemplate = new SeoTemplate();

            $newSeoTemplate->setEntityId($filterRuleId);
            $newSeoTemplate->setEntityType('FR');
            $newSeoTemplate->setCode($code);
            $newSeoTemplate->setTemplate($value);

            $seoTemplates[] = $newSeoTemplate;
        }

        $result = $seoTemplates->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    private function updateSeoTemplates($filterRuleId, array $data)
    {
        $seoTemplates = SmartseoSeoTemplateTable::getList([
            'filter' => [
              'ENTITY_ID' => $filterRuleId,
              '=ENTITY_TYPE' => 'FR',
            ]
          ])->fetchCollection();

        foreach ($seoTemplates as $seoTemplate) {
            $_code = $seoTemplate->getCode();

            if(isset($data[$_code])) {
                $seoTemplate->setTemplate($data[$_code]);
                unset($data[$_code]);
            }
        }

        if($data) {
            $this->addSeoTemplates($filterRuleId, $data);
        }

        $result = $seoTemplates->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    private function listenGridActions($dataFilterRule = [])
    {
        if ($this->request->get('grid_action') && $this->request->get('grid_id')) {
            $gridId = $this->request['grid_id'];
            $gridAction = $this->request->get('grid_action');
            $gridSort = [];

            if($this->request->get('by') && $this->request->get('order')) {
               $gridSort[$this->request->get('by')] = $this->request->get('order');
            }

            $currentPage = false;
            if($gridAction == 'pagination') {
                $currentPage = 1;
                if($this->request->get($gridId)) {
                    $currentPage = (int)preg_replace('/page-/', '', $this->request->get($gridId));
                }
            }

            $result = [];
            switch ($gridId) {
                case 'grid_filter_rule_conditions':
                    $grid = new GridSmartseo\FilterRuleConditionGrid($dataFilterRule['ID']);
                    $grid->addSort($gridSort);
                    $grid->addFilter();
                    $grid->setCurrentPage($currentPage);

                    $result = [
                        'gridConditions' => $grid->getComponentParams(true),
                    ];

                    break;

                 case 'grid_filter_rule_urls':
                    $grid = new GridSmartseo\FilterRuleUrlGrid($dataFilterRule['ID']);
                    $grid->addSort($gridSort);
                    $grid->addFilter();
                    $grid->setCurrentPage($currentPage);

                    $result = [
                        'gridUrls' => $grid->getComponentParams(true),
                    ];

                     break;
                 case 'grid_filter_rule_sitemap':
                    $grid = new GridSmartseo\FilterRuleSitemapGrid($dataFilterRule['ID']);
                    $grid->addSort($gridSort);
                    $grid->addFilter();
                    $grid->setCurrentPage($currentPage);

                    $result = [
                        'gridSitemap' => $grid->getComponentParams(true),
                    ];

                    break;
                case 'grid_filter_rule_tags':
                    $grid = new GridSmartseo\FilterRuleTagGrid($dataFilterRule['ID']);
                    $grid->addSort($gridSort);
                    $grid->addFilter();
                    $grid->setCurrentPage($currentPage);

                    $result = [
                        'gridTags' => $grid->getComponentParams(true),
                    ];

                    break;

                 case 'grid_filter_rule_search':
                    $grid = new GridSmartseo\FilterRuleSearchGrid($dataFilterRule['ID']);
                    $grid->addSort($gridSort);
                    $grid->addFilter();
                    $grid->setCurrentPage($currentPage);

                    $result = [
                        'gridSearch' => $grid->getComponentParams(true),
                    ];

                    break;
                default:
                    break;
            }

            $this->render('grids/' . $gridId, $result);

            return true;
        }

        return false;
    }

}
