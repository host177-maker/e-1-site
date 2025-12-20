'use strict';

var contextMenuGrid = new AsproUI.ContextMenuMainGrid();

BX.ready(function ()
{
  if (typeof phpObjectSeoText != 'object') {
    console.log('Object phpObjectSeoText expected');

    return;
  }

  contextMenuGrid.register(
    'grid_seo_text',
    phpObjectSeoText.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_MESSAGE_DELETE')
    }
  ); 
})