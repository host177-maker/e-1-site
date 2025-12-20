<?php

namespace Aspro\Max\Smartseo\Admin;

use Aspro\Max\Smartseo,
    Bitrix\Main\Request,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FrontController
{

    /**
     * @var Bitrix\Main\Request
     */
    private $request;
    private $rootViewsPath;
    private $isIblockModule = false;
    private $isCatalogModule = false;
    private $isSearchModule = false;
    private $isSeoModule = false;
    private $isParentModule = false;

    function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run()
    {
        $config = [
            'ROOT_VIEWS_PATH' => $this->rootViewsPath,
            'IS_IBLOCK_MODULE' => $this->isIblockModule,
            'IS_CATALOG_MODULE' => $this->isCatalogModule,
            'IS_SEO_MODULE' => $this->isSeoModule,
            'IS_SEARCH_MODULE' => $this->isSearchModule,
        ];

        $routeParam = $this->request->getQuery('route');

        list($controllerName, $methodName) = explode('/', $routeParam);
        $controllerClass = __NAMESPACE__ . '\\Controllers\\' . $this->getModifierRouteStr($controllerName) . 'Controller';
        $methodClass = 'action' . $this->getModifierRouteStr($methodName);

        if (!$routeParam) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_PAGE_FOUND'));
        }

        if (!class_exists($controllerClass) || !method_exists($controllerClass, $methodClass)) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_PAGE_FOUND'));
        }

        $controller = new $controllerClass($this->request, $config);

        call_user_func_array([$controller, $methodClass], $this->getMethodArgs($controllerClass, $methodClass));
    }

    public function setRootViewsPath($path)
    {
        $this->rootViewsPath = $path;
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

    public function registerExtensions()
    {
        $moduleId = Smartseo\General\Smartseo::MODULE_ID;
        $relativePath = str_replace('/', DIRECTORY_SEPARATOR, Smartseo\General\Smartseo::getModulePath(false));

        if(!$this->isCatalogModule()) {
            require_once(Smartseo\General\Smartseo::getModulePath() . 'lib/smartseo/condition/bxcond/catalog_cond.php');

            $bxCondition = [
                'js' => '/bitrix/js/' . $moduleId . '/smartseo/bxcond/core_tree.min.js',
                'css' => '/bitrix/css/' . $moduleId . '/smartseo/bxcond/catalog_cond.css',
                'lang' => $relativePath . 'lang/' . LANGUAGE_ID . '/bxcond/core_tree.php',
                'rel' => ['core', 'date', 'window']
            ];

            \CJSCore::RegisterExt('core_condtree', $bxCondition);
        }
    }

    public function validateModules()
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            throw new \Exception('Bitrix Iblock [iblock] module not installed');
        }

        if (!\Bitrix\Main\Loader::includeModule('seo')) {
            throw new \Exception('Bitrix Seo [seo] module not installed');
        } else {
            $this->isSeoModule = true;
        }

        if (!\Bitrix\Main\Loader::includeModule(Smartseo\General\Smartseo::MODULE_ID)) {
            throw new \Exception('Module aspro.max not installed');
        } else {
            $this->isParentModule = true;
        }

        if (!\Bitrix\Main\Loader::includeModule('search')) {
            throw new \Exception('Bitrix Search [search] module not installed');
        } else {
            $this->isSearchModule = true;
        }

        $this->isIblockModule = true;

        if (\Bitrix\Main\Loader::includeModule('catalog')) {
            $this->isCatalogModule = true;
        }

        return true;
    }

    private function getModifierRouteStr($str)
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '__', 'action'], ' ', $str)));
    }

    private function getMethodArgs($class, $method)
    {
        $result = [];

        $reflection = new \ReflectionClass($class);
        $methodReflection = $reflection->getMethod($method);
        foreach ($methodReflection->getParameters() as $i => $param) {
            if ($_value = $this->request->getQuery($param->getName())) {
                $result[mb_strtolower($param->getName())] = $_value;
            } else {
                $result[mb_strtolower($param->getName())] = $param->getDefaultValue();
            }
        }

        return $result;
    }
}
