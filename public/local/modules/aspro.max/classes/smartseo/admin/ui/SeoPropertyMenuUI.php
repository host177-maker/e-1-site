<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SeoPropertyMenuUI
{

    use BitrixCoreEntity;

    const DEFAULT_FUNCTION_FORMAT = "AsproUI.Form.ControlTemplateEngine.valueInputEntry('%s', '%s');";
    const CATEGORY_SECTION = 'section';
    const CATEGORY_PARENT = 'parent';
    const CATEGORY_IBLOCK = 'iblock';
    const CATEGORY_PROPERTIES = 'property';
    const CATEGORY_SKU_PROPERTIES = 'sku_property';
    const CATEGORY_FUNCTIONS = 'functions';

    protected $category = [];
    protected $categoryEnd = [];

    private $functionFormat;
    private $iblockId = null;
    private $sectionIds = [];
    private $propertyIds = [];
    private $sectionProperties = [];

    private $needSelectedProperties = false;

    function __construct()
    {

    }

    public function setIblockId($value)
    {
        $this->iblockId = $value;

        return $this;
    }

    public function setSectionIds(array $values)
    {
        $this->sectionIds = $values;

        return $this;
    }

    public function setPropertyIds(array $values)
    {
        $this->propertyIds = $values;
        $this->needSelectedProperties = true;

        return $this;
    }

    public function getMenuItems($controlId, $onlyCategory = [])
    {
        $this->category[self::CATEGORY_SECTION] = $this->getSectionMenuCategory($controlId);
        $this->category[self::CATEGORY_PARENT] = $this->getParentSectionMenuCategory($controlId);
        $this->category[self::CATEGORY_IBLOCK] = $this->getIblockMenuCategory($controlId);
        $this->category[self::CATEGORY_PROPERTIES] = $this->getPropertyMenuCategory($controlId);
        $this->category[self::CATEGORY_SKU_PROPERTIES] = $this->getSkuPropertyMenuCategory($controlId);
        $this->categoryEnd[self::CATEGORY_FUNCTIONS] = $this->getFunctionMenuCategory($controlId);

        $result = [];
        foreach ($this->category as $key => $value) {
            if ($onlyCategory && !in_array($key, $onlyCategory)) {
                continue;
            }

            if (!empty($value) && !empty($value['MENU'])) {
                $result[] = $value;
            }
        }

        if ($this->categoryEnd) {
            $result[] = [
                'SEPARATOR' => true,
            ];

            foreach ($this->categoryEnd as $key => $value) {
                if ($onlyCategory && !in_array($key, $onlyCategory)) {
                    continue;
                }

                if (!empty($value) && !empty($value['MENU'])) {
                    $result[] = $value;
                }
            }
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

    protected function getSectionMenuCategory($controlId)
    {
        $sectionCategory = [
            'TEXT' => Loc::getMessage('SMARTSEO_SPM_SECTION'),
            'MENU' => [
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_SECTION_NAME'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=section.Name}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_SECTION_LOWER_NAME'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=lower section.Name}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_SECTION_CODE'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=section.Code}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_SECTION_PREVIEW_TEXT'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=section.PreviewText}'
                    ]),
                ],
            ],
        ];

        if ($this->getSectionProperties()) {
            $_menuSectionProperty = [];
            foreach ($this->getSectionProperties() as $sectionProperty) {
                $_menuSectionProperty[] = [
                    'TEXT' => $sectionProperty['NAME'],
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=section.property.' . $sectionProperty['FIELD'] . '}'
                    ]),
                ];
            }

            $sectionCategory['MENU'][] = [
                'TEXT' => Loc::getMessage('SMARTSEO_SPM_SECTION_PROPERTIES'),
                'MENU' => $_menuSectionProperty,
            ];
        }

        return $sectionCategory;
    }

    protected function getParentSectionMenuCategory($controlId)
    {
        $sectionCategory = [
            'TEXT' => Loc::getMessage('SMARTSEO_SPM_PARENT'),
            'MENU' => [
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_PARENT_NAME'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=section.parent.Name}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_PARENT_LOWER_NAME'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=lower section.parent.Name}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_PARENT_CODE'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=section.parent.Code}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_PARENT_TEXT'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=section.parent.PreviewText}'
                    ]),
                ],
            ],
        ];

        if ($this->getSectionProperties()) {
            $_menuSectionProperty = [];
            foreach ($this->getSectionProperties() as $sectionProperty) {
                $_menuSectionProperty[] = [
                    'TEXT' => $sectionProperty['NAME'],
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=section.parent.property.' . $sectionProperty['FIELD'] . '}'
                    ]),
                ];
            }

            $sectionCategory['MENU'][] = [
                'TEXT' => Loc::getMessage('SMARTSEO_SPM_SECTION_PROPERTIES'),
                'MENU' => $_menuSectionProperty,
            ];
        }

        return $sectionCategory;
    }

    protected function getIblockMenuCategory($controlId)
    {
        return [
            'TEXT' => Loc::getMessage('SMARTSEO_SPM_IBLOCK'),
            'MENU' => [
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_IBLOCK_NAME'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=iblock.Name}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_IBLOCK_TEXT'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=iblock.PreviewText}'
                    ]),
                ],
            ],
        ];
    }

    protected function getPropertyMenuCategory($controlId)
    {
        if ($this->iblockId > 0) {
            $iblockPropertyList = $this->getProperties($this->propertyIds);

            $_menu = [];
            foreach ($iblockPropertyList as $property) {
                if ($property['PROPERTY_TYPE'] == 'F') {
                    continue;
                }

                $_menu[] = [
                    'TEXT' => $property['NAME'],
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=property.' . ($property['CODE'] ?: $property['ID']) . '}',
                    ]),
                ];
            }

            return [
                'TEXT' => Loc::getMessage('SMARTSEO_SPM_PROPERTIES'),
                'MENU' => $_menu,
            ];
        }
    }

    protected function getSkuPropertyMenuCategory($controlId)
    {
        if ($this->iblockId > 0) {
            $iblockSkuPropertyList = $this->getSkuProperties($this->propertyIds);

            $_menu = [];
            foreach ($iblockSkuPropertyList as $property) {
                if ($property['PROPERTY_TYPE'] == 'F') {
                    continue;
                }

                $_menu[] = [
                    'TEXT' => $property['NAME'],
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=sku_property.' . ($property['CODE'] ?: $property['ID']) . '}',
                    ]),
                ];
            }

            return [
                'TEXT' => Loc::getMessage('SMARTSEO_SPM_SKU_PROPERTIES'),
                'MENU' => $_menu,
            ];
        }
    }

    protected function getFunctionMenuCategory($controlId)
    {
        $_exampleValue = Loc::getMessage('SMARTSEO_SPM_EXAMPLE_VALUE');
        $_exampleNumValue = Loc::getMessage('SMARTSEO_SPM_EXAMPLE_NUM_VALUE');

        return [
            'TEXT' => Loc::getMessage('SMARTSEO_SPM_FUNCTIONS'),
            'MENU' => [
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY'),
                    'MENU' => [
                        [
                            'TEXT' => Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_NOMINATIV'),
                            'ONCLICK' => $this->getFunctionOnClick([
                                'id' => $controlId,
                                'value' => $this->getMorphySprintf([
                                    $_exampleValue,
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VNOMINATIV'),
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VSINGULAR'),
                                ]),
                            ]),
                        ],
                        [
                            'TEXT' => Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_GENITIV'),
                            'ONCLICK' => $this->getFunctionOnClick([
                                'id' => $controlId,
                                'value' => $this->getMorphySprintf([
                                    $_exampleValue,
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VGENITIV'),
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VSINGULAR'),
                                ]),
                            ]),
                        ],
                        [
                            'TEXT' => Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_DATIV'),
                            'ONCLICK' => $this->getFunctionOnClick([
                                'id' => $controlId,
                                'value' => $this->getMorphySprintf([
                                    $_exampleValue,
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VDATIV'),
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VSINGULAR'),
                                ]),
                            ]),
                        ],
                        [
                            'TEXT' => Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_ACCUSATIV'),
                            'ONCLICK' => $this->getFunctionOnClick([
                                'id' => $controlId,
                                'value' => $this->getMorphySprintf([
                                    $_exampleValue,
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VACCUSATIV'),
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VSINGULAR'),
                                ]),
                            ]),
                        ],
                        [
                            'TEXT' => Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_INSTRUMENTALIS'),
                            'ONCLICK' => $this->getFunctionOnClick([
                                'id' => $controlId,
                                'value' => $this->getMorphySprintf([
                                    $_exampleValue,
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VINSTRUMENTALIS'),
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VSINGULAR'),
                                ]),
                            ]),
                        ],
                        [
                            'TEXT' => Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_LOCATIV'),
                            'ONCLICK' => $this->getFunctionOnClick([
                                'id' => $controlId,
                                'value' => $this->getMorphySprintf([
                                    $_exampleValue,
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VLOCATIV'),
                                    Loc::getMessage('SMARTSEO_SPM_MORPHOLOGY_VSINGULAR'),
                                ]),
                            ]),
                        ],
                    ],
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_CONCAT'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=concat ' . $_exampleValue . ' ", "}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_UPPER'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=upper ' . $_exampleValue . '}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_UPPER_FIRST'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=aspro_upperf ' . $_exampleValue . '}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_LOWER'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=lower ' . $_exampleValue . '}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_TRANSLIT'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=translit ' . $_exampleValue . '}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_LIMIT'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=limit ' . $_exampleValue . ' ", " 10}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_MIN'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=min ' . $_exampleNumValue . '}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_MAX'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=max ' . $_exampleNumValue . '}'
                    ]),
                ],
                [
                    'TEXT' => Loc::getMessage('SMARTSEO_SPM_DISTINCT'),
                    'ONCLICK' => $this->getFunctionOnClick([
                        'id' => $controlId,
                        'value' => '{=distinct ' . $_exampleValue . '}'
                    ]),
                ],
            ],
        ];
    }

    protected function getFunctionOnClick($params)
    {
        return vsprintf($this->getFunctionFormat(), $params);
    }

    private function getProperties($propertyIds = [])
    {
        if (empty($this->iblockId)) {
            return [];
        }

        if($this->needSelectedProperties && !$propertyIds) {
            return [];
        }

        return $this->getIblockPropertyList([
              'NAME' => 'ASC'
            ], array_filter([
              'IBLOCK_ID' => $this->iblockId,
              'ID' => $propertyIds,
        ]));
    }

    private function getSkuProperties($propertyIds = [])
    {
        if (empty($this->iblockId)) {
            return [];
        }

        if($this->needSelectedProperties && !$propertyIds) {
            return [];
        }

        $row = $this->getCatalogIblockRow([
            'PRODUCT_IBLOCK_ID' => $this->iblockId,
          ], [
            'IBLOCK_ID'
        ]);

        if (!$row) {
            return [];
        }

        $skuIblockId = $row['IBLOCK_ID'];

        return $this->getIblockPropertyList([
              'NAME' => 'ASC'
            ], array_filter([
              'IBLOCK_ID' => $skuIblockId,
              'ID' => $propertyIds,
        ]));
    }

    private function getSectionProperties()
    {
        if (empty($this->iblockId)) {
            return [];
        }

        if ($this->sectionProperties) {
            return $this->sectionProperties;
        }

        $rows = $this->getUserFieldList([], [
            'ENTITY_ID' => 'IBLOCK_' . $this->iblockId . '_SECTION',
            'USER_TYPE_ID' => 'string',
          ], [
            'LANG_NAME' => 'LANG.EDIT_FORM_LABEL', 'FIELD_NAME', 'ID', 'USER_TYPE_ID'
        ]);

        $result = [];
        foreach ($rows as $row) {
            $_field = str_replace('UF_', '', $row['FIELD_NAME']);
            $result[] = [
                'NAME' => $row['LANG_NAME'] ?: $row['FIELD_NAME'],
                'FIELD' => $_field,
            ];
        }

        $this->sectionProperties = $result;

        return $result;
    }

    private function getMorphySprintf(array $args)
    {
        return vsprintf('{=aspro_morphy %s "%s" "%s"}', $args);
    }

}
