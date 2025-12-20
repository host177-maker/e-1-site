'use strict';

var contextMenuHandler = {};

BX.ready(function ()
{
  /**
   *  The context menu event handler for the page
   */
  (function ()
  {
    contextMenuHandler.delete = function (fnCallback)
    {
      let elContentContainer = document.createElement('div');
      elContentContainer.classList.add('aspro-ui-popup__content');
      elContentContainer.innerHTML = BX.message('SMARTSEO_POPUP_MESSAGE_DELETE');
      
      if (typeof this.popup === 'undefined') {
        this.popup = new BX.PopupWindow('popup_window_context_menu', null, {
          closeIcon: true,
          zIndex: 0,
          offsetLeft: 0,
          offsetTop: 0,
          draggable: false,
          overlay: {
            backgroundColor: 'black',
            opacity: '80'
          },
        });
      }
      
      this.popup.setContent(elContentContainer);

      let btnDelete = new BX.PopupWindowButton({
        text: BX.message('SMARTSEO_POPUP_BTN_DELETE'),
        className: 'ui-btn ui-btn-danger',
        events: {click: function ()
          {
            fnCallback();
            this.popupWindow.close();
          }}
      });

      let btnCancel = new BX.PopupWindowButton({
        text: BX.message('SMARTSEO_POPUP_BTN_CANCEL'),
        className: 'ui-btn ui-btn-light',
        events: {click: function ()
          {
            this.popupWindow.close();
          }}
      });

      this.popup.setButtons([
        btnDelete,
        btnCancel
      ])

      this.popup.show();
    }

  }());
})