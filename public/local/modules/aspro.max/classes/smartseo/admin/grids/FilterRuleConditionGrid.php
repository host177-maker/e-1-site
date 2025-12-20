<?php

namespace Aspro\Max\Smartseo\Admin\Grids;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\UI;

class FilterRuleConditionGrid extends InnerGrid
{
    private $filterRuleId = null;
    private $filterIblockId = null;
    private $isCatalogModule = false;

    function __construct($filterRuleId)
    {
        if (\Bitrix\Main\Loader::includeModule('catalog')) {
            $this->isCatalogModule = true;
        }

        $this->ui = new UI\FilterRuleConditionAdminUI($filterRuleId);
        $this->filterRuleId = $filterRuleId;
    }

    public function setFilterIblockId($value)
    {
        $this->filterIblockId = $value;
    }

    public function getFilter()
    {
        return array_filter(
          array_merge($this->filter, [
            'FILTER_RULE_ID' => $this->filterRuleId,
          ])
        );
    }

    protected function getRsList($navigation)
    {
        if (!$this->getFilter()) {
            return [];
        }

        $rsResultList = Smartseo\Models\SmartseoFilterConditionTable::getList([
              'select' => [
                  '*',
                  new \Bitrix\Main\Entity\ExpressionField('COUNT_URLS', 'COUNT(%s)', ['FILTER_CONDITION_URL.FILTER_CONDITION_ID']),
                  'REF_SITEMAP_NAME' => 'FILTER_SITEMAP.SITEMAP.NAME',
                  'REF_SITEMAP_ID' => 'FILTER_SITEMAP.SITEMAP.ID',
                  'REF_SITEMAP_SITE_ID' => 'FILTER_SITEMAP.SITEMAP.SITE_ID',
              ],
              'filter' => $this->getFilter(),
              'count_total' => true,
              'offset' => $navigation->getOffset(),
              'limit' => $navigation->getLimit(),
              'order' => $this->getSort() ?: [
                'SORT' => 'ASC',
                'ID' => 'ASC',
              ],
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoFilterConditionTable::getCacheTtl(),
              ]
        ]);

        return $rsResultList;
    }

    protected function modifiedResultRows($rows)
    {
        $conditionTree = new Smartseo\Condition\ConditionTree();

        $conditionTree
          ->addControlBuild(new Smartseo\Condition\Controls\GroupBuildControls())
          ->addControlBuild(new Smartseo\Condition\Controls\IblockPropertyBuildControls(
            $this->getIblockId()
          ));

        if($this->isCatalogModule) {
            $conditionTree->addControlBuild(new Smartseo\Condition\Controls\CatalogGroupBuildControls());
        }

        $conditionTree->init(BT_COND_MODE_PARSE, Smartseo\Condition\ConditionTree::BT_COND_BUILD_SMARTSEO, []);

        foreach ($rows as &$row) {
            if (!$row['CONDITION_TREE'] && !CheckSerializedData($row['CONDITION_TREE'])) {
                continue;
            }

            $_condition = unserialize($row['CONDITION_TREE'], ['allowed_classes' => false]);

            $_properties = $conditionTree->getConditionValues($_condition);

            $row['CONDITION_GROUP'] = $_condition['DATA'];

            $row['CONDITION_PROPERTY'] = $this->getPrepareConditionProperty($_properties);
        }

        return $rows;
    }

    protected function getCurrentPageSessionName()
    {
        return $this->getGridId() . '_current_page_' . $this->filterRuleId;
    }

    private function getPrepareConditionProperty(array $properties)
    {
        $result = [];
        foreach ($properties as $property) {
            $_property = null;
            if ($property['ENTITY'] == 'ELEMENT_PROPERTY') {
                $_property = [
                    'GROUP_NAME' => $property['ENTITY_NAME'] . ' [' . $property['IBLOCK_ID'] . ']',
                    'GROUP_ID' => $property['IBLOCK_ID'],
                    'NAME' => $property['PROPERTY_NAME'],
                    'ID' => $property['PROPERTY_ID'],
                ];
            }

            if ($property['ENTITY'] == 'CATALOG_GROUP') {
                $_property = [
                    'GROUP_NAME' => $property['ENTITY_NAME'],
                    'GROUP_ID' => 500,
                    'NAME' => $property['CATALOG_GROUP_NAME'],
                    'ID' => $property['CATALOG_GROUP_ID'],
                ];
            }

            $_property['CONDITIONS'] = $property['CONDITIONS'];

            $result[] = $_property;
        }

        usort($result, function($a, $b) {
            return ($a['GROUP_ID'] > $b['GROUP_ID']);
        });

        return $result;
    }

    private function getIblockId()
    {
        if ($this->filterIblockId) {
            return $this->filterIblockId;
        }

        $row = Smartseo\Models\SmartseoFilterRuleTable::getRow([
              'select' => [
                  'IBLOCK_ID' => 'IBLOCK_ID',
              ],
              'filter' => [
                  'ID' => $this->filterRuleId,
              ]
        ]);

        $this->filterIblockId = $row['IBLOCK_ID'];

        return $this->filterIblockId;
    }

}
