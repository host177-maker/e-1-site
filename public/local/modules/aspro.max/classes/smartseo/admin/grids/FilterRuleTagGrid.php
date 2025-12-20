<?php

namespace Aspro\Max\Smartseo\Admin\Grids;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\UI;

class FilterRuleTagGrid extends InnerGrid
{

    private $filterRuleId = null;

    function __construct($filterRuleId)
    {
        $this->filterRuleId = $filterRuleId;
        $this->ui = new UI\FilterRuleTagAdminUI($filterRuleId);
        $this->filterFields = $this->ui->getFilterFields();
    }

    public function getFilter()
    {
        return array_filter(
          array_merge($this->filter, [
            'FILTER_CONDITION.FILTER_RULE.ID' => $this->filterRuleId,
          ])
        );
    }

    protected function getRsList($navigation)
    {
        if (!$this->getFilter()) {
            return [];
        }

        $rsResultList = Smartseo\Models\SmartseoFilterTagTable::getList([
              'select' => [
                  '*',
                  'FILTER_CONDITION_NAME' => 'FILTER_CONDITION.NAME',
                  'FILTER_CONDITION_ACTIVE' => 'FILTER_CONDITION.ACTIVE',
                  'PARENT_FILTER_CONDITION_NAME' => 'PARENT_FILTER_CONDITION.NAME',
                  'SECTION_NAME' => 'SECTION.NAME',
              ],
              'filter' => $this->getFilter(),
              'count_total' => true,
              'offset' => $navigation->getOffset(),
              'limit' => $navigation->getLimit(),
              'order' => $this->getSort() ?: [
                'ID' => 'ASC',
              ]
        ]);

        return $rsResultList;
    }

    protected function modifiedResultRows($rows)
    {
        return $rows;
    }

    protected function getCurrentPageSessionName()
    {
        return $this->getGridId() . '_current_page_' . $this->filterRuleId;
    }

}
