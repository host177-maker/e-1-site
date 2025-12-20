<?php
/**
 *  @var array $dataFilterRule
 *  @var string $gridTags[GRID_ID]
 *  @var string $gridTags[FILTER_ID]
 *  @var array $gridTags[COLUMNS]
 *  @var array $gridTags[FILTER_FIELDS]
 *  @var array $gridTags[ROWS]
 *  @var int $gridTags[TOTAL_ROWS_COUNT]
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;
?>
<div class="aspro-smartseo__form-detail__urls-tabs-wrapper">
  <div id="tabs_tags" class="aspro-ui-tabs">
    <div tabs-role="tabs-head" class="aspro-ui-tabs__wrapper-tabs">
      <div tabs-role="tabs" class="aspro-ui-tabs__tabs">
        <div tabs-role="tab" data-target="#tabs_tags_tab_view" class="aspro-ui-tabs__tab aspro-ui-tabs__tab--active">
          <span tabs-role="tab-name" class="aspro-ui-tabs__tab__name"><?= Loc::getMessage('SMARTSEO_TAG_GENERAL_NAME') ?></span>
        </div>
      </div>
      <div tabs-role="toogle-fix" class="aspro-ui-tabs__toggle-fix aspro-ui-tabs__toggle-fix--off"></div>
    </div>
    <div tabs-role="tab-views" class="aspro-ui-tabs__view-tabs">
      <div tabs-role="tab-view" id="tabs_tags_tab_view" tabs-role="tab" class="aspro-ui-tabs__view-tab active">
        <div tabs-role="tab-view-title" class="aspro-ui-tabs__view-tab__title"><?= Loc::getMessage('SMARTSEO_TAG_GENERAL_TITLE') ?></div>
        <div tabs-role="tab-view-body">
          <div class="aspro-smartseo__conditions-toolbar">
            <button class="ui-btn ui-btn-default ui-btn-success-light" page-role="add-tab-tag">
              <?= Loc::getMessage('SMARTSEO_TAG_BTN_ADD') ?>
            </button>
          </div>
          <div class="aspro-smartseo__wrapper-tags">
            <?  include $this->getViewPath() . 'grids/' . $gridTags['GRID_ID'] . '.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>