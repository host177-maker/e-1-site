<?php

namespace Aspro\Max\Smartseo\Generator\Handlers;

use Aspro\Max\Smartseo\Condition,
    Bitrix\Highloadblock\HighloadBlockTable;

class PropertyUrlHandler extends AbstractUrlHandler
{
    /** >= (great or equal) */
    const LOGIC_EGR = 'EqGr';
    /** <= (less or equal)  */
    const LOGIC_ELS = 'EqLs';

    const URL_TYPE_GENERATE_MERGE = 'MR';
    const URL_TYPE_GENERATE_COMBO = 'CM';

    const CACHE_TTL_ENUM_VALUES = 3600;
    const CACHE_TTL_ELEMENTS = 3600;

    protected $iblockId = null;
    protected $condition = null;
    protected $isHighLoadBlockModule = false;
    protected $isFriendlyUrl = false;
    protected $generationType = '';

    protected $paramSmartfilterName = 'arrFilter';
    protected $paramSeparatorValues = '-';
    protected $paramReplaceSpace = '-';
    protected $paramReplaceOther = '-';
    protected $paramPropertyListCode = 'VALUE';
    protected $paramPropertyElementCode = 'CODE';
    protected $paramPropertyDirectoryCode = 'UF_XML_ID';

    protected $paramPrefixMin = '';
    protected $paramPrefixMax = 'to-';

    protected $patternSmartfilterPriceFrom = 'price-%s-from-%s';
    protected $patternSmartfilterPriceTo = 'to-%s';

    /** @var \Aspro\Max\Smartseo\Condition\ConditionResult */
    private $conditionTreeResult = null;

    public function __construct(
      $iblockId,
      $condition,
      $generationType
      )
    {
        if(!in_array($generationType, [self::URL_TYPE_GENERATE_COMBO, self::URL_TYPE_GENERATE_COMBO,])) {
            $this->generationType = $generationType;
        } else {
            $this->generationType = self::URL_TYPE_GENERATE_COMBO;
        }

        $this->iblockId = $iblockId;
        $this->condition = $condition;

        if (\Bitrix\Main\Loader::includeModule('highloadblock')) {
            $this->isHighLoadBlockModule = true;
        }

        $this->conditionTreeResult = new Condition\ConditionResult($this->iblockId, $this->condition);
    }

    public function setSettings(array $settings)
    {
        if ($settings['SEPARATOR_VALUES']) {
            $this->paramSeparatorValues = $settings['SEPARATOR_VALUES'];
        }

        if ($settings['REPLACE_SPACE']) {
            $this->paramReplaceSpace = $settings['REPLACE_SPACE'];
        }

        if ($settings['REPLACE_OTHER']) {
            $this->paramReplaceOther = $settings['REPLACE_OTHER'];
        }

        if ($settings['PROPERTY_LIST_CODE']) {
            $this->paramPropertyListCode = $settings['PROPERTY_LIST_CODE'];
        }

        if ($settings['PROPERTY_ELEMENT_CODE']) {
            $this->paramPropertyElementCode = $settings['PROPERTY_ELEMENT_CODE'];
        }

        if ($settings['PROPERTY_DIRECTORY_CODE']) {
            $this->paramPropertyDirectoryCode = $settings['PROPERTY_DIRECTORY_CODE'];
        }

        if ($settings['PREFIX_NUMBER_MIN']) {
            $this->paramPrefixMin = $settings['PREFIX_NUMBER_MIN'];
        }

        if ($settings['PREFIX_NUMBER_MAX']) {
            $this->paramPrefixMax = $settings['PREFIX_NUMBER_MAX'];
        }

        if ($settings['SMARTFILTER_FILTER_NAME']) {
            $this->globalFilterName = $settings['SMARTFILTER_FILTER_NAME'];
        }

        if ($settings['IS_FRIENDLY_URL']) {
            $this->isFriendlyUrl = $settings['IS_FRIENDLY_URL'];
        }
    }

    public function getPropertyReplacements()
    {
        return [
            '#PROPERTY_ID#' => 'PROPERTY_ID',
            '#PROPERTY_CODE#' => 'PROPERTY_CODE',
            '#PROPERTY_VALUE#' => 'VALUES',
        ];
    }

    public function getPriceReplacements()
    {
        return [
            '#PRICE_ID#' => 'PROPERTY_ID',
            '#PRICE_CODE#' => 'PROPERTY_CODE',
            '#PRICE_VALUE#' => 'VALUES',
        ];
    }

    public function getPropertyTokens()
    {
        return array_keys($this->getPropertyReplacements());
    }

    public function getPriceTokens()
    {
        return array_keys($this->getPriceReplacements());
    }

    public function getSmartFilterTokens()
    {
        return [
            '#SMART_FILTER_PATH#'
        ];
    }

    public function validateInitialParams()
    {
        if (!$this->condition) {
            $this->addError('PropertyUrlHandler: Requered params CONDITION not value or not found');

            return false;
        }

        if (!$this->iblockId) {
            $this->addError('PropertyUrlHandler: Requered params IBLOCK ID not value or not found');

            return false;
        }

        return true;
    }

    public function generateResult(&$results)
    {
        $newResult = [];

        $i = 0;

        foreach ($results as $result) {
            if (!$result['PARAMS']['SECTION']) {
                continue;
            }

            $resultRows = $this->getResultValues($result['PARAMS']['SECTION']);

            if (!$resultRows) {
                continue;
            }

            foreach ($resultRows as $properties) {
                $newResult[$i]['URL_PAGE'] = $this->getReplacedUrl($result['URL_PAGE'], $properties);

                if($this->isFriendlyUrl) {
                    $newResult[$i]['URL_SMART_FILTER'] = $this->getReplacedUrlForBitrix($result['URL_SMART_FILTER'], $properties);
                } else {
                     $newResult[$i]['URL_SMART_FILTER'] = $this->getReplacedNotFriendlyUrlForBitrix($result['URL_SECTION'], $properties);
                }
                $newResult[$i]['PARAMS'] = $result['PARAMS'];
                $newResult[$i]['PARAMS']['PROPERTIES'] = $properties;

                $i++;
            }
        }

        $results = $newResult;
    }

    protected function getResultValues(array $section)
    {
        $items = $this->getConditionResult($section);
        $countItems = count($items);

        if (!$items) {
            return [];
        }

        $result = [];
        $resultCatalogPrice = [];

        $i = 0;

        if ($this->getCatalogPricePropertyFields()) {
            foreach ($this->getCatalogPricePropertyFields() as $catalogGroupField) {
                $resultCatalogPrice[] = $this->getModifiedCatalogPriceProperty($catalogGroupField);
            }
        }

        if ($this->generationType == self::URL_TYPE_GENERATE_MERGE) {
            if ($resultCatalogPrice && $items) {
                $result[$i] = $resultCatalogPrice;
            }

            foreach ($this->getAllPropertyFields() as $field) {
                $values = array_unique(
                  array_map(function($item) use ($field) {
                      return $item['PROPERTY_' . $field['PROPERTY_ID']];
                  }, $items)
                );

                if (!$values) {
                    continue;
                }

                $result[$i][] = $this->getModifiedProperty($field, $values);
            }
        }

        $i = 0;

        if ($this->generationType == self::URL_TYPE_GENERATE_COMBO) {
            foreach ($items as $item) {
                if ($resultCatalogPrice) {
                    $result[$i] = $resultCatalogPrice;
                }

                foreach ($this->getAllPropertyFields() as $field) {
                    $values = $item['PROPERTY_' . $field['PROPERTY_ID']];

                    $result[$i][] = $this->getModifiedProperty($field, [$values]);
                }

                $i++;
            }
        }

        return $result;
    }

    protected function getModifiedProperty($field, array $values)
    {
        $result = [
            'PROPERTY_ID' => $field['PROPERTY_ID'],
            'PROPERTY_CODE' => $field['PROPERTY_CODE'],
            'PROPERTY_TYPE' => $field['PROPERTY_TYPE'],
            'PROPERTY_NAME' => $field['PROPERTY_NAME'],
            'PROPERTY_DISPLAY_TYPE' => $field['PROPERTY_DISPLAY_TYPE'],
            'PROPERTY_LINK_IBLOCK_ID' => (int) $field['PROPERTY_LINK_IBLOCK_ID'],
        ];

        switch ($field['PROPERTY_TYPE']) {
            case 'N' :
                $result['_VALUES'] = $this->getMinMaxByLogics($field['LOGICS']);
                $result['VALUES'] = $this->getModifiedNumberValues($result['_VALUES']);
                break;

            case 'S' && $field['USER_TYPE'] == 'directory' && $this->isHighLoadBlockModule :
                $result['_VALUES'] = $values;
                $result['VALUES'] = $this->getModifiedDirectoryValues($values, $field['USER_TYPE_SETTINGS']);

                break;
            case 'S' :
                $result['_VALUES'] = $values;
                $result['VALUES'] = $this->getModifiedStringValues($values);

                break;
            case 'L' :
                $result['_VALUES'] = $values;
                $result['VALUES'] = $this->getModifiedEnumValues($values);

                break;
            case 'E' :
                $result['_VALUES'] = $values;
                if ($field['PROPERTY_LINK_IBLOCK_ID']) {
                    $result['VALUES'] = $this->getModifiedElementValues($values, $field['PROPERTY_LINK_IBLOCK_ID']);
                }

                break;
            default:
                break;
        }

        return $result;
    }

    protected function getModifiedCatalogPriceProperty($field)
    {
        $result = [
            'PROPERTY_ID' => $field['CATALOG_GROUP_ID'],
            'PROPERTY_NAME' => $field['CATALOG_GROUP_NAME'],
            'PROPERTY_CODE' => $field['CATALOG_GROUP_NAME'],
            'PROPERTY_TYPE' => 'PRICE',
        ];

        $result['_VALUES'] = $this->getMinMaxByLogics($field['LOGICS']);

        $result['VALUES'] = [
            'NEW' => array_filter([
                $result['_VALUES']['MIN'] ? $this->paramPrefixMin . $result['_VALUES']['MIN'] : null,
                $result['_VALUES']['MAX'] ? $this->paramPrefixMax . $result['_VALUES']['MAX'] : null,
            ]),
            'DISPLAY' => $result['_VALUES'],
        ];

        if($this->isFriendlyUrl) {
            $result['VALUES']['ORIGIN'] = array_filter([
                $result['_VALUES']['MIN']
                    ? sprintf($this->patternSmartfilterPriceFrom, $field['CATALOG_GROUP_NAME'], $result['_VALUES']['MIN'])
                    : null,
                $result['_VALUES']['MAX']
                    ? sprintf($this->patternSmartfilterPriceTo, $result['_VALUES']['MAX'])
                    : null,
            ]);
        } else {
            $result['VALUES']['ORIGIN'] = $result['_VALUES'];
        }

        return $result;
    }

    protected function getModifiedNumberValues(array $values)
    {
        $result = [];

        $result = [
            'NEW' => array_filter([
                $values['MIN'] ? $this->paramPrefixMin . $values['MIN'] : null,
                $values['MAX'] ? $this->paramPrefixMax . $values['MAX'] : null,
            ]),
            'DISPLAY' => $values,
        ];

        if($this->isFriendlyUrl) {
            $result['ORIGIN'] = array_filter([
                $values['MIN'] ? 'from-' . $values['MIN'] : null,
                $values['MAX'] ? 'to-' . $values['MAX'] : null,
            ]);
        } else {
             $result['ORIGIN'] = array_filter([
                $values['MIN'] ? $values['MIN'] : null,
                $values['MAX'] ? $values['MAX'] : null,
            ]);
        }

        return $result;
    }

    protected function getModifiedStringValues(array $values)
    {
        $newValues = [];
        $originValues = [];

        foreach ($values as $value) {
            $value = trim($value);
            $newValues[] = \Cutil::translit($value, 'ru', [
                'replace_space' => '-',
                'replace_other' => '-',
            ]);
            $originValues[] = $value;
        }

        natcasesort($newValues);
        natcasesort($originValues);

        return [
            'NEW' => $newValues,
            'ORIGIN' => $originValues,
            'DISPLAY' => $originValues,
        ];
    }

    protected function getModifiedEnumValues(array $values)
    {
        $rows = \Bitrix\Iblock\PropertyEnumerationTable::getList([
              'select' => [
                  'ID',
                  'VALUE',
                  'XML_ID',
              ],
              'filter' => [
                  '=ID' => $values
              ],
              'order' => [
                  'SORT',
                  'VALUE',
              ],
              'cache' => [
                  'ttl' => self::CACHE_TTL_ENUM_VALUES,
              ]
          ])->fetchAll();

        $newValues = [];
        $originValues = [];
        $displayValues = [];
        foreach ($rows as $row) {
            $newValues[] = \Cutil::translit($row[$this->paramPropertyListCode] ?: $row['VALUE'], 'ru', [
                'replace_space' => $this->paramReplaceSpace,
                'replace_other' => $this->paramReplaceOther,
            ]);
            $originValues[] = $this->isFriendlyUrl ? $row['XML_ID'] : $row['ID'];
            $displayValues[] = $row['VALUE'];
        }

        return [
            'NEW' => $newValues,
            'ORIGIN' => $originValues,
            'DISPLAY' => $displayValues,
        ];
    }

    protected function getModifiedElementValues(array $values, $iblockId)
    {
        $rows = \Bitrix\Iblock\ElementTable::getList([
              'select' => [
                  'ID',
                  'CODE',
                  'NAME',
                  'XML_ID',
              ],
              'filter' => [
                  '=ID' => $values,
                  'IBLOCK_ID' => $iblockId,
              ],
              'order' => [
                  'SORT',
                  'NAME',
                  'CODE',
              ],
              'cache' => [
                  'ttl' => self::CACHE_TTL_ELEMENTS,
              ]
          ])->fetchAll();

        $newValues = [];
        $originValues = [];
        $displayValues = [];
        foreach ($rows as $row) {
            $newValues[] = \Cutil::translit($row[$this->paramPropertyElementCode] ?: $row['NAME'], 'ru', [
                  'replace_space' => $this->paramReplaceSpace,
                  'replace_other' => $this->paramReplaceOther,
            ]);

            if($this->isFriendlyUrl) {
                $originValues[] = $row['CODE'] ?: $row['NAME'];
            } else {
                $originValues[] = $row['ID'];
            }

            $displayValues[] = $row['NAME'];
        }

        return [
            'NEW' => $newValues,
            'ORIGIN' => $originValues,
            'DISPLAY' => $displayValues,
        ];
    }

    protected function getModifiedDirectoryValues(array $values, $userTypeSettings)
    {
        if (!$userTypeSettings) {
            return [];
        }

        $settings = unserialize($userTypeSettings, ['allowed_classes' => false]);
        $tableName = $settings['TABLE_NAME'];

        $hlblock = HighloadBlockTable::getList([
              'filter' => [
                  '=TABLE_NAME' => $tableName
              ]
          ])->fetch();

        if (!$hlblock) {
            return [];
        }

        $hlClassName = HighloadBlockTable::compileEntity($hlblock)->getDataClass();

        $rows = $hlClassName::getList([
              'select' => [
                  'ID',
                  'UF_NAME',
                  'UF_XML_ID',
              ],
              'filter' => [
                  'UF_XML_ID' => $values
              ],
              'order' => [
                  'UF_SORT',
                  'UF_NAME',
                  'UF_XML_ID'
              ]
          ])->fetchAll();

        $newValues = [];
        $originValues = [];
        $displayValues = [];
        foreach ($rows as $row) {
            $newValues[] = \Cutil::translit($row[$this->paramPropertyDirectoryCode] ?: $row['UF_XML_ID'], 'ru', [
                'replace_space' => $this->paramReplaceSpace,
                'replace_other' => $this->paramReplaceOther,
            ]);
            $originValues[] = $row['UF_XML_ID'];
            $displayValues[] = $row['UF_NAME'];
        }

        return [
            'NEW' => $newValues,
            'ORIGIN' => $originValues,
            'DISPLAY' => $displayValues,
        ];
    }

    protected function getPropertyFields()
    {
        $properties = $this->conditionTreeResult->getPropertyFields();

        usort($properties, function($a, $b) {
            return ($a['PROPERTY_SORT'] > $b['PROPERTY_SORT']);
        });

        return $properties;
    }

    protected function getSkuPropertyFields()
    {
        $skuProperties = $this->conditionTreeResult->getSkuPropertyFields();

        usort($skuProperties, function($a, $b) {
            return ($a['PROPERTY_SORT'] > $b['PROPERTY_SORT']);
        });

        return $skuProperties;
    }

    protected function getAllPropertyFields()
    {
        return array_merge($this->getPropertyFields(), $this->getSkuPropertyFields());
    }

    protected function getCatalogPricePropertyFields()
    {
        return $this->conditionTreeResult->getCatalogPricePropertyFields();
    }

    private function getConditionResult($section)
    {
        $this->conditionTreeResult->setSectionMargins([
            [
                'LEFT_MARGIN' => $section['LEFT_MARGIN'],
                'RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
            ]
        ]);

        $result = $this->conditionTreeResult->getResult();

        return $result;
    }

    protected function getReplacedUrl($url, $data)
    {
        $propertyReplaseTokens = array_map(function($token) {
            return '/' . $token . '/';
        }, $this->getPropertyTokens());

        $priceReplaseTokens = array_map(function($token) {
            return '/' . $token . '/';
        }, $this->getPriceTokens());

        preg_match_all('/\{(.+?)\}/', $url, $matches);

        if (!$matches) {
            return $url;
        }

        $urlReplacementPatterns = $matches[0];
        $tokenReplacementPatterns = $matches[1];

        $result = null;

        foreach ($data as $property) {
            if (!$property['VALUES']['NEW']) {
                continue;
            }

            if ($property['PROPERTY_TYPE'] == 'PRICE') {
                $replaseTokens = $priceReplaseTokens;
            } else {
                $replaseTokens = $propertyReplaseTokens;
            }

            $i = 0;
            foreach ($tokenReplacementPatterns as $template) {
                $propertyCode = \Cutil::translit($property['PROPERTY_CODE'], 'ru', [
                    'replace_space' => $this->paramReplaceSpace,
                    'replace_other' => $this->paramReplaceOther,
                ]);
                $replaceResultStr = preg_replace($replaseTokens, [
                    $property['PROPERTY_ID'],
                    $propertyCode,
                    implode($this->paramSeparatorValues, $property['VALUES']['NEW'])
                    ], $template
                );

                if($replaceResultStr != $template) {
                    $result[$i] .= mb_strtolower($replaceResultStr);
                }

                $i++;
            }
        }

        $i = 0;
        foreach ($urlReplacementPatterns as $template) {
            $url = str_replace(['{', '}'], '', preg_replace($template, $result[$i], $url));

            $i++;
        }

        $url = preg_replace('|([/]+)|s', '/', $url);

        return $url;
    }

    protected function getReplacedUrlForBitrix($urlSmartFilterTemplate, $data)
    {
        $patterns = array_map(function($token) {
            return '/' . $token . '/';
        }, $this->getSmartFilterTokens());

        $result = '';

        $replacements = [];

        $_temp = [];
        foreach ($data as $property) {
            if (!$property['VALUES']['ORIGIN']) {
                continue;
            }

            $separator = '-is-';
            $paramSeparatorValue = '-or-';

            if ($property['PROPERTY_TYPE'] == 'N') {
                $separator = '-';
                $paramSeparatorValue = '-';
            }

            if ($property['PROPERTY_TYPE'] == 'PRICE') {
                $property['PROPERTY_CODE'] = '';
                $separator = '';
                $paramSeparatorValue = '-';
            }

            $_temp[] = mb_strtolower($property['PROPERTY_CODE'] . $separator . implode($paramSeparatorValue, $property['VALUES']['ORIGIN']));
        }

        $replacements[] = implode('/', $_temp);

        $url = preg_replace($patterns, $replacements, $urlSmartFilterTemplate);

        $url = preg_replace('|([/]+)|s', '/', $url);

        return $url;
    }

    protected function getReplacedNotFriendlyUrlForBitrix($urlSection, $data)
    {
        $filterParams = [];
        $filterParams['set_filter'] = 'y';
        $filterPriceParams = [];

        foreach ($data as $property) {
             if($property['PROPERTY_TYPE'] == 'PRICE') {
                if($property['VALUES']['ORIGIN']['MIN']) {
                    $_code = $this->globalFilterName . '_P' . $property['PROPERTY_ID']. '_MIN';
                    $filterPriceParams[$_code] = $property['VALUES']['ORIGIN']['MIN'];
                }

                if($property['VALUES']['ORIGIN']['MAX']) {
                    $_code = $this->globalFilterName . '_P' . $property['PROPERTY_ID']. '_MAX';
                    $filterPriceParams[$_code] = $property['VALUES']['ORIGIN']['MAX'];
                }

                continue;
            }

            if ($property['PROPERTY_TYPE'] == 'N') {
                if ($property['VALUES']['ORIGIN']['MIN']) {
                    $_code = $this->globalFilterName . '_' . $property['PROPERTY_ID'] . '_MIN';
                    $filterParams[$_code] = $property['VALUES']['ORIGIN']['MIN'];
                }

                if ($property['VALUES']['ORIGIN']['MAX']) {
                    $_code = $this->globalFilterName . '_' . $property['PROPERTY_ID'] . '_MAX';
                    $filterParams[$_code] = $property['VALUES']['ORIGIN']['MAX'];
                }

                continue;
            }

            foreach ($property['VALUES']['ORIGIN'] as $value) {
                $_code = $this->globalFilterName . '_' . $property['PROPERTY_ID'];
                $value = abs(crc32(htmlspecialcharsbx($value)));

                if(in_array($property['PROPERTY_DISPLAY_TYPE'], ['F', 'G', 'H'])) {
                    $filterParams[$_code . '_'  . $value] = 'Y';
                } else {
                    $filterParams[$_code] = $value;
                }
            }
        }

        $url = \CHTTP::urlAddParams($urlSection, array_merge($filterParams, $filterPriceParams), [
            'skip_empty' => true,
            'encode' => true,
        ]);

        $url = preg_replace('|([/]+)|s', '/', $url);

        return $url;
    }

    private function getMinMaxByLogics($logics)
    {
        $min = null;
        $max = null;

        foreach ($logics as $logic) {
            if ($logic['OPERATOR'] == self::LOGIC_EGR) {
                $min = is_array($logic['VALUE']) ? min($logic['VALUE']) : $logic['VALUE'];
            }

            if ($logic['OPERATOR'] == self::LOGIC_ELS) {
                $max = is_array($logic['VALUE']) ? max($logic['VALUE']) : $logic['VALUE'];
            }
        }

        return array_filter([
            'MIN' => $min,
            'MAX' => $max,
        ]);
    }
}
