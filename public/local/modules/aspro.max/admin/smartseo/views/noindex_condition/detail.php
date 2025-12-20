<?php
/**
 * @var string $alias
 * @var array $data
 * @var inr $noindexRuleId
 * @var string $gridConditionId
 * @var array $listConditionTypes
 * @var array $listProperties
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

Loc::loadMessages(__FILE__);

$_suffix = $this->getUnique();

?>
<div id="noindex_condition_detail_page_<?= $_suffix ?>" class="aspro-smartseo__form-detail aspro-smartseo__form-detail--inner aspro-ui--animate-fade-in">

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
        <button page-role="save" data-action="save" class="ui-btn ui-btn-sm ui-btn-success" title="<?= Loc::getMessage('SMARTSEO_AI__HINT__SAVE') ?>">
          <?= Loc::getMessage('SMARTSEO_AI__BTN__SAVE') ?>
        </button>
        <button page-role="apply" data-action="apply" class="ui-btn ui-btn-sm ui-btn-primary-dark" title="<?= Loc::getMessage('SMARTSEO_AI__HINT__APPLY') ?>">
          <?= Loc::getMessage('SMARTSEO_AI__BTN__APPLY') ?>
        </button>
        <a page-role="cancel" data-action="cancel" href="" class="ui-btn ui-btn-sm ui-btn-light" title="<?= Loc::getMessage('SMARTSEO_AI__HINT__CANCEL') ?>">
          <?= Loc::getMessage('SMARTSEO_AI__BTN__CANCEL') ?>
        </a>
      </div>
      <div class="aspro-smartseo__form-detail__footer__info">
        ID: <span page-role="form-label-ID"><?= $data['ID'] ?: '---' ?></span>
      </div>
    </div>
  </div>
</div>

<script>
    BX.ajax.loadScriptAjax('<?= $this->getPathSelfScripts() ?>/detail.js', function ()
    {
      new NoindexConditionDetailPage('noindex_condition_detail_page_<?= $_suffix ?>', <?=
          CUtil::PhpToJSObject([
              'alias' => $alias,
              'selectors' => [
                  'PARENT_GRID_ID' => $gridConditionId,
                  'PARENT_TAB_CONTROL' => 'tabs_noindex_rule_condition',
                  'FORM' => '#form_noindex_condition_' . $_suffix,
              ],
          ])
          ?>);
   });

    BX.message({
        SMARTSEO_POPUP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_AI__BTN__DELETE') ?>',
        SMARTSEO_POPUP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_AI__BTN__CANCEL') ?>',
        SMARTSEO_POPUP_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_AI__BTN__CLOSE') ?>',
        SMARTSEO_POPUP_MESSAGE_SUCCESS: '<?= Loc::getMessage('SMARTSEO_AI__MESSAGE__SAVE_SUCCESS') ?>',
        SMARTSEO_ELEMENT_DEFAULT_NAME: '<?= Loc::getMessage('SMARTSEO__ELEMENT__DEFAULT_NAME') ?>'
    });
</script>