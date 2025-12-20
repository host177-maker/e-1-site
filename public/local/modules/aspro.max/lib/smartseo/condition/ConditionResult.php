<?php

namespace Aspro\Max\Smartseo\Condition;

use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

class ConditionResult
{

    private $errors = [];

    private $isCatalogModule = false;

    private $iblockId = null;
    private $skuIblockId = null;
    private $skuPropertyId = null;
    private $sectionIds = null;
    private $sectionMargins = [];
    private $propertyFields = [];
    private $skuPropertyFields = [];
    private $pricePropertyFields = [];

    private $needParse = false;
    private $isOnlyActive = true;
    private $condition = [];
    private $resultConditionTreeForQuery = [];

    /** @var \Aspro\Max\Smartseo\Condition\ConditionTree */
    private $conditionTree = null;
    /** @var \Aspro\Max\Smartseo\Condition\ConditionQuery */
    private $conditionQuery = null;

    /** \CGlobalCondCtrl */
    protected $builds = [];

    public function __construct($iblockId, $condition, $controlBuilds = [])
    {
        if (\Bitrix\Main\Loader::includeModule('catalog')) {
            $this->isCatalogModule = true;
        }

        $this->setIblockId($iblockId);
        $this->setInitialCondition($condition);
        $this->setConditionControlBuilds($controlBuilds);

        $this->initConditionTree();
    }

    public function setIblockId($iblockId)
    {
        $this->iblockId = $iblockId;

        $this->loadDataSkuIblock($this->iblockId);

        return $this;
    }
    public function setSectionIds(array $sectionIds)
    {
        $this->sectionIds = $sectionIds;

        return $this;
    }

    public function setSectionMargins(array $sectionMargins)
    {
        $this->sectionMargins = $sectionMargins;

        return $this;
    }

    public function setConditionControlBuilds($controlBuilds = [])
    {
        if ($controlBuilds) {
            foreach ($controlBuilds as $build) {
                $this->addControlBuild($build);
            }
        }

        return $this;
    }

    public function setInitialCondition($condition)
    {
        if(!is_array($condition) && CheckSerializedData($condition)) {
            $condition = unserialize($condition, ['allowed_classes' => false]);
        }

        if(!isset($condition['CLASS_ID'])) {
            $this->needParse = true;
        }

        $this->condition = $condition;

        return $this;
    }

    public function isOnlyActiveElement(bool $isOnlyActive)
    {
        $this->isOnlyActive = $isOnlyActive;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function getPropertyFields()
    {
        return $this->propertyFields;
    }

    public function getSkuPropertyFields()
    {
        return $this->skuPropertyFields;
    }

    public function getCatalogPricePropertyFields()
    {
        return $this->pricePropertyFields;
    }

    public function getAllPropertyFields()
    {
        return array_merge($this->getPropertyFields(), $this->getSkuPropertyFields());
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function setErrors($errors)
    {
        if (is_array($errors)) {
            $this->errors = array_map(function($item) {
                return $item;
            }, $errors);
        } else {
            $this->errors[] = $errors;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors ? true : false;
    }

    public function getResult()
    {
        $query = $this->getQuery();

        if($query != null) {
            return $query->exec()->fetchAll();
        }

        return [];
    }

    public function getResultElementIds($filter = [], $order = [])
    {
        $query = $this->getQuery();

        if($query == null) {
            return [];
        }

        $query->setSelect(['IBLOCK_ELEMENT_ID']);
        $query->setGroup(['IBLOCK_ELEMENT_ID']);

        if($filter) {
            $query->setFilter($filter);
        }

        if($order) {
            $query->setOrder($order);
        }

        $result = $query->exec()->fetchAll();

        return array_column($result, 'IBLOCK_ELEMENT_ID');
    }

    protected function getQuery()
    {
        if(!$this->resultConditionTreeForQuery) {
            return null;
        }

        $this->conditionQuery = new Smartseo\Condition\ConditionQuery();
        $this->conditionQuery->setConditionItemTree($this->resultConditionTreeForQuery);

        $this->conditionQuery
          ->setIblockId($this->iblockId)
          ->isOnlyActiveElement($this->isOnlyActive);

         if($this->sectionMargins) {
            $this->conditionQuery->setSectionMargins($this->sectionMargins);
        } elseif($this->sectionIds) {
           $this->conditionQuery->setSectionIds($this->sectionIds);
        }

        if($this->skuIblockId && $this->skuPropertyId) {
            $this->conditionQuery
                ->setSkuIblockId($this->skuIblockId)
                ->setSkuPropertyId($this->skuPropertyId);
        }

        return $this->conditionQuery->getQuery();
    }

    protected function initConditionTree()
    {
        $this->conditionTree = new ConditionTree();

        foreach ($this->getConditionControlBuilds() as $build) {
            $this->conditionTree->addControlBuild($build);
        }

        $this->conditionTree->init(BT_COND_MODE_PARSE, ConditionTree::BT_COND_BUILD_SMARTSEO, []);

        $condition = $this->condition;
        if($this->needParse) {
            $this->condition = $this->conditionTree->parse($condition);
        }

        $conditionItemTree = $this->conditionTree->getItemTree($this->condition);

        if(!$conditionItemTree) {
            return;
        }

        $this->resultConditionTreeForQuery = $this->getPrepareTreeCondition($conditionItemTree);
    }

    protected function getConditionControlBuilds()
    {
        if (!$this->builds) {
            $this->addControlBuild(new Controls\GroupBuildControls());
            $this->addControlBuild(new Controls\IblockPropertyBuildControls(
              $this->iblockId
            ));

            if ($this->isCatalogModule) {
                $this->conditionTree->addControlBuild(new Controls\CatalogGroupBuildControls());
            }
        }

        return $this->builds;
    }

    protected function addControlBuild(\CGlobalCondCtrl $controlBuild)
    {
        $this->builds[uniqid()] = $controlBuild;

        return $this;
    }

    private function loadDataSkuIblock($iblockId)
    {
        $row = \Bitrix\Catalog\CatalogIblockTable::getRow([
              'select' => [
                  'IBLOCK_ID',
                  'SKU_PROPERTY_ID',
              ],
              'filter' => [
                  'PRODUCT_IBLOCK_ID' => $iblockId,
              ],
        ]);

        if (!$row) {
            return;
        }

        $this->skuIblockId = $row['IBLOCK_ID'];
        $this->skuPropertyId = $row['SKU_PROPERTY_ID'];
    }

    protected function getPrepareTreeCondition($items)
    {
        $result = [];

        $level = 0;
        foreach ($items as $item) {
             if($item['GROUP'] == 'Y') {
                 $childrenItems = $item['CHILDREN'];
                 $item['CHILDREN'] = [];

                 $result[$item['CLASS_ID']] = $item;

                 $this->treeTraversalConditionLevel($result[$item['CLASS_ID']]['CHILDREN'], $childrenItems);
             }
        }

        return $result;
    }

    protected function treeTraversalConditionLevel(&$result, $items)
    {
        foreach ($items as $item) {
            if ($item['GROUP'] == 'Y') {
                $childrenItems = $item['CHILDREN'];
                $item['CHILDREN'] = [];

                $result[$item['CLASS_ID']] = $item;

                $this->parseConditionItemTreeLevel($result[$item['CLASS_ID']]['CHILDREN'], $childrenItems);

                continue;
            }

            if($result[$item['CLASS_ID']]) {
                $logics = $result[$item['CLASS_ID']]['LOGICS'];
            } else {
                $logics = [];
            }

            $logics = $this->prepareLogicValues($logics, $item['LOGIC']);

            unset($item['LOGIC']);

            $result[$item['CLASS_ID']] = $item;
            $result[$item['CLASS_ID']]['LOGICS'] = $logics;

            if ($item['ENTITY'] == 'ELEMENT_PROPERTY') {
                $this->propertyFields[$item['ENTITY'] . '_' . $item['ID']] = $result[$item['CLASS_ID']];
            }

            if ($item['ENTITY'] == 'SKU_ELEMENT_PROPERTY') {
                $this->skuPropertyFields[$item['ENTITY'] . '_' . $item['ID']] = $result[$item['CLASS_ID']];
            }

            if ($item['ENTITY'] == 'CATALOG_GROUP') {
                $this->pricePropertyFields[$item['ENTITY'] . '_' . $item['ID']] = $result[$item['CLASS_ID']];
            }
        }
    }

    protected function prepareLogicValues($logics, $newLogic)
    {
        foreach ($logics as $key => $logic) {
            if($logic['OPERATOR'] == $newLogic['OPERATOR']) {
                $logics[$key]['VALUE'] = [];

                if(is_array($logic['VALUE'])) {
                    $logics[$key]['VALUE'] = array_merge($logic['VALUE'], [$newLogic['VALUE']]);
                } else {
                    $logics[$key]['VALUE'] = [$logic['VALUE'], $newLogic['VALUE']];
                }

                return $logics;
            }
        }

        array_push($logics, $newLogic);

        return $logics;
    }
}