<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use
    Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Grids as GridSmartseo,
    Aspro\Max\Smartseo\Admin\Traits,
    Aspro\Max\Smartseo\Admin\UI,
    Aspro\Max\Smartseo\Entity,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterSearchController extends Controller
{
    use Traits\BitrixCoreEntity;

    const ALIAS = 'FILTER_SEARCH';

    public function getViewFolderName()
    {
        return 'filter_search';
    }

    public function actionDetail($id = null, $filter_rule_id)
    {
        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filter_rule_id)) {
            throw new \Exception('Parent element not found');
        }

        $data = null;
        if ($id && (!$data = Smartseo\Models\SmartseoFilterSearchTable::getRowById($id))) {
            throw new \Exception('Search not found');
        }

        $dataSample = null;
        if($data) {
            $dataSample = $this->getDataSample($data);
        }

        $this->render('detail', [
            'filterRuleId' => $filterRule['ID'],
            'alias' => self::ALIAS,
            'data' => $data,
            'dataSample' => $dataSample,
            'listFilterCondition' => $this->getListFilterCondition($filterRule['ID'], $data['FILTER_CONDITION_ID']),
            'gridId' => GridSmartseo\FilterRuleSearchGrid::getInstance($filterRule['ID'])->getGridId(),
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

        if (!$this->request->get(self::ALIAS) || !is_array($this->request->get(self::ALIAS))) {
            echo Json::encode([
                'result' => false,
                'message' => 'No update data found'
            ]);
            return;
        }

        $data = $this->request->get(self::ALIAS);

        global $DB;

        try {
            $DB->StartTransaction();

            if (!$data['ID']) {
                $id = $this->add($data);
            } else {
                $id = $this->update($data['ID'], $data);
            }

            if ($this->hasErrors()) {
                throw new \Exception(implode('<br>', $this->getErrors()));
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSearch::wakeUp($id);
        $entity->fill(['FILTER_CONDITION', 'FILTER_CONDITION_ID']);

        $reindexResult = null;
        $needReindex = $this->request->get('reindex') === 'Y';

        if($needReindex) {
            $reindexResult = $this->searchReindex($entity->getFilterConditionId());
        }

        echo Json::encode([
            'result' => true,
            'fields' => [
                'ID' => $id,
                'NAME' => $entity->getFilterCondition()->getName()
                    ? '[' . $entity->getFilterConditionId() . '] ' . $entity->getFilterCondition()->getName()
                    : '',
                'FILTER_CONDITION_ID' => $entity->getFilterConditionId(),
                'COUNT_SEARCH_INDEX' => $reindexResult ? $reindexResult['COUNT'] : 0,
            ],
            'action' => $this->request->get('action'),
            'actionReindex' => $needReindex,
            'message' => 'Search saved successfully',
        ]);
    }

    public function actionGetMenuSeoProperty()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
            || (!$controlId = $this->request->get('control'))
            || (!$filterConditionId = $this->request->get('filter_condition'))) {
            echo Json::encode([
                'result' => true,
                'menu' => [
                    ['TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA2')]
                ]
            ]);

            return;
        }

        if (!Smartseo\Models\SmartseoFilterRuleTable::getByPrimary($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        $filterRule = Entity\FilterRule::wakeUp($filterRuleId);

        $filterRule->fill(['IBLOCK_ID', 'IBLOCK_SECTIONS']);

        $iblockId = $filterRule->getIblockId();

        $filterCondition = Smartseo\Models\SmartseoFilterConditionTable::getRowById($filterConditionId);

        $propertyIds = [];
        if($filterCondition['CONDITION_TREE']) {
            $conditionTreeResult = new \Aspro\Max\Smartseo\Condition\ConditionResult($iblockId, $filterCondition['CONDITION_TREE']);
            $propertyIds = array_column($conditionTreeResult->getAllPropertyFields(), 'PROPERTY_ID');
        }

        $menuUiSeoProperty = new UI\SeoPropertyMenuUI();
        $menuUiSeoProperty
            ->setIblockId($iblockId)
            ->setPropertyIds($propertyIds ?: [0]);

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
            || (!$filterConditionId = $this->request->get('filter_condition'))) {

            return;
        }

         if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filterRuleId)) {
            throw new \Exception('Element element not found');
        }

        $filterCondition = Smartseo\Models\SmartseoFilterConditionTable::getRowById($filterConditionId);

        if(!$filterCondition['CONDITION_TREE']) {
            return;
        }

        try {
            $element = new Smartseo\Template\Entity\FilterRuleCondition(0);
            $element->setFields([
                'FILTER_RULE_ID' => $filterRule['ID'],
                'IBLOCK_ID' => $filterRule['IBLOCK_ID'],
                'CONDITION' => $filterCondition['CONDITION_TREE'],
            ]);
            echo \Bitrix\Main\Text\HtmlFilter::encode(
              \Bitrix\Iblock\Template\Engine::process($element, $template)
            );
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
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
            'message' => 'Search deactivated successfully'
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
            'message' => 'Search activated successfully'
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
            'message' => 'Search deleted successfully'
        ]);
    }

    public function actionReindex()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!Smartseo\Models\SmartseoFilterSearchTable::getByPrimary($id)) {
            throw new \Exception('Element not found');
        }

        $filterSearch = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSearch::wakeUp($id);
        $filterSearch->fill(['FILTER_CONDITION', 'FILTER_CONDITION_ID']);

        $reindexResult = $this->searchReindex($filterSearch->getFilterConditionId());

        echo Json::encode([
            'result' => true,
            'fields' => [
                'ID' => $id,
                'NAME' => $filterSearch->getFilterCondition()->getName()
                    ? '[' . $filterSearch->getFilterConditionId() . '] ' . $filterSearch->getFilterCondition()->getName()
                    : '',
                'FILTER_CONDITION_ID' => $filterSearch->getFilterConditionId(),
                'COUNT_SEARCH_INDEX' => $reindexResult ? $reindexResult['COUNT'] : 0,
            ],
            'message' => 'Search reindex successfully',
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

    private function getDataSample($data)
    {
        $result = [];

        $element = new Smartseo\Template\Entity\FilterRuleCondition($data['FILTER_CONDITION_ID']);

        $result['TITLE_TEMPLATE'] = \Bitrix\Main\Text\HtmlFilter::encode(
            \Bitrix\Iblock\Template\Engine::process($element, $data['TITLE_TEMPLATE'])
        );

        $result['BODY_TEMPLATE'] = \Bitrix\Main\Text\HtmlFilter::encode(
            \Bitrix\Iblock\Template\Engine::process($element, $data['BODY_TEMPLATE'])
        );

        return $result;
    }

    private function getListFilterCondition($filterRuleId, $currentFilterConditionId = null)
    {
         $existRows = Smartseo\Models\SmartseoFilterSearchTable::getList([
              'select' => [
                  'FILTER_CONDITION_ID',
              ],
              'filter' => array_filter([
                  'FILTER_CONDITION.FILTER_RULE.ID' => $filterRuleId,
                  '!FILTER_CONDITION_ID' => $currentFilterConditionId,
              ])
        ])->fetchAll();

        $existingIds = array_column($existRows, 'FILTER_CONDITION_ID');

        $rows = Smartseo\Models\SmartseoFilterConditionTable::getList([
              'select' => [
                  'ID',
                  'NAME',
              ],
              'filter' => [
                  'FILTER_RULE.ID' => $filterRuleId,
                  '!ID' => $existingIds,
              ],
              'order' => [
                  'SORT' => 'ASC',
                  'ID' => 'ASC',
              ]
          ])->fetchAll();

        return array_column($rows, 'NAME', 'ID');
    }

    private function deactivate($id)
    {
        if (!Smartseo\Models\SmartseoFilterSearchTable::getByPrimary($id)) {
            $this->addError('Search not found');

            return false;
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSearch::wakeUp($id);
        $entity->setActive('N');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function activate($id)
    {
        if (!Smartseo\Models\SmartseoFilterSearchTable::getByPrimary($id)) {
            $this->addError('Search not found');

            return false;
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSearch::wakeUp($id);
        $entity->setActive('Y');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function delete($id)
    {
        if (!Smartseo\Models\SmartseoFilterSearchTable::getByPrimary($id)) {
            $this->addError('Search not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoFilterSearchTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());
        }

        return true;
    }

    private function update($id, array $data)
    {
        if (!Smartseo\Models\SmartseoFilterSearchTable::getByPrimary($id)) {
            throw new \Exception('Element not found');
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSearch::wakeUp($id);
        $entity->setActive($data['ACTIVE']);
        $entity->setFilterConditionId($data['FILTER_CONDITION_ID']);
        $entity->setTitleTemplate($data['TITLE_TEMPLATE']);
        $entity->setBodyTemplate($data['BODY_TEMPLATE']);
        $entity->setStatus(Smartseo\Models\SmartseoFilterSearchTable::STATUS_NOT_INDEXED);
        $entity->setDateChange(new \Bitrix\Main\Type\DateTime());

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }

    private function add(array $data)
    {
        $entity = new \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSearch();
        $entity->setActive($data['ACTIVE']);
        $entity->setFilterConditionId($data['FILTER_CONDITION_ID']);
        $entity->setTitleTemplate($data['TITLE_TEMPLATE']);
        $entity->setBodyTemplate($data['BODY_TEMPLATE']);
        $entity->setStatus(Smartseo\Models\SmartseoFilterSearchTable::STATUS_NOT_INDEXED);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }

    private function searchReindex($filterFilterConditionId)
    {
        if(!$filterFilterConditionId) {
            return;
        }

        $searchEngine = new Smartseo\Engines\SearchEngine();

        $searchEngine->reindexByFilterCondition($filterFilterConditionId);

        return $searchEngine->getResult();
    }

}
