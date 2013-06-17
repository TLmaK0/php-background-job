<?php
class BackgroundJob{
  private $url, $params;
  
  function __construct($params){
    $base_url = (@$_SERVER["HTTPS"] ? "https" : "http") . "://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];

    $this->url = $base_url . static::get_script_file();
    $this->params = $params;
  }

  public function start(){
    $this->call($this->url, $this->params);
  }
  
  public static function execute_onrequest($params){
    if (static::get_script_file() == $_SERVER['SCRIPT_NAME']) {
      ignore_user_abort(true);
      set_time_limit(0);
      static::execute($params);
    }
  }
  
  private function call($url, $params){
    //$debug=true;
   //background execution
    $curl= curl_init();
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
    
    if(!ISSET($debug)) curl_setopt($curl, /*CURLOPT_CONNECTTIMEOUT_MS bug*/ 156, 1000);    
    
    curl_setopt($curl, CURLOPT_POST, count($params));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    
    if(ISSET($debug)){
      $fp = fopen("request_".microtime().".log", "wr");
      curl_setopt($curl, CURLOPT_VERBOSE, true);
      curl_setopt($curl, CURLOPT_STDERR, $fp);
    }
    
    $response = curl_exec($curl);
    
    if(ISSET($debug)) echo $response;
    
    curl_close($curl);
    if(ISSET($debug) && $fp) fclose($fp);    
  }  
  
  private static function get_script_file(){
    $server_root = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);;
    $file_path = str_replace('\\','/',static::$file_path);    
    $url_script = str_replace($server_root, '', $file_path);    
    return $url_script;
  }
}
?>
