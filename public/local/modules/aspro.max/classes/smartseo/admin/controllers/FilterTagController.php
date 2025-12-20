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

class FilterTagController extends Controller
{
    use Traits\BitrixCoreEntity;

    const ALIAS = 'FILTER_TAG';

    public function getViewFolderName()
    {
        return 'filter_tag';
    }

    public function actionDetail($id = null, $filter_rule_id)
    {
        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filter_rule_id)) {
            throw new \Exception('Parent element not found');
        }

        $data = null;
        if ($id && (!$data = Smartseo\Models\SmartseoFilterTagTable::getRowById($id))) {
            throw new \Exception('Tag not found');
        }

        $this->render('detail', [
            'alias' => self::ALIAS,
            'data' => $data,
            'filterRuleId' => $filterRule['ID'],
            'listTypes' => Smartseo\Models\SmartseoFilterTagTable::getTypeParams(),
            'listFilterCondition' => $this->getListFilterCondition($filterRule['ID']),
            'listIblockSections' => $filterRule['IBLOCK_ID']
              ? $this->getListIblockSections($filterRule['IBLOCK_ID'])
              : [],
            'listTags' => $this->getTagsByFilterCondition($data['FILTER_CONDITION_ID'], $data['TEMPLATE']),
            'listIdenticalProperty' => $this->getListIdenticalProperty($filterRule['IBLOCK_ID'], $data['PARENT_FILTER_CONDITION_ID'], $data['FILTER_CONDITION_ID']),
            'gridId' => GridSmartseo\FilterRuleTagGrid::getInstance($filter_rule_id)->getGridId(),
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

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterTag::wakeUp($id);
        $entity->fill(['FILTER_CONDITION', 'FILTER_CONDITION_ID']);

        echo Json::encode([
            'result' => true,
            'fields' => [
                'ID' => $id,
                'NAME' => $entity->getFilterCondition()->getName()
                    ? '[' . $entity->getFilterConditionId() . '] ' . $entity->getFilterCondition()->getName()
                    : '',
                'FILTER_CONDITION_ID' => $entity->getFilterConditionId(),
            ],
            'action' => $this->request->get('action'),
            'message' => 'Tag saved successfully',
        ]);
    }

    public function actionGetMenuTagProperty()
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

    public function actionHtmlSampleTagProperty()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
            || (!$template = $this->request->get('template'))
            || (!$filterConditionId = $this->request->get('filter_condition'))) {

            return;
        }

        if (!Smartseo\Models\SmartseoFilterRuleTable::getByPrimary($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        try {
            $this->render('_sample_tags', [
                'listTags' => $this->getTagsByFilterCondition($filterConditionId, $template),
            ]);

        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function actionHtmlIdenticalProperty()
    {
         if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
            || (!$parentFilterConditionId = $this->request->get('parent_filter_condition'))
            || (!$filterConditionId = (int)$this->request->get('filter_condition'))) {

            $this->render('_checkbox_related_property', [
                'alias' => self::ALIAS,
                'dataRelatedProperty' => [],
                'listIdenticalProperty' => [],
            ]);

            return;
        }

        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filterRuleId)) {
            throw new \Exception('Parent element not found');
        }

        $filterTagId = $this->request->get('tag');
        $relatedProperties = [];

        if ($filterTagId) {
            $row = Smartseo\Models\SmartseoFilterTagTable::getRow([
                  'select' => [
                      'RELATED_PROPERTY',
                  ],
                  'filter' => [
                      'ID' => $filterTagId
                  ]
            ]);

            if ($row['RELATED_PROPERTY']) {
                $relatedProperties = unserialize($row['RELATED_PROPERTY'], ['allowed_classes' => false]);
            }
        }

        try {
            $this->render('_checkbox_related_property', [
                'alias' => self::ALIAS,
                'dataRelatedProperty' => $relatedProperties,
                'listIdenticalProperty' => $this->getListIdenticalProperty($filterRule['IBLOCK_ID'], $parentFilterConditionId, $filterConditionId),
            ]);

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
            'message' => 'Tag deactivated successfully'
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
            'message' => 'Tag activated successfully'
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
            'message' => 'Tag deleted successfully'
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

    private function getListFilterCondition($filterRuleId)
    {
        $rows = Smartseo\Models\SmartseoFilterConditionTable::getList([
              'select' => [
                  'ID',
                  'NAME',
              ],
              'filter' => [
                  'FILTER_RULE.ID' => $filterRuleId,
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
        if (!Smartseo\Models\SmartseoFilterTagTable::getByPrimary($id)) {
            $this->addError('Tag not found');

            return false;
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterTag::wakeUp($id);
        $entity->setActive('N');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
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

    private function getListIdenticalProperty($iblockId, $parentFilterConditionId, $filterConditionId)
    {
        if(!$parentFilterConditionId || !$filterConditionId) {
            return [];
        }

        $rows = Smartseo\Models\SmartseoFilterConditionTable::getList([
            'select' => [
                'ID',
                'CONDITION_TREE',

            ],
            'filter' => [
                'ID' => [
                    $parentFilterConditionId,
                    $filterConditionId
                ],
            ]
        ])->fetchAll();

        $filterConditions = array_column($rows, 'CONDITION_TREE', 'ID');

        $parentConditionTreeResult = new \Aspro\Max\Smartseo\Condition\ConditionResult($iblockId, $filterConditions[$parentFilterConditionId]);
        $conditionTreeResult = new \Aspro\Max\Smartseo\Condition\ConditionResult($iblockId, $filterConditions[$filterConditionId]);

        $parentProperties = array_column($parentConditionTreeResult->getAllPropertyFields(), 'PROPERTY_NAME', 'PROPERTY_ID');
        $properties = array_column($conditionTreeResult->getAllPropertyFields(), 'PROPERTY_NAME', 'PROPERTY_ID');

        return array_intersect_assoc($parentProperties, $properties);

    }

    private function activate($id)
    {
        if (!Smartseo\Models\SmartseoFilterTagTable::getByPrimary($id)) {
            $this->addError('Tag not found');

            return false;
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterTag::wakeUp($id);
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
        if (!Smartseo\Models\SmartseoFilterTagTable::getByPrimary($id)) {
            $this->addError('Tag not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoFilterTagTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());
        }

        return true;
    }

    private function update($id, array $data)
    {
        if (!Smartseo\Models\SmartseoFilterTagTable::getByPrimary($id)) {
            throw new \Exception('Element not found');
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterTag::wakeUp($id);
        $entity->setActive($data['ACTIVE']);
        $entity->setType($data['TYPE']);
        $entity->setFilterConditionId($data['FILTER_CONDITION_ID']);
        $entity->setTemplate($data['TEMPLATE']);
        $entity->setParentFilterConditionId($data['PARENT_FILTER_CONDITION_ID']);
        $entity->setSectionId($data['SECTION_ID']);
        $entity->setRelatedProperty($data['RELATED_PROPERTY'] ? serialize($data['RELATED_PROPERTY']) : null);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }

    private function add(array $data)
    {
        $entity = new \Aspro\Max\Smartseo\Models\EO_SmartseoFilterTag();
        $entity->setActive($data['ACTIVE']);
        $entity->setType($data['TYPE']);
        $entity->setFilterConditionId($data['FILTER_CONDITION_ID']);
        $entity->setTemplate($data['TEMPLATE']);
        $entity->setParentFilterConditionId($data['PARENT_FILTER_CONDITION_ID']);
        $entity->setSectionId($data['SECTION_ID']);
        $entity->setRelatedProperty($data['RELATED_PROPERTY'] ? serialize($data['RELATED_PROPERTY']) : null);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }

    private function getTagsByFilterCondition($filterConditionId, $template, $limit = 3)
    {
        if(!$filterConditionId || !$template) {
            return [];
        }

        $filterConditionUrls = Smartseo\Models\SmartseoFilterConditionUrlTable::getList([
              'select' => [
                  'ID',
              ],
              'filter' => [
                  'FILTER_CONDITION_ID' => $filterConditionId,
              ],
              'limit' => $limit
          ])->fetchAll();


        $tags = null;
        foreach ($filterConditionUrls as $url) {
            $element = new \Aspro\Max\Smartseo\Template\Entity\FilterRuleUrl($url['ID']);

            $tags[] = \Bitrix\Main\Text\HtmlFilter::encode(
                \Bitrix\Iblock\Template\Engine::process($element, $template)
            );
        }

        return $tags;
    }

}
