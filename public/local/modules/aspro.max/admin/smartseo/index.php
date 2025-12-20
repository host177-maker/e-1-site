<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

if(isset($_SESSION['SMARTSEO_NEED_RELOAD'])) {
    unset($_SESSION['SMARTSEO_NEED_RELOAD']);
    echo '<script>window.location.reload()</script>';
    die();
}

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\FrontController,
    Bitrix\Main\Application,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

$moduleID = 'aspro.max';

global $APPLICATION, $USER;

\Bitrix\Main\UI\Extension::load('ui.buttons');
\Bitrix\Main\UI\Extension::load('ui.alerts');

$APPLICATION->SetAdditionalCss('/bitrix/css/main/grid/webform-button.css');
$APPLICATION->SetAdditionalCss('/bitrix/css/' . $moduleID . '/smartseo/style.css');
$APPLICATION->SetAdditionalCss('/bitrix/css/' . $moduleID . '/smartseo/src/ui.css');
$APPLICATION->SetAdditionalCss('/bitrix/css/main/grid/pagenavigation.css');

$APPLICATION->AddHeadScript('/bitrix/js/' . $moduleID . '/smartseo/src/polyphils/ie11.js');
$APPLICATION->AddHeadScript('/bitrix/js/' . $moduleID . '/smartseo/src/polyphils/form_data_ie11.js');
$APPLICATION->AddHeadScript('/bitrix/js/' . $moduleID . '/smartseo/src/form.js');
$APPLICATION->AddHeadScript('/bitrix/js/' . $moduleID . '/smartseo/src/animate.js');
$APPLICATION->AddHeadScript('/bitrix/js/' . $moduleID . '/smartseo/src/tabs.js');
$APPLICATION->AddHeadScript('/bitrix/js/' . $moduleID . '/smartseo/src/popup.js');
$APPLICATION->AddHeadScript('/bitrix/js/' . $moduleID . '/smartseo/src/menu_inner_grid.js');
$APPLICATION->AddHeadScript('/bitrix/js/' . $moduleID . '/smartseo/src/filter_custom_entity.js');

$APPLICATION->setTitle(Loc::getMessage('SMARTSEO_INDEX__TITLE__MAIN'));

if ($APPLICATION->GetGroupRight($moduleID) < 'R') {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$request = Application::getInstance()->getContext()->getRequest();

if (mb_strtolower(LANG_CHARSET) === 'windows-1251' || mb_strtolower(LANG_CHARSET) === 'cp1251') {
    $request->setValues(\Bitrix\Main\Text\Encoding::convertEncoding($request->getValues(), 'UTF-8', LANG_CHARSET));
}

if (!$request->isAjaxRequest()) {
     require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
}

$resultLoaderModule = \Bitrix\Main\Loader::includeSharewareModule($moduleID);

if ($resultLoaderModule == \Bitrix\Main\Loader::MODULE_DEMO || $resultLoaderModule == \Bitrix\Main\Loader::MODULE_INSTALLED) {
    if(!$request->isAjaxRequest() && $resultLoaderModule == \Bitrix\Main\Loader::MODULE_DEMO) {
        \CAdminMessage::ShowMessage(Loc::getMessage('SMARTSEO_INDEX__ERROR__MODULE_DEMO'));
    }

    $frontController = new FrontController($request);
    $frontController->setRootViewsPath(Smartseo\General\Smartseo::getModulePath() . join('/', ['admin', 'smartseo', 'views']));
   
    try {
        if (!$USER->IsAuthorized()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__MODULE_NOT_AUTHORIZED'));
        }

        if($frontController->validateModules()) {
            $frontController->registerExtensions();
            $frontController->run();
        }

    } catch (Exception $e) {
        if ($request->isAjaxRequest()) {
            echo Json::encode([
                'result' => false,
                'message' => $e->getMessage(),
            ]);
        } else {
            \CAdminMessage::ShowMessage($e->getMessage());
        }
    }
} elseif($resultLoaderModule == \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
    \CAdminMessage::ShowMessage(Loc::getMessage('SMARTSEO_INDEX__ERROR__DEMO_EXPIRED'));
} else {
    \CAdminMessage::ShowMessage(Loc::getMessage('SMARTSEO_INDEX__ERROR__MODULE_NOT_INSTALLED'));
}

if (!$request->isAjaxRequest()) {
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
}
