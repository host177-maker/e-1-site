<?php
/**
 *  @var array $dataFilterRule
 *  @var string $gridConditions[GRID_ID]
 *  @var array $gridConditions[COLUMNS]
 *  @var array $gridConditions[FILTER_FIELDS]
 *  @var array $gridConditions[ROWS]
 *  @var Bitrix\Main\UI\PageNavigation $gridConditions[NAV_OBJECT]
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;
?>
<div class="aspro-smartseo__form-detail__tabs-wrapper">
  <div id="tabs_conditions" class="aspro-ui-tabs">
    <div tabs-role="tabs-head" class="aspro-ui-tabs__wrapper-tabs">
      <div tabs-role="tabs" class="aspro-ui-tabs__tabs">
        <div tabs-role="tab" data-target="#tabs_condition_tab_view" class="aspro-ui-tabs__tab aspro-ui-tabs__tab--active">
          <span tabs-role="tab-name" class="aspro-ui-tabs__tab__name"><?= Loc::getMessage('SMARTSEO_CONDITIONS_GENERAL_NAME') ?></span>
        </div>
      </div>
      <div tabs-role="toogle-fix" class="aspro-ui-tabs__toggle-fix aspro-ui-tabs__toggle-fix--off"></div>
    </div>
    <div tabs-role="tab-views" class="aspro-ui-tabs__view-tabs">
      <div tabs-role="tab-view" id="tabs_condition_tab_view" tabs-role="tab" class="aspro-ui-tabs__view-tab active">
        <div tabs-role="tab-view-title" class="aspro-ui-tabs__view-tab__title"><?= Loc::getMessage('SMARTSEO_CONDITIONS_GENERAL_TITLE') ?></div>
        <div tabs-role="tab-view-body">
          <? if ($dataFilterRule) : ?>
              <div class="aspro-smartseo__conditions-toolbar">
                <button class="ui-btn ui-btn-default ui-btn-success-light" page-role="add-tab-condition">
                  <?= Loc::getMessage('SMARTSEO_CONDITIONS_BTN_ADD') ?>
                </button>
              </div>
          <? else : ?>
              <div class="ui-alert ui-alert-xs ui-alert-warning" style="display: block;">
                <span class="ui-alert-message"><?= Loc::getMessage('SMARTSEO_ALERT_NEED_TO_PRESERVE') ?></span>
              </div>
          <? endif ?>
          <div class="aspro-smartseo__wrapper-conditions">
            <? include $this->getViewPath() . 'grids/' . $gridConditions['GRID_ID'] . '.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>