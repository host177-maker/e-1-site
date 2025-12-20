<?php

namespace Aspro\Max\Smartseo\Admin\Grids;

class InnerGrid
{

    /**
     * @var Aspro\Max\Smartseo\Admin\UI\AbstractAdminUI
     */
    protected $ui;
    protected $filter = [];
    protected $sort = [];
    protected $filterFields = [];
    protected $currentPage = 1;

    function __construct($parentId = null, $ui = null)
    {
        $this->ui = $ui;
    }

    public static function getInstance($parentId = null)
    {
        return new static($parentId);
    }

    public function getGridId()
    {
        return $this->ui->getGridId();
    }

    public function getFilterFields()
    {
        return $this->filterFields;
    }

    public function addSort(array $sort = [])
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setCurrentPage($page)
    {
        if($page === false) {
            return;
        }

        $this->currentPage = $page;
        $_SESSION[$this->getCurrentPageSessionName()] = $this->currentPage;
    }

    public function getCurrentPage()
    {
        if($_SESSION[$this->getCurrentPageSessionName()]) {
            $this->currentPage = $_SESSION[$this->getCurrentPageSessionName()];
        }

        return $this->currentPage ?: 1;
    }

    public function getFilterFromOptions()
    {
        $filterOption = new \Bitrix\Main\UI\Filter\Options($this->ui->getFilterId());

        $filterData = $filterOption->getFilter();

        $fields = array_column($this->filterFields , 'type', 'id');

        $quickSearchFields = array_filter($this->filterFields, function($item) {
            return isset($item['quickSearch']);
        });

        if($filterData['FIND']) {
            foreach ($quickSearchFields as $quickField) {
                if($filterData[$quickField['id']]) {
                    continue;
                }
                $filterData[$quickField['id']] = $filterData['FIND'];
            }
        }

        $result = [];

        foreach ($filterData as $code => $value) {
            if(!array_key_exists($code, $fields)) {
                continue;
            }

            if($fields[$code] == 'text') {
                $value = '%' . $value . '%';
            }

            $result[$code] = $value;
        }

        return $result;
    }

    public function addFilter(array $filter = [])
    {
        $this->filter = array_merge($this->getFilterFromOptions(), $filter);
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getComponentParams($needRows = false)
    {
        $result = [];

        if($needRows) {
           $result = $this->getResult();
        }

        return [
            'GRID_ID' => $this->ui->getGridId(),
            'FILTER_ID' => $this->ui->getFilterId(),
            'COLUMNS' => $this->ui->getGridColumns(),
            'COLUMN_PREFIX' => $this->ui->getColumnGridPrefix(),
            'FILTER_FIELDS' => $this->ui->getFilterFields(),
            'ROWS' => $result['ROWS'] ?: [],
            'TOTAL_ROWS_COUNT' => $result['TOTAL_ROWS_COUNT'] ?: null,
            'NAV_OBJECT' => $result['NAVIGATION'] ?: null,
        ];
    }

    public function getNavigation()
    {
        $gridOptions = new \Bitrix\Main\Grid\Options($this->ui->getGridId());

        $navigationParams = $gridOptions->GetNavParams();

        $navigation = new \Bitrix\Main\UI\PageNavigation($this->ui->getGridId());

        $navigation->allowAllRecords(true)
          ->setPageSize($navigationParams['nPageSize'])
          ->setCurrentPage($this->getCurrentPage())
          ->initFromUri();

        return $navigation;
    }

    protected function getResult()
    {
        $navigation = $this->getNavigation();

        $rsList = $this->getRsList($navigation);

        if(!$rsList) {
            return [];
        }

        $totalRowsCount = $rsList->getCount();
        $navigation->setRecordCount($totalRowsCount);

        if($this->getCurrentPage() > $navigation->getPageCount()) {
            $this->setCurrentPage($navigation->getPageCount());
            $navigation->setCurrentPage($this->getCurrentPage());

            $rsList = $this->getRsList($navigation);
            $totalRowsCount = $rsList->getCount();
            $navigation->setRecordCount($totalRowsCount);
        }

        $rows = $rsList->fetchAll();

        $result = $this->modifiedResultRows($rows);

        return [
            'ROWS' => $result,
            'NAVIGATION' => $navigation,
            'TOTAL_ROWS_COUNT' => $totalRowsCount,
        ];
    }

    protected function modifiedResultRows($rows)
    {
        return $rows;
    }

    protected function getRsList($navigation)
    {
        if (!$this->getFilter()) {
            return [];
        }

        $rsResultList = [];

        return $rsResultList;
    }

    protected function getCurrentPageSessionName()
    {
       return $this->getGridId() . '_current_page';
    }


}
