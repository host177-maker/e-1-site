<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use
    Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SeoTextPropertyMenuUI
{

    use BitrixCoreEntity;

    const DEFAULT_FUNCTION_FORMAT = "";

    const CATEGORY_SECTION = 'section';
    const CATEGORY_ELEMENT = 'element';

    protected $category = [];
    protected $categoryEnd = [];

    private $functionFormat;
    private $iblockId = null;
    private $iblock = null;
    private $propertyIds = [];
    private $sectionProperties = [];
    private $elementProperties = [];
    private $ingoreSectionPropertyIds = [];
    private $ingoreElementPropertyIds = [];

    function __construct()
    {

    }

    public function setIblockId($value)
    {
        $this->iblockId = $value;

        $this->iblock = $this->getIblockRow([
            'ID' => $this->iblockId,
          ], [
              'ID',
              'NAME',
              'CODE',
              'SECTION_PAGE_URL',
        ]);

        return $this;
    }

    public function setPropertyIds(array $value)
    {
        $this->propertyIds = $value;

        return $this;
    }

    public function getMenuItems($onlyCategory = [])
    {
        $this->category[self::CATEGORY_SECTION] = $this->getSectionMenuCategory();
        $this->category[self::CATEGORY_ELEMENT] = $this->getElementMenuCategory();

        $result = [];
        foreach ($this->category as $key => $value) {
            if ($onlyCategory && !in_array($key, $onlyCategory)) {
                continue;
            }

            $result = array_merge($result, $value);
        }

        return $result;
    }

    public function setFunctionFormat($value)
    {
        $this->functionFormat = $value;
    }

    public function getFunctionFormat()
    {
        return $this->functionFormat ?: self::DEFAULT_FUNCTION_FORMAT;
    }

    public function setSectionPropertyIngore(array $ids)
    {
        $this->ingoreSectionPropertyIds = $ids;
    }

    public function getSectionPropertyIngore()
    {
        return $this->ingoreSectionPropertyIds;
    }

    public function setElementPropertyIngore(array $ids)
    {
        $this->ingoreElementPropertyIds = $ids;
    }

    public function getElementPropertyIngore()
    {
        return $this->ingoreElementPropertyIds;
    }

    protected function getSectionMenuCategory()
    {
        if ($this->getSectionProperties()) {
            foreach ($this->getSectionProperties() as $sectionProperty) {
                $sectionCategory[] = [
                    'TEXT' => $sectionProperty['NAME'],
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $sectionProperty['ID'],
                    ]),
                ];
            }
        } else {
            $sectionCategory[] = [
                'TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_PROPERTY'),
            ];
        }

        return $sectionCategory;
    }

    protected function getElementMenuCategory()
    {
        if ($this->getElementProperties()) {
            foreach ($this->getElementProperties() as $elementProperty) {
                $elementCategory[] = [
                    'TEXT' => $elementProperty['NAME'],
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $elementProperty['ID'],
                    ]),
                ];
            }
        } else {
            $elementCategory[] = [
                'TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_PROPERTY'),
            ];
        }

        return $elementCategory;
    }

    private function getFunctionOnClick($params)
    {
        return vsprintf($this->getFunctionFormat(), $params);
    }

    private function getSectionProperties()
    {
        if (empty($this->iblockId)) {
            return [];
        }

        if ($this->sectionProperties) {
            return $this->sectionProperties;
        }

        $result = Smartseo\Models\SmartseoSeoTextPropertyTable::getSectionProperties($this->iblockId, $this->getSectionPropertyIngore());

        $this->sectionProperties = $result;

        return $result;
    }

    private function getElementProperties($propertyIds = [])
    {
        if (empty($this->iblockId)) {
            return [];
        }

        if ($this->elementProperties) {
            return $this->elementProperties;
        }

        $result = Smartseo\Models\SmartseoSeoTextPropertyTable::getElementProperties($this->iblockId, $this->getElementPropertyIngore());

        return $result;
    }
}
