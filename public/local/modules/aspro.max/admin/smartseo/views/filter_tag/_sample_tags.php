<?php

/**
 *  @var array $listTags
 */
use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<? if ($listTags) : ?>
    <style>
      .aspro-smartseo__exemple-tags {
        display: flex;
        flex-wrap: wrap;
        margin-top: 4px;
        margin-left: -4px;
        margin-right: -4px;
      }
      .aspro-smartseo__exemple-tags .ui-alert {
        width: auto !important;
        padding: 4px 6px !important;
        margin: 4px;
      }
      .aspro-smartseo__exemple-tags__label  {
        display: inline-block;
        padding-top: 8px;
        background-color: #f5f9f9;
        color: #4b6267;
        font-size: 11px;
      }
    </style>

    <div class="aspro-smartseo__exemple-tags__label">
      <?= loc::getMessage('SMARTSEO_FORM_SAMPLE_EXAMPLE_LABEL') ?>:
    </div>
    <div class="aspro-smartseo__exemple-tags">
      <? foreach ($listTags as $tag) : ?>
          <div class="ui-alert ui-alert-primary ui-alert-xs">
            <span class="ui-alert-message"><?= htmlspecialcharsback($tag) ?></span>
          </div>
      <? endforeach ?>
    </div>
<? endif ?>