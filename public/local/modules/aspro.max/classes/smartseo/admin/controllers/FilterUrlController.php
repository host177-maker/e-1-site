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

class FilterUrlController extends Controller
{
    use Traits\BitrixCoreEntity;

    const ALIAS = 'FILTER_URL';
    const ALIAS_SEO = 'FILTER_URL_SEO';

    public function getViewFolderName()
    {
        return 'filter_url';
    }

    public function actionDetail($id = null, $filter_rule_id)
    {
        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filter_rule_id)) {
            throw new \Exception('Parent element not found');
        }

        $dataUrl = null;
        if ($id && (!$dataUrl = $this->getDataUrl($id))) {
            throw new \Exception('Url not found');
        }

        $dataSeo = $this->getDataUrlSeo($dataUrl['ID'], $dataUrl['FILTER_CONDITION_ID'], $filterRule['ID']);

        $this->render('detail', [
            'aliasUrl' => self::ALIAS,
            'aliasSeo' => self::ALIAS_SEO,
            'dataUrl' => $dataUrl,
            'dataSeo' => $dataSeo,
            'filterRuleId' => $filterRule['ID'],
            'gridId' => GridSmartseo\FilterRuleUrlGrid::getInstance($filterRule['ID'])->getGridId(),
        ]);
    }

    public function actionGetMenuSeoProperty()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$filterRuleId = $this->request->get('filter_rule'))
            || (!$controlId = $this->request->get('control'))
            || (!$filterUrlId = $this->request->get('filter_url'))) {
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

        if (!Smartseo\Models\SmartseoFilterRuleTable::getByPrimary($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        if (!Smartseo\Models\SmartseoFilterConditionUrlTable::getByPrimary($filterUrlId)) {
            throw new \Exception('Url not found');
        }

        $filterRule = Entity\FilterRule::wakeUp($filterRuleId);
        $filterRule->fill(['IBLOCK_ID']);

        $filterUrl = Entity\FilterConditionUrl::wakeUp($filterUrlId);
        $properties = unserialize($filterUrl->fill(['PROPERTIES']), ['allowed_classes' => false]);

        $menuUiSeoProperty = new UI\SeoPropertyMenuUI();
        $menuUiSeoProperty
            ->setIblockId($filterRule->getIblockId())
            ->setPropertyIds(array_column($properties, 'PROPERTY_ID'));

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
            || (!$filterUrlId = $this->request->get('filter_url'))) {
            return;
        }

        if (!Smartseo\Models\SmartseoFilterRuleTable::getByPrimary($filterRuleId)) {
            throw new \Exception('Element not found');
        }

        if (!Smartseo\Models\SmartseoFilterConditionUrlTable::getByPrimary($filterUrlId)) {
            throw new \Exception('Url not found');
        }

        try {
            $element = new Smartseo\Template\Entity\FilterRuleUrl($filterUrlId);
            echo \Bitrix\Main\Text\HtmlFilter::encode(
              \Bitrix\Iblock\Template\Engine::process($element, $template)
            );
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
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
        $dataSeo = $this->request->get(self::ALIAS_SEO);

        global $DB;

        try {
            $DB->StartTransaction();

            if ($data['ID']) {
                $id = $this->update($data['ID'], $data);
                $this->updateSeoTemplates($id, $dataSeo ?: []);
            }

            if ($this->hasErrors()) {
                throw new \Exception(implode('<br>', $this->getErrors()));
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $filterUrl = $this->getDataUrl($id);

        echo Json::encode([
            'result' => true,
            'fields' => [
                'ID' => $id,
                'NAME' => $filterUrl['NAME'],
            ],
            'action' => $this->request->get('action'),
            'message' => 'Url saved successfully',
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
            'message' => 'Url deactivated successfully'
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
            'message' => 'Url activated successfully'
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
            'message' => 'Url deleted successfully'
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
        if (!Smartseo\Models\SmartseoFilterConditionUrlTable::getByPrimary($id)) {
            $this->addError('Url not found');

            return false;
        }

        $entity = Entity\FilterConditionUrl::wakeUp($id);
        $entity->setActive('N');
        $entity->setStateModified('Y');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function activate($id)
    {
        if (!Smartseo\Models\SmartseoFilterConditionUrlTable::getByPrimary($id)) {
            $this->addError('Url not found');

            return false;
        }

        $entity = Entity\FilterConditionUrl::wakeUp($id);
        $entity->setActive('Y');
        $entity->setStateModified('Y');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function delete($id)
    {
        if (!Smartseo\Models\SmartseoFilterConditionUrlTable::getByPrimary($id)) {
            $this->addError('Url not found');

            return false;
        }

        $entity = Entity\FilterConditionUrl::wakeUp($id);
        $entity->setStateDeleted('Y');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return true;
    }

    private function update($id, array $data)
    {
        if (!Smartseo\Models\SmartseoFilterConditionUrlTable::getByPrimary($id)) {
            throw new \Exception('Element not found');
        }

        $entity = Entity\FilterConditionUrl::wakeUp($id);
        $entity->setActive($data['ACTIVE']);
        $entity->setStateModified('Y');
        $entity->setNewUrl($data['NEW_URL']);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }

    private function updateSeoTemplates($filterUrlId, array $data)
    {
        Smartseo\Models\SmartseoSeoTemplateTable::updateSeoTemplates(
          $filterUrlId,
          Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_URL,
          $data
        );
    }

    private function getDataUrl($filterUrlId)
    {
        $data = Smartseo\Models\SmartseoFilterConditionUrlTable::getRow([
            'select' => [
                '*',
                'SECTION_NAME' => 'SECTION.NAME'
            ],
            'filter' => [
                '=ID' => $filterUrlId,
            ]
        ]);

        if(!$data) {
            return null;
        }

        $data['PROPERTIES'] = unserialize($data['PROPERTIES'], ['allowed_classes' => false]);

        $seoTemplates = $this->getDataUrlSeo($data['ID'], $data['FILTER_CONDITION_ID']);

        if ($seoTemplates['PAGE_TITLE']['TEMPLATE']) {
            $element = new \Aspro\Max\Smartseo\Template\Entity\FilterRuleUrl($data['ID']);
            $data['NAME'] = \Bitrix\Main\Text\HtmlFilter::encode(
                \Bitrix\Iblock\Template\Engine::process($element, $seoTemplates['PAGE_TITLE']['TEMPLATE'])
            );
        } else {
            $propertyDisplayValues = [];
            foreach ($data['PROPERTIES'] as $property) {
                $propertyDisplayValues[] = implode(', ', $property['VALUES']['DISPLAY']);
            }

            $data['NAME'] = $data['SECTION_NAME'] . ' - ' . implode(', ', $propertyDisplayValues);
        }

        $data['NAME'] = htmlspecialchars($data['NAME']);

        return $data;
    }

    private function getDataUrlSeo($filterUrlId, $filterConditionId = null, $filterRuleId = null)
    {
        $seoTemplates = Smartseo\Models\SmartseoSeoTemplateTable::getDataSeoTemplates([
            Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_RULE => $filterRuleId,
            Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_CONDITION => $filterConditionId,
            Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_URL => $filterUrlId,
        ]);

        if(!$seoTemplates) {
            return [];
        }

        $element = new Smartseo\Template\Entity\FilterRuleUrl($filterUrlId);

        $result = [];
        foreach ($seoTemplates as $property) {
            $result[$property['CODE']] = $property;
            $result[$property['CODE']]['SAMPLE'] =  \Bitrix\Main\Text\HtmlFilter::encode(
              \Bitrix\Iblock\Template\Engine::process($element, $property['TEMPLATE'])
            );
        }

        return $result;
    }
}
