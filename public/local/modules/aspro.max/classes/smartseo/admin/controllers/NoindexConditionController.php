<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Helper,
    Aspro\Max\Smartseo\Admin\Grids as GridSmartseo,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

class NoindexConditionController extends Controller
{
    use BitrixCoreEntity;

    const ALIAS = 'NOINDEX_CONDITION';

    public function getViewFolderName()
    {
        return 'noindex_condition';
    }

    public function actionDetail($id = null, $noindex_rule_id)
    {
        if(!$noindexRule = Smartseo\Models\SmartseoNoindexRuleTable::getRowById($noindex_rule_id)) {
            throw new \Exception('Parent element not found');
        }

        $data = null;

        if ($id && (!$data = Smartseo\Models\SmartseoNoindexConditionTable::getRowById($id))) {
            throw new \Exception('Element not found');
        }

        $this->render('detail', [
            'alias' => self::ALIAS,
            'data' => $data,
            'noindexRuleId' => $noindexRule['ID'],
            'listConditionTypes' => Smartseo\Models\SmartseoNoindexConditionTable::getTypeParams(),
            'listProperties' => $this->getPropertyList($noindexRule['IBLOCK_ID']),
            'gridConditionId' => GridSmartseo\NoindexRuleConditionGrid::getInstance($noindexRule['ID'])->getGridId(),
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

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $listConditionTypes = Smartseo\Models\SmartseoNoindexConditionTable::getTypeParams();

        echo Json::encode(array_filter([
            'result' => true,
            'message' => '',
            'action' => $this->request->get('action'),
            'fields' => [
                'ID' => $elementId,
                'NAME' => '[' . $elementId . '] ' . $listConditionTypes[$data['TYPE']]
            ],
        ]));
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
            'message' => 'Noindex condition deactivated successfully'
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
            'message' => 'Noindex condition activated successfully'
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
            'message' => 'Noindex condition deleted successfully',
        ]);
    }

    private function add(array $data)
    {
        $noindexRule = null;
        if (!$data['NOINDEX_RULE_ID'] || (!$noindexRule = Smartseo\Models\SmartseoNoindexRuleTable::getRowById($data['NOINDEX_RULE_ID']))) {
            throw new \Exception('Parent element not found');
        }

        $element = new \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexCondition();
        $element->setNoindexRuleId($data['NOINDEX_RULE_ID']);
        $element->setActive($data['ACTIVE']);
        $element->setType($data['TYPE']);
        $element->setValue($data['VALUE']);
        $element->setSort($data['SORT']);

        if($data['PROPERTIES'] && $data['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_PROPERTIES) {
            $properties = $this->getPropertyList($noindexRule['IBLOCK_ID'], $data['PROPERTIES']);
            $data['PROPERTIES'] = serialize($properties);
            $element->setProperties($data['PROPERTIES']);
        }

        $result = $element->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $elementId = $result->getId();

        return $elementId;
    }

    private function update($id, array $data)
    {
        $noindexRule = null;
        if (!$data['NOINDEX_RULE_ID'] || (!$noindexRule = Smartseo\Models\SmartseoNoindexRuleTable::getRowById($data['NOINDEX_RULE_ID']))) {
            throw new \Exception('Parent element not found');
        }

        if (!Smartseo\Models\SmartseoNoindexConditionTable::getByPrimary($id)) {
            throw new \Exception('Noindex condition not found');
        }

        $element = \Aspro\Max\Smartseo\Models\EO_SmartseoNoindexCondition::wakeUp($id);
        $element->setNoindexRuleId($data['NOINDEX_RULE_ID']);
        $element->setActive($data['ACTIVE']);
        $element->setType($data['TYPE']);
        $element->setValue($data['VALUE']);
        $element->setSort($data['SORT']);

        if($data['PROPERTIES'] && $data['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_PROPERTIES) {
            $properties = $this->getPropertyList($noindexRule['IBLOCK_ID'], $data['PROPERTIES']);
            $data['PROPERTIES'] = serialize($properties);
            $element->setProperties($data['PROPERTIES']);
        }

        $result = $element->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $elementId = $result->getId();

        return $elementId;
    }

    private function deactivate($id)
    {
        if (!Smartseo\Models\SmartseoNoindexConditionTable::getByPrimary($id)) {
            $this->addError('Noindex condition not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoNoindexConditionTable::update($id, [
            'ACTIVE' => 'N'
        ]);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function activate($id)
    {
        if (!Smartseo\Models\SmartseoNoindexConditionTable::getByPrimary($id)) {
            $this->addError('Noindex condition not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoNoindexConditionTable::update($id, [
            'ACTIVE' => 'Y'
        ]);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function delete($id)
    {
        if (!Smartseo\Models\SmartseoNoindexConditionTable::getByPrimary($id)) {
            $this->addError('Noindex condition not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoNoindexConditionTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return true;
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

    private function getSkuIblockId($iblockId)
    {
        if(!$this->isCatalogModule()) {
            return null;
        }

        $row = \Bitrix\Catalog\CatalogIblockTable::getRow(array_filter([
              'select' => [
                  'IBLOCK_ID',
              ],
              'filter' => [
                  'PRODUCT_IBLOCK_ID' => $iblockId,
              ]
        ]));

        if(!$row) {
            return null;
        }

        return $row['IBLOCK_ID'];
    }

    protected static function getPriceList($priceIds = [])
    {
        $rows = \Bitrix\Catalog\GroupTable::getList(array_filter([
              'select' => [
                  'ID',
                  'NAME',
                  'BASE',
                  'XML_ID',
                  'LANG_' => 'CURRENT_LANG'
              ],
              'filter' => array_filter([
                  'ID' => $priceIds
              ]),
              'order' => [
                  'BASE' => 'ASC',
                  'SORT' => 'ASC',
              ]
          ]))->fetchAll();

        return $rows;
    }

    private function getPropertyList($iblockId, $propertyValues = [])
    {
        $propertyIds = [];
        $priceIds = [];

        foreach ($propertyValues as $value) {
            if (preg_match('#IBLOCK_\d+|SKU_IBLOCK_\d+#', $value)) {                ;
                $propertyIds[] = (int)preg_replace('#IBLOCK_|SKU_IBLOCK_#', '', $value);
            }
            if (preg_match('#PRICE_\d+#', $value)) {
                $priceIds[] = (int)preg_replace('#PRICE_#', '', $value);
            }
        }

        $skuIblockId = $this->getSkuIblockId($iblockId);

        $rows = \Bitrix\Iblock\SectionPropertyTable::getList([
            'select' => [
                'PROPERTY_ID',
                'DISPLAY_TYPE',
            ],
            'filter' => [
                'SMART_FILTER' => 'Y',
                'IBLOCK_ID' => array_filter([$iblockId, $skuIblockId])
            ],
            'group' => [
                'PROPERTY_ID',
            ]
        ])->fetchAll();

        if($propertyValues && !$propertyIds) {
            $propertyList = [];
        } else {
            $sectionPropertyIds = array_column($rows, 'DISPLAY_TYPE', 'PROPERTY_ID');

            $propertyList = \Bitrix\Iblock\PropertyTable::getList(array_filter([
                  'select' => [
                      'ID',
                      'IBLOCK_ID',
                      'NAME',
                      'CODE',
                      'REF_IBLOCK_ID' => 'IBLOCK.ID',
                      'REF_IBLOCK_NAME' => 'IBLOCK.NAME',
                  ],
                  'filter' => array_filter([
                      'IBLOCK_ID' => [$iblockId, $skuIblockId],
                      'ID' => $propertyIds ?: array_keys($sectionPropertyIds),
                  ]),
                  'order' => [
                      'NAME' => 'ASC',
                  ],
              ]))->fetchAll();
        }

        $iblockProperties = [];
        $skuIblockProperties = [];
        foreach ($propertyList as $property) {
            if($property['REF_IBLOCK_ID'] == $iblockId) {
                $iblockProperties[] = [
                    'PROPERTY_UNIQUE' => 'IBLOCK_' . $property['ID'],
                    'PROPERTY_ID' => $property['ID'],
                    'PROPERTY_CODE' => $property['CODE'],
                    'PROPERTY_NAME' => $property['NAME'],
                    'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
                    'PROPERTY_IBLOCK_ID' => $property['REF_IBLOCK_ID'],
                    'PROPERTY_IBLOCK_NAME' => $property['REF_IBLOCK_NAME'],
                    'GROUP' => 'IBLOCK',
                ];
            }

            if($skuIblockId && $property['REF_IBLOCK_ID'] == $skuIblockId) {
                $skuIblockProperties[] = [
                    'PROPERTY_UNIQUE' => 'SKU_IBLOCK_' . $property['ID'],
                    'PROPERTY_ID' => $property['ID'],
                    'PROPERTY_CODE' => $property['CODE'],
                    'PROPERTY_NAME' => $property['NAME'],
                    'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
                    'PROPERTY_IBLOCK_ID' => $property['REF_IBLOCK_ID'],
                    'PROPERTY_IBLOCK_NAME' => $property['REF_IBLOCK_NAME'],
                    'GROUP' => 'SKU_IBLOCK',
                ];
            }
        }

        if($propertyValues && !$priceIds) {
           $priceList = [];
        } else {
           $priceList = $this->getPriceList($priceIds);
        }

        $ñatalogGroupList = [];
        if($this->isCatalogModule()) {
            foreach ($priceList as $price) {
                $ñatalogGroupList[] = [
                    'PROPERTY_UNIQUE' => 'PRICE_' . $price['ID'],
                    'PROPERTY_ID' => $price['ID'],
                    'PROPERTY_CODE' => $price['NAME'],
                    'PROPERTY_NAME' => $price['LANG_NAME'],
                    'PROPERTY_TYPE' => 'PRICE',
                    'GROUP' => 'PRICE',
                ];
            }
        }

        return array_merge($iblockProperties, $skuIblockProperties, $ñatalogGroupList);
    }

}
