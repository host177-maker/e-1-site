<?php

/**
 *  @var array $dataSitemapSitemap
 *  @var array $dataSitemapSite
 */
use Bitrix\Main\Localization\Loc;

$isVisibleTopInfo = $_COOKIE['SMARTSEO_VISIBLE_TOP_INFO'] === 'Y';
?>
<div class="aspro-smartseo__form-detail__info">
  <div class="aspro-smartseo__form-detail__info__row">
    <div class="aspro-smartseo__form-detail__info__col">
      <table>
        <? if ($dataSitemap['ID']) : ?>
            <tr>
              <td>
                <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ID') ?>:</span>
              </td>
              <td>
                <span><?= $dataSitemap['ID'] ?></span>
              </td>
            </tr>
        <? endif ?>
        <tr>
          <td>
            <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SITE_ID') ?>:</span>
          </td>
          <td>
            <span><?= $dataSite['NAME'] ?></span>
          </td>
        </tr>
      </table>
    </div>
    <div class="aspro-smartseo__form-detail__info__col">
        <table>
          <tr>
            <td>
              <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_LAST_LAUNCH') ?>:</span>
            </td>
            <td>
              <? if ($dataSitemap['DATE_LAST_LAUNCH'] && $dataSitemap['DATE_LAST_LAUNCH'] instanceof \Bitrix\Main\Type\DateTime) : ?>
              <span><?= $dataSitemap['DATE_LAST_LAUNCH']->toString() ?></span>
              <? else : ?>
              <span><?= Loc::getMessage('SMARTSEO_FORM_VALUE_DATE_LAST_LAUNCH_NEVER') ?></span>
              <? endif ?>
            </td>
          </tr>
          <? if($dataSitemap['SITEMAP_URL']) : ?>
            <tr>
              <td colspan="2">
                <? if ($dataSitemap['SITEMAP_URL']) : ?>
                <a target="_blank" href="<?= $dataSitemap['SITEMAP_URL'] ?>">
                  <?= $dataSitemap['SITEMAP_URL'] ?>
                </a>
                <? endif ?>
              </td>
            </tr>
          <? endif ?>
        </table>
    </div>

    <? if ($dataSitemap) : ?>
        <div class="aspro-smartseo__form-detail__info__arraow-wrapper">
          <div page-role="action-expand-info"
               class="adm-detail-title-setting aspro-smartseo__form-detail__info__arrow-expand <?= $isVisibleTopInfo ? 'adm-detail-title-setting-active'
              : '' ?>">
            <span class="adm-detail-title-setting-btn adm-detail-title-expand"></span>
          </div>
        </div>
    <? endif ?>

  </div>

  <? if ($dataSitemap) : ?>
      <div class="aspro-smartseo__form-detail__info__wrapper" page-role="container-expand-info" style="<?= !$isVisibleTopInfo ? 'display: none' : '' ?>">
        <div class="aspro-smartseo__form-detail__separator"></div>
        <div class="aspro-smartseo__form-detail__info__row">
          <div class="aspro-smartseo__form-detail__info__col">
            <table>
              <? if ($dataSitemap['DATE_CREATE'] && $dataSitemap['DATE_CREATE'] instanceof \Bitrix\Main\Type\DateTime) : ?>
                  <tr>
                    <td>
                      <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_CREATE') ?>: </span>
                    </td>
                    <td>
                      <span><?= $dataSitemap['DATE_CREATE']->toString() ?></span>
                    </td>
                  </tr>
              <? endif ?>
              <? if ($dataSitemap['DATE_CHANGE'] && $dataSitemap['DATE_CHANGE'] instanceof \Bitrix\Main\Type\DateTime) : ?>
                  <tr>
                    <td>
                      <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_CHANGE') ?>: </span>
                    </td>
                    <td>
                      <span><?= $dataSitemap['DATE_CHANGE']->toString() ?></span>
                    </td>
                  </tr>
              <? endif ?>
            </table>
          </div>
        </div>
      </div>

  <? endif ?>
</div>