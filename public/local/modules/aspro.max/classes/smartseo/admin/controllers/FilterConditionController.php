<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use
    Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Models\SmartseoFilterConditionTable,
    Aspro\Max\Smartseo\Models\SmartseoFilterRuleTable,
    Aspro\Max\Smartseo\Entity,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\UI,
    Aspro\Max\Smartseo\Condition,
    Aspro\Max\Smartseo\Admin\Grids as GridSmartseo,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterConditionController extends Controller
{

    const ALIAS_CONDITION = 'FILTER_CONDITION';
    const ALIAS_SEO = 'FILTER_CONDITION_SEO';
    const ALIAS_SITEMAP = 'FILTER_CONDITION_SITEMAP';


    public function getViewFolderName()
    {
        return 'filter_condition';
    }

    public function actionDetail($id = null, $filter_rule_id)
    {
        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filter_rule_id)) {
            throw new \Exception('Parent element not found');
        }

        $dataCondition = null;
        if ($id && (!$dataCondition = Smartseo\Models\SmartseoFilterConditionTable::getRowById($id))) {
            throw new \Exception('Condition not found');
        }

        $dataSeo = $this->getDataSeo($filterRule['ID'], $dataCondition ? $dataCondition['ID'] : null);
        $dataSitemap = $this->getDataSitemap($dataCondition ? $dataCondition['ID'] : null);

        $this->render('detail', [
            'filterRuleId' => $filterRule['ID'],
            'filterRuleIblockId' => $filterRule['IBLOCK_ID'],
            'aliasCondition' => self::ALIAS_CONDITION,
            'aliasSeo' => self::ALIAS_SEO,
            'aliasSitemap' => self::ALIAS_SITEMAP,
            'dataCondition' => $dataCondition,
            'dataSeo' => $dataSeo,
            'dataSitemap' => $dataSitemap,
            'gridConditionId' => GridSmartseo\FilterRuleConditionGrid::getInstance($filterRule['ID'])->getGridId(),
            'gridUrlId' => GridSmartseo\FilterRuleUrlGrid::getInstance($filterRule['ID'])->getGridId(),
            'listTypeGenerate' => Smartseo\Models\SmartseoFilterConditionTable::getTypeGenerateList(),
            'listSitemap' => $this->getListSitemap($filterRule['SITE_ID']),
            'listChangefreq' => Smartseo\Models\SmartseoFilterSitemapTable::getChangefreqParams(),
            'listPriority' => Smartseo\Models\SmartseoFilterSitemapTable::getPriorityParams(),
            'defaultChangefreq' => Smartseo\Models\SmartseoFilterSitemapTable::getMap()['CHANGEFREQ']['default_value'],
            'defaultPriority' => Smartseo\Models\SmartseoFilterSitemapTable::getMap()['PRIORITY']['default_value'],
            'isCatalogModule' => $this->isCatalogModule()
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

        if (!$this->request->get(self::ALIAS_CONDITION) || !is_array($this->request->get(self::ALIAS_CONDITION))) {
            echo Json::encode([
                'result' => false,
                'message' => 'No update data found'
            ]);
            return;
        }

        $data = $this->request->get(self::ALIAS_CONDITION);
        $dataSeo = $this->request->get(self::ALIAS_SEO);
        $dataSitemap = $this->request->get(self::ALIAS_SITEMAP);

        $resultGenerated = null;
        $needGenerateUrl = $this->request->get('generate') === 'Y';

        if (!$data['FILTER_RULE_ID'] || !$data['FILTER_RULE_IBLOCK_ID']) {
            throw new \Exception('Parent element params not found');
        }

        global $DB;

        try {
            $DB->StartTransaction();

            if (!$data['ID']) {
                $filterConditionId = $this->addFilterCondition($data);
            } else {
                $filterConditionId = $this->updateFilterCondition($data['ID'], $data);
            }

            if ($this->hasErrors()) {
                throw new \Exception(implode('<br>', $this->getErrors()));
            }

            if ($filterConditionId) {
                $this->updateSeoTemplates($filterConditionId, $dataSeo ?: []);
                $this->updateSitemap($filterConditionId, $dataSitemap);
            }

            if($needGenerateUrl) {
                $resultGenerated = $this->createGeneratedUrls($filterConditionId);
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $filterCondition = Entity\FilterCondition::wakeUp($filterConditionId);
        $filterCondition->fill(['NAME']);

        echo Json::encode([
            'result' => true,
            'fields' => [
               'ID' => $filterCondition->getId(),
               'NAME' => $filterCondition->getName(),
               'COUNT_CREATED_LINKS' => $resultGenerated ? $resultGenerated['COUNT'] : 0,
            ],
            'action' => $this->request->get('action'),
            'actionGenerate' => $needGenerateUrl,
            'message' => 'Element saved successfully',
        ]);
    }

    public function actionGetMenuSeoProperty()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
            || (!$controlId = $this->request->get('control'))) {
            echo Json::encode([
                'result' => true,
                'menu' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA2')
                    ]
                ]
            ]);

            return;
        }

        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        $dataCondition = null;
        if ($this->request->get(self::ALIAS_CONDITION) && is_array($this->request->get(self::ALIAS_CONDITION))) {
            $dataCondition = $this->request->get(self::ALIAS_CONDITION);
        }

        $propertyIds = [];
        if($dataCondition['CONDITION']) {
            $conditionTreeResult = new \Aspro\Max\Smartseo\Condition\ConditionResult($filterRule['IBLOCK_ID'], $dataCondition['CONDITION']);
            $propertyIds = array_column($conditionTreeResult->getAllPropertyFields(), 'PROPERTY_ID');
        }

        $menuUiSeoProperty = new UI\SeoPropertyMenuUI();
        $menuUiSeoProperty
            ->setIblockId($filterRule['IBLOCK_ID'])
            ->setPropertyIds($propertyIds);

        echo Json::encode([
            'result' => true,
            'menu' => $menuUiSeoProperty->getMenuItems($controlId)
        ]);
    }

    public function actionGetSampleSeoProperty()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
            || (!$template = $this->request->get('template'))
            || (!$dataCondition = $this->request->get(self::ALIAS_CONDITION))) {

            return;
        }

        if(!is_array($dataCondition) || !$dataCondition['CONDITION']) {
            return;
        }

        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filterRuleId)) {
            throw new \Exception('Element element not found');
        }

        try {
            $element = new Smartseo\Template\Entity\FilterRuleCondition(0);
            $element->setFields([
                'FILTER_RULE_ID' => $filterRule['ID'],
                'IBLOCK_ID' => $filterRule['IBLOCK_ID'],
                'CONDITION' => $dataCondition['CONDITION'],
            ]);
            echo \Bitrix\Main\Text\HtmlFilter::encode(
              \Bitrix\Iblock\Template\Engine::process($element, $template)
            );
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function actionGetMenuUrlProperty()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
          || (!$controlId = $this->request->get('control'))) {
            echo Json::encode([
                'result' => true,
                'menu' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA2')
                    ]
                ]
            ]);

            return;
        }

        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        $menuUiUrlProperty = new UI\UrlPropertyMenuUI();
        $menuUiUrlProperty
            ->setSiteId($filterRule['SITE_ID'])
            ->setIblockId($filterRule['IBLOCK_ID']);

        echo Json::encode([
            'result' => true,
            'menu' => $menuUiUrlProperty->getMenuItems($controlId)
        ]);
    }

    public function actionDeactivate()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->deactivate($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Condition deactivated successfully'
        ]);
    }

    public function actionActivate()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->activate($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Condition activated successfully'
        ]);
    }

    public function actionDelete()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->delete($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Condition deleted successfully'
        ]);
    }

    public function actionCopy()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->copy($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Condition copy successfully'
        ]);
    }

    public function actionCloseUrlIndexing()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->closeUrlIndexing($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Successfully url indexing closed'
        ]);
    }

    public function actionOpenUrlIndexing()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->openUrlIndexing($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Successfully url indexing opened'
        ]);
    }

    public function actionActivateUrlStrictCompliance()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->activateUrlStrictCompliance($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Successfully url strict compliance activated'
        ]);
    }

    public function actionDeactivateUrlStrictCompliance()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->deactivateUrlStrictCompliance($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Successfully url strict compliance deactivated'
        ]);
    }

    public function actionGenerateUrls()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        $filterCondition = SmartseoFilterConditionTable::getRowById($id);

        if(!$filterCondition) {
            throw new \Exception('Element condition not found');
        }

        global $DB;

        try {
            $DB->StartTransaction();

            $result = $this->createGeneratedUrls($filterCondition['ID']);

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        echo Json::encode([
            'result' => true,
            'fields' => [
               'ID' => $filterCondition['ID'],
               'NAME' =>  $filterCondition['NAME'],
               'COUNT_CREATED_LINKS' => $result ? $result['COUNT'] : 0,
            ],
            'message' => 'Generate urls successfully'
        ]);
    }

    protected function validateParamsForMenuAction()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!check_bitrix_sessid()) {
            $this->addError('Bitrix session not found');

            return false;
        }

        if (!$this->request->get('id')) {
            $this->addError('ID param expected');

            return false;
        }

        return true;
    }

    private function deactivate($id)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            $this->addError('Condition not found');

            return false;
        }

        $filterCondition = Entity\FilterCondition::wakeUp($id);
        $filterCondition->setActive('N');

        $result = $filterCondition->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function activate($id)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            $this->addError('Condition not found');

            return false;
        }

        $filterCondition = Entity\FilterCondition::wakeUp($id);
        $filterCondition->setActive('Y');

        $result = $filterCondition->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function delete($id)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            $this->addError('Condition not found');

            return false;
        }

        $result = SmartseoFilterConditionTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());
        }

        return true;
    }

    private function copy($id)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            $this->addError('Condition not found');

            return false;
        }

        $replication = new Smartseo\Admin\Actions\Replication();

        if(!$replication->copyFilterRuleCondition($id) || $replication->hasErrors()) {
            throw new \Exception($replication->getErrors());
        }

        return true;
    }

    private function closeUrlIndexing($id)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            $this->addError('Condition not found');

            return false;
        }

        $filterCondition = Entity\FilterCondition::wakeUp($id);
        $filterCondition->setUrlCloseIndexing('Y');

        $result = $filterCondition->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function openUrlIndexing($id)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            $this->addError('Condition not found');

            return false;
        }

        $filterCondition = Entity\FilterCondition::wakeUp($id);
        $filterCondition->setUrlCloseIndexing('N');

        $result = $filterCondition->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function activateUrlStrictCompliance($id)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            $this->addError('Condition not found');

            return false;
        }

        $filterCondition = Entity\FilterCondition::wakeUp($id);
        $filterCondition->setUrlStrictCompliance('Y');

        $result = $filterCondition->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function deactivateUrlStrictCompliance($id)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            $this->addError('Condition not found');

            return false;
        }

        $filterCondition = Entity\FilterCondition::wakeUp($id);
        $filterCondition->setUrlStrictCompliance('N');

        $result = $filterCondition->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function addFilterCondition(array $data)
    {
        if (!SmartseoFilterRuleTable::getByPrimary($data['FILTER_RULE_ID'])) {
            throw new \Exception('Parent element not found');
        }

        $filterCondition = new Entity\FilterCondition;
        $filterCondition->setFilterRuleId($data['FILTER_RULE_ID']);
        $filterCondition->setActive($data['ACTIVE']);
        $filterCondition->setName($data['NAME']);
        $filterCondition->setUrlCloseIndexing($data['URL_CLOSE_INDEXING']);
        $filterCondition->setUrlStrictCompliance($data['URL_STRICT_COMPLIANCE']);
        $filterCondition->setUrlTemplate($data['URL_TEMPLATE']);
        $filterCondition->setUrlTypeGenerate($data['URL_TYPE_GENERATE']);
        $filterCondition->setSort($data['SORT']);

        $condition = '';
        if ($data['CONDITION']) {
            $conditionTree = new Condition\ConditionTree();
            $conditionTree
              ->addControlBuild(new Condition\Controls\GroupBuildControls())
              ->addControlBuild(new Condition\Controls\IblockPropertyBuildControls(
                    $data['FILTER_RULE_IBLOCK_ID']
                ));

            if($this->isCatalogModule()) {
                $conditionTree->addControlBuild(new Condition\Controls\CatalogGroupBuildControls());
            }

            $conditionTree->init(BT_COND_MODE_PARSE, Condition\ConditionTree::BT_COND_BUILD_SMARTSEO, []);
            $_resultParse = $conditionTree->Parse($data['CONDITION']);
            $condition = $_resultParse['CHILDREN'] ? serialize($_resultParse) : '';
        }

        $filterCondition->setConditionTree($condition);

        $result = $filterCondition->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());
        }

        return $result->getId();
    }

    private function updateFilterCondition($id, array $data)
    {
        if (!SmartseoFilterConditionTable::getByPrimary($id)) {
            throw new \Exception('Element not found');
        }

        if (!SmartseoFilterRuleTable::getByPrimary($data['FILTER_RULE_ID'])) {
            throw new \Exception('Parent element not found');
        }

        $filterCondition = Entity\FilterCondition::wakeUp($id);
        $filterCondition->setActive($data['ACTIVE']);
        $filterCondition->setName($data['NAME']);
        $filterCondition->setUrlCloseIndexing($data['URL_CLOSE_INDEXING']);
        $filterCondition->setUrlStrictCompliance($data['URL_STRICT_COMPLIANCE']);
        $filterCondition->setUrlTemplate($data['URL_TEMPLATE']);
        $filterCondition->setUrlTypeGenerate($data['URL_TYPE_GENERATE']);
        $filterCondition->setDateChange(new \Bitrix\Main\Type\DateTime());
        $filterCondition->setSort($data['SORT']);

        $condition = '';
        if ($data['CONDITION']) {
            $conditionTree = new Condition\ConditionTree();
            $conditionTree
              ->addControlBuild(new Condition\Controls\GroupBuildControls())
              ->addControlBuild(new Condition\Controls\IblockPropertyBuildControls(
                    $data['FILTER_RULE_IBLOCK_ID']
                ));

            if($this->isCatalogModule()) {
                $conditionTree->addControlBuild(new Condition\Controls\CatalogGroupBuildControls());
            }

            $conditionTree->init(BT_COND_MODE_PARSE, Condition\ConditionTree::BT_COND_BUILD_SMARTSEO, []);
            $_resultParse = $conditionTree->Parse($data['CONDITION']);
            $condition = $_resultParse['CHILDREN'] ? serialize($_resultParse) : '';
        }

        $filterCondition->setConditionTree($condition);

        $result = $filterCondition->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());
        }

        return $result->getId();
    }

    private function updateSeoTemplates($filterConditionId, array $data)
    {
        Smartseo\Models\SmartseoSeoTemplateTable::updateSeoTemplates(
          $filterConditionId,
          Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_CONDITION,
          $data
        );
    }

    private function updateSitemap($filterConditionId, array $data)
    {
        $sitemap = Smartseo\Models\SmartseoFilterSitemapTable::getRow([
            'select' => [
                'ID',
            ],
            'filter' => [
                '=FILTER_CONDITION_ID' => $filterConditionId,
            ]
        ]);

        if($data['SITEMAP_ID'] == 0 && $sitemap) {
            Smartseo\Models\SmartseoFilterSitemapTable::delete($sitemap['ID']);
        } elseif($data['SITEMAP_ID'] == 0) {
            return;
        }

        if($sitemap) {
            $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSitemap::wakeUp($sitemap['ID']);
        } else {
            $entity = new \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSitemap();
        }

        $entity->setFilterConditionId($filterConditionId);
        $entity->setSitemapId($data['SITEMAP_ID']);
        $entity->setChangefreq($data['CHANGEFREQ']);
        $entity->setPriority($data['PRIORITY']);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    private function getDataSeo($filterRuleId, $filterConditionId = null)
    {
        $seoTemplates = Smartseo\Models\SmartseoSeoTemplateTable::getDataSeoTemplates([
            Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_RULE => $filterRuleId,
            Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_CONDITION => $filterConditionId,
        ]);

        if(!$seoTemplates) {
            return [];
        }

        if(!$filterConditionId) {
            $element = new Smartseo\Template\Entity\FilterRule($filterRuleId);
        } else {
            $element = new Smartseo\Template\Entity\FilterRuleCondition($filterConditionId);
        }

        $result = [];
        foreach ($seoTemplates as $property) {
            $result[$property['CODE']] = $property;
            $result[$property['CODE']]['SAMPLE'] = \Bitrix\Main\Text\HtmlFilter::encode(
              \Bitrix\Iblock\Template\Engine::process($element, $property['TEMPLATE'])
            );
        }

        return $result;
    }

    private function getDataSitemap($filterConditionId)
    {
        if(!$filterConditionId) {
            return [];
        }

        return Smartseo\Models\SmartseoFilterSitemapTable::getRow([
            'select' => [
                'SITEMAP_ID',
                'CHANGEFREQ',
                'PRIORITY',
            ],
            'filter' => [
                'FILTER_CONDITION_ID' => $filterConditionId,
            ]
        ]);
    }

    private function createGeneratedUrls($filterConditionId)
    {
        $urlEngine = new Smartseo\Engines\UrlEngine($filterConditionId);

        if (!$urlEngine->update()) {
            if($urlEngine->hasErrors()) {
                throw new \Exception(implode('<br>', $urlEngine->getErrors()));
            }

            return false;
        }

        return $urlEngine->getResult();
    }

    private function getListSitemap($siteId = null)
    {
         $rows = Smartseo\Models\SmartseoSitemapTable::getList([
             'select' => [
                 'ID',
                 'NAME',
                 'SITE_ID',
             ],
             'filter' => array_filter([
               'SITE_ID' => $siteId
             ]),
             'order' => [
                 'ID' => 'ASC',
             ]
         ])->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['ID']] = $row['NAME'] ? $row['NAME'] . ' (' . $row['SITE_ID'] . ')' : '';
        }

        return $result;
    }
}
