<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php $this->setFrameMode(true); ?>

<?php if ($arResult['ITEMS']): ?>
    <?php foreach ($arResult['ITEMS'] as $arItem): ?>
        <?php
        $file = CFile::MakeFileArray($arItem['PROPERTIES']['FILE']['VALUE']);

        $nameParts = explode('.', $file['name']);
        $extension = end($nameParts);
        ?>

        <div class="instruction-item">
            <div class="instruction-head accordion-head accordion-close" data-toggle="collapse" data-parent="#instruction-body-<?= $arItem['ID'] ?>" href="#instruction-body-<?= $arItem['ID'] ?>">
                    <span class="instruction-arrow">
                        <svg width="8" height="6" viewBox="0 0 8 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6L7.4641 0H0.535898L4 6Z" fill="#999999"/>
                        </svg>
                    </span>
                <a class="accordion-active-link" href="<?= CFile::GetPath($arItem['PROPERTIES']['FILE']['VALUE']) ?>" target="_blank">
                    <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13 0.502559C13.0012 0.23662 12.7865 0.0200914 12.5206 0.0189301L8.18735 4.69057e-06C7.92144 -0.00115667 7.70494 0.213489 7.70377 0.479428C7.70261 0.745368 7.91723 0.961896 8.18315 0.963058L11.354 0.976906L5.92026 6.36401L6.59819 7.04797L12.032 1.66086L12.0181 4.83209C12.017 5.09803 12.2316 5.31456 12.4975 5.31572C12.7634 5.31688 12.9799 5.10224 12.9811 4.8363L13 0.502559ZM0.962958 2.92466C0.962958 2.65872 1.17852 2.44313 1.44444 2.44313H5.37034V1.48007H1.44444C0.646698 1.48007 0 2.12683 0 2.92466V12.5553C0 13.3531 0.646697 13.9999 1.44444 13.9999H10.1111C10.9088 13.9999 11.5555 13.3531 11.5555 12.5553V7.67881H10.5925V12.5553C10.5925 12.8212 10.377 13.0368 10.1111 13.0368H1.44444C1.17852 13.0368 0.962958 12.8212 0.962958 12.5553V2.92466Z" fill="currentColor"/>
                    </svg>
                    <span><?= $arItem['NAME'] ?></span>
                </a>
                <div class="instruction-size">(<?= mb_strtolower($extension) ?>, <?= \Cosmos\Content::getHumanFilesize($file['size'], 0) ?>)</div>
            </div>
            <div id="instruction-body-<?= $arItem['ID'] ?>" class="panel-collapse collapse">
                <div class="instruction-body">
                    <div><?= $arItem['PREVIEW_TEXT'] ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
