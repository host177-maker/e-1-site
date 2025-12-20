<?php

/**
 * @var array $arResult
 */

$this->setFrameMode(true);

if ($arResult["NavPageCount"] <= 1) {
    return;
}

if ($arResult["NavQueryString"]) {
    $arUrl = explode('&amp;', $arResult["NavQueryString"]);
    if ($arUrl) {
        foreach ($arUrl as $key => $url) {
            if (str_contains($url, 'ajax_get') || str_contains($url, 'AJAX_REQUEST')) {
                unset($arUrl[$key]);
            }
        }
    }

    $arResult["NavQueryString"] = implode('&amp;', $arUrl);
}

$count_item_between_cur_page = 2; // count numbers left and right from cur page
$count_item_dotted = 2; // count numbers to end or start pages

$arResult["nStartPage"] = $arResult["NavPageNomer"] - $count_item_between_cur_page;
$arResult["nStartPage"] = $arResult["nStartPage"] <= 0 ? 1 : $arResult["nStartPage"];
$arResult["nEndPage"] = $arResult["NavPageNomer"] + $count_item_between_cur_page;
$arResult["nEndPage"] = min($arResult["nEndPage"], $arResult["NavPageCount"]);
$strNavQueryString = ($arResult["NavQueryString"] !== "" ? $arResult["NavQueryString"] . "&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] !== "" ? "?" . $arResult["NavQueryString"] : "");

if ($arResult["NavPageNomer"] === 1) {
    $bPrevDisabled = true;
} elseif ($arResult["NavPageNomer"] < $arResult["NavPageCount"]) {
    $bPrevDisabled = false;
}

if ($arResult["NavPageNomer"] === $arResult["NavPageCount"]) {
    $bNextDisabled = true;
} else {
    $bNextDisabled = false;
}
?>

<?php if (!$bNextDisabled) { ?>
    <div class="ajax_load_btn rounded3 colored_theme_hover_bg">
        <span class="more_text_ajax font_upper_md"><?= GetMessage('PAGER_SHOW_MORE') ?></span>
    </div>
<?php } ?>

<?php
global $APPLICATION;
$pagen = 'PAGEN_' . $arResult["NavNum"];
$bHasPage = (isset($_GET[$pagen]) && $_GET[$pagen]);

if ($bHasPage) {
    if ($_GET[$pagen] === 1 && !isset($_GET['q'])) {
        LocalRedirect($arResult["sUrlPath"], false, "301 Moved permanently");
    } elseif ($_GET[$pagen] > $arResult["nEndPage"]) {
        if (!defined("ERROR_404")) {
            define("ERROR_404", "Y");
            CHTTP::setStatus("404 Not Found");
        }
    }

}
?>

<div class="module-pagination">
    <div class="nums">
        <ul class="flex-direction-nav">
            <?php if (!$bPrevDisabled) { ?>
                <?php
                $page = ($bHasPage ? ($arResult["NavPageNomer"] - 1 === 1 ? '' : $arResult["NavPageNomer"] - 1) : '');
                $url = ($page ? '?' . $strNavQueryString . $pagen . '=' . $page : $strNavQueryStringFull);
                ?>
                <li class="flex-nav-prev colored_theme_hover_text">
                    <a href="<?= $arResult["sUrlPath"] ?><?= $url ?>" class="flex-prev">
                        <?= CMax::showIconSvg(
                            "down",
                            SITE_TEMPLATE_PATH . '/images/svg/catalog/arrow_pagination.svg',
                            '',
                            '',
                            true,
                            false
                        ) ?>
                    </a>
                </li>
            <?php } ?>

            <?php if (!$bNextDisabled) { ?>
                <li class="flex-nav-next colored_theme_hover_text">
                    <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] + 1) ?>"
                       class="flex-next">
                        <?= CMax::showIconSvg(
                            "down",
                            SITE_TEMPLATE_PATH . '/images/svg/catalog/arrow_pagination.svg',
                            '',
                            '',
                            true,
                            false
                        ) ?>
                    </a>
                </li>
            <?php } ?>
        </ul>

        <?php if ($arResult["nStartPage"] > 1) { ?>
            <a href="<?= $arResult["sUrlPath"] ?>" class="dark_link">1</a>

            <?php if (($arResult["nStartPage"] - $count_item_dotted) > 1) { ?>
                <span class='point_sep'>...</span>
            <?php } elseif (($firstPage = $arResult["nStartPage"] - 1) > 1 && $arResult["nStartPage"] !== 2) { ?>
                <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= $firstPage ?>">
                    <?= $firstPage ?>
                </a>
            <?php } ?>
        <?php } ?>

        <?php while ($arResult["nStartPage"] <= $arResult["nEndPage"]) { ?>
            <?php if ($arResult["nStartPage"] === $arResult["NavPageNomer"]) { ?>
                <span class="cur"><?= $arResult["nStartPage"] ?></span>
            <?php } elseif ($arResult["nStartPage"] === 1 && $arResult["bSavePage"] === false) { ?>
                <a href="<?= $arResult["sUrlPath"] ?><?= $strNavQueryStringFull ?>"
                   class="dark_link"><?= $arResult["nStartPage"] ?></a>
            <?php } else { ?>
                <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= $arResult["nStartPage"] ?>"
                   class="dark_link"><?= $arResult["nStartPage"] ?></a>
            <?php } ?>
            <?php $arResult["nStartPage"]++ ?>
        <?php } ?>

        <?php if ($arResult["nEndPage"] < $arResult["NavPageCount"]) { ?>
            <?php if (($arResult["nEndPage"] + $count_item_dotted) < $arResult["NavPageCount"]) { ?>
                <span class='point_sep'>...</span>
            <?php } elseif (($lastPage = $arResult["nEndPage"] + 1) < $arResult["NavPageCount"]) { ?>
                <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= $lastPage ?>">
                    <?= $lastPage ?>
                </a>
            <?php } ?>

            <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= $arResult["NavPageCount"] ?>"
               class="dark_link"><?= $arResult["NavPageCount"] ?></a>
        <?php } ?>

        <?php if ($arResult["bShowAll"]) { ?>
            <div class="all_block_nav">
                <!--noindex-->
                <?php if ($arResult["NavShowAll"]) { ?>
                    <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>SHOWALL_<?= $arResult["NavNum"] ?>=0"
                       class="link" rel="nofollow"><?= GetMessage("nav_paged") ?></a>
                <?php } else { ?>
                    <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>SHOWALL_<?= $arResult["NavNum"] ?>=1"
                       class="link" rel="nofollow"><?= GetMessage("nav_all") ?></a>
                <?php } ?>
                <!--/noindex-->
            </div>
        <?php } ?>
    </div>
</div>
