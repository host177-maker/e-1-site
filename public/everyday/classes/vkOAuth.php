<?php
  class vkOAuth
  {
    private $client_id = "5629681";
    private $client_secret = "dGea10ag28CfW241o2qj";
    private $redirect_uri = "/everyday/";
    private $url = "http://oauth.vk.com/authorize";

    private $params = array();

    public function __construct () {
            $protocol = (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']) ? "https://" : "http://");

      $this->params = array(
        'client_id'     => $this->client_id,
        'redirect_uri'  => $protocol . $_SERVER['HTTP_HOST'] . $this->redirect_uri,//$protocol . 'www.e-1.ru' . $this->redirect_uri,
        'response_type' => 'code'
      );
    }

    public function getLink () {
      return $this->url . '?' . urldecode(http_build_query($this->params));
    }

    public function action () {
      if (isset($_GET['code'])) {
        $this->params['code'] = $_GET['code'];
        $this->params['client_secret'] = $this->client_secret;

        $token = $this->sendGet('https://oauth.vk.com/access_token', $this->params);

        if (isset($token['access_token'])) {
          $params = array(
            'user_id'      => $token['user_id'],
            'fields'       => 'uid,first_name,last_name,screen_name,bdate,photo_big,contacts',
            'access_token' => $token['access_token'],
            'v'            => '5.95'
          );

          $userInfo = $this->sendGet('https://api.vk.com/method/users.get', $params);
          if (isset($userInfo['response'][0]['id'])) {
            return $userInfo['response'][0];
          }
        }
      }

      return false;
    }

        public function sendGet($method, $params = array(), $headers = array())
        {
            if (is_array($params)) {
                $requestParams = urldecode(http_build_query($params, '', '&', PHP_QUERY_RFC3986));
            }

            if (strlen($requestParams) > 0) {
                $url = $method . '?' . $requestParams;
            } else {
                $url = $method;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'
            ));

            $result = curl_exec($ch);
            $errorCode = curl_errno($ch);

            curl_close($ch);

            if ($errorCode == CURLE_OK) {
                return json_decode($result, true);
            } else {
                return false;
            }
        }
  }
?>