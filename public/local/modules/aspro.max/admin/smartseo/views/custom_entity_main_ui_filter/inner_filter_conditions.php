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
          <?
          $item['NAME'] = $item['NAME']
            ? '[' . $item['ID'] . '] ' . $item['NAME']
            : Loc::getMEssage('SMARTSEO_CUSTOM_ENTITY_FILTER_CONDITION_DEFAUL', [
                '#ID#' => $item['ID'],
            ])
          ?>
          <div
            custom-entity="item"
            data-id="<?= $item['ID'] ?>"
            data-name="<?= $item['NAME'] ?>"
            class="main-ui-select-inner-item">
            <label class="main-ui-select-inner-label">
              <?= $item['NAME'] ?>
            </label>
          </div>
      <? endforeach; ?>
  <? endif ?>
</div>