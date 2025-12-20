"use strict";

var AsproUI = AsproUI || {};

/**
 * AsproUI.DynamicTabsManager
 *
 * @returns {AsproUI.DynamicTabs}
 */

AsproUI.DynamicTabsManager = {
  data: [],
  push: function push(id, instance)
  {
    if (BX.type.isNotEmptyString(id) && instance) {
      let object = {
        id: id,
        instance: instance,
      };

      if (this.getById(id) === null) {
        this.data.push(object);
      } else {
        this.data[0] = object;
      }
    }
  },
  
  getById: function getById(id)
  {
    let result = this.data.filter(function (current)
    {
      return current.id === id || current.id.replace('dynamic_tab_', '') === id;
    });
    return result.length === 1 ? result[0] : null;
  },
  
  getInstanceById: function getInstanceById(id)
  {
    let result = this.getById(id);
    return BX.type.isPlainObject(result) ? result['instance'] : null;
  },
  
  getListIds: function()
  {
     let result = [];
     this.data.forEach(function (item, index) {
        result.push(item.id);
      });
      
      return result
  },
  
  hasInstanceById: function(id)
  {
    if(!this.getInstanceById(id)) {
      return false
    }
    
    return true;
  },
  
  destroy: function destroy(id)
  {
  }
};


/**
 * AsproUI.DynamicTabs
 *
 * @returns {AsproUI.DynamicTabs}
 */
AsproUI.DynamicTabs = function (
  container,
  settings,
  classSettings,
  messages
  )
{
  if (container instanceof HTMLElement) {
    this.container = container;
  } else {
    this.container = document.getElementById(container);
  }

  this.containerWrapperTabs = this.container.querySelector('[tabs-role="tabs-head"]');
  this.containerTabs = this.container.querySelector('[tabs-role="tabs"]');
  this.containerTabViews = this.container.querySelector('[tabs-role="tab-views"]');
  this.toggleFix = this.container.querySelector('[tabs-role="toogle-fix"]');
  this.isIE11 = BX.browser.IsIE11();

  this.tabs = [];

  this.activeTabIndex = 0;

  this._setSettings(settings);
  this._registerTabs();
  this._setClassSettings(classSettings);
  this._setMessages(messages);  
  this._registerEventHandlers();
  this.refreshTabHandlers();
  this._initSettings();
  
  AsproUI.DynamicTabsManager.push(this.container.getAttribute('id'), this);
}

AsproUI.DynamicTabs.prototype = {
  openTab: function (tabIndex, scrollTop)
  {
    let isScrollTop = scrollTop !== undefined ? scrollTop : true;
    
    if (!this.tabs[tabIndex]) {
      return;
    }

    let tab = this.tabs[tabIndex];

    this.closeTabs();

    tab.element.setAttribute('data-active', 'y');
    tab.element.classList.add(this.classes.tabActive);
    tab.element.classList.add('active');

    if (tab.elTabView) {
      tab.elTabView.style.display = 'block';
    }

    this.activeTabIndex = tabIndex;
    
    this.onAfterOpen(
      tab.elName,
      tab.elTabView.querySelector('[tabs-role="tab-view-body"]'),
      tab
      );
    
    if(isScrollTop && this.settings.isScrollTop === true) {
      this.scrollTop(this.container, this.settings.offsetScrollTop);
    }
  },

  closeTabs: function ()
  {
    if (this.tabs.length === 0) {
      return;
    }

    for (let tabIndex = 0; tabIndex < this.tabs.length; tabIndex++) {
      this.tabs[tabIndex].element.removeAttribute('data-active');
      this.tabs[tabIndex].element.classList.remove(this.classes.tabActive);
      this.tabs[tabIndex].element.classList.remove('active');

      if (this.tabs[tabIndex].elTabView) {
        this.tabs[tabIndex].elTabView.style.display = 'none';
      }
    }
  },

  removeTab: function (tabIndex)
  {
    this.tabs[tabIndex].elName.remove();
    this.tabs[tabIndex].elTabView.remove();
    this.tabs[tabIndex].element.remove();

    this.tabs.splice(tabIndex, 1);

    if (this.activeTabIndex == tabIndex || this.tabs.length == 1) {
      this.openTab(this.tabs[tabIndex] ? tabIndex : tabIndex - 1);
    } else {
      this.activeTabIndex = this.activeTabIndex - 1;
    }

    this.refreshTabHandlers();

    this.onAfterRemove(tabIndex);
  },
  
  removeActiveTab: function()
  {
    this.removeTab(this.activeTabIndex);
  },

  addTab: function (id, name, body, dataset, opened)
  {
    let isOpened = opened !== undefined ? opened : true;
    
    if (!this.onBeforeAdd()) {
      return;
    }

    let newTabIndex = this.tabs.length;

    let tabName = name ? name : this.messages.newTabName;
    let elNewTab = this._createElementTab(tabName);
    this.containerTabs.appendChild(elNewTab);

    let tabTitle = this.messages.newTabTitle ? this.messages.newTabTitle : tabName;
    let elTabView = this._createElementTabView(tabTitle, body ? body : '');
    this.containerTabViews.appendChild(elTabView);

    this._registerTab(elNewTab, id, dataset);
    this._registerTabHandler(newTabIndex);
    
    if (isOpened === true) {
      this.openTab(newTabIndex);
    }

    this.onAfterAdd(
      this.tabs[newTabIndex].elName,
      this.tabs[newTabIndex].elTabView.querySelector('[tabs-role="tab-view-body"]'),
      this.getTabByIndex(newTabIndex)
      );
  },
  
  getTabByIndex: function(index)
  {
    if(this.tabs[index]) {
      return this.tabs[index]; 
    }
    
    return false;
  },
  
  getTabIndexById: function(id) 
  {
    for (let tabIndex = 0; tabIndex < this.tabs.length; tabIndex++) {
      if(typeof this.tabs[tabIndex].id != 'undefined' && this.tabs[tabIndex].id == id) {
        return tabIndex;
      }
    }
    
    return false;
  },
  
  getActiveTabIndex: function()
  {
    return this.activeTabIndex;
  },

  refreshTabHandlers: function ()
  {
    this._unRegisterTabHandlers();
    this._registerTabHandlers();
  },

  fixTop: function ()
  {    
    BX.Fix(this.containerWrapperTabs, {type: 'top', limit_node: this.container});

    this.toggleFix.dataset.state = 'on';
    
    BX.setCookie('SMARTSEO_TAB_TOGGLE_FIX', 'Y', {expires: 86400});
  },

  unFixTop: function ()
  {
    BX.UnFix(this.containerWrapperTabs);

    this.toggleFix.dataset.state = 'off';

    BX.setCookie('SMARTSEO_TAB_TOGGLE_FIX', 'N', {expires: 86400});
  },

  scrollTop: function (element, offsetTop, behavior)
  {
     let clientRect = element.getBoundingClientRect();
     let top = window.scrollY + clientRect.top;
     let self = this;
     
    if (this.isIE11) {
      
    } else {
      window.scrollTo({
        top: top - offsetTop,
        behavior: behavior ? behavior : self.settings.scrollBehavior
      });
    }
  },
  
  setHeightWrapperByActiveTab: function() 
  {   
    if(!this.tabs[this.activeTabIndex]) {
      return;
    }
    
    this.containerTabViews.style.height = this.tabs[this.activeTabIndex].elTabView.offsetHeight + 'px';
  },
  
  slideHeightWrapperByActiveTab: function (offset, duration)
  { 
    if(!this.tabs[this.activeTabIndex]) {
      return;
    }
    
    let heightTab = this.tabs[this.activeTabIndex].elTabView.offsetHeight;
    let heightTabViews = this.containerTabViews.offsetHeight;
    let self = this;
    
    let easing = new BX.easing({
      duration: duration ? duration : 600,
      start: {
        height: heightTabViews - (offset ? offset : 41)
      },
      finish: {
        height: heightTab
      },
      transition: BX.easing.transitions.linear,
      step: function (state)
      {
        self.containerTabViews.style.height = state.height + 'px';
      },
      complete: function ()
      {
        self.containerTabViews.style.height = 'auto';
      }
    });
    
    easing.animate();
  },

  onBeforeChange: function (elTabName, elTabViewBody, tabIndex)
  {
    return true;
  },

  onBeforeRemove: function (elTabName, elTabViewBody, tabIndex)
  {
    return true;
  },

  onAfterRemove: function ()
  {
    if (this.tabs.length <= 1) {
      this.tabs[this.activeTabIndex].element.classList.remove(this.classes.tabArrow);
    }
  },

  onBeforeAdd: function ()
  {
    if (this.tabs.length > 0) {
      this.tabs[0].element.classList.add(this.classes.tabArrow);
    }

    return true;
  },

  onAfterAdd: function (elTabName, elTabViewBody, tab)
  {        

  },
  
  onAfterOpen: function (elTabName, elTabViewBody, tab)
  {        

  }, 
  
  _registerEventHandlers: function ()
  {   
    this.toggleFix.addEventListener('click', this._handleToogleFix.bind(this), false);
  },

  _registerTabs: function ()
  {
    const tabs = this.container.querySelectorAll('[tabs-role="tab"]');

    if (tabs.length === 0) {
      return;
    }

    for (let tabIndex = 0; tabIndex < tabs.length; tabIndex++) {
      this._registerTab(tabs[tabIndex]);
    }
  },

  _registerTab: function (elTab, id, dataset)
  {    
    let elTabView = this.container.querySelector(elTab.dataset.target);
    
    this.tabs.push({
      element: elTab,
      id: id,
      dataset: dataset ? dataset : {},
      elName: elTab.querySelector('[tabs-role="tab-name"]'),     
      elClose: elTab.querySelector('[tabs-role="tab-remove"]'),
      elTabViewTitle: elTabView.querySelector('[tabs-role="tab-view-title"]'),
      elTabView: elTabView,
    });
  },

  _registerTabHandlers: function ()
  {
    if (this.tabs.length === 0) {
      return;
    }

    for (let tabIndex = 0; tabIndex < this.tabs.length; tabIndex++) {
      this._registerTabHandler(tabIndex);
    }
  },

  _unRegisterTabHandlers: function ()
  {
    if (this.tabs.length === 0) {
      return;
    }

    for (let tabIndex = 0; tabIndex < this.tabs.length; tabIndex++) {
      this._unRegisterTabHandler(tabIndex);
    }
  },

  _registerTabHandler: function (tabIndex)
  {
    this.tabs[tabIndex].onChange = this._handleChange.bind(this, tabIndex);
    this.tabs[tabIndex].onRemove = this._handleRemove.bind(this, tabIndex);

    this.tabs[tabIndex].elName.addEventListener('click', this.tabs[tabIndex].onChange, false);

    if (this.tabs[tabIndex].elClose) {
      this.tabs[tabIndex].elClose.addEventListener('click', this.tabs[tabIndex].onRemove, false);
    }
  },

  _unRegisterTabHandler: function (tabIndex)
  {
    this.tabs[tabIndex].elName.removeEventListener('click', this.tabs[tabIndex].onChange, false);

    if (this.tabs[tabIndex].elClose) {
      this.tabs[tabIndex].elClose.removeEventListener('click', this.tabs[tabIndex].onRemove, false);
    }
  },

  _handleChange: function (tabIndex, event)
  {
    const elTabName = this.tabs[tabIndex].elName;
    const elTabViewBody = this.tabs[tabIndex].elTabView.querySelector('[tabs-role="tab-view-body"]');

    if (!this.onBeforeChange(elTabName, elTabViewBody, tabIndex)) {
      return;
    }

    this.openTab(tabIndex);
  },

  _handleRemove: function (tabIndex, event)
  {
    const elTabName = this.tabs[tabIndex].elName;
    const elTabViewBody = this.tabs[tabIndex].elTabView.querySelector('[tabs-role="tab-view-body"]');

    if (!this.onBeforeRemove(elTabName, elTabViewBody, tabIndex)) {
      return;
    }

    this.removeTab(tabIndex);

    event.stopPropagation();
  },

  _handleToogleFix: function (event)
  {
    if (this.toggleFix.getAttribute('data-state') == 'on') {
      this.unFixTop();
    } else {
      this.fixTop();
    }
  },

  _createElementTab: function (name)
  {
    let elTab = document.createElement('div');
    elTab.className = this.classes.tab;
    elTab.setAttribute('tabs-role', 'tab');
    elTab.dataset.target =
      '#' + this.container.getAttribute('id') + '_tab_view_' + this.tabs.length;

    let elTabName = document.createElement('div');
    elTabName.className = this.classes.tabName;
    elTabName.setAttribute('tabs-role', 'tab-name');
    elTabName.innerHTML = name;

    let elTabRemove = document.createElement('div');
    elTabRemove.className = this.classes.tabRemove;
    elTabRemove.setAttribute('tabs-role', 'tab-remove');

    elTab.appendChild(elTabRemove);
    elTab.appendChild(elTabName);

    return elTab;
  },

  _createElementTabView: function (name, body)
  {
    let elTabView = document.createElement('div');
    elTabView.className = this.classes.tabView;
    elTabView.setAttribute('tabs-role', 'tab-view');
    elTabView.setAttribute('id',
      this.container.getAttribute('id') + '_tab_view_' + this.tabs.length
      );
    elTabView.style.display = 'none';

    let elTabViewTitle = document.createElement('div');
    elTabViewTitle.className = this.classes.tabViewTitle;
    elTabViewTitle.setAttribute('tabs-role', 'tab-view-title');
    elTabViewTitle.innerHTML = name;

    let elTabViewBody = document.createElement('div');
    elTabViewBody.className = this.classes.tabViewBody;
    elTabViewBody.setAttribute('tabs-role', 'tab-view-body');
    elTabViewBody.innerHTML = body;

    elTabView.appendChild(elTabViewTitle);
    elTabView.appendChild(elTabViewBody);

    return elTabView;
  },

  _initSettings: function ()
  {
    let isCookieFixed = BX.getCookie('SMARTSEO_TAB_TOGGLE_FIX');

    if (isCookieFixed) {
      this.settings.defaultFixed = isCookieFixed === 'Y';
    }

    let self = this;
    if (this.settings.defaultFixed) {      
      setTimeout(function ()
      {
        self.fixTop();
      }, this.settings.timeoutFixed);
    } else {
      this.unFixTop();
    }
  },

  _setSettings: function (settings)
  {
    this.settings = {
      defaultFixed: true,
      isScrollTop: true,
      scrollBehavior: 'instant',
      offsetScrollTop: 60,
      // Eliminate conflicts with standard kernel tabs
      timeoutFixed: 1000,      
    }

    if (settings) {
      this.settings = Object.assign(this.settings, settings)
    }
  },

  _setClassSettings: function (settings)
  {
    this.classes = {
      tab: 'aspro-ui-tabs__tab',
      tabName: 'aspro-ui-tabs__tab__name',
      tabActive: 'aspro-ui-tabs__tab--active',
      tabArrow: 'aspro-ui-tabs__tab--arrow',
      tabRemove: 'aspro-ui-tabs__tab__action-close',
      tabView: 'aspro-ui-tabs__view-tab',
      tabViewTitle: 'aspro-ui-tabs__view-tab__title',
      tabViewBody: 'aspro-ui-tabs__view-tab__body'
    }

    if (settings) {
      this.classes = Object.assign(this.classes, settings)
    }
  },

  _setMessages: function (settings)
  {
    this.messages = {
      newTabName: 'New tab',
      newsTabTitle: 'New tab',
    }

    if (settings) {
      this.messages = Object.assign(this.messages, settings)
    }
  }

}