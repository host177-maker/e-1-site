"use strict";

var contextMenuGridSitemap = new AsproUI.ContextMenuInnerGrid();

BX.ready(function ()
{
  if (typeof phpObjectGridSitemap != 'object'
    || !phpObjectGridSitemap.hasOwnProperty('gridId')) {
    console.log('Object phpObjectGridSitemap expected');

    return;
  }

  contextMenuGridSitemap.register(
    phpObjectGridSitemap.gridId,
    'tabs_sitemap',
    phpObjectGridSitemap.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_SITEMAP_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_SITEMAP_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_SITEMAP_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_SITEMAP_MESSAGE_DELETE')
    }
  );

  let elSitemapPageContainer = document.getElementById('tabs_sitemap');

  /*
   * Tabs Init
   */
  (function ()
  {
    let tabs = new AsproUI.DynamicTabs('tabs_sitemap', {}, {}, {
      newTabName: BX.message('SMARTSEO_GRID_SITEMAP_NEW'),
    });

    let btnAddTab = elSitemapPageContainer.querySelector('[page-role="add-tab-sitemap"]');

    if (btnAddTab) {
      btnAddTab.onclick = function (event)
      {
        tabs.addTab();
      }
    }

    tabs.onAfterAdd = function (elName, elBody, tab)
    {
      if (!phpObjectGridSitemap.urls.hasOwnProperty('DETAIL_PAGE')) {
        return;
      }

      let dataset = tab.dataset;
      let url = phpObjectGridSitemap.urls.DETAIL_PAGE;

      if (phpObjectGridSitemap.filterRuleId) {
        url = url + '&filter_rule_id=' + phpObjectGridSitemap.filterRuleId;
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

