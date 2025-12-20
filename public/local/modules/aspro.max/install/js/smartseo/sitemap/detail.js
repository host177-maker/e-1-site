"use strict";

var contextMenuHandler = {};
contextMenuHandler.delete = function (){}

BX.ready(function ()
{
  let elMainPageContainer = document.getElementById('sitemap_detail');
  
   /**
   * CAdminTabControl('filter_rule_tab_control')
   */
  (function ()
  {
    if (typeof sitemap_tab_control == 'object') {  

      if (!phpObjectSitemap.dataSitemap) {
        for (let tabIndex = 0; tabIndex < sitemap_tab_control.aTabs.length; tabIndex++) {
          let tab = sitemap_tab_control.aTabs[tabIndex];

          if (tab.DIV != 'sitemap_main') {
            sitemap_tab_control.DisableTab(tab.DIV);
          }
        }
      }     
    }
  }());
  
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
        }, 400);

        event.preventDefault();
      }
    }
  }());
  
  (function ()
  {
    let elForm = elMainPageContainer.querySelector('#form_sitemap'),
      elFormInSitemap = elForm.querySelector('[data-field="in_index_sitemap"]'),
      elFormIndexSitemapFile = elForm.querySelector('[data-field="index_sitemap_file"]'),
      elFormUpdateSitemapIndex = elForm.querySelector('[data-field="update_sitemap_index"]'),
      elWrapperIndexSitemapFile = elForm.querySelectorAll('[page-role="index-sitemap-file-wrapper"]');
            
      elFormInSitemap.onchange = function (event) {        
        for (let i = 0; i < elWrapperIndexSitemapFile.length; i++) {
          elWrapperIndexSitemapFile[i].style.display = 'table-row';
        }
        elFormIndexSitemapFile.disabled = !this.checked;
        elFormUpdateSitemapIndex.disabled = !this.checked;
      }
  }());
  
  (function ()
  {
    let elBtnSave = elMainPageContainer.querySelector('[page-role="save"]'),
      elBtnApply = elMainPageContainer.querySelector('[page-role="apply"]'),
      elBtnCancel = elMainPageContainer.querySelector('[page-role="cancel"]'),
      elAlert = elMainPageContainer.querySelector('[page-role="alert"]');

    let form = new AsproUI.Form('form_sitemap', {
      elBtnSave: elBtnSave,
      elBtnApply: elBtnApply,
      elBtnCancel: elBtnCancel,
      elAlert: elAlert,
    });
    
    let elCheckboxApplyGenerateSitemap = elMainPageContainer.querySelector('[page-role="apply-generate-sitemap"]'),
      elFormFieldGenerate = elMainPageContainer.querySelector('[page-role="form-field-GENERATE"]');
    
    elCheckboxApplyGenerateSitemap.onchange = function (event)
    {
      elFormFieldGenerate.value = this.checked == true ? 'Y' : 'N';
    }

  }());
  
  (function ()
  {
    contextMenuHandler.delete = function (filterRuleId)
    {
      if (!phpObjectSitemap.urls.DELETE) {
        return;
      }

      new AsproUI.Popup.Confirm(
        phpObjectSitemap.urls.DELETE, {
          id: filterRuleId,
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
    let elementHints = elMainPageContainer.querySelectorAll('[data-ext="hint"]');
    for (var i = 0; i < elementHints.length; i++) {
      BX.UI.Hint.init(elementHints[i]);
    }
  }());
  
})

