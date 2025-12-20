<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? $this->setFrameMode(true); ?>

<?
COrwoFunctions::PrepareCatalogItemArray($arResult, 'list', $arParams);
?>

<? if ($arResult["ITEMS"]): ?>
    <? foreach ($arResult["ITEMS"] as $key => $arItem): ?>
        <?
        $arPrice = CCatalogProduct::GetOptimalPrice($arItem['ID']);
        ?>
        <div class="swiper-slide">
            <?
            // \Bitrix\Main\Diag\Debug::dump($arPrice);
            ?>
            <a href="<?= $arItem['DETAIL_PAGE_URL']; ?>" class="wardrobes__card">
                <div class="wardrobes__card-info">
                    <img src="<?= $arItem['DETAIL_PICTURE']['SRC']; ?>" alt="" />
                    <div class="wardrobes__card-title"><?= $arItem['NAME']; ?></div>
                    <div class="wardrobes__card-price">от <?= number_format($arPrice['RESULT_PRICE']['DISCOUNT_PRICE'], 0, ',', ' ') ?> ₽</div>
                </div>
                <div class="wardrobes__card-button">
                    <button class="btn btn-default js-buy-btn" data-url="?action=BUY&id=<?= $arItem['OFFERS'][0]['ID']; ?>">купить</button>
                </div>
            </a>
        </div> <!-- /.swiper-slide -->
    <? endforeach; ?>
<? endif; ?>