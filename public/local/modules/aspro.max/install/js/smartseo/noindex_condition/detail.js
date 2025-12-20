var NoindexConditionDetailPage = function (container, settings)
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
  this.elFormFieldType = this.container.querySelector('[page-role="form-field-TYPE"]');
  this.elWrapperValueControl = this.container.querySelector('[page-role="wrapper-value-control"]');
  this.elWrapperPropertiesControl = this.container.querySelector('[page-role="wrapper-properties-control"]');
  
  this.initForms();
  this.deleteFadeInAnimateClass();
  this.registerHandles();
}

NoindexConditionDetailPage.prototype = {
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
    
    this.elFormFieldType.onchange = function(event) 
     {
       if(this.value == 'EP') {
         self.elWrapperValueControl.style.display = 'none';
         self.elWrapperPropertiesControl.style.display = 'table-row';
       } else {
         self.elWrapperValueControl.style.display = 'table-row';
         self.elWrapperPropertiesControl.style.display = 'none';
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
      
      if(data.fields.ID) {
        self.elFormFieldID.value = data.fields.ID;
        self.elFormLabelID.innerHTML = data.fields.ID;
      }
      
      let elementName = data.fields.NAME 
        ? data.fields.NAME 
        : BX.message('SMARTSEO_ELEMENT_DEFAULT_NAME').replace(/#ID#/gi, data.fields.ID);

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
     
        let _contentMessage = BX.message('SMARTSEO_POPUP_MESSAGE_SUCCESS');
        
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
      console.log('NoindexConditionDetailPage: Array selectors expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('FORM')) {
      console.log('NoindexConditionDetailPage: Selector FORM expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('PARENT_GRID_ID')) {
      console.log('NoindexConditionDetailPage: Selector PARENT_GRID_ID expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('PARENT_TAB_CONTROL')) {
      console.log('NoindexConditionDetailPage: Selector PARENT_TAB_CONTROL expected')
      return false;
    }

    if (!AsproUI.DynamicTabsManager.hasInstanceById(this.settings.selectors.PARENT_TAB_CONTROL)) {
      console.log('DynamicTabs "tabs_conditions" instance not found');
      return false;
    }

    return true;
  }
}
