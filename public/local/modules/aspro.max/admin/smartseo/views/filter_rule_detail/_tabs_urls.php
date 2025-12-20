<?php
/**
 *  @var array $dataFilterRule
 *  @var string $gridUrls[GRID_ID]
 *  @var string $gridUrls[FILTER_ID]
 *  @var array $gridUrls[COLUMNS]
 *  @var array $gridUrls[FILTER_FIELDS]
 *  @var array $gridUrls[ROWS]
 *  @var int $gridUrls[TOTAL_ROWS_COUNT]
 *  @var Bitrix\Main\UI\PageNavigation $gridUrls[NAV_OBJECT]
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;
?>
<div class="aspro-smartseo__form-detail__urls-tabs-wrapper">
  <div id="tabs_urls" class="aspro-ui-tabs">
    <div tabs-role="tabs-head" class="aspro-ui-tabs__wrapper-tabs">
      <div tabs-role="tabs" class="aspro-ui-tabs__tabs">
        <div tabs-role="tab" data-target="#tabs_url_tab_view" class="aspro-ui-tabs__tab aspro-ui-tabs__tab--active">
          <span tabs-role="tab-name" class="aspro-ui-tabs__tab__name"><?= Loc::getMessage('SMARTSEO_URLS_GENERAL_NAME') ?></span>
        </div>
      </div>
      <div tabs-role="toogle-fix" class="aspro-ui-tabs__toggle-fix aspro-ui-tabs__toggle-fix--off"></div>
    </div>
    <div tabs-role="tab-views" class="aspro-ui-tabs__view-tabs">
      <div tabs-role="tab-view" id="tabs_url_tab_view" tabs-role="tab" class="aspro-ui-tabs__view-tab active">
        <div tabs-role="tab-view-title" class="aspro-ui-tabs__view-tab__title"><?= Loc::getMessage('SMARTSEO_URLS_GENERAL_TITLE') ?></div>
        <div tabs-role="tab-view-body">
          <div class="aspro-smartseo__wrapper-urls">
            <? include $this->getViewPath() . 'grids/' . $gridUrls['GRID_ID'] . '.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>