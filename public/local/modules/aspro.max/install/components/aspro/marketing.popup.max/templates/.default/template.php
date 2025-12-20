<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?use \Bitrix\Main\Localization\Loc;?>
<?$frame = $this->createFrame()->begin('');?>
	<?if($arResult):
		foreach ($arResult as $key => $arItem):?>
			<?
			$type = ($arItem['PROPERTY_MODAL_TYPE_ENUM_ID']);
			$type = $type ? CIBlockPropertyEnum::GetByID( $type )['XML_ID'] : 'MAIN';
			?>
			<div 
				class="dyn_mp_jqm" 
				data-name="dyn_mp_jqm" 
				data-event="jqm" 
				data-param-type="marketing" 
				data-param-id="<?=$arItem['ID']?>" 
				data-param-iblock_id="<?=$arItem['IBLOCK_ID']?>"
				data-param-popup_type="<?=$arItem['POPUP_TYPE']?>"
				data-param-delay="<?=$arItem['PROPERTY_DELAY_SHOW_VALUE']?>"
				data-no-mobile="Y"
				data-ls="mw_<?=$arItem['ID']?>"
				data-ls_timeout="<?=$arItem['PROPERTY_LS_TIMEOUT_VALUE']?>"
				data-no-overlay="<?=$type == 'TEXT' ? 'Y' : ''?>"
				data-param-template="<?=$type?>"
			></div>
		<?endforeach;
	endif;?>
<?$frame->end();?>