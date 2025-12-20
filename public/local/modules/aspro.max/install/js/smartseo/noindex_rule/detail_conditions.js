"use strict";

var contextMenuGridCondition = new AsproUI.ContextMenuInnerGrid();

BX.ready(function ()
{
  if (typeof phpObjectGridConditions != 'object'
    || !phpObjectGridConditions.hasOwnProperty('gridId')) {
    console.log('Object phpObjectGridConditions expected');

    return;
  }

  contextMenuGridCondition.register(
    phpObjectGridConditions.gridId,
    'tabs_noindex_rule_condition',
    phpObjectGridConditions.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_MESSAGE_DELETE')
    }
  );

  let elMainPageContainer = document.getElementById('noindex_rule_detail');

  /*
   * Tabs Init
   */
  (function ()
  {
    let conditionTabs = new AsproUI.DynamicTabs('tabs_noindex_rule_condition', {}, {}, {
      newTabName: BX.message('SMARTSEO_GRID_CONDITION_NEW_TAB'),
    });

    let btnAddTab = elMainPageContainer.querySelector('[page-role="add-tab-condition"]');

    if (btnAddTab) {
      btnAddTab.onclick = function (event)
      {
        conditionTabs.addTab();
      }
    }

    conditionTabs.onAfterAdd = function (elName, elBody, tab)
    {
      if (!phpObjectGridConditions.urls.hasOwnProperty('DETAIL_PAGE')) {
        return;
      }

      let dataset = tab.dataset;
      let url = phpObjectGridConditions.urls.DETAIL_PAGE;

      if (phpObjectGridConditions.noindexRuleId) {
        url = url + '&noindex_rule_id=' + phpObjectGridConditions.noindexRuleId;
      }

      if (dataset.ID) {
        url = url + '&id=' + dataset.ID;
      }

      let wait = BX.showWait(elBody);

      BX.ajax({
        url: url,
        data: {
          module: 'smartseo'
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function (html)
        {
          elBody.innerHTML = html;
          BX.closeWait(elBody, wait);
        },
        onfailure: function ()
        {}
      });
    }

    conditionTabs.onAfterOpen = function (elName, elBody, tab)
    {
      if (typeof noindex_rule__section_tab_control != 'object') {
        return;
      }

      noindex_rule__section_tab_control.bFixed['bottom'] = true;
      noindex_rule__section_tab_control.ToggleFix('bottom');
    }
  }());

})

