<?global $APPLICATION, $arRegion, $arSite, $arTheme, $bIndexBot, $is404, $isForm, $isIndex;?>
<?if($APPLICATION->GetProperty('viewed_show') == 'Y' || $is404):?>
<?$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    'basket',
    [
        'COMPONENT_TEMPLATE'  => 'basket',
        'PATH'                => SITE_DIR . 'include/footer/comp_viewed.php',
        'AREA_FILE_SHOW'      => 'file',
        'AREA_FILE_SUFFIX'    => '',
        'AREA_FILE_RECURSIVE' => 'Y',
        'EDIT_TEMPLATE'       => 'standard.php',
        'PRICE_CODE'          => [
            0 => 'BASE',
        ],
        'STORES' => [
            0 => '',
            1 => '',
        ],
        'BIG_DATA_RCM_TYPE' => 'bestsell'
    ],
    false
);?>
<?endif;?>

<?php
$page = $APPLICATION->GetCurPage(true);

if ($_SERVER['REDIRECT_REGION'] && strpos($page, $_SERVER['REDIRECT_REGION'] . '/index.php') !== false) {
    include_once 'top_footer_custom.php';
}?>

<?CMax::ShowPageType('footer');?>

<!-- marketnig popups -->
<?$APPLICATION->IncludeComponent(
    'aspro:marketing.popup.max',
    '.default',
    [],
    false,
    ['HIDE_ICONS' => 'Y']
);?>
<!-- /marketnig popups -->