"use strict";

var AsproUI = AsproUI || {};

/**
 * AsproUI.ContextMenuInnerGrid
 * 
 * @returns {AsproUI.ContextMenuInnerGrid}
 */

AsproUI.ContextMenuInnerGrid = function (
  gridId,
  dynamicTabsId,
  urlActions,
  messages
  )
{
  this.gridId = gridId;
  this.dynamicTabsId = dynamicTabsId;
  this.urls = urlActions;
  this.messages = messages;
};

AsproUI.ContextMenuInnerGrid.prototype = {
  register: function(
    gridId,
    dynamicTabsId,
    urlActions,
    messages
    )
  {
    this.gridId = gridId;
    this.dynamicTabsId = dynamicTabsId;
    this.urls = urlActions;
    this.messages = messages;
  },
  
  actionDataPush: function (url, data)
  {
    this._sendRequest(url, data);
  },
  
  actionValuePush: function (url, name, value)
  {
    let data = [];
    
    data[name] = value;
    
    this._sendRequest(url, data);
  },
  
  actionEdit: function (id, name)
  {
    let dynamicTabs;

    dynamicTabs = this._getDynamicTabsInstance(this.dynamicTabsId);

    if (!dynamicTabs) {
      return;
    }

    let hasTabIndex = dynamicTabs.getTabIndexById(this.dynamicTabsId + '_' + id);

    if (hasTabIndex !== false) {
      dynamicTabs.openTab(hasTabIndex);
    } else {
      dynamicTabs.addTab(this.dynamicTabsId + '_' + id, name, '', {
        ID: id,
        NAME: name
      });
    }

    return false;
  },

  actionDeactivate: function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_DEACTIVATE')) {
      console.log('Action "ACTION_DEACTIVATE" not found');

      return;
    }

    this._sendRequest(this.urls.ACTION_DEACTIVATE, {
        id: id,
        module: 'smartseo',
    });
  },

  actionActivate: function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_ACTIVATE')) {
      console.log('Action "ACTION_ACTIVATE" not found');
      
      return;
    }

    this._sendRequest(this.urls.ACTION_ACTIVATE, {
        id: id,
        module: 'smartseo',
    });
  },
  
  actionCopy: function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_COPY')) {
      console.log('Action "ACTION_COPY" not found');
      
      return;
    }

    this._sendRequest(this.urls.ACTION_COPY, {
        id: id,
        module: 'smartseo',
    });
  },

  actionDelete: function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_DELETE')) {
      console.log('Action "ACTION_DELETE" not found');
      
      return;
    }
    
    let grid = this._getGridInstance(this.gridId);

    let popupConfirm = new AsproUI.Popup.Confirm(this.urls.ACTION_DELETE, 
        {
          id: id,
          module: 'smartseo',
        }, 
        {
          confirmMessage: this.messages.popupMessageDelete,
          btnOk: this.messages.popupBtnDelete,
          btnCancel: this.messages.popupBtnCancel,
        },
        this.gridId
    );

    popupConfirm.onSuccess = function(data) {
      grid.reloadTable(null, null);
    }
  },
  
  actionOpenOnSite: function (href)
  {    
    if (!href) {
      return;
    }

    window.open(
      href,
      '_blank'
      );
  },

  onSendSuccess: function ()
  {

  },  

  _sendRequest: function (url, data, callbackFunction)
  {
    let grid = this._getGridInstance(this.gridId);

    if (!grid) {
      return;
    }

    let self = this;

    BX.ajax({
      url: url,
      data: data,
      method: 'POST',
      dataType: 'json',
      onsuccess: function (data)
      {
        if (data.result == true) {
          grid.reloadTable(null, null);
          
          self.onSendSuccess(data);
          
          if (typeof callbackFunction == 'function') {
            callbackFunction(data);
          }
        } else {
          self._showPopupAlert(data.message);
        }
      },
      onfailure: function ()
      {

      }
    });
  },

  _getGridInstance: function (gridId)
  {
    let gridObject = BX.Main.gridManager.getById(gridId);

    if (!gridObject.hasOwnProperty('instance')) {
      console.log('Grid instance not found');
      return false;
    }

    return gridObject.instance;
  },
  
  _getDynamicTabsInstance: function(dynamicTabsId)
  {
    let dynamicTabsObject = AsproUI.DynamicTabsManager.getInstanceById(dynamicTabsId);

    if (!dynamicTabsObject) {
      console.log('DynamicTabs "' + dynamicTabsId + '" instance not found');
      
      return false;
    }

    return dynamicTabsObject;
  },

  _showPopupAlert: function (message)
  {
      new AsproUI.Popup.Alert(message, '',
      {
        btnClose: this.messages.popupBtnClose,
      },
      this.gridId
    );
  }
}

/**
 * AsproUI.ContextMenuMainGrid
 * 
 * @returns {AsproUI.ContextMenuMainGrid}
 */

AsproUI.ContextMenuMainGrid = function (
  gridId,
  urlActions,
  messages
  )
{
  this.gridId = gridId;
  this.urls = urlActions;
  this.messages = messages;
};

AsproUI.ContextMenuMainGrid.prototype = {
  register: function(
    gridId,
    urlActions,
    messages
    )
  {
    this.gridId = gridId;
    this.urls = urlActions;
    this.messages = messages;
  },
  
  actionDataPush: function (url, data)
  {
    this._sendRequest(url, data);
  },
  
  actionValuePush: function (url, name, value)
  {
    let data = [];
    
    data[name] = value;
    
    this._sendRequest(url, data);
  },
  
  actionEdit: function (url)
  {      
    if(url) {
      window.location.href = url;
      
      return;
    }
    
    if (!this.urls.hasOwnProperty('ACTION_EDIT')) {
      console.log('Action "ACTION_EDIT" not found');

      return;
    }
    
    window.location.href = this.urls.ACTION_EDIT;

    return false;
  },

  actionDeactivate: function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_DEACTIVATE')) {
      console.log('Action "ACTION_DEACTIVATE" not found');

      return;
    }

    this._sendRequest(this.urls.ACTION_DEACTIVATE, {
        id: id,
        module: 'smartseo',
    });
  },

  actionActivate: function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_ACTIVATE')) {
      console.log('Action "ACTION_ACTIVATE" not found');
      
      return;
    }

    this._sendRequest(this.urls.ACTION_ACTIVATE, {
        id: id,
        module: 'smartseo',
    });
  },
  
  actionCopy: function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_COPY')) {
      console.log('Action "ACTION_COPY" not found');
      
      return;
    }

    this._sendRequest(this.urls.ACTION_COPY, {
        id: id,
        module: 'smartseo',
    });
  },

  actionDelete: function (id)
  {
    if (!this.urls.hasOwnProperty('ACTION_DELETE')) {
      console.log('Action "ACTION_DELETE" not found');
      
      return;
    }
    
    let grid = this._getGridInstance(this.gridId);

    let popupConfirm = new AsproUI.Popup.Confirm(this.urls.ACTION_DELETE, 
        {
          id: id,
          module: 'smartseo',
        }, 
        {
          confirmMessage: this.messages.popupMessageDelete,
          btnOk: this.messages.popupBtnDelete,
          btnCancel: this.messages.popupBtnCancel,
        },
        this.gridId
    );

    popupConfirm.onSuccess = function(data) {
      grid.reloadTable(null, null);
    }
  },

  onSendSuccess: function ()
  {

  },  

  _sendRequest: function (url, data, callbackFunction)
  {
    let grid = this._getGridInstance(this.gridId);

    if (!grid) {
      return;
    }

    let self = this;

    BX.ajax({
      url: url,
      data: data,
      method: 'POST',
      dataType: 'json',
      onsuccess: function (data)
      {
        if (data.result == true) {
          grid.reloadTable(null, null);
          
          self.onSendSuccess(data);
          
          if (typeof callbackFunction == 'function') {
            callbackFunction(data);
          }
        } else {
          self._showPopupAlert(data.message);
        }
      },
      onfailure: function ()
      {

      }
    });
  },

  _getGridInstance: function (gridId)
  {
    let gridObject = BX.Main.gridManager.getById(gridId);

    if (!gridObject.hasOwnProperty('instance')) {
      console.log('Grid instance not found');
      return false;
    }

    return gridObject.instance;
  },

  _showPopupAlert: function (message)
  {
      new AsproUI.Popup.Alert(message, '',
      {
        btnClose: this.messages.popupBtnClose,
      },
      this.gridId
    );
  }
}