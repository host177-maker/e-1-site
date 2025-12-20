"use strict";

var AsproUI = AsproUI || {};

AsproUI.Popup = function () {

};

/**
 * AsproUI.Popup.Confirm
 *
 * @returns {AsproUI.Popup.Confirm}
 */

AsproUI.Popup.Confirm = function (
    url,
    data,
    messages,
    popupSuffix
) {
    this.url = url;
    this.data = data;
    this.popupSuffix = popupSuffix ? popupSuffix : 'default';

    this._setMessages(messages);
    this._showPopup();
};

AsproUI.Popup.Confirm.prototype = {
    onSuccess: function (data) {

    },

    _setMessages: function (messages) {
        this.messages = {
            confirmMessage: 'All information related to this section will be deleted! <br> Proceed?',
            btnOk: 'Delete',
            btnCancel: 'Cancel'
        }

        if (messages) {
            this.messages = Object.assign(this.messages, messages);
        }
    },

    _showPopup: function () {
        let self = this;

        let elContentContainer = document.createElement('div');
        elContentContainer.classList.add('aspro-ui-popup__content');
        elContentContainer.innerHTML = this.messages.confirmMessage;

        if (!(this.popup instanceof BX.PopupWindow)) {
            this.popup = BX.PopupWindowManager.create('popup_window_confirm_' + this.popupSuffix, null, {
                closeIcon: true,
                zIndex: 0,
                offsetLeft: 0,
                offsetTop: 0,
                draggable: false,
                overlay: {
                    backgroundColor: 'black',
                    opacity: '80'
                },
            });
        }

        this.popup.setContent(elContentContainer);

        let popup = this.popup;

        let btnOk = new BX.PopupWindowButton({
            text: self.messages.btnOk,
            className: 'ui-btn ui-btn-danger',
            events: {
                click: function () {
                    let btn = this;
                    btn.addClassName('ui-btn-wait');

                    BX.ajax({
                        url: self.url,
                        data: self.data,
                        method: 'POST',
                        dataType: 'json',
                        onsuccess: function (data) {
                            var message = '';
                            if (Array.isArray(data.message)) {
                                data.message.forEach(function (value) {
                                    message += value + '<br\>';
                                });
                            } else {
                                message = data.message;
                            }

                            if (data.result === false) {
                                var bxAlert = new BX.UI.Alert({
                                    text: message,
                                    textCenter: true,
                                    color: BX.UI.Alert.Color.DANGER,
                                });

                                elContentContainer.innerHTML = '';
                                elContentContainer.appendChild(bxAlert.getContainer());
                                popup.setContent(elContentContainer);
                                popup.adjustPosition();
                                btn.removeClassName('ui-btn-wait');
                            }

                            if (data.result === true) {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    btn.popupWindow.close();
                                }

                                self.onSuccess(data);
                            }
                        },
                        onfailure: function () {
                            btn.removeClassName('ui-btn-wait');
                        }
                    });
                }
            }
        });

        let btnCancel = new BX.PopupWindowButton({
            text: self.messages.btnCancel,
            className: 'ui-btn ui-btn-light',
            events: {
                click: function () {
                    this.popupWindow.close();
                }
            }
        });

        this.popup.setButtons([
            btnOk,
            btnCancel
        ])

        this.popup.show();
    }
}

/**
 * AsproUI.Popup.ConfirmAction
 *
 * @returns {AsproUI.Popup.ConfirmAction}
 */

AsproUI.Popup.ConfirmAction = function (
    actions,
    messages,
    classSettings,
    popupSuffix
) {
    this.popupSuffix = popupSuffix ? popupSuffix : 'action';

    this.actionBtnOk = null;
    if (actions.btnOk && typeof actions.btnOk == 'function') {
        this.actionBtnOk = actions.btnOk;
    }

    this._setClassSettings(classSettings);
    this._setMessages(messages);
};

AsproUI.Popup.ConfirmAction.prototype = {
    onSuccess: function (data) {

    },

    show: function () {
        this._init();
        this.popup.show();
    },

    hide: function () {
        if(this.popup === undefined) {
            return;
        }

        this.popup.close();
    },

    isShown() {
        if (this.popup === undefined) {
            return false;
        }

        return this.popup.isShown();
    },

    setMessageConfirm(message) {
        this.messages.confirmMessage = message;
    },

    _setMessages: function (messages) {
        this.messages = {
            confirmMessage: 'All information related to this section will be deleted! <br> Proceed?',
            btnOk: 'Delete',
            btnCancel: 'Cancel'
        }

        if (messages) {
            this.messages = Object.assign(this.messages, messages);
        }
    },

    _init: function () {
        let self = this;

        let elContentContainer = document.createElement('div');
        elContentContainer.classList.add('aspro-ui-popup__content');
        elContentContainer.innerHTML = this.messages.confirmMessage;

        if (!(this.popup instanceof BX.PopupWindow)) {
            this.popup = BX.PopupWindowManager.create('popup_window_confirm_' + this.popupSuffix, null, {
                closeIcon: true,
                zIndex: 0,
                offsetLeft: 0,
                offsetTop: 0,
                draggable: false,
                overlay: {
                    backgroundColor: 'black',
                    opacity: '80'
                },
            });
        }

        this.popup.setContent(elContentContainer);

        let btnOk = null
        if (this.actionBtnOk) {
            btnOk = new BX.PopupWindowButton({
                text: self.messages.btnOk,
                className: this.classes.btnOk,
                events: {click: this.actionBtnOk}
            });
        }

        let btnCancel = new BX.PopupWindowButton({
            text: self.messages.btnCancel,
            className: this.classes.btnCancel,
            events: {
                click: function () {
                    this.popupWindow.close();
                }
            }
        });

        this.popup.setButtons([
            btnOk,
            btnCancel
        ])
    },

    _setClassSettings: function (settings) {
        this.classes = {
            btnOk: 'ui-btn ui-btn-danger',
            btnCancel: 'ui-btn ui-btn-light',
            content: ''
        }

        if (settings) {
            this.classes = Object.assign(this.classes, settings)
        }
    },
}

/**
 * AsproUI.Popup.Progressbar
 *
 * @returns {AsproUI.Popup.Progressbar}
 */

AsproUI.Popup.ProgressBar = function (
    actions,
    messages,
    classSettings,
    popupSuffix,
    progressOptions,
) {
    if (typeof BX.UI.ProgressBar === undefined) {
        return;
    }

    let root = this;

    this.popupSuffix = popupSuffix ? popupSuffix : 'action';

    this._postUrl = '';
    this._postData = '';
    this._postFnCallBack = '';
    this._isAbortReplayProcess = false;

    this.actionBtnAbort = null;
    if (actions.btnAbort && typeof actions.btnAbort == 'function') {
        this.actionBtnAbort = function() {
            actions.btnAbort(root, this);
        };
    }

    this._setClassSettings(classSettings);
    this._setMessages(messages);
    this._initProgressBar(progressOptions);
};

AsproUI.Popup.ProgressBar.prototype = {
    pushProcess: function (url, data, fnCallBack) {
        this._postUrl = url;
        this._postData = data;
        this._postFnCallBack = fnCallBack;

        this._isAbortReplayProcess = false;

        if (!this._postUrl || !this._postData) {
            console.log('AsproUI.Popup.ProgressBar.post() - url and data parameters  expected');

            return;
        }

        let root = this;

        BX.ajax({
            url: this._postUrl,
            data: this._postData,
            method: 'POST',
            dataType: 'json',
            onsuccess: function (responce) {
                if (typeof fnCallBack === "function") {
                    root._postFnCallBack(responce)
                }
            },
            onfailure: function () {

            }
        });
    },
    replayProcess: function () {
        if (this._isAbortReplayProcess) {
            return;
        }

        this.pushProcess(this._postUrl, this._postData, this._postFnCallBack);
    },
    abortReplayProcess: function () {
        this._isAbortReplayProcess = true;
    },
    isAbortReplayProcess: function () {
        return this._isAbortReplayProcess;
    },
    setProgressValue: function (value) {
        this.progress.setValue(value);
        this.progress.update(value);
    },
    setProgressMaxValue: function (value) {
        this.progress.setMaxValue(value);
        this.progress.update(value);
    },
    show: function () {
        this._init();
        this.popup.show();
    },
    hide: function () {
        if(this.popup === undefined) {
            return;
        }

        this.popup.close();
    },
    isShown() {
        if (this.popup === undefined) {
            return false;
        }

        return this.popup.isShown();
    },
    _setMessages: function (messages) {
        this.messages = {
            textBefore: 'Removal in progress',
            btnCancel: 'Cancel',
            btnAbort: 'Abort',
        }

        if (messages) {
            this.messages = Object.assign(this.messages, messages);
        }
    },
    _initProgressBar: function (progressOptions) {
        let options = {
            textBefore: this.messages.textBefore,
            size: BX.UI.ProgressBar.Size.LARGE,
            column: true,
            statusType: BX.UI.ProgressBar.Status.COUNTER,
            color: BX.UI.ProgressBar.Color.SUCCESS,
        };

        Object.assign(options, options, progressOptions);

        this.progress = new BX.UI.ProgressBar(options);
    },
    _init: function () {
        let root = this;

        let elContentContainer = document.createElement('div');
        elContentContainer.classList.add('aspro-ui-popup__content');
        elContentContainer.appendChild(this._getProgressBar());

        if (!(this.popup instanceof BX.PopupWindow)) {
            this.popup = BX.PopupWindowManager.create('popup_window_progressbar_' + this.popupSuffix, null, {
                closeIcon: true,
                zIndex: 0,
                offsetLeft: 0,
                offsetTop: 0,
                draggable: false,
                overlay: {
                    backgroundColor: 'black',
                    opacity: '80'
                },
            });
        }

        this.popup.setContent(elContentContainer);

        let btnAbort = null
        if (this.actionBtnAbort) {
            btnAbort = new BX.PopupWindowButton({
                text: root.messages.btnAbort,
                className: this.classes.btnAbort,
                events: {
                    click: this.actionBtnAbort
                }
            });
        }

        this.popup.setButtons([
            btnAbort
        ])
    },
    _setClassSettings: function (settings) {
        this.classes = {
            btnAbort: 'ui-btn ui-btn-danger-light',
            content: ''
        }

        if (settings) {
            this.classes = Object.assign(this.classes, settings)
        }
    },
    _getProgressBar: function () {
        return this.progress.getContainer();
    },
}

/**
 * AsproUI.Popup.Alert
 *
 * @returns {AsproUI.Popup.Alert}
 */

AsproUI.Popup.Alert = function (
    alertMessage,
    color,
    messages,
    popupSuffix
) {
    this.alertMessage = alertMessage;
    this.popupSuffix = popupSuffix ? popupSuffix : 'default';

    if (!color) {
        this.color = BX.UI.Alert.Color.DANGER
    }

    this._setMessages(messages);
    this._showPopup();
};

AsproUI.Popup.Alert.prototype = {
    onSuccess: function (data) {

    },

    _setMessages: function (messages) {
        this.messages = {
            btnClose: 'Close'
        }

        if (messages) {
            this.messages = Object.assign(this.messages, messages);
        }
    },

    _showPopup: function () {
        let self = this;

        let elContentContainer = document.createElement('div');
        elContentContainer.classList.add('aspro-ui-popup__content');

        if (!(this.popup instanceof BX.PopupWindow)) {
            this.popup = BX.PopupWindowManager.create('popup_window_alert_' + this.popupSuffix, null, {
                closeIcon: true,
                zIndex: 0,
                offsetLeft: 0,
                offsetTop: 0,
                draggable: false,
                overlay: {
                    backgroundColor: 'black',
                    opacity: '80'
                },
            });
        }

        this.popup.setContent(elContentContainer);

        let popup = this.popup;

        let btnClose = new BX.PopupWindowButton({
            text: self.messages.btnClose,
            className: 'ui-btn ui-btn-light',
            events: {
                click: function () {
                    this.popupWindow.close();
                }
            }
        });

        let bxAlert = new BX.UI.Alert({
            text: this.alertMessage,
            textCenter: true,
            color: this.color,
        });

        elContentContainer.innerHTML = '';
        elContentContainer.appendChild(bxAlert.getContainer());

        this.popup.setButtons([
            btnClose
        ])

        this.popup.show();
    }
}