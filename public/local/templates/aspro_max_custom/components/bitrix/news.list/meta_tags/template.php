<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?if($arResult['ITEMS']):?>
	<div class="tags">
		<ul class="tags__list">
			<?foreach($arResult['ITEMS'] as $i => $arItem):?>
				<?
					$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
					$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				?>
					<? if (!empty($arItem["PROPERTIES"]["LINK"]["VALUE"]) && !empty($arItem["PROPERTIES"]["NAME"]["VALUE"])) {?>
						<li class="tags__list-item">
							<a class="tags__list-item-link" href="<?= $arItem["PROPERTIES"]["LINK"]["VALUE"] ?>"><?= $arItem["PROPERTIES"]["NAME"]["VALUE"] ?></a>
						</li>
					<? } ?>
			<?endforeach;?>
		</ul>
	</div>
<?endif;?>