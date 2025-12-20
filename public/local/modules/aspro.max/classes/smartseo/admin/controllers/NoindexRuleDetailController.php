<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Helper,
    Aspro\Max\Smartseo\Admin\Grids as GridSmartseo,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

class NoindexRuleDetailController extends Controller
{
    use BitrixCoreEntity;

    const ALIAS = 'NOINDEX_RULE';

    public function getViewFolderName()
    {
        return 'noindex_rule';
    }

    public function actionDetail($id = null)
    {
        $data = null;

        if ($id && (!$data = Smartseo\Models\SmartseoNoindexRuleTable::getRowById($id))) {
            throw new \Exception('Element not found');
        }

        if($this->listenGridActions($data)) {
            return;
        }

        $this->render('detail', [
            'alias' => self::ALIAS,
            'data' => $data,
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
            'listConditionTypes' => Smartseo\Models\SmartseoNoindexConditionTable::getTypeParams(),
            'gridCondition' => GridSmartseo\NoindexRuleConditionGrid::getInstance($data['ID'])->getComponentParams(),
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

        $data = $this->request->get(self::ALIAS);

        $elementId = null;

        try {
            $DB->StartTransaction();

            if (!$data['ID']) {
                $elementId = $this->add($data);
            } else {
                $elementId = $this->update($data['ID'], $data);
            }

            $this->generateUrls($elementId);

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $redirectUrl = '';
        if ($this->request->get('action') == 'save') {
           $redirectUrl = Helper::url('noindex_rules/list');
        } else {
           $redirectUrl = Helper::url('noindex_rule_detail/detail', [
               'id' => $elementId,
           ]);
        }

        echo Json::encode(array_filter([
            'result' => true,
            'message' => '',
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
            'message' => 'Noindex rule deleted successfully',
            'redirect' =>  Helper::url('noindex_rules/list')
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

        if (!$elementId = $this->copy($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Noindex rule copy successfully',
            'redirect' =>  Helper::url('noindex_rule_detail/detail', [
                'id' => $elementId,
            ])
        ]);
    }

    public function actionGetOptionIblockType()
    {
        if (!$siteId = $this->request->get('site_id')) {
            throw new \Exception('Site parameter expected');
        }

        $this->render('detail/partial/option_iblock_type', [
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

        $this->render('detail/partial/option_iblock', [
            'listIblocks' => $this->getListIblocks($siteId, $iblockTypeId),
        ]);
    }

    public function actionGetOptionIblockSections()
    {
        if (!$iblockId = $this->request->get('iblock_id')) {
            throw new \Exception('Iblock parameter expected');
        }

        $this->render('detail/partial/option_iblock_sections', [
            'listIblockSections' => $this->getListIblockSections($iblockId),
        ]);
    }

    public function actionGetValueUrlTemplate()
    {
        if (!$siteId = $this->request->get('site_id')) {
            throw new \Exception('Site parameter expected');
        }

        $iblockId = null;
        if ($this->request->get('iblock_id')) {
            $iblockId = (int)$this->request->get('iblock_id');
        }

        $iblockSections = null;
        if ($this->request->get('iblock_sections')) {
            $iblockSections = $this->request->get('iblock_sections');
        }

        $iblockSectionAll = null;
        if ($this->request->get('iblock_section_all')) {
            $iblockSectionAll = $this->request->get('iblock_section_all') === 'true';
        }

        $iblock = null;

        if($iblockId) {
            $iblock = $this->getIblockRow([
                'ID' => $iblockId
            ], [
                'SECTION_PAGE_URL'
            ]);
        }

        if (($iblockSections || $iblockSectionAll) && $iblock && $iblock['SECTION_PAGE_URL']) {
            echo Json::encode([
                'result' => true,
                'value' => $iblock['SECTION_PAGE_URL'],
            ]);

            return;
        }

        if($iblock) {
            echo Json::encode([
                'result' => true,
                'value' => $iblock['SECTION_PAGE_URL'],
            ]);

            return;
        }

        echo Json::encode([
            'result' => true,
            'value' => '',
        ]);
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
        $setting = Smartseo\Admin\Settings\SettingSmartseo::getInstance();

        $rows = $this->getIblockSiteList([], [
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

        return $this->getIblockTypeList([], [
            'ID' => array_unique($iblockTypeIds),
            'LANG_MESSAGE.LANGUAGE_ID' => 'ru',
        ]);
    }

    private function getListIblocks($siteId, $iblockTypeId)
    {
        $setting = Smartseo\Admin\Settings\SettingSmartseo::getInstance();

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

    private function add(array $data)
    {
        global $USER;

        $userId = $USER->GetID();

        $element = new \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexRule();

        $element->setActive($data['ACTIVE']);
        $element->setName($data['NAME']);
        $element->setSiteId($data['SITE_ID']);
        $element->setIblockTypeId($data['IBLOCK_TYPE_ID']);
        $element->setIblockId($data['IBLOCK_ID']);
        $element->setSort($data['SORT']);
        $element->setModifiedBy($userId);
        $element->setCreatedBy($userId);
        $element->setIblockSectionAll($data['IBLOCK_SECTION_ALL']);
        $element->setUrlTemplate($data['URL_TEMPLATE']);

        $element->setDateCreate(new \Bitrix\Main\Type\DateTime());
        $element->setDateChange(new \Bitrix\Main\Type\DateTime());

        $result = $element->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $elementId = $result->getId();

        if ($data['IBLOCK_SECTIONS'] && !$data['IBLOCK_SECTION_ALL']) {
            foreach ($data['IBLOCK_SECTIONS'] as $sectionId) {
                $_iblockSection = new \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexIblockSections();
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
        if (!Smartseo\Models\SmartseoNoindexRuleTable::getByPrimary($id)) {
            throw new \Exception('Noindex rule not found');
        }

        global $USER;

        $userId = $USER->GetID();

        $element = \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexRule::wakeUp($id);

        $element->setActive($data['ACTIVE']);
        $element->setName($data['NAME']);
        $element->setSiteId($data['SITE_ID']);
        $element->setIblockTypeId($data['IBLOCK_TYPE_ID']);
        $element->setIblockId($data['IBLOCK_ID']);
        $element->setSort($data['SORT']);
        $element->setModifiedBy($userId);
        $element->setDateChange(new \Bitrix\Main\Type\DateTime());
        $element->setIblockSectionAll($data['IBLOCK_SECTION_ALL']);
        $element->setUrlTemplate($data['URL_TEMPLATE']);

        $result = $element->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $elementId = $result->getId();

        if ($data['IBLOCK_SECTIONS'] && !$data['IBLOCK_SECTION_ALL']) {
            $this->updateIblockSections($element, $data['IBLOCK_SECTIONS']);
        } else {
            $this->deleteIblockSections($element);
        }

        return $elementId;
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

            $_iblockSection = new \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexIblockSections();
            $_iblockSection->setSectionId($sectionId);

            $element->addToIblockSections($_iblockSection);
        }

        $result = $element->save(true);

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        if ($iblockSectionIdsForDelete) {
            foreach ($iblockSectionIdsForDelete as $_iblockSectionId) {
                $_iblockSection = \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexIblockSections::wakeUp($_iblockSectionId);

                if (!$_iblockSection) {
                    throw new \Exception(
                    "No IblockSection found for deletion, [ID: $_iblockSectionId]");
                }

                $result = $_iblockSection->delete(true);

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
            $_iblockSection = \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexIblockSections::wakeUp($_iblockSection->getId());

            $result = $_iblockSection->delete(true);

            if (!$result->isSuccess()) {
                throw new \Exception(implode('<br>', $result->getErrorMessages()));
            }
        }
    }

    private function delete($id)
    {
        if (!Smartseo\Models\SmartseoNoindexRuleTable::getByPrimary($id)) {
            $this->addError('Noindex rule not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoNoindexRuleTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return true;
    }

    private function copy($id)
    {
        if (!Smartseo\Models\SmartseoNoindexRuleTable::getByPrimary($id)) {
            $this->addError('Noindex rule not found');

            return false;
        }

        $replication = new Smartseo\Admin\Actions\Replication();

        global $DB;

        $newId = null;

        try {
            $DB->StartTransaction();

            if (!($newId = $replication->copyNoindexRule($id)) || $replication->hasErrors()) {
                throw new \Exception(implode('<br/>', $replication->getErrors()));
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        return $newId;
    }

    private function generateUrls($noindexRuleId)
    {
        $urlEngine = new Smartseo\Engines\UrlNoindexEngine($noindexRuleId);

        if (!$urlEngine->update()) {
            if($urlEngine->hasErrors()) {
                throw new \Exception(implode('<br>', $urlEngine->getErrors()));
            }

            return false;
        }

        return $urlEngine->getResult();
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

    private function listenGridActions($data = [])
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
                case 'grid_noindex_rule_conditions' :
                    $grid = new GridSmartseo\NoindexRuleConditionGrid($data['ID']);
                    $grid->addSort($gridSort);
                    $grid->addFilter();
                    $grid->setCurrentPage($currentPage);

                    $result = [
                        'gridCondition' => $grid->getComponentParams(true),
                        'listConditionTypes' => Smartseo\Models\SmartseoNoindexConditionTable::getTypeParams(),
                    ];

                    break;

                default:
                    break;
            }

            $this->render('detail/_grid_conditions', $result);

            return true;
        }

        return false;
    }

}
