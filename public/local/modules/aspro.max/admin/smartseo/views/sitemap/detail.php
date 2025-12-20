<?php
/**
 *  @var string $alias
 *  @var array  $dataSitemap
 *  @var array $dataSite
 *  @var string $defaultProtocol
 *  @var string $defaultSitemapFile
 *  @var string $defaultMainSitemapFile
 */
use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

\Bitrix\Main\UI\Extension::load('ui.notification');
\Bitrix\Main\UI\Extension::load('ui.hint');

$APPLICATION->AddHeadScript('/bitrix/js/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/src/core_custom.js');

$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/detail.js');
$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/tab_conditions.js');

$elementTitle = '';

if($dataSitemap['ID'] && $dataSitemap['NAME']) {
    $elementTitle = htmlspecialchars($dataSitemap['NAME']);
}
if($dataSitemap['ID'] && !$dataSitemap['NAME']) {
    $elementTitle = Loc::getMessage('SMARTSEO_ELEMENT_DEFAULT_NAME', [
        '#ID#' => $dataSitemap['ID'],
    ]);
}

$pageTitle = Loc::getMessage('SMARTSEO_PAGE_TITLE')
  . ($dataSitemap['ID'] ? ': ' . $elementTitle : '')
  . ': ' . ($dataSitemap['ID'] ? Loc::getMessage('SMARTSEO_ACTION_TYPE_EDIT') : Loc::getMessage('SMARTSEO_ACTION_TYPE_ADD'));


$APPLICATION->setTitle($pageTitle);

$adminContextMenu = new CAdminUiContextMenu(array_filter([
    [
        'TEXT' => Loc::getMessage('SMARTSEO_MENU_ADD'),
        'TITLE' => Loc::getMessage('SMARTSEO_MENU_DELETE'),
        'LINK' => Helper::url('sitemap_detail/detail', ['site_id' => $dataSite['LID']]),
        'ICON' => 'edit',
    ],
    $dataSitemap ? [
        'TEXT' => Loc::getMessage('SMARTSEO_MENU_DELETE'),
        'TITLE' => Loc::getMessage('SMARTSEO_MENU_DELETE'),
        'LINK' => '',
        'ICON' => 'delete',
        'ONCLICK' => "contextMenuHandler.delete($dataSitemap[ID])"
    ] : [],
  ]));

$adminTabControl = new CAdminTabControl('sitemap_tab_control', [
    [
        'DIV' => 'sitemap_main',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_GENERAL_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_GENERAL_TITLE'),
        'ICON' => '',
    ],
    [
        'DIV' => 'sitemap_condition',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_CONDITION_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_CONDITION_TITLE'),
        'ICON' => '',
    ],
  ], true, true);
?>
<div id="sitemap_detail" class="aspro-smartseo__form-detail">
  <div class="aspro-smartseo__form-detail__toolbar">
    <div class="aspro-smartseo__form-detail__col">
      <a href="<?= Helper::url('sitemap/list') ?>" class="ui-btn ui-btn-light-border">
        <?= Loc::getMessage('SMARTSEO_ACTION_BACK_LIST') ?>
      </a>
    </div>
    <div class="aspro-smartseo__form-detail__col">
      <? $adminContextMenu->Show() ?>
    </div>
  </div>

  <? include $this->getViewPath() . 'detail/_top_info.php'; ?>

  <div page-role="alert" class="ui-alert ui-alert-danger ui-alert-icon-danger aspro-ui-form__alert" style="display: none;">
    <span class="ui-alert-message" form-role="alert-body"></span>
  </div>

  <div class="aspro-smartseo__form-detail__body">
    <? $adminTabControl->Begin() ?>

    <? $adminTabControl->BeginNextTab() ?>

    <tr>
      <td colspan="2">
        <? include $this->getViewPath() . 'detail/_form_sitemap.php'; ?>
      </td>
    </tr>

    <? $adminTabControl->BeginNextTab() ?>

    <? if($dataSitemap) : ?>
        <tr>
          <td colspan="2">
            <? include $this->getViewPath() . 'detail/grids/grid_sitemap_conditions.php'; ?>
          </td>
        </tr>
    <? endif ?>

    <? $adminTabControl->Buttons() ?>
    <div class="aspro-smartseo__form-detail__buttons">
      <button page-role="save" data-action="save" class="ui-btn ui-btn-success" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_SAVE') ?>">
        <?= Loc::getMessage('SMARTSEO_FORM_BTN_SAVE') ?>
      </button>
      <button page-role="apply" data-action="apply" class="ui-btn ui-btn-primary-dark" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_APPLY') ?>">
        <?= Loc::getMessage('SMARTSEO_FORM_BTN_APPLY') ?>
      </button>
      <a page-role="cancel" data-action="cancel" href="<?= Helper::url('sitemap/list') ?>" class="ui-btn ui-btn-light" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_CANCEL') ?>">
        <?= Loc::getMessage('SMARTSEO_FORM_BTN_CANCEL') ?>
      </a>
      <div class="aspro-smartseo__form-detail__footer__checkbox">
        <div class="aspro-smartseo__form-control">
          <input page-role="apply-generate-sitemap" id="checkbox_apply_generate_sitemap" class="adm-designed-checkbox" type="checkbox">
          <label class="adm-designed-checkbox-label" for="checkbox_apply_generate_sitemap" title="<?= Loc::getMessage('SMARTSEO_FORM_CHECKBOX_GENERATE_ADD_SITEMAP') ?>">
            <?= $dataSitemap['SITEMAP_URL']
                ? Loc::getMessage('SMARTSEO_FORM_CHECKBOX_GENERATE_UPDATE_SITEMAP')
                : Loc::getMessage('SMARTSEO_FORM_CHECKBOX_GENERATE_ADD_SITEMAP')
            ?>
          </label>
        </div>
      </div>
    </div>

    <? $adminTabControl->End() ?>
  </div>
</div>
<script>
    var phpObjectSitemap = <?= CUtil::PhpToJSObject([
        'urls' => [
            'DELETE' => Helper::url('sitemap_detail/delete', ['sessid' => bitrix_sessid()]),
         ],
        'dataSitemap' => $dataSitemap,
        'alias' => $alias,
    ]) ?>;

   BX.message({
        SMARTSEO_POPUP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_DELETE') ?>',
        SMARTSEO_POPUP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_MESSAGE_DELETE') ?>',
    });
</script>
