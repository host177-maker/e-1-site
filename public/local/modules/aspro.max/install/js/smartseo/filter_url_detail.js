var FilterUrlDetailPage = function (container, settings)
{
  if (container instanceof HTMLElement) {
    this.container = container;
  } else {
    this.container = document.getElementById(container);
  }

  this.settings = settings;

  if (!this._validateParams()) {
    return;
  }

  this.urls = settings.urls;
  this.elForm = this.container.querySelector(this.settings.selectors.FORM);
  this.elFormSeo = this.container.querySelector(this.settings.selectors.FORM_SEO);
  this.tabs = AsproUI.DynamicTabsManager.getInstanceById(this.settings.selectors.PARENT_TAB_CONTROL);
  this.currentTab = this.tabs.getTabByIndex(this.tabs.getActiveTabIndex());
  this.grid = BX.Main.gridManager.getInstanceById(this.settings.selectors.PARENT_GRID_ID);
  
  this.dataUrl = settings.dataUrl

  this.elFormFieldID = this.container.querySelector('[page-role="form-field-ID"]');
  this.elFormLabelID = this.container.querySelector('[page-role="form-label-ID"]');    
    
  this.init();
  this.initForms();
  this.initMenuSeoPropertyForForm();
  this.deleteFadeInAnimateClass();
  this.registerHandles();
  this.fixedButtonPanel();
}

FilterUrlDetailPage.prototype = { 
  init: function()
  {
    if(!this.settings.dataUrl) {
        return;
    }
    
    if(this.currentTab && this.dataUrl) {     
      this.currentTab.elTabViewTitle.innerHTML = this.dataUrl.NAME;
    }
  },
  
  initMenuSeoPropertyForForm: function ()
  {
    let elTemplateControls = this.elFormSeo.querySelectorAll('[page-role="control-engine-template"]');

    let self = this;

    for (let i = 0; i < elTemplateControls.length; i++) {
      let controlTemplateEngine = new AsproUI.Form.ControlTemplateEngine(elTemplateControls[i], false, {
        urlMenuResponse: this.urls.MENU_SEO_PROPERTY,
        urlSampleResponse: this.urls.SAMPLE_SEO_PROPERTY,
      });

      controlTemplateEngine.onBeforeSend = function ()
      {
        this.clearMenu();
        this.setAdditionalData({
          filter_url: self.dataUrl.ID
        });
      }
    }
  },
  
  deleteFadeInAnimateClass: function ()
  {
    let self = this;
    setTimeout(function ()
    {
      self.container.classList.remove('aspro-ui--animate-fade-in');
    }, 1000)

  },
  
  fixedButtonPanel: function ()
  {
    let elButtonPanel = this.container.querySelector('[page-role="button-panel"]');

    if (!elButtonPanel) {
      return;
    }

    elButtonPanel.classList.add('aspro-ui--animate-slide-top');

    BX.Fix(elButtonPanel, {type: 'bottom', limit_node: this.container});
  },

  registerHandles: function ()
  {
    let self = this;  
  },

  initForms: function ()
  {
    let elBtnSave = this.container.querySelector('[page-role="save"]'),
      elBtnApply = this.container.querySelector('[page-role="apply"]'),
      elBtnCancel = this.container.querySelector('[page-role="cancel"]'),
      elAlert = this.container.querySelector('[page-role="alert"]');

    let form = new AsproUI.Form(this.elForm, {
      elBtnSave: elBtnSave,
      elBtnApply: elBtnApply,
      elBtnCancel: elBtnCancel,
      elAlert: elAlert,
      offsetAlertScroll: function ()
      {
        return 120;
      },
    });

    let self = this;

    form.onSuccess = function (data, status, form)
    {
      if (data.result === false) {
        this.showAlert(data.message);

        this.hideBtnLoading();

        return;
      }

      if (data.fields.ID) {
        self.elFormFieldID.value = data.fields.ID;
        self.elFormLabelID.innerHTML = data.fields.ID;
      }
      
      let elementName = data.fields.NAME
        ? data.fields.NAME
        : '';

      this.hideAlert();
      this.hideBtnLoading();
      this.slideUpAlert();    

      if (data.action === 'save') {
        self.tabs.removeActiveTab();
        self.tabs.openTab(0);
      }
      
      self.grid.reloadTable(null, null);

      let _contentMessage = BX.message('SMARTSEO_MESSAGE_SAVE_SUCCESS');

      _contentMessage = _contentMessage.replace(/#NAME#/gi, elementName);

      BX.UI.Notification.Center.notify({
        content: _contentMessage,
        autoHideDelay: 2000,
      });
    }

    form.onBtnCancelClick = function (event)
    {
      self.tabs.removeActiveTab();
      self.tabs.openTab(0);

      event.preventDefault();
    }
    
    if (this.settings.aliasSeo) {
      form.mergeForm(this.elFormSeo, this.settings.aliasSeo);
    }
  },

  fixedButtonPanel: function ()
  {
    let elButtonPanel = this.container.querySelector('[page-role="button-panel"]');

    if (!elButtonPanel) {
      return;
    }

    elButtonPanel.classList.add('aspro-ui--animate-slide-top');

    BX.Fix(elButtonPanel, {type: 'bottom', limit_node: this.container});
  }, 

  _validateParams: function ()
  {
    if (!this.settings.selectors) {
      console.log('FilterUrlDetailPage: Array selectors expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('FORM')) {
      console.log('FilterUrlDetailPage: Selector FORM expected')
      return false;
    }
    
    if (!this.settings.selectors.hasOwnProperty('PARENT_GRID_ID')) {
      console.log('FilterUrlDetailPage: Selector PARENT_GRID_ID expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('PARENT_TAB_CONTROL')) {
      console.log('FilterUrlDetailPage: Selector PARENT_TAB_CONTROL expected')
      return false;
    }

    if (!AsproUI.DynamicTabsManager.hasInstanceById(this.settings.selectors.PARENT_TAB_CONTROL)) {
      console.log('DynamicTabs "tabs_urls" instance not found');
      return false;
    }

    return true;
  },
}
