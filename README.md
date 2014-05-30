php-background-job
==================

A background job for php


Create a class that extends BackgroundJob in a php script and add a call to execute_onrequest($_REQUEST)

```php
<?php
//runinbackground.php
include_once dirname(__FILE__) . '/background_job.php';

class RunInBackground extends BackgroundJob{
  protected static $file_path = __FILE__;

  public static function execute($params){
    //run your background or split in more background jobs with $job = new RunInBackground($newParams); $job->start();
  }
}

RunInBackground::execute_onrequest($_REQUEST);

?>
```

Then include your runinbackground.php in your normal script

```php
$job = new RunInBackground(Array("param1"=>"value1"));
$job->start();
```

Send more complex data with json

```php
...
  public static function execute($json_data){
    $params = json_decode($json_data);
    //run your background or split in more background jobs with $job = new RunInBackground($newParams); $job->start();
  }
}
RunInBackground::execute_onrequest(file_get_contents('php://input'));
...
...
$job = new RunInBackground(json_encode(Array("param1"=>"value1", "param3"=>Array("param4"=>"value4"))));
...
```

Enable debug mode with:

```php
define('BACKGROUND_JOB_DEBUG', true);
```