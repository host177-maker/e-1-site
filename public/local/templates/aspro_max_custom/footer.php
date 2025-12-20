<?php CMax::checkRestartBuffer() ?>
<?php IncludeTemplateLangFile(__FILE__) ?>
<?php if (!$isIndex) { ?>
    <?php if($isHideLeftBlock && !$isWidePage) { ?>
        </div> <?php // .maxwidth-theme ?>
    <?php } ?>
    </div> <?php // .container ?>
<?php } else { ?>
    <?php CMax::ShowPageType('indexblocks') ?>
<?php } ?>
<?php CMax::get_banners_position('CONTENT_BOTTOM') ?>
</div> <?php // .middle ?>

<?php if(($isIndex && ($isShowIndexLeftBlock || $bActiveTheme)) || (!$isIndex && !$isHideLeftBlock)) { ?>
    </div> <?php // .right_block ?>
    <?php if ($APPLICATION->GetProperty("HIDE_LEFT_BLOCK") !== "Y" && !defined("ERROR_404")) { ?>
        <?php CMax::ShowPageType('left_block') ?>
    <?php } ?>
<?php } ?>
</div> <?php // .container_inner ?>
<?php if($isIndex) { ?>
    </div>
<?php } elseif(!$isWidePage) { ?>
    </div> <?php // .wrapper_inner ?>
<?php } ?>
</div> <?php #content ?>
<?php CMax::get_banners_position('FOOTER') ?>
</div><?php // .wrapper ?>

<footer id="footer">
    <?php
    $APPLICATION->IncludeFile('/include/footer_include/under_footer.php');
    $APPLICATION->IncludeFile('/include/footer_include/top_footer.php');
    ?>
</footer>

<?php
$APPLICATION->IncludeComponent("bitrix:main.include", "", [
    "AREA_FILE_SHOW" => "file",
    "PATH" => "/include/getblueFooter.php",
]);
$APPLICATION->IncludeFile('/include/footer_include/bottom_footer.php');
echo COrwoFunctions::ShowAddidgitalJsScripts();

if (!isPageSpeedDetected()) {
    $APPLICATION->IncludeFile('/include/footer_include/counters/calltouch.php');
    $APPLICATION->IncludeFile('/include/footer_include/body_end.php');
    $APPLICATION->IncludeFile('/include/footer_include/counters/dolyame.php');
}
?>

</body>

</html>
