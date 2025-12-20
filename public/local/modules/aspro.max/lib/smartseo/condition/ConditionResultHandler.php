<?php

namespace Aspro\Max\Smartseo\Condition;

use Bitrix\Main\Localization\Loc;

class ConditionResultHandler
{

    const BT_COND_LOGIC_EQ = 'Equal';           // = (equal)
    const BT_COND_LOGIC_NOT_EQ = 'Not';         // != (not equal)
    const BT_COND_LOGIC_GR = 'Great';           // > (great)
    const BT_COND_LOGIC_LS = 'Less';            // < (less)
    const BT_COND_LOGIC_EGR = 'EqGr';           // >= (great or equal)
    const BT_COND_LOGIC_ELS = 'EqLs';           // <= (less or equal)
    const BT_COND_LOGIC_CONT = 'Contain';       // contain
    const BT_COND_LOGIC_NOT_CONT = 'NotCont';   // not contain
    const LOGIC_AND = 'and';
    const LOGIC_OR = 'or';

    private $logic = self::LOGIC_AND;
    private $condition = [];
    private $iblockId = null;
    private $errors = [];
    private $propertyFields = [];
    private $skuPropertyFields = [];
    private $catalogGroups = [];
    private $isIncludeSubsection = true;
    private $sectionLeftMargin = null;
    private $sectionRightMargin = null;
    private $sectionMargins = [];
    private $needParse = false;

    private $isCatalogModule = false;

    private $skuIblockId = null;
    private $skuPropertyId = null;

    /**
     * @var \CGlobalCondCtrl
     */
    protected $builds = [];

    public function __construct($iblockId, $condition = null, $controlBuilds = [])
    {
        if (\Bitrix\Main\Loader::includeModule('catalog')) {
            $this->isCatalogModule = true;
        }

        $this->setIblockId($iblockId);
        $this->setCondition($condition);

        $this->logic = mb_strtolower($this->condition['DATA']['All']);

        if (!in_array($this->logic, [self::LOGIC_AND, self::LOGIC_OR])) {
            $this->logic = self::LOGIC_AND;
        }

        if($controlBuilds) {
            foreach ($controlBuilds as $build) {
                $this->addControlBuild($build);
            }
        }

        $this->eachConditionValues();
    }

    public function setIblockId($value)
    {
        $this->iblockId = $value;

        $this->loadSkuIblockData($this->iblockId);
    }

    public function setCondition($condition)
    {
        if(!is_array($condition) && CheckSerializedData($condition)) {
            $condition = unserialize($condition, ['allowed_classes' => false]);
        }

        if(!isset($condition['CLASS_ID'])) {
            $this->needParse = true;
        }

        $this->condition = $condition;
    }

    public function getLogic()
    {
        return $this->logic;
    }

    public function getPropertyFields()
    {
        return $this->propertyFields;
    }

    public function getSkuPropertyFields()
    {
        return $this->skuPropertyFields;
    }

    public function getAllPropertyFields()
    {
        return array_merge($this->getPropertyFields(), $this->getSkuPropertyFields());
    }

    public function getCatalogGroupPropertyFields()
    {
        return $this->catalogGroups;
    }

    public function getElementPropertyValues()
    {
        $query = null;
        if($this->propertyFields ||($this->propertyFields && $this->skuPropertyFields)) {
            $query = $this->getQueryElementProperty();
        } else {
            $query = $this->getQueryOnlySkuElementProperty();
        }

        if(!$query) {
            return [];
        }

        return $query->exec()->fetchAll();
    }

    public function getElementIds()
    {
        if(!$this->propertyFields && !$this->skuPropertyFields) {
            return [];
        }

        $query = null;
        if($this->propertyFields ||($this->propertyFields && $this->skuPropertyFields)) {
            $query = $this->getQueryElementProperty();
        } else {
            $query = $this->getQueryOnlySkuElementProperty();
        }

        $query->setSelect(['IBLOCK_ELEMENT_ID']);
        $query->setGroup(['IBLOCK_ELEMENT_ID']);
        $query->setOrder([]);

        $result = $query->exec()->fetchAll();

        return array_column($result, 'IBLOCK_ELEMENT_ID');
    }

    public function setSectionMargin($sectionLeftMargin, $sectionRightMargin)
    {
        $this->sectionLeftMargin = $sectionLeftMargin;
        $this->sectionRightMargin = $sectionRightMargin;
    }

    /**
     * @param array $sectionMargins (0 => ['LEFT_MARGIN' => int, 'RIGHT_MARGIN' => int], ...)
     */
    public function setSectionMargins(array $sectionMargins)
    {
        $this->sectionMargins = $sectionMargins;
    }

    public function setIncludeSubsection($value)
    {
        $this->isIncludeSubsection = $value === false ? false : true;
    }

    public function getConditionValues($condition = null)
    {
        if($condition) {
            $this->setCondition($condition);
        }

        if (!$this->condition) {
            return [];
        }

        $conditionTree = new ConditionTree();

        foreach ($this->getConditionControlBuilds() as $build) {
            $conditionTree->addControlBuild($build);
        }

        if($this->isCatalogModule) {
           $conditionTree->addControlBuild(new Controls\CatalogGroupBuildControls());
        }

        $conditionTree->init(BT_COND_MODE_PARSE, ConditionTree::BT_COND_BUILD_SMARTSEO, []);

        if($this->needParse) {
            $this->condition = $conditionTree->parse($this->condition);
        }

        $result = $conditionTree->getConditionValues(
            $this->condition
        );

        return $result;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors ? true : false;
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

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    protected function addControlBuild(\CGlobalCondCtrl $controlBuild)
    {
        $this->builds[uniqid()] = $controlBuild;

        return $this;
    }

    protected function getConditionControlBuilds()
    {
        if(!$this->builds) {
            $this->addControlBuild(new Controls\GroupBuildControls());
            $this->addControlBuild(new Controls\IblockPropertyBuildControls(
                $this->iblockId
          ));
        }

        return $this->builds;
    }

    private function eachConditionValues()
    {
        foreach ($this->getConditionValues() as $conditionItem) {
            if ($conditionItem['ENTITY'] == 'ELEMENT_PROPERTY') {
                $_property = [
                    'PROPERTY_ID' => $conditionItem['PROPERTY_ID'],
                    'PROPERTY_CODE' => $conditionItem['PROPERTY_CODE'],
                    'PROPERTY_NAME' => $conditionItem['PROPERTY_NAME'],
                    'PROPERTY_TYPE' => $conditionItem['PROPERTY_TYPE'],
                    'PROPERTY_SORT' => $conditionItem['PROPERTY_SORT'],
                    'PROPERTY_DISPLAY_TYPE' => $conditionItem['PROPERTY_DISPLAY_TYPE'],
                    'IBLOCK_ID' => $conditionItem['PROPERTY_IBLOCK_ID'],
                    'LINK_IBLOCK_ID' => $conditionItem['PROPERTY_LINK_IBLOCK_ID'],
                    'CONDITIONS' => $conditionItem['CONDITIONS'],
                    'USER_TYPE' => $conditionItem['USER_TYPE'],
                    'USER_TYPE_SETTINGS' => $conditionItem['USER_TYPE_SETTINGS'],
                    'VALUES' => $conditionItem['VALUES'],
                ];

                if ($this->skuIblockId && $_property['IBLOCK_ID'] == $this->skuIblockId) {
                    $this->skuPropertyFields['SKU_' . $_property['PROPERTY_ID']] = $_property;
                } else {
                    $this->propertyFields['P_' . $_property['PROPERTY_ID']] = $_property;
                }
            }

            if ($conditionItem['ENTITY'] == 'CATALOG_GROUP') {
                $_catalogGroup = [
                    'CATALOG_GROUP_ID' => $conditionItem['CATALOG_GROUP_ID'],
                    'CATALOG_GROUP_NAME' => $conditionItem['CATALOG_GROUP_NAME'],
                    'CATALOG_GROUP_BASE' => $conditionItem['CATALOG_GROUP_BASE'],
                    'CONDITIONS' => $conditionItem['CONDITIONS'],
                    'VALUES' => $conditionItem['VALUES'],
                ];

                $this->catalogGroups['CATALOG_GROUP_' . $conditionItem['CATALOG_GROUP_ID']] = $_catalogGroup;
            }
        }
    }

    private function getQueryElementProperty()
    {
        if(!$this->getAllPropertyFields()) {
            return null;
        }

        $query = \Bitrix\Iblock\ElementPropertyTable::query();

        $querySelect = [];

        $whereByJoinProperty = \Bitrix\Main\Entity\Query::filter();
        $whereByJoinProperty->logic('and');

        $whereByProperty = \Bitrix\Main\Entity\Query::filter();
        $whereByProperty->logic('or');

        foreach ($this->getPropertyFields() as $property) {
            $_alias = $property['PROPERTY_ID'];

            $querySelect['PROPERTY_' . $property['PROPERTY_ID']] = $_alias . '.VALUE';

            $_whereRangeNumber = \Bitrix\Main\Entity\Query::filter();

            foreach ($property['CONDITIONS'] as $condition) {
                if ($property['PROPERTY_TYPE'] == 'N') {
                    $this->appendWhereByPropertyCondition($_whereRangeNumber, $property['PROPERTY_ID'], $condition, $_alias);
                    $this->appendWhereByPropertyCondition($_whereRangeNumber, $property['PROPERTY_ID'], $condition);
                } else {
                    $this->appendWhereByPropertyCondition($whereByJoinProperty, $property['PROPERTY_ID'], $condition, $_alias);
                    $this->appendWhereByPropertyCondition($whereByProperty, $property['PROPERTY_ID'], $condition);
                }
            }

            $query->registerRuntimeField(
              (new \Bitrix\Main\ORM\Fields\Relations\Reference(
              $_alias, \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.ELEMENT.ID')
              ))->configureJoinType('inner')
            );

            if ($property['PROPERTY_TYPE'] == 'N') {
                $whereByJoinProperty->where($_whereRangeNumber);
                $whereByProperty->where($_whereRangeNumber);
            }
        }

        if($this->getSkuPropertyFields()) {
            $query->registerRuntimeField(
              (new \Bitrix\Main\ORM\Fields\Relations\Reference(
              'ONLY_SKU', \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.VALUE', 'this.ELEMENT.ID')
                ->where('ref.IBLOCK_PROPERTY_ID', '=', $this->skuPropertyId)
              ))->configureJoinType('left')
            );

            foreach ($this->getSkuPropertyFields() as $property) {
                $_alias = $property['PROPERTY_ID'];

                $querySelect['PROPERTY_' . $property['PROPERTY_ID']] = $_alias . '.VALUE';

                $_whereRangeNumber = \Bitrix\Main\Entity\Query::filter();

                foreach ($property['CONDITIONS'] as $condition) {
                    if ($property['PROPERTY_TYPE'] == 'N') {
                        $this->appendWhereByPropertyCondition($_whereRangeNumber, $property['PROPERTY_ID'], $condition, $_alias);
                        $this->appendWhereByPropertyCondition($_whereRangeNumber, $property['PROPERTY_ID'], $condition);
                    } else {
                        $this->appendWhereByPropertyCondition($whereByJoinProperty, $property['PROPERTY_ID'], $condition, $_alias);
                        $this->appendWhereByPropertyCondition($whereByProperty, $property['PROPERTY_ID'], $condition);
                    }
                }

                if ($property['PROPERTY_TYPE'] == 'N') {
                    $whereByJoinProperty->where($_whereRangeNumber);
                    $whereByProperty->where($_whereRangeNumber);
                }

                $query->registerRuntimeField(
                  (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                  $_alias, \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.ONLY_SKU.IBLOCK_ELEMENT_ID')
                  ))->configureJoinType('inner')
                );
            }
        }

        $whereByCatalogGroup = null;
        if ($this->getCatalogGroupPropertyFields()) {
            $whereByCatalogGroup = \Bitrix\Main\Entity\Query::filter();

            foreach ($this->getCatalogGroupPropertyFields() as $property) {
                $_alias = 'catalog_price_' . $property['CATALOG_GROUP_ID'];

                foreach ($property['CONDITIONS'] as $condition) {
                    $this->appendWhereByCatalogGroupCondition($whereByCatalogGroup, $property['CATALOG_GROUP_ID'], $condition, $_alias);
                }

                $query->registerRuntimeField(
                  (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                  $_alias, \Bitrix\Catalog\PriceTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.PRODUCT_ID', 'this.ELEMENT.ID')
                  ))->configureJoinType('inner')
                );
            }
        }

        $query->setSelect($querySelect);
        $query->setOrder(array_values($querySelect));
        $query->setGroup(array_values($querySelect));

        $query->where($whereByJoinProperty);
        $query->where($whereByProperty);
        $query->where('ELEMENT.IBLOCK_ID', '=', $this->iblockId);

        if($whereByCatalogGroup) {
            $query->where($whereByCatalogGroup);
        }

        $this->appendWhereBySection($query);

        return $query;
    }

    private function getQueryOnlySkuElementProperty()
    {
        if(!$this->getSkuPropertyFields()) {
            return null;
        }

        $query = \Bitrix\Iblock\ElementPropertyTable::query();

        $querySelect = [];

        $whereByJoinProperty = \Bitrix\Main\Entity\Query::filter();
        $whereByJoinProperty->logic('and');

        $whereByProperty = \Bitrix\Main\Entity\Query::filter();
        $whereByProperty->logic('or');

        $query->registerRuntimeField(
          (new \Bitrix\Main\ORM\Fields\Relations\Reference(
          'ONLY_SKU', \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.IBLOCK_ELEMENT_ID')
            ->where('ref.IBLOCK_PROPERTY_ID', '=', $this->skuPropertyId)
          ))->configureJoinType('left')
        );

        $query->registerRuntimeField(
          (new \Bitrix\Main\ORM\Fields\Relations\Reference(
          'PARENT_ELEMENT', \Bitrix\Iblock\ElementTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.ID', 'this.ONLY_SKU.VALUE')
          ))->configureJoinType('left')
        );

        $query->registerRuntimeField(
          (new \Bitrix\Main\ORM\Fields\Relations\Reference(
          'SECTION_PARENT_ELEMENT', \Bitrix\Iblock\SectionTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.ID', 'this.PARENT_ELEMENT.IBLOCK_SECTION_ID')
          ))->configureJoinType('left')
        );

        foreach ($this->getSkuPropertyFields() as $property) {
            $_alias = $property['PROPERTY_ID'];

            $querySelect['PROPERTY_' . $property['PROPERTY_ID']] = $_alias . '.VALUE';

            $_whereRangeNumber = \Bitrix\Main\Entity\Query::filter();

            foreach ($property['CONDITIONS'] as $condition) {
                if ($property['PROPERTY_TYPE'] == 'N') {
                    $this->appendWhereByPropertyCondition($_whereRangeNumber, $property['PROPERTY_ID'], $condition, $_alias);
                    $this->appendWhereByPropertyCondition($_whereRangeNumber, $property['PROPERTY_ID'], $condition);
                } else {
                    $this->appendWhereByPropertyCondition($whereByJoinProperty, $property['PROPERTY_ID'], $condition, $_alias);
                    $this->appendWhereByPropertyCondition($whereByProperty, $property['PROPERTY_ID'], $condition);
                }
            }

            if ($property['PROPERTY_TYPE'] == 'N') {
                $whereByJoinProperty->where($_whereRangeNumber);
                $whereByProperty->where($_whereRangeNumber);
            }

            $query->registerRuntimeField(
              (new \Bitrix\Main\ORM\Fields\Relations\Reference(
              $_alias, \Bitrix\Iblock\ElementPropertyTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.ONLY_SKU.IBLOCK_ELEMENT_ID')
              ))->configureJoinType('inner')
            );
        }

        $whereByCatalogGroup = null;
        if ($this->getCatalogGroupPropertyFields()) {
            $whereByCatalogGroup = \Bitrix\Main\Entity\Query::filter();

            foreach ($this->getCatalogGroupPropertyFields() as $property) {
                $_alias = 'catalog_price_' . $property['CATALOG_GROUP_ID'];

                foreach ($property['CONDITIONS'] as $condition) {
                    $this->appendWhereByCatalogGroupCondition($whereByCatalogGroup, $property['CATALOG_GROUP_ID'], $condition, $_alias);
                }

                $query->registerRuntimeField(
                  (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                  $_alias, \Bitrix\Catalog\PriceTable::class, \Bitrix\Main\ORM\Query\Join::on('ref.PRODUCT_ID', 'this.ONLY_SKU.IBLOCK_ELEMENT_ID')
                  ))->configureJoinType('inner')
                );
            }
        }

        $query->setSelect($querySelect);
        $query->setOrder(array_values($querySelect));
        $query->setGroup(array_values($querySelect));

        $query->where($whereByJoinProperty);
        $query->where($whereByProperty);
        $query->where('PARENT_ELEMENT.IBLOCK_ID', '=', $this->iblockId);

        if($whereByCatalogGroup) {
            $query->where($whereByCatalogGroup);
        }

        $this->appendWhereBySection($query, $isOnlySku = true);

        return $query;
    }

    private function appendWhereByPropertyCondition(&$query, $propertyId, $condition, $alias = '')
    {
        if ($alias) {
            $alias = $alias . '.';
        }

        $_values = array_filter($condition['VALUES']);

        switch ($condition['LOGIC']) {
            case self::BT_COND_LOGIC_EQ :
                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId);

                if($_values) {
                    $_query->whereIn($alias . 'VALUE', $_values);
                }

                break;
            case self::BT_COND_LOGIC_NOT_EQ :
                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId);

                if($_values) {
                    $_query->whereNotIn($alias . 'VALUE', $_values);
                } else {
                    $_query->whereNull($alias . 'VALUE');
                }

                break;
            case self::BT_COND_LOGIC_CONT :
                $_queryLike = \Bitrix\Main\Entity\Query::filter();
                $_queryLike->logic('OR');

                if($_values) {
                    foreach ($_values as $_value) {
                        $_queryLike->whereLike($alias . 'VALUE', '%' . $_value . '%');
                    }
                } else {
                    $_queryLike->whereNotNull($alias . 'VALUE');
                }

                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($_queryLike);

                break;

            case self::BT_COND_LOGIC_NOT_CONT :
                $_queryLike = \Bitrix\Main\Entity\Query::filter();
                $_queryLike->logic('OR');

                if($_values) {
                    foreach ($_values as $_value) {
                        $_queryLike->whereNotLike($alias . 'VALUE', '%' . $_value . '%');
                    }
                } else {
                     $_query->whereNull($alias . 'VALUE');
                }

                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($_queryLike);

                break;

            case self::BT_COND_LOGIC_EGR :
                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($alias . 'VALUE_NUM', '>=', min($_values));

                break;
            case self::BT_COND_LOGIC_ELS :
                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($alias . 'VALUE_NUM', '<=', max($_values));

                break;
            default:
                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query
                  ->where($alias . 'IBLOCK_PROPERTY_ID', '=', $propertyId)
                  ->where($alias . 'VALUE', 'in', $_values);

                break;
        }

        $query->where($_query);
    }

    private function appendWhereByCatalogGroupCondition(&$query, $catalogGroupId, $condition, $alias = '')
    {
        if ($alias) {
            $alias = $alias . '.';
        }

        $_values = array_filter($condition['VALUES']);

        switch ($condition['LOGIC']) {
            case self::BT_COND_LOGIC_EGR :
                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query
                  ->where($alias . 'CATALOG_GROUP_ID', '=', $catalogGroupId)
                  ->where($alias . 'PRICE', '>=', min($_values));

                break;
            case self::BT_COND_LOGIC_ELS :
                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query
                  ->where($alias . 'CATALOG_GROUP_ID', '=', $catalogGroupId)
                  ->where($alias . 'PRICE', '<=', max($_values));

                break;
            default:
                $_query = \Bitrix\Main\Entity\Query::filter();
                $_query
                  ->where($alias . 'CATALOG_GROUP_ID', '=', $catalogGroupId)
                  ->where($alias . 'PRICE', 'in', $_values);

                break;
        }

        $query->where($_query);
    }

    private function appendWhereBySection(&$query, $isOnlySku = false)
    {
        $sectionMargins =  [];

        $entityAlias = 'ELEMENT.IBLOCK_SECTION';

        if($isOnlySku) {
            $entityAlias = 'SECTION_PARENT_ELEMENT';
        }

        if($this->sectionLeftMargin && $this->sectionRightMargin) {
            $sectionMargins[] = [
                'LEFT_MARGIN' => $this->sectionLeftMargin,
                'RIGHT_MARGIN' => $this->sectionRightMargin,
            ];
        }

        if(!$sectionMargins && !$this->sectionMargins) {
            return;
        }

        $sectionMargins = array_merge($sectionMargins, $this->sectionMargins);

        $whereBySection = \Bitrix\Main\Entity\Query::filter();
        $whereBySection->logic('or');

        foreach ($sectionMargins as $sectionMargin) {
            $whereBySection->where(
              \Bitrix\Main\Entity\Query::filter()->where([
                  [$entityAlias . '.LEFT_MARGIN', $this->isIncludeSubsection ? '>=' : '=', $sectionMargin['LEFT_MARGIN']],
                  [$entityAlias . '.RIGHT_MARGIN', $this->isIncludeSubsection ? '<=' : '=', $sectionMargin['RIGHT_MARGIN']],
              ])
            );
        }

        if($whereBySection) {
            $query->where($whereBySection);
        }
    }

    private function loadSkuIblockData($iblockId)
    {
        if(!$this->isCatalogModule) {
            return;

        }

        $row = \Bitrix\Catalog\CatalogIblockTable::getRow([
              'select' => [
                  'IBLOCK_ID',
                  'SKU_PROPERTY_ID',
               ],
              'filter' => [
                  'PRODUCT_IBLOCK_ID' => $iblockId,
              ],
          ]);

        if(!$row) {
            return;
        }

        $this->skuIblockId = $row['IBLOCK_ID'];
        $this->skuPropertyId = $row['SKU_PROPERTY_ID'];
    }

}
