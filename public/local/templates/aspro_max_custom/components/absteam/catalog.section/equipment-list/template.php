<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Web\Json,
Bitrix\Main\Localization\Loc;?>

<?
$showElements = true;
?>
<?
    global $iScuBlockCounter;
?>
<?if($arResult['NAME']):?>
    <p class="product-param-choose-title equipment-block-title"><span><?=$iScuBlockCounter;?>. </span><?=$arResult['NAME'];?></p>
<?endif;?>
<div class="equipment-block-summ-cost"><span>+ 0 ₽</span>к базовой стоимости</div>
<?if($arResult["ITEMS"] && $showElements):?>
    <ul class="equipment-block-template">
        <?foreach($arResult["ITEMS"] as $key => $arItem):?>
            <?$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
        ?>
            <li style="" data-item="<?=$arItem['ID'];?>" data-iblockid="<?=$arItem['IBLOCK_ID'];?>" data-price="<?=$arItem['PRICE'];?>" data-code="<?=$arItem['PROPERTIES']['SERVICE_PRICE']['VALUE'];?>"><span class="cnt1"><span data-lazyload="" class="cnt_item lazyloaded" style="background-image:url('<?=$arItem['PREVIEW_PICTURE']['SRC'];?>');" data-obgi="url('<?=$arItem['DETAIL_PICTURE']['SRC'];?>')" title="<?=$arItem['NAME'];?>"></span></span><i title="<?=$arItem['NAME'];?>"></i></li>
        <?endforeach;?>
    </ul>
<?endif;?>

<?$iScuBlockCounter++;?>