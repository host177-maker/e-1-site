"use strict";

var contextMenuHandler = {};
contextMenuHandler.delete = function (){}

let engineFormProperty = {};
engineFormProperty.showMenu = function(){}
engineFormProperty.initControlTemplateEngines = function ()
{
  let elMainPageContainer = document.getElementById('seo_text_detail'),
    elSeoTextForm = elMainPageContainer.querySelector('#form_seo_text_detail'),
    elIblockField = elSeoTextForm.querySelector('[data-field="iblock"]'),
    elIblockSectionsField = elSeoTextForm.querySelector('[data-field="iblock_sections"]'),
    elPropertyForm = document.getElementById('form_seo_text_properties'),
    elTemplateControls = elPropertyForm.querySelectorAll('[page-role="control-engine-template"]');

  if (!phpObjectSeoTextSection.urls.MENU_SEO_PROPERTY) {
    return;
  }

  let _getOptionSelected = function (options)
  {
    let result = [];
    for (var i = 0; i < options.length; i++) {
      if (options[i].selected) {
        result.push(options[i].value);
      }
    }

    return result;
  }

  for (let i = 0; i < elTemplateControls.length; i++) {
    if(elTemplateControls[i].getAttribute('control-template-engine') == 'init') {
      continue;
    }
    
    let controlTemplateEngine = new AsproUI.Form.ControlTemplateEngine(elTemplateControls[i], false, {
      urlMenuResponse: phpObjectSeoTextSection.urls.MENU_SEO_PROPERTY,
      urlSampleResponse: phpObjectSeoTextSection.urls.SAMPLE_SEO_PROPERTY,
    });

    controlTemplateEngine.onBeforeSend = function ()
    {
      this.clearMenu();
      this.setAdditionalData({
        iblock_id: elIblockField.value,
        iblock_sections: _getOptionSelected(elIblockSectionsField.options),
      });
    }

    elTemplateControls[i].setAttribute('control-template-engine', 'init');
  }
}
engineFormProperty.togglePropertyControls = function(iblockId)
{
  let elPropertyForm = document.getElementById('form_seo_text_properties'),
    elSectionProperties = elPropertyForm.querySelectorAll('[page-role="section-property"]');
    
  for (let i = 0; i < elSectionProperties.length; i++) {
    if(elSectionProperties[i].dataset.iblock != iblockId) {
      elSectionProperties[i].style.display = 'none';
    } else {
      elSectionProperties[i].style.display = 'block';
    }
  }
}

BX.ready(function ()
{
  let elMainPageContainer = document.getElementById('seo_text_detail');

  (function ()
  {
    let elActionExpandTopInfo = elMainPageContainer.querySelector('[page-role="action-expand-info"]'),
      elContainerExpandTopInfo = elMainPageContainer.querySelector('[page-role="container-expand-info"]');

    if (elActionExpandTopInfo) {
      elActionExpandTopInfo.onclick = function (event)
      {
        if (elContainerExpandTopInfo.offsetHeight == 0) {
          this.classList.add('adm-detail-title-setting-active');
        } else {
          this.classList.remove('adm-detail-title-setting-active');
        }

        AsproUI.AnimateEasy.slideToggle(elContainerExpandTopInfo, function (state, nodeVisible)
        {
          if (nodeVisible) {
            BX.setCookie('SMARTSEO_VISIBLE_TOP_INFO', 'Y', {expires: 86400});
          } else {
            BX.setCookie('SMARTSEO_VISIBLE_TOP_INFO', 'N', {expires: 86400});
          }
        });

        event.preventDefault();
      }
    }
    
  }());

  (function ()
  {
    let elSeoTextForm = elMainPageContainer.querySelector('#form_seo_text_detail'),      
      elSiteField = elSeoTextForm.querySelector('[data-field="site"]'),
      elIblockTypeField = elSeoTextForm.querySelector('[data-field="iblock_type"]'),
      elIblockField = elSeoTextForm.querySelector('[data-field="iblock"]'),
      elIblockSectionsField = elSeoTextForm.querySelector('[data-field="iblock_sections"]');

    elSiteField.onchange = function (event)
    {
      if (!phpObjectSeoTextSection.urls.FIELD_OPTION_IBLOCK_TYPE) {
        return;
      }

      elIblockTypeField.dataset.state = 'loading';
      
      BX.ajax({
        url: phpObjectSeoTextSection.urls.FIELD_OPTION_IBLOCK_TYPE,
        data: {
          site_id: this.value,
          module: 'smartseo'
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function (html)
        {
          elIblockTypeField.innerHTML = html;
          elIblockTypeField.dataset.state = '';

          elIblockField.innerHTML = '';
          elIblockField.append(
            elIblockTypeField.querySelector('[disabled]').cloneNode(true)
            );

          elIblockSectionsField.innerHTML = '';
          elIblockSectionsField.append(
            elIblockTypeField.querySelector('[disabled]').cloneNode(true)
            );
          
          engineFormProperty.togglePropertyControls(elIblockField.value);
        },
        onfailure: function ()
        {
          elIblockTypeField.dataset.state = '';
        }
      });
    }

    elIblockTypeField.onchange = function (event)
    {
      if (!phpObjectSeoTextSection.urls.FIELD_OPTION_IBLOCK) {
        return;
      }

      elIblockField.dataset.state = 'loading';

      BX.ajax({
        url: phpObjectSeoTextSection.urls.FIELD_OPTION_IBLOCK,
        data: {
          site_id: elSiteField.value,
          iblock_type_id: this.value,
          module: 'smartseo'
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function (html)
        {
          elIblockField.innerHTML = html;
          elIblockField.dataset.state = '';

          elIblockSectionsField.innerHTML = '';
          elIblockSectionsField.append(
            elIblockField.querySelector('[disabled]').cloneNode(true)
            );
          
          engineFormProperty.togglePropertyControls(elIblockField.value);
        },
        onfailure: function ()
        {
          elIblockTypeField.dataset.state = '';
        }
      });
    }

    elIblockField.onchange = function (event)
    {
      if (!phpObjectSeoTextSection.urls.FIELD_OPTION_IBLOCK_SECTIONS) {
        return;
      }

      elIblockSectionsField.dataset.state = 'loading';
      
      engineFormProperty.togglePropertyControls(this.value);

      BX.ajax({
        url: phpObjectSeoTextSection.urls.FIELD_OPTION_IBLOCK_SECTIONS,
        data: {
          iblock_id: this.value,
          module: 'smartseo'
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function (html)
        {
          elIblockSectionsField.innerHTML = html;
          elIblockSectionsField.dataset.state = '';
        },
        onfailure: function ()
        {
          elIblockSectionsField.dataset.state = '';
        }
      });
    }
    
  }());

  (function ()
  {
    let elBtnSave = elMainPageContainer.querySelector('[seo-text-form-role="save"]'),
      elBtnApply = elMainPageContainer.querySelector('[seo-text-form-role="apply"]'),
      elBtnCancel = elMainPageContainer.querySelector('[seo-text-form-role="cancel"]'),
      elAlert = elMainPageContainer.querySelector('[seo-text-form-role="alert"]');
      
    let elFormFieldID = elMainPageContainer.querySelector('[page-role="form-field-ID"]');  

    let form = new AsproUI.Form('form_seo_text_detail', {
      elBtnSave: elBtnSave,
      elBtnApply: elBtnApply,
      elBtnCancel: elBtnCancel,
      elAlert: elAlert,
    });

    let pupupConfirm = new AsproUI.Popup.ConfirmAction({
      btnOk: function () {
        if (!AsproUI.FormManager.hasInstanceById('form_seo_text_detail')) {
          return;
        }

        let form = AsproUI.FormManager.getInstanceById('form_seo_text_detail');

        form.elBtnApply.classList.add('ui-btn-wait');
        form.submit('apply');
        this.popupWindow.close();
      }
    }, {
      btnOk: BX.message('SMARTSEO_POPUP_BTN_SAVE_AND_REFRESH'),
      btnCancel: BX.message('SMARTSEO_POPUP_BTN_CANCEL'),
    }, {
      btnOk: 'ui-btn ui-btn-primary-dark',
      content: ''
    });
    
    form.onSuccess = function (data, status, form)
    {      
      if (data.action == 'update_confirm') {
        pupupConfirm.setMessageConfirm(BX.message('SMARTSEO_MESSAGE_UPDATE_CONFIRM').replace(/#COUNT#/gi, data.fields.COUNT));
        pupupConfirm.show();

        this.hideBtnLoading();

        return;
      }
      
      if (!data.redirect) {
         this.hideBtnLoading();
      } else {
         window.location.href = data.redirect;
         
         return;
      }
      
      if (data.result === false) {
        this.showAlert(data.message, this.classes.alertDanger);     

        return;
      }
      
      this.showAlert(data.message, this.classes.alertSuccess);
      
      if(data.fields.ID) {
        elFormFieldID.value = data.fields.ID;
        let url = window.location.search.replace(/(&id=)(\w+|_)/i, '');
        window.history.replaceState(null, null, url + '&id=' + data.fields.ID);
      }     
    }

    if (phpObjectSeoTextSection.aliasProperty) {
      form.mergeForm('form_seo_text_properties', phpObjectSeoTextSection.aliasProperty);
    }

  }());
  
  (function ()
  {
    engineFormProperty.initControlTemplateEngines();

  }());

  (function ()
  {    
    contextMenuHandler.delete = function (elementId)
    {
      if (!phpObjectSeoTextSection.urls.DELETE) {
        return;
      }

      new AsproUI.Popup.Confirm(
        phpObjectSeoTextSection.urls.DELETE, {
          id: elementId,
          module: 'smartseo',
        }, {
        confirmMessage: BX.message('SMARTSEO_POPUP_MESSAGE_DELETE'),
        btnOk: BX.message('SMARTSEO_POPUP_BTN_DELETE'),
        btnCancel: BX.message('SMARTSEO_POPUP_BTN_CANCEL'),
      }
      );
    }
    
  }());
  
  (function ()
  {
     let elPropertyForm = document.getElementById('form_seo_text_properties'),
     elBtnAddProperty = elPropertyForm.querySelector('[page-role="property-action-add"]'),
     elSeoTextForm = elMainPageContainer.querySelector('#form_seo_text_detail'),
     elIblockField = elSeoTextForm.querySelector('[data-field="iblock"]');
     
     elBtnAddProperty.addEventListener('click', function (event)
    {      
      let self = this;   
      let timerId = null;
      let elSectionProperties = elPropertyForm.querySelectorAll('[page-role="section-property"]');
            
      if(self.OPENER instanceof BX.COpener) {       
        self.OPENER = null;
      }
      
      timerId = setTimeout(function(){
        self.classList.add('aspro-ui--loading-white');
      }, 1100);
      
      let sectionPropertyIds = [];
      for (let i = 0; i < elSectionProperties.length; i++) {
        sectionPropertyIds.push(elSectionProperties[i].dataset.id);
      }
      
      BX.ajax({
        url: phpObjectSeoTextSection.urls.MENU_ADD_PROPERTY,
        data: {
          iblock_id: elIblockField.value,
          property_ids: sectionPropertyIds
        },
        method: 'POST',
        dataType: 'json',
        onsuccess: function (data)
        {
          if (data.result == true) {
            BX.adminShowMenu(self, data.menu, {
              active_class: 'ui-btn-active',
            });
          }

          clearTimeout(timerId);
          self.classList.remove('aspro-ui--loading-white');
        },
        onfailure: function ()
        {

        }
      });
    }, false)
  }());
  
  (function ()
  {
    engineFormProperty.showMenu = function (propertyId)
    {
      let elPropertyForm = document.getElementById('form_seo_text_properties'),
      elPropertyContainer = elPropertyForm.querySelector('[page-role="property-container"]'),
      elSeoTextForm = elMainPageContainer.querySelector('#form_seo_text_detail'),
      elIblockField = elSeoTextForm.querySelector('[data-field="iblock"]'),
      elBtnAddProperty = elPropertyForm.querySelector('[page-role="property-action-add"]');
         
      if(elBtnAddProperty.OPENER instanceof BX.COpener) {
        elBtnAddProperty.OPENER.MENU.DIV.remove();
        elBtnAddProperty.OPENER = null;
      }
      
      BX.ajax({
        url: phpObjectSeoTextSection.urls.GET_PROPERTY_CONTROL,
        data: {
          iblock_id: elIblockField.value,
          property_id: propertyId,
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function (html)
        {
          elPropertyContainer.insertAdjacentHTML('beforeend', html);
          engineFormProperty.initControlTemplateEngines();
        },
        onfailure: function ()
        {

        }
      });
    }
    
  }());
  
  (function ()
  {
    let elFormFieldSaveInTable = elMainPageContainer.querySelector('[page-role="form-field-SAVE-IN-TABLE"]'),
      elCheckboxSaveInTable = elMainPageContainer.querySelector('[page-role="save-in-table"]');
      
      if(!(elCheckboxSaveInTable instanceof HTMLElement)) {
        return;
      }
      
      elCheckboxSaveInTable.onchange = function(event) 
      {
        elFormFieldSaveInTable.value = this.checked == true ? 'Y' : 'N';
      }
      
  }());

})

