<?php
  
class Logger
{
  private static $LogFileName = "../logs/log.txt";

  public static function LogMessage($text)
  {
    return self::WriteToLog("Message", $text);
  }
  
  public static function LogWarning($text)
  {
    return self::WriteToLog("Warning", $text);
  }

  public static function LogException($ex)
  {
    return self::WriteToLog("Exception", $ex->__toString());
  }
  
  private static function WriteToLog($type, $message)
  {
    $errId = self::GetUniqueId();
    $output = date("Y-m-d h:i:s")."\t".$type."\t ID ".$errId.":\t".$message.PHP_EOL.PHP_EOL;
    file_put_contents(self::$LogFileName, $output, FILE_APPEND | LOCK_EX);
    return $errId;
  }
  
  private static function GetUniqueId()
  {
    //UNIX timestamp should be unique in the vast majority of cases, and is inexpensive to generate. Errors hopefully won't be so frequent(!), even  with large numbers of concurrent users
    return time();
  }  
}
?>
