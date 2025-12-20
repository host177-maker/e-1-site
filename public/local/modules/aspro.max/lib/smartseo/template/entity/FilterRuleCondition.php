<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class FilterRuleCondition extends Iblock\Template\Entity\Base
{

    /** @var \Bitrix\Iblock\Template\Entity\Iblock */
    protected $iblock = null;

    /** @var \Aspro\Max\Smartseo\Template\Entity\RuleElementSections */
    protected $sections = null;

    /** @var \Aspro\Max\Smartseo\Template\Entity\RuleElementSections */
    protected $section = null;

    /** @var \Aspro\Max\Smartseo\Template\Entity\PropertyCondition */
    protected $property = null;

    /** @var \Aspro\Max\Smartseo\Template\Entity\PriceCondition */
    protected $price = null;

    private $filterRuleId = null;
    private $iblockId = null;
    private $condition = [];

    public function __construct($filterConditionId)
    {
        parent::__construct($filterConditionId);

        $this->fieldMap = [
            'ID' => 'ID',
            'FILTER_RULE_ID' => 'FILTER_RULE_ID',
            'IBLOCK_ID' => 'IBLOCK_ID',
            'CONDITION' => 'CONDITION',
        ];
    }

    public function resolve($entity)
    {
        switch ($entity) {
            case 'iblock':
                if (!$this->iblock && $this->loadFromDatabase()) {
                    if ($this->fields['IBLOCK_ID'] > 0) {
                        $this->iblock = new Iblock\Template\Entity\Iblock($this->fields['IBLOCK_ID']);
                    }
                }

                if ($this->iblock) {
                    return $this->iblock;
                }

                break;

            case 'sections':
                if (!$this->sections && $this->loadFromDatabase()) {
                    if ($this->fields['FILTER_RULE_ID'] > 0) {
                        $this->sections = new FilterRuleIblockSections($this->fields['FILTER_RULE_ID'], $limit = 10);
                    }
                }

                if ($this->sections) {
                    return $this->sections;
                }

                break;

            case 'section':
                if (!$this->section && $this->loadFromDatabase()) {
                    if ($this->fields['FILTER_RULE_ID'] > 0) {
                        $this->section = new FilterRuleIblockSections($this->fields['FILTER_RULE_ID']);
                    }
                }

                if ($this->section) {
                    return $this->section;
                }

                break;

            case 'price':
                if (!$this->price && $this->loadFromDatabase()) {
                    if (\Bitrix\Main\Loader::includeModule('catalog') && $this->fields['ID'] > 0 && $this->fields['CONDITION']) {
                        $this->price = new FilterRuleConditionPrice(0);

                        $this->price->setParams([
                            'FILTER_RULE_ID' => $this->fields['ID'],
                            'IBLOCK_ID' => $this->fields['IBLOCK_ID'],
                            'CONDITION' => $this->fields['CONDITION'],
                        ]);
                    }
                }

                if ($this->price) {
                    return $this->price;
                }

                break;

            case 'property' || 'sku_property' :
                if (!$this->property && $this->loadFromDatabase()) {
                    if ($this->fields['ID'] > 0 && $this->fields['CONDITION']) {
                        $this->property = new FilterRuleConditionProperty($this->fields['ID']);
                    }
                }

                if ($this->property) {
                    return $this->property;
                }

                break;

            default:
                break;
        }

        return parent::resolve($entity);
    }

    public function setFields(array $fields)
    {
        parent::setFields($fields);

        if (!is_array($this->fields)) {
            return;
        }

        if ($fields['IBLOCK_ID'] > 0) {
            $this->iblock = new Iblock\Template\Entity\Iblock($fields['IBLOCK_ID']);
        }

        if ($fields['SECTIONS']) {
            $this->sections = new FilterRuleIblockSections(0);
            $this->sections->setFields($fields);
        }

        if ($fields['FILTER_RULE_ID']) {
            $this->filterRuleId = $fields['FILTER_RULE_ID'];
        }

        if ($fields['CONDITION']) {
            $this->condition = $fields['CONDITION'];
        }

        if ($this->filterRuleId && !$this->iblockId) {
            $_filterRule = $this->getDataFilterRule();
            $this->iblockId = $_filterRule['IBLOCK_ID'];
        }

        if ($this->filterRuleId && $this->iblockId && $this->condition) {
            $this->property = new FilterRuleConditionProperty(0);
            $this->property->setParams([
                'FILTER_RULE_ID' => $this->filterRuleId,
                'IBLOCK_ID' => $this->iblockId,
                'CONDITION' => $this->condition,
            ]);
        }

        if ($this->iblockId && $this->condition) {
            $this->price = new FilterRuleConditionPrice(0);
            $this->price->setParams([
                'FILTER_RULE_ID' => $this->filterRuleId,
                'IBLOCK_ID' => $this->iblockId,
                'CONDITION' => $this->condition,
            ]);
        }
    }

    protected function loadFromDatabase()
    {
        if (isset($this->fields)) {
            return is_array($this->fields);
        }

        $row = Smartseo\Models\SmartseoFilterConditionTable::getRow([
              'select' => [
                  'ID',
                  'FILTER_RULE_ID',
                  'CONDITION_TREE',
                  'IBLOCK_ID' => 'FILTER_RULE.IBLOCK_ID',
              ],
              'filter' => [
                  '=ID' => $this->id,
              ],
              'cache' => [
                  'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  'cache_joins' => true,
              ],
        ]);

        $this->fields = [
            'ID' => $row['ID'],
            'FILTER_RULE_ID' => $row['FILTER_RULE_ID'],
            'IBLOCK_ID' => $row['IBLOCK_ID'],
            'CONDITION' => $row['CONDITION_TREE'],
        ];

        return true;
    }

    protected function getDataFilterRule()
    {
        $row = Smartseo\Models\SmartseoFilterRuleTable::getRow([
              'select' => [
                  'ID',
                  'IBLOCK_ID',
              ],
              'filter' => [
                  '=ID' => $this->filterRuleId
              ],
              'cache' => [
                  'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  'cache_joins' => true,
              ],
        ]);

        return $row;
    }

}
