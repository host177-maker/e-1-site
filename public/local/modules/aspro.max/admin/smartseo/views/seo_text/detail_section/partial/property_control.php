<?php
/**
 *  @var array $property
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;

\Bitrix\Main\Loader::includeModule('fileman');

$_suffix = $this->getUnique();

?>
<div page-role="section-property" data-id="<?= $property['ID'] ?>" data-iblock="<?= $property['IBLOCK_ID'] ?>" class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100 aspro-ui--animate-fade-in">
    <div page-role="control-engine-template">
      <div class="aspro-smartseo__form-control__label">
        <span><?= $property['NAME'] ?>:</span>
        <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu aspro-smartseo__form-control__menu--textarea"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
      </div>
      <div control-role="input-wrapper">
          <?
            \CFileMan::AddHTMLEditorFrame(
              $property['CODE'] . '_property_' . $_suffix, '', false, 'html', [
                'height' => 120,
                'width' => '100%'
              ], 'N', 0, '', 'control-role="input"'
            )
            ?>
      </div>
      <div control-role="sample" class="aspro-smartseo__form-control__note"></div>
    </div>
</div>