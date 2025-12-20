"use strict";

var contextMenuGridCondition = new AsproUI.ContextMenuInnerGrid();

BX.ready(function ()
{
  if (typeof phpObjectGridSitemapCondition != 'object'
    || !phpObjectGridSitemapCondition.hasOwnProperty('gridId')) {
    console.log('Object phpObjectGridSitemapCondition expected');

    return;
  }
  
  contextMenuGridCondition.register(
    phpObjectGridSitemapCondition.gridId,
    '',
    phpObjectGridSitemapCondition.urls,
    {
      popupBtnDelete: BX.message('SMARTSEO_POPUP_CONDITION_BTN_DELETE'),
      popupBtnCancel: BX.message('SMARTSEO_POPUP_CONDITION_BTN_CANCEL'),
      popupBtnClose: BX.message('SMARTSEO_POPUP_CONDITION_BTN_CLOSE'),
      popupMessageDelete: BX.message('SMARTSEO_POPUP_CONDITION_MESSAGE_DELETE')
    }
  );

})

