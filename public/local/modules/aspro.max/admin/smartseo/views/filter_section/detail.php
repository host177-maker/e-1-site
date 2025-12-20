<?php
/**
 *  @var array $dataFilterSection
 *  @var array $chainSections
 *  @var int $parentSectionId
 *  @var array $listSections
 *  @var string $dataFormName
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

$APPLICATION->AddHeadScript('/bitrix/js/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/filter_section.js');

$pageTitle = Loc::getMessage('SMARTSEO_INDEX__TITLE__FILTER_RULE')
  . ': ' . Loc::getMessage('SMARTSEO_PAGE_TITLE')
  . ': ' . ($dataFilterSection['ID'] ? Loc::getMessage('SMARTSEO_ACTION_TYPE_EDIT') : Loc::getMessage('SMARTSEO_ACTION_TYPE_ADD'));

$APPLICATION->setTitle($pageTitle);

$adminContextMenu = new CAdminUiContextMenu(array_filter([
      [
          'TEXT' => Loc::getMessage('SMARTSEO_MENU_ADD'),
          'TITLE' => Loc::getMessage('SMARTSEO_MENU_DELETE'),
          'LINK' => Helper::url('filter_section/detail', ['parent_section_id' => $parentSectionId]),
          'ICON' => 'add',
      ],
      $dataFilterSection['ID'] ? [
        'TEXT' => Loc::getMessage('SMARTSEO_MENU_DELETE'),
        'TITLE' => Loc::getMessage('SMARTSEO_MENU_DELETE'),
        'LINK' => '',
        'ICON' => 'delete',
        'ONCLICK' => "contextMenuHandler.delete($dataFilterSection[ID])"
      ] : []
  ]));

$adminTabControl = new CAdminTabControl('section_tab_control', [
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_SECTION_NAME'),
        'ICON' => '',
        'TITLE' => $dataFilterSection['ID'] ? Loc::getMessage('SMARTSEO_TAB_SECTION_EDIT_TITLE') : Loc::getMessage('SMARTSEO_TAB_SECTION_ADD_TITLE'),
    ],
  ]);
?>

<div class="aspro-smartseo__form-detail">
  <div class="aspro-smartseo__form-detail__chain">
    <? include $this->getViewPath() . '_chain.php'; ?>
  </div>

  <div class="aspro-smartseo__form-detail__toolbar">
    <div class="aspro-smartseo__form-detail__col">
      <a href="<?= Helper::url('filter_rules/list', ['section_id' => $parentSectionId]) ?>" class="ui-btn ui-btn-light-border">
        <?= Loc::getMessage('SMARTSEO_ACTION_BACK_LIST') ?>
      </a>
    </div>
    <div class="aspro-smartseo__form-detail__col">
      <? $adminContextMenu->Show() ?>
    </div>
  </div>

  <form id="filter_section_form" method="POST" action="<?= Helper::url('filter_section/update') ?>" enctype="multipart/form-data" name="filter_section_form">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="<?= $dataFormName ?>[ID]" value="<?= $dataFilterSection['ID'] ?>">

    <div form-role="alert" class="ui-alert ui-alert-danger ui-alert-icon-danger aspro-ui-form__alert" style="display: none;">
      <span class="ui-alert-message" form-role="alert-body"></span>
    </div>

    <div class="aspro-smartseo__form-detail__body">
      <? $adminTabControl->Begin() ?>

      <? $adminTabControl->BeginNextTab() ?>

      <? if ($dataFilterSection['ID']) : ?>
          <tr>
            <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ID') ?>: </td>
            <td width="60%">
              <?= $dataFilterSection['ID'] ?>
            </td>
          </tr>
      <? endif ?>
      <? if ($dataFilterSection['DATE_CREATE']) : ?>
          <tr>
            <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_CREATE') ?>: </td>
            <td width="60%">
              <?= $dataFilterSection['DATE_CREATE']->toString() ?>
            </td>
          </tr>
      <? endif ?>
      <? if ($dataFilterSection['DATE_CHANGE']) : ?>
          <tr>
            <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DATE_CHANGE') ?>: </td>
            <td width="60%">
              <?= $dataFilterSection['DATE_CHANGE']->toString() ?>
            </td>
          </tr>
      <? endif ?>
      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_PARENT_ID') ?>: </td>
        <td width="60%">
          <select name="<?= $dataFormName ?>[PARENT_ID]" class="aspro-smartseo__form-control__select">
            <option value="0"><?= Loc::getMessage('SMARTSEO_FORM_SECTION_DEFAULT') ?></option>
            <? foreach ($listSections as $section) : ?>
                <option <?= $parentSectionId == $section['ID'] ? 'selected' : '' ?> value="<?= $section['ID'] ?>">
                  <?= str_repeat(' . ', (int) $section['DEPTH_LEVEL']) . $section['NAME'] ?>
                </option>
            <? endforeach ?>
          </select>
        </td>
      </tr>
      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SORT') ?>: </td>
        <td width="60%">
          <input type="text" name="<?= $dataFormName ?>[SORT]" size="7" maxlength="10" value="<?=
          $dataFilterSection['SORT'] ?: 500
          ?>">
        </td>
      </tr>
      <tr>
        <td width="40%"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_NAME') ?>:</b></td>
        <td width="60%">
          <input type="text" required name="<?= $dataFormName ?>[NAME]" value="<?= htmlspecialchars($dataFilterSection['NAME']) ?>" maxlength="255" class="aspro-smartseo__form-control__input">
        </td>
      </tr>
      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_DESCRIPTION') ?>:</td>
        <td width="60%">
          <textarea cols="55" rows="5" name="<?= $dataFormName ?>[DESCRIPTION]" class="aspro-smartseo__form-control__textarea"><?= $dataFilterSection['DESCRIPTION'] ?></textarea>
        </td>
      </tr>
      <? $adminTabControl->Buttons() ?>
      <div class="aspro-smartseo__form-detail__buttons">
        <button form-role="save" data-action="save" class="ui-btn ui-btn-success" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_SAVE') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_SAVE') ?>
        </button>
        <button form-role="apply" data-action="apply" class="ui-btn ui-btn-primary-dark" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_APPLY') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_APPLY') ?>
        </button>
        <a form-role="cancel" data-action="cancel" href="<?= Helper::url('filter_rules/list', ['section_id' => $parentSectionId]) ?>" class="ui-btn ui-btn-light" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_CANCEL') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_CANCEL') ?>
        </a>
      </div>
      <? $adminTabControl->End() ?>
    </div>
  </form>
</div>

<script>
    var phpObjectFilterSection = <?= CUtil::PhpToJSObject([
          'urls' => [
              'DELETE' => Helper::url('filter_section/delete', ['sessid' => bitrix_sessid()]),
          ],
          'messages' => [
              'popupWindow' => [
                  'BTN_DELETE' => Loc::getMessage('SMARTSEO_POPUP_BTN_DELETE'),
                  'BTN_CANCEL' => Loc::getMessage('SMARTSEO_POPUP_BTN_CANCEL'),
                  'MESSAGE_DELETE' => Loc::getMessage('SMARTSEO_POPUP_MESSAGE_DELETE'),
              ]
          ]
      ])
      ?>

      BX.message({
          SMARTSEO_POPUP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_DELETE') ?>',
          SMARTSEO_POPUP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_CANCEL') ?>',
          SMARTSEO_POPUP_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_MESSAGE_DELETE') ?>',
      });
</script>

