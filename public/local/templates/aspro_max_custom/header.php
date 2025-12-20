<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($_GET["debug"] === "y") {
    error_reporting(E_ERROR | E_PARSE);
}

IncludeTemplateLangFile(__FILE__);

global $APPLICATION, $arRegion, $arSite, $arTheme, $bIndexBot, $bIframeMode;
$arSite = CSite::GetByID(SITE_ID)->Fetch();
$htmlClass = ($_REQUEST && isset($_REQUEST['print']) ? 'print' : false);
$bIncludedModule = (Loader::includeModule("aspro.max"));
?>

    <!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= LANGUAGE_ID ?>"
      lang="<?= LANGUAGE_ID ?>" <?= ($htmlClass ? 'class="' . $htmlClass . '"' : '') ?> <?= ($bIncludedModule ? CMax::getCurrentHtmlClass() : '') ?>>
    <head>
        <?php CJSCore::Init(["jquery"]) ?>
        <title><?php $APPLICATION->ShowTitle() ?></title>
        <?php
        $APPLICATION->ShowMeta("viewport");
        $APPLICATION->ShowMeta("HandheldFriendly");
        $APPLICATION->ShowMeta("apple-mobile-web-app-capable", "yes");
        $APPLICATION->ShowMeta("apple-mobile-web-app-status-bar-style");
        $APPLICATION->ShowMeta("SKYPE_TOOLBAR");
        ?>
        <meta name="yandex-verification" content="1c0c0b56108a2deb"/>

        <?php if (isPaginationDetected()) { ?>
            <meta name="robots" content="noindex, follow"/>
        <?php } ?>

        <?php if (!isPageSpeedDetected()) { ?>
<!--            <link rel="preconnect" href="https://api.searchbooster.net" crossorigin />-->
<!--            <link rel="preconnect" href="https://cdn2.searchbooster.net" crossorigin />-->
<!--            <link rel="preconnect" href="https://api4.searchbooster.io" crossorigin />-->
<!--            <link rel="preconnect" href="https://cdn.searchbooster.io" crossorigin />-->
        <?php } ?>

        <?php
        $APPLICATION->ShowHead();
        $APPLICATION->AddHeadString('<script>BX.message(' . CUtil::PhpToJSObject($MESS, false) . ')</script>', true);

        if ($bIncludedModule) {
            CMax::Start(SITE_ID);
        }

        // counters
        if (!isPageSpeedDetected()) {
            $APPLICATION->IncludeFile('/include/header_include/counters/gtag.php');
            $APPLICATION->IncludeFile('/include/header_include/counters/mail.php');
            $APPLICATION->IncludeFile('/include/header_include/inc_scripts.php');

            $APPLICATION->IncludeFile('/include/header_include/scripts/searchbooster.php');
            $APPLICATION->IncludeFile('/include/header_include/scripts/yourgood.php');
        }
        ?>
    </head>

<?php if (!CMax::checkAjaxRequest()) { ?>
    <div id="panel"><?php $APPLICATION->ShowPanel() ?></div>
<?php } ?>
<?php
$useWarningMessage = Option::get('e1.site.settings', 'E1_SS_USE_WARNING_MESSAGE', 'N');
if ($useWarningMessage === 'Y') {
    $APPLICATION->IncludeFile('/include/header_include/warningModal.php');
}
?>
<?php $bIndexBot = CMax::checkIndexBot() ?>
<body class="<?= ($bIndexBot ? "wbot" : ""); ?> site_<?= SITE_ID ?> <?= ($bIncludedModule ? CMax::getCurrentBodyClass() : '') ?>"
      id="main" data-site="<?= SITE_DIR ?>">
<?php
$APPLICATION->IncludeFile('/include/header_include/orderModal.php');
if (!isPageSpeedDetected()) {
    $APPLICATION->IncludeFile('/include/header_include/inc_roistat.php');
    $APPLICATION->IncludeFile('/include/header_include/counters/mail_body_top.php');
    $APPLICATION->IncludeFile('/include/header_include/counters/gtag_body_top.php');
}
?>

<?php if (!$bIncludedModule) { ?>
    <?php $APPLICATION->SetTitle(GetMessage("ERROR_INCLUDE_MODULE_ASPRO_MAX_TITLE")) ?>
    <center><?php $APPLICATION->IncludeFile("/include/error_include_module.php"); ?></center>
    </body>
    </html>
    <?php die() ?>
<?php } ?>

<?php
if (!isPageSpeedDetected()) {
    $APPLICATION->IncludeFile('/include/header_include/body_top.php');
}
?>

<?php
$arTheme = $APPLICATION->IncludeComponent(
    "aspro:theme.max",
    ".default",
    ["COMPONENT_TEMPLATE" => ".default"],
    false,
    ["HIDE_ICONS" => "Y"]
);
include_once('defines.php');
CMax::SetJSOptions();
?>

<?php $APPLICATION->IncludeFile('/include/header_include/under_wrapper1.php'); ?>
<div class="wrapper1 <?= ($isIndex && $isShowIndexLeftBlock ? "with_left_block" : "") ?> <?= CMax::getCurrentPageClass(); ?> <?php $APPLICATION->AddBufferContent(array('CMax', 'getCurrentThemeClasses')) ?>  ">
<?php $APPLICATION->IncludeFile('/include/header_include/top_wrapper1.php') ?>

<div class="wraps hover_<?= $arTheme["HOVER_TYPE_IMG"]["VALUE"] ?>" id="content">
<?php $APPLICATION->IncludeFile('/include/header_include/top_wraps.php') ?>

<?php if ($isIndex) { ?>
    <?php $APPLICATION->ShowViewContent('front_top_big_banner') ?>
    <div class="wrapper_inner front <?= ($isShowIndexLeftBlock ? "" : "wide_page") ?> <?= $APPLICATION->ShowViewContent('wrapper_inner_class') ?>">
    <?php } elseif (!$isWidePage) { ?>
    <div class="wrapper_inner <?= ($isHideLeftBlock ? "wide_page" : "") ?> <?= $APPLICATION->ShowViewContent('wrapper_inner_class') ?>">
<?php } ?>

<div class="container_inner clearfix <?= $APPLICATION->ShowViewContent('container_inner_class') ?>">
<?php if (($isIndex && ($isShowIndexLeftBlock || $bActiveTheme)) || (!$isIndex && !$isHideLeftBlock)) { ?>
    <div class="right_block <?= (defined("ERROR_404") ? "error_page" : "") ?> wide_<?= CMax::ShowPageProps("HIDE_LEFT_BLOCK") ?> <?= $APPLICATION->ShowViewContent('right_block_class') ?>">
<?php } ?>
<div class="middle <?= ($is404 ? 'error-page' : '') ?> <?= $APPLICATION->ShowViewContent('middle_class') ?>">
<?php CMax::get_banners_position('CONTENT_TOP') ?>
<?php if (!$isIndex) { ?>
    <div class="container">
    <?php if ($isHideLeftBlock && !$isWidePage) { ?>
    <div class="maxwidth-theme">
<?php } ?>
<?php } ?>
<?php CMax::checkRestartBuffer() ?>