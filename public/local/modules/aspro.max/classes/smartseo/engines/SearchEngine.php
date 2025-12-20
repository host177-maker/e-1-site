<?php

namespace Aspro\Max\Smartseo\Engines;

use Aspro\Max\Smartseo;

class SearchEngine extends \Aspro\Max\Smartseo\Engines\Engine
{

    const PERMISSION_ALL_USERS = 2;
    const PREFIX_ITEM = 'smartseo_';
    const MODULE_TO = 'iblock';

    private $siteId;

    function __construct($siteId = null)
    {
        if(!\Bitrix\Main\Loader::includeModule('search')) {
           $this->addError('Module Search not installed');
        }

        $this->siteId = $siteId;
    }

    public function getAllPages(array $filter = [])
    {
        $rows = Smartseo\Models\SmartseoFilterSearchTable::getList([
              'select' => [
                  'URL_ID' => 'FILTER_CONDITION.FILTER_CONDITION_URL.ID',
                  'URL_NEW' => 'FILTER_CONDITION.FILTER_CONDITION_URL.NEW_URL',
                  'URL_SECTION_ID' => 'FILTER_CONDITION.FILTER_CONDITION_URL.SECTION_ID',
                  'URL_PROPERTIES' => 'FILTER_CONDITION.FILTER_CONDITION_URL.PROPERTIES',
                  'CONDITION_DATE_CHANGE' => 'FILTER_CONDITION.DATE_CHANGE',
                  'SITE_ID' => 'FILTER_CONDITION.FILTER_RULE.SITE_ID',
                  'IBLOCK_ID' => 'FILTER_CONDITION.FILTER_RULE.IBLOCK_ID',
                  'IBLOCK_TYPE' => 'FILTER_CONDITION.FILTER_RULE.IBLOCK_TYPE_ID',
                  'FILTER_CONDITION_ID',
                  'TITLE_TEMPLATE',
                  'BODY_TEMPLATE',
                  'DATE_CHANGE',
                  'STATUS'
              ],
              'filter' => array_filter(
                    array_merge([
                      '!==FILTER_CONDITION.FILTER_CONDITION_URL.NEW_URL' => null,
                      'FILTER_CONDITION.FILTER_CONDITION_URL.STATE_DELETED' => 'N',
                      'FILTER_CONDITION.ACTIVE' => 'Y',
                      'FILTER_CONDITION.FILTER_RULE.ACTIVE' => 'Y',
                      '!==TITLE_TEMPLATE' => null,
                      'SITE_ID' => $this->siteId,
                      'ACTIVE' => 'Y',
                    ], $filter)
                ),
              'order' => [
                  'URL_ID' => 'ASC',
              ]
          ])->fetchAll();
        
        $element = new \Aspro\Max\Smartseo\Template\Entity\FilterRuleUrl(0);

        $result = [];
        foreach ($rows as $row) {
            $element->setFields([
                'ID' => $row['URL_ID'],
                'IBLOCK_ID' => $row['IBLOCK_ID'],
                'SECTION_ID' => $row['URL_SECTION_ID'],
                'PROPERTIES' => unserialize($row['URL_PROPERTIES'], ['allowed_classes' => false]),
            ]);

            $_dateChange = '';
            if (($row['DATE_CHANGE'] && $row['DATE_CHANGE'] instanceof \Bitrix\Main\Type\DateTime) && ($row['CONDITION_DATE_CHANGE'] && $row['CONDITION_DATE_CHANGE'] instanceof \Bitrix\Main\Type\DateTime)
            ) {
                if ($row['CONDITION_DATE_CHANGE']->getTimestamp() > $row['DATE_CHANGE']->getTimestamp()) {
                    $_dateChange = $row['CONDITION_DATE_CHANGE']->toString();
                } else {
                    $_dateChange = $row['DATE_CHANGE']->toString();
                }
            } else {
                $objDateTime = new \Bitrix\Main\Type\DateTime();
                $_dateChange = $objDateTime->toString();
            }

            $_title = '';
            if ($row['TITLE_TEMPLATE']) {
                $_title = \Bitrix\Main\Text\HtmlFilter::encode(
                    \Bitrix\Iblock\Template\Engine::process($element, $row['TITLE_TEMPLATE'])
                );
            }

            $_body = '';
            if ($row['BODY_TEMPLATE']) {
                $_body = \Bitrix\Main\Text\HtmlFilter::encode(
                    \Bitrix\Iblock\Template\Engine::process($element, $row['BODY_TEMPLATE'])
                );
            }

            $result[] = [
                'ID' => $this->getUniqueItemId($row['URL_ID'], $row['FILTER_CONDITION_ID']),
                'SITE_ID' => [
                    $row['SITE_ID'],
                ],
                'DATE_CHANGE' => $_dateChange,
                'URL' => $row['URL_NEW'],
                'PERMISSIONS' => [
                    self::PERMISSION_ALL_USERS,
                ],
                'TITLE' => $_title,
                'BODY' => $_body,
                'PARAM1' => $row['IBLOCK_TYPE'],
                'PARAM2' => $row['IBLOCK_ID']
            ];
        }

        return $result;
    }

    public function reindexByFilterCondition($filterConditionId)
    {
        if($this->hasErrors()) {
            return null;
        }

        $allPages = $this->getAllPages([
            '=FILTER_CONDITION_ID' => $filterConditionId,
        ]);

        $count = 0;
        foreach ($allPages as $pageFields) {
            $index = $this->addItemIndex($pageFields, true);

            if($index) {
                $count++;
            }
        }

        $this->setResult([
            'COUNT' => $count
        ]);
    }

    public function addItemIndex(array $fields, $needOverWrite = true)
    {
        $itemId = $fields['ID'];

        unset($fields['ID']);
        unset($fields['MODULE_ID']);

        return \CSearch::Index(
          self::MODULE_TO, $itemId, $fields, $needOverWrite
        );
    }

    public function getUrlIdByIndex($indexId)
    {
        $result = $this->parseIndex($indexId);

        return (int)$result['URL_ID'];
    }

    public function getFilterConditionIdByIndex($indexId)
    {
        $result = $this->parseIndex($indexId);

        return (int)$result['FILTER_CONDITION_ID'];
    }

    protected function parseIndex($indexId)
    {
        preg_match('/' . self::PREFIX_ITEM .'(\d+)_(\d+)/', $indexId, $matches);

        return [
            'URL_ID' => (int)$matches[2],
            'FILTER_CONDITION_ID' => (int)$matches[1],
        ];
    }

    protected function getUniqueItemId($urlId, $filterConditionId)
    {
        return self::PREFIX_ITEM . $filterConditionId . '_' . $urlId;
    }
}
