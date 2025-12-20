<?php
/**
 *  @var array $dataFilterRule
 */

use Bitrix\Main\Localization\Loc;

$isVisibleTopInfo = $_COOKIE['SMARTSEO_VISIBLE_TOP_INFO'] === 'Y';

?>
<div class="aspro-smartseo__form-detail__info">
  <div class="aspro-smartseo__form-detail__info__row">
    <div class="aspro-smartseo__form-detail__info__col">
      <table>
        <tr>
          <td>
            <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ID') ?>:</span>
          </td>
          <td>
            <span><?= $dataFilterRule['ID'] ?></span>
          </td>
        </tr>
      </table>

      <div page-role="action-expand-info"
           class="adm-detail-title-setting aspro-smartseo__form-detail__info__arrow-expand <?= $isVisibleTopInfo ? 'adm-detail-title-setting-active' : '' ?>">
        <span class="adm-detail-title-setting-btn adm-detail-title-expand"></span>
      </div>
    </div>
  </div>

  <div page-role="container-expand-info" class="aspro-smartseo__form-detail__info__row"
       style="<?= !$isVisibleTopInfo ? 'display: none' : ''?>">
    <div class="aspro-smartseo__form-detail__info__col">
      <table>       
        <? if($dataFilterRule['DATE_CREATE'] && $dataFilterRule['DATE_CREATE'] instanceof \Bitrix\Main\Type\DateTime) : ?>
        <tr>
          <td>
            <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_CREATE') ?>: </span>
          </td>
          <td>
            <span><?= $dataFilterRule['DATE_CREATE']->toString() ?></span>
          </td>
        </tr>
        <? endif ?>
        <? if($dataFilterRule['DATE_CHANGE'] && $dataFilterRule['DATE_CHANGE'] instanceof \Bitrix\Main\Type\DateTime) : ?>
        <tr>
          <td>
            <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_CHANGE') ?>: </span>
          </td>
          <td>
            <span><?= $dataFilterRule['DATE_CHANGE']->toString() ?></span>
          </td>
        </tr>
        <? endif ?>
      </table>
    </div>
  </div>
</div>