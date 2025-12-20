<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class FilterRuleUrl extends Iblock\Template\Entity\Base
{
    /** @var \Bitrix\Iblock\Template\Entity\Iblock */
    protected $iblock = null;

    /** @var \Bitrix\Iblock\Template\Entity\Section */
    protected $section = null;

    /** @var \Aspro\Max\Smartseo\Template\Entity\PropertyUrlCondition */
    protected $property = null;

    public function __construct($filterConditionUrlId)
    {
        parent::__construct($filterConditionUrlId);

        $this->fieldMap = [
            'ID' => 'ID',
            'IBLOCK_ID' => 'IBLOCK_ID',
            'SECTION_ID' => $row['SECTION_ID'],
            'PROPERTIES' => 'PROPERTIES',
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

            case 'section':
                if (!$this->section && $this->loadFromDatabase()) {
                    if ($this->fields['SECTION_ID'] > 0) {
                        $this->section = new Iblock\Template\Entity\Section($this->fields['SECTION_ID']);
                    }
                }

                if ($this->section) {
                    return $this->section;
                }

                break;

            case 'property' || 'sku_property' :
                if (!$this->property && $this->loadFromDatabase()) {
                    if ($this->fields['PROPERTIES'] && is_array($this->fields['PROPERTIES'])) {
                        $this->property = new FilterRuleUrlProperty(0);
                        $this->property->setProperties($this->fields['PROPERTIES']);
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

        if ($fields['SECTION_ID']) {
            $this->section = new Iblock\Template\Entity\Section($fields['SECTION_ID']);
        }

        if ($fields['PROPERTIES'] && is_array($fields['PROPERTIES'])) {
            $this->property = new FilterRuleUrlProperty(0);
            $this->property->setProperties($fields['PROPERTIES']);
        }
    }

    protected function loadFromDatabase()
    {
        if (isset($this->fields)) {
            return is_array($this->fields);
        }

        $row = Smartseo\Models\SmartseoFilterConditionUrlTable::getRow([
              'select' => [
                  'ID',
                  'IBLOCK_ID',
                  'SECTION_ID',
                  'PROPERTIES',
              ],
              'filter' => [
                  '=ID' => $this->id,
              ],
              'cache' => [
                  'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                  'cache_joins' => true,
              ],
        ]);

        $properties = [];
        if(CheckSerializedData($row['PROPERTIES'])) {
            $properties = unserialize($row['PROPERTIES'], ['allowed_classes' => false]);
        }

        $this->fields = [
            'ID' => $row['ID'],
            'IBLOCK_ID' => $row['IBLOCK_ID'],
            'SECTION_ID' => $row['SECTION_ID'],
            'PROPERTIES' => $properties,
        ];

        return true;
    }

}
