<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (empty($arResult)) {
    return;
}
?>

<div class="city-tags">
    <?php foreach ($arResult as $city) { ?>
        <a class="tag" href="<?= $city['url'] ?>"><?= $city['name'] ?></a>
    <?php } ?>
</div>
