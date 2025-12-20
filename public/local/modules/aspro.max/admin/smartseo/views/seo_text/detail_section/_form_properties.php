<?php
/**
 * @var string $alias
 * @var string $aliasProperty
 * @var array $data
 * @var array $dataProperties
 * @var array $listSites
 * @var array $listIblockTypes
 * @var array $listIblocks
 * @var array $listIblockSections
 */
use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;

\Bitrix\Main\Loader::includeModule('fileman');

?>
<form id="form_seo_text_properties" method="POST">
  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td page-role="property-container" width="100%">
          <? foreach ($dataProperties as $field) : ?>
          <?
            $_attr = '';
            if($field['ENTITY_ID']) {
                $_attr = 'page-role="section-property" data-id="'  . $field['ENTITY_ID'] . '" data-iblock="' . $data['IBLOCK_ID'] . '"';
            }
          ?>
          <div <?= $_attr ?> class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
            <div page-role="control-engine-template" data-id="<?= $field['ENTITY_ID'] ?>">
              <div class="aspro-smartseo__form-control__label">
                <span><?= $field['NAME'] ?>:</span>
                <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu aspro-smartseo__form-control__menu--textarea"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
              </div>
              <div control-role="input-wrapper">
                  <?
                  \CFileMan::AddHTMLEditorFrame(
                    $field['CODE'] . '_property', $field['TEXT'], false, 'html', [
                      'height' => 120,
                      'width' => '100%'
                    ], 'N', 0, '', 'control-role="input"'
                  )
                  ?>
              </div>
              <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $field['SAMPLE'] ?></div>
            </div>
          </div>
          <? endforeach; ?>
        </td>
      </tr>
      <tr>
        <td width="100%">
          <div class="aspro-smartseo__form-detail__wrapper-btn-more">
            <div>
              <button page-role="property-action-add" type="button" class="ui-btn ui-btn-xs ui-btn-icon-add"><?= Loc::getMessage('SMARTSEO_BTN_ADD_PROPERTY') ?></button>

            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>