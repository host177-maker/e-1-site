<?php
$arDisplays     = ['block', 'list', 'table'];
$arDelUrlParams = ['sort', 'order', 'control_ajax', 'ajax_get_filter', 'linerow', 'display'];
if(array_key_exists('display', $_REQUEST) || (array_key_exists('display', $_SESSION)) || $arParams['DEFAULT_LIST_TEMPLATE']) {
    if($_REQUEST['display'] && (in_array(trim($_REQUEST['display']), $arDisplays))) {
        $display             = trim($_REQUEST['display']);
        $_SESSION['display'] = trim($_REQUEST['display']);
    } elseif($_SESSION['display'] && (in_array(trim($_SESSION['display']), $arDisplays))) {
        $display = $_SESSION['display'];
    } else {
        $display = $arParams['DEFAULT_LIST_TEMPLATE'];
    }
} else {
    $display = 'block';
}
$template = 'catalog_' . $display;

if($arTheme['HEADER_TYPE']['VALUE'] == 28 || $arTheme['HEADER_TYPE']['VALUE'] == 29) {
    $APPLICATION->SetPageProperty('HIDE_LEFT_BLOCK', 'Y');
    $arTheme['LEFT_BLOCK_CATALOG_SECTIONS']['VALUE'] = 'N';
}

$bHideLeftBlock       = ($arTheme['LEFT_BLOCK_CATALOG_SECTIONS']['VALUE'] == 'N');
$bShowCompactHideLeft = ($arTheme['COMPACT_FILTER_HIDE_LEFT_BLOCK']['VALUE'] == 'Y');
if($bHideLeftBlock) {
    if($bShowCompactHideLeft) {
        $arTheme['FILTER_VIEW']['VALUE'] = 'COMPACT';
    } else {
        $arTheme['FILTER_VIEW']['VALUE'] = 'VERTICAL';
    }
}

$bShowSortInFilter = ($arParams['SHOW_SORT_IN_FILTER'] != 'N');

$bHideLeftBlock = $APPLICATION->GetDirProperty('HIDE_LEFT_BLOCK') == 'Y' || ($arTheme['HEADER_TYPE']['VALUE'] == 28 || $arTheme['HEADER_TYPE']['VALUE'] == 29);
// $arTheme["FILTER_VIEW"]["VALUE"] = 'VERTICAL';
?>

<div
	class="filter-panel sort_header view_<?=$display?> <?=($bShowCompactHideLeft && $bHideLeftBlock ? 'show-compact' : '');?>  <?=(!$bShowSortInFilter ? 'show-normal-sort' : '');?>">
	<?if($bShowFilter):?>
	<div
		class="filter-panel__filter pull-left filter-<?=strtolower($arTheme['FILTER_VIEW']['VALUE']);?> <?=($bHideLeftBlock && !$bShowCompactHideLeft ? 'filter-panel__filter--visible' : '');?>">
		<div
			class="bx-filter-title filter_title <?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? 'active-filter' : '')?>">
			<?=CMax::showIconSvg('icon', SITE_TEMPLATE_PATH . '/images/svg/catalog/filter.svg', '', '', true, false);?>
			<span
				class="font_upper_md font-bold darken <?=($bHideLeftBlock ? 'dotted' : '')?>"><?=\Bitrix\Main\Localization\Loc::getMessage('CATALOG_SMART_FILTER_TITLE');?></span>
		</div>
		<div class="controls-hr"></div>
	</div>
	<?endif;?>
	<!--noindex-->

	<div class="filter-panel__view controls-view pull-right">
		<?foreach($arDisplays as $displayType):?>
		<?php
                $current_url = '';
		    $current_url           = $APPLICATION->GetCurPageParam('display=' . $displayType, $arDelUrlParams);
		    // $url = str_replace('+', '%2B', $current_url);
		    $url = $current_url;
		    ?>
		<?if($display == $displayType):?>
		<span
			title="<?=\Bitrix\Main\Localization\Loc::getMessage('SECT_DISPLAY_' . strtoupper($displayType))?>"
			class="controls-view__link controls-view__link--<?=$displayType?> controls-view__link--current"><?=CMax::showIconSvg('type', SITE_TEMPLATE_PATH . '/images/svg/catalog/' . $displayType . 'type.svg', '', '', true, false);?></span>
		<?else:?>
		<a rel="nofollow" href="<?=$url;?>"
			data-url="<?=$url?>"
			title="<?=\Bitrix\Main\Localization\Loc::getMessage('SECT_DISPLAY_' . strtoupper($displayType))?>"
			class="controls-view__link controls-view__link--<?=$displayType?> muted<?=($arParams['AJAX_CONTROLS'] == 'Y' ? ' js-load-link' : '');?>"><?=CMax::showIconSvg('type', SITE_TEMPLATE_PATH . '/images/svg/catalog/' . $displayType . 'type.svg', '', '', true, false);?></a>
		<?endif;?>
		<?endforeach;?>
	</div>
	<?if($display == 'block'):?>
	<div class="filter-panel__view controls-linecount pull-right">
		<?$arLineCount = [3, 4];?>
		<?if(array_key_exists('linerow', $_REQUEST) || (array_key_exists('linerow', $_SESSION)) || $arParams['LINE_ELEMENT_COUNT']) {
		    if($_REQUEST['linerow'] && (in_array(trim($_REQUEST['linerow']), $arLineCount))) {
		        $linerow             = trim($_REQUEST['linerow']);
		        $_SESSION['linerow'] = trim($_REQUEST['linerow']);
		    } elseif($_SESSION['linerow'] && (in_array(trim($_SESSION['linerow']), $arLineCount))) {
		        $linerow = $_SESSION['linerow'];
		    } elseif($arParams['LINE_ELEMENT_COUNT'] && (in_array(trim($arParams['LINE_ELEMENT_COUNT']), $arLineCount))) {
		        $linerow = $arParams['LINE_ELEMENT_COUNT'];
		    } else {
		        $linerow = 4;
		    }
		} else {
		    $linerow = 4;
		}?>

		<?foreach($arLineCount as $value):?>
		<?php
		    $current_url = '';
		    $current_url = $APPLICATION->GetCurPageParam('linerow=' . $value, $arDelUrlParams);
		    $url         = str_replace('+', '%2B', $current_url);
		    ?>
		<?if($linerow == $value):?>
		<span
			title="<?=\Bitrix\Main\Localization\Loc::getMessage('SECT_DISPLAY_' . $value)?>"
			class="controls-view__link controls-view__link--current"><?=CMax::showIconSvg('type', SITE_TEMPLATE_PATH . '/images/svg/catalog/' . $value . 'inarow.svg', '', '', true, false);?></span>
		<?else:?>
		<a rel="nofollow" href="<?=$url;?>"
			data-url="<?=$url?>"
			title="<?=\Bitrix\Main\Localization\Loc::getMessage('SECT_DISPLAY_' . $value)?>"
			class="controls-view__link muted<?=($arParams['AJAX_CONTROLS'] == 'Y' ? ' js-load-link' : '');?>"><?=CMax::showIconSvg('type', SITE_TEMPLATE_PATH . '/images/svg/catalog/' . $value . 'inarow.svg', '', '', true, false);?></a>
		<?endif;?>
		<?endforeach;?>
		<div class="controls-hr"></div>
	</div>
	<?endif;?>
	<div class="clearfix"></div>
	<!--/noindex-->
</div>