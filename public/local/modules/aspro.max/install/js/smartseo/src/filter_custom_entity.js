"use strict";

var AsproUI = AsproUI || {};

AsproUI.FilterCustomEntity = function (params)
{  
  this.fieldId = params.fieldId;
  this.url = params.url;
  this.data = params.data;
  this.items = [];

  this.init();
};

AsproUI.FilterCustomEntity.prototype = {
  init: function ()
  {
    BX.addCustomEvent(window, 'BX.Main.Filter:customEntityFocus', BX.proxy(this._selectorOpen, this));
    BX.addCustomEvent(window, 'BX.Main.Filter:customEntityBlur', BX.proxy(this._closeOpen, this));
  },

  _selectorOpen: function (control)
  {
    if (control.getId() !== this.fieldId) {
      return;
    }

    this.control = control;
    this._showPopupWindow();
  },

  _closeOpen: function (control)
  {
    if (control.getId() !== this.fieldId) {
      return;
    }
    
    this.control = control;
    this._closePopupWindow();
  },

  _showPopupWindow: function (html)
  {   
     let self = this;
     let timerId = null;
    
     if (!(this.popup instanceof BX.PopupWindow)) {
      this.popup = new BX.PopupWindow('popup_filter_custom_entity_' + this.fieldId, null, {
        closeIcon: false,
        autoHide: true,
        zIndex: 100,
        offsetLeft: 0,
        offsetTop: 3,
        draggable: false,
      });
    }
    
    if(this.timerId) {
      clearTimeout(this.timerId);
    }
      
    if(this.popup.isShown()) {      
      this.timerId = setTimeout(function(){
        self.popup.close();
      }, 100);
      
      return;
    }
    
    let wait = BX.showWait(self.control.getField());

    this.popup.setBindElement(this.control.getField());
    this.popup.setWidth(515);
    this.popup.setMinHeight(50);
    this.popup.setMaxHeight(260);
    this.popup.setAnimation('fading-slide');
    this.popup.setContent('<div></div>');
    
    this._popupAdjustPosition();
    
    BX.ajax({
      url: this.url,
      data: this.data,
      method: 'POST',
      dataType: 'html',
      onsuccess: function (html)
      {
        let parser = new DOMParser()
        let elDocument = parser.parseFromString(html, 'text/html');
        let elPopupContent = elDocument.querySelector('#popup_filter_custom_entity');   
        
        self._removeAllItems();                  
        self._addItemsByContent(elPopupContent);        
        self.popup.setContent(elPopupContent);         
        
        self.control.setPopupContainer(self.popup.getPopupContainer());        
         
        BX.closeWait(self.control.getField(), wait);
        
        self.popup.show();
      },
      onfailure: function ()
      {

      }
    });    
  },

  _closePopupWindow: function ()
  {
    if ((this.popup instanceof BX.PopupWindow)) {
       this.popup.close();
    }   
  },
  
  _addItemsByContent: function (content)
  {
    let elItems = content.querySelectorAll('[custom-entity="item"]');
    
    let self = this;
    for (let index = 0; index < elItems.length; index++) {      
      this._registerItem(elItems[index], index, {
        label: elItems[index].dataset.name,
        value: elItems[index].dataset.id,
      });
    }    
  },
  
  _removeAllItems: function ()
  {    
    for (let index = 0; index < this.items.length; index++) {      
      this._unregisterItemHandler(index);
    }    
    
    this.items = [];
  },
  
  _registerItem: function(elItem, index, dataset)
  {      
    let controlCurrentValues = this._arrayColumn(this.control.getCurrentValues(), 'value');
    
    let isSelected = false;
    for (let index = 0; index < controlCurrentValues.length; index++) {
      if(controlCurrentValues[index] == dataset.value) {
        
        isSelected = true;
      }
    }
    
    if(isSelected == true) {
      elItem.classList.add('main-ui-checked');
      elItem.dataset.select = 'Y';
    }
        
    this.items.push({
      element: elItem,
      select: isSelected,
      dataset: dataset ? dataset : {},    
    });
    
    this._registerItemHandler(index);
  },
  
  _registerItemHandler: function (index)
  {
    this.items[index].onClick = this._handleClick.bind(this, index);
    this.items[index].element.addEventListener('click', this.items[index].onClick, false);
  },
  
  _unregisterItemHandler: function (index)
  {
    this.items[index].element.removeEventListener('click', this.items[index].onClick, false);
  },
  
  _handleClick: function (index, event)
  {
    let item = this.items[index];
    
    if(item.element.dataset.select == 'Y') {
      item.element.classList.remove('main-ui-checked');
      item.element.dataset.select = 'N';
      item.select = false;
    } else {
      item.element.classList.add('main-ui-checked');
      item.element.dataset.select = 'Y';
      item.select = true;
    }    
   
    this._setControlMultipleData();
    this._popupAdjustPosition();
  },
  
  _popupAdjustPosition: function()
  {
    let pos = BX.pos(this.control.getField());
    pos.forceBindPosition = true;
    this.popup.adjustPosition(pos);
  },
  
  _setControlMultipleData: function()
  {
    let data = [];
    for (let index = 0; index < this.items.length; index++) {      
      if(this.items[index].select == true) {
        data.push({
          label: this.items[index].dataset.label,
          value: this.items[index].dataset.value,
        });
      }
    }
    
    this.control.setMultipleData(data);
  },
  
  _arrayColumn: function (array, columnName)
  {
    return array.map(function (value, index)
    {
      return value[columnName];
    });
  }
 }