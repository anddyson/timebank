<?php include_once("components_include.php");

$term = $_GET["term"]; //term is passed by the jQuery autocomplete plugin
$Username = trim(strtolower($_GET["Username"])); //value is passed by the jQuery validation plugin
$Email = trim(strtolower($_GET["Email"])); //value is passed by the jQuery validation plugin

if (strlen($term) > 0)
{
  //find users who match the search terms and return details

  $db = MySQLConnection::GetInstance();
  $users = $db->GetUsers(null, $term);
  
  $output = array();
  $usroutput = array();
  foreach ($users as $usr)
  {
    $usroutput["label"] = htmlspecialchars($usr->FirstName." ".$usr->LastName." (".$usr->Username.")");
    $usroutput["id"] = $usr->Id;
    $usroutput["username"] = $usr->Username;
    $output[] = $usroutput;
  }
  echo json_encode($output);
  die;
}
else if (strlen($Username) > 0)
{
  //check whether a proposed username is already in use

  $db = MySQLConnection::GetInstance();
  $users = $db->GetUsers(null, $Username);

  foreach ($users as $usr)
  {
    if (strtolower($usr->Username) == $Username)
    {
      echo "false"; //usernames are matched, so does not validate, hence return false
      die;
    }
  }
  
  echo "true";
  die;
}
else if (strlen($Email) > 0)
{
  //check whether a proposed email address is already in use

  $db = MySQLConnection::GetInstance();
  $users = $db->GetUsers(null, $Email);

  foreach ($users as $usr)
  {
    if (strtolower($usr->Email) == $Email)
    {
      echo "false"; //emails are matched, so does not validate, hence return false
      die;
    }
  }
  
  echo "true";
  die;
}
?>
