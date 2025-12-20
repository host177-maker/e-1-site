var FilterSearchDetailPage = function (container, settings)
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
  this.tabs = AsproUI.DynamicTabsManager.getInstanceById(this.settings.selectors.PARENT_TAB_CONTROL);
  this.currentTab = this.tabs.getTabByIndex(this.tabs.getActiveTabIndex());
  this.grid = BX.Main.gridManager.getInstanceById(this.settings.selectors.PARENT_GRID_ID);  

  this.elFormFieldID = this.container.querySelector('[page-role="form-field-ID"]');
  this.elFormLabelID = this.container.querySelector('[page-role="form-label-ID"]');
  this.elFormSelectFilterCondition = this.container.querySelector('[page-role="form-field-FILTER_CONDITION_ID"]'); 
  this.elFormFieldReindex = this.container.querySelector('[page-role="form-field-REINDEX"]');
  this.elFormCheckboxReindex = this.container.querySelector('[page-role="apply-reindex"]'),
        
  this.initForms();
  this.deleteFadeInAnimateClass();
  this.registerHandles();
  this.initMenuSeoPropertyForForm();
}

FilterSearchDetailPage.prototype = {
  initMenuSeoPropertyForForm: function ()
  {
    let elTemplateControls = this.elForm.querySelectorAll('[page-role="control-engine-template"]');
    
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
          filter_condition: self.elFormSelectFilterCondition.value
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

  registerHandles: function ()
  {
    let self = this;
    
    if(this.elFormCheckboxReindex && this.elFormFieldReindex) {
      this.elFormCheckboxReindex.onchange = function(event) 
      {
        self.elFormFieldReindex.value = this.checked == true ? 'Y' : 'N';
      }
    }
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
        : BX.message('SMARTSEO_DEFAULT_TAB_NAME').replace(/#ID#/gi, data.fields.FILTER_CONDITION_ID);

      this.hideAlert();
      this.hideBtnLoading();
      this.slideUpAlert();

      self.currentTab.elName.innerHTML = elementName;
      self.currentTab.elTabViewTitle.innerHTML = elementName;

      if (data.action === 'save') {
        self.tabs.removeActiveTab();
        self.tabs.openTab(0);
      }

      self.grid.reloadTable(null, null);
      
      if(data.actionReindex) {
        let _contentMessage = BX.message('SMARTSEO_MESSAGE_REINDEX_SUCCESS');
        
        _contentMessage = _contentMessage.replace(/#NAME#/gi, elementName);
        _contentMessage = _contentMessage.replace(/#COUNT#/gi, data.fields.COUNT_SEARCH_INDEX);
        
        BX.UI.Notification.Center.notify({
          content: _contentMessage,
          autoHideDelay: 3000,
        });
      } else {
        let _contentMessage = BX.message('SMARTSEO_MESSAGE_SAVE_SUCCESS');

        _contentMessage = _contentMessage.replace(/#NAME#/gi, elementName);

        BX.UI.Notification.Center.notify({
          content: _contentMessage,
          autoHideDelay: 2000,
        });
      }      
    }

    form.onBtnCancelClick = function (event)
    {
      self.tabs.removeActiveTab();
      self.tabs.openTab(0);

      event.preventDefault();
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
      console.log('FilterSearchDetailPage: Array selectors expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('FORM')) {
      console.log('FilterSearchDetailPage: Selector FORM expected')
      return false;
    }
    
    if (!this.settings.selectors.hasOwnProperty('PARENT_GRID_ID')) {
      console.log('FilterSearchDetailPage: Selector PARENT_GRID_ID expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('PARENT_TAB_CONTROL')) {
      console.log('FilterSearchDetailPage: Selector PARENT_TAB_CONTROL expected')
      return false;
    }

    if (!AsproUI.DynamicTabsManager.hasInstanceById(this.settings.selectors.PARENT_TAB_CONTROL)) {
      console.log('DynamicTabs "tabs_search" instance not found');
      return false;
    }

    return true;
  }  
}
