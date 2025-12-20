<?php

namespace Aspro\Max\Smartseo\Condition;

use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

class ConditionQuery
{
    /** = (equal) **/
    const LOGIC_EQ = 'Equal';
    /** != (not equal)  */
    const LOGIC_NOT_EQ = 'Not';
    /** > (great) */
    const LOGIC_GR = 'Great';
    /** < (less) */
    const LOGIC_LS = 'Less';
    /** >= (great or equal) */
    const LOGIC_EGR = 'EqGr';
    /** <= (less or equal) */
    const LOGIC_ELS = 'EqLs';
    /** contain */
    const LOGIC_CONT = 'Contain';
    /** not contain */
    const LOGIC_NOT_CONT = 'NotCont';
    /** AND */
    const LOGIC_AND = 'AND';
    /** OR */
    const LOGIC_OR = 'OR';

    private $errors = [];

    private $iblockId = null;
    private $skuIblockId = null;
    private $skuPropertyId = null;
    private $conditionItemTree = [];
    private $sectionIds = [];
    private $sectionMargins = [];
    private $entityTableJoin = [];
    private $isIncludeSubsection = true;
    private $isOnlyActive = true;

    public function __construct(){}

    public function setIblockId($iblockId)
    {
        $this->iblockId = $iblockId;

        return $this;
    }

    public function setSkuIblockId($iblockId)
    {
        $this->skuIblockId = $iblockId;

        return $this;
    }

    public function setSkuPropertyId($skuPropertyId)
    {
        $this->skuPropertyId = $skuPropertyId;

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

    public function isOnlyActiveElement(bool $isOnlyActive)
    {
        $this->isOnlyActive = $isOnlyActive;

        return $this;
    }

    public function getSectionIds()
    {
        return $this->sectionIds;
    }

    public function getSectionMargins()
    {
        return $this->sectionMargins;
    }

    public function setIncludeSubsection(bool $value)
    {
        $this->isIncludeSubsection = $value;
    }

    public function setConditionItemTree(array $conditionItemTree)
    {
        $this->conditionItemTree = $conditionItemTree;

        $this->treeTraversal($this->conditionItemTree);
    }

    public function getConditionItemTree()
    {
        return $this->conditionItemTree;
    }

    public function getQuery()
    {
        if(!$this->validate()) {
            return null;
        }

        return $this->createQueryModeDefault();
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

    public function validate()
    {
        if(!$this->iblockId) {
            $this->addError(get_class($this) . ': IBLOCK_ID param expected not found');

            return false;
        }

        return true;
    }

    protected function registerEntityTableJoin(array $conditionItem)
    {
        $alias = $this->getEntityAlias($conditionItem);

        if($this->entityTableJoin[$alias]) {
            return;
        }

        $this->entityTableJoin[$alias] = array_filter([
            'ALIAS' => $alias,
            'ENTITY' => $conditionItem['ENTITY'],
            'LINK_IBLOCK_ID' => $conditionItem['PROPERTY_LINK_IBLOCK_ID'],
        ]);
    }

    protected function createQueryModeDefault()
    {
        if(!$this->conditionItemTree) {
            return null;
        }

        $query = \Bitrix\Iblock\ElementPropertyTable::query();
        $querySelect = [];

        $queryWhereEntityJoin = \Bitrix\Main\Entity\Query::filter();
        $queryWhereEntityJoin->logic('and');

        $queryWhereSection = \Bitrix\Main\Entity\Query::filter();
        $queryWhereSection->logic('and');

        $queryWhereValues = \Bitrix\Main\Entity\Query::filter();
        $queryWhereValues->logic('or');

        foreach ($this->conditionItemTree as $item) {
            $this->appendLevelQueryModeDefault($querySelect, $queryWhereEntityJoin, $queryWhereValues, $item['CHILDREN']);
        }

        foreach ($querySelect as $select) {
            $query->addSelect($select['FIELD'], $select['ALIAS']);
        }

        $groupBy = array_filter($querySelect, function($item){
            return $item['GROUP_BY'] === 'Y';
        });

        $orderBy = array_filter($querySelect, function($item){
            return $item['ORDER_BY'] === 'Y';
        });

        $query->setOrder(array_column($orderBy, 'ORDER_FIELD'));
        $query->setGroup(array_column($groupBy, 'GROUP_FIELD'));

        $this->appendEntityJoin($query);

        if($this->getSectionMargins()) {
            $this->appendSectionMarginWhere($queryWhereSection, $this->getSectionMargins());
        } elseif($this->getSectionIds()) {
            $this->appendSectionWhere($queryWhereSection, $this->getSectionIds());
        }

        if ($queryWhereEntityJoin->hasConditions()) {
            $query->where($queryWhereEntityJoin);
        }

        if ($queryWhereValues->hasConditions()) {
          $query->where($queryWhereValues);
        }

        if ($queryWhereSection->hasConditions()) {
            $query->where($queryWhereSection);
        }

        return $query;
    }

    protected function appendEntityJoin(&$query)
    {
        $isRegisterSkuElements = false;
        $isRegisterElements = false;
        $isRegisterPrices = false;

        foreach ($this->entityTableJoin as $table) {
            if ($table['ENTITY'] == 'ELEMENT_PROPERTY') {
                $isRegisterElements = true;
            }

            if ($table['ENTITY'] == 'SKU_ELEMENT_PROPERTY' && $this->skuIblockId && $this->skuPropertyId) {
                $isRegisterSkuElements = true;
            }

            if ($table['ENTITY'] == 'CATALOG_GROUP') {
                $isRegisterPrices = true;
            }
        }

        if (!$isRegisterElements && $isRegisterSkuElements) {
            $query->registerRuntimeField(
              (new \Bitrix\Main\ORM\Fields\Relations\Reference(
              'sku', \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.IBLOCK_ELEMENT_ID')
                ->where('ref.IBLOCK_PROPERTY_ID', '=', $this->skuPropertyId)
              ))->configureJoinType('left')
            );
        } else {
            if($isRegisterSkuElements || $isRegisterPrices){
                $query->registerRuntimeField(
                (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                'sku', \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.VALUE', 'this.ELEMENT.ID')
                    ->where('ref.IBLOCK_PROPERTY_ID', '=', $this->skuPropertyId)
                ))->configureJoinType('left')
                );

                if($this->isOnlyActive) {
                    $query->where('ELEMENT.ACTIVE', '=', 'Y');
                }
                $query->where('ELEMENT.IBLOCK_ID', '=', $this->iblockId);
            }
        }

        if (!$isRegisterElements && $isRegisterSkuElements) {
            $join = \Bitrix\Main\Entity\Query::filter();
            $join->whereColumn('ref.ID', 'this.sku.VALUE');
            if($this->isOnlyActive) {
                $join->where('ref.ACTIVE', '=', 'Y');
            }

            $query->registerRuntimeField(
              (new \Bitrix\Main\ORM\Fields\Relations\Reference(
              'parent_element', \Bitrix\Iblock\ElementTable::class, $join))->configureJoinType('inner')
            );

            if ($this->getSectionIds() || $this->getSectionMargins()) {
                $query->registerRuntimeField(
                  (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                  'section', \Bitrix\Iblock\SectionElementTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.parent_element.ID')
                  ))->configureJoinType('inner')
                );
            }
        } else {
            if ($isRegisterSkuElements) {
                $join = \Bitrix\Main\Entity\Query::filter();
                $join->whereColumn('ref.ID', 'this.sku.IBLOCK_ELEMENT_ID');
                if($this->isOnlyActive) {
                    $join->where('ref.ACTIVE', '=', 'Y');
                }

                $query->registerRuntimeField(
                  (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                    'sku_element', \Bitrix\Iblock\ElementTable::class, $join
                  ))->configureJoinType('inner')
                );
            }

            if ($this->getSectionIds() || $this->getSectionMargins()) {
                $query->registerRuntimeField(
                  (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                  'section', \Bitrix\Iblock\SectionElementTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.ELEMENT.ID')
                  ))->configureJoinType('inner')
                );
            }
        }

        foreach ($this->entityTableJoin as $table) {
            if ($table['ENTITY'] == 'ELEMENT_PROPERTY') {
                $query->registerRuntimeField(
                  (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                  $table['ALIAS'], \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.ELEMENT.ID')
                  ))->configureJoinType('inner')
                );

                if(isset($table['LINK_IBLOCK_ID']) && $table['LINK_IBLOCK_ID'] > 0) {
                    $join = \Bitrix\Main\Entity\Query::filter();
                    $join->whereColumn('ref.ID', 'this.' . $table['ALIAS'] . '.VALUE');
                    $join->where('ref.IBLOCK_ID', '=', $table['LINK_IBLOCK_ID']);
                    if($this->isOnlyActive) {
                        $join->where('ref.ACTIVE', '=', 'Y');
                    }

                    $query->registerRuntimeField(
                       (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                       'link_' . $table['ALIAS'], \Bitrix\Iblock\ElementTable::class, $join))->configureJoinType('inner')
                     );
                }
            }

            if ($table['ENTITY'] == 'SKU_ELEMENT_PROPERTY') {
                $query->registerRuntimeField(
                  (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                  $table['ALIAS'], \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.sku.IBLOCK_ELEMENT_ID')
                  ))->configureJoinType('inner')
                );

                if (isset($table['LINK_IBLOCK_ID']) && $table['LINK_IBLOCK_ID'] > 0) {
                    $query->registerRuntimeField(
                      (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                      'sku_link_' . $table['ALIAS'], \Bitrix\Iblock\ElementTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.ID', 'this.' . $table['ALIAS'] . '.VALUE')
                        ->where('ref.IBLOCK_ID', '=', $table['LINK_IBLOCK_ID'])
                        ->where('ref.ACTIVE', '=', 'Y')
                      ))->configureJoinType('inner')
                    );
                }
            }

            if ($table['ENTITY'] == 'CATALOG_GROUP') {
                if (!$isRegisterElements && $isRegisterSkuElements) {
                    $query->registerRuntimeField(
                      (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                      $table['ALIAS'], \Bitrix\Catalog\PriceTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.PRODUCT_ID', 'this.IBLOCK_ELEMENT_ID')
                      ))->configureJoinType('inner')
                    );
                } else {
                    $join = \Bitrix\Main\Entity\Query::filter();
                    $join->logic('or');
                    $join->whereColumn('ref.PRODUCT_ID', 'this.IBLOCK_ELEMENT_ID');
                    $join->whereColumn('ref.PRODUCT_ID', 'this.sku.IBLOCK_ELEMENT_ID');

                    $query->registerRuntimeField(
                      (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                      $table['ALIAS'], \Bitrix\Catalog\PriceTable::class, $join
                      ))->configureJoinType('inner')
                    );
                }
            }
        }
    }

    protected function appendSectionWhere(&$queryWhereSection, array $sectionIds)
    {
        if(!$sectionIds) {
            return;
        }

        $where = \Bitrix\Main\Entity\Query::filter();
        $where->whereIn('section.IBLOCK_SECTION_ID', $sectionIds);
        $where->whereNull('section.ADDITIONAL_PROPERTY_ID');

        if($this->isOnlyActive) {
            $where->where('section.IBLOCK_SECTION.ACTIVE', 'Y');
            $where->where('section.IBLOCK_SECTION.GLOBAL_ACTIVE', 'Y');
        }

        $queryWhereSection->where($where);
    }

    protected function appendSectionMarginWhere(&$queryWhereSection, array $sectionMargins)
    {
        if(!$sectionMargins) {
            return;
        }

        $whereMargin = \Bitrix\Main\Entity\Query::filter();
        $whereMargin->logic('or');

        foreach ($sectionMargins as $margin) {
            $whereMargin->where(
              \Bitrix\Main\Entity\Query::filter()->where([
                  ['section.IBLOCK_SECTION.LEFT_MARGIN', $this->isIncludeSubsection ? '>=' : '=', $margin['LEFT_MARGIN']],
                  ['section.IBLOCK_SECTION.RIGHT_MARGIN', $this->isIncludeSubsection ? '<=' : '=', $margin['RIGHT_MARGIN']],
              ])
            );
        }
        $whereActive = \Bitrix\Main\Entity\Query::filter();
        if($this->isOnlyActive) {
            $whereActive->where('section.IBLOCK_SECTION.ACTIVE', 'Y');
            $whereActive->where('section.IBLOCK_SECTION.GLOBAL_ACTIVE', 'Y');
        }

        $queryWhereSection->where($whereMargin);

        if ($whereActive->hasConditions()) {
            $queryWhereSection->where($whereActive);
        }
    }

    protected function appendLevelQueryModeDefault(&$select, &$queryWhereEntityJoin, &$queryWhereValues, $conditionLevel)
    {
        if(!$conditionLevel) {
            return;
        }

        $where = \Bitrix\Main\Entity\Query::filter();

        foreach ($conditionLevel as $item) {
            if ($item['GROUP'] == 'Y') {
                $this->appendLevelQueryModeDefault($select, $queryWhereEntityJoin, $queryWhereValues, $item['CHILDREN']);

                continue;
            }

            $alias = $this->getEntityAlias($item);

            $this->appendSelect($select, $item, $alias);

            if($item['ENTITY'] == 'ELEMENT_PROPERTY' || $item['ENTITY'] == 'SKU_ELEMENT_PROPERTY') {
                if ($item['PROPERTY_TYPE'] == 'N') {
                    $this->appendNumberProperty($queryWhereEntityJoin, $queryWhereValues, $item, $alias);
                } else {
                    $this->appendDefaultProperty($queryWhereEntityJoin, $queryWhereValues, $item, $alias);
                }
            }

            if ($item['ENTITY'] == 'CATALOG_GROUP') {
                $this->appendPriceProperty($queryWhereEntityJoin, $item, $alias);
            }
        }
    }

    protected function appendSelect(&$select, $item, $alias)
    {
        if ($item['ENTITY'] == 'ELEMENT_PROPERTY' || $item['ENTITY'] == 'SKU_ELEMENT_PROPERTY') {
            if ($item['PROPERTY_TYPE'] == 'N') {
                $select['MIN_PROPERTY_' . $item['PROPERTY_ID']] = [
                    'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->min($alias . '.VALUE'),
                    'ALIAS' => 'MIN_PROPERTY_' . $item['PROPERTY_ID'],
                    'GROUP_BY' => 'Y',
                    'GROUP_FIELD' => 'ELEMENT.IBLOCK_ID',
                    'ORDER_BY' => 'N',
                    'ORDER_FIELD' => $alias . '.VALUE',
                ];
                $select['MAX_PROPERTY_' . $item['PROPERTY_ID']] = [
                    'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->max($alias . '.VALUE'),
                    'ALIAS' => 'MAX_PROPERTY_' . $item['PROPERTY_ID'],
                    'GROUP_BY' => 'Y',
                    'GROUP_FIELD' => 'ELEMENT.IBLOCK_ID',
                    'ORDER_BY' => 'N',
                    'ORDER_FIELD' => $alias . '.VALUE',
                ];
            } else {
                $select['PROPERTY_' . $item['PROPERTY_ID']] = [
                    'FIELD' => $alias . '.VALUE',
                    'ALIAS' => 'PROPERTY_' . $item['PROPERTY_ID'],
                    'GROUP_BY' => 'Y',
                    'GROUP_FIELD' => $alias . '.VALUE',
                    'ORDER_BY' => 'Y',
                    'ORDER_FIELD' => $alias . '.VALUE',
                ];
            }
        }

        if ($item['ENTITY'] == 'CATALOG_GROUP') {
            foreach ($item['LOGICS'] as $logic) {
                $select['MIN_CATALOG_PRICE_' . $item['CATALOG_GROUP_ID']] = [
                     'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->min($alias . '.PRICE_SCALE'),
                     'ALIAS' => 'MIN_CATALOG_PRICE_' . $item['CATALOG_GROUP_ID'],
                     'GROUP_BY' => 'Y',
                     'GROUP_FIELD' => 'ELEMENT.IBLOCK_ID',
                     'ORDER_BY' => 'N',
                 ];

                 $select['MAX_CATALOG_PRICE_' . $item['CATALOG_GROUP_ID']] = [
                     'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->max($alias . '.PRICE_SCALE'),
                     'ALIAS' => 'MAX_CATALOG_PRICE_' . $item['CATALOG_GROUP_ID'],
                     'GROUP_BY' => 'Y',
                     'GROUP_FIELD' => 'ELEMENT.IBLOCK_ID',
                     'ORDER_BY' => 'N',
                 ];
            }

        }
    }

    protected function appendDefaultProperty(&$queryWhereEntityJoin, &$queryWhereValues, $item, $alias = '')
    {
        foreach ($item['LOGICS'] as $logic) {
            $this->appendPropertyWhere($queryWhereEntityJoin, $item['PROPERTY_ID'], $logic, $alias);
            $this->appendPropertyWhere($queryWhereValues, $item['PROPERTY_ID'], $logic);
        }
    }

    protected function appendNumberProperty(&$queryWhereEntityJoin, &$queryWhereValues, $item, $alias = '')
    {
        $whereRangeNumber = \Bitrix\Main\Entity\Query::filter();
        $egrValue = 0;
        $elsValue = 0;

        foreach ($item['LOGICS'] as $logic) {
            if($logic['OPERATOR'] == self::LOGIC_EGR) {
                $egrValue = is_array($logic['VALUE']) ? min($logic['VALUE']) : $logic['VALUE'];
            }
            if($logic['OPERATOR'] == self::LOGIC_ELS) {
                $elsValue = is_array($logic['VALUE']) ? max($logic['VALUE']) : $logic['VALUE'];
            }

            $this->appendPropertyWhere($whereRangeNumber, $item['PROPERTY_ID'], $logic, $alias);
            $this->appendPropertyWhere($queryWhereValues, $item['PROPERTY_ID'], $logic);
        }

        if($egrValue > $elsValue) {
            $whereRangeNumber->logic('or');
        }

        $queryWhereEntityJoin->where($whereRangeNumber);
    }

    protected function appendPriceProperty(&$queryWhereEntityJoin, $item, $alias = '')
    {
        $whereRangeNumber = \Bitrix\Main\Entity\Query::filter();
        $egrValue = 0;
        $elsValue = 0;

        foreach ($item['LOGICS'] as $logic) {
            if($logic['OPERATOR'] == self::LOGIC_EGR) {
                $egrValue = is_array($logic['VALUE']) ? min($logic['VALUE']) : $logic['VALUE'];
            }
            if($logic['OPERATOR'] == self::LOGIC_ELS) {
                $elsValue = is_array($logic['VALUE']) ? max($logic['VALUE']) : $logic['VALUE'];
            }

            $this->appendPriceWhere($whereRangeNumber, $item['CATALOG_GROUP_ID'], $logic, $alias);
        }

        if($egrValue > $elsValue) {
            $whereRangeNumber->logic('or');
        }

        $queryWhereEntityJoin->where($whereRangeNumber);
    }

    protected function appendPropertyWhere(&$queryWhere, $propertyId, $logic, $alias = '')
    {
        if ($alias) {
            $alias = $alias . '.';
        }

        $values = [];

        if($logic['VALUE']) {
            if(is_array($logic['VALUE'])) {
                $values = array_filter($logic['VALUE']);
            } else {
                $values = [$logic['VALUE']];
            }
        }

        switch ($logic['OPERATOR']) {
            case self::LOGIC_EQ :
                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId);

                if($values) {
                    $queryFilter->whereIn($alias . 'VALUE', $values);
                }

                break;
            case self::LOGIC_NOT_EQ :
                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId);

                if($values) {
                    $queryFilter->whereNotIn($alias . 'VALUE', $values);
                } else {
                    $queryFilter->whereNull($alias . 'VALUE');
                }

                break;
            case self::LOGIC_CONT :
                $queryFilterLike = \Bitrix\Main\Entity\Query::filter();
                $queryFilterLike->logic('OR');

                if($values) {
                    foreach ($values as $_value) {
                        $queryFilterLike->whereLike($alias . 'VALUE', '%' . $_value . '%');
                    }
                } else {
                    $queryFilterLike->whereNotNull($alias . 'VALUE');
                }

                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($queryFilterLike);

                break;

            case self::LOGIC_NOT_CONT :
                $queryFilterLike = \Bitrix\Main\Entity\Query::filter();
                $queryFilterLike->logic('OR');

                if($values) {
                    foreach ($values as $_value) {
                        $queryFilterLike->whereNotLike($alias . 'VALUE', '%' . $_value . '%');
                    }
                } else {
                    $queryFilterLike->whereNull($alias . 'VALUE');
                }

                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($queryFilterLike);

                break;

            case self::LOGIC_EGR :
                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($alias . 'VALUE_NUM', '>=', min($values));

                break;
            case self::LOGIC_ELS :
                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($alias . 'VALUE_NUM', '<=', max($values));

                break;
            default:
                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($alias . 'VALUE', 'in', $values);

                break;
        }

        $queryWhere->where($queryFilter);
    }

    private function appendPriceWhere(&$queryWhere, $catalogGroupId, $logic, $alias = '')
    {
        if ($alias) {
            $alias = $alias . '.';
        }

        if(is_array($logic['VALUE'])) {
            $values = array_filter($logic['VALUE']);
        } else {
            $values = [$logic['VALUE']];
        }

        switch ($logic['OPERATOR']) {
            case self::LOGIC_EGR :
                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter
                  ->where($alias . 'CATALOG_GROUP_ID', '=', $catalogGroupId)
                  ->where($alias . 'PRICE_SCALE', '>=', min($values));

                break;
            case self::LOGIC_ELS :
                $queryFilter = \Bitrix\Main\Entity\Query::filter();
                $queryFilter
                  ->where($alias . 'CATALOG_GROUP_ID', '=', $catalogGroupId)
                  ->where($alias . 'PRICE_SCALE', '<=', max($values));

                break;
        }

        $queryWhere->where($queryFilter);
    }

    private function getEntityAlias($conditionItem)
    {
        return mb_strtolower($conditionItem['ENTITY'] . '_' . $conditionItem['ID']);
    }

    private function treeTraversal($items)
    {
        $result = [];

        foreach ($items as $item) {
             if($item['GROUP'] == 'Y') {
                 $this->treeTraversalLevel($item['CHILDREN']);
             }
        }

        return $result;
    }

    private function treeTraversalLevel($items)
    {
        foreach ($items as $item) {
            if ($item['GROUP'] == 'Y') {
                $this->treeTraversalLevel($item['CHILDREN']);

                continue;
            }

            $this->registerEntityTableJoin($item);
        }
    }
}
