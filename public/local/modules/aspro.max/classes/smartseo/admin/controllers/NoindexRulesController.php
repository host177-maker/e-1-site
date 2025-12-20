<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use \Aspro\Max\Smartseo,
    \Aspro\Max\Smartseo\Admin\App\Controller,
    \Bitrix\Main\Web\Json,
    \Bitrix\Main\Localization\Loc;

class NoindexRulesController extends Controller
{

    public function getViewFolderName()
    {
        return 'noindex_rule';
    }

    public function actionList($section_id = null)
    {
        $order = [
            'by' => 'ID',
            'order' => 'DESC',
        ];

        if ($this->request->get('by') && $this->request->get('order')) {
            $order = [
                'by' => $this->request->get('by'),
                'order' => $this->request->get('order'),
            ];
        }

        $ui = new Smartseo\Admin\UI\NoindexRuleAdminUI();

        $adminUiList = new \CAdminUiList($ui->getGridId(), new \CAdminUiSorting($ui->getGridId(), $order['by'], $order['order']));

        $adminUiList->AddAdminContextMenu($ui->getContextMenu(), $isShowExcel = false);

        $this->listenEditActionAdminUiList($adminUiList);

        $this->listenGroupActionAdminUiList($adminUiList);

        $adminUiList->AddHeaders($ui->getGridColumns());

        $filter = [];

        $adminUiList->AddFilter($ui->getFilterFields(), $filter);

        $rsData = new \CAdminUiResult(
          $this->getRsData([
              $order['by'] => $order['order']
            ], $filter), $ui->getGridId()
        );

        $rsData->NavStart();

        $adminUiList->SetNavigationParams($rsData);

        $this->render('list', [
            'adminUiList' => $adminUiList,
            'filterFields' => $ui->getFilterFields(),
            'rsData' => $rsData,
        ]);
    }

    public function actionDeactivate()
    {
        if (!$this->validateAjaxAction()) {
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
            'message' => 'Noindex rule deactivated successfully'
        ]);
    }

    public function actionActivate()
    {
        if (!$this->validateAjaxAction()) {
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
            'message' => 'Noindex rule activated successfully'
        ]);
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
            'message' => 'Noindex rule deleted successfully'
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
            'message' => 'Noindex rule copy successfully'
        ]);
    }

    private function getRsData($order = [], $filter = [])
    {
        $elements = $this->getElements($order, $filter);

        $result = new \CDBResult;

        $result->InitFromArray($elements);

        return $result;
    }

    private function getElements($order = [], $filter = [])
    {
        foreach ($filter as $field => $value) {
            if (preg_match('#IBLOCK_\d+_SECTION_ID#', $field)) {
                $filter['IBLOCK_SECTIONS.SECTION_ID'][] = $value;
                unset($filter[$field]);
            }
        }
        if ($filter['NAME']) {
            $filter['NAME'] = '%' . $filter['NAME'] . '%';
        }

        $rsRows = Smartseo\Models\SmartseoNoindexRuleTable::getList([
              'select' => [
                  '*',
                  'REF_IBLOCK_' => 'IBLOCK'
              ],
              'order' => $order,
              'filter' => $filter,
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoNoindexRuleTable::getCacheTtl(),
              ]
        ]);

        $elements = [];
        $elementIds = [];
        while ($_row = $rsRows->fetch()) {
            $elementIds[] = $_row['ID'];
            $elements['E' . $_row['ID']] = $_row;
            $elements['E' . $_row['ID']]['ROW_TYPE'] = 'E';
        }

        $iblockSectionRows = $this->getIblockSectionsByElementIds($elementIds);
        foreach ($iblockSectionRows as $elementId => $iblockSections) {
            $elements['E' . $elementId]['IBLOCK_SECTIONS'] = $iblockSections;
        }

        $conditions = $this->getConditionsByElementIds($elementIds);
        foreach ($conditions as $elementId => $condition) {
            $elements['E' . $elementId]['CONDITIONS'] = $condition;
        }

        return $elements;
    }

    private function getIblockSectionsByElementIds($ids)
    {
        $rows = Smartseo\Models\SmartseoNoindexIblockSectionsTable::getList([
              'select' => [
                  'NOINDEX_RULE_ID',
                  'REF_SECTION_ID' => 'SECTION.ID',
                  'REF_SECTION_NAME' => 'SECTION.NAME',
                  'REF_DEPTH_LEVEL' => 'SECTION.DEPTH_LEVEL',
                  'REF_LEFT_MARGIN' => 'SECTION.LEFT_MARGIN'
              ],
              'filter' => [
                  'NOINDEX_RULE_ID' => $ids
              ],
              'order' => [
                  'REF_LEFT_MARGIN' => 'ASC',
                  'REF_DEPTH_LEVEL' => 'ASC',
                  'REF_SECTION_NAME' => 'ASC'],
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoNoindexIblockSectionsTable::getCacheTtl(),
              ],
          ])
          ->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['NOINDEX_RULE_ID']][] = [
                'ID' => $row['REF_SECTION_ID'],
                'NAME' => $row['REF_SECTION_NAME'],
                'DEPTH_LEVEL' => $row['REF_DEPTH_LEVEL'],
            ];
        }

        return $result;
    }

    private function getConditionsByElementIds($ids)
    {
        $rows = Smartseo\Models\SmartseoNoindexConditionTable::getList([
              'select' => [
                  'NOINDEX_RULE_ID',
                  'TYPE',
                  'VALUE',
                  'PROPERTIES',
              ],
              'filter' => [
                  'ACTIVE' => 'Y',
                  'NOINDEX_RULE_ID' => $ids
              ],
              'order' => [
                  'SORT' => 'ASC',
              ],
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoNoindexConditionTable::getCacheTtl(),
              ],
          ])
          ->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            if ($row['PROPERTIES']) {
                $row['PROPERTIES'] = unserialize($row['PROPERTIES'], ['allowed_classes' => false]);
            }

            $result[$row['NOINDEX_RULE_ID']][] = [
                'TYPE' => $row['TYPE'],
                'VALUE' => $row['VALUE'],
                'PROPERTIES' => $row['PROPERTIES']
            ];
        }

        return $result;
    }

    private function listenGroupActionAdminUiList(\CAdminUiList &$adminUiList)
    {
        if (!$action = $this->request->get('action_button_' . $adminUiList->table_id)) {
            return;
        }

        if (!$mixidResultIds = $adminUiList->GroupAction()) {
            return;
        }

        $elementIds = [];

        foreach ($mixidResultIds as $mixidId) {
            $_type = substr($mixidId, 0, 1);
            $_id = (int) substr($mixidId, 1);

            switch ($_type) {
                case 'E' :
                    $elementIds[] = $_id;
                    break;
            }
        }

        $elementCollection = null;
        if ($elementIds) {
            $elementCollection = Smartseo\Models\SmartseoNoindexRuleTable::getList([
                  'filter' => [
                      'ID' => $elementIds
                  ]
              ])->fetchCollection();
        }

        switch ($action) {
            case 'deactivate' :
                $this->deactivateCollection($elementCollection);
                break;
            case 'activate' :
                $this->activateCollection($elementCollection);
                break;
            case 'delete' :
                $this->deleteElementByIds($elementIds);
                break;
        }

        if ($this->hasErrors()) {
            foreach ($this->getErrors() as $errorMessage) {
                $adminUiList->AddGroupError($errorMessage);
            }
        }
    }

    private function listenEditActionAdminUiList(\CAdminUiList &$adminUiList)
    {
         if ($adminUiList->EditAction()) {
            $mixidResults = $this->request->get('FIELDS');

            foreach ($mixidResults as $mixidId => $mixidData) {
                if (!$adminUiList->IsUpdated($mixidId)) {
                    continue;
                }

                $_type = substr($mixidId, 0, 1);
                $_id = (int) substr($mixidId, 1);

                switch ($_type) {
                    case 'E' :
                        Smartseo\Models\SmartseoNoindexRuleTable::update($_id, array_filter($mixidData, function($code) {
                              return Smartseo\Models\SmartseoNoindexRuleTable::hasMapField($code);
                          }, ARRAY_FILTER_USE_KEY));
                        break;
                }
            }
        }
    }

    private function deactivateCollection($collection)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection as $item) {
            $item->setActive('N');
        }

        $collection->save();
    }

    private function activateCollection($collection)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection as $item) {
            $item->setActive('Y');
        }

        $collection->save();
    }

    private function deleteElementByIds($elementIds)
    {
        global $DB;

        if (!$elementIds) {
            return;
        }

        try {
            $DB->StartTransaction();

            foreach ($elementIds as $elementId) {
                $result = Smartseo\Models\SmartseoNoindexRuleTable::delete($elementId);

                if (!$result->isSuccess()) {
                    $this->setErrors($result->getErrorMessages());
                }
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }
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

    private function activate($id)
    {
        if (!Smartseo\Models\SmartseoNoindexRuleTable::getByPrimary($id)) {
            $this->addError('Noindex rule not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoNoindexRuleTable::update($id, [
            'ACTIVE' => 'Y',
        ]);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function deactivate($id)
    {
        if (!Smartseo\Models\SmartseoNoindexRuleTable::getByPrimary($id)) {
            $this->addError('Noindex rule not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoNoindexRuleTable::update($id, [
            'ACTIVE' => 'N',
        ]);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function copy($id)
    {
        if (!Smartseo\Models\SmartseoNoindexRuleTable::getByPrimary($id)) {
            $this->addError('Noindex rule not found');

            return false;
        }

        $replication = new Smartseo\Admin\Actions\Replication();

        global $DB;

        try {
            $DB->StartTransaction();

            if (!$replication->copyNoindexRule($id) || $replication->hasErrors()) {
                throw new \Exception(implode('<br/>', $replication->getErrors()));
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        return true;
    }

}
