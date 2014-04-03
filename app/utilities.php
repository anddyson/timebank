<?php

class Utils
{
  //expects to receive an already instantiated copy of a class
  static function ArrayToObject($Array, $Obj, $stripslashes = false)
  {
      foreach ($Array as $Key => $Val)
      {
        //only add the item to the class if a property exists with that name
        if (property_exists($Obj, $Key)) 
        {
          if (is_string($Val))
          {
            //if it's a string, make sure there's no malicious code or banned words in there
            $Val = Utils::CleanHTML($Val, "");
            $Val = Utils::CleanLanguage($Val, "preset");
            if ($stripslashes == true) $Val = stripslashes($Val);
          }
          $Obj->{$Key} = $Val;
        }
      }
    
      return $Obj;
  }
  
  static function ArrayToCommaSeparatedList($Array)
  {
    return implode(',', $Array);
  }
  
  //takes a whole array of objects, extracts the same field from each and returns the values as a comma-separated list
  static function ObjectFieldToCommaSeparatedList($ObjList, $Field)
  {
    $arr = array();
    foreach ($ObjList as $Obj) { $arr[] = $Obj->{$Field}; }
    return implode(',', $arr);
  }
  
  //gets the name of the current page
  static function GetCurrentFileName()
  {
    $currentFile = $_SERVER["PHP_SELF"];
    $parts = explode('/', $currentFile);
    $currentFile = $parts[count($parts) - 1];

  return $currentFile;
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
  
  //make universal date string in yyyy-mm-dd format from dd/mm/yyyy or dd-mm-yyyy string input
  static function GetUniversalDateString($date)
  {
    list($dy, $mon, $yr) = preg_split('/[\/.-]/', $date);
    return $yr."-".$mon."-".$dy;
  }
  
  //convert a date/time field into YYYY-MM-DD format
  static function ConvertDateToYMD($dt)
  {
    return date("Y-m-d", strtotime($dt));
  }
  
  //strips unwanted HTML tags from a string and returns the cleaned-up string
  static function CleanHTML($html, $allowedTags = "preset")
  {
    //cleans out all the tags except those specified in the second argument
    if ($allowedTags == "preset") $allowedTags = "<a><abbr><acronym><address><b><big><blockquote><br><caption><center><cite><code><col><colgroup><dd><del><dir><div><dfn><dl><dt><em><font><h1><h2><h3><h4><h5><h6><hr><i><img><ins><kbd><li><menu><ol><p><pre><q><s><samp><small><span><strike><strong><style><sub><sup><table><tbody><td><tfoot><th><thead><tr><tt><u><ul><var>";
    return strip_tags($html, $allowedTags);
  }
  
  //strips unwanted words from a string and returns the cleaned up string
  static function CleanLanguage($text, $bannedWords = "preset")
  {
    //cleans out all the words specified in the second argument. Note -it can only detect exact matches and spellings!
    if ($bannedWords == "preset") $bannedWords = array(" fuck"," shit "," wank "," cunt "," bollocks "," twat "," cocksucker "," arse "," shitting "," shitter "," bastard "," wanker "," shagging "," arsehole "," bugger "," jizz "," cumming "," spunk "," minge "," blowjob "," nigger ", " wog ");
    return str_replace($bannedWords, " ", $text);
  }
}
  
class Log
{
  static function MakeLogEntry($text)
  {
    return true;
  }
  
  static function LogException($ex)
  {
    return true;
  }
}
?>
