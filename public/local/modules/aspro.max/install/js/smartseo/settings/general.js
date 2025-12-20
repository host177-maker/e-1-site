'use strict';

BX.ready(function ()
{
  new AsproUI.Form('general_setting_form');

  let elGeneralSettingForm = document.getElementById('general_setting_form');
  
  (function ()
  {
    let elFilterRuleNameTemplate = elGeneralSettingForm.querySelector('[page-role="control-engine-filter-rule-name"]');

    if (elFilterRuleNameTemplate && phpObjectGeneralSetting.urls.MENU_FILTER_NAME) {
      new AsproUI.Form.ControlTemplateEngine(elFilterRuleNameTemplate, false, {
        urlMenuResponse: phpObjectGeneralSetting.urls.MENU_FILTER_NAME,
      });
    }

  }());

  (function ()
  {
    let elBtnClearCache = elGeneralSettingForm.querySelector('[page-role="clear-cache"]');

    elBtnClearCache.onclick = function (event) {
      let self = this;

      self.classList.add('ui-btn-wait');

      BX.ajax({
        url: phpObjectGeneralSetting.urls.ACTION_CLEAR_CACHE,
        data: {},
        method: 'POST',
        dataType: 'json',
        onsuccess: function (html)
        {
          self.classList.remove('ui-btn-wait');

          BX.UI.Notification.Center.notify({
            content: BX.message('SMARTSEO_NOTIFICATION_CLEAR_CACHE_SUCCESS'),
            autoHideDelay: 3000,
          });
        },
        onfailure: function ()
        {
          self.classList.remove('ui-btn-wait');
        }
      });
    }

  }());

  (function ()
  {
    let elementHints = elGeneralSettingForm.querySelectorAll('[data-ext="hint"]');
    for (var i = 0; i < elementHints.length; i++) {
      BX.UI.Hint.init(elementHints[i]);
    }
  }());
})