<?php
/**
 * @var array $arParams
 * @var array $arItem
 * @var array $arQuantityData
 * @var array $arQuantityDataCMP
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

?>
<div class="sa_block"
     data-id-t="<?= $arItem['OFFERS'][0]['ID'] ?>"
     data-fields='<?= Json::encode($arParams["FIELDS"]) ?>'
     data-stores='<?= Json::encode($arParams["STORES"]) ?>'
     data-user-fields='<?= Json::encode($arParams["USER_FIELDS"]) ?>'
>
    <?php
    echo $arQuantityData["HTML"];
    if (isset($arQuantityDataCMP) && $arQuantityDataCMP && $arItem['OFFERS']) {
        echo $arQuantityDataCMP["HTML"];
    }

    $bHasArticle = isset($arItem['ARTICLE']) && $arItem['ARTICLE']['VALUE'];
    $attrs = [
        'class="article_block"',
    ];
    if ($bHasArticle) {
        $attrs[] = 'data-name="' . Loc::getMessage('T_ARTICLE_COMPACT') . '"';
        $attrs[] = 'data-value="' . $arItem['ARTICLE']['VALUE'] . '"';
    }
    ?>
    <div <?= implode(' ', $attrs)?>>
        <?php if ($bHasArticle) { ?>
            <div class="muted font_sxs">
                <?= Loc::getMessage('T_ARTICLE_COMPACT') ?>: <?= $arItem['ARTICLE']['VALUE'] ?>
            </div>
        <?php } ?>
    </div>
</div>
