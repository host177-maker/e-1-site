<?php

namespace Sprint\Migration;


class FORM_FILLING_SELECTION_WARDROBE20250514093024 extends Version
{
    protected $author = "joy_999@mail.ru";

    protected $description = "[ticket/18445] Событие отправки формы \"Заказать подбор\"";

    protected $moduleVersion = "5.0.2";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Event()->saveEventType('FORM_FILLING_SELECTION_WARDROBE', array (
  'LID' => 'ru',
  'EVENT_TYPE' => 'email',
  'NAME' => 'Заполнена web-форма "SELECTION_WARDROBE"',
  'DESCRIPTION' => '#RS_FORM_ID# - ID формы
#RS_FORM_NAME# - Имя формы
#RS_FORM_SID# - SID формы
#RS_RESULT_ID# - ID результата
#RS_DATE_CREATE# - Дата заполнения формы
#RS_USER_ID# - ID пользователя
#RS_USER_EMAIL# - EMail пользователя
#RS_USER_NAME# - Фамилия, имя пользователя
#RS_USER_AUTH# - Пользователь был авторизован?
#RS_STAT_GUEST_ID# - ID посетителя
#RS_STAT_SESSION_ID# - ID сессии
#CLIENT_NAME# - Ваше имя
#CLIENT_NAME_RAW# - Ваше имя (оригинальное значение)
#PHONE# - Телефон
#PHONE_RAW# - Телефон (оригинальное значение)
#CLIENT_EMAIL# - Ваш Email
#CLIENT_EMAIL_RAW# - Ваш Email (оригинальное значение)
#PAGE_URL# - Адрес страницы
#PAGE_URL_RAW# - Адрес страницы (оригинальное значение)
',
  'SORT' => '100',
));
            $helper->Event()->saveEventType('FORM_FILLING_SELECTION_WARDROBE', array (
  'LID' => 'en',
  'EVENT_TYPE' => 'email',
  'NAME' => 'Web form filled "SELECTION_WARDROBE"',
  'DESCRIPTION' => '#RS_FORM_ID# - Form ID
#RS_FORM_NAME# - Form name
#RS_FORM_SID# - Form SID
#RS_RESULT_ID# - Result ID
#RS_DATE_CREATE# - Form filling date
#RS_USER_ID# - User ID
#RS_USER_EMAIL# - User e-mail
#RS_USER_NAME# - First and last user names
#RS_USER_AUTH# - User authorized?
#RS_STAT_GUEST_ID# - Visitor ID
#RS_STAT_SESSION_ID# - Session ID
#CLIENT_NAME# - Ваше имя
#CLIENT_NAME_RAW# - Ваше имя (original value)
#PHONE# - Телефон
#PHONE_RAW# - Телефон (original value)
#CLIENT_EMAIL# - Ваш Email
#CLIENT_EMAIL_RAW# - Ваш Email (original value)
#PAGE_URL# - Адрес страницы
#PAGE_URL_RAW# - Адрес страницы (original value)
',
  'SORT' => '100',
));
            $helper->Event()->saveEventMessage('FORM_FILLING_SELECTION_WARDROBE', array (
  'LID' => 
  array (
    0 => 's1',
  ),
  'ACTIVE' => 'Y',
  'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
  'EMAIL_TO' => '#DEFAULT_EMAIL_FROM#',
  'SUBJECT' => 'Заказ подбора шкафа с сайта #SITE_NAME#',
  'MESSAGE' => '#SERVER_NAME#<br>
Заполнена web-форма: [#RS_FORM_ID#] #RS_FORM_NAME# <br>
<br>
&nbsp;------------------------------------------------------- <br>
Имя: #CLIENT_NAME# <br>
Телефон: #PHONE#<br>
Email: #CLIENT_EMAIL#<br>
Адрес страницы: #PAGE_URL#<br>
<br>
&nbsp;------------------------------------------------------- <br>
Заявка отправлена: #RS_DATE_CREATE#<br>
<br>
Просмотр результата на сайте: <a href="http://#SERVER_NAME#/bitrix/admin/form_result_edit.php?lang=ru&WEB_FORM_ID=#RS_FORM_ID#&RESULT_ID=#RS_RESULT_ID#&WEB_FORM_NAME=#RS_FORM_NAME#" target="_blank">http://#SERVER_NAME#/bitrix/admin/form_result_edit.php?lang=ru&amp;WEB_FORM_ID=#RS_FORM_ID#&amp;RESULT_ID=#RS_RESULT_ID#&amp;WEB_FORM_NAME=#RS_FORM_NAME#</a> <br>
-------------------------------------------------------<br>
 Письмо сгенерировано автоматически.<br>',
  'BODY_TYPE' => 'html',
  'BCC' => '',
  'REPLY_TO' => '',
  'CC' => '',
  'IN_REPLY_TO' => '',
  'PRIORITY' => '',
  'FIELD1_NAME' => '',
  'FIELD1_VALUE' => '',
  'FIELD2_NAME' => '',
  'FIELD2_VALUE' => '',
  'SITE_TEMPLATE_ID' => '',
  'ADDITIONAL_FIELD' => 
  array (
  ),
  'LANGUAGE_ID' => '',
  'EVENT_TYPE' => '[ FORM_FILLING_SELECTION_WARDROBE ] Заполнена web-форма "SELECTION_WARDROBE"',
));
            $helper->Event()->saveEventMessage('FORM_FILLING_SELECTION_WARDROBE', array (
  'LID' => 
  array (
    0 => 's1',
  ),
  'ACTIVE' => 'Y',
  'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
  'EMAIL_TO' => '#CLIENT_EMAIL#',
  'SUBJECT' => 'Подбора шкафа  #SITE_NAME#',
  'MESSAGE' => '<p>
	 Добрый день, <b>#CLIENT_NAME#</b>!
</p>
<p>
	 Благодарим Вас за обращение в нашу компанию! Мы получили Ваш запрос на предоставление дополнительной информации. С вами свяжуться в ближайшее время.
</p>
<p>
 <a href="https://185.103.132.46/upload/iblock/b08/%D0%9A%D0%B0%D0%BA%20%D0%B2%D1%8B%D0%B1%D1%80%D0%B0%D1%82%D1%8C%20%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9%20%D1%88%D0%BA%D0%B0%D1%84.pdf" target="_blank">Каталог шкафы-купе Е1</a>
</p>
<p>
	 С уважением,
</p>
<p>
	 Интернет магазин E1
</p>',
  'BODY_TYPE' => 'html',
  'BCC' => '',
  'REPLY_TO' => '',
  'CC' => '',
  'IN_REPLY_TO' => '',
  'PRIORITY' => '',
  'FIELD1_NAME' => '',
  'FIELD1_VALUE' => '',
  'FIELD2_NAME' => '',
  'FIELD2_VALUE' => '',
  'SITE_TEMPLATE_ID' => 'aspro_max_mail',
  'ADDITIONAL_FIELD' => 
  array (
  ),
  'LANGUAGE_ID' => '',
  'EVENT_TYPE' => '[ FORM_FILLING_SELECTION_WARDROBE ] Заполнена web-форма "SELECTION_WARDROBE"',
));
        }
}
