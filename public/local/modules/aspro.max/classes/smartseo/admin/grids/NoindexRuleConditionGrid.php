<?php

namespace Aspro\Max\Smartseo\Admin\Grids;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\UI;

class NoindexRuleConditionGrid extends InnerGrid
{
    private $noindexRuleId = null;

    function __construct($noindexRuleId)
    {
        $this->ui = new UI\NoindexRuleConditionAdminUI($noindexRuleId);
        $this->noindexRuleId = $noindexRuleId;
    }

    public function getFilter()
    {
        return array_filter(
          array_merge($this->filter, [
            'NOINDEX_RULE_ID' => $this->noindexRuleId,
          ])
        );
    }

    protected function getRsList($navigation)
    {
        if (!$this->getFilter()) {
            return [];
        }

        $rsResultList = Smartseo\Models\SmartseoNoindexConditionTable::getList([
              'select' => [
                  '*',
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
                  'ttl' => Smartseo\Models\SmartseoNoindexConditionTable::getCacheTtl(),
              ]
        ]);

        return $rsResultList;
    }

    protected function modifiedResultRows($rows)
    {
        foreach ($rows as &$row) {
            if ($row['PROPERTIES']) {
               $row['PROPERTIES'] = unserialize($row['PROPERTIES'], ['allowed_classes' => false]);
            }
        }

        return $rows;
    }

    protected function getCurrentPageSessionName()
    {
        return $this->getGridId() . '_current_page_' . $this->noindexRuleId;
    }

}
