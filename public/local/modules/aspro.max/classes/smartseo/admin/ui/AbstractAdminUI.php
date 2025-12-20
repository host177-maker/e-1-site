<?php

namespace Aspro\Max\Smartseo\Admin\UI;

abstract class AbstractAdminUI
{

    abstract public function getGridId();

    abstract public function getFilterFields();

    abstract public function getContextMenu();

    abstract public function getGridColumns();

    public function getColumnGridPrefix()
    {
        return '';
    }
}
