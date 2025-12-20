<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Iblock;

class SeoText extends Iblock\Template\Entity\Base
{

    protected $iblock = null;
    protected $sections = null;
    protected $section = null;
    protected $properties = null;

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->fieldMap = [
            'id' => 'ID',
            'name' => 'NAME',
            'ID' => 'ID',
            'IBLOCK_ID' => 'IBLOCK_ID',
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
                    if ($this->fields['ID'] > 0) {
                        $this->sections = new SeoTextIblockSections($this->fields['ID'], $limit = 10);
                    }
                }

                if ($this->sections) {
                    return $this->sections;
                }

                break;

            case 'section':
                if (!$this->section && $this->loadFromDatabase()) {
                    if ($this->fields['ID'] > 0) {
                        $this->section = new SeoTextIblockSections($this->fields['ID']);
                    }
                }

                if ($this->section) {
                    return $this->section;
                }

                break;

             case 'property' || 'sku_property' :
                if (!$this->property && $this->loadFromDatabase()) {
                    if ($this->fields['ID'] > 0 && $this->fields['CONDITION']) {
                        $this->property = new SeoTextElementProperties(0);
                        $this->property->setIblockId($this->fields['IBLOCK_ID']);
                        $this->property->setCondition($this->fields['CONDITION']);
                        $this->property->setSectionMargins($this->fields['SECTION_MARGINS']);
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

        if ($fields['ID'] > 0) {
            $this->fields['ID'] = $fields['ID'];
        }

        if ($fields['IBLOCK_ID'] > 0) {
            $this->iblock = new Iblock\Template\Entity\Iblock($fields['IBLOCK_ID']);
        }

        if ($fields['SECTIONS']) {
            $this->sections = new SeoTextIblockSections(0);
            $this->sections->setFields([
                'SECTIONS' => $fields['SECTIONS']
            ]);

            $this->section = new SeoTextIblockSections(0);
            $this->section->setFields([
                'SECTION' => $fields['SECTIONS'][0]
            ]);
        }

        if ($fields['IBLOCK_ID'] && $fields['CONDITION']) {
            $this->property = new SeoTextElementProperties(0);
            $this->property->setIblockId($fields['IBLOCK_ID']);
            $this->property->setCondition($fields['CONDITION']);
           
            if($fields['SECTIONS']) {
                $sectionIds = array_column($fields['SECTIONS'], 'ID');
                $this->property->setSectionIds($sectionIds ?: []);
            }
        }
    }

    protected function loadFromDatabase()
    {
        if (!isset($this->fields)) {
            if(!$this->id) {
                return false;
            }

            $rows = Smartseo\Models\SmartseoSeoTextTable::getList([
                  'select' => [
                      'ID',
                      'NAME',
                      'IBLOCK_ID',
                      'CONDITION_TREE',
                      'IBLOCK_SECTION_ID' => 'IBLOCK_SECTIONS.SECTION_ID',
                      'REF_LEFT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.LEFT_MARGIN',
                      'REF_RIGHT_MARGIN' => 'IBLOCK_SECTIONS.SECTION.RIGHT_MARGIN',
                  ],
                  'filter' => [
                      '=ID' => $this->id
                  ],
                  'cache' => [
                      'ttl' => SettingSmartseo::getInstance()->getCacheSEOTemplate(),
                      'cache_joins' => true,
                  ],
              ])->fetchAll();

            $result = [];
            foreach ($rows as $row) {
                if (!$result) {
                    $result = [
                        'ID' => $row['ID'],
                        'NAME' => $row['NAME'],
                        'IBLOCK_ID' => $row['IBLOCK_ID'],
                        'CONDITION' => $row['CONDITION_TREE'],
                        'SECTIONS' => [
                            $row['IBLOCK_SECTION_ID']
                        ],
                        'SECTION_MARGINS' => [
                            [
                                'LEFT_MARGIN' => $row['REF_LEFT_MARGIN'],
                                'RIGHT_MARGIN' => $row['REF_RIGHT_MARGIN'],
                            ]
                        ]
                    ];
                } else {
                    $result['SECTIONS'][] = $row['IBLOCK_SECTION_ID'];
                    $result['SECTION_MARGINS'][] = [
                        'LEFT_MARGIN' => $row['REF_LEFT_MARGIN'],
                        'RIGHT_MARGIN' => $row['REF_RIGHT_MARGIN'],
                    ];
                }
            }

            $this->fields = $result;
        }

        return is_array($this->fields);
    }

}
