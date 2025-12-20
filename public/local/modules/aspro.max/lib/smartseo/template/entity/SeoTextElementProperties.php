<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class SeoTextElementProperties extends Iblock\Template\Entity\Base
{
    const LIMIT_DISPLAY_VALUES = 1;

    private $iblockId = null;

    /** @var string  */
    private $condition = null;

    private $sectionIds = [];

    private $sectionMargins = [];

    /** @var \Aspro\Max\Smartseo\Condition\ConditionResult */
    private $conditionResult = null;

    public function __construct($seotextId)
    {
        parent::__construct($seotextId);
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

    public function setSectionIds(array $values)
    {
       $this->sectionIds = $values;
    }

    public function setSectionMargins(array $values)
    {
       $this->sectionMargins = $values;
    }

    protected function loadFromDatabase()
    {

        if (isset($this->fields)) {
            return is_array($this->fields);
        }

        if (!$this->condition || !$this->iblockId) {
            if (!$this->loadDataBySeotext()) {
                return false;
            }
        }

        $this->conditionResult = new \Aspro\Max\Smartseo\Condition\ConditionResult($this->iblockId, $this->condition, [
            new Smartseo\Condition\Controls\GroupBuildControls(),
            new Smartseo\Condition\Controls\IblockPropertyBuildControls($this->iblockId, [
                'ONLY_PROPERTY_SMART_FILTER' => 'N',
                'SHOW_PROPERTY_SKU' => 'N',
              ])
        ]);

        $this->conditionResult->setSectionMargins($this->getSectionMargins());

        foreach ($this->conditionResult->getAllPropertyFields() as $field) {
            if (!$this->fieldMap[$field['PROPERTY_CODE']]) {
                $this->fieldMap[strtolower($field['PROPERTY_CODE'])] = $field['PROPERTY_ID'];
            }

            $values = [];
            $limit = 0;
            foreach ($this->conditionResult->getResult() as $resultItem) {
                if (self::LIMIT_DISPLAY_VALUES <= $limit) {
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

    protected function loadDataBySeotext()
    {
        if(!$this->id) {
            return;
        }

        $row = Smartseo\Models\SmartseoSeoTextTable::getRow([
              'select' => [
                  'ID',
                  'CONDITION_TREE',
                  'IBLOCK_ID',
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
            return false;
        }

        $this->iblockId = $row['IBLOCK_ID'];
        $this->condition = $row['CONDITION_TREE'];

        return true;
    }

    protected function getSectionMargins()
    {
        if($this->sectionMargins) {
            return $this->sectionMargins;
        }

        if ($this->sectionIds) {
            return \Bitrix\Iblock\SectionTable::getList([
                  'select' => [
                      'LEFT_MARGIN',
                      'RIGHT_MARGIN'
                  ],
                  'filter' => [
                      '=ID' => $this->sectionIds,
                  ],
                  'cache' => [
                      'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  ],
              ])->fetchAll();
        }

        if ($this->id) {
            return Smartseo\Models\SmartseoSeoTextTable::getList([
                  'select' => [
                      'LEFT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.LEFT_MARGIN',
                      'RIGHT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.RIGHT_MARGIN',
                  ],
                  'filter' => [
                      '=ID' => $this->id
                  ],
                  'cache' => [
                      'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                      'cache_joins' => true,
                  ],
              ])->fetchAll();
        }

        return [];
    }

}
