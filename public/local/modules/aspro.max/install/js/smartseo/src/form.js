"use strict";

var AsproUI = AsproUI || {};

/**
 * AsproUI.FormManager
 *
 * @returns {AsproUI.FormManager}
 */

AsproUI.FormManager = {
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
 * AsproUI.Form
 * 
 * @returns {AsproUI.Form}
 */

AsproUI.Form = function (
  form,
  settings,
  classSettings
  )
{
  if (form instanceof HTMLElement) {
    this.form = form;
  } else {
    this.form = document.getElementById(form);
  }
  
  this._hotSubmitAction = '';

  this._setSettings(settings);
  
  this.relationForms = [];

  this._registerControls();
  this._setClassSettings(classSettings);
  this._registerEventHandlers();
  this._autoHeightTextareaControls();

  this.hideAlert();
  
  AsproUI.FormManager.push(this.form.getAttribute('id'), this);
}

AsproUI.Form.prototype = {
  showAlert: function (message, alertClass)
  {
    if (!this.elAlert) {
      return;
    }
    
    if(!message) {
      return;
    }

    let body = this.elAlert.querySelector('[form-role="alert-body"]');

    if (body) {
      body.innerHTML = message;
    } else {
      this.elAlert.innerHTML = message;
    }
    
    if(alertClass) {
      this.elAlert.classList.remove(this.classes.alertDanger, this.classes.alertSuccess);
      this.elAlert.classList.add(alertClass);
    }

    this.elAlert.classList.remove(this.classes.alertUnvisible);    
    this.elAlert.classList.add(this.classes.alertVisible);   
    this.elAlert.removeAttribute('style');

    if (this.settings.scrollToAlert === true) {
      let offset = typeof this.settings.offsetAlertScroll == 'function' 
        ? this.settings.offsetAlertScroll()
        : this.settings.offsetAlertScroll;
        
      let element;  
      if (this.settings.elementToScroll instanceof HTMLElement) {
        element = this.settings.elementToScroll;
      } else {
        element = document.getElementById(this.settings.elementToScroll);
      }  
      
      this._scrollToElement(element ? element : this.elAlert, offset);
    }
  },

  hideAlert: function ()
  {
    if (!this.elAlert) {
      return;
    }

    this.elAlert.classList.remove(this.classes.alertVisible);
    this.elAlert.classList.add(this.classes.alertUnvisible);   
  },
  
  slideUpAlert: function()
  {
    if(typeof AsproUI.AnimateEasy === 'function') {
      AsproUI.AnimateEasy.slideUp(this.elAlert, false, 200);
    } 
  },

  showBtnLoading: function ()
  {
    if (!this.elBtnInitiator) {
      return;
    }

    this.elBtnInitiator.classList.add(this.classes.btnLoading);
  },

  hideBtnLoading: function ()
  {
    if (!this.elBtnInitiator) {
      return;
    }

    this.elBtnInitiator.classList.remove(this.classes.btnLoading);
  },
  
  mergeForm: function(form, aliasData)
  {
    let formNode;
    
    if (form instanceof HTMLElement) {
      formNode = form;
    } else {
      formNode = document.getElementById(form);
    }
    
    this._registerForm(formNode, aliasData);
  },
  
  getFormData: function ()
  {
    let formData = new FormData(this.form);

    if (this.relationForms.length !== 0) {
      this.relationForms.forEach(function (current, index, array)
      {
        let form = new FormData(current.form),
          entries = form.entries(),
          field = entries.next();

        while (undefined !== field.value) {
          formData.append(current.alias + '[' + field.value[0] + ']', field.value[1]);
          
          field = entries.next();
        }
      })
    }

    return formData;
  },
  
  submit: function(action)
  {        
    this._hotSubmitAction = action;
    this._dispatchSubmit();
  },

  onBeforeSubmit: function (event)
  {
    this.showBtnLoading();

    return true;
  },

  _onBeforeSend: function (form)
  {
    if (!this.elBtnInitiator) {
      return false;
    }
      
    let action = '';  
    if(this._hotSubmitAction) {
      action = this._hotSubmitAction;
    } else {
      action = this.elBtnInitiator.dataset.action;
    }
    
    this._setAction(action);
    
    this._hotSubmitAction = '';

    return this.onBeforeSend(form);
  },
  
  onBeforeSend: function (form)
  {
    return true;
  },
  
  _onSuccess: function (response, status, form)
  {
    var data = JSON.parse(response);
    
    if (data.message && Array.isArray(data.message)) {
      let message = '';
      data.message.forEach(function (value)
      {
        message += value + '<br\>';
      });
      data.message = message;
    }
    
    this.onSuccess(data, status, form);
  },

  onSuccess: function (data, status, form)
  {
    if (data.result === false) {
       this.showAlert(data.message);

       this.hideBtnLoading();

       return;
     }

    this.hideAlert();
    
     if (data.redirect) {
       window.location.href = data.redirect;
     }
  },

  onComplete: function (response, status, form)
  {},

  onError: function (message, request)
  {
    this.hideBtnLoading();
  },
  
  onBtnCancelClick: function()
  {
    return true;
  },
    
  _setAction: function(action)
  {
    let elInputAction = this.form.querySelector('[form-role="action"]');

    if (!elInputAction) {
      elInputAction = document.createElement('input');
      elInputAction.setAttribute('type', 'hidden');
      elInputAction.setAttribute('form-role', 'action');
      elInputAction.setAttribute('name', 'action');
      elInputAction.value = action;
      this.form.prepend(elInputAction);
    } else {
      elInputAction.value = action;
    }
  },

  _registerControls: function ()
  {
    this.elBtnInitiator;

    if (typeof this.settings.elBtnSave != 'undefined') {
      if (this.settings.elBtnSave instanceof HTMLElement) {
        this.elBtnSave = this.settings.elBtnSave;
      } else {
        this.elBtnSave = document.getElementById(this.settings.elBtnSave);
      }
    }

    if (typeof this.settings.elBtnApply != 'undefined') {
      if (this.settings.elBtnApply instanceof HTMLElement) {
        this.elBtnApply = this.settings.elBtnApply;
      } else {
        this.elBtnApply = document.getElementById(this.settings.elBtnApply);
      }
    }

    if (typeof this.settings.elBtnCancel != 'undefined') {
      if (this.settings.elBtnCancel instanceof HTMLElement) {
        this.elBtnCancel = this.settings.elBtnCancel;
      } else {
        this.elBtnCancel = document.getElementById(this.settings.elBtnCancel);
      }
    }
    
    if (typeof this.settings.elAlert != 'undefined') {
      if (this.settings.elAlert instanceof HTMLElement) {
        this.elAlert = this.settings.elAlert;
      } else {
        this.elAlert = document.getElementById(this.settings.elAlert);
      }
    }
    
    if(!this.elAlert) {
      this.elAlert = this.form.querySelector('[form-role="alert"]');
    }

    if (!this.elBtnSave) {
      this.elBtnSave = this.form.querySelector('[form-role="save"]');
    }

    if (!this.elBtnApply) {
      this.elBtnApply = this.form.querySelector('[form-role="apply"]');
    }

    if (!this.elBtnCancel) {
      this.elBtnCancel = this.form.querySelector('[form-role="cancel"]');
    }
  },

  _registerEventHandlers: function ()
  {
    if (this.elBtnSave) {
      this.elBtnSave.addEventListener('click', this._handleBtnSave.bind(this), false);
    }

    if (this.elBtnApply) {
      this.elBtnApply.addEventListener('click', this._handleBtnApply.bind(this), false);
    }

    if (this.elBtnCancel) {
      this.elBtnCancel.addEventListener('click', this._handleBtnCancel.bind(this), false);
    }

    this.form.addEventListener('submit', this._handleSubmit.bind(this), false);
  }, 
  
  _registerForm: function (node, alias) {
    this.relationForms.push({
      form: node,
      alias: alias,
    });
  },

  _dispatchSubmit: function ()
  {
    let newEvent;
    if (typeof (Event) === 'function') {
      newEvent = new Event('submit', {cancelable: true});
    } else {
      newEvent = document.createEvent('Event');
      newEvent.initEvent('submit', true, true);
    }

    this.form.dispatchEvent(newEvent);
  },

  _handleBtnSave: function (event)
  {
    this.elBtnInitiator = this.elBtnSave;
    if (this.form.reportValidity() && this.onBeforeSubmit(event)) {
      this.showBtnLoading();

      this._dispatchSubmit();
    }

    event.preventDefault();
  },

  _handleBtnApply: function (event)
  {
    this.elBtnInitiator = this.elBtnApply;
    if (this.form.reportValidity() && this.onBeforeSubmit(event)) {
      this._dispatchSubmit();
    }

    event.preventDefault();
  },

  _handleBtnCancel: function (event)
  {
    this.elBtnInitiator = this.elBtnCancel;
    if (!this.onBeforeSubmit(event)) {
      
      this.onBtnCancelClick();
      
      event.preventDefault();
      return;
    }
    
    this.onBtnCancelClick(event);
  },

  _handleSubmit: function (event)
  {
    event.preventDefault();

    if(!this._onBeforeSend(this.form)) {
      this.hideBtnLoading();
      return;
    }

    let formData = this.getFormData();
    formData.append('module', 'smartseo');

    let request = new XMLHttpRequest();

    request.open(this.form.getAttribute('method'), this.form.getAttribute('action'), true);
    request.setRequestHeader('Accept', 'application/json');
    request.setRequestHeader('Bx-ajax', 'true');
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    let self = this;
    request.onreadystatechange = function ()
    {
      try {
        if (request.readyState < 4) {

        }
        if (request.readyState === 4 && request.status == 200 && request.status < 300) {
          self._onSuccess(request.responseText, request.statusText, self.form);          
        }
      } catch (e) {
        self.onError(e.message, request);
      }

      self.onComplete(request, self.form);

      self._stateLoadingAlert(false);
    }

    this._stateLoadingAlert(true);
    request.send(formData);
  },

  _setSettings: function (settings)
  {
    this.settings = {
      elBtnSave: '',
      elBtnCancel: '',
      scrollToAlert: true,
      elementToScroll: '',
      offsetAlertScroll: 0,
    }

    if (settings) {
      this.settings = Object.assign(this.settings, settings)
    }
  },

  _setClassSettings: function (settings)
  {
    this.classes = {
      alertVisible: 'aspro-ui-form__alert--visible',
      alertUnvisible: 'aspro-ui-form__alert--unvisible',
      alertDanger: 'ui-alert-danger',
      alertSuccess: 'ui-alert-success',
      alertLoading: 'aspro-ui-form__alert--loading',
      btnLoading: 'ui-btn-wait',
    }

    if (settings) {
      this.classes = Object.assign(this.classes, settings)
    }
  },

  _setMessages: function (settings)
  {
    this.messages = {

    }

    if (settings) {
      this.messages = Object.assign(this.messages, settings)
    }
  },

  _scrollToElement: function (element, offsetTop)
  {
     let clientRect = element.getBoundingClientRect();
     let top = window.scrollY + clientRect.top;

    window.scrollTo({
      top: top - element.offsetHeight - offsetTop,
      behavior: 'smooth'
    });
  },

  _stateLoadingAlert: function (state)
  {
    if (!this.elAlert) {
      return;
    }

    if (state === true) {
      this.elAlert.classList.add(this.classes.alertLoading);
    } else {
      this.elAlert.classList.remove(this.classes.alertLoading);
    }
  },  
  _autoHeightTextareaControls: function()
  {
      let elTextareaControls = this.form.querySelectorAll('textarea');
      
      if(!elTextareaControls) {
        return;
      }
      
      for (let i = 0; i < elTextareaControls.length; i++) {       
        elTextareaControls[i].style.height = 'auto'; 
        elTextareaControls[i].style.height = elTextareaControls[i].scrollHeight + 'px'; 
      }
  }
  
}

AsproUI.Form.setHiddenInput = function(formId, name, value) 
{
  let form = document.getElementById(formId);
  
  if(!form) {
    return;
  }
  
  let elInput = form.querySelector('[name="' + name + '"]');
  
  if(!elInput) {
    elInput = document.createElement('input');
    elInput.setAttribute('type', 'hidden');
    elInput.setAttribute('name', name);
    elInput.value = value;
  } else {
    elInput.value = value;
  }
  
  form.prepend(elInput);  
}

/**
 * AsproUI.Form.ControlTemplateEngine
 * 
 * @returns {AsproUI.Form.ControlTemplateEngine}
 */

AsproUI.Form.ControlTemplateEngine = function (
  container,
  menuItems,
  settings,
  classSettings
  )
{
  if (container instanceof HTMLElement) {
    this.container = container;
  } else {
    this.container = document.getElementById(container);
  }

  if (menuItems) {
    this.menu = menuItems;
  }
  
  this.additionalData = [];

  this.elMenu = this.container.querySelector('[control-role="menu"]');
  this.elInput = this.container.querySelector('[control-role="input"]');
  this.elInputWrapper = this.container.querySelector('[control-role="input-wrapper"]');
  this.elEditCheckbox = this.container.querySelector('[control-role="edit-checkbox"]');
  this.elEditCheckboxLabel = this.container.querySelector('[control-role="edit-checkbox-label"]');
  this.elSample = this.container.querySelector('[control-role="sample"]');
  
  if (!this._validate()) {
    return;
  }
  
  if (!this.elInput.getAttribute('id')) {
    this.elInput.setAttribute('id', this._getUniqueId());
  }
      
  this._scriptForBXHtmlEditor();
  
  this._setSettings(settings);
  this._setClassSettings(classSettings);
  this._stateDisabled();

  this._registerEventHandlers();
}

AsproUI.Form.ControlTemplateEngine.prototype = {
  setAdditionalData: function(data)
  {
    this.additionalData = data;
  },
  
  getAdditionalData: function()
  {
    return this.additionalData ? this.additionalData : [];
  },
  
  clearMenu: function()
  {
    this.menu = null;
  },
  
  onBeforeSend: function ()
  {
    
  },
  
  _validate: function ()
  {
    if (!this.elMenu) {
      console.log('FormControlTemplateEngineAsproUI: Attribute [control-role="menu"] expected');
      return false;
    }
    if (!this.elInput) {
      console.log('FormControlTemplateEngineAsproUI: Attribute [control-role="input"] expected');
      return false;
    }

    return true;
  },
  
  _scriptForBXHtmlEditor: function ()
  {
    if(!this.elSample) {
      return;
    }
    
    let toggleEditorId = '#' + this.elInput.getAttribute('id') + '_editor';
    let elToggleEditor = this.container.querySelector(toggleEditorId);

    if (!elToggleEditor) {
      return;
    }
    
    if(elToggleEditor.checked) {
      this.elSample.style.display = 'none';
      
      if(this.elEditCheckboxLabel) {
        this.elEditCheckboxLabel.style.display = 'none';
      }
    }

    let self = this;
    elToggleEditor.addEventListener('change', function ()
    {
      if(this.checked) {
        self.elSample.style.display = 'none';        
        if(self.elEditCheckboxLabel) {
          self.elEditCheckboxLabel.style.display = 'none';
        }        
      } else {
        self.elSample.style.display = 'block';
        if(self.elEditCheckboxLabel) {
          self.elEditCheckboxLabel.style.display = 'inline-block';
        }
      }
      
      if(this.checked == false) {
        self._dispatchChangeInput();
      }
    }, false);
  },

  _stateDisabled: function (state)
  {
    let disabled = state;

    if (typeof state == 'undefined') {
      disabled = this.container.dataset.state === 'disabled'
        || this.container.dataset.state === 'false'
        ? true
        : false;
    }

    if (typeof state == 'undefined' && this.elEditCheckbox) {
      this.elEditCheckbox.checked = !disabled;
    }

    if (disabled) {
      this.elMenu.classList.add(this.classes.disableMenu);
      this.elInput.classList.add(this.classes.disableInput);
      this.elMenu.disabled = true;
      this.elInput.disabled = true;
    } else {
      this.elMenu.classList.remove(this.classes.disableMenu);
      this.elInput.classList.remove(this.classes.disableInput);
      this.elMenu.disabled = false;
      this.elInput.disabled = false;
    }
  },

  _registerEventHandlers: function ()
  {
    if (this.elEditCheckbox) {
      this.elEditCheckbox.addEventListener('change', this._handleEditCheckbox.bind(this), false);
    }

    this.elMenu.addEventListener('click', this._handleMenu.bind(this), false);
    this.elInput.addEventListener('change', this._handleInput.bind(this), false);
    this.elInput.addEventListener('keypress', this._handleInput.bind(this), false);
    this.elInput.addEventListener('keyup', this._handleInput.bind(this), false);     
    
    if(this.elInputWrapper) {
      this.elInputWrapper.addEventListener('click', this._handleInputWrapper.bind(this), false);
    }
  },

  _handleEditCheckbox: function (event)
  {
    this._stateDisabled(!this.elEditCheckbox.checked);
  },

  _handleMenu: function (event)
  {
    if (this.elMenu.disabled) {
      return;
    }
    
    this.onBeforeSend();

    if (this.settings.urlMenuResponse && !this.menu) {
      this.elMenu.classList.add(this.classes.loadingMenu);

      let data = Object.assign({
        control: this.elInput.getAttribute('id'),
      }, this.container.dataset, this.getAdditionalData(), {module: 'smartseo'});

      let self = this;
      BX.ajax({
        url: this.settings.urlMenuResponse,
        data: data,
        method: 'POST',
        dataType: 'json',
        onsuccess: function (data)
        {
          if(data.result == true) {
            self.menu = data.menu;
            self.elMenu.OPENER = false;
            BX.adminShowMenu(self.elMenu, data.menu, {
              active_class: self.classes.activeMenu,
            });
          }

          self.elMenu.classList.remove(self.classes.loadingMenu);
        },
        onfailure: function ()
        {

        }
      });
    }
  },

  _handleInput: function (event)
  {
    if(this.elSample && this.settings.urlSampleResponse) {
      if (this.timer) {
          clearTimeout(this.timer);
      }      
      
      this.onBeforeSend();
      
      let self = this;
      this.timer = setTimeout(function ()
      {
        let data = Object.assign({
          template: self.elInput.value,
        }, self.container.dataset, self.getAdditionalData(), {module: 'smartseo'});


        BX.ajax({
          url: self.settings.urlSampleResponse,
          data: data,
          method: 'POST',
          dataType: 'html',
          onsuccess: function (html)
          {
            if(self.elSample.tagName == 'IFRAME') { 
              let document = self.elSample.contentWindow.document;
              document.open();
              document.write(html);
              document.close();
              
            } else {
              self.elSample.innerHTML = html;
            }            
          },
          onfailure: function ()
          {

          }
        });
      }, self.settings.timeoutSampleResponse);

    }
  },
  
  /** Not used **/
  _getGeneratedPageURL: function (html, css, js)
  {
    const getBlobURL = function(code, type) {
      const blob = new Blob([code], {type: type})
      return URL.createObjectURL(blob)
    }

    const cssURL = getBlobURL(css, 'text/css')
    const jsURL = getBlobURL(js, 'text/javascript')

    const source = '<html><head><body>' + html + '</body></html>';
    
    return getBlobURL(source, 'text/html')
  },
  
  _handleInputWrapper: function (event)
  {
    if(this.elEditCheckbox) {
      this.elEditCheckbox.checked = true;
      this._stateDisabled(!this.elEditCheckbox.checked);
    }    
  },

  _getUniqueId: function ()
  {
    return 'control_' + Math.random().toString(36).substr(4, 12);
  },
  
  _dispatchChangeInput: function ()
  {
    let newEvent;
    if (typeof (Event) === 'function') {
      newEvent = new Event('change');
    } else {
      newEvent = document.createEvent('Event');
      newEvent.initEvent('change', true, true);
    }
    
    this.elInput.dispatchEvent(newEvent);
  },

  _setSettings: function (settings)
  {
    this.settings = {
      urlMenuResponse: '',
      urlSampleResponse: '',
      timeoutSampleResponse: 400
    }

    if (settings) {
      this.settings = Object.assign(this.settings, settings)
    }
  },

  _setClassSettings: function (settings)
  {
    this.classes = {
      disableInput: 'aspro-ui--disable',
      disableMenu: 'ui-btn-disabled',
      activeMenu: 'ui-btn-active',
      loadingMenu: 'aspro-ui--loading-white',
    }

    if (settings) {
      this.classes = Object.assign(this.classes, settings)
    }
  }
}

AsproUI.Form.ControlTemplateEngine.valueInputEntry = function (selector, value, byCaretPosition)
{
  let control = document.getElementById(selector);

  if (!(control instanceof HTMLElement)) {
    control = document.querySelector('[name=' + selector + ']');
  }

  if (!control) {
    return;
  }  
  
  let caret = this.getCaretPosition(control);
  let controlName = control.getAttribute('name');
  
  if(byCaretPosition === false || byCaretPosition === 0) {
    control.value = value;
  } else {    
    control.value = control.value.substring(0, caret.start)
      + value + control.value.substring(caret.end, control.value.length);
  }

  try {
    let editor = window.BXHtmlEditor.Get(controlName);

    if (editor) {
      editor.SetContent(control.value, true);
    }

  } catch (e) {
    console.log('FormControlTemplateEngineAsproUI: ' + e.message);
  }

  let newEvent;
  if (typeof (Event) === 'function') {
    newEvent = new Event('change');
  } else {
    newEvent = document.createEvent('Event');
    newEvent.initEvent('change', true, true);
  }

  control.dispatchEvent(newEvent);
}

AsproUI.Form.ControlTemplateEngine.getCaretPosition = function (ctrl)
{
  // IE < 9 Support 
  if (document.selection) {
    ctrl.focus();
    var range = document.selection.createRange();
    var rangelen = range.text.length;
    range.moveStart('character', -ctrl.value.length);
    var start = range.text.length - rangelen;
    return {
      start: start,
      end: start + rangelen
    };
  } // IE >=9 and other browsers
  else if (ctrl.selectionStart || ctrl.selectionStart == '0') {
    return {
      start: ctrl.selectionStart,
      end: ctrl.selectionEnd
    };
  } else {
    return {
      start: 0,
      end: 0
    };
  }
}

AsproUI.Form.ControlTemplateEngine.setCaretPosition = function (ctrl, start, end)
{
  // IE >= 9 and other browsers
  if (ctrl.setSelectionRange) {
    ctrl.focus();
    ctrl.setSelectionRange(start, end);
  }
  // IE < 9 
  else if (ctrl.createTextRange) {
    var range = ctrl.createTextRange();
    range.collapse(true);
    range.moveEnd('character', end);
    range.moveStart('character', start);
    range.select();
  }
}