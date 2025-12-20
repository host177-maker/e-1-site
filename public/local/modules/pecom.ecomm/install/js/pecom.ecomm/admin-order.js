var pecAdmin = {
    urlAjax: '/bitrix/js/pecom.ecomm/ajax.php',
    orderId: (BX('ID')) ? BX('ID').value : 0,
    pecIdViewMode: 'view',
    pecId: '',
    pecStatus: '',
    saveNewPecId: function (pecId) {
        var pecId = document.getElementById('pec-delivery__pec-id').value;
        var $this = this;
        document.getElementsByClassName('pec-delivery__change-pec-id')[0].disabled = true;
        document.getElementById('pec-delivery__pec-status').value = '';
        document.getElementById('pec-delivery__pec-status').setAttribute('title', '');
        BX.ajax({
            timeout:    60,
            method:     'POST',
            url: $this.urlAjax,
            dataType: 'json',
            data: {orderId: this.orderId, method: 'savePecId', pecId: pecId, sessid: BX.bitrix_sessid()},
            onsuccess: function (data) {
                if (1 || data.status == 'ok') {
                    $this.pecId = pecId;
                    $this.pecIdViewMode = 'view';
                    document.getElementById('pec-delivery__pec-id').disabled = true;
                    document.getElementsByClassName('pec-delivery__change-pec-id')[0].value = 'Сменить код груза';
                    document.getElementsByClassName('pec-delivery__change-pec-id')[0].disabled = false;
                    $this.showFooterBtn();
                }
            }
        });
    },
    showFooterBtn: function() {
        if (this.pecId) {
            document.getElementById('pec-delivery__send-order').style.display = 'none';
            document.getElementById('pec-delivery__pre-registration').style.display = 'none';
            document.getElementById('pec-delivery__get-status').style.display = 'inline-block';
            document.getElementById('pec-delivery__print-tag').style.display = 'inline-block';
            document.getElementById('pec-delivery__pec_pickup_date').disabled = true;
            document.getElementById('pec-delivery__pec-count-positions').disabled = true;
            document.getElementById('pec-delivery__transport-type').disabled = true;
        } else {
            document.getElementById('pec-delivery__get-status').style.display = 'none';
            document.getElementById('pec-delivery__print-tag').style.display = 'none';
            document.getElementById('pec-delivery__send-order').style.display = 'inline-block';
            document.getElementById('pec-delivery__pre-registration').style.display = 'inline-block';
            document.getElementById('pec-delivery__pec_pickup_date').disabled = false;
            document.getElementById('pec-delivery__pec-count-positions').disabled = false;
            document.getElementById('pec-delivery__transport-type').disabled = false;
        }
    },
    eventChangePecId: function() {
        var $this = this;
        BX.bindDelegate(
            document.body, 'click', {className: 'pec-delivery__change-pec-id'}, function () {
                // var pecId = document.getElementById('pec-delivery__pec-id').value;
                if ($this.pecIdViewMode == 'view') {
                    $this.pecIdViewMode = 'edit';
                    document.getElementById('pec-delivery__pec-id').disabled = false
                    document.getElementById('pec-delivery__pec_pickup_date').disabled = false
                    document.getElementById('pec-delivery__pec-count-positions').disabled = false
                    document.getElementById('pec-delivery__transport-type').disabled = false
                    document.getElementsByClassName('pec-delivery__change-pec-id')[0].value = 'Сохранить'
                } else {
                    newPecId = document.getElementById('pec-delivery__pec-id').value;
                    $this.saveNewPecId(newPecId);
                }
            }
        )
    },
    eventGetPecStatus() {
        var $this = this;
        BX.bind(BX('pec-delivery__get-status'), 'click', function () {
            document.getElementById('pec-delivery__get-status').disabled = true;
            $this.updateStatus();
        })
    },
    eventPrintTag() {
        var $this = this;
        BX.bind(BX('pec-delivery__print-tag'), 'click', function () {
            if (!$this.pecId) return;
            document.getElementById('pec-delivery__print-tag').disabled = true;
            BX.ajax({
                timeout:    60,
                method:     'POST',
                url: $this.urlAjax,
                dataType: 'json',
                data: {orderId: $this.orderId, method: 'getTag', pecId: $this.pecId, sessid: BX.bitrix_sessid()},
                onsuccess: function (data) {
                    document.getElementById('pec-delivery__print-tag').disabled = false;

                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    var win = window.open('about:blank', "_new");
                    win.document.open();
                    win.document.write(data.html);
                    win.document.close();
                }
            });
        })
    },
    eventSendOrder() {
        var $this = this;
        BX.bind(BX('pec-delivery__send-order'), 'click', function () {
            if ($this.pecId) return;
            document.getElementById('pec-delivery__send-order').disabled = true;
            var pickupDate = document.getElementById('pec-delivery__pec_pickup_date').value;
            var positionCount = document.getElementById('pec-delivery__pec-count-positions').value;
            var transpotType = document.getElementById('pec-delivery__transport-type').value;
            $this.hideError();
            BX.ajax({
                timeout:    60,
                method:     'POST',
                url: $this.urlAjax,
                dataType: 'json',
                data: {orderId: $this.orderId, pickupDate: pickupDate, positionCount: positionCount, transpotType: transpotType, method: 'pickupSubmit', sessid: BX.bitrix_sessid()},
                onsuccess: function (data) {
                    document.getElementById('pec-delivery__send-order').disabled = false;
                    if (data.hasOwnProperty('cargos') && data.cargos[0].cargoCode) {
                        // document.getElementsByClassName('pec-delivery__change-pec-id')[0].remove()
                        $this.pecId = data.cargos[0].cargoCode;
                        $this.updateStatus();
                    }
                    if (data.hasOwnProperty('error')) {
                        $this.showError(data);
                    }
                    document.getElementById('pec-delivery__pec-id').value = $this.pecId;
                    document.getElementById('pec-delivery__pec_pickup_date').disabled = true;
                    document.getElementById('pec-delivery__pec-count-positions').disabled = true;
                    document.getElementById('pec-delivery__transport-type').disabled = true;
                    $this.showFooterBtn();
                    // $this.saveNewPecId($this.pecId);
                }
            });
        })
    },
    eventPreRegistration() {
        var $this = this;
        BX.bind(BX('pec-delivery__pre-registration'), 'click', function () {
            if ($this.pecId) return;
            document.getElementById('pec-delivery__pre-registration').disabled = true;
            var positionCount = document.getElementById('pec-delivery__pec-count-positions').value;
            var transpotType = document.getElementById('pec-delivery__transport-type').value;
            $this.hideError();
            BX.ajax({
                timeout:    60,
                method:     'POST',
                url: $this.urlAjax,
                dataType: 'json',
                data: {orderId: $this.orderId, positionCount: positionCount, transpotType: transpotType, method: 'preRegistration', sessid: BX.bitrix_sessid()},
                onsuccess: function (data) {
                    document.getElementById('pec-delivery__pre-registration').disabled = false;
                    if (data.hasOwnProperty('cargos') && data.cargos[0].cargoCode) {
                        $this.pecId = data.cargos[0].cargoCode;
                        $this.updateStatus();
                        // document.getElementsByClassName('pec-delivery__change-pec-id')[0].remove()
                    }
                    if (data.hasOwnProperty('error')) {
                        $this.showError(data);
                    }
                    document.getElementById('pec-delivery__pec-id').value = $this.pecId;
                    document.getElementById('pec-delivery__pec_pickup_date').disabled = true;
                    document.getElementById('pec-delivery__pec-count-positions').disabled = true;
                    document.getElementById('pec-delivery__transport-type').disabled = true;
                    $this.showFooterBtn();
                    // $this.saveNewPecId($this.pecId);
                }
            });
        })
    },
    updateStatus: function() {
        if (!this.pecId) return;
        var $this = this;
        BX.ajax({
            timeout:    60,
            method:     'POST',
            url: $this.urlAjax,
            dataType: 'json',
            data: {orderId: $this.orderId, method: 'getPecStatus', pecId: $this.pecId, sessid: BX.bitrix_sessid()},
            onsuccess: function (data) {
                if (data.hasOwnProperty('name')) {
                    document.getElementById('pec-delivery__get-status').disabled = false;
                    document.getElementById('pec-delivery__pec-status').value = data.name;
                    document.getElementById('pec-delivery__pec-status').setAttribute('title', data.name);
                    $this.pecStatus = data.code;
                }
                if (data.code == 'error') {
                    alert(data.name);
                }
            }
        });
    },
    hideError: function() {
        document.getElementById('pec-delivery__api-error').style.display = 'none';
    },
    showError: function(data) {
        document.getElementById('pec-delivery__api-error').style.display = 'block';
        var error = data.error.fields;
        var txt = '';
        for (i in error) {
            txt += error[i].Key + ': ' + error[i].Value[0] + '<br>';
        }
        document.getElementById('pec-delivery__api-error').innerHTML = txt;
    },

    init: function () {
        this.pecId = document.getElementById('pec-delivery__pec-id').value;
        this.orderId = (BX('ID')) ? BX('ID').value : 0;
        this.showFooterBtn();
        this.eventChangePecId();
        this.eventGetPecStatus();
        this.eventPrintTag();
        this.eventSendOrder();
        this.eventPreRegistration();
    }
}

BX.ready(function () {
    pecAdmin.init();
})
