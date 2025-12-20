"use strict";

var contextMenuGridSearch = new AsproUI.ContextMenuInnerGrid();

BX.ready(function ()
{
  if (typeof phpObjectGridSearch != 'object'
    || !phpObjectGridSearch.hasOwnProperty('gridId')) {
    console.log('Object phpObjectGridSearch expected');

    return;
  }
  
  contextMenuGridSearch.register(
    phpObjectGridSearch.gridId,
    'tabs_search',
    phpObjectGridSearch.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_SEARCH_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_SEARCH_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_SEARCH_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_SEARCH_MESSAGE_DELETE')
    }
  );

  contextMenuGridSearch.actionReindex = function(id)
  {
    if (!this.urls.hasOwnProperty('ACTION_REINDEX')) {
      console.log('Action "ACTION_REINDEX" not found');

      return;
    }

    this._sendRequest(this.urls.ACTION_REINDEX, {
        id: id,
        module: 'smartseo',
    }, function(data) {
        let elementName = data.fields.NAME
          ? data.fields.NAME
          : BX.message('SMARTSEO_DEFAULT_TAB_NAME').replace(/#ID#/gi, data.fields.FILTER_CONDITION_ID);
      
        let contentMessage = BX.message('SMARTSEO_GRID_SEARCH_MESSAGE_REINDEX_SUCCESS');
        
        contentMessage = contentMessage.replace(/#NAME#/gi, elementName);
        contentMessage = contentMessage.replace(/#COUNT#/gi, data.fields.COUNT_SEARCH_INDEX);
        
        BX.UI.Notification.Center.notify({
          content: contentMessage,
          autoHideDelay: 3000,
        });
    });
  }

  let elSearchPageContainer = document.getElementById('tabs_search');

  /*
   * Tabs Init
   */
  (function ()
  {
    let tabs = new AsproUI.DynamicTabs('tabs_search', {}, {}, {
      newTabName: BX.message('SMARTSEO_GRID_SEARCH_NEW'),
    });

    let btnAddTab = elSearchPageContainer.querySelector('[page-role="add-tab-search"]');

    if (btnAddTab) {
      btnAddTab.onclick = function (event)
      {
        tabs.addTab();
      }
    }    
    
    tabs.onAfterAdd = function (elName, elBody, tab)
    {
      if (!phpObjectGridSearch.urls.hasOwnProperty('DETAIL_PAGE')) {
        return;
      }

      let dataset = tab.dataset;
      let url = phpObjectGridSearch.urls.DETAIL_PAGE;

      if (phpObjectGridSearch.filterRuleId) {
        url = url + '&filter_rule_id=' + phpObjectGridSearch.filterRuleId;
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
        onfailure: function (){}
      });
      
    }
  }());

})

