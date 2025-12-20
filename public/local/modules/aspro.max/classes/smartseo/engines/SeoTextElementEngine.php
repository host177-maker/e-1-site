<?php

namespace Aspro\Max\Smartseo\Engines;

use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SeoTextElementEngine extends \Aspro\Max\Smartseo\Engines\Engine
{
    const SESSION_NAME = 'SEO_TEXT_ELEMENT_ENGINE';
    const STATUS_UPDATE = 'update';
    const STATUS_SUCCESS = 'success';

    protected $iblockId;
    protected $sectionIds;
    protected $dataUpdate;
    protected $condition;
    protected $isRewrite;
    protected $sessionUnique = '';

    private $elementIds = [];
    private $elementFields = [];
    private $lastUpdateElementId = 0;
    private $stepMaxExecuteTime = 20;
    private $countElements = 0;
    private $countUpdateElements = 0;

    /** @var Smartseo\Condition\ConditionResult */
    protected $conditionTreeResult;

    function __construct($isRewrite = false)
    {
        $this->setRewrite($isRewrite);
    }

    public function validate()
    {
        if (!$this->condition) {
            $this->addError(Loc::getMessage('SMARTSEO_STEE__VALIDATE__CONDITION'));

            return false;
        }

        if (!$this->iblockId) {
            $this->addError(Loc::getMessage('SMARTSEO_STEE__VALIDATE__IBLOCK'));

            return false;
        }

        return true;
    }

    public function setRewrite(bool $value)
    {
        $this->isRewrite = $value;
    }

    public function setIblockId($iblockId)
    {
        $this->iblockId = $iblockId;
    }

    public function setSectionIds($sectionIds = [])
    {
        $this->sectionIds = $sectionIds;
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    public function setDataUpdate($data)
    {
        $this->dataUpdate = $data;
    }

    public function setLastUpdateElementId($elementId)
    {
        $this->lastUpdateElementId = $elementId;
    }

    public function setCountElements($value)
    {
        $this->countElements = $value;
    }

    public function getCountElements()
    {
        return $this->countElements;
    }

    public function setCountUpdateElements(int $value)
    {
        $this->countUpdateElements += $value;
    }

    public function getCountUpdateElements()
    {
        return $this->countUpdateElements;
    }

    public function setSessionUnique($value)
    {
        $this->sessionUnique = $value;
    }

    public function getSessionUnique()
    {
        return $this->sessionUnique;
    }

    public function getUpdateInfo()
    {
        if (!$this->validate()) {
            return false;
        }
        $this->initConditionResult();

        foreach ($this->getElementList() as $element) {
            $updated = [];

            $fields = $this->getElementUpdateFields($element);
            if ($fields) {
                $updated[$element['ID']] = true;
            }

            $properties = $this->getElementUpdateProperties($element);
            if ($properties) {
                $updated[$element['ID']] = true;
            }

            $this->setCountUpdateElements(count($updated));
        }

        if (!$this->hasErrors()) {
            return [
                'COUNT_ELEMENTS' => $this->getCountUpdateElements(),
            ];
        }

        return [];
    }

    public function update($needSteps = false)
    {
        if (!$this->validate()) {
            return false;
        }

        if ($needSteps) {
            $startExecuteTime = time();
        }

        $this->initConditionResult();

        foreach ($this->getElementList() as $element) {
            $entityTemplate = new \Bitrix\Iblock\Template\Entity\Element($element['ID']);
            $updated = [];

            $fields = $this->getElementUpdateFields($element);
            if ($fields) {
                $this->fillElementValueByTemplate($entityTemplate, $fields);
                $this->updateElement($element['ID'], $fields);

                if (!$this->hasErrors()) {
                    $updated[$element['ID']] = true;
                }
            }

            $properties = $this->getElementUpdateProperties($element);
            if ($properties) {
                $this->fillElementValueByTemplate($entityTemplate, $properties);
                $this->updateElementPropertyValues($element['ID'], $properties);

                if (!$this->hasErrors()) {
                    $updated[$element['ID']] = true;
                }
            }

            $this->setCountUpdateElements(count($updated));

            if ($needSteps) {
                $endExecuteTime = time();
                if ($endExecuteTime - $startExecuteTime >= $this->stepMaxExecuteTime) {
                    $this->setLastUpdateElementId($element['ID']);
                    $this->updateCurrentSession();

                    $this->setResult([
                        'STATUS' => self::STATUS_UPDATE,
                        'MESSAGE' => Loc::getMessage('SMARTSEO_STEE__SUCCESS__UPDATE_ELEMENT', [
                            '#COUNT#' => $this->getCountUpdateElements(),
                        ]),
                        'COUNT_ELEMENTS' => $this->getCountElements(),
                        'COUNT_UPDATE_ELEMENTS' => $this->getCountUpdateElements(),
                    ]);

                    return;
                }
            }
        }

        if (!$this->hasErrors()) {
            $this->setResult([
                'STATUS' => self::STATUS_SUCCESS,
                'MESSAGE' => Loc::getMessage('SMARTSEO_STEE__SUCCESS__UPDATE_ELEMENT', [
                    '#COUNT#' => $this->getCountUpdateElements(),
                ]),
                'COUNT_ELEMENTS' => $this->getCountElements(),
                'COUNT_UPDATE_ELEMENTS' => $this->getCountUpdateElements(),
            ]);

            $this->deleteCurrentSession();
        }
    }

    public function createUpdateSession()
    {
        $unique = uniqid();

        $this->setSessionUnique($unique);

        $info = $this->getUpdateInfo();

        if ($this->hasErrors()) {
            return false;
        }

        $_SESSION[self::SESSION_NAME][$this->getSessionUnique()] = [
            'IBLOCK_ID' => $this->iblockId,
            'SECTIONS' => $this->sectionIds,
            'CONDITION' => $this->condition,
            'DATA' => $this->dataUpdate,
            'IS_REWRITE' => $this->isRewrite,
            'LAST_ELEMENT_ID' => $this->lastUpdateElementId,
            'COUNT_ELEMENTS' => $info['COUNT_ELEMENTS'],
            'COUNT_UPDATE_ELEMENTS' => 0,
        ];

        return $this->getSessionUnique();
    }

    public function updateCurrentSession()
    {
        $_SESSION[self::SESSION_NAME][$this->getSessionUnique()] = [
            'IBLOCK_ID' => $this->iblockId,
            'SECTIONS' => $this->sectionIds,
            'CONDITION' => $this->condition,
            'DATA' => $this->dataUpdate,
            'IS_REWRITE' => $this->isRewrite,
            'LAST_ELEMENT_ID' => $this->lastUpdateElementId,
            'COUNT_ELEMENTS' => $this->getCountElements(),
            'COUNT_UPDATE_ELEMENTS' => $this->getCountUpdateElements(),
        ];
    }

    public function deleteCurrentSession()
    {
        unset($_SESSION[self::SESSION_NAME][$this->getSessionUnique()]);
    }

    public function loadBySession($sessionUnique)
    {
        if (!$_SESSION['SEO_TEXT_ELEMENT_ENGINE'][$sessionUnique]) {
            $this->addError('No found session data');

            return false;
        }

        $session = $_SESSION['SEO_TEXT_ELEMENT_ENGINE'][$sessionUnique];

        $this->setRewrite($session['IS_REWRITE']);
        $this->setSessionUnique($sessionUnique);
        $this->setLastUpdateElementId($session['LAST_ELEMENT_ID']);
        $this->setIblockId($session['IBLOCK_ID']);
        $this->setSectionIds($session['SECTIONS']);
        $this->setCondition($session['CONDITION']);
        $this->setDataUpdate($session['DATA']);
        $this->setCountElements($session['COUNT_ELEMENTS']);
        $this->setCountUpdateElements($session['COUNT_UPDATE_ELEMENTS']);
    }

    protected function initConditionResult()
    {
        $this->conditionTreeResult = new Smartseo\Condition\ConditionResult($this->iblockId, $this->condition, [
            new Smartseo\Condition\Controls\GroupBuildControls(),
            new Smartseo\Condition\Controls\IblockPropertyBuildControls($this->iblockId, [
                'ONLY_PROPERTY_SMART_FILTER' => 'N',
                'SHOW_PROPERTY_SKU' => 'N',
            ])
        ]);

        $this->conditionTreeResult->isOnlyActiveElement(false);
        $this->conditionTreeResult->setSectionMargins($this->getSectionMargins($this->sectionIds));
    }

    protected function getElementList()
    {
        if (!$this->getElementIds()) {
            return [];
        }

        $rsElements = \CIBlockElement::GetList(['ID' => 'ASC'], $this->getFilter(), false, [], $this->getSelect());

        $elements = [];
        while ($element = $rsElements->Fetch()) {
            $elements[] = $element;
        }

        return $elements;
    }

    protected function updateElement($elementId, $fields)
    {
        $el = new \CIBlockElement;
        $update = $el->Update($elementId, $fields);

        if (!$update) {
            $this->addError($update->LAST_ERROR);

            return false;
        }

        return true;
    }

    protected function updateElementPropertyValues($elementId, $fields)
    {
        \CIBlockElement::SetPropertyValuesEx($elementId, false, $fields);
    }

    protected function getElementUpdateFields($element)
    {
        $elementFields = $this->getFields();

        $result = [];

        if ($this->isRewrite) {
            $result = $elementFields['FIELDS'];
        } else {
            foreach ($elementFields['FIELDS'] as $fieldCode => $fieldValue) {
                if (trim($element[$fieldCode]) || !$fieldValue) {
                    continue;
                }

                $result[$fieldCode] = $fieldValue;
            }
        }

        return $result;
    }

    protected function getElementUpdateProperties($element)
    {
        $elementFields = $this->getFields();

        $result = [];

        if ($this->isRewrite) {
            $result = $elementFields['PROPERTIES'];
        } else {
            foreach ($elementFields['PROPERTIES'] as $fieldCode => $fieldValue) {
                if (!$element['PROPERTY_' . $fieldCode . '_VALUE']['TEXT'] || !$fieldValue) {
                    continue;
                }

                $result[$fieldCode] = $fieldValue;
            }
        }

        return $result;
    }

    private function getElementIds()
    {
        if ($this->elementIds) {
            return $this->elementIds;
        }

        $filter = [];
        if ($this->lastUpdateElementId) {
            $filter = [
                '>IBLOCK_ELEMENT_ID' => (int) $this->lastUpdateElementId,
            ];
        }

        $this->elementIds = $this->conditionTreeResult->getResultElementIds($filter);

        return $this->elementIds;
    }

    private function getFields()
    {
        if ($this->elementFields) {
            return $this->elementFields;
        }

        $this->elementFields = $this->getPrepareElementFields($this->iblockId, $this->dataUpdate);

        return $this->elementFields;
    }

    private function fillElementValueByTemplate($entityTemplate, array &$data)
    {
        foreach ($data as &$value) {
            if (is_array($value) && $value['VALUE']['TEXT']) {
                $value['VALUE']['TEXT'] = str_replace('section', 'sections', $value['VALUE']['TEXT']);
                $value['VALUE']['TEXT'] = \Bitrix\Iblock\Template\Engine::process($entityTemplate, $value['VALUE']['TEXT']);
            } else {
                $value = str_replace('section', 'sections', $value);
                $value = \Bitrix\Iblock\Template\Engine::process($entityTemplate, $value);
            }
        }
    }

    private function getPrepareElementFields($iblockId, $data)
    {
        $elementFields = Smartseo\Models\SmartseoSeoTextPropertyTable::getAllElementnFields($iblockId);

        $result = [];

        foreach ($data as $code => $value) {
            $_code = preg_replace('/_property.*/', '', $code);

            if (!$elementFields[$_code]) {
                continue;
            }

            if ($elementFields[$_code]['ENTITY_TYPE'] == Smartseo\Models\SmartseoSeoTextPropertyTable::ENTITY_TYPE_ELEMENT_FIELD) {
                $result['FIELDS'][$_code] = $value;
            }

            if ($elementFields[$_code]['ENTITY_TYPE'] == Smartseo\Models\SmartseoSeoTextPropertyTable::ENTITY_TYPE_ELEMENT_PROPERTY) {
                $result['PROPERTIES'][$_code] = [
                    'VALUE' => [
                        'TEXT' => $value,
                    ]
                ];
            }
        }

        return $result;
    }

    private function getSelect()
    {
        $elementFields = $this->getFields();

        $result = ['ID'];

        if ($elementFields['FIELDS']) {
            $result = array_merge($result, array_keys($elementFields['FIELDS']));
        }

        if ($elementFields['PROPERTIES']) {
            foreach ($elementFields['PROPERTIES'] as $propertyCode => $propertyValue) {
                $result[] = 'PROPERTY_' . $propertyCode;
            }
        }

        return $result;
    }

    private function getFilter()
    {
        return [
            'IBLOCK_ID' => $this->iblockId,
            '=ID' => $this->getElementIds()
        ];
    }

    private function getSectionMargins(array $sectionIds)
    {
        if (!$this->sectionIds) {
            return [];
        }

        return \Bitrix\Iblock\SectionTable::getList([
            'select' => [
                'LEFT_MARGIN',
                'RIGHT_MARGIN'
            ],
            'filter' => [
                '=ID' => $this->sectionIds,
            ],
            'cache' => [
                'ttl' => Smartseo\Admin\Settings\SettingSmartseo::getInstance()->getCacheTable(),
            ],
        ])->fetchAll();
    }
}
