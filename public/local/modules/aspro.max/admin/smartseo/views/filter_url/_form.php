<?php
/**
 *  @var string $aliasUrl
 *  @var string $aliasSeo
 *  @var array $dataUrl
 *  @var array $dataSeo
 *  @var string $gridId
 *  @var int $filterRuleId
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$_suffix = $this->getUnique();
?>
<form id="form_url_<?= $_suffix ?>" method="POST" action="<?= Helper::url('filter_url/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?= $aliasUrl ?>[ID]" value="<?= $dataUrl['ID'] ?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $aliasUrl ?>_ACTIVE_<?= $_suffix ?>" class="adm-designed-checkbox" type="checkbox"
                   name="<?= $aliasUrl ?>[ACTIVE]"
                   <?= $dataUrl['ACTIVE'] !== 'N' ? 'checked' : '' ?>  value="Y">
            <label class="adm-designed-checkbox-label" for="<?= $aliasUrl ?>_ACTIVE_<?= $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>"></label>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SECTION') ?>:</td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <?= $dataUrl['SECTION_NAME'] . ' [' . $dataUrl['SECTION_ID'] . ']' ?>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l adm-detail-content-cell-l--align-top"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_PROPERTIES') ?>:</td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <? foreach ($dataUrl['PROPERTIES'] as $property) : ?>
                <? if (!$property['VALUES']) : ?>
                    <? continue; ?>
                <? endif ?>
                <div class="aspro-smartseo__condition__item aspro-smartseo__condition__item--sm">
                  <div class="aspro-smartseo__condition__name">
                    <?= $property['PROPERTY_NAME'] ?> [<?= $property['PROPERTY_ID'] ?>]
                  </div>
                  <div class="aspro-smartseo__condition__value">
                    <div class="aspro-smartseo__condition__logic-group__values">
                      <? if ($property['PROPERTY_TYPE'] == 'PRICE' || $property['PROPERTY_TYPE'] == 'N') : ?>
                          <? if (isset($property['VALUES']['DISPLAY']['MIN'])) : ?>
                              <span class="aspro-smartseo__condition__logic-group__label"><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EGR') ?></span>
                              <span><?= $property['VALUES']['DISPLAY']['MIN'] ?></span>
                          <? endif ?>
                          <? if (isset($property['VALUES']['DISPLAY']['MAX'])) : ?>
                              <span class="aspro-smartseo__condition__logic-group__label"><?= Loc::getMessage('SMARTSEO_FORM_LABEL_ELS') ?></span>
                              <span><?= $property['VALUES']['DISPLAY']['MAX'] ?></span>
                          <? endif ?>
                      <? else : ?>
                          <span class="aspro-smartseo__condition__logic-group__label"><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EQUALLY') ?></span>
                          <span><?= implode(', ', $property['VALUES']['DISPLAY']) ?></span>
                      <? endif ?>
                    </div>
                  </div>
                </div>
            <? endforeach ?>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l adm-detail-content-cell-l--align-top"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_REAL_URL') ?>:</td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <?= $dataUrl['REAL_URL'] ?>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l adm-detail-content-cell-l--align-top">
            <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_NEW_URL') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control aspro-smartseo__form-control--w100">
            <textarea class="aspro-smartseo__form-control__textarea" type="text" cols="55" rows="1" name="<?= $aliasUrl ?>[NEW_URL]"><?= $dataUrl['NEW_URL'] ?></textarea>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>