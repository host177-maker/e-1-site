var FilterTagDetailPage = function (container, settings)
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
  this.elFormSelectType = this.container.querySelector('[page-role="form-field-TYPE"]');
  this.elFormSelectFilterCondition = this.container.querySelector('[page-role="form-field-FILTER_CONDITION_ID"]');
  this.elFormSelectParentFilterCondition = this.container.querySelector('[page-role="form-field-PARENT_FILTER_CONDITION_ID"]');
  
  this.elWrapperParentFilterConditions = this.container.querySelectorAll('[page-role="parent-filter-condition-wrapper"]');
  this.elWrapperSection = this.container.querySelector('[page-role="section-wrapper"]');
  this.elWrapperSectionSelect = this.container.querySelector('[page-role="section-wrapper"] select');
  this.elWrapperRelatedPropertyControls = this.container.querySelector('[page-role="form-wrapper-RELATED_PROPERTY"]');
    
  this.initForms();
  this.deleteFadeInAnimateClass();
  this.registerHandles();
  this.initMenuTagPropertyForForm();
  this.elFormSelectType.onchange();
}

FilterTagDetailPage.prototype = {
  initMenuTagPropertyForForm: function ()
  {
    let elTemplateControls = this.elForm.querySelectorAll('[page-role="control-engine-template"]');

    let self = this;

    for (let i = 0; i < elTemplateControls.length; i++) {
      let controlTemplateEngine = new AsproUI.Form.ControlTemplateEngine(elTemplateControls[i], false, {
        urlMenuResponse: this.urls.MENU_TAG_PROPERTY,
        urlSampleResponse: this.urls.SAMPLE_TAG_PROPERTY,
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
    
    this.elFormSelectType.onchange = function (event) {
      self._displayWrapperParentFilterConditions(false);
      
      self.elWrapperSection.style.display = 'none';
      
      if(this.value == 'SC') {
        self.elWrapperSection.style.display = 'table-row';
        self.elWrapperSectionSelect.setAttribute("required",'');
      } else {
        self.elWrapperSectionSelect.removeAttribute("required");
      }
      
      if(this.value == 'FC') {
        self._displayWrapperParentFilterConditions(true);
        self.elFormSelectParentFilterCondition.setAttribute("required",'');
      } else {
        self.elFormSelectParentFilterCondition.removeAttribute("required");
      }
    }
    
    this.elFormSelectFilterCondition.onchange = function (event)
    {
      if (self.elFormSelectType.value != 'FC') {
        return;
      }
      
      let parentConditionValue = self.elFormSelectParentFilterCondition.value,
        conditionValue = this.value,
        tag = self.elFormFieldID.value;

      BX.ajax({
        url: self.urls.IDENTICAL_PROPERTY,
        data: {
          tag: tag,
          parent_filter_condition: parentConditionValue,
          filter_condition: conditionValue,
          module: 'smartseo'
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function (html)
        {
          self.elWrapperRelatedPropertyControls.innerHTML = html;
        },
        onfailure: function ()
        {
          
        }
      });
    }
    
    this.elFormSelectParentFilterCondition.onchange = function (event)
    {
      if (self.elFormSelectType.value != 'FC') {
        return;
      }
      
      let parentConditionValue = this.value,
        conditionValue = self.elFormSelectFilterCondition.value,
        tag = self.elFormFieldID.value;          

      BX.ajax({
        url: self.urls.IDENTICAL_PROPERTY,
        data: {
          tag: tag,
          parent_filter_condition: parentConditionValue,
          filter_condition: conditionValue,
          module: 'smartseo'
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function (html)
        {
          self.elWrapperRelatedPropertyControls.innerHTML = html;        
        },
        onfailure: function ()
        {
          
        }
      });
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
  
  refrechSelectSitemap: function(listSitemap, sitemapId) 
  {
    if(!this.elSelectSitemap || !listSitemap || !sitemapId) {
      return;
    }
    
    listSitemap.forEach(function (name, index) {
      console.log(index, name);
    });
    
    this.elSelectSitemap.innerHTML = '';    
  },

  _validateParams: function ()
  {
    if (!this.settings.selectors) {
      console.log('FilterTagDetailPage: Array selectors expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('FORM')) {
      console.log('FilterTagDetailPage: Selector FORM expected')
      return false;
    }
    
    if (!this.settings.selectors.hasOwnProperty('PARENT_GRID_ID')) {
      console.log('FilterTagDetailPage: Selector PARENT_GRID_ID expected')
      return false;
    }

    if (!this.settings.selectors.hasOwnProperty('PARENT_TAB_CONTROL')) {
      console.log('FilterTagDetailPage: Selector PARENT_TAB_CONTROL expected')
      return false;
    }

    if (!AsproUI.DynamicTabsManager.hasInstanceById(this.settings.selectors.PARENT_TAB_CONTROL)) {
      console.log('DynamicTabs "tabs_tags" instance not found');
      return false;
    }

    return true;
  },
  
  _displayWrapperParentFilterConditions: function(isVisible)
  {    
    if(!this.elWrapperParentFilterConditions) {
      return;
    }
    
    for (let i = 0; i < this.elWrapperParentFilterConditions.length; i++) {
       if(isVisible === true) {
         this.elWrapperParentFilterConditions[i].style.display = 'table-row';
       } else {
         this.elWrapperParentFilterConditions[i].style.display = 'none';
       }
    }
  }
}
