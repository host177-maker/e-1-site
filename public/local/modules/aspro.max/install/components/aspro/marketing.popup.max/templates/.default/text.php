<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?use \Bitrix\Main\Localization\Loc;?>
<?$frame = $this->createFrame()->begin('');?>
	<?
	$bPicture = ($arResult['ITEM']['PREVIEW_PICTURE']);

	$type = 'TEXT';

	$position = ($arResult['ITEM']['PROPERTY_POSITION_ENUM_ID']);
	$position = $position ? CIBlockPropertyEnum::GetByID( $position )['XML_ID'] : 'BOTTOM_LEFT';

	$bBtn1 = strtoupper($arResult['ITEM']['PROPERTY_BTN1_TEXT_VALUE']);
	$bBtn2 = strtoupper($arResult['ITEM']['PROPERTY_BTN2_TEXT_VALUE']);
	
	$link1 = $arResult['ITEM']["PROPERTY_BTN1_LINK_VALUE"] ? 'href="'.SITE_DIR.$arResult['ITEM']["PROPERTY_BTN1_LINK_VALUE"].'"' : '';
	$link2 = $arResult['ITEM']["PROPERTY_BTN2_LINK_VALUE"] ? 'href="'.SITE_DIR.$arResult['ITEM']["PROPERTY_BTN2_LINK_VALUE"].'"' : '';
	
	?>
	<div class="form marketing-popup popup-text-info <?=$templateName?>" data-classes="<?=$type?> <?=$position?> <?=$position == 'BOTTOM_CENTER' ? 'maxwidth-theme-popup' : ''?>">
		<?
		// echo '<prE>';
		// print_r($arResult['ITEM']);
		// echo '</pre>';
		?>
		<?if($arResult['ITEM']):?>
			<?if($arResult['ITEM']):?>

				<?if($position == 'BOTTOM_CENTER'):?>
					<div class="popup-text-info__wrapper">
				<?endif;?>

				<?if( $arResult['ITEM']['PROPERTY_HIDE_TITLE_VALUE'] != 'Y' ):?>
					<div class="popup-text-info__title font_exlg darken option-font-bold"><?=$arResult['ITEM']["NAME"];?></div>
				<?endif;?>
				<div class="popup-text-info__text font_sm">
					<?$obParser = new CTextParser;?>
					<?=$obParser->html_cut($arResult['ITEM']["PREVIEW_TEXT"], 500);?>
					<?if($bBtn1 || $bBtn2):?>

						<?if($position == 'BOTTOM_CENTER'):?>
								</div>
							</div>
						<?endif;?>

						<div class="popup-text-info__btn">
							<?if($bBtn1):?>
								<<?=$link1 ? 'a' : 'span'?> class="btn <?=($arResult['ITEM']['BTN1_CLASS_INFO']['XML_ID'] ? $arResult['ITEM']['BTN1_CLASS_INFO']['XML_ID'] : "btn-default");?>" <?=$link1?> >
									<?=$arResult['ITEM']['PROPERTY_BTN1_TEXT_VALUE'];?>
								</<?=$link1 ? 'a' : 'span'?>>
							<?endif;?>
							<?if($bBtn2):?>
								<<?=$link2 ? 'a' : 'span'?>  class="btn <?=($arResult['ITEM']['BTN2_CLASS_INFO']['XML_ID'] ? $arResult['ITEM']['BTN2_CLASS_INFO']['XML_ID'] : "btn-transparent-border");?>" <?=$link2?> >
									<?=$arResult['ITEM']['PROPERTY_BTN2_TEXT_VALUE'];?>
								</<?=$link2 ? 'a' : 'span'?>>
							<?endif;?>
						</div>
					<?endif;?>
				</div>

			<?endif;?>
		<?else:?>
			ERROR
		<?endif;?>
	</div>

<?$frame->end();?>