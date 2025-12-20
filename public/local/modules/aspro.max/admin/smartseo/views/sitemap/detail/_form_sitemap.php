<?php

/**
 *  @var array $dataSitemap
 *  @var array $dataSite
 *  @var string $defaultProtocol
 *  @var string $defaultSitemapFile
 *  @var string $defaultMainSitemapFile
 */
use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;
?>
<form id="form_sitemap" method="POST" action="<?= Helper::url('sitemap_detail/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-GENERATE" type="hidden" name="generate" value="N">
  <input type="hidden" name="<?= $alias ?>[ID]" value="<?= $dataSitemap['ID'] ?>">
  <input type="hidden" name="<?= $alias ?>[SITE_ID]" value="<?= $dataSite['LID'] ?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_NAME') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input type="text" name="<?= $alias ?>[NAME]" value="<?= htmlspecialchars($dataSitemap['NAME']) ?>"
                maxlength="255" class="aspro-smartseo__form-control__input">
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SITE_ADDRESS') ?>:</b> </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control aspro-smartseo__form-control--site-address">
            <select name="<?= $alias ?>[PROTOCOL]" class="aspro-smartseo__form-control__select">
              <? foreach (['http://', 'https://'] as $protocol) : ?>
                  <?
                  $_selected = false;

                  if ($dataSitemap['PROTOCOL'] && $protocol == $dataSitemap['PROTOCOL']) {
                      $_selected = true;
                  }

                  if (!$dataSitemap['PROTOCOL'] && $protocol == $defaultProtocol) {
                      $_selected = true;
                  }
                  ?>

                  <option <?= $_selected ? 'selected' : '' ?> value="<?= $protocol ?>">
                    <?= $protocol ?>
                  </option>
              <? endforeach ?>
            </select>
            <input type="text" name="<?= $alias ?>[DOMAIN]" value="<?= $dataSitemap['DOMAIN'] ?: $dataSite['SERVER_NAME'] ?>"
                maxlength="255" class="aspro-smartseo__form-control__input">
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SITEMAP_FILE') ?>:</b>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control aspro-smartseo__form-control--file-path">
            <span><?= $dataSite['DIR'] ?></span>
            <input type="text" name="<?= $alias ?>[SITEMAP_FILE]" value="<?=
            $dataSitemap['SITEMAP_FILE'] ? $dataSitemap['SITEMAP_FILE'] : $defaultSitemapFile
            ?>"
                maxlength="255" class="aspro-smartseo__form-control__input">
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="2"><div class="aspro-smartseo__form-detail__separator"></div></td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <div class="aspro-smartseo__form-control__hint" data-ext="hint">
            <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IN_INDEX_SITEMAP') ?>:
            <span data-hint="<?= Loc::getMessage('SMARTSEO_HINT_IN_INDEX_SITEMAP') ?>"></span>
          </div>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input data-field="in_index_sitemap" id="<?= $alias ?>_IN_INDEX_SITEMAP" class="adm-designed-checkbox"
                type="checkbox" name="<?= $alias ?>[IN_INDEX_SITEMAP]" <?=
                $dataSitemap['IN_INDEX_SITEMAP'] === 'Y' ? 'checked' : ''
                ?>
                value="Y" >
            <label class="adm-designed-checkbox-label" for="<?= $alias ?>_IN_INDEX_SITEMAP" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IN_INDEX_SITEMAP') ?>">
          </div>
        </td>
      </tr>
      <tr page-role="index-sitemap-file-wrapper" style="<?= $dataSitemap['IN_INDEX_SITEMAP'] === 'Y' ? 'display: table-row;' : 'display: none;'?>">
        <td width="40%" class="adm-detail-content-cell-l"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_INDEX_SITEMAP_FILE') ?>:</b> </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control aspro-smartseo__form-control--file-path">
            <span><?= $dataSite['DIR'] ?></span>
            <input data-field="index_sitemap_file" required <?= $dataSitemap['IN_INDEX_SITEMAP'] === 'Y' ? '' : 'disabled' ?>
                type="text" name="<?= $alias ?>[INDEX_SITEMAP_FILE]"
                value="<?= $dataSitemap['INDEX_SITEMAP_FILE'] ? $dataSitemap['INDEX_SITEMAP_FILE'] : $defaultMainSitemapFile ?>"
                maxlength="255" class="aspro-smartseo__form-control__input">
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <div class="aspro-smartseo__form-control__hint" data-ext="hint">
            <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IN_ROBOTS') ?>:
            <span data-hint="<?= Loc::getMessage('SMARTSEO_HINT_IN_ROBOTS') ?>"></span>
          </div>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $alias ?>_IN_ROBOTS" class="adm-designed-checkbox"
                type="checkbox" name="<?= $alias ?>[IN_ROBOTS]" <?= $dataSitemap['IN_ROBOTS'] === 'Y' ? 'checked' : '' ?>
                value="Y" >
            <label class="adm-designed-checkbox-label" for="<?= $alias ?>_IN_ROBOTS" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IN_ROBOTS') ?>"></label>
          </div>
        </td>
      </tr>

      <tr>
        <td colspan="2">
          <div class="aspro-smartseo__form-detail__separator">
            <div class="aspro-smartseo__form-detail__separator__legend">
              <span><?= Loc::getMessage('SMARTSEO_FORM_GROUP_SETTINGS') ?></span>
            </div>
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <div class="aspro-smartseo__form-control__checkbox-flex">
              <input id="<?= $alias ?>_UPDATE_SITEMAP_FILE" class="adm-designed-checkbox"
                type="checkbox" name="<?= $alias ?>[UPDATE_SITEMAP_FILE]" <?=
                $dataSitemap['UPDATE_SITEMAP_FILE'] !== 'N' ? 'checked' : ''
                ?>
                value="Y" >
              <label class="adm-designed-checkbox-label" for="<?= $alias ?>_UPDATE_SITEMAP_FILE" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_UPDATE_SITEMAP_FILE') ?>"></label>
              <div><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_UPDATE_SITEMAP_FILE_FIELD') ?></div>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <div class="aspro-smartseo__form-control__checkbox-flex">
            <input data-field="update_sitemap_index" id="<?= $alias ?>_UPDATE_SITEMAP_INDEX" class="adm-designed-checkbox"
                type="checkbox" name="<?= $alias ?>[UPDATE_SITEMAP_INDEX]"
                <?= $dataSitemap['UPDATE_SITEMAP_INDEX'] !== 'N' ? 'checked' : '' ?>
                <?= $dataSitemap['IN_INDEX_SITEMAP'] === 'Y' ? '' : 'disabled'?>
                value="Y" >
            <label class="adm-designed-checkbox-label" for="<?= $alias ?>_UPDATE_SITEMAP_INDEX" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_UPDATE_SITEMAP_INDEX') ?>"></label>
            <div><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_UPDATE_SITEMAP_INDEX_FIELD') ?></div>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>