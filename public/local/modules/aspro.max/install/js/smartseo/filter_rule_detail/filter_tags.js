"use strict";

var contextMenuGridTags = new AsproUI.ContextMenuInnerGrid();

BX.ready(function ()
{
  if (typeof phpObjectGridTags != 'object'
    || !phpObjectGridTags.hasOwnProperty('gridId')) {
    console.log('Object phpObjectGridTags expected');

    return;
  }
  
  contextMenuGridTags.register(
    phpObjectGridTags.gridId,
    'tabs_tags',
    phpObjectGridTags.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_TAG_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_TAG_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_TAG_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_TAG_MESSAGE_DELETE')
    }
  );

  let elTagPageContainer = document.getElementById('tabs_tags');

  /*
   * Tabs Init
   */
  (function ()
  {
    let tabs = new AsproUI.DynamicTabs('tabs_tags', {}, {}, {
      newTabName: BX.message('SMARTSEO_GRID_TAG_NEW'),
    });

    let btnAddTab = elTagPageContainer.querySelector('[page-role="add-tab-tag"]');

    if (btnAddTab) {
      btnAddTab.onclick = function (event)
      {
        tabs.addTab();
      }
    }    
    
    tabs.onAfterAdd = function (elName, elBody, tab)
    {
      if (!phpObjectGridTags.urls.hasOwnProperty('DETAIL_PAGE')) {
        return;
      }

      let dataset = tab.dataset;
      let url = phpObjectGridTags.urls.DETAIL_PAGE;

      if (phpObjectGridTags.filterRuleId) {
        url = url + '&filter_rule_id=' + phpObjectGridTags.filterRuleId;
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

