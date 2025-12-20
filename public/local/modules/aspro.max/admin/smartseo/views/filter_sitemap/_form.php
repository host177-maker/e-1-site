<?php
/**
 *  @var string $alias
 *  @var array $data
 *  @var array $listSitemap
 *  @var array $listFilterCondition
 *  @var array $listChangefreq
 *  @var array $listPriority
 *  @var string $defaultChangefreq
 *  @var float $defaultPriority
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$_suffix = $this->getUnique();
?>
<form id="form_sitemap_<?= $_suffix ?>" method="POST" action="<?= Helper::url('filter_sitemap/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?= $alias ?>[ID]" value="<?= $data['ID'] ?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $alias ?>_ACTIVE_<?= $_suffix ?>" class="adm-designed-checkbox" type="checkbox"
                   name="<?= $alias ?>[ACTIVE]"
                   <?= $data['ACTIVE'] !== 'N' ? 'checked' : '' ?>  value="Y">
            <label class="adm-designed-checkbox-label" for="<?= $alias ?>_ACTIVE_<?= $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>"></label>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SITEMAP') ?>:</b></td>
        <td width="60%" class="adm-detail-content-cell-r">
          <select page-role="form-field-SITEMAP" required name="<?= $alias ?>[SITEMAP_ID]" class="aspro-smartseo__form-control__select">
              <option value="0"><?= Loc::getMessage('SMARTSEO_FORM_VALUE_NEW_SITEMAP') ?></option>
              <? foreach ($listSitemap as $key => $value) : ?>
                <?
                    $_selected = $data['SITEMAP_ID'] == $key;
                ?>
                <option <?= $_selected ? 'selected' : '' ?>  value="<?= $key ?>">
                  <?=
                      $value
                      ? '[' . $key . '] ' . $value
                      : Loc::getMessage('SMARTSEO_FORM_VALUE_SITEMAP_DEFAULT', ['#ID#' => $key]) ?>
                </option>
              <? endforeach ?>
           </select>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CONDITION') ?>:</b></td>
        <td width="60%" class="adm-detail-content-cell-r">
          <select required name="<?= $alias ?>[FILTER_CONDITION_ID]" class="aspro-smartseo__form-control__select">
              <option disabled <?= !$data ? 'selected' : '' ?>><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_CONDITION') ?></option>
              <? foreach ($listFilterCondition as $key => $value) : ?>
                <?
                    $_selected = $data['FILTER_CONDITION_ID'] == $key;
                ?>
                <option <?= $_selected ? 'selected' : '' ?> value="<?= $key ?>">
                  <?=
                      $value
                      ? '[' . $key . '] ' . $value
                      : Loc::getMessage('SMARTSEO_FORM_VALUE_CONDITION_DEFAULT', ['#ID#' => $key]) ?>
                </option>
              <? endforeach ?>
           </select>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CHANGEFREQ') ?>:</b></td>
        <td width="60%" class="adm-detail-content-cell-r">
          <select required name="<?= $alias ?>[CHANGEFREQ]" class="aspro-smartseo__form-control__select aspro-smartseo__form-control__select--w50">
              <? foreach ($listChangefreq as $key => $value) : ?>
                <?
                    $_selected = false;

                    if(!$data && $value == $defaultChangefreq) {
                        $_selected = true;
                    } else {
                        $_selected = $data['CHANGEFREQ'] == $key;
                    }
                ?>
                <option <?= $_selected ? 'selected' : '' ?> value="<?= $key ?>">
                  <?= $value ?>
                </option>
              <? endforeach ?>
           </select>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_PRIORITY') ?>:</b></td>
        <td width="60%" class="adm-detail-content-cell-r">
          <select required name="<?= $alias ?>[PRIORITY]" class="aspro-smartseo__form-control__select aspro-smartseo__form-control__select--w50">
              <? foreach ($listPriority as $key => $value) : ?>
                <?
                    $_selected = false;

                    if(!$data && $value == $defaultPriority) {
                        $_selected = true;
                    } else {
                        $_selected = $data['PRIORITY'] == $key;
                    }
                ?>
                <option <?= $_selected ? 'selected' : '' ?> value="<?= $key ?>">
                  <?= $value ?>
                </option>
              <? endforeach ?>
           </select>
        </td>
      </tr>
    </tbody>
  </table>
</form>