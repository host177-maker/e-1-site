var FilterConditionDetailPage = function (container, settings)
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
  this.elFormCondition = this.container.querySelector(this.settings.selectors.FORM_CONDITION);
  this.elFormSeoProperty = this.container.querySelector(this.settings.selectors.FORM_SEO_PROPERTY);
  this.tabs = AsproUI.DynamicTabsManager.getInstanceById(this.settings.selectors.PARENT_TAB_CONTROL);
  this.currentTab = this.tabs.getTabByIndex(this.tabs.getActiveTabIndex());
  this.grid = BX.Main.gridManager.getInstanceById(this.settings.selectors.PARENT_GRID_ID);
  this.gridUrls = BX.Main.gridManager.getInstanceById(this.settings.selectors.GRID_URL_ID);
  
  this.elFormFieldID = this.container.querySelector('[page-role="form-field-ID"]');
  this.elFormFieldGenerate = this.container.querySelector('[page-role="form-field-GENERATE"]');
  this.elFormLabelID = this.container.querySelector('[page-role="form-label-ID"]');
  this.elCheckboxApplyGenerateUrls = this.container.querySelector('[page-role="apply-generate-url"]');
  this.elFormSelectSitemap = this.container.querySelector('[page-role="form-field-SITEMAP"]');
  this.elWrapperSitemapControls = this.container.querySelectorAll('[page-role="sitemap-wrapper-controls"]');
  
  this.initMenuSeoPropertyForForm();
  this.initMenuUrlPropertyForForm();
  this.initForms();
  this.fixedButtonPanel();
  this.deleteFadeInAnimateClass();
  this.registerHandles();
}

FilterConditionDetailPage.prototype = {
  initMenuSeoPropertyForForm: function ()
  {
    let elTemplateControls = this.elFormSeoProperty.querySelectorAll('[page-role="control-engine-template"]');

    let self = this;

    for (let i = 0; i < elTemplateControls.length; i++) {
      let controlTemplateEngine = new AsproUI.Form.ControlTemplateEngine(elTemplateControls[i], false, {
        urlMenuResponse: this.urls.MENU_SEO_PROPERTY,
        urlSampleResponse: this.urls.SAMPLE_SEO_PROPERTY,
      });

      controlTemplateEngine.onBeforeSend = function ()
      {
        this.clearMenu();
        this.setAdditionalData(self.getConditionDataFromForm());
      }
    }
  },
  
  initMenuUrlPropertyForForm: function ()
  {
    let elTemplateControls = this.elFormCondition.querySelectorAll('[page-role="control-engine-template"]');

    let self = this;

    for (let i = 0; i < elTemplateControls.length; i++) {
      let controlTemplateEngine = new AsproUI.Form.ControlTemplateEngine(elTemplateControls[i], false, {
        urlMenuResponse: this.urls.MENU_URL_PROPERTY,
      });
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
  
  registerHandles: function()
  {
    let self = this;
    if(this.elCheckboxApplyGenerateUrls && this.elFormFieldGenerate) {
      this.elCheckboxApplyGenerateUrls.onchange = function(event) 
      {
        self.elFormFieldGenerate.value = this.checked == true ? 'Y' : 'N';
      }
    }
   
    if(this.elFormSelectSitemap && this.elWrapperSitemapControls) {
      this.elFormSelectSitemap.onchange = function(event) 
      {
        if(this.value != 0) {          
          for (let i = 0; i < self.elWrapperSitemapControls.length; i++) {
            self.elWrapperSitemapControls[i].style.display = 'table-row';
          }
          
        } else {
          for (let i = 0; i < self.elWrapperSitemapControls.length; i++) {
            self.elWrapperSitemapControls[i].style.display = 'none';
          }
        }
      }
    }
  },

  initForms: function ()
  {
    let elBtnSave = this.container.querySelector('[page-role="save"]'),
      elBtnApply = this.container.querySelector('[page-role="apply"]'),
      elBtnCancel = this.container.querySelector('[page-role="cancel"]'),
      elAlert = this.container.querySelector('[page-role="alert"]');

    let filterConditionForm = new AsproUI.Form(this.elFormCondition, {
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

    filterConditionForm.onSuccess = function (data, status, form)
    {
      if (data.result === false) {
        this.showAlert(data.message);

        this.hideBtnLoading();

        return;
      }
      
      if(data.fields.ID) {
        self.elFormFieldID.value = data.fields.ID;
        self.elFormLabelID.innerHTML = data.fields.ID;
      }
      
      let elementName = data.fields.NAME 
        ? data.fields.NAME 
        : BX.message('SMARTSEO_DEFAULT_TAB_NAME').replace(/#ID#/gi, data.fields.ID);

      this.hideAlert();
      this.hideBtnLoading();
      this.slideUpAlert();    
        
      self.currentTab.elName.innerHTML = elementName;
      self.currentTab.elTabViewTitle.innerHTML = elementName;      

      if (data.action === 'save') {
        self.tabs.removeActiveTab();
        self.tabs.openTab(0);
      }

      self.grid.reloadTable(null, null, function ()
      {
        if (typeof BX.UI.Hint !== 'object') {
          return;
        }
        let elGrid = document.getElementById(self.settings.selectors.PARENT_GRID_ID);
        let elementHints = elGrid.querySelectorAll('[data-ext="hint"]');
        for (var i = 0; i < elementHints.length; i++) {
          BX.UI.Hint.init(elementHints[i]);
        }
      }); 
      
      if(data.actionGenerate) {
        let _contentMessage = BX.message('SMARTSEO_MESSAGE_COUNT_CREATED_LINKS');
        
        _contentMessage = _contentMessage.replace(/#NAME#/gi, elementName);
        _contentMessage = _contentMessage.replace(/#COUNT#/gi, data.fields.COUNT_CREATED_LINKS);
        
        self.gridUrls.reloadTable(null, null, null);
        
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

    filterConditionForm.onBtnCancelClick = function (event)
    {
      self.tabs.removeActiveTab();
      self.tabs.openTab(0);

      event.preventDefault();
    }

    if (this.settings.aliasSeo) {
      filterConditionForm.mergeForm(this.elFormSeoProperty, this.settings.aliasSeo);
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

  /**
    let formData = new FormData(this.elFormCondition),
      entries = formData.entries(),
      filed = entries.next();

    let result = [];
    while (undefined !== filed.value) {
      if (filed.value[0].match(/\[CONDITION\]/)) {
        result[filed.value[0]] = filed.value[1];
      }
      filed = entries.next();
    }
    
    return result;
   */
  getConditionDataFromForm: function ()
  {
    let formData = new FormData(this.elFormCondition),
      entries = formData.entries(),
      filed = entries.next();

    let result = [];
    while (undefined !== filed.value) {
      if (filed.value[0].match(/\[CONDITION\]/)) {
        result[filed.value[0]] = filed.value[1];
      }
      filed = entries.next();
    }
    
    return result;
  },

  _validateParams: function ()
  {
    if (!this.settings.urls) {
      console.log('FilterConditionDetailPage: Array urls expected')
      return false;
    }

    if (!this.settings.urls.hasOwnProperty('MENU_SEO_PROPERTY')
      || !this.settings.urls.hasOwnProperty('SAMPLE_SEO_PROPERTY')) {
      console.log('FilterConditionDetailPage: Not has own property urls.MENU_SEO_PROPERTY and urls.SAMPLE_SEO_PROPERTY')

      return false;
    }

    if (!this.settings.selectors) {
      console.log('FilterConditionDetailPage: Array selectors expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('FORM_CONDITION')) {
      console.log('FilterConditionDetailPage: Selector FORM_CONDITION expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('FORM_SEO_PROPERTY')) {
      console.log('FilterConditionDetailPage: Selector FORM_SEO_PROPERTY expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('PARENT_GRID_ID')) {
      console.log('FilterConditionDetailPage: Selector PARENT_GRID_ID expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('PARENT_TAB_CONTROL')) {
      console.log('FilterConditionDetailPage: Selector PARENT_TAB_CONTROL expected')
      return false;
    }

    if (!AsproUI.DynamicTabsManager.hasInstanceById(this.settings.selectors.PARENT_TAB_CONTROL)) {
      console.log('DynamicTabs "tabs_conditions" instance not found');
      return false;
    }

    return true;
  }
}
