<?php

namespace Aspro\Max\Smartseo\Admin\Grids;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\UI;

class FilterRuleSearchGrid extends InnerGrid
{
    private $filterRuleId = null;

    function __construct($filterRuleId = null)
    {
        $this->filterRuleId = $filterRuleId;
        $this->ui = new UI\FilterRuleSearchAdminUI($filterRuleId);
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

        $rsResultList = Smartseo\Models\SmartseoFilterSearchTable::getList([
              'select' => [
                  '*',
                  'FILTER_CONDITION_NAME' => 'FILTER_CONDITION.NAME',
                  'FILTER_CONDITION_ACTIVE' => 'FILTER_CONDITION.ACTIVE',
              ],
              'filter' => $this->getFilter(),
              'count_total' => true,
              'offset' => $navigation->getOffset(),
              'limit' => $navigation->getLimit(),
              'order' => $this->getSort() ?: [
                'ID' => 'ASC',
              ],
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
