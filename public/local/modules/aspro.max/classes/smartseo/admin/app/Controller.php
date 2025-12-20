<?php

namespace Aspro\Max\Smartseo\Admin\App;

use Bitrix\Main\Request;

class Controller
{

    protected $isIblockModule = false;
    protected $isCatalogModule = false;
    protected $isSearchModule = false;
    protected $isSeoModule = false;

    /**
     *
     * @var Aspro\Max\Smartseo\Admin\App\View
     */
    private $view;
    private $rootViewsPath;
    private $errors = [];

    /**
     * @var Bitrix\Main\Request
     */
    protected $request;

    function __construct(Request $request = null, $config = [])
    {
        $this->rootViewsPath = $config['ROOT_VIEWS_PATH'];
        $this->isIblockModule = $config['IS_IBLOCK_MODULE'];
        $this->isCatalogModule = $config['IS_CATALOG_MODULE'];
        $this->isSeoModule = $config['IS_SEO_MODULE'];
        $this->isSearchModule = $config['IS_SEARCH_MODULE'];

        $this->request = $request;
        $this->view = new View();
        $this->view->setViewPath($this->getViewPath());
        $this->view->setFolderName($this->getViewFolderName());
        $this->view->setUnique(uniqid());
    }

    public function render($view, array $params = [])
    {
        return $this->getView()->render($view, $params);
    }

    public function getRootPathViews()
    {
        return $this->rootViewsPath;
    }

    public function getViewFolderName()
    {
        $replace = str_replace(['Controller', 'Controllers'], '', static::class);
        $explode = explode('\\', $replace);

        return mb_strtolower(array_pop($explode));
    }

    public function getViewPath()
    {
        return $this->getRootPathViews() . DIRECTORY_SEPARATOR . $this->getViewFolderName() . DIRECTORY_SEPARATOR;
    }

    public function getView()
    {
        return $this->view;
    }

    public function getHasModules()
    {
        return [
            'isIblockModule' => $this->isIblockModule,
            'isCatalogModule' => $this->isCatalogModule,
            'isSeoModule' => $this->isSeoModule,
            'isSearchModule' => $this->isSearchModule,
        ];
    }

    public function isCatalogModule()
    {
        return $this->isCatalogModule;
    }

    public function isIblockModule()
    {
        return $this->isIblockModule;
    }

    public function isSearchModule()
    {
        return $this->isSearchModule;
    }

    public function isSeoModule()
    {
        return $this->isSeoModule;
    }

    protected function getErrors()
    {
        return $this->errors;
    }

    protected function hasErrors()
    {
        return $this->errors
          ? true
          : false;
    }

    protected function setErrors($errors)
    {
        if (is_array($errors)) {
            $this->errors = array_map(function($item) {
                return $item;
            }, $errors);
        } else {
            $this->errors[] = $errors;
        }
    }

    protected function addError($error)
    {
        $this->errors[] = $error;
    }

}
