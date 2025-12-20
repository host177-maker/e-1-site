<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use \Aspro\Max\Smartseo,
    \Aspro\Max\Smartseo\Admin\App\Controller,
    \Aspro\Max\Smartseo\Admin\Helper,
    \Aspro\Max\Smartseo\Admin\UI,
    \Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    \Bitrix\Main\Web\Json,
    \Bitrix\Main\Localization\Loc;

class SeoTextElementController extends Controller
{
    const ALIAS = 'SEO_TEXT_ELEMENT';
    const ALIAS_PROPERTY = 'SEO_TEXT_ELEMENT_PROPERTY';

    use BitrixCoreEntity;

    public function getViewFolderName()
    {
        return 'seo_text';
    }

    public function actionDetail($id = null)
    {
        $data = null;

        if ($id && (!$data = Smartseo\Models\SmartseoSeoTextTable::getRowById($id))) {
            throw new \Exception('Element not found');
        }

        $dataProperties = Smartseo\Models\SmartseoSeoTextTable::getElementProperties($id);

        $this->render('detail_element', [
            'data' => $data,
            'dataProperties' => $dataProperties,
            'alias' => self::ALIAS,
            'aliasProperty' => self::ALIAS_PROPERTY,
            'listSites' => $this->getListSites(),
            'listIblockTypes' => (
                $data['SITE_ID']
                    ? $this->getListIblockTypes($data['SITE_ID'])
                    : []
                ),
            'listIblocks' => (
                $data['SITE_ID'] && $data['IBLOCK_TYPE_ID']
                    ? $this->getListIblocks($data['SITE_ID'], $data['IBLOCK_TYPE_ID'])
                    : []
                ),
            'listIblockSections' => (
                $data['IBLOCK_ID']
                    ? $this->getListIblockSections($data['IBLOCK_ID'])
                    : []
                ),
        ]);
    }

    public function actionUpdate()
    {
        global $DB;

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

        $action = $this->request->get('action');
        $data = $this->request->get(self::ALIAS);
        $dataProperties = $this->request->get(self::ALIAS_PROPERTY);

        if($data['CONDITION']) {
            $data['CONDITION'] = $this->getPrepareConditionValue($data['IBLOCK_ID'], $data['CONDITION']);
        }

        if($action == 'update_confirm' && $data && $dataProperties) {
            $seotextEngine = new Smartseo\Engines\SeoTextEngine();
            $seotextEngine->updateElements($data['IBLOCK_ID'], $data['IBLOCK_SECTIONS'] ?: [], $data['CONDITION'], $dataProperties, $data['REWRITE'] === 'Y', true);

            echo Json::encode(array_filter([
                'result' => true,
                'fields' => [
                   'COUNT' => $seotextEngine->getResult('COUNT'),
                ],
                'action' => $action,
            ]));

            return;
        }

        $elementId = null;
        $resultMessage = '';
        try {
            $DB->StartTransaction();

            if ($this->request->get('save_in_table') === 'Y') {
                if (!$data['ID']) {
                    $elementId = $this->add($data);
                } else {
                    $elementId = $this->update($data['ID'], $data);
                }

                if ($elementId && $dataProperties) {
                    $this->updateProperties($elementId, $dataProperties);
                }
            }

            $seotextEngine = new Smartseo\Engines\SeoTextEngine();
            $seotextEngine->updateElements($data['IBLOCK_ID'], $data['IBLOCK_SECTIONS'] ?: [], $data['CONDITION'], $dataProperties, $data['REWRITE'] === 'Y');

            if ($seotextEngine->hasErrors()) {
                throw new \Exception(implode('<br>', $seotextEngine->getErrors()));
            }

            $resultMessage = $seotextEngine->getResult('MESSAGE');

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }


        $redirectUrl = '';
        if ($elementId && $this->request->get('action') == 'save') {
           $redirectUrl = Helper::url('seo_text/list');
        }

        echo Json::encode(array_filter([
            'result' => true,
            'message' => $resultMessage,
            'fields' => [
                'ID' => $elementId,
            ],
            'redirect' => $redirectUrl
        ]));
    }

    public function actionDelete()
    {
        if (!$this->validateAjaxAction()) {
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
            'message' => 'Seo text deleted successfully',
            'redirect' =>  Helper::url('seo_text/list')
        ]);
    }

    public function actionCopy()
    {
        if (!$this->validateAjaxAction()) {
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
            'message' => 'Seo text copy successfully'
        ]);
    }

    public function actionGetOptionIblockType()
    {
        if (!$siteId = $this->request->get('site_id')) {
            throw new \Exception('Site parameter expected');
        }

        $this->render('detail_section/partial/option_iblock_type', [
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

        $this->render('detail_section/partial/option_iblock', [
            'listIblocks' => $this->getListIblocks($siteId, $iblockTypeId),
        ]);
    }

    public function actionGetOptionIblockSections()
    {
        if (!$iblockId = $this->request->get('iblock_id')) {
            throw new \Exception('Iblock parameter expected');
        }

        $this->render('detail_section/partial/option_iblock_sections', [
            'listIblockSections' => $this->getListIblockSections($iblockId),
        ]);
    }

    public function actionGetMenuSeoProperty() {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!$controlId = $this->request->get('control')) {
            echo Json::encode([
                'result' => true,
                'menu' => [
                    ['TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA2')]
                ]
            ]);

            return;
        }

        $iblockId = $this->request->get('iblock_id');

        $dataConditionTree = null;
        if($_data = $this->request->get(self::ALIAS)) {
            if($_data['CONDITION']) {
               $dataConditionTree = $_data['CONDITION'];
            }
        }

        $propertyIds = [];
        if($dataConditionTree && $iblockId) {
            $conditionTreeResult = new Smartseo\Condition\ConditionResult($iblockId, $dataConditionTree, [
                new Smartseo\Condition\Controls\GroupBuildControls(),
                new Smartseo\Condition\Controls\IblockPropertyBuildControls($iblockId, [
                    'ONLY_PROPERTY_SMART_FILTER' => 'N',
                    'SHOW_PROPERTY_SKU' => 'N',
                  ])
            ]);

            $propertyIds = array_column($conditionTreeResult->getAllPropertyFields(), 'PROPERTY_ID');
        }

        $menuUiSeoProperty = new UI\SeoPropertyMenuUI();

        if($iblockId) {
            $menuUiSeoProperty->setIblockId($iblockId);
        }

        if($propertyIds) {
            $menuUiSeoProperty->setPropertyIds($propertyIds);
        }

        echo Json::encode([
            'result' => true,
            'menu' => $menuUiSeoProperty->getMenuItems($controlId, [
                UI\SeoPropertyMenuUI::CATEGORY_PROPERTIES,
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

        if ((!$iblockId = $this->request->get('iblock_id'))
            || (!$template = $this->request->get('template'))) {

            return;
        }

        $iblockSections = [];
        if($iblockSectionIds = $this->request->get('iblock_sections')) {
            $iblockSections = $this->getIblockSectionList([], [
                'ID' => $iblockSectionIds,
                'IBLOCK_ID' => $iblockId,
            ], [
                'ID',
                'CODE',
                'NAME',
                'DESCRIPTION',
            ], [], [
                'limit' => 10
            ]);
        }

        $dataConditionTree = null;
        if($_data = $this->request->get(self::ALIAS)) {
            if($_data['CONDITION']) {
               $dataConditionTree = $_data['CONDITION'];
            }
        }

        try {
            $element = new Smartseo\Template\Entity\SeoText(0);
            $element->setFields([
                'IBLOCK_ID' => $iblockId,
                'SECTIONS' => $iblockSections,
                'CONDITION' => $dataConditionTree,
            ]);

            echo \Bitrix\Main\Text\HtmlFilter::encode(
              \Bitrix\Iblock\Template\Engine::process($element, $template)
            );
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function actionGetMenuElementProperty() {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!$iblockId = (int) $this->request->get('iblock_id')) {
            echo Json::encode([
                'result' => true,
                'menu' => [
                    ['TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA2')]
                ]
            ]);

            return;
        }

        $ingorePropertyIds = [];
        if($this->request->get('property_ids')) {
            $ingorePropertyIds = $this->request->get('property_ids');
        }

        $menu = new UI\SeoTextPropertyMenuUI();
        $menu->setIblockId($iblockId);
        $menu->setElementPropertyIngore($ingorePropertyIds);
        $menu->setFunctionFormat("engineFormProperty.showMenu(%d);");

        echo Json::encode([
            'result' => true,
            'menu' => $menu->getMenuItems([
                UI\SeoTextPropertyMenuUI::CATEGORY_ELEMENT,
            ])
        ]);
    }

    public function actionGetElementPropertyControl()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if ((!$propertyId = (int) $this->request->get('property_id'))
            || (!$iblockId = $this->request->get('iblock_id'))) {

            return;
        }

        $property = Smartseo\Models\SmartseoSeoTextPropertyTable::getElementProperty($iblockId, $propertyId);

        $this->render('detail_element/partial/property_control', [
            'property' => $property,
        ]);
    }

     public function actionGetConditionControl()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!$iblockId = $this->request->get('iblock_id')) {
            return;
        }

        $seotextId = $this->request->get('seo_text');

        $condition = [];
        if($seotextId) {
            if (!$data = Smartseo\Models\SmartseoSeoTextTable::getRowById($seotextId)) {
                throw new \Exception('Element not found');
            }

            $condition = $data['CONDITION_TREE'];
        }

        $this->render('detail_element/partial/condition_control', [
            'alias' => self::ALIAS,
            'iblockId' => $iblockId,
            'condition' => $condition,
        ]);
    }

    private function add(array $data)
    {
        global $USER;

        $userId = $USER->GetID();

        $element = new \Aspro\Max\Smartseo\Models\EO_SmartseoSeoText();

        $element->setType(Smartseo\Models\SmartseoSeoTextTable::TYPE_ELEMENT);
        $element->setName($data['NAME']);
        $element->setSiteId($data['SITE_ID']);
        $element->setIblockTypeId($data['IBLOCK_TYPE_ID']);
        $element->setIblockId($data['IBLOCK_ID']);
        $element->setSort($data['SORT']);
        $element->setModifiedBy($userId);
        $element->setCreatedBy($userId);
        $element->setRewrite($data['REWRITE']);
        $element->setConditionTree($data['CONDITION']);

        $element->setDateCreate(new \Bitrix\Main\Type\DateTime());
        $element->setDateChange(new \Bitrix\Main\Type\DateTime());

        $result = $element->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $elementId = $result->getId();

        if ($data['IBLOCK_SECTIONS']) {
            foreach ($data['IBLOCK_SECTIONS'] as $sectionId) {
                $_iblockSection = new \Aspro\Max\Smartseo\Models\EO_SmartseoSeoTextIblockSections();
                $_iblockSection->setSectionId($sectionId);

                $element->addToIblockSections($_iblockSection);
            }

            $result = $element->save(true);

            if (!$result->isSuccess()) {
                throw new \Exception(implode('<br>', $result->getErrorMessages()));
            }
        }

        return $elementId;
    }

    private function update($id, array $data)
    {
        if (!Smartseo\Models\SmartseoSeoTextTable::getByPrimary($id)) {
            throw new \Exception('Seo text not found');
        }

        global $USER;

        $userId = $USER->GetID();

        $element = \Aspro\Max\Smartseo\Models\EO_SmartseoSeoText::wakeUp($id);

        $element->setType(Smartseo\Models\SmartseoSeoTextTable::TYPE_ELEMENT);
        $element->setName($data['NAME']);
        $element->setSiteId($data['SITE_ID']);
        $element->setIblockTypeId($data['IBLOCK_TYPE_ID']);
        $element->setIblockId($data['IBLOCK_ID']);
        $element->setSort($data['SORT']);
        $element->setModifiedBy($userId);
        $element->setRewrite($data['REWRITE']);
        $element->setDateChange(new \Bitrix\Main\Type\DateTime());
        $element->setConditionTree($data['CONDITION']);

        $result = $element->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $elementId = $result->getId();

        if ($data['IBLOCK_SECTIONS']) {
            $this->updateIblockSections($element, $data['IBLOCK_SECTIONS']);
        } else {
            $this->deleteIblockSections($element);
        }

        return $elementId;
    }

    private function delete($id)
    {
        if (!Smartseo\Models\SmartseoSeoTextTable::getByPrimary($id)) {
            $this->addError('Seo text not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoSeoTextTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return true;
    }

    private function copy($id)
    {
        return true;
    }

    private function updateProperties($seotextId, array $data)
    {
        Smartseo\Models\SmartseoSeoTextTable::updateElementProperties(
          $seotextId,
          $data
        );
    }

    private function updateIblockSections($element, $iblockSections)
    {
        $element->fillIblockSections();

        $currentIblockSections = [];

        foreach ($element->getIblockSections() as $_iblockSection) {
            $currentIblockSections[$_iblockSection->getId()] = $_iblockSection->collectValues();
        }

        $existSectionIds = array_column($currentIblockSections, 'SECTION_ID', 'ID');

        $iblockSectionIdsForDelete = array_keys(array_diff($existSectionIds, $iblockSections));

        foreach ($iblockSections as $sectionId) {
            if (in_array($sectionId, $existSectionIds)) {
                continue;
            }

            $_iblockSection = new \Aspro\Max\Smartseo\Models\EO_SmartseoSeoTextIblockSections();
            $_iblockSection->setSectionId($sectionId);

            $element->addToIblockSections($_iblockSection);
        }

        $result = $element->save(true);

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        if ($iblockSectionIdsForDelete) {
            foreach ($iblockSectionIdsForDelete as $_iblockSectionId) {
                $_iblockSection = \Aspro\Max\Smartseo\Models\EO_SmartseoSeoTextIblockSections::wakeUp($_iblockSectionId);

                if (!$_iblockSection) {
                    throw new \Exception(
                    "No IblockSection found for deletion, [ID: $_iblockSectionId]");
                }

                $result = $_iblockSection->delete();

                if (!$result->isSuccess()) {
                    throw new \Exception(implode('<br>', $result->getErrorMessages()));
                }
            }
        }
    }

    private function deleteIblockSections($element)
    {
         $element->fillIblockSections();

        foreach ($element->getIblockSections() as $_iblockSection) {
            $_iblockSection = \Aspro\Max\Smartseo\Models\EO_SmartseoSeoTextIblockSections::wakeUp($_iblockSection->getId());

            $result = $_iblockSection->delete();

            if (!$result->isSuccess()) {
                throw new \Exception(implode('<br>', $result->getErrorMessages()));
            }
        }
    }

    private function getPrepareConditionValue($iblockId, $condition)
    {
        $result = '';
        if ($condition) {
            $conditionTree = new Smartseo\Condition\ConditionTree();
            $conditionTree
              ->addControlBuild(new Smartseo\Condition\Controls\GroupBuildControls())
              ->addControlBuild(new Smartseo\Condition\Controls\IblockPropertyBuildControls(
                $iblockId, [
                  'ONLY_PROPERTY_SMART_FILTER' => 'N',
                  'SHOW_PROPERTY_SKU' => 'N',
                ]
            ));

            $conditionTree->init(BT_COND_MODE_PARSE, Smartseo\Condition\ConditionTree::BT_COND_BUILD_SMARTSEO, []);
            $_resultParse = $conditionTree->Parse($condition);
            $result = $_resultParse['CHILDREN'] ? serialize($_resultParse) : '';
        }

        return $result;
    }

    private function validateAjaxAction()
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

    private function getListSites()
    {
        return $this->getSiteList([
              'SORT' => 'ASC',
            ], [
              'ACTIVE' => 'Y'
        ]);
    }

    private function getListIblockTypes($siteId)
    {
        $rows = $this->getIblockSiteList([], [
            'SITE_ID' => $siteId,
          ], [
            'IBLOCK_ID',
            'SITE_ID',
            'REF_IBLOCK_TYPE_ID' => 'IBLOCK.IBLOCK_TYPE_ID'
        ]);

        $iblockTypeIds = array_column($rows, 'REF_IBLOCK_TYPE_ID');

        return $this->getIblockTypeList([], [
            'ID' => array_unique($iblockTypeIds),
            'LANG_MESSAGE.LANGUAGE_ID' => 'ru',
        ]);
    }

    private function getListIblocks($siteId, $iblockTypeId)
    {
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

        return $this->getIblockList([], [
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

}
