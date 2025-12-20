"use strict";

var contextMenuHandler = {};
contextMenuHandler.delete = function (){}

BX.Event.EventEmitter.setMaxListeners(50);

BX.ready(function ()
{
  let elMainPageContainer = document.getElementById('filter_rule_detail');

  /**
   * CAdminTabControl('filter_rule_tab_control')
   */
  (function ()
  {
    if (typeof filter_rule_tab_control == 'object') {

      filter_rule_tab_control.onSelectTab = function (tabName)
      {
        let url = window.location.search.replace(/(&active_tab=)(\w+|_)/i, '');
        window.history.replaceState(null, null, url + '&active_tab=' + tabName);
        AsproUI.Form.setHiddenInput('form_filter_rule', 'active_tab', tabName);

        let elWrapperButtons = BX(this.name + '_buttons_div');
        if (tabName == 'filter_rule_main' || tabName == 'filter_rule_seo') {
          elWrapperButtons.style.display = 'block';
          elWrapperButtons.classList.add('aspro-ui--animate-slide-top');
        } else {
          elWrapperButtons.style.display = 'none';
        }
      }

      filter_rule_tab_control.gridReload = function (gridId)
      {
        let gridObject = BX.Main.gridManager.getById(gridId);

        if (!gridObject.hasOwnProperty('instance')) {
          console.log('Grid instance not found');
          return false;
        }

        gridObject.instance.reloadTable(null, null, function ()
        {
          if (typeof BX.UI.Hint !== 'object') {
            return;
          }
          let elGrid = document.getElementById(gridId);
          let elementHints = elGrid.querySelectorAll('[data-ext="hint"]');
          for (var i = 0; i < elementHints.length; i++) {
            BX.UI.Hint.init(elementHints[i]);
          }
        });
      }

      if (!phpObjectFilterRule.dataFilterRule) {
        for (let tabIndex = 0; tabIndex < filter_rule_tab_control.aTabs.length; tabIndex++) {
          let tab = filter_rule_tab_control.aTabs[tabIndex];

          if (tab.DIV != 'filter_rule_main') {
            filter_rule_tab_control.DisableTab(tab.DIV);
          }
        }
      }

      if (phpObjectFilterRule.dataFilterRule && phpObjectFilterRule.activeTab) {
        let elWrapperButtons = BX(filter_rule_tab_control.name + '_buttons_div');
        if (phpObjectFilterRule.activeTab == 'filter_rule_main' || phpObjectFilterRule.activeTab == 'filter_rule_seo') {
        } else {
          elWrapperButtons.style.display = 'none';
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
        });

        event.preventDefault();
      }
    }
  }());

  (function ()
  {

    let elFilterRuleForm = elMainPageContainer.querySelector('#form_filter_rule'),
      elSiteField = elFilterRuleForm.querySelector('[data-field="site"]'),
      elIblockTypeField = elFilterRuleForm.querySelector('[data-field="iblock_type"]'),
      elIblockField = elFilterRuleForm.querySelector('[data-field="iblock"]'),
      elIblockSectionsField = elFilterRuleForm.querySelector('[data-field="iblock_sections"]');   

    elSiteField.onchange = function (event)
    {
      if (!phpObjectFilterRule.urls.FIELD_OPTION_IBLOCK_TYPE) {
        return;
      }

      elIblockTypeField.dataset.state = 'loading';

      BX.ajax({
        url: phpObjectFilterRule.urls.FIELD_OPTION_IBLOCK_TYPE,
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
      if (!phpObjectFilterRule.urls.FIELD_OPTION_IBLOCK) {
        return;
      }

      elIblockField.dataset.state = 'loading';

      BX.ajax({
        url: phpObjectFilterRule.urls.FIELD_OPTION_IBLOCK,
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
      if (!phpObjectFilterRule.urls.FIELD_OPTION_IBLOCK_SECTIONS) {
        return;
      }

      elIblockSectionsField.dataset.state = 'loading';

      BX.ajax({
        url: phpObjectFilterRule.urls.FIELD_OPTION_IBLOCK_SECTIONS,
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
    let elBtnSave = elMainPageContainer.querySelector('[filter-rule-form-role="save"]'),
      elBtnApply = elMainPageContainer.querySelector('[filter-rule-form-role="apply"]'),
      elBtnCancel = elMainPageContainer.querySelector('[filter-rule-form-role="cancel"]'),
      elAlert = elMainPageContainer.querySelector('[filter-rule-form-role="alert"]');

    let filterRuleForm = new AsproUI.Form('form_filter_rule', {
      elBtnSave: elBtnSave,
      elBtnApply: elBtnApply,
      elBtnCancel: elBtnCancel,
      elAlert: elAlert,
    });

    if (phpObjectFilterRule.dataFilterRule.ID
      && phpObjectFilterRule.aliasSeoFilterRule) {
      filterRuleForm.mergeForm('form_seo_property_filter_rule', phpObjectFilterRule.aliasSeoFilterRule);
    }

  }());

  (function ()
  {
    let elSeoPropertyForm = document.getElementById('form_seo_property_filter_rule'),
      elTemplateControls = elSeoPropertyForm.querySelectorAll('[page-role="control-engine-template"]');

    if (!phpObjectFilterRule.urls.MENU_SEO_PROPERTY) {
      return;
    }

    for (let i = 0; i < elTemplateControls.length; i++) {
      new AsproUI.Form.ControlTemplateEngine(elTemplateControls[i], false, {
        urlMenuResponse: phpObjectFilterRule.urls.MENU_SEO_PROPERTY,
        urlSampleResponse: phpObjectFilterRule.urls.SAMPLE_SEO_PROPERTY,
      });
    }

  }());

  (function ()
  {
    contextMenuHandler.delete = function (filterRuleId)
    {
      if (!phpObjectFilterRule.urls.DELETE) {
        return;
      }

      new AsproUI.Popup.Confirm(
        phpObjectFilterRule.urls.DELETE, {
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

})

