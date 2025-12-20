<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class FilterRuleConditionPrice extends Iblock\Template\Entity\Base
{
    private $iblockId = null;
    private $filterConditionId = null;
    private $filterRuleId = null;
    private $condition = null;

    /** @var \Aspro\Max\Smartseo\Condition\ConditionResult */
    private $conditionResult = null;

    public function __construct($filterConditionId)
    {
        parent::__construct($filterConditionId);
    }

    public function resolve($entity)
    {
        return parent::resolve($entity);
    }

    public function setFields(array $fields)
    {
        parent::setFields($fields);

        if (!is_array($this->fields)) {
            return;
        }
    }

    public function setIblockId($value)
    {
        $this->iblockId = $value;
    }

    public function setCondition($value)
    {
        $this->condition = $value;
    }

    public function setParams(array $params)
    {
        if ($params['IBLOCK_ID'] > 0) {
            $this->iblockId = $params['IBLOCK_ID'];
        }

        if ($params['FILTER_RULE_ID'] > 0) {
            $this->filterRuleId = $params['FILTER_RULE_ID'];
        }

        if ($params['FILTER_CONDITION_ID'] > 0) {
            $this->filterConditionId = $params['FILTER_CONDITION_ID'];
        }

        if ($params['CONDITION']) {
            $this->condition = $params['CONDITION'];
        }
    }

    protected function loadFromDatabase()
    {
        if (isset($this->fields)) {
            return is_array($this->fields);
        }

        if (!$this->condition || !$this->iblockId) {
            return false;
        }

        $this->conditionResult = new \Aspro\Max\Smartseo\Condition\ConditionResult($this->iblockId, $this->condition);

        foreach ($this->conditionResult->getCatalogPricePropertyFields() as $field) {
            $values = null;

            if($field['LOGICS']) {
                $values = array_column($field['LOGICS'], 'VALUE');
            }

            if (!$this->fieldMap[$field['CATALOG_GROUP_NAME']]) {
                $this->fieldMap[strtolower($field['CATALOG_GROUP_NAME'])] = $field['CATALOG_GROUP_ID'];
            }

            $this->fields[$field['CATALOG_GROUP_ID']] = $values;
        }

        return is_array($this->fields);
    }
}
