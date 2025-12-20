<?php

/**
 *  @var array $list
 */
use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;
?>

<div id="popup_filter_custom_entity">
  <? if (!$list) : ?>
      <div style="text-align: center; margin-top: 6px;">
        <?= Loc::getMessage('SMARTSEO_INDEX__NOT_DATA') ?>
      </div>
  <? else : ?>
      <? foreach ($list as $item) : ?>
          <div
            custom-entity="item"
            data-id="<?= $item['ITEM_ID'] ?>"
            data-name="<?= $item['ITEM_NAME'] ?>"
            class="main-ui-select-inner-item">
            <label class="main-ui-select-inner-label">
              <?= $item['ITEM_NAME'] ?>
            </label>
          </div>
      <? endforeach; ?>
  <? endif ?>
</div>