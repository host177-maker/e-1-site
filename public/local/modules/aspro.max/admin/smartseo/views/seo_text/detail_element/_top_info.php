<?php
/**
 * @var string $alias
 * @var string $aliasProperty
 * @var array $data
 * @var array $dataProperties
 * @var array $listSites
 * @var array $listIblockTypes
 * @var array $listIblocks
 * @var array $listIblockSections
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$isVisibleTopInfo = $_COOKIE['SMARTSEO_VISIBLE_TOP_INFO'] === 'Y';
?>
<div class="aspro-smartseo__form-detail__info">
  <div class="aspro-smartseo__form-detail__info__row">
    <div class="aspro-smartseo__form-detail__info__col">
      <table>
        <? if ($data['ID']) : ?>
            <tr>
              <td>
                <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ID') ?>:</span>
              </td>
              <td>
                <span><?= $data['ID'] ?></span>
              </td>
            </tr>
        <? endif ?>
        <tr>
          <td>
            <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_TYPE') ?>:</span>
          </td>
          <td>
            <span>
                <?= Loc::getMessage('SMARTSEO_FORM_VALUE_TYPE_SECTION'); ?>
            </span>
          </td>
        </tr>
      </table>
    </div>
    <div class="aspro-smartseo__form-detail__info__col"></div>

    <? if ($data) : ?>
        <div class="aspro-smartseo__form-detail__info__arraow-wrapper">
          <div page-role="action-expand-info"
               class="adm-detail-title-setting aspro-smartseo__form-detail__info__arrow-expand <?= $isVisibleTopInfo ? 'adm-detail-title-setting-active'
              : '' ?>">
            <span class="adm-detail-title-setting-btn adm-detail-title-expand"></span>
          </div>
        </div>
    <? endif ?>

  </div>

  <? if ($data) : ?>
      <div class="aspro-smartseo__form-detail__info__wrapper" page-role="container-expand-info" style="<?= !$isVisibleTopInfo ? 'display: none' : '' ?>">
        <div class="aspro-smartseo__form-detail__separator"></div>
        <div class="aspro-smartseo__form-detail__info__row">
          <div class="aspro-smartseo__form-detail__info__col">
            <table>
              <? if ($data['DATE_CREATE'] && $data['DATE_CREATE'] instanceof \Bitrix\Main\Type\DateTime) : ?>
                  <tr>
                    <td>
                      <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_CREATE') ?>: </span>
                    </td>
                    <td>
                      <span><?= $data['DATE_CREATE']->toString() ?></span>
                    </td>
                  </tr>
              <? endif ?>
              <? if ($data['DATE_CHANGE'] && $data['DATE_CHANGE'] instanceof \Bitrix\Main\Type\DateTime) : ?>
                  <tr>
                    <td>
                      <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_CHANGE') ?>: </span>
                    </td>
                    <td>
                      <span><?= $data['DATE_CHANGE']->toString() ?></span>
                    </td>
                  </tr>
              <? endif ?>
            </table>
          </div>
        </div>
      </div>

  <? endif ?>
</div>