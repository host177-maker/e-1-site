"use strict";

var contextMenuHandler = {};
contextMenuHandler.delete = function (){}
contextMenuHandler.copy = function (){}

let engineForm = {};
engineForm.loadValueUrlTemplateControl = function (siteValue, iblockValue, sectionOptions, isSectionAll)
{
  if(!phpObjectNoindexRule.urls.FIELD_VALUE_URL_TEMPLATE) {
    console.log('Action "FIELD_VALUE_URL_TEMPLATE" not found');
    return;
  }
  
  let elMainPageContainer = document.getElementById('noindex_rule_detail'),
    elNoindexRuleForm = elMainPageContainer.querySelector('#form_noindex_rule_detail'),
    elUrlTemplateField = elNoindexRuleForm.querySelector('[data-field="url_template"]');
  
  elUrlTemplateField.dataset.state = 'loading';
  
  BX.ajax({
    url: phpObjectNoindexRule.urls.FIELD_VALUE_URL_TEMPLATE,
    data: {
      site_id: siteValue,
      iblock_id: this._isInt(iblockValue) ? iblockValue : 0,
      iblock_sections: this._getOptionSelected(sectionOptions),
      iblock_section_all: isSectionAll,
      module: 'smartseo'
    },
    method: 'POST',
    dataType: 'json',
    onsuccess: function (data)
    {
      if(data.result == true) {
        elUrlTemplateField.value = data.value;
      }
      
      elUrlTemplateField.dataset.state = '';
    },
    onfailure: function ()
    {
      elUrlTemplateField.dataset.state = '';
    }
  });

}
engineForm._getOptionSelected = function (options)
{
  let result = [];
  for (var i = 0; i < options.length; i++) {
    if (options[i].selected) {     
      if(this._isInt(options[i].value)) {
        result.push(options[i].value);
      }
    }
  }

  return result;
}

engineForm._isInt = function(value) {
  var x = parseFloat(value);
  return !isNaN(value) && (x | 0) === x;
}

BX.ready(function ()
{
  let elMainPageContainer = document.getElementById('noindex_rule_detail');

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
    let elNoindexRuleForm = elMainPageContainer.querySelector('#form_noindex_rule_detail'),      
      elSiteField = elNoindexRuleForm.querySelector('[data-field="site"]'),
      elIblockTypeField = elNoindexRuleForm.querySelector('[data-field="iblock_type"]'),
      elIblockField = elNoindexRuleForm.querySelector('[data-field="iblock"]'),
      elIblockSectionsField = elNoindexRuleForm.querySelector('[data-field="iblock_sections"]'),
      elIblockSectionAllField = elNoindexRuleForm.querySelector('[data-field="iblock_section_all"]');

    elSiteField.onchange = function (event)
    {
      if (!phpObjectNoindexRule.urls.FIELD_OPTION_IBLOCK_TYPE) {
        return;
      }

      elIblockTypeField.dataset.state = 'loading';
      
      BX.ajax({
        url: phpObjectNoindexRule.urls.FIELD_OPTION_IBLOCK_TYPE,
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
        },
        onfailure: function ()
        {
          elIblockTypeField.dataset.state = '';
        }
      });
    }

    elIblockTypeField.onchange = function (event)
    {
      if (!phpObjectNoindexRule.urls.FIELD_OPTION_IBLOCK) {
        return;
      }

      elIblockField.dataset.state = 'loading';

      BX.ajax({
        url: phpObjectNoindexRule.urls.FIELD_OPTION_IBLOCK,
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
        },
        onfailure: function ()
        {
          elIblockTypeField.dataset.state = '';
        }
      });
    }

    elIblockField.onchange = function (event)
    {
      if (!phpObjectNoindexRule.urls.FIELD_OPTION_IBLOCK_SECTIONS) {
        return;
      }

      elIblockSectionsField.dataset.state = 'loading';
      
      engineForm.loadValueUrlTemplateControl(elSiteField.value, elIblockField.value, [], elIblockSectionAllField.checked);

      BX.ajax({
        url: phpObjectNoindexRule.urls.FIELD_OPTION_IBLOCK_SECTIONS,
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
    
    elIblockSectionAllField.onchange = function(event)
    {      
      elIblockSectionsField.disabled = this.checked;
      elIblockSectionsField.required = !this.checked
    }
    
  }());

  (function ()
  {
    let elBtnSave = elMainPageContainer.querySelector('[seo-text-form-role="save"]'),
      elBtnApply = elMainPageContainer.querySelector('[seo-text-form-role="apply"]'),
      elBtnCancel = elMainPageContainer.querySelector('[seo-text-form-role="cancel"]'),
      elAlert = elMainPageContainer.querySelector('[seo-text-form-role="alert"]');
      
    let elFormFieldID = elMainPageContainer.querySelector('[page-role="form-field-ID"]');  

    let form = new AsproUI.Form('form_noindex_rule_detail', {
      elBtnSave: elBtnSave,
      elBtnApply: elBtnApply,
      elBtnCancel: elBtnCancel,
      elAlert: elAlert,
    });
    
    form.onSuccess = function (data, status, form)
    {       
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
  }());

  (function ()
  {    
    contextMenuHandler.delete = function (elementId)
    {
      if (!phpObjectNoindexRule.urls.DELETE) {
        return;
      }

      new AsproUI.Popup.Confirm(
        phpObjectNoindexRule.urls.DELETE, {
          id: elementId,
          module: 'smartseo',
        }, {
        confirmMessage: BX.message('SMARTSEO_POPUP_MESSAGE_DELETE'),
        btnOk: BX.message('SMARTSEO_POPUP_BTN_DELETE'),
        btnCancel: BX.message('SMARTSEO_POPUP_BTN_CANCEL'),
      }
      );
    }
    
    contextMenuHandler.copy = function (elementId)
    {
      if (!phpObjectNoindexRule.urls.COPY) {
        return;
      }

      BX.ajax({
        url: phpObjectNoindexRule.urls.COPY,
        data: {
          id: elementId,
          module: 'smartseo'
        },
        method: 'POST',
        dataType: 'json',
        onsuccess: function (data)
        {
          if(data.result === true && data.redirect) {
            window.location.href = data.redirect;
          }
         
          if (data.result === false) {
            new AsproUI.Popup.Alert(data.message, '', {
                btnClose: BX.message('SMARTSEO_POPUP_BTN_CLOSE'),
              }, 'noindex_rule'
            );
          }
          
        },
        onfailure: function ()
        {
          
        }
      });
    }
    
  }());

})

