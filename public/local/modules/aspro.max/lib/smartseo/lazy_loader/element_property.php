<?php

namespace Aspro\Max\Smartseo\LazyLoader\ElementProperty;

use \Bitrix\Iblock\Template\Entity\LazyValueLoader;

class ElementPropertyUserField extends LazyValueLoader
{

    /** @var array  */
    private $property = null;
    private $params = [];

    /**
     * 
     * @param integer $key  Iblock element identifier.
     * @param array|mixed $property Iblock property array.
     * @param array $params Parameters ([
     *  'IS_DISPLAY_KEY_IF_EMPTY'(Y|N)  - Show key if the value is empty
     * ])
     */
    function __construct($key, $property, $params = [])
    {
        parent::__construct($key);

        $this->params = $params;

        if (is_array(($property))) {
            $this->property = $property;
        }
    }

    /**
     * Actual work method which have to retrieve data from the DB.
     *
     * @return mixed
     */
    protected function load()
    {
        if(!$this->key) {
            return '';
        }
        
        $propertyFormatFunction = $this->getFormatFunction();
        if ($propertyFormatFunction) {
            $value = call_user_func_array($propertyFormatFunction, [
                $this->property,
                ['VALUE' => $this->key],
                ['MODE' => 'ELEMENT_TEMPLATE'],
              ]
            );

            if (!$value && $this->params['IS_DISPLAY_KEY_IF_EMPTY'] === 'Y') {
                return $this->key;
            }

            return $value;
        } else {
            return $this->key;
        }
    }

    /**
     * Retruns GetPublicViewHTML handler function for $this->property.
     * Returns false if no handler defined.
     *
     * @return callable|false
     */
    protected function getFormatFunction()
    {
        static $propertyFormatFunction = array();
        if (!isset($propertyFormatFunction[$this->property['ID']])) {
            $propertyFormatFunction[$this->property['ID']] = false;
            if ($this->property && strlen($this->property['USER_TYPE'])) {
                $propertyUserType = \CIBlockProperty::getUserType($this->property['USER_TYPE']);
                if (
                  array_key_exists('GetPublicViewHTML', $propertyUserType) && is_callable($propertyUserType['GetPublicViewHTML'])
                ) {
                    $propertyFormatFunction[$this->property['ID']] = $propertyUserType['GetPublicViewHTML'];
                }
            }
        }
        return $propertyFormatFunction[$this->property['ID']];
    }

}

class ElementPropertyEnum extends LazyValueLoader
{

    private $params = [];

    /**
     * @param integer $key ID PropertyEnumerationTable.
     * @param array $params Parameters ([
     *  'IS_DISPLAY_KEY_IF_EMPTY'(Y|N)  - Show key if the value is empty
     * ])
     */
    function __construct($key, $params = [])
    {
        parent::__construct($key);

        $this->params = $params;
    }

    /**
     * Actual work method which have to retrieve data from the DB.
     *
     * @return mixed
     */
    protected function load()
    {
        if(!$this->key) {
            return '';
        }
        
        $enumList = \Bitrix\Iblock\PropertyEnumerationTable::getList([
              'select' => ['VALUE'],
              'filter' => ['=ID' => $this->key],
        ]);
        $enum = $enumList->fetch();
        if ($enum) {
            return $enum['VALUE'];
        } elseif ($this->params['IS_DISPLAY_KEY_IF_EMPTY'] === 'Y') {
            return $this->key;
        } else {
            return '';
        }
    }

}

class ElementPropertyElement extends LazyValueLoader
{

    private $params = [];

    /**
     * @param integer $key ID ElementTable.
     * @param array $params Parameters ([
     *  'IS_DISPLAY_KEY_IF_EMPTY'(Y|N)  - Show key if the value is empty
     * ])
     */
    function __construct($key, $params = [])
    {
        parent::__construct($key);

        $this->params = $params;
    }

    /**
     * Actual work method which have to retrieve data from the DB.
     *
     * @return mixed
     */
    protected function load()
    {
        if(!$this->key) {
            return '';
        }
        
        $elementList = \Bitrix\Iblock\ElementTable::getList([
              'select' => ['NAME'],
              'filter' => ['=ID' => $this->key],
        ]);
        $element = $elementList->fetch();
        if ($element) {
            return $element['NAME'];
        } elseif ($this->params['IS_DISPLAY_KEY_IF_EMPTY'] === 'Y') {
            return $this->key;
        } else {
            return '';
        }
    }

}

class ElementPropertySection extends LazyValueLoader
{

    /**
     * Actual work method which have to retrieve data from the DB.
     *
     * @return mixed
     */
    protected function load()
    {
        $sectionList = \Bitrix\Iblock\SectionTable::getList([
              'select' => ['NAME'],
              'filter' => ['=ID' => $this->key],
        ]);
        $section = $sectionList->fetch();
        if ($section)
            return $section['NAME'];
        else
            return '';
    }

}
