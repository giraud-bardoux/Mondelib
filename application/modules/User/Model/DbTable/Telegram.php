<?php

class User_Model_DbTable_Telegram extends Engine_Db_Table {

    protected $_name = 'user_telegram';

    public static function loginButton(){
        $dataTelegram = Engine_Api::_()->user()->telegramEnable();
        return '<a>
          <script async src="https://telegram.org/js/telegram-widget.js?15" data-telegram-login="'.$dataTelegram['username'].'" data-size="large" data-onauth="onTelegramLogin(user)" data-request-access="write"></script>
      </a><script>function onTelegramLogin(user){
        scriptJquery.post(en4.core.baseUrl+"user/auth/telegram",user,function(response){
          var data = JSON.parse(response);
          if(data.status == 1){
            window.location.href = en4.core.baseUrl+"signup";
          }else if(data.status == 2){
            window.location.href = data.url ? data.url : en4.core.baseUrl;
          }else{
            alert(data.message ?? data.error);
          }
        })
      }</script>';
    }
}
