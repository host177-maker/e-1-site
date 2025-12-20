'use strict';

var contextMenuGrid = new AsproUI.ContextMenuMainGrid();

BX.ready(function ()
{
  if (typeof phpObjectSitemap != 'object') {
    console.log('Object phpObjectSitemap expected');

    return;
  }

  contextMenuGrid.register(
    'grid_sitemap',
    phpObjectSitemap.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_MESSAGE_DELETE')
    }
  );

  contextMenuGrid.actionGenerate = function(id)
  {
    if (!this.urls.hasOwnProperty('ACTION_GENERATE_SITEMAP')) {
      console.log('Action "ACTION_GENERATE_SITEMAP" not found');

      return;
    }

    this._sendRequest(this.urls.ACTION_GENERATE_SITEMAP, {
        id: id,
        module: 'smartseo',
    }, function(data) {      
        let contentMessage = BX.message('SMARTSEO_POPUP_MESSAGE_GENERATE_SUCCESS');
        
        BX.UI.Notification.Center.notify({
          content: contentMessage,
          autoHideDelay: 3000,
        });
    });
  }
})