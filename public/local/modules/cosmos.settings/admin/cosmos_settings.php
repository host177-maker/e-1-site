<?php

use Bitrix\Main\Context,
  Cosmos\HtmlHelperSettings,
  \Bitrix\Main\Loader,
  \Bitrix\Sale\Internals\StatusLangTable;

require_once( $_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/main/include/prolog_admin_before.php" );

CJSCore::Init( [ "jquery" ] );

if ( $APPLICATION->GetGroupRight( 'cosmos.settings' ) == 'D' ) {
  LocalRedirect( '/bitrix/' );
}

$aTabs = [];

$aTabs = [
  [
    "DIV" => "tab_all_settings",
    "TAB" => "Дополнительные настройки сайта",
  ],
];

$settingList = [];

$settingList[ 'tab_all_settings' ] = [
  [
    'type'  => 'separator',
    'label' => 'Настройки для формы (Заказать звонок)',
  ],
  'topic_email_one' => [
    'type'  => 'string',
    'label' => 'Почта для темы (Заказать шкаф) - (СИМ)',
  ],
  'topic_email_two' => [
    'type'  => 'string',
    'label' => 'Почта для темы (Узнать статус заказа или Сообщить о браке) - (СКС)',
  ],
    [
        'type'  => 'separator',
        'label' => 'Настройки для формы (Задать вопрос)',
    ],
    'ask_topic_email_one' => [
        'type'  => 'string',
        'label' => 'Почта для темы (Заказать шкаф) - (СИМ)',
    ],
    'ask_topic_email_two' => [
        'type'  => 'string',
        'label' => 'Почта для темы (Узнать статус заказа или Сообщить о браке) - (СКС)',
    ],
];

$context = Context::getCurrent();
$request = $context->getRequest();

if ( $request->isPost() ) {
  // Сохранение настроек
  $PROPS = $request->getPost( "PROP" );

  foreach ( $settingList as $settings ) {
    foreach ( $settings as $name => $setting ) {
      if ( $setting[ 'type' ] == 'separator' ) {
        continue;
      }
      if ( $setting[ 'type' ] == 'file'
           && !empty( $_FILES[ 'PROP' ][ 'name' ][ $name ] )
      ) {
        $arr_file = [
          "name"      => $_FILES[ 'PROP' ][ 'name' ][ $name ],
          "size"      => $_FILES[ 'PROP' ][ 'size' ][ $name ],
          "tmp_name"  => $_FILES[ 'PROP' ][ 'tmp_name' ][ $name ],
          "type"      => "",
          "old_file"  => "",
          "del"       => "Y",
          "MODULE_ID" => "iblock",
        ];
        $fid = CFile::SaveFile( $arr_file, "settings" );

        \COption::SetOptionString( 'cosmos.settings', $name, $fid );

        continue;
      }
      elseif ( $setting[ 'type' ] == 'file' ) {
        $sDelName = $PROPS[ $name . '_del' ];
        if ( isset( $sDelName ) && $sDelName == 1 ) {
          $fileId = (int) \COption::GetOptionString(
            'cosmos.settings',
            $name
          );
          if ( $fileId > 0 ) {
            \CFile::Delete( $fileId );
            \COption::SetOptionString( 'cosmos.settings', $name, '' );
          }
        }
        continue;
      }

      \COption::SetOptionString(
        'cosmos.settings',
        $name,
        ( $PROPS[ $name ] ? : '' )
      );
    }
  }
}

require_once( $_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/main/include/prolog_admin_after.php" );

$APPLICATION->SetTitle( 'Дополнительные, общие настройки сайта' );

$tabControl = new CAdminTabControl( "tabControl", $aTabs );
?>
    <form action="" method="post" enctype="multipart/form-data">
      <?php
      $tabControl->Begin();

      foreach ( $aTabs as $key => $value ) {
        $tabControl->BeginNextTab();
        HtmlHelperSettings::renderSettings( $settingList[ $value[ "DIV" ] ] );
      }

      // завершение формы - вывод кнопок сохранения изменений
      $tabControl->Buttons(
        [
          "btnCancel" => FALSE,
          "btnSave"   => FALSE,
          "btnApply"  => TRUE,
        ]
      );
      ?>
      <?php
      // завершаем интерфейс закладки
      $tabControl->End();
      ?>
    </form>

<?php
require_once( $_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/modules/main/include/epilog_admin.php" );
