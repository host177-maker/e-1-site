<?php
/**
 *  @var int $filterRuleId
 *  @var array $row
 */

use Bitrix\Main\Localization\Loc;

?>
<div class="aspro-smartseo__conditions__info" title="">
  <table>
    <tr>
      <td>
        <div class="aspro-smartseo__conditions__info__label">
            <?= Loc::getMessage('SMARTSEO_GRID_CONDITION_FIELD_ID') ?>:
        </div>
      </td>
      <td>
        <div>
            <?= $row['ID'] ?>
        </div>
      </td>
    </tr>

    <? if($row['NAME']) : ?>
    <tr>
      <td>
        <div class="aspro-smartseo__conditions__info__label">
            <?= Loc::getMessage('SMARTSEO_GRID_CONDITION_FIELD_NAME') ?>:
        </div>
      </td>
      <td>
        <div>
            <a href="#" onclick="gridConditionMenuHandler.edit(<?= CUtil::PhpToJSObject([
                  'ID' => $row['ID'],
                  'NAME' => $row['NAME'],
              ]) ?>); return false;">
              <?= $row['NAME'] ?>
          </a>
        </div>
      </td>
    </tr>
    <? endif ?>

    <tr>
      <td>
        <div class="aspro-smartseo__conditions__info__label">
            <?= Loc::getMessage('SMARTSEO_GRID_CONDITION_FIELD_ACTIVE') ?>:
        </div>
      </td>
      <td>
        <div>
            <?= $row['ACTIVE'] === 'Y'
                ? Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_Y')
                : Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_N')
            ?>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div></div>
      </td>
    </tr>
    <tr>
      <td>
        <div class="aspro-smartseo__conditions__info__label">
          <div class="aspro-smartseo__grid__label-hint" data-ext="hint">
            <?= Loc::getMessage('SMARTSEO_GRID_CONDITION_FIELD_URLS') ?>:
          </div>
        </div>
      </td>
      <td>
        <div>
          <? if ($row['COUNT_URLS'] > 0) : ?>
             <?= $row['COUNT_URLS'] ?>
          <? else : ?>
            <?= $row['COUNT_URLS'] ?>
          <? endif ?>
          <a href="#" class="aspro-smartseo__conditions__info__link-more">
            <?= Loc::getMessage('SMARTSEO_GRID_CONDITION_FIELD_URLS_MORE') ?>
          </a>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <div class="aspro-smartseo__conditions__info__label">
          <div class="aspro-smartseo__grid__label-hint" data-ext="hint">
            <?= Loc::getMessage('SMARTSEO_GRID_CONDITION_FIELD_URL_OPEN_INDEXING') ?>:
            <span data-hint="<?= Loc::getMessage('SMARTSEO_GRID_CONDITION_HINT_INDEXING') ?>"></span>
          </div>
        </div>
      </td>
      <td>
        <div>
            <?= $row['URL_CLOSE_INDEXING'] === 'Y'
                ? Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_N')
                : Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_Y')
            ?>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <div class="aspro-smartseo__conditions__info__label">
           <?= Loc::getMessage('SMARTSEO_GRID_CONDITION_FIELD_SITEMAP') ?>:
        </div>
      </td>
      <td>
        <div>
          <?= $row['SITEMAP'] ?>
        </div>
      </td>
    </tr>
  </table>
</div>