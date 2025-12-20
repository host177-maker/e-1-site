'use strict';

var SiteSettingForm = function (form, settings)
{
  if (form instanceof HTMLElement) {
    this.elForm = form;
  } else {
    this.elForm = document.getElementById(form);
  }

  this.settings = settings;

  if (!this._validateParams()) {
    return;
  }

  let elUrlMenuControls = this.elForm.querySelectorAll('[page-role="control-engine-url"]');
  
  this.elFormSelectSmartfilterFriendly = this.elForm.querySelector('[page-role="form-field-SMARTFILTER_FRIENDLY"]');
  this.elWrapperSmartfilterUrl = this.elForm.querySelectorAll('[page-role="smartfilter-url-wrapper"]');
  this.elWrapperNotFriendlyControls = this.elForm.querySelectorAll('[page-role="not-friendly-controls-wrapper"]');

  for (let i = 0; i < elUrlMenuControls.length; i++) {
    new AsproUI.Form.ControlTemplateEngine(elUrlMenuControls[i], false, {
      urlMenuResponse: this.settings.urls.MENU_URL_TEMPLATE,
    });
  }

  let settingForm = new AsproUI.Form(this.elForm);
  
  let self = this;

  settingForm.onSuccess = function (data, status, form)
  {
    if (data.result === false) {
      this.showAlert(data.message);

      this.hideBtnLoading();

      return;
    }

    this.hideAlert();
    this.hideBtnLoading();
    this.slideUpAlert();
    
    let _contentMessage = BX.message('SMARTSEO_MESSAGE_SAVE_SUCCESS');
        
    _contentMessage = _contentMessage.replace(/#NAME#/gi, self.settings.data.SITE_NAME);
    
    console.log(_contentMessage);

    BX.UI.Notification.Center.notify({
      content: _contentMessage,
      autoHideDelay: 3000,
    });    
  }
  
  this.registerHandles();
}

SiteSettingForm.prototype = {
  registerHandles: function ()
  {
    let self = this;
    
    this.elFormSelectSmartfilterFriendly.onchange = function (event) {
      if(this.value == 'Y') {
       self._displayWrapperSmartfilterUrl(true);   
       self._displayWrapperNotFriendlyControls(false);
      } else {
        self._displayWrapperSmartfilterUrl(false);
        self._displayWrapperNotFriendlyControls(true);
      }
    }
  },
  
  _displayWrapperSmartfilterUrl: function(isVisible)
  {    
    if(!this.elWrapperSmartfilterUrl) {
      return;
    }
    
    for (let i = 0; i < this.elWrapperSmartfilterUrl.length; i++) {
       if(isVisible === true) {
         this.elWrapperSmartfilterUrl[i].style.display = 'table-row';
       } else {
         this.elWrapperSmartfilterUrl[i].style.display = 'none';
       }
    }
  },
  
  _displayWrapperNotFriendlyControls: function(isVisible)
  {    
    if(!this.elWrapperSmartfilterUrl) {
      return;
    }
    
    for (let i = 0; i < this.elWrapperNotFriendlyControls.length; i++) {
       if(isVisible === true) {
         this.elWrapperNotFriendlyControls[i].style.display = 'table-row';
       } else {
         this.elWrapperNotFriendlyControls[i].style.display = 'none';
       }
    }
  },
  
  _validateParams: function ()
  {    
    if(!this.settings.hasOwnProperty('data') || !this.settings.data.hasOwnProperty('SITE_NAME')) {
      this.settings.data.SITE_NAME = '';
    }

    return true;
  }
}