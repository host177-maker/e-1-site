<?php
/**
 *  @var string $alias
 *  @var array $data
 *  @var array $dataSample
 *  @var string $gridId
 *  @var int $filterRuleId
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

Loc::loadMessages(__FILE__);

$_suffix = $this->getUnique();

?>
<div id="filter_search_detail_page_<?= $_suffix ?>" class="aspro-smartseo__form-detail aspro-smartseo__form-detail--inner aspro-ui--animate-fade-in">

  <div page-role="alert" style="display: none;">
    <div class="ui-alert ui-alert-danger ui-alert-icon-danger aspro-ui-form__alert">
      <span class="ui-alert-message" form-role="alert-body"></span>
    </div>
  </div>

  <div class="adm-detail-content-item-block">
    <div>
      <? include $this->getViewPath() . '_form.php'; ?>
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
        <div class="aspro-smartseo__form-detail__footer__checkbox">
          <div class="aspro-smartseo__form-control">
            <input page-role="apply-reindex" id="checkbox_apply_reindex_<?= $_suffix ?>" class="adm-designed-checkbox" type="checkbox">
            <label class="adm-designed-checkbox-label" for="checkbox_apply_reindex_<?= $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_CHECKBOX_REINDEX') ?>">
              <?= Loc::getMessage('SMARTSEO_FORM_CHECKBOX_REINDEX') ?>
            </label>
          </div>
        </div>
      </div>
      <div class="aspro-smartseo__form-detail__footer__info">
        ID: <span page-role="form-label-ID"><?= $data['ID'] ?: '---' ?></span>
      </div>
    </div>
  </div>
</div>

<script>
    BX.ajax.loadScriptAjax('<?= $this->getPathModuleScripts() ?>/filter_search_detail.js', function ()
    {
      new FilterSearchDetailPage('filter_search_detail_page_<?= $_suffix ?>', <?=
          CUtil::PhpToJSObject([
              'data' => $data,
              'alias' => $alias,
              'selectors' => [
                  'PARENT_GRID_ID' => $gridId,
                  'PARENT_TAB_CONTROL' => 'tabs_search',
                  'FORM' => '#form_search_' . $_suffix,
              ],
              'urls' => [
                  'MENU_SEO_PROPERTY' => Helper::url('filter_search/get_menu_seo_property', ['filter_rule' => $filterRuleId]),
                  'SAMPLE_SEO_PROPERTY' => Helper::url('filter_search/get_sample_seo_property', ['filter_rule' => $filterRuleId]),
              ],
          ])
          ?>);
    });

    BX.message({
        SMARTSEO_MESSAGE_SAVE_SUCCESS: '<?= Loc::getMessage('SMARTSEO_MESSAGE_SAVE_SUCCESS') ?>',
        SMARTSEO_MESSAGE_REINDEX_SUCCESS: '<?= Loc::getMessage('SMARTSEO_MESSAGE_REINDEX_SUCCESS') ?>',
        SMARTSEO_DEFAULT_TAB_NAME: '<?= Loc::getMessage('SMARTSEO_FORM_VALUE_CONDITION_DEFAULT') ?>',
    });
</script>