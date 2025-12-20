<?php
/**
 *  @var array $dataFilterRule
 *  @var string $gridSitemap[GRID_ID]
 *  @var string $gridSitemap[FILTER_ID]
 *  @var array $gridSitemap[COLUMNS]
 *  @var array $gridSitemap[FILTER_FIELDS]
 *  @var array $gridSitemap[ROWS]
 *  @var int $gridSitemap[TOTAL_ROWS_COUNT]
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;
?>
<div class="aspro-smartseo__form-detail__urls-tabs-wrapper">
  <div id="tabs_sitemap" class="aspro-ui-tabs">
    <div tabs-role="tabs-head" class="aspro-ui-tabs__wrapper-tabs">
      <div tabs-role="tabs" class="aspro-ui-tabs__tabs">
        <div tabs-role="tab" data-target="#tabs_url_tab_view" class="aspro-ui-tabs__tab aspro-ui-tabs__tab--active">
          <span tabs-role="tab-name" class="aspro-ui-tabs__tab__name"><?= Loc::getMessage('SMARTSEO_SITEMAP_GENERAL_NAME') ?></span>
        </div>
      </div>
      <div tabs-role="toogle-fix" class="aspro-ui-tabs__toggle-fix aspro-ui-tabs__toggle-fix--off"></div>
    </div>
    <div tabs-role="tab-views" class="aspro-ui-tabs__view-tabs">
      <div tabs-role="tab-view" id="tabs_url_tab_view" tabs-role="tab" class="aspro-ui-tabs__view-tab active">
        <div tabs-role="tab-view-title" class="aspro-ui-tabs__view-tab__title"><?= Loc::getMessage('SMARTSEO_SITEMAP_GENERAL_TITLE') ?></div>
        <div tabs-role="tab-view-body">
          <div class="aspro-smartseo__conditions-toolbar">
            <button class="ui-btn ui-btn-default ui-btn-success-light" page-role="add-tab-sitemap">
              <?= Loc::getMessage('SMARTSEO_SITEMAP_BTN_ADD') ?>
            </button>
          </div>
          <div class="aspro-smartseo__wrapper-sitemap">
            <? include $this->getViewPath() . 'grids/' . $gridSitemap['GRID_ID'] . '.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>