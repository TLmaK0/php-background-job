<?php
class BackgroundJob{
  private $url, $params;
  private static $is_in_background;
  
  function __construct($params){
    $this->url = self::get_full_script_url();
    $this->params = $params;
  }

  public function start(){
    self::request_background_job($this->url, $this->params);
  }
  
  public static function execute_onrequest($params){
    if (self::is_background_job_request() && (self::get_background_job_url() == self::get_full_script_url())) {    
      ignore_user_abort(true);
      set_time_limit(0);
      if (self::is_in_background()) static::execute($params);
      else{
        self::set_in_background();
        self::request_background_job(self::get_background_job_url(), $params);
      }
    }
  }
  
  private function is_background_job_request(){
    return ISSET($_SERVER['HTTP_BACKGROUND_JOB_REQUEST']);
  }
  
  private function get_background_job_url(){
    return $_SERVER['HTTP_BACKGROUND_JOB_REQUEST'];
  }
  
  private static function get_full_script_url(){
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . self::get_script_file();
  }
  
  private static function is_in_background(){
    return self::$is_in_background || ISSET($_SERVER['HTTP_BACKGROUND_JOB_REQUEST_BACKGROUND']);
  }
  
  private static function set_in_background(){
    self::$is_in_background = true;
  }
  
  private static function request_background_job($url, $params, $headers = Array()){
    $debug = false;
    if (defined('BACKGROUND_JOB_DEBUG') && BACKGROUND_JOB_DEBUG) $debug = true;
    
    if (self::is_in_background()) $headers[] = 'Background-Job-Request-Background: true';
    $headers[] = "Background-Job-Request: $url";
       
    if ($debug) {
      echo "calling $url with:\n";
      var_dump($params);
      echo "headers:\n";
      var_dump($headers);
    }
    
    $curl= curl_init();
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
    
    if(!$debug) curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);    
    
    curl_setopt($curl, CURLOPT_POST, count($params));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    
    if($debug){
      $fp = fopen("request_".microtime().".log", "wr");
      curl_setopt($curl, CURLOPT_VERBOSE, true);
      curl_setopt($curl, CURLOPT_STDERR, $fp);
    }
    
    $response = curl_exec($curl);
    
    if($debug){
      echo $response;
      flush();
    }
    
    curl_close($curl);
    if($debug && $fp) fclose($fp);    
  } 
  
  private static function get_script_file(){
    $server_root = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);
    $file_path = str_replace('\\','/',static::$file_path);    
    $url_script = str_replace($server_root, '', $file_path);    
    return $url_script;
  }  
}
?>
