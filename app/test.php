<?php
  $DbServer = "localhost";
  $DbName = "andr0135_timebank";
  //private $DbName = "timebank";
  //private $DbUsername = "andr0135_timebnk";
  $DbUsername = "andr0135_admin";
//  private $DbUsername = "timebank_user";
//  private $DbPassword = "levy2012";
  $DbPassword = "l3vy2013!";

class Category
{
  public $Id;
  public $Name;
}
class Utils
{
  //expects to receive an already instantiated copy of a class
  static function ArrayToObject($Array, $Obj)
  {
      foreach ($Array as $Key => $Val)
      {
        //only add the item to the class if a property exists with that name
        if (property_exists($Obj, $Key)) 
        {
          if (is_string($Val))
          {
            //if it's a string, make sure there's no malicious code or banned words in there
          }
          $Obj->{$Key} = $Val;
        }
      }
    
      return $Obj;
  }
 
  //get the current URL without the filename
  static function GetCurrentUrl()
  {
    $currentURL = $_SERVER["PHP_SELF"];
    $parts = explode('/', $currentFile);
    for ($count = 0; $count < sizeof($parts); $count++)
    {
      $currentURL .= $parts[$count]."/";
    }
    return $currentURL;
  }
  
  //get the current domain name
  static function GetCurrentDomain()
  {
    return $_SERVER['HTTP_HOST'];
  }
}

  echo Utils::GetCurrentDomain();
  echo "<br/>";
  echo Utils::GetCurrentURL();
  /*$ClassName = "Category";
$ParamArray = array("CatId" => "2", "UserId" => '2013-03-02');
//$ParamArray = array(0);
$SpcName = "spc_GetCategories";

      $Results = array();
      $ParamString = "";

$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ALL;
      
      try
      {
$mysqli = new mysqli("localhost", $DbUsername, $DbPassword, $DbName);
//    $mysqli = new mysqli($this->DbServer, $this->DbUsername, $this->DbPassword, $this->DbName);

         foreach ($ParamArray as $Value)
         {
            if ($ParamString != "") $ParamString .= ",";
           if (!is_null($Value))
           {
             $Value = $mysqli->escape_string($Value);
             if (!is_numeric($Value)) { $ParamString .= "'".$Value."'"; }
             else { $ParamString .= $Value; }
           }
           else $ParamString .= "null";
         }
         
echo $ParamString."<br/><br/>";

        $Sql = "CALL ".$SpcName."(".$ParamString.")";
        $Res = $mysqli->query($Sql);
//        $Stmt = $mysqli->prepare($Sql);
//        $Stmt->bind_param('sss', $_POST['EmailID'], $_POST['SLA'], $_POST['Password']);
//        $Stmt->execute();
//        $Res = $Stmt->get_result();

        if ($ClassName != null)
        {
          $ObjectResults = array();
          while ($Row = $Res->fetch_array(MYSQLI_ASSOC))
          {
            $Obj = new $ClassName;
            $ObjectResults[] = Utils::ArrayToObject($Row, $Obj);
          }
          $Results[] = $ObjectResults;
        }

      }
      catch (Exception $e)
      {
        //try { $this->RollBack(); } catch (Exception $e2) {} // if a transaction was active, roll it back. If not, catch the resulting exception
        throw $e;
      }

  var_dump($Results);*/


