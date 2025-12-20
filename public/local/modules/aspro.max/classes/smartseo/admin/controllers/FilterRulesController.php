<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use \Aspro\Max\Smartseo\Models\SmartseoFilterRuleTable,
    \Aspro\Max\Smartseo\Models\SmartseoFilterIblockSectionsTable,
    \Aspro\Max\Smartseo\Models\SmartseoFilterSectionTable,
    \Aspro\Max\Smartseo\Admin\App\Controller,
    \Aspro\Max\Smartseo\Admin\UI\FilterRulesAdminUI,
    \Aspro\Max\Smartseo\Admin\Traits\FilterChainSectionTree;

class FilterRulesController extends Controller
{

    use FilterChainSectionTree;

    public function getViewFolderName()
    {
        return 'filter_rules';
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

        $ui = new FilterRulesAdminUI();

        $adminUiList = new \CAdminUiList($ui->getGridId(), new \CAdminUiSorting($ui->getGridId(), $order['by'], $order['order']));

        $this->listenEditActionAdminUiList($adminUiList);

        $this->listenGroupActionAdminUiList($adminUiList);

        $adminUiList->AddAdminContextMenu($ui->getContextMenu([
              'parent_section_id' => $section_id
          ]), $isShowExcel = false);

        $adminUiList->AddHeaders($ui->getGridColumns());

        $filter = [];

        $filter['SECTION_ID'] = null;

        if ($section_id) {
            $filter['SECTION_ID'] = (int) $section_id;
        }

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
            'chainSections' => $this->getChainSections($section_id)
        ]);
    }

    private function getRsData($order = [], $filter = [])
    {
        $elements = $this->getElements($order, $filter);
        $sections = $this->getSections($order, $filter);

        $result = new \CDBResult;

        $result->InitFromArray($sections + $elements);

        return $result;
    }

    private function getSections($order = [], $filter = [])
    {
        if ($filter['NAME']) {
            $filter['NAME'] = '%' . $filter['NAME'] . '%';
        }

        $filter['PARENT_ID'] = $filter['SECTION_ID'] ?: null;

        $rsRows = SmartseoFilterSectionTable::getList([
              'select' => [
                  'ID',
                  'NAME',
                  'DESCRIPTION',
                  'DATE_CREATE',
                  'DATE_CHANGE',
                  'SORT',
                  'PARENT_ID',
                  'DEPTH_LEVEL',
              ],
              'order' => array_filter($order, function($code) {
                    return !empty(SmartseoFilterSectionTable::getMap()[$code]);
                }, ARRAY_FILTER_USE_KEY),
              'filter' => array_filter($filter, function($code) {
                    return !empty(SmartseoFilterSectionTable::getMap()[$code]);
                }, ARRAY_FILTER_USE_KEY),
              'cache' => [
                  'ttl' => SmartseoFilterSectionTable::getCacheTtl(),
              ]
        ]);

        $result = [];
        while ($_row = $rsRows->fetch()) {
            $result['S ' . $_row['ID']] = $_row;
            $result['S ' . $_row['ID']]['TYPE'] = 'S';
        }

        return $result;
    }

    private function getElements($order = [], $filter = [])
    {
        foreach ($filter as $field => $value) {
            if (preg_match('[IBLOCK_\d+_SECTION_ID]', $field)) {
                $filter['IBLOCK_SECTIONS.SECTION_ID'][] = $value;
                unset($filter[$field]);
            }
        }

        if ($filter['NAME']) {
            $filter['NAME'] = '%' . $filter['NAME'] . '%';
        }

        $rsRows = SmartseoFilterRuleTable::getList([
              'select' => [
                  '*',
                  'REF_IBLOCK_' => 'IBLOCK'
              ],
              'order' => $order,
              'filter' => $filter,
              'cache' => [
                  'ttl' => SmartseoFilterRuleTable::getCacheTtl(),
              ]
        ]);

        $elements = [];
        $elementIds = [];
        while ($_row = $rsRows->fetch()) {
            $elementIds[] = $_row['ID'];
            $elements['E' . $_row['ID']] = $_row;
            $elements['E' . $_row['ID']]['TYPE'] = 'E';
        }

        $iblockSectionRows = $this->getIblockSectionsByElementIds($elementIds);

        foreach ($iblockSectionRows as $elementId => $iblockSections) {
            $elements['E' . $elementId]['IBLOCK_SECTIONS'] = $iblockSections;
        }

        return $elements;
    }

    private function getIblockSectionsByElementIds($ids)
    {
        $rows = SmartseoFilterIblockSectionsTable::getList([
              'select' => [
                  'FILTER_RULE_ID',
                  'REF_SECTION_ID' => 'SECTION.ID',
                  'REF_SECTION_NAME' => 'SECTION.NAME',
                  'REF_DEPTH_LEVEL' => 'SECTION.DEPTH_LEVEL',
                  'REF_LEFT_MARGIN' => 'SECTION.LEFT_MARGIN'
              ],
              'filter' => [
                  'FILTER_RULE_ID' => $ids
              ],
              'order' => [
                  'REF_LEFT_MARGIN' => 'ASC',
                  'REF_DEPTH_LEVEL' => 'ASC',
                  'REF_SECTION_NAME' => 'ASC'],
              'cache' => [
                  'ttl' => SmartseoFilterIblockSectionsTable::getCacheTtl(),
              ],
          ])
          ->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['FILTER_RULE_ID']][] = [
                'ID' => $row['REF_SECTION_ID'],
                'NAME' => $row['REF_SECTION_NAME'],
                'DEPTH_LEVEL' => $row['REF_DEPTH_LEVEL'],
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
        $sectionIds = [];

        foreach ($mixidResultIds as $mixidId) {
            $_type = substr($mixidId, 0, 1);
            $_id = (int) substr($mixidId, 1);

            switch ($_type) {
                case 'S' :
                    $sectionIds[] = $_id;
                    break;
                case 'E' :
                    $elementIds[] = $_id;
                    break;
            }
        }

        $sectionCollection = null;
        if ($sectionIds) {
            $sectionCollection = SmartseoFilterSectionTable::getList([
                  'filter' => [
                      'ID' => $sectionIds
                  ]
              ])->fetchCollection();
        }

        $elementCollection = null;
        if ($elementIds) {
            $elementCollection = SmartseoFilterRuleTable::getList([
                  'filter' => [
                      'ID' => $elementIds
                  ]
              ])->fetchCollection();
        }

        switch ($action) {
            case 'deactivate' :
                $this->deactivateCollection($sectionCollection);
                $this->deactivateCollection($elementCollection);
                break;
            case 'activate' :
                $this->activateCollection($sectionCollection);
                $this->activateCollection($elementCollection);
                break;
            case 'deactivate_indexing' :
                $this->deactivateIndexingCollection($elementCollection);
                break;
            case 'activate_indexing' :
                $this->activateIndexingCollection($elementCollection);
                break;
            case 'delete' :
                $this->deleteSectionByIds($sectionIds);
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
                    case 'S' :
                        SmartseoFilterSectionTable::update($_id, array_filter($mixidData, function($code) {
                              return !empty(SmartseoFilterSectionTable::getMap()[$code]);
                          }, ARRAY_FILTER_USE_KEY));
                        break;
                    case 'E' :
                        SmartseoFilterRuleTable::update($_id, array_filter($mixidData, function($code) {
                              return !empty(SmartseoFilterRuleTable::getMap()[$code]);
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

    private function deactivateIndexingCollection($collection)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection as $item) {
            $item->setUrlCloseIndexing('N');
        }

        $collection->save();
    }

    private function activateIndexingCollection($collection)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection as $item) {
            $item->setUrlCloseIndexing('Y');
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
                $result = SmartseoFilterRuleTable::delete($elementId);

                if (!$result->isSuccess()) {
                    $this->setErrors($result->getErrorMessages());
                }
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }
    }

    private function deleteSectionByIds($sectionIds)
    {
        global $DB;

        if (!$sectionIds) {
            return;
        }

        try {
            $DB->StartTransaction();
            foreach ($sectionIds as $sectionId) {
                $result = SmartseoFilterSectionTable::delete($sectionId);

                if (!$result->isSuccess()) {
                    $this->setErrors($result->getErrorMessages());
                }
            }
            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }
    }

}
