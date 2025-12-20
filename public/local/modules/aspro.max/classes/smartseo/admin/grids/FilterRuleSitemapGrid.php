<?php

namespace Aspro\Max\Smartseo\Admin\Grids;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\UI;

class FilterRuleSitemapGrid extends InnerGrid
{

    private $filterRuleId = null;

    function __construct($filterRuleId)
    {
        $this->filterRuleId = $filterRuleId;
        $this->ui = new UI\FilterRuleSitemapAdminUI($filterRuleId);
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

        $rsResultList = Smartseo\Models\SmartseoFilterSitemapTable::getList([
              'select' => [
                  '*',
                  'REF_CONDITION_NAME' => 'FILTER_CONDITION.NAME',
                  'REF_CONDITION_ACTIVE' => 'FILTER_CONDITION.ACTIVE',
                  'REF_SITEMAP_NAME' => 'SITEMAP.NAME',
                  'REF_SITEMAP_SITE_ID' => 'SITEMAP.SITE_ID',
                  'REF_SITEMAP_ACTIVE' => 'SITEMAP.ACTIVE',
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
        $changefreqParams = Smartseo\Models\SmartseoFilterSitemapTable::getChangefreqParams();

        $result = [];
        foreach ($rows as $row) {
            $row['CHANGEFREQ'] = '[' . $row['CHANGEFREQ'] . '] ' . $changefreqParams[$row['CHANGEFREQ']];

            $result[] = $row;
        }

        return $result;
    }

    protected function getCurrentPageSessionName()
    {
        return $this->getGridId() . '_current_page_' . $this->filterRuleId;
    }

}
