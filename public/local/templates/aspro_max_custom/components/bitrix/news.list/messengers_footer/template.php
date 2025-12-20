<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?if($arResult['ITEMS']):?>
	<div class="messengers-icons  wrap_icon">
		<?foreach($arResult['ITEMS'] as $i => $arItem):?>
			<?
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
			?>
			<a id="<?=$this->GetEditAreaId($arItem['ID'])?>" class="messenger" href="<?= $arItem["PROPERTIES"]["LINK"]["VALUE"] ?>">
				<img alt="<?= $arItem["PROPERTIES"]["FILE_FOOTER"]["DESCRIPTION"] ?>" src="<?= CFile::GetPath($arItem["PROPERTIES"]["FILE_FOOTER"]["VALUE"]) ?>">
			</a>
		<?endforeach;?>
	</div>
<?endif;?>