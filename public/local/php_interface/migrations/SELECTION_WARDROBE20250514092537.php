<?php

namespace Sprint\Migration;


class SELECTION_WARDROBE20250514092537 extends Version
{
    protected $author = "joy_999@mail.ru";

    protected $description = "[ticket/18445] Создает миграцию формы \"Заказать подбор\"";

    protected $moduleVersion = "5.0.2";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $formId = $helper->Form()->saveForm(array (
  'NAME' => 'Заказать подбор',
  'SID' => 'SELECTION_WARDROBE',
  'BUTTON' => 'Отправить',
  'C_SORT' => '290',
  'MAIL_EVENT_TYPE' => 'FORM_FILLING_SELECTION_WARDROBE',
  'FILTER_RESULT_TEMPLATE' => '',
  'TABLE_RESULT_TEMPLATE' => '',
  'STAT_EVENT2' => 'selection_wardrobe',
  'arSITE' => 
  array (
    0 => 's1',
  ),
  'arMENU' => 
  array (
    'ru' => 'Заказать подбор',
    'en' => '',
  ),
  'arGROUP' => 
  array (
  ),
  'arMAIL_TEMPLATE' => 
  array (
    0 => 
    array (
      'EVENT_NAME' => 'FORM_FILLING_SELECTION_WARDROBE',
      'SUBJECT' => 'Заказ подбора шкафа с сайта #SITE_NAME#',
    ),
    1 => 
    array (
      'EVENT_NAME' => 'FORM_FILLING_SELECTION_WARDROBE',
      'SUBJECT' => 'Подбора шкафа  #SITE_NAME#',
    ),
  ),
));
        $helper->Form()->saveStatuses($formId, array (
  0 => 
  array (
    'CSS' => 'statusgreen',
    'TITLE' => 'DEFAULT',
    'DESCRIPTION' => '',
    'HANDLER_OUT' => '',
    'HANDLER_IN' => '',
    'arPERMISSION_VIEW' => 
    array (
      0 => '2',
    ),
    'arPERMISSION_MOVE' => 
    array (
      0 => '2',
    ),
    'arPERMISSION_EDIT' => 
    array (
      0 => '2',
    ),
    'arPERMISSION_DELETE' => 
    array (
      0 => '2',
    ),
  ),
));
        $helper->Form()->saveFields($formId, array (
  0 => 
  array (
    'TITLE' => 'Ваше имя',
    'TITLE_TYPE' => 'text',
    'SID' => 'CLIENT_NAME',
    'REQUIRED' => 'Y',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'N',
    'FIELD_TYPE' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'text',
        'C_SORT' => '100',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  1 => 
  array (
    'TITLE' => 'Телефон',
    'TITLE_TYPE' => 'text',
    'SID' => 'PHONE',
    'C_SORT' => '200',
    'REQUIRED' => 'Y',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'N',
    'FIELD_TYPE' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'text',
        'FIELD_PARAM' => 'class="phone"',
        'C_SORT' => '100',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  2 => 
  array (
    'TITLE' => 'Ваш Email',
    'TITLE_TYPE' => 'text',
    'SID' => 'CLIENT_EMAIL',
    'C_SORT' => '300',
    'REQUIRED' => 'Y',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'N',
    'FIELD_TYPE' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'email',
        'FIELD_PARAM' => 'class="email"',
        'C_SORT' => '100',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  3 => 
  array (
    'TITLE' => 'Адрес страницы',
    'TITLE_TYPE' => 'text',
    'SID' => 'PAGE_URL',
    'C_SORT' => '400',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'N',
    'FIELD_TYPE' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'hidden',
        'FIELD_PARAM' => 'left',
        'C_SORT' => '100',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
));
    }
}

