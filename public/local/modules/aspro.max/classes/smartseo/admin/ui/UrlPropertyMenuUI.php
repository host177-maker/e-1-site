<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class UrlPropertyMenuUI
{

    use BitrixCoreEntity;

    const DEFAULT_FUNCTION_FORMAT = "AsproUI.Form.ControlTemplateEngine.valueInputEntry('%s', '%s', %b);";

    const CATEGORY_IBLOCK = 'iblock';
    const CATEGORY_SITE = 'site';
    const CATEGORY_SECTION = 'section';
    const CATEGORY_PROPERTY = 'property';
    const CATEGORY_PRICE = 'price';

    private $functionFormat;
    private $siteId = null;
    private $iblockId = null;
    private $iblock = null;
    private $propertyIds = [];

    function __construct()
    {

    }

    public function setSiteId($value)
    {
        $this->siteId = $value;

        return $this;
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

    public function getMenuItems($controlId)
    {
        $category[self::CATEGORY_SITE] = $this->getSiteMenuCategory($controlId);
        $category['separator'] = [[
            'SEPARATOR' => true,
        ]];
        $category[self::CATEGORY_IBLOCK] = $this->getIblockMenuCategory($controlId);
        $category[self::CATEGORY_SECTION] = $this->getSectionMenuCategory($controlId);
        $category[self::CATEGORY_PROPERTY] = $this->getPropertyMenuCategory($controlId);
        $category[self::CATEGORY_PRICE] = $this->getPriceMenuCategory($controlId);


        $result = [];
        foreach ($category as $value) {
            if (!empty($value)) {
                $result = array_merge($result, $value);
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

    protected function getSiteMenuCategory($controlId)
    {
        $setting = \Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo::getInstance();

        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_SITE_DIR'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '#SITE_DIR#',
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_PAGE_URL'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => $setting->site($this->siteId)->getNewUrlSection(),
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_PAGE_URL_WITH_PROPERTIES'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => $setting->site($this->siteId)->getNewUrlSection() . '{#PROPERTY_CODE#-#PROPERTY_VALUE#/}/',
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_PAGE_URL_WITH_PRICE_PROPERTIES'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => $setting->site($this->siteId)->getNewUrlSection() . '{#PRICE_CODE#-#PRICE_VALUE#/}/{#PROPERTY_CODE#-#PROPERTY_VALUE#/}/',
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_PAGE_URL_OTHER'),
                'MENU' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_PAGE_URL_WITH_PROPERTIES_WITHOUT_CODES'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => $setting->site($this->siteId)->getNewUrlSection() . '{#PROPERTY_VALUE#/}/',
                            'byCaretPosition' => false,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_PAGE_URL_WITH_PRICE_PROPERTIES_WITHOUT_CODES'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => $setting->site($this->siteId)->getNewUrlSection() . '{#PRICE_VALUE#/}/{#PROPERTY_VALUE#/}/',
                            'byCaretPosition' => false,
                        ]),
                    ],
                ]
            ],
        ];
    }

    protected function getSectionMenuCategory($controlId)
    {

        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION'),
                'MENU' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_ID'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_ID#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_CODE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_CODE#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_SECTION_CODE_PATH'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_CODE_PATH#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                ]
            ]
        ];
    }

    protected function getIblockMenuCategory($controlId)
    {
        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_IBLOCK'),
                'MENU' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_IBLOCK_ID'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#IBLOCK_ID#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_IBLOCK_CODE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#IBLOCK_CODE#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_IBLOCK_EXTERNAL_ID'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#IBLOCK_EXTERNAL_ID#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                ],
            ],
        ];
    }

    protected function getPropertyMenuCategory($controlId)
    {
        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_PROPERTY'),
                'MENU' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_PROPERTY_CODE_VALUE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '{#PROPERTY_CODE#-#PROPERTY_VALUE#/}',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_PROPERTY_CODE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '{#PROPERTY_CODE#/}',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_PROPERTY_VALUE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '{#PROPERTY_VALUE#/}',
                            'byCaretPosition' => true,
                        ]),
                    ],
                ],
            ],
        ];
    }

    protected function getPriceMenuCategory($controlId)
    {
        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_UPM_PRICE'),
                'MENU' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_PRICE_CODE_VALUE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '{#PRICE_CODE#-#PRICE_VALUE#/}',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_PRICE_CODE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '{#PRICE_CODE#/}',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_UPM_PRICE_VALUE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '{#PRICE_VALUE#/}',
                            'byCaretPosition' => true,
                        ]),
                    ],
                ],
            ],
        ];
    }

    private function getFunctionOnClick($params)
    {
        return vsprintf($this->getFunctionFormat(), $params);
    }

    private function getProperties($propertyIds = [])
    {
        if (empty($this->iblockId)) {
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

        $row = $this->getCatalogIblockRow([
            'PRODUCT_IBLOCK_ID' => $this->iblockId,
          ], [
            'IBLOCK_ID'
        ]);

        $skuIblockId = $row['IBLOCK_ID'];

        return $this->getIblockPropertyList([
              'NAME' => 'ASC'
            ], array_filter([
              'IBLOCK_ID' => $skuIblockId,
              'ID' => $propertyIds,
        ]));
    }

}
