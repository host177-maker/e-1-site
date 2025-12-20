<?php

namespace Aspro\Max\Smartseo\Admin\Actions;

use Aspro\Max\Smartseo;

class Replication
{
    private $errors = [];

    function __construct()
    {

    }

    public function copyFilterRule($sourceId)
    {
        $sourceFilterRule = Smartseo\Models\SmartseoFilterRuleTable::getRow([
              'select' => [
                  '*',
              ],
              'filter' => [
                  'ID' => $sourceId,
              ]
        ]);

        if(!$sourceFilterRule) {
            $this->addError('Source element not found');

            return false;
        }
    }

    public function copyFilterRuleCondition($sourceId)
    {
        $sourceFilterCondition = Smartseo\Models\SmartseoFilterConditionTable::getRow([
              'select' => [
                  '*',
              ],
              'filter' => [
                  'ID' => $sourceId,
              ]
        ]);

        if (!$sourceFilterCondition) {
            $this->addError('Source element not found');

            return false;
        }

        $seoTemplateFields = Smartseo\Models\SmartseoSeoTemplateTable::getList([
            'select' => [
                '*'
            ],
            'filter' => [
                'ENTITY_TYPE' => 'FC',
                'ENTITY_ID' => $sourceFilterCondition['ID'],
            ]
        ])->fetchAll();

        $sourceFilterCondition['NAME'] = '[copy id: ' . $sourceFilterCondition['ID'] . '] ' . $sourceFilterCondition['NAME'];
        unset($sourceFilterCondition['ID']);

        $result = Smartseo\Models\SmartseoFilterConditionTable::add($sourceFilterCondition);

        if ($result->isSuccess()) {
            $filterConditionId = $result->getId();
        } else {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        foreach ($seoTemplateFields as $seoField) {
            unset($seoField['ID']);

            $seoField['ENTITY_ID'] = $filterConditionId;
            $result = Smartseo\Models\SmartseoSeoTemplateTable::add($seoField);

            if (!$result->isSuccess()) {
                $this->setErrors($result->getErrorMessages());

                return false;
            }
        }

        return $filterConditionId;
    }

    public function copySeotext($sourceId)
    {
        $sourceSeotext = Smartseo\Models\SmartseoSeoTextTable::getRow([
              'select' => [
                  '*',
              ],
              'filter' => [
                  'ID' => $sourceId,
              ]
        ]);

        if (!$sourceSeotext) {
            $this->addError('Source element not found');

            return false;
        }

        $sourceIblockSections = Smartseo\Models\SmartseoSeoTextIblockSectionsTable::getList([
            'select' => [
                '*'
            ],
            'filter' => [
                'SEO_TEXT_ID' => $sourceSeotext['ID'],
            ]
        ])->fetchAll();

        $sourceProperties = Smartseo\Models\SmartseoSeoTextPropertyTable::getList([
            'select' => [
                '*'
            ],
            'filter' => [
                'SEO_TEXT_ID' => $sourceSeotext['ID'],
            ]
        ])->fetchAll();

        $sourceSeotext['NAME'] = '[copy id: ' . $sourceSeotext['ID'] . '] ' . $sourceSeotext['NAME'];
        unset($sourceSeotext['ID'], $sourceSeotext['DATE_LAST_RUN']);

        $result = Smartseo\Models\SmartseoSeoTextTable::add($sourceSeotext);

        $newSeotextId = null;
        if ($result->isSuccess()) {
            $newSeotextId = $result->getId();
        } else {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        foreach ($sourceIblockSections as $section) {
            unset($section['ID']);

            $section['SEO_TEXT_ID'] = $newSeotextId;
            $result = Smartseo\Models\SmartseoSeoTextIblockSectionsTable::add($section);

            if (!$result->isSuccess()) {
                $this->setErrors($result->getErrorMessages());

                return false;
            }
        }

        foreach ($sourceProperties as $property) {
            unset($property['ID']);

            $property['SEO_TEXT_ID'] = $newSeotextId;
            $result = Smartseo\Models\SmartseoSeoTextPropertyTable::add($property);

            if (!$result->isSuccess()) {
                $this->setErrors($result->getErrorMessages());

                return false;
            }
        }

        return $newSeotextId;
    }

    public function copyNoindexRule($sourceId)
    {
        $sourceNoindexRule = Smartseo\Models\SmartseoNoindexRuleTable::getRow([
              'select' => [
                  '*',
              ],
              'filter' => [
                  'ID' => $sourceId,
              ]
        ]);

        if (!$sourceNoindexRule) {
            $this->addError('Source element not found');

            return false;
        }

        $sourceIblockSections = Smartseo\Models\SmartseoNoindexIblockSectionsTable::getList([
            'select' => [
                '*'
            ],
            'filter' => [
                'NOINDEX_RULE_ID' => $sourceNoindexRule['ID'],
            ]
        ])->fetchAll();

        $sourceConditions = Smartseo\Models\SmartseoNoindexConditionTable::getList([
            'select' => [
                '*'
            ],
            'filter' => [
                'NOINDEX_RULE_ID' => $sourceNoindexRule['ID'],
            ]
        ])->fetchAll();

        $sourceNoindexRule['NAME'] = '[copy id: ' . $sourceNoindexRule['ID'] . '] ' . $sourceNoindexRule['NAME'];
        unset($sourceNoindexRule['ID'], $sourceNoindexRule['DATE_LAST_RUN']);

        $result = Smartseo\Models\SmartseoNoindexRuleTable::add($sourceNoindexRule);

        $newNoindexRuleId = null;
        if ($result->isSuccess()) {
            $newNoindexRuleId = $result->getId();
        } else {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        foreach ($sourceIblockSections as $section) {
            unset($section['ID']);

            $section['NOINDEX_RULE_ID'] = $newNoindexRuleId;
            $result = Smartseo\Models\SmartseoNoindexIblockSectionsTable::add($section);

            if (!$result->isSuccess()) {
                $this->setErrors($result->getErrorMessages());

                return false;
            }
        }

        foreach ($sourceConditions as $condition) {
            unset($condition['ID']);

            $condition['NOINDEX_RULE_ID'] = $newNoindexRuleId;
            $result = Smartseo\Models\SmartseoNoindexConditionTable::add($condition);

            if (!$result->isSuccess()) {
                $this->setErrors($result->getErrorMessages());

                return false;
            }
        }

        return $newNoindexRuleId;
    }


    function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors ? true : false;
    }

    public function setErrors($errors)
    {
        if (is_array($errors)) {
            $this->errors = array_map(function($item) {
                return $item;
            }, $errors);
        } else {
            $this->errors[] = $errors;
        }
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

}
