<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class FilterRuleConditionProperty extends Iblock\Template\Entity\Base
{
    const LIMIT_DISPLAY_VALUES = 1;

    private $iblockId = null;
    private $filterConditionId = null;
    private $filterRuleId = null;

    /** @var string  */
    private $condition = null;

    /** @var \Aspro\Max\Smartseo\Condition\ConditionResult  */
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

        $this->loadRuleCondition();

        if (!$this->condition || !$this->iblockId || !$this->filterRuleId) {
            return false;
        }

        $this->conditionResult = new \Aspro\Max\Smartseo\Condition\ConditionResult($this->iblockId, $this->condition);
        $this->conditionResult->setSectionMargins($this->getSectionMargins());

        foreach ($this->conditionResult->getAllPropertyFields() as $field) {
            if (!$this->fieldMap[$field['PROPERTY_CODE']]) {
                $this->fieldMap[strtolower($field['PROPERTY_CODE'])] = $field['PROPERTY_ID'];
            }

            $values = [];
            $limit = 0;
            foreach ($this->conditionResult->getResult() as $resultItem) {
                if(self::LIMIT_DISPLAY_VALUES <= $limit) {
                    continue;
                }

                $value = $resultItem['PROPERTY_' . $field['PROPERTY_ID']];

                switch ($field['PROPERTY_TYPE']) {
                    case 'S' && $field['USER_TYPE'] :
                        $values[] = new Iblock\Template\Entity\ElementPropertyUserField($value, [
                            'ID' => $field['PROPERTY_ID'],
                            'USER_TYPE' => $field['USER_TYPE'],
                            'USER_TYPE_SETTINGS' => unserialize($field['USER_TYPE_SETTINGS'], ['allowed_classes' => false]),
                        ]);

                        $limit++;

                        break;

                    case 'E' :
                        $values[] = new Iblock\Template\Entity\ElementPropertyElement($value);

                        $limit++;

                        break;

                    case 'L' :
                        $values[] = new Iblock\Template\Entity\ElementPropertyEnum($value);

                        $limit++;

                        break;

                    case 'N' :
                        $values = array_column($field['LOGICS'], 'VALUE');

                        break;
                    default:
                        $values[] = $value;

                        $limit++;

                        break;
                }
            }

            $this->fields[$field['PROPERTY_ID']] = array_unique($values);
        }

        return is_array($this->fields);
    }

    protected function loadRuleCondition()
    {
        if(!$this->id) {
            return;
        }

        $row = Smartseo\Models\SmartseoFilterConditionTable::getRow([
              'select' => [
                  'ID',
                  'FILTER_RULE_ID',
                  'CONDITION_TREE',
                  'IBLOCK_ID' => 'FILTER_RULE.IBLOCK_ID',
              ],
              'filter' => [
                  '=ID' => $this->id
              ],
              'cache' => [
                  'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  'cache_joins' => true,
              ],
        ]);

        if (!$row) {
            return;
        }

        $this->filterConditionId = $row['ID'];
        $this->filterRuleId = $row['FILTER_RULE_ID'];
        $this->iblockId = $row['IBLOCK_ID'];
        $this->condition = $row['CONDITION_TREE'];
    }

    protected function getSectionMargins()
    {
        if (!$this->filterRuleId) {
            return [];
        }

        $result = Smartseo\Models\SmartseoFilterRuleTable::getList([
              'select' => [
                  'LEFT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.LEFT_MARGIN',
                  'RIGHT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.RIGHT_MARGIN',
              ],
              'filter' => [
                  '=ID' => $this->filterRuleId
              ],
              'cache' => [
                  'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  'cache_joins' => true,
              ],
          ])->fetchAll();

        return $result;
    }

}
