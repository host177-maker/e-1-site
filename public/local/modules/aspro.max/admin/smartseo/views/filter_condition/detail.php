<?php
/**
 *  @var string $aliasCondition
 *  @var string $aliasSeo
 *  @var array $dataCondition
 *  @var array $dataSeo
 *  @var array $dataSitemap
 *  @var int $filterRuleId
 *  @var int $filterRuleIblockId
 *
 *  @var string $gridConditionId
 *  @var string $gridUrlId
 *
 *  @var array $listTypeGenerate
 *  @var array $listSitemap
 *  @var array $listChangefreq
 *  @var array $listPriority
 *  @var string $defaultChangefreq
 *  @var float $defaultPriority
 *
 *  @var bollean $isCatalogModule
 */
use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

Loc::loadMessages(__FILE__);

$_suffix = $this->getUnique();
?>
<div id="filter_condition_detail_page_<?= $_suffix ?>" class="aspro-smartseo__form-detail aspro-smartseo__form-detail--inner aspro-ui--animate-fade-in">

  <div page-role="alert" style="display: none;">
    <div class="ui-alert ui-alert-danger ui-alert-icon-danger aspro-ui-form__alert">
      <span class="ui-alert-message" form-role="alert-body"></span>
    </div>
  </div>

  <div class="adm-detail-content-item-block">
    <div>
      <? include $this->getViewPath() . '_form_condition.php'; ?>
    </div>
    <div class="aspro-smartseo__form-detail__group-title">
      <?= Loc::getMessage('SMARTSEO_FORM_GROUP_SEO') ?>
    </div>
    <div>
      <? include $this->getViewPath() . '_form_condition_seo.php'; ?>
    </div>
  </div>
  <div page-role="button-panel" class="aspro-smartseo__form-detail__wrapper-footer">
    <div class="aspro-smartseo__form-detail__footer__toolbar">
      <div class="aspro-smartseo__form-detail__buttons">
        <button page-role="save" data-action="save" class="ui-btn ui-btn-sm ui-btn-success" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_SAVE') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_SAVE') ?>
        </button>
        <button page-role="apply" data-action="apply" class="ui-btn ui-btn-sm ui-btn-primary-dark" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_APPLY') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_APPLY') ?>
        </button>
        <a page-role="cancel" data-action="cancel" href="" class="ui-btn ui-btn-sm ui-btn-light" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_CANCEL') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_CANCEL') ?>
        </a>
        <div class="aspro-smartseo__form-detail__footer__checkbox">
            <div class="aspro-smartseo__form-control">
              <input page-role="apply-generate-url" id="checkbox_apply_generate_url_<?= $_suffix ?>" class="adm-designed-checkbox" type="checkbox">
              <label class="adm-designed-checkbox-label" for="checkbox_apply_generate_url_<?= $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_CHECKBOX_GENERATE_URLS') ?>">
                <?= Loc::getMessage('SMARTSEO_FORM_CHECKBOX_GENERATE_URLS') ?>
              </label>
            </div>
        </div>
      </div>
      <div class="aspro-smartseo__form-detail__footer__info">
        ID: <span page-role="form-label-ID"><?= $dataCondition['ID'] ?: '---' ?></span>
      </div>
    </div>
  </div>
</div>

<script>
    BX.ajax.loadScriptAjax('<?= $this->getPathModuleScripts() ?>/filter_condition_detail.js', function ()
    {
      new FilterConditionDetailPage('filter_condition_detail_page_<?= $_suffix ?>', <?=
          CUtil::PhpToJSObject([
              'aliasSeo' => $aliasSeo,
              'selectors' => [
                  'PARENT_GRID_ID' => $gridConditionId,
                  'GRID_URL_ID' => $gridUrlId,
                  'PARENT_TAB_CONTROL' => 'tabs_conditions',
                  'FORM_CONDITION' => '#form_condition_' . $_suffix,
                  'FORM_SEO_PROPERTY' => '#form_seo_property_filter_condition_' . $_suffix,
              ],
              'urls' => [
                  'MENU_SEO_PROPERTY' => Helper::url('filter_condition/get_menu_seo_property', ['filter_rule' => $filterRuleId]),
                  'MENU_URL_PROPERTY' => Helper::url('filter_condition/get_menu_url_property', ['filter_rule' => $filterRuleId]),
                  'SAMPLE_SEO_PROPERTY' => Helper::url('filter_condition/get_sample_seo_property', ['filter_rule' => $filterRuleId]),
              ],
          ])
          ?>);
    });

    BX.message({
        SMARTSEO_MESSAGE_SAVE_SUCCESS: '<?= Loc::getMessage('SMARTSEO_MESSAGE_SAVE_SUCCESS') ?>',
        SMARTSEO_DEFAULT_TAB_NAME: '<?= Loc::getMessage('SMARTSEO_DEFAULT_TAB_NAME') ?>',
        SMARTSEO_MESSAGE_COUNT_CREATED_LINKS: '<?= Loc::getMessage('SMARTSEO_MESSAGE_COUNT_CREATED_LINKS') ?>',
    });
</script>