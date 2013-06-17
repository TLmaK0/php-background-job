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

´´´php
$job = new RunInBackground(Array("param1"=>"param2"));
$job->start();
```
