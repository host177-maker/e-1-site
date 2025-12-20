<script>
    BX.ready(function() {
        stream_land('tL0GSZGkyHF7pEg=');
        BX.loadScript('//code-eu1.jivosite.com/widget/mbxON6Cps1');
    })
</script>

<script>
    jQuery(document).on("click", 'form [type="submit"]', function () {
        var form = jQuery(this).closest('form');
        var fio = form.find('[data-sid="CLIENT_NAME"]').val();
        var phoneNumber = form.find('[data-sid="PHONE"]').val();
        var email = form.find('[data-sid="EMAIL"]').val();
        var ct_site_id = window.ct('calltracking_params', '2514qjt6').siteId;
        var comment = form.find('textarea[data-sid="MESSAGE"]').val();
        var subject = 'Заявка с ' + location.hostname;

        var ct_data = {
            fio: fio,
            phoneNumber: phoneNumber,
            email: email,
            subject: subject,
            requestUrl: location.href,
            sessionId: window.ct('calltracking_params', '2514qjt6').sessionId
        };
        if (!!phoneNumber && !window.ct_snd_flag) {
            window.ct_snd_flag = 1; setTimeout(function () { window.ct_snd_flag = 0; }, 10000);
            jQuery.ajax({
                url: 'https://api.calltouch.ru/calls-service/RestAPI/requests/' + ct_site_id + '/register/',
                dataType: 'json', type: 'POST', data: ct_data, async: false
            });
        }
    });
    jQuery(document).on("click", '#app [class*="j-order-button"]', function () {
        var form = jQuery(this).closest('#app');
        var fio = form.find('[name="PROPS[CONTACT_PERSON]"], [name="PROPS[FIO]"]').val();
        var phoneNumber = form.find('[name="PROPS[PHONE]"]').val();
        var email = form.find('[name="PROPS[EMAIL]"]').val();
        var ct_site_id = window.ct('calltracking_params', '2514qjt6').siteId;
        var comment = form.find('textarea[data-sid="MESSAGE"]').val();
        var subject = 'Заказ с ' + location.hostname;

        var ct_data = {
            fio: fio,
            phoneNumber: phoneNumber,
            email: email,
            subject: subject,
            requestUrl: location.href,
            sessionId: window.ct('calltracking_params', '2514qjt6').sessionId
        };
        if (!!phoneNumber && !!email && !!fio && !window.ct_snd_flag) {
            window.ct_snd_flag = 1; setTimeout(function () { window.ct_snd_flag = 0; }, 10000);
            jQuery.ajax({
                url: 'https://api.calltouch.ru/calls-service/RestAPI/requests/' + ct_site_id + '/register/',
                dataType: 'json', type: 'POST', data: ct_data, async: false
            });
        }
    });
    jQuery(document).on('click', 'form#one_click_buy_form [type="submit"]', function () {
        var form = jQuery(this).closest('form');
        var fio = form.find('input[name="ONE_CLICK_BUY[FIO]"]').val();
        var email = form.find('input[name="ONE_CLICK_BUY[EMAIL]"]').val();
        var phoneNumber = form.find('input[name="ONE_CLICK_BUY[PHONE]"]').val();
        var comment = form.find('textarea[name="ONE_CLICK_BUY[COMMENT]"]').val();
        var ct_site_id = window.ct('calltracking_params', 'yx9putjb').siteId;
        var subject = 'Купить в 1 клик';
        var ct_data = {
            fio: fio,
            email: email,
            phoneNumber: phoneNumber,
            comment: comment,
            subject: subject,
            requestUrl: location.href,
            sessionId: window.ct('calltracking_params', 'yx9putjb').sessionId
        };

        if (!!fio && !!email && !!phoneNumber && window.ct_snd_flag != 1) {
            window.ct_snd_flag = 1; setTimeout(function () { window.ct_snd_flag = 0; }, 20000);
            jQuery.ajax({
                url: 'https://api.calltouch.ru/calls-service/RestAPI/requests/' + ct_site_id + '/register/',
                dataType: 'json', type: 'POST', data: ct_data, async: false
            });
        }
    });
    var _ctreq_jivo = function (sub) {
        var sid = window.ct('calltracking_params', '2514qjt6').siteId;
        var jc = jivo_api.getContactInfo(); var fio = ''; var phone = ''; var email = '';
        if (!!jc.client_name) { fio = jc.client_name; } if (!!jc.phone) { phone = jc.phone; } if (!!jc.email) { email = jc.email; }
        var ct_data = { fio: fio, phoneNumber: phone, email: email, subject: sub, requestUrl: location.href, sessionId: window.ct('calltracking_params', '2514qjt6').sessionId };
        var request = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
        var post_data = Object.keys(ct_data).reduce(function (a, k) { if (!!ct_data[k]) { a.push(k + '=' + encodeURIComponent(ct_data[k])); } return a }, []).join('&');
        var url = 'https://api.calltouch.ru/calls-service/RestAPI/' + sid + '/requests/orders/register/';
        if (!window.ct_snd_flag) {
            window.ct_snd_flag = 1; setTimeout(function () { window.ct_snd_flag = 0; }, 10000);
            request.open("POST", url, true); request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); request.send(post_data);
        }
    }
    window.jivo_onIntroduction = function () { _ctreq_jivo('JivoSite посетитель оставил контакты'); }
    window.jivo_onCallStart = function () { _ctreq_jivo('JivoSite обратный звонок'); }
</script>

<script>
    carrotquest.addCallback('user_replied', function(data) {
        console.log('carrotquest: user_replied');
        if (!window.ct_snd_flag){ window.ct_snd_flag = 1; ct('goal','cq_conversation'); }
    });
</script>

<script>
    Element.prototype.matches||(Element.prototype.matches=Element.prototype.matchesSelector||Element.prototype.webkitMatchesSelector||Element.prototype.mozMatchesSelector||Element.prototype.msMatchesSelector),Element.prototype.closest||(Element.prototype.closest=function(e){for(var t=this;t;){if(t.matches(e))return t;t=t.parentElement}return null});var ct_get_val=function(form,selector){if(!!form.querySelector(selector)){return form.querySelector(selector).value;}else{return '';}}

    var send_ct_carrotquest = function(sub, data) {
        if (!window.ct_send_flag){ window.ct_send_flag = 1
            var fio = ''; var phone = ''; var email = '';
            if (!!data.fio){fio = data.fio;} if (!!data.phone){phone = data.phone;} if (!!data.email){email = data.email;}
            var ct_site_id = '28415';
            var ct_data = { fio: fio, phoneNumber: phone, email: email, subject: sub, requestUrl: location.href, sessionId: window.call_value };
            var request = window.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest();
            var post_data = Object.keys(ct_data).reduce(function(a, k) {if(!!ct_data[k]){a.push(k + '=' + encodeURIComponent(ct_data[k]));}return a}, []).join('&');
            var url = 'https://api.calltouch.ru/calls-service/RestAPI/'+ct_site_id+'/requests/orders/register/';
            if (!window.ct_snd_flag){
                window.ct_snd_flag = 1; setTimeout(function(){ window.ct_snd_flag = 0; }, 20000);
                request.open("POST", url, true); request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); request.send(post_data);
            }
        }
    };

    carrotquest.addCallback('messenger_opened', function(e) { console.log(e);
        var iframe_chat = document.querySelector('#carrot-messenger-frame'); var iframe_chat_document;
        if (iframe_chat){
            var iframe_chat_window = iframe_chat.contentWindow;
            iframe_chat_document = iframe_chat.contentDocument || iframe_chat_window.document;
        }
        var iframe_popup = document.querySelector('#carrot-popup-frame'); var iframe_popup_document;
        if (iframe_popup){
            var iframe_popup_window = iframe_popup.contentWindow;
            iframe_popup_document = iframe_popup.contentDocument || iframe_popup_window.document;
        }
        var events = ['mousedown','touchstart']; events.forEach(function(ev){
            if (!!iframe_chat_document){
                iframe_chat_document.addEventListener(ev, function(e) {
                    if (e.target.closest('#carrotquest-messenger-innerreply-submit') || e.target.closest('#carrotquest-messenger-autoreply-request-ok')) {
                        var ct_data = {}; var cq_form = e.target.closest('#carrotquest-messenger');
                        ct_data.fio = ct_get_val(cq_form,'input[name="name"]');
                        ct_data.phone = ct_get_val(cq_form,'input[name="phone"]');
                        ct_data.email = ct_get_val(cq_form,'input[name="email"]');
                        ct_data.requestUrl = location.href;
                        if (!!ct_data.phone || !!ct_data.email){send_ct_carrotquest('Посетитель оставил контакты в Carrotquest',ct_data);}
                        console.log('ct click: ',e.target,'ct_data: ',ct_data);
                    }
                    if (e.target.closest('button[id*="block-popup-button"]')) {
                        var ct_data = {}; var cq_form = e.target.closest('#carrotquest-messenger');
                        ct_data.fio = ct_get_val(cq_form,'input[data-prop="$name"]');
                        ct_data.phone = ct_get_val(cq_form,'input[data-prop="$phone"]');
                        ct_data.email = ct_get_val(cq_form,'input[data-prop="$email"]');
                        ct_data.requestUrl = location.href;
                        if (!!ct_data.phone || !!ct_data.email){send_ct_carrotquest('Заявка из формы Carrotquest',ct_data);}
                        console.log('ct click: ',e.target,'ct_data: ',ct_data);
                    }
                });
            }
            if (iframe_popup_document){
                iframe_popup_document.addEventListener(ev, function(e) {
                    if (e.target.closest('button[id*="block-popup-button"]')) {
                        var ct_data = {}; var cq_form = e.target.closest('#carrotquest-messenger');
                        ct_data.fio = ct_get_val(cq_form,'input[data-prop="$name"]');
                        ct_data.phone = ct_get_val(cq_form,'input[data-prop="$phone"]');
                        ct_data.email = ct_get_val(cq_form,'input[data-prop="$email"]');
                        ct_data.requestUrl = location.href;
                        if (!!ct_data.phone || !!ct_data.email){send_ct_carrotquest('Заявка из popup-формы Carrotquest',ct_data);}
                        console.log('ct click: ',e.target,'ct_data: ',ct_data);
                    }
                });
            }
        });
    });
</script>