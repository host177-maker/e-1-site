<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);
$arParams['TITLE_BLOCK'] = strlen($arParams['TITLE_BLOCK']) ? $arParams['TITLE_BLOCK'] : GetMessage('CATALOG_VIEWED_TITLE');
global $bHeaderStickyMenu;
?>
<!-- noindex -->
<?if(strlen($arResult['ERROR'])):?>
	<?ShowError($arResult['ERROR']);?>
<?else:?>
	<?if($arResult['ITEMS']):?>
		<div class="viewed-wrapper swipeignore <?=$templateName;?>">
			<h3 class="font_lg"><?=$arParams["TITLE_BLOCK"]?></h3>
			<div class="block-items flexbox flexbox--row owl-carousel owl-theme owl-bg-nav short-nav hidden-dots visible-nav loading-state block-items--margined" data-plugin-options='{"nav": true, "autoplay" : false, "autoplayTimeout" : "3000", "margin": 10, "smartSpeed":1000, <?=(count($arResult["ITEMS"]) > 5 ? "\"loop\": true," : "")?> "responsiveClass": true, "responsive":{"0":{"items": 1},"600":{"items": 2},"768":{"items": 3},"992":{"items": <?=$bHeaderStickyMenu ? '3' : '4'?>},"1200":{"items": <?=$bHeaderStickyMenu ? '4' : '5'?>}}}'>
				
					<?foreach($arResult['ITEMS'] as $key=>$arItem):?>
						<?
						if($key > 7)
							continue;
						$isItem = (isset($arItem['PRODUCT_ID']) ? true : false);
						?>
						<div class="block-item bordered rounded3<?=($isItem ? " box-shadow-sm" : '');?>">
							<?if($isItem):?>
								<div data-id=<?=$arItem['PRODUCT_ID']?> data-picture='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arItem['PICTURE']))?>' class="item_wrap item block-item__wrapper<?=($isItem ? ' has-item' : '' );?>" id=<?=$this->GetEditAreaId($arItem['PRODUCT_ID'])?>>
									<?
									$this->AddEditAction($arItem['PRODUCT_ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT'));
									$this->AddDeleteAction($arItem['PRODUCT_ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
									?>
								</div>
							<?else:?>
								<div class="item_wrap item block-item__wrapper"></div>
							<?endif;?>
						</div>
					<?endforeach;?>
				
			</div>
		</div>
		<script type="text/javascript">
			BX.message({
				LAST_ACTIVE_FROM_VIEWED: '<?=$arResult['LAST_ACTIVE_FROM']?>',
				SHOW_MEASURE_VIEWED: '<?=($arParams['SHOW_MEASURE'] !== 'N' ? 'true' : '')?>',
				SITE_TEMPLATE_PATH: '<?=SITE_TEMPLATE_PATH?>',
				CATALOG_FROM_VIEWED: '<?=GetMessage('CATALOG_FROM')?>',
				SITE_ID: '<? echo SITE_ID; ?>'
			})
			var lastViewedTime = BX.message('LAST_ACTIVE_FROM_VIEWED');
			var bShowMeasure = BX.message('SHOW_MEASURE_VIEWED');
			var $viewedSlider = $('.viewed-wrapper .block-item');

			showViewedItems(lastViewedTime, bShowMeasure, $viewedSlider);
		</script>
	<?endif;?>
<?endif;?>
<!-- /noindex -->