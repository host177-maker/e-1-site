<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $this->setFrameMode( true ); ?>
<?use \Bitrix\Main\Localization\Loc;?>
<?if($arResult["SECTIONS"]){?>
	<?global $arTheme;
	$bSlick = ($arParams['NO_MARGIN'] == 'Y');
	$bIcons = ($arParams['SHOW_ICONS'] == 'Y');?>

	<div class="section-compact-list">
		<div class="row<?=($bSlick ? ' margin0' : '');?> flexbox">
			<?foreach( $arResult["SECTIONS"] as $arItems ){
				$this->AddEditAction($arItems['ID'], $arItems['EDIT_LINK'], CIBlock::GetArrayByID($arItems["IBLOCK_ID"], "SECTION_EDIT"));
				$this->AddDeleteAction($arItems['ID'], $arItems['DELETE_LINK'], CIBlock::GetArrayByID($arItems["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_SECTION_DELETE_CONFIRM')));
			?>
				<div class="<?=($arParams['GRID_CLASS']) ? $arParams['GRID_CLASS'] : 'col-lg-3 col-md-4 col-xs-6 col-xxs-12';?>">
					<div class="section-compact-list__item item bordered box-shadow flexbox flexbox--row section-compact-list__item-link" id="<?=$this->GetEditAreaId($arItems['ID']);?>">
						<a href="<?=$arItems["SECTION_PAGE_URL"]?>" class="dark_link section-compact-list__item_body">
							<?if($arParams["SHOW_SECTION_LIST_PICTURES"]=="Y"):?>
								<?if($arParams['BIG_IMG'] == 'Y'):?>
									<div class="img shine">
										<?if($arItems["PICTURE"]["SRC"]):?>
											<?$img = CFile::ResizeImageGet($arItems["PICTURE"]["ID"], array( "width" => 400, "height" => 400 ), BX_RESIZE_IMAGE_PROPORTIONAL, true );?>
											<div href="<?=$arItems["SECTION_PAGE_URL"]?>" class="thumb"><img src="<?=$img["src"]?>" alt="<?=($arItems["PICTURE"]["ALT"] ? $arItems["PICTURE"]["ALT"] : $arItems["NAME"])?>" title="<?=($arItems["PICTURE"]["TITLE"] ? $arItems["PICTURE"]["TITLE"] : $arItems["NAME"])?>" /></div>
										<?elseif($arItems["~PICTURE"]):?>
											<?$img = CFile::ResizeImageGet($arItems["~PICTURE"], array( "width" => 400, "height" => 400 ), BX_RESIZE_IMAGE_PROPORTIONAL, true );?>
											<div href="<?=$arItems["SECTION_PAGE_URL"]?>" class="thumb"><img src="<?=$img["src"]?>" alt="<?=($arItems["PICTURE"]["ALT"] ? $arItems["PICTURE"]["ALT"] : $arItems["NAME"])?>" title="<?=($arItems["PICTURE"]["TITLE"] ? $arItems["PICTURE"]["TITLE"] : $arItems["NAME"])?>" /></div>
										<?else:?>
											<div href="<?=$arItems["SECTION_PAGE_URL"]?>" class="thumb"><img src="<?=SITE_TEMPLATE_PATH?>/images/catalog_category_noimage.png" alt="<?=$arItems["NAME"]?>" title="<?=$arItems["NAME"]?>" /></div>
										<?endif;?>
									</div>
								<?else:?>
									<div class="section-compact-list__image<?=($bIcons && $arItems["UF_CATALOG_ICON"] ? ' with-icons colored_theme_svg' : '');?> flexbox flexbox--row">
										<?\Aspro\Functions\CAsproMaxItem::showSectionImg($arParams, $arItems, $bIcons);?>
									</div>
								<?endif;?>
							<?endif;?>
							<div class="section-compact-list__info">
								<div href="<?=$arItems["SECTION_PAGE_URL"]?>" class="section-compact-list__link dark_link option-font-bold"><span><?=$arItems["NAME"]?></span></div>
								<?if($arItems["ELEMENT_CNT"]):?>
									<span class="element-count2 muted font_upper"><?=\Aspro\Functions\CAsproMax::declOfNum($arItems["ELEMENT_CNT"], array(Loc::getMessage('COUNT_ELEMENTS_TITLE'), Loc::getMessage('COUNT_ELEMENTS_TITLE_2'), Loc::getMessage('COUNT_ELEMENTS_TITLE_3')))?></span>
								<?endif;?>
							</div>
						</a>
					</div>
				</div>
			<?}?>
		</div>
		</div>
<?}?>