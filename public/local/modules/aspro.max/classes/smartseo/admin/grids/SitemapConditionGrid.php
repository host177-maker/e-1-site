<?php

namespace Aspro\Max\Smartseo\Admin\Grids;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\UI;

class SitemapConditionGrid extends InnerGrid
{

    private $sitemapId = null;

    function __construct($sitemapId)
    {
        $this->sitemapId = $sitemapId;
        $this->ui = new UI\SitemapConditionAdminUI($sitemapId);
        $this->filterFields = $this->ui->getFilterFields();
    }

    public function getFilter()
    {
        return array_filter(
          array_merge($this->filter, [
            'SITEMAP.ID' => $this->sitemapId
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
                  'FILTER_RULE_ID' => 'FILTER_CONDITION.FILTER_RULE.ID',
                  'REF_RULE_NAME' => 'FILTER_CONDITION.FILTER_RULE.NAME',
                  'REF_RULE_ACTIVE' => 'FILTER_CONDITION.FILTER_RULE.ACTIVE',
                  'REF_CONDITION_NAME' => 'FILTER_CONDITION.NAME',
                  'REF_CONDITION_ACTIVE' => 'FILTER_CONDITION.ACTIVE',
                  new \Bitrix\Main\Entity\ExpressionField('COUNT_URLS', 'COUNT(%s)', ['FILTER_CONDITION.FILTER_CONDITION_URL.FILTER_CONDITION_ID']),
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
        return $this->getGridId() . '_current_page_' . $this->sitemapId;
    }

}
