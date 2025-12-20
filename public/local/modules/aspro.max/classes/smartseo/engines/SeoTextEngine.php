<?php

namespace Aspro\Max\Smartseo\Engines;

use Aspro\Max\Smartseo,
  Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SeoTextEngine extends \Aspro\Max\Smartseo\Engines\Engine
{
    function __construct()
    {

    }

    public function update($seotextId)
    {

    }

    public function updateSections($iblockId, array $sectionIds, array $data, bool $isRewrite = false, $noUpdate = false)
    {
        global $USER_FIELD_MANAGER;

        $filter = array_filter([
            'IBLOCK_ID' => $iblockId,
            'ID' => $sectionIds,
        ]);

        $data = $this->getPrepareSectionData($iblockId, $data);

        if(!$filter || !$data) {
            return false;
        }

        $entityTemplate = new Smartseo\Template\Entity\SeoText(0);

        $updateResult = [];
        foreach ($this->getSectionList($filter) as $section) {
            $entityTemplate->setFields([
                'IBLOCK_ID' => $iblockId,
                'SECTIONS' => [
                    $section
                 ]
            ]);

            if($data['FIELDS']) {
                $_fields = [];

                if (!$isRewrite) {
                    $_fields = array_filter($data['FIELDS'], function($code) use ($section) {
                        if (!trim($section[$code])) {
                            return true;
                        }

                        return false;
                    }, ARRAY_FILTER_USE_KEY);
                } else {
                    $_fields = $data['FIELDS'];
                }

                if ($_fields) {
                    if(!$noUpdate) {
                        $this->fillDataValueByTemplate($entityTemplate, $_fields);

                        $iblockSection = new \CIBlockSection;
                        $update = $iblockSection->Update($section['ID'], $_fields);

                        if (!$update) {
                            $this->addError($update->LAST_ERROR);

                            break;
                        }
                    }

                    $updateResult[$section['ID']] = true;
                }
            }

            if($data['PROPERTIES']) {
                $_fields = [];

                if (!$isRewrite) {
                    $userFields = $USER_FIELD_MANAGER->getUserFields(
                        'IBLOCK_' . $section['IBLOCK_ID'] . '_SECTION',
                        $section['ID']
                    );

                    $_fields = array_filter($data['PROPERTIES'], function($code) use ($userFields) {
                        if (!trim($userFields[$code]['VALUE'])) {
                            return true;
                        }

                        return false;
                    }, ARRAY_FILTER_USE_KEY);
                } else {
                    $_fields = $data['PROPERTIES'];
                }

                if($_fields) {
                    if(!$noUpdate) {
                        $this->fillDataValueByTemplate($entityTemplate, $_fields);
                        $USER_FIELD_MANAGER->Update('IBLOCK_' . $section['IBLOCK_ID'] . '_SECTION', $section['ID'], $_fields);
                    }
                    $updateResult[$section['ID']] = true;
                }
            }
        }

        $count = count($updateResult);
        if(!$this->hasErrors()) {
            $this->setResult([
                'MESSAGE' => Loc::getMessage('SMARTSEO_ENGINE_SEOTEXT__SUCCESS__UPDATE_SECTION', [
                    '#COUNT#' => $count,
                ]),
                'COUNT' => $count,
            ]);
        }
    }

    public function updateElements($iblockId, array $sectionIds, string $condition, array $data, bool $isRewrite = false, $noUpdate = false)
    {
        if(!$condition) {
            $this->addError(Loc::getMessage('SMARTSEO_ENGINE_SEOTEXT___VALIDATE__CONDITION_TREE'));
            return false;
        }

        $conditionTreeResult = new Smartseo\Condition\ConditionResult($iblockId, $condition, [
            new Smartseo\Condition\Controls\GroupBuildControls(),
            new Smartseo\Condition\Controls\IblockPropertyBuildControls($iblockId, [
                'ONLY_PROPERTY_SMART_FILTER' => 'N',
                'SHOW_PROPERTY_SKU' => 'N',
              ])
        ]);

        $conditionTreeResult->isOnlyActiveElement(false);
        $conditionTreeResult->setSectionMargins($this->getSectionMargins($sectionIds));

        $elementIds = $conditionTreeResult->getResultElementIds();
        $properties = $this->getPrepareElementData($iblockId, $data);

        $updateResult = [];
        foreach ($this->getElementList($iblockId, $elementIds, $properties) as $element) {
            $entityTemplate = new \Bitrix\Iblock\Template\Entity\Element($element['ID']);

            if($properties['FIELDS']) {
                if (!$isRewrite) {
                    $_fields = array_filter($properties['FIELDS'], function($code) use ($element) {
                        if (!trim($element[$code])) {
                            return true;
                        }

                        return false;
                    }, ARRAY_FILTER_USE_KEY);
                } else {
                    $_fields = $properties['FIELDS'];
                }

                if ($_fields) {
                    if(!$noUpdate) {
                        $this->fillElementValueByTemplate($entityTemplate, $_fields);
                        $el = new \CIBlockElement;
                        $update = $el->Update($element['ID'], $_fields);

                        if (!$update) {
                            $this->addError($update->LAST_ERROR);

                            break;
                        }
                    }

                    $updateResult[$element['ID']] = true;
                }
            }

            if ($properties['PROPERTIES']) {
                $_fields = [];

                if (!$isRewrite) {
                    $_fields = array_filter($properties['PROPERTIES'], function($code) use ($element) {
                        if (!$element['PROPERTY_' . $code . '_VALUE']['TEXT']) {
                            return true;
                        }

                        return false;
                    }, ARRAY_FILTER_USE_KEY);
                } else {
                    $_fields = $properties['PROPERTIES'];
                }

                if ($_fields) {
                    if(!$noUpdate) {
                        $this->fillElementValueByTemplate($entityTemplate, $_fields);
                        \CIBlockElement::SetPropertyValuesEx($element['ID'], false, $_fields);
                    }

                    $updateResult[$element['ID']] = true;
                }
            }
        }

        $count = count($updateResult);
        if(!$this->hasErrors()) {
            $this->setResult([
                'MESSAGE' => Loc::getMessage('SMARTSEO_ENGINE_SEOTEXT__SUCCESS__UPDATE_ELEMENT', [
                    '#COUNT#' => $count,
                ]),
                'COUNT' => $count,
            ]);
        }

    }

    private function getPrepareSectionData($iblockId, $data)
    {
        $sectionFields = Smartseo\Models\SmartseoSeoTextPropertyTable::getAllSectionFields($iblockId);

        $result = [];
        foreach ($data as $code => $value) {
            $_code = preg_replace('/_property.*/', '', $code);

            if(!$sectionFields[$_code]) {
                continue;
            }

            if($sectionFields[$_code]['ENTITY_TYPE'] == Smartseo\Models\SmartseoSeoTextPropertyTable::ENTITY_TYPE_SECTION_FIELD) {
                $result['FIELDS'][$_code] = $value;
            }

            if($sectionFields[$_code]['ENTITY_TYPE'] == Smartseo\Models\SmartseoSeoTextPropertyTable::ENTITY_TYPE_SECTION_PROPERTY) {
                $result['PROPERTIES'][$_code] = $value;
            }
        }

        return $result;
    }

    private function getPrepareElementData($iblockId, $data)
    {
        $elementFields = Smartseo\Models\SmartseoSeoTextPropertyTable::getAllElementnFields($iblockId);

        $result = [];

        foreach ($data as $code => $value) {
            $_code = preg_replace('/_property.*/', '', $code);

            if(!$elementFields[$_code]) {
                continue;
            }

            if($elementFields[$_code]['ENTITY_TYPE'] == Smartseo\Models\SmartseoSeoTextPropertyTable::ENTITY_TYPE_ELEMENT_FIELD) {
                $result['FIELDS'][$_code] = $value;
            }

            if($elementFields[$_code]['ENTITY_TYPE'] == Smartseo\Models\SmartseoSeoTextPropertyTable::ENTITY_TYPE_ELEMENT_PROPERTY) {
                $result['PROPERTIES'][$_code] = [
                    'VALUE' => [
                        'TEXT' => $value,
                    ]
                ];
            }
        }

        return $result;
    }

    private function getSectionList(array $filter)
    {
        if(!$filter) {
            $this->addError('No filter parameters. Absent parameters expected IBLOCK_ID and sections ID.');

            return [];
        }

        return \Bitrix\Iblock\SectionTable::getList([
            'select' => [
                'ID',
                'IBLOCK_ID',
                'CODE',
                'NAME',
                'DESCRIPTION'
            ],
            'filter' => $filter
        ])->fetchAll();
    }

    private function getElementList($iblockId, $elementIds, array $properties)
    {
        if(!$elementIds) {
            return [];
        }

        $filter = [
            'IBLOCK_ID' => $iblockId,
            '=ID' => $elementIds
        ];

        $select = ['ID'];

        if($properties['FIELDS']) {
            $select = array_merge($select, array_keys($properties['FIELDS']));
        }

        if($properties['PROPERTIES']) {
            foreach ($properties['PROPERTIES'] as $propertyCode => $propertyValue) {
                $select[] = 'PROPERTY_' . $propertyCode;
            }
        }

        $rsElements = \CIBlockElement::GetList([], $filter, false, [], $select);

        $elements = [];
        while ($element = $rsElements->Fetch()) {
            $elements[] = $element;
        }

        return $elements;
    }

    private function fillDataValueByTemplate($entityTemplate, array &$data)
    {
        foreach ($data as &$value) {
            if(is_array($value) && $value['VALUE']['TEXT']) {
                $value['VALUE']['TEXT'] = \Bitrix\Iblock\Template\Engine::process($entityTemplate, $value['VALUE']['TEXT']);
            } else {
                $value = \Bitrix\Iblock\Template\Engine::process($entityTemplate, $value);
            }
        }
    }

    private function fillElementValueByTemplate($entityTemplate, array &$data)
    {
        foreach ($data as &$value) {
            if(is_array($value) && $value['VALUE']['TEXT']) {
                $value['VALUE']['TEXT'] = str_replace('section', 'sections', $value['VALUE']['TEXT']);
                $value['VALUE']['TEXT'] = \Bitrix\Iblock\Template\Engine::process($entityTemplate, $value['VALUE']['TEXT']);
            } else {
                $value = str_replace('section', 'sections', $value);
                $value = \Bitrix\Iblock\Template\Engine::process($entityTemplate, $value);
            }
        }
    }

    private function getSectionMargins(array $sectionIds)
    {
        if(!$sectionIds) {
            return [];
        }

        return \Bitrix\Iblock\SectionTable::getList([
              'select' => [
                  'LEFT_MARGIN',
                  'RIGHT_MARGIN'
              ],
              'filter' => [
                  '=ID' => $sectionIds,
              ],
              'cache' => [
                  'ttl' => Smartseo\Admin\Settings\SettingSmartseo::getInstance()->getCacheTable(),
              ],
          ])->fetchAll();
    }
}
