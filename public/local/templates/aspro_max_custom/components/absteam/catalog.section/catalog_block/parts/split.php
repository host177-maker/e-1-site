<?php
/**
 * @var array $arItem
 */
?>
<div class="catalog-item__split-container">
    <yandex-pay-badge
            merchant-id="5ef67561-d34e-45e2-bb25-8320a4653548"
            type="bnpl"
            amount="<?= data_get($arItem, 'MIN_PRICE', [])["DISCOUNT_VALUE"] ?>"
            size="m"
            theme="light"
            align="left"
            color="primary"
            style="max-width: 100%"
    />
</div>