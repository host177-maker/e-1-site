<?php

namespace Aspro\Max\Smartseo\Condition\Controls;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Settings,
    Aspro\Max\Smartseo\LazyLoader\ElementProperty,
    Bitrix\Main\Localization\Loc;

class IblockPropertyBuildControls extends \CCatalogCondCtrlIBlockProps implements BuildControlsInterface
{
    public static $iblockId = null;
    public static $isCatalogModule = false;

    private $filter;
    private static $controlList = [];
    private static $smartfilterProperties = [];
    private static $cacheTtl = 0;
    private static $params = [];

    public function __construct($iblockId, array $params = [])
    {
        if (\Bitrix\Main\Loader::includeModule('catalog')) {
            self::$isCatalogModule = true;
        }

        self::setParams($params);

        self::$iblockId = $iblockId;

        $this->filter = [
            'IBLOCK_ID' => $iblockId,
        ];
    }

    public function getBuild()
    {
        self::$cacheTtl = Settings\SettingSmartseo::getInstance()->getCacheConditionControls();

        return [
            'COMPLEX' => 'Y',
            'SORT' => 300,
            'CONTROLS' => static::GetControls(false, $this->filter),
            'GetControlShow' => [self::class, 'GetControlShow'],
            'GetConditionShow' => [self::class, 'GetConditionShow'],
            'IsGroup' => [self::class, 'IsGroup'],
            'Parse' => [self::class, 'Parse'],
            'Generate' => [self::class, 'Generate'],
            'ApplyValues' => [self::class, 'ApplyValues'],
            'getApplyControl' => [self::class, 'getApplyControl'],
            'InitParams' => [self::class, 'InitParams'],
        ];
    }

    public static function getControls($controlId = false, $filter = [])
    {
        if (self::$controlList) {
            return static::searchControl(self::$controlList, $controlId);
        }

        $iblocks = self::getIblocks($filter);

        self::loadSmartfilterProperties(array_column($iblocks, 'ID'));

        foreach ($iblocks as $iblock) {
            $isSeparator = true;
            $properties = self::getPropertiesByIblockId($iblock['ID']);

            foreach ($properties as $property) {
                self::modifiedProperty($property);

                $propertyJS = self::getModifiedPropertyForJS($property);

                self::$controlList['CondIBProp:' . $iblock['ID'] . ':' . $property['ID']] = [
                    'ID' => 'CondIBProp:' . $iblock['ID'] . ':' . $property['ID'],
                    'PARENT' => false,
                    'EXIST_HANDLER' => 'Y',
                    'MODULE_ID' => Smartseo\General\Smartseo::MODULE_ID,
                    'MODULE_ENTITY' => 'iblock',
                    'ENTITY' => 'ELEMENT_PROPERTY',
                    'ENTITY_NAME' => $iblock['NAME'],
                    'IBLOCK_ID' => $iblock['ID'],
                    'IBLOCK_NAME' => $iblock['NAME'],
                    'IBLOCK_TYPE' => $iblock['TYPE'],
                    'PROPERTY_ID' => $property['ID'],
                    'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
                    'PROPERTY_CODE' => $property['CODE'],
                    'PROPERTY_NAME' => $property['NAME'],
                    'PROPERTY_LINK_IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
                    'PROPERTY_SORT' => $property['SORT'],
                    'PROPERTY_DISPLAY_TYPE' => $property['DISPLAY_TYPE'],
                    'USER_TYPE' => $property['USER_TYPE'],
                    'USER_TYPE_SETTINGS' => $property['USER_TYPE_SETTINGS'],
                    'FIELD' => 'PROPERTY_' . $property['ID'],
                    'FIELD_TABLE' => $iblock['ID'] . ':' . $property['ID'],
                    'FIELD_TYPE' => $propertyJS['type'],
                    'MULTIPLE' => 'Y',
                    'GROUP' => 'N',
                    'SEP' => $isSeparator ? 'Y' : 'N',
                    'SEP_LABEL' => (
                    $isSeparator
                        ? str_replace(
                        ['#ID#', '#NAME#'],
                        [$iblock['ID'], $iblock['NAME']],
                        Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_PROP_LABEL')
                    )
                        : ''
                    ),
                    'LABEL' => $property['NAME'],
                    'PREFIX' => str_replace(
                        ['#NAME#', '#IBLOCK_ID#', '#IBLOCK_NAME#'],
                        [$property['NAME'], $iblock['ID'], $iblock['NAME']],
                        Loc::getMessage('BT_MOD_CATALOG_COND_CMP_IBLOCK_ONE_PROP_PREFIX')
                    ),
                    'LOGIC' => $propertyJS['logic'],
                    'JS_VALUE' => $propertyJS['value'],
                    'PHP_VALUE' => $propertyJS['phpValue']
                ];

                $isSeparator = false;
            }
        }

        return static::searchControl(self::$controlList, $controlId);
    }

    public static function applyValues($condition, $control)
    {
        $logics = [
            BT_COND_LOGIC_EQ,
            BT_COND_LOGIC_NOT_EQ,
            BT_COND_LOGIC_CONT,
            BT_COND_LOGIC_NOT_CONT,
            BT_COND_LOGIC_GR,
            BT_COND_LOGIC_LS,
            BT_COND_LOGIC_EGR,
            BT_COND_LOGIC_ELS
        ];

        if (is_string($control)) {
            $control = static::getControls($control);
        }

        $values = static::check($condition, $condition, $control, false);

        if ($values === false) {
            return false;
        }

        $logic = static::searchLogic($values['logic'], $control['LOGIC']);

        $result = null;

        if (in_array($logic['ID'], $logics)) {
            $result = [
                'ID' => $control['ID'],
                'FIELD' => $control['FIELD'],
                'FIELD_TYPE' => $control['FIELD_TYPE'],
                'ENTITY' => $control['ENTITY'],
                'ENTITY_NAME' => $control['ENTITY_NAME'],
                'IBLOCK_ID' => $control['IBLOCK_ID'],
                'IBLOCK_NAME' => $control['IBLOCK_NAME'],
                'PROPERTY_ID' => $control['PROPERTY_ID'],
                'PROPERTY_TYPE' => $control['PROPERTY_TYPE'],
                'PROPERTY_NAME' => $control['PROPERTY_NAME'],
                'PROPERTY_CODE' => $control['PROPERTY_CODE'],
                'PROPERTY_IBLOCK_ID' => $control['IBLOCK_ID'],
                'PROPERTY_LINK_IBLOCK_ID' => $control['PROPERTY_LINK_IBLOCK_ID'],
                'PROPERTY_SORT' => $control['PROPERTY_SORT'],
                'PROPERTY_DISPLAY_TYPE' => $control['PROPERTY_DISPLAY_TYPE'],
                'USER_TYPE' => $control['USER_TYPE'],
                'USER_TYPE_SETTINGS' => $control['USER_TYPE_SETTINGS'],
                'VALUES' => (is_array($values['value']) ? $values['value'] : [$values['value']]),
                'LOGIC' => $logic['VALUE'],
                'FILED_LOGIC' => $logic['LABEL'],
            ];

            if ($result['VALUES']) {
                $result['DISPLAY_VALUES'] = static::getPropertyDisplayValue([
                    'PROPERTY_ID' => $result['PROPERTY_ID'],
                    'PROPERTY_TYPE' => $result['PROPERTY_TYPE'],
                    'USER_TYPE' => $result['USER_TYPE'],
                    'USER_TYPE_SETTINGS' => $result['USER_TYPE_SETTINGS'],
                    'VALUES' => $result['VALUES'],
                ]);
            }
        }

        return $result ?: false;
    }

    public static function getApplyControl($condition, $control)
    {
        $logics = [
            BT_COND_LOGIC_EQ,
            BT_COND_LOGIC_NOT_EQ,
            BT_COND_LOGIC_CONT,
            BT_COND_LOGIC_NOT_CONT,
            BT_COND_LOGIC_GR,
            BT_COND_LOGIC_LS,
            BT_COND_LOGIC_EGR,
            BT_COND_LOGIC_ELS
        ];

        if (is_string($control)) {
            $control = static::getControls($control);
        }

        $values = static::check($condition, $condition, $control, false);

        if ($values === false) {
            return false;
        }

        $logic = static::searchLogic($values['logic'], $control['LOGIC']);

        $result = null;

        if (in_array($logic['ID'], $logics)) {
            $result = [
                'ID' => $control['PROPERTY_ID'],
                'CLASS_ID' => $control['ID'],
                'ENTITY' => $control['IBLOCK_TYPE'] === 'SKU' ? $control['IBLOCK_TYPE'] . '_' . $control['ENTITY'] : $control['ENTITY'],
                'IBLOCK_ID' => $control['IBLOCK_ID'],
                'IBLOCK_NAME' => $control['IBLOCK_NAME'],
                'IBLOCK_TYPE' => $control['IBLOCK_TYPE'],
                'PROPERTY_ID' => $control['PROPERTY_ID'],
                'PROPERTY_TYPE' => $control['PROPERTY_TYPE'],
                'PROPERTY_NAME' => $control['PROPERTY_NAME'],
                'PROPERTY_CODE' => $control['PROPERTY_CODE'],
                'PROPERTY_LINK_IBLOCK_ID' => $control['PROPERTY_LINK_IBLOCK_ID'],
                'PROPERTY_SORT' => $control['PROPERTY_SORT'],
                'PROPERTY_DISPLAY_TYPE' => $control['PROPERTY_DISPLAY_TYPE'],
                'USER_TYPE' => $control['USER_TYPE'],
                'USER_TYPE_SETTINGS' => $control['USER_TYPE_SETTINGS'],
                'LOGIC' => [
                    'OPERATOR' => $logic['VALUE'],
                    'VALUE' => (is_array($values['value']) ? $values['value'] : $values['value']),
                    'LABEL' => $logic['LABEL'],
                ]
            ];
        }

        return $result ?: false;
    }

    public static function check($condition, $params, $control, $isShow)
    {
        if ($isShow && !$condition['value']) {
            return [
                'id' => $params['COND_NUM'],
                'controlId' => $control['ID'],
                'values' => [
                    'logic' => $condition['logic'],
                    'value' => ''
                ]
            ];
        }

        if (!$isShow && !$condition['value']) {
            return [
                'logic' => $condition['logic'],
                'value' => ''
            ];
        }

        return parent::Check($condition, $params, $control, $isShow);
    }

    protected static function modifiedProperty(&$property)
    {
        if (preg_match('|SAsproCustomFilter(\w+)|', $property['USER_TYPE']) && $property['USER_TYPE_SETTINGS']) {
            $_userTypeSettings = unserialize($property['USER_TYPE_SETTINGS'], ['allowed_classes' => false]);

            $property['USER_TYPE'] = '';
            $property['PROPERTY_TYPE'] = 'E';
            $property['LINK_IBLOCK_ID'] = $_userTypeSettings['IBLOCK_ID'];
        }
    }

    protected static function getModifiedPropertyForJS($property)
    {
        if ('CML2_LINK' == $property['XML_ID'] || 'F' == $property['PROPERTY_TYPE']) {
            return [];
        }

        $type = null;
        $logic = null;
        $value = null;
        $phpValue = null;

        if ($property['USER_TYPE']) {
            switch ($property['USER_TYPE']) {
                case 'DateTime':
                    $type = 'datetime';
                    $logic = static::GetLogic([
                        BT_COND_LOGIC_EQ,
                        BT_COND_LOGIC_NOT_EQ,
                        BT_COND_LOGIC_GR,
                        BT_COND_LOGIC_LS,
                        BT_COND_LOGIC_EGR,
                        BT_COND_LOGIC_ELS
                    ]);
                    $value = [
                        'type' => 'datetime',
                        'format' => 'datetime'
                    ];
                    break;
                case 'Date':
                    $type = 'date';
                    $logic = static::GetLogic([
                        BT_COND_LOGIC_EQ,
                        BT_COND_LOGIC_NOT_EQ,
                        BT_COND_LOGIC_GR,
                        BT_COND_LOGIC_LS,
                        BT_COND_LOGIC_EGR,
                        BT_COND_LOGIC_ELS
                    ]);
                    $value = [
                        'type' => 'datetime',
                        'format' => 'date'
                    ];
                    break;
                case 'directory':
                    $type = 'text';
                    $logic = static::GetLogic([
                        BT_COND_LOGIC_EQ,
                        BT_COND_LOGIC_NOT_EQ
                    ]);
                    $value = [
                        'type' => 'lazySelect',
                        'load_url' => '/bitrix/tools/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/get_property_values.php',
                        'load_params' => [
                            'lang' => LANGUAGE_ID,
                            'propertyId' => $property['ID']
                        ]
                    ];

                    break;
                default:

                    break;
            }

            return array_filter([
                'type' => $type,
                'logic' => $logic,
                'value' => $value,
                'phpValue' => $phpValue,
            ]);
        }

        switch ($property['PROPERTY_TYPE']) {
            case 'N':
                $type = 'double';
                $logic = static::GetLogic([
                    BT_COND_LOGIC_EGR,
                    BT_COND_LOGIC_ELS
                ]);
                $value = [
                    'type' => 'input'
                ];
                break;
            case 'S':
                $type = 'text';
                $logic = static::GetLogic([
                    BT_COND_LOGIC_EQ,
                    BT_COND_LOGIC_NOT_EQ,
                    BT_COND_LOGIC_CONT,
                    BT_COND_LOGIC_NOT_CONT
                ]);
                $value = [
                    'type' => 'input'
                ];
                break;
            case 'L':
                $type = 'int';
                $logic = static::GetLogic([
                    BT_COND_LOGIC_EQ,
                    BT_COND_LOGIC_NOT_EQ
                ]);
                $value = [
                    'type' => 'lazySelect',
                    'load_url' => '/bitrix/tools/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/get_property_values.php',
                    'load_params' => [
                        'lang' => LANGUAGE_ID,
                        'propertyId' => $property['ID'],
                    ]
                ];
                $phpValue = [
                    'VALIDATE' => 'enumValue'
                ];
                break;
            case 'E':
                $type = 'int';
                $logic = static::GetLogic([
                    BT_COND_LOGIC_EQ,
                    BT_COND_LOGIC_NOT_EQ
                ]);
                $value = [
                    'type' => 'popup',
                    'popup_url' => self::getSelfFolderUrl() . 'iblock_element_search.php',
                    'popup_params' => [
                        'lang' => LANGUAGE_ID,
                        'IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
                        'discount' => 'Y'
                    ],
                    'param_id' => 'n'
                ];
                $phpValue = [
                    'VALIDATE' => 'element'
                ];
                break;
            case 'G':
                $type = 'int';
                $logic = static::GetLogic([
                    BT_COND_LOGIC_EQ,
                    BT_COND_LOGIC_NOT_EQ
                ]);
                $value = [
                    'type' => 'popup',
                    'popup_url' => self::getSelfFolderUrl() . 'iblock_section_search.php',
                    'popup_params' => array_filter([
                        'lang' => LANGUAGE_ID,
                        'IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
                        'discount' => 'Y',
                        'simplename' => 'Y',
                        'iblockfix' => $property['LINK_IBLOCK_ID'] > 0 ? 'y' : null
                    ]),
                    'param_id' => 'n'
                ];
                $phpValue = [
                    'VALIDATE' => 'section'
                ];
                break;
        }

        return array_filter([
            'type' => $type,
            'logic' => $logic,
            'value' => $value,
            'phpValue' => $phpValue,
        ]);
    }

    protected static function getIblocks($filter = [])
    {
        if (self::$isCatalogModule && self::$params['SHOW_PROPERTY_SKU']) {
            $rows = \Bitrix\Catalog\CatalogIblockTable::getList(array_filter([
                'select' => [
                    'IBLOCK_ID',
                    'PRODUCT_IBLOCK_ID',
                    'SKU_PROPERTY_ID',
                    'REF_IBLOCK_NAME' => 'IBLOCK.NAME',
                ],
                'filter' => array_filter([
                    'LOGIC' => 'OR',
                    'PRODUCT_IBLOCK_ID' => $filter['IBLOCK_ID'],
                    'IBLOCK_ID' => $filter['IBLOCK_ID'],
                ]),
                'order' => [
                    'IBLOCK_ID' => 'ASC'
                ]
            ]))->fetchAll();
        } else {
            $rows = \Bitrix\Iblock\IblockTable::getList(array_filter([
                'select' => [
                    'IBLOCK_ID' => 'ID',
                    'REF_IBLOCK_NAME' => 'NAME',
                ],
                'filter' => array_filter([
                    'IBLOCK_ID' => $filter['IBLOCK_ID'],
                ]),
                'order' => [
                    'IBLOCK_ID' => 'ASC'
                ]
            ]))->fetchAll();
        }

        $result = [];
        foreach ($rows as $row) {
            if ($row['IBLOCK_ID']) {
                if (!isset($row['SKU_PROPERTY_ID'])) {
                    $row['SKU_PROPERTY_ID'] = '';
                }

                $result[] = [
                    'ID' => $row['IBLOCK_ID'],
                    'NAME' => $row['REF_IBLOCK_NAME'],
                    'TYPE' => $row['SKU_PROPERTY_ID'] && $row['SKU_PROPERTY_ID'] > 0 ? 'SKU' : 'IBLOCK',
                ];
            }
        }

        return $result;
    }

    protected static function getPropertiesByIblockId($iblockId)
    {
        if (!self::$iblockId) {
            return [];
        }

        $smartfilterProperties = [];
        if (self::$params['ONLY_PROPERTY_SMART_FILTER'] === true) {
            $smartfilterProperties = self::getSmartfilterProperties();

            $filter = [
                'IBLOCK_ID' => $iblockId,
                '=ID' => $smartfilterProperties ? array_keys($smartfilterProperties) : [],
                '!PROPERTY_TYPE' => 'F',
            ];
        } else {
            $filter = [
                'IBLOCK_ID' => $iblockId,
                '!PROPERTY_TYPE' => 'F',
            ];
        }

        $propertyList = \Bitrix\Iblock\PropertyTable::getList(array_filter([
            'select' => [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'CODE',
                'ACTIVE',
                'PROPERTY_TYPE',
                'DEFAULT_VALUE',
                'USER_TYPE',
                'USER_TYPE_SETTINGS',
                'LINK_IBLOCK_ID',
                'SORT',
            ],
            'filter' => $filter,
            'order' => [
                'NAME' => 'ASC',
            ],
            'cache' => [
                'ttl' => self::$cacheTtl,
            ]
        ]))->fetchAll();

        $result = [];

        foreach ($propertyList as $property) {
            $property['DISPLAY_TYPE'] = $smartfilterProperties[$property['ID']];
            $result[] = $property;
        }

        return $result;
    }

    protected static function loadSmartfilterProperties(array $iblockIds)
    {
        $rows = \Bitrix\Iblock\SectionPropertyTable::getList([
            'select' => [
                'PROPERTY_ID',
                'DISPLAY_TYPE',
            ],
            'filter' => [
                'SMART_FILTER' => 'Y',
                'IBLOCK_ID' => $iblockIds
            ],
            'group' => [
                'PROPERTY_ID',
            ]
        ])->fetchAll();

        self::$smartfilterProperties = array_column($rows, 'DISPLAY_TYPE', 'PROPERTY_ID');
    }

    protected static function getSmartfilterProperties()
    {
        return self::$smartfilterProperties;
    }

    protected static function getPropertyDisplayValue($property)
    {
        $result = [];
        foreach ($property['VALUES'] as $value) {
            $_value = '';

            if ($property['USER_TYPE']) {
                $_value = new ElementProperty\ElementPropertyUserField($value, [
                    'ID' => $property['PROPERTY_ID'],
                    'USER_TYPE' => $property['USER_TYPE'],
                    'USER_TYPE_SETTINGS' => unserialize($property['USER_TYPE_SETTINGS'], ['allowed_classes' => false]),
                ], [
                    'IS_DISPLAY_KEY_IF_EMPTY' => 'Y'
                ]);
            } elseif ($property['PROPERTY_TYPE'] === 'E') {
                $_value = new ElementProperty\ElementPropertyElement($value, [
                    'IS_DISPLAY_KEY_IF_EMPTY' => 'Y'
                ]);
            } elseif ($property['PROPERTY_TYPE'] === 'L') {
                $_value = new ElementProperty\ElementPropertyEnum($value, [
                    'IS_DISPLAY_KEY_IF_EMPTY' => 'Y'
                ]);
            } else {
                $_value = $value;
            }

            $result[] = $_value;
        }

        return $result;
    }

    protected static function getSelfFolderUrl()
    {
        return (defined('SELF_FOLDER_URL') ? SELF_FOLDER_URL : '/bitrix/admin/');
    }

    protected static function setParams(array $params = [])
    {
        if (isset($params['ONLY_PROPERTY_SMART_FILTER']) && $params['ONLY_PROPERTY_SMART_FILTER'] === 'N') {
            self::$params['ONLY_PROPERTY_SMART_FILTER'] = false;
        } else {
            self::$params['ONLY_PROPERTY_SMART_FILTER'] = true;
        }

        if (isset($params['SHOW_PROPERTY_SKU']) && $params['SHOW_PROPERTY_SKU'] === 'N') {
            self::$params['SHOW_PROPERTY_SKU'] = false;
        } else {
            self::$params['SHOW_PROPERTY_SKU'] = true;
        }
    }
}
