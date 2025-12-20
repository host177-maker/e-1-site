"use strict";

var contextMenuGridUrl = new AsproUI.ContextMenuInnerGrid();

BX.ready(function ()
{
  if (typeof phpObjectGridConditionUrls != 'object'
    || !phpObjectGridConditionUrls.hasOwnProperty('gridId')) {
    console.log('Object phpObjectGridConditionUrls expected');

    return;
  }
  
  contextMenuGridUrl.register(
    phpObjectGridConditionUrls.gridId,
    'tabs_urls',
    phpObjectGridConditionUrls.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_URL_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_URL_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_URL_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_URL_MESSAGE_DELETE')
    }
  );

  new AsproUI.FilterCustomEntity({
    fieldId: 'FILTER_CONDITION_ID',
    url: phpObjectGridConditionUrls.urls.FILTER_ENTITY_FILTER_CONDITION,
    data: {
      filter_rule: phpObjectGridConditionUrls.filterRuleId,
    }
  });
  
  new AsproUI.FilterCustomEntity({
    fieldId: 'SECTION_ID',
    url: phpObjectGridConditionUrls.urls.FILTER_ENTITY_SECTIONS,
    data: {
      filter_rule: phpObjectGridConditionUrls.filterRuleId,
    }
  });
  
  /*
   * Tabs Init
   */
  (function ()
  {
    let tabs = new AsproUI.DynamicTabs('tabs_urls', {}, {}, {
      newTabTitle: BX.message('SMARTSEO_GRID_URL_TAB_TITLE'),
    });

    tabs.onAfterAdd = function (elName, elBody, tab)
    {
      if (!phpObjectGridConditionUrls.urls.hasOwnProperty('DETAIL_PAGE')) {
        return;
      }

      let dataset = tab.dataset;
      let url = phpObjectGridConditionUrls.urls.DETAIL_PAGE;

      if (phpObjectGridConditionUrls.filterRuleId) {
        url = url + '&filter_rule_id=' + phpObjectGridConditionUrls.filterRuleId;
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
  }());

})

