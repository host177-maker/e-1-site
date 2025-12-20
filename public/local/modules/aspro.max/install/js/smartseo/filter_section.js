'use strict';

var contextMenuHandler = {};

BX.ready(function ()
{
  new AsproUI.Form('filter_section_form');

  /**
   *  The context menu event handler for the page
   */
  (function ()
  {
    contextMenuHandler.delete = function (sectionId)
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

      let popup = this.popup;
      let btnDelete = new BX.PopupWindowButton({
        text: BX.message('SMARTSEO_POPUP_BTN_DELETE'),
        className: 'ui-btn ui-btn-danger',
        events: {click: function ()
          {
            let btn = this;
            btn.addClassName('ui-btn-wait');

            BX.ajax({
              url: phpObjectFilterSection.urls.DELETE,
              data: {
                id: sectionId,
                module: 'smartseo',
              },
              method: 'POST',
              dataType: 'json',
              onsuccess: function (data)
              {
                var message = '';
                if (Array.isArray(data.message)) {
                  data.message.forEach(function (value)
                  {
                    message += value + '<br\>';
                  });
                } else {
                  message = data.message;
                }

                if (data.result === false) {
                  var myAlert = new BX.UI.Alert({
                    text: message,
                    textCenter: true,
                    color: BX.UI.Alert.Color.DANGER,
                  });

                  elContentContainer.innerHTML = '';
                  elContentContainer.appendChild(myAlert.getContainer());
                  popup.setContent(elContentContainer);
                  popup.adjustPosition();
                  btn.removeClassName('ui-btn-wait');
                }

                if (data.result === true) {
                  if (data.redirect) {
                    window.location.href = data.redirect;
                  }
                }
              },
              onfailure: function ()
              {
                btn.removeClassName('ui-btn-wait');
              }
            });
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