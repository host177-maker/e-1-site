<?php
/**
 *  @var array $dataFilterRule
 *  @var string $gridSearch[GRID_ID]
 *  @var string $gridSearch[FILTER_ID]
 *  @var array $gridSearch[COLUMNS]
 *  @var array $gridSearch[FILTER_FIELDS]
 *  @var array $gridSearch[ROWS]
 *  @var int $gridSearch[TOTAL_ROWS_COUNT]
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;
?>
<div class="aspro-smartseo__form-detail__urls-tabs-wrapper">
  <div id="tabs_search" class="aspro-ui-tabs">
    <div tabs-role="tabs-head" class="aspro-ui-tabs__wrapper-tabs">
      <div tabs-role="tabs" class="aspro-ui-tabs__tabs">
        <div tabs-role="tab" data-target="#tabs_search_tab_view" class="aspro-ui-tabs__tab aspro-ui-tabs__tab--active">
          <span tabs-role="tab-name" class="aspro-ui-tabs__tab__name"><?= Loc::getMessage('SMARTSEO_SEARCH_GENERAL_NAME') ?></span>
        </div>
      </div>
      <div tabs-role="toogle-fix" class="aspro-ui-tabs__toggle-fix aspro-ui-tabs__toggle-fix--off"></div>
    </div>
    <div tabs-role="tab-views" class="aspro-ui-tabs__view-tabs">
      <div tabs-role="tab-view" id="tabs_search_tab_view" tabs-role="tab" class="aspro-ui-tabs__view-tab active">
        <div tabs-role="tab-view-title" class="aspro-ui-tabs__view-tab__title"><?= Loc::getMessage('SMARTSEO_SEARCH_GENERAL_TITLE') ?></div>
        <div tabs-role="tab-view-body">
          <div class="aspro-smartseo__conditions-toolbar">
            <button class="ui-btn ui-btn-default ui-btn-success-light" page-role="add-tab-search">
              <?= Loc::getMessage('SMARTSEO_SEARCH_BTN_ADD') ?>
            </button>
          </div>
          <div class="aspro-smartseo__wrapper-tags">
            <? include $this->getViewPath() . 'grids/' . $gridSearch['GRID_ID'] . '.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>