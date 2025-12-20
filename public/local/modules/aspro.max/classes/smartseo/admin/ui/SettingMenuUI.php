<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SettingMenuUI
{
    const CATEGORY_FILTER_RULE_NAME = 'filter_rule_name_template';
    const CATEGORY_URL_SMARTFILTER = 'url_template_smartfilter';
    const CATEGORY_URL_SEF_FOLDER = 'url_sef_folder';
    const CATEGORY_URL_SECTION = 'url_section';
    const CATEGORY_NEW_URL_SECTION = 'new_url_section';

    const DEFAULT_FUNCTION_FORMAT = "AsproUI.Form.ControlTemplateEngine.valueInputEntry('%s', '%s', %b);";

    private $functionFormat;

    function __construct()
    {

    }

    public function getMenuItems($controlId, $categoryName = '')
    {
        $category[self::CATEGORY_FILTER_RULE_NAME] = $this->getFilterRuleNameTemplateMenu($controlId);
        $category[self::CATEGORY_URL_SMARTFILTER] = $this->getSmartfilterUrlMenu($controlId);
        $category[self::CATEGORY_URL_SEF_FOLDER] = $this->getSefFolderUrlMenu($controlId);
        $category[self::CATEGORY_URL_SECTION] = $this->getSectionUrlMenu($controlId);
        $category[self::CATEGORY_NEW_URL_SECTION] = $this->getNewSectionUrlMenu($controlId);

        $result = [];

        if($categoryName && $category[$categoryName]) {
            return $category[$categoryName];
        }

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

    protected function getFilterRuleNameTemplateMenu($controlId)
    {
        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_FILTER_RULE_ID'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '{=this.id}',
                    'byCaretPosition' => true,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_FILTER_RULE_SECTIONS'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '{=lower {=concat this.sections.name ", "}}',
                    'byCaretPosition' => true,
                ]),
            ],
        ];
    }

    protected function getSmartfilterUrlMenu($controlId)
    {
        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_URL_DEFAULT'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '#SECTION_CODE_PATH#/filter/#SMART_FILTER_PATH#/apply/',
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION'),
                'MENU' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_URL_SMARTFITLER'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SMART_FILTER_PATH#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_CODE_PATH'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_CODE_PATH#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_CODE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_CODE#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_ID'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_ID#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                ]
            ]
        ];
    }

    protected function getSefFolderUrlMenu($controlId)
    {
        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_URL_DEFAULT'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '/catalog/',
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_URL_IBLOCK'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '#IBLOCK_LIST_PAGE_URL#',
                    'byCaretPosition' => false,
                ]),
            ],
        ];
    }

    protected function getSectionUrlMenu($controlId)
    {
        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_URL_DEFAULT'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '#SECTION_CODE_PATH#/',
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION'),
                'MENU' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_CODE_PATH'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_CODE_PATH#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_CODE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_CODE#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_ID'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_ID#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                ]
            ]
        ];
    }

    protected function getNewSectionUrlMenu($controlId)
    {
        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_URL_DEFAULT'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '/catalog/#SECTION_CODE_PATH#/',
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_URL_IBLOCK_SECTION_PAGE'),
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => '#IBLOCK_SECTION_PAGE_URL#',
                    'byCaretPosition' => false,
                ]),
            ],
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION'),
                'MENU' => [
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_CODE_PATH'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_CODE_PATH#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_CODE'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_CODE#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                    [
                        'TEXT' => Loc::getMessage('SMARTSEO_SM_SECTION_ID'),
                        'ONCLICK' => $this->getFunctionOnClick([
                            'id' => $controlId,
                            'value' => '#SECTION_ID#',
                            'byCaretPosition' => true,
                        ]),
                    ],
                ]
            ]
        ];
    }

    private function getFunctionOnClick($params)
    {
        return vsprintf($this->getFunctionFormat(), $params);
    }
}
