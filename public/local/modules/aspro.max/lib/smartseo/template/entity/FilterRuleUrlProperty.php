<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class FilterRuleUrlProperty extends Iblock\Template\Entity\Base
{
    const VALUES_SLICE = 3;

    public function __construct($id)
    {
        parent::__construct(0);
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

    public function setProperties(array $properties)
    {
        foreach ($properties as $property) {
            if (!$this->fieldMap[$property['PROPERTY_CODE']]) {
                $this->fieldMap[strtolower($property['PROPERTY_CODE'])] = $property['PROPERTY_ID'];
            }

            $this->fields[$property['PROPERTY_ID']] = $property['VALUES']['DISPLAY'];
        }
    }

    protected function loadFromDatabase()
    {
        return is_array($this->fields);
    }

}
