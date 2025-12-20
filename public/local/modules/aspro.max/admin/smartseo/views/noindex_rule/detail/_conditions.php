<?php
/**
 * @var string $alias
 * @var array $data
 * @var array $listSites
 * @var array $listIblockTypes
 * @var array $listIblocks
 * @var array $listIblockSections
 */
use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

?>
<div class="aspro-smartseo__form-detail__tabs-wrapper">
  <div id="tabs_noindex_rule_condition" class="aspro-ui-tabs">
    <div tabs-role="tabs-head" class="aspro-ui-tabs__wrapper-tabs">
      <div tabs-role="tabs" class="aspro-ui-tabs__tabs">
        <div tabs-role="tab" data-target="#tabs_condition_tab_view" class="aspro-ui-tabs__tab aspro-ui-tabs__tab--active">
          <span tabs-role="tab-name" class="aspro-ui-tabs__tab__name">
            <?= Loc::getMessage('SMARTSEO__CONDITIONS__TAB_NAME') ?>
          </span>
        </div>
      </div>
      <div tabs-role="toogle-fix" class="aspro-ui-tabs__toggle-fix aspro-ui-tabs__toggle-fix--off"></div>
    </div>
    <div tabs-role="tab-views" class="aspro-ui-tabs__view-tabs">
      <div tabs-role="tab-view" id="tabs_condition_tab_view" tabs-role="tab" class="aspro-ui-tabs__view-tab active">
        <div tabs-role="tab-view-title" class="aspro-ui-tabs__view-tab__title">
            <?= Loc::getMessage('SMARTSEO__CONDITIONS__TAB_TITLE') ?>
        </div>
        <div tabs-role="tab-view-body">
          <? if ($data) : ?>
              <div class="aspro-smartseo__conditions-toolbar">
                <button class="ui-btn ui-btn-default ui-btn-success-light" page-role="add-tab-condition">
                  <?= Loc::getMessage('SMARTSEO__CONDITIONS__BTN_ADD') ?>
                </button>
              </div>
          <? else : ?>
              <div class="ui-alert ui-alert-xs ui-alert-warning" style="display: block;">
                <span class="ui-alert-message">
                    <?= Loc::getMessage('SMARTSEO__MESSAGE__NEED_TO_SAVE') ?>
                </span>
              </div>
          <? endif ?>
          <div class="aspro-smartseo__wrapper-noindex-conditions">
             <? include $this->getViewPath() . 'detail/_grid_conditions.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>