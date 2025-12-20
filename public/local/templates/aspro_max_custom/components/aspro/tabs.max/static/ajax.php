<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$this->setFrameMode(true);

$class_block="s_".$this->randString();

$arTab=array();

$arParams['SET_TITLE'] = 'N';
$arTmp = reset($arResult["TABS"]);
$arParams["FILTER_HIT_PROP"] = $arTmp["CODE"];
$arParamsTmp = urlencode(serialize($arParams));

if($arResult["SHOW_SLIDER_PROP"]):?>
	<div class="content_wrapper_block <?=$templateName;?>">
		<div class="maxwidth-theme best-sale">
			<div class="tab_slider_wrapp specials <?=$class_block;?> best_block clearfix" itemscope itemtype="http://schema.org/WebPage">
				<span class='request-data' data-value='<?=$arParamsTmp?>'></span>
				<div class="top_block">
					<?if($arParams['TITLE_BLOCK']):?>
						<h3><?=$arParams['TITLE_BLOCK'];?></h3>
					<?endif;?>
					<div class="right_block_wrapper">
						<?if($arParams['TITLE_BLOCK_ALL'] && $arParams['ALL_URL']):?>
							<a href="<?=$arParams['ALL_URL'];?>" class="font_upper muted"><?=$arParams['TITLE_BLOCK_ALL'];?></a>
						<?endif;?>
					</div>
				</div>
				<ul class="tabs_content">
					<li class="tab  cur opacity1" >
						<div class="tabs_slider  wr">
							<?if(strtolower($_REQUEST['ajax']) == 'y')
								$APPLICATION->RestartBuffer();

								//start e1 fix
								COrwoFunctions::MakeElementFilterInRegion($arElementFilter, $GLOBALS[$arParams['PREFILTER_NAME']], $GLOBALS[$arParams['FILTER_NAME']], $arParams);
								//end e1 fix

								include(str_replace("//", "/", $_SERVER["DOCUMENT_ROOT"].SITE_DIR."include/mainpage/comp_catalog_ajax.php"));
							?>
							<?if(strtolower($_REQUEST['ajax']) == 'y')
								CMax::checkRestartBuffer(true, 'catalog_tab');?>
						</div>
					</li>
				</ul>
        <div class="sale-block">
			<?if ( \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_IS_HOME_SHOW_BUTTON', 'N') !== "Y") {?>
				<a
				  href="https://salebot.site/md/catalog_e1_2"
				  class="btn btn-default has-ripple"
				  target="_blank">Скачать новый каталог</a>

			<? } ?>
        </div>
			</div>
			<div class="visible-lg visible-md">
			<?
			$sUrlPage = $APPLICATION->GetCurPage(false);
			global $arrFilterMeta;
			$arrFilterMeta   =   array("ACTIVE" => "Y", "PROPERTY_URL" =>  (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_URL_TAGS_SECTION'))) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_URL_TAGS_SECTION'): '/catalog/shkafy_kupe/');
			//добавляем мета теги
			$APPLICATION->IncludeComponent("bitrix:news.list","meta_tags",Array(
					"DISPLAY_DATE" => "Y",
					"DISPLAY_NAME" => "Y",
					"DISPLAY_PICTURE" => "Y",
					"DISPLAY_PREVIEW_TEXT" => "Y",
					"AJAX_MODE" => "N",
					"IBLOCK_TYPE" => "aspro_max_content",
					"IBLOCK_ID" => Cosmos\Config::getInstance()->getIblockIdByCode('meta_tags'),
					"NEWS_COUNT" => "100",
					"SORT_BY1" => "ACTIVE_FROM",
					"SORT_ORDER1" => "DESC",
					"SORT_BY2" => "SORT",
					"SORT_ORDER2" => "ASC",
					"FILTER_NAME" => "arrFilterMeta",
					"FIELD_CODE" => Array("ID"),
					"PROPERTY_CODE" => Array("NAME", "LINK", "URL"),
					"CHECK_DATES" => "Y",
					"DETAIL_URL" => "",
					"PREVIEW_TRUNCATE_LEN" => "",
					"ACTIVE_DATE_FORMAT" => "d.m.Y",
					"SET_TITLE" => "N",
					"SET_BROWSER_TITLE" => "N",
					"SET_META_KEYWORDS" => "N",
					"SET_META_DESCRIPTION" => "N",
					"SET_LAST_MODIFIED" => "Y",
					"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
					"ADD_SECTIONS_CHAIN" => "N",
					"HIDE_LINK_WHEN_NO_DETAIL" => "Y",
					"PARENT_SECTION" => "",
					"PARENT_SECTION_CODE" => "",
					"INCLUDE_SUBSECTIONS" => "Y",
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600",
					"CACHE_FILTER" => "Y",
					"CACHE_GROUPS" => "N",
					"DISPLAY_TOP_PAGER" => "N",
					"DISPLAY_BOTTOM_PAGER" => "N",
					"PAGER_TITLE" => "Мета теги",
					"PAGER_SHOW_ALWAYS" => "N",
					"PAGER_TEMPLATE" => "",
					"PAGER_DESC_NUMBERING" => "Y",
					"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
					"PAGER_SHOW_ALL" => "N",
					"PAGER_BASE_LINK_ENABLE" => "Y",
					"SET_STATUS_404" => "N",
					"SHOW_404" => "N",
					"MESSAGE_404" => "",
					"PAGER_BASE_LINK" => "",
					"PAGER_PARAMS_NAME" => "arrPager",
					"AJAX_OPTION_JUMP" => "N",
					"AJAX_OPTION_STYLE" => "Y",
					"AJAX_OPTION_HISTORY" => "N",
					"AJAX_OPTION_ADDITIONAL" => "",
					"CURRENT_SECTION" => ["GLOBAL_ACTIVE"=>  "Y", "ID" => (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_ID_TAGS_SECTION'))) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_ID_TAGS_SECTION'): '348', "IBLOCK_ID" => Cosmos\Config::getInstance()->getIblockIdByCode('1c_catalog')]
				)
			);?>
			</div>
		</div>
	</div>
	<script>try{window.tabsInitOnReady();}catch{}</script>
<?endif;?>