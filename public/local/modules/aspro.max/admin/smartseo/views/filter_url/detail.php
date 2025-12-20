<?php
/**
 *  @var string $aliasUrl
 *  @var string $aliasSeo
 *  @var array $dataUrl
 *  @var array $dataSeo
 *  @var string $gridId
 *  @var int $filterRuleId
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

Loc::loadMessages(__FILE__);

$_suffix = $this->getUnique();

?>
<div id="filter_url_detail_page_<?= $_suffix ?>" class="aspro-smartseo__form-detail aspro-smartseo__form-detail--inner aspro-ui--animate-fade-in">

  <div page-role="alert" style="display: none;">
    <div class="ui-alert ui-alert-danger ui-alert-icon-danger aspro-ui-form__alert">
      <span class="ui-alert-message" form-role="alert-body"></span>
    </div>
  </div>

  <div class="adm-detail-content-item-block">
    <div>
      <? include $this->getViewPath() . '_form.php'; ?>
    </div>
    <div class="aspro-smartseo__form-detail__group-title">
      <?= Loc::getMessage('SMARTSEO_FORM_GROUP_SEO') ?>
    </div>
    <div>
      <? include $this->getViewPath() . '_form_seo.php'; ?>
    </div>
  </div>
  <div page-role="button-panel" class="aspro-smartseo__form-detail__wrapper-footer">
    <div class="aspro-smartseo__form-detail__footer__toolbar">
      <div class="aspro-smartseo__form-detail__buttons">
        <button page-role="save" data-action="save" class="ui-btn ui-btn-sm ui-btn-success" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_SAVE') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_SAVE') ?>
        </button>
        <a page-role="cancel" data-action="cancel" href="" class="ui-btn ui-btn-sm ui-btn-light" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_CANCEL') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_CANCEL') ?>
        </a>
      </div>
      <div class="aspro-smartseo__form-detail__footer__info">
        ID: <span page-role="form-label-ID"><?= $dataUrl['ID'] ?: '---' ?></span>
      </div>
    </div>
  </div>
</div>

<script>
    BX.ajax.loadScriptAjax('<?= $this->getPathModuleScripts() ?>/filter_url_detail.js', function ()
    {
      new FilterUrlDetailPage('filter_url_detail_page_<?= $_suffix ?>', <?=
          CUtil::PhpToJSObject([
              'dataUrl' => $dataUrl,
              'aliasSeo' => $aliasSeo,
              'alias' => $aliasUrl,
              'selectors' => [
                  'PARENT_GRID_ID' => $gridId,
                  'PARENT_TAB_CONTROL' => 'tabs_urls',
                  'FORM' => '#form_url_' . $_suffix,
                  'FORM_SEO' => '#form_url_seo_' . $_suffix,
              ],
              'urls' => [
                  'MENU_SEO_PROPERTY' => Helper::url('filter_url/get_menu_seo_property', ['filter_rule' => $filterRuleId]),
                  'SAMPLE_SEO_PROPERTY' => Helper::url('filter_url/get_sample_seo_property', ['filter_rule' => $filterRuleId]),
              ],
          ])
          ?>);
    });

    BX.message({
        SMARTSEO_MESSAGE_SAVE_SUCCESS: '<?= Loc::getMessage('SMARTSEO_MESSAGE_SAVE_SUCCESS') ?>',
        SMARTSEO_DEFAULT_TAB_NAME: '',
    });
</script>