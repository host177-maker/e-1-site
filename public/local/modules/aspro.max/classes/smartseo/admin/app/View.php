<?php

namespace Aspro\Max\Smartseo\Admin\App;

use Aspro\Max\Smartseo,
    \Bitrix\Main\Localization\Loc;

class View
{

    private $viewPath;
    private $folderName = null;
    private $unique = null;

    public function render($view, array $params = [])
    {
        $file = $this->getViewPath() . $view . '.php';

        $this->includeFile($file, $params);
    }

    public function setViewPath($path)
    {
        $this->viewPath = $path;
    }

    public function getViewPath()
    {
        return $this->viewPath;
    }

    public function setFolderName($value)
    {
        $this->folderName = $value;
    }

    public function getFolderName()
    {
        return $this->folderName;
    }

    public function setUnique($value)
    {
        $this->unique = $value;
    }

    public function getUnique()
    {
        return $this->unique;
    }

    public function getPathSelfScripts()
    {
        return '/bitrix/js/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/' . $this->getFolderName();
    }

    public function getPathModuleScripts()
    {
        return '/bitrix/js/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/';
    }

    protected function includeFile($file, array $params)
    {
        if (file_exists($file)) {

            extract($params);
            include $file;
        }
    }

}
