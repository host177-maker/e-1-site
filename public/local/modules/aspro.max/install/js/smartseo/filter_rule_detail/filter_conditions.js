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
    'tabs_conditions',
    phpObjectGridConditions.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_CONDITION_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_CONDITION_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_CONDITION_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_CONDITION_MESSAGE_DELETE')
    }
  );

  contextMenuGridCondition.actionCopy = function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_COPY')) {
      console.log('Action "ACTION_COPY" not found');

      return;
    }

    this._sendRequest(this.urls.ACTION_COPY, {
      id: id,
      module: 'smartseo',
    });
  }

  contextMenuGridCondition.actionGenerateUrls = function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_GENERATE_URLS')) {
      console.log('Action "ACTION_GENERATE_URLS" not found');

      return;
    }

    this._sendRequest(this.urls.ACTION_GENERATE_URLS, {
      id: id,
      module: 'smartseo',
    }, function (data)
    {
      let elementName = data.fields.NAME
        ? data.fields.NAME
        : BX.message('SMARTSEO_GRID_CONDITION_VALUE_NAME_DEFAULT').replace(/#ID#/gi, data.fields.ID);

      let contentMessage = BX.message('SMARTSEO_GRID_CONDITION_MESSAGE_GENERATE_SUCCESS');

      contentMessage = contentMessage.replace(/#NAME#/gi, elementName);
      contentMessage = contentMessage.replace(/#COUNT#/gi, data.fields.COUNT_CREATED_LINKS);

      BX.UI.Notification.Center.notify({
        content: contentMessage,
        autoHideDelay: 3000,
      });
    });
  }

  contextMenuGridCondition.actionOpenUrlIndexing = function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_OPEN_URL_INDEXING')) {
      console.log('Action "ACTION_OPEN_URL_INDEXING" not found');

      return;
    }

    this._sendRequest(this.urls.ACTION_OPEN_URL_INDEXING, {
      id: id,
      module: 'smartseo',
    });
  }

  contextMenuGridCondition.actionCloseUrlIndexing = function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_CLOSE_URL_INDEXING')) {
      console.log('Action "ACTION_CLOSE_URL_INDEXING" not found');

      return;
    }

    this._sendRequest(this.urls.ACTION_CLOSE_URL_INDEXING, {
      id: id,
      module: 'smartseo',
    });
  }

  let elMainPageContainer = document.getElementById('filter_rule_detail');

  /*
   * Tabs Init
   */
  (function ()
  {
    let conditionTabs = new AsproUI.DynamicTabs('tabs_conditions', {}, {}, {
      newTabName: BX.message('SMARTSEO_GRID_CONDITIONS_NEW'),
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

      if (phpObjectGridConditions.filterRuleId) {
        url = url + '&filter_rule_id=' + phpObjectGridConditions.filterRuleId;
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
      if (typeof filter_rule_tab_control != 'object') {
        return;
      }

      let footerParentTabControl = BX(filter_rule_tab_control.name + '_buttons_div');

      if (this.activeTabIndex != 0) {
        filter_rule_tab_control.bFixed['bottom'] = true;
        filter_rule_tab_control.ToggleFix('bottom');
      } else {
        filter_rule_tab_control.bFixed['bottom'] = false;
        filter_rule_tab_control.ToggleFix('bottom');
      }
    }
  }());

})

