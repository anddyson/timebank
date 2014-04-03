<?php

class MySQLConnection
{

  /*************
  Variables
  **************/
  private static $instance = null; //the single instance of the class
  
  private $DbConnection;
  private $DbServer = "localhost";
  private $DbName = "andr0135_timebank";

  private $DbUsername = "andr0135_timebnk";
  //private $DbUsername = "andr0135_admin";

  private $DbPassword = "levy2012";
  //private $DbPassword = "l3vy2013!";

  //private $mode = "pdo";
  private $mode = "mysqli";

  /*************
  Constructor / Destructor. Note constructor is private - using Singleton pattern to prevent multiple DB connections being opened
  **************/
    private function __construct()
    {
      //name the destructor as the shutdown function in the case of a fatal error. Want to make sure we still close the database connection
      register_shutdown_function(array($this, '__destruct'));
    
      if ($this->mode == "pdo")
      {
        // open a connection to the database
        try
        {
          $this->DbConnection = new PDO("mysql:host=".$this->DbServer.";dbname=".$this->DbName, $this->DbUsername, $this->DbPassword);  
          $this->DbConnection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch(PDOException $e)
        {
          $ErrorId = Logger::LogException($e);
          throw new Exception("Sorry! An error has occured. Error logged with ID: ".$ErrorId, 500);
        }
      }
      else if ($this->mode == "mysqli")
      {
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_STRICT;
        try
        {
          $this->DBConnection = new mysqli($this->DbServer, $this->DbUsername, $this->DbPassword, $this->DbName);
        }
        catch (Exception $e)
        {
          $ErrorId = Logger::LogException($e);
          throw new Exception("Sorry! An error has occured. Error logged with ID: ".$ErrorId, 500);
        }
      }
    }

    public function __destruct()
    {
      // close the database connection when the object exits
      //if ($this->mode == "pdo") $this->DbConnection = null;
      //else if ($this->mode == "mysqli") $this->DBConnection->close();
      $this->DBConnection = null;
    }

  
  /*************
  Instantiator. Call this method to get the single instance of the class for use
  **************/
    public static function GetInstance()
    {
      if (!isset($instance)) $instance = new MySQLConnection();
      return $instance;
    }


  /*************
  Core functions
  **************/
    private function RunStoredProcedure($SpcName, $ParamArray = null, $ClassName = null)
    {
      $Results = array();
      $ParamString = "";
      
      if ($this->mode == "pdo")
      {
        try
        {
          if (!is_null($ParamArray))
          {
            $ParamNamesArray = array_keys($ParamArray);
            foreach ($ParamNamesArray as &$Value) $Value = (":".$Value);
            $ParamString = implode(",", $ParamNamesArray); // comma-separated list of the parameter names, prefixed with : so the statement preparation engine understands them
          }

          $Stmt = $this->DbConnection->prepare("CALL ".$SpcName."(".$ParamString.")");
//$Stmt->debugDumpParams(); echo "<br/><br/>";
          if (!is_null($ParamArray)) $Stmt->execute($ParamArray);
          else $Stmt->execute();

          if ($ClassName != null)
          {
            do
            {
              $Results[] = $Stmt->fetchAll(PDO::FETCH_CLASS, $ClassName);
            } while ($Stmt->nextRowset());
          }
        }
        catch (PDOException $e)
        {
          try { $this->RollBack(); } catch (PDOException $e2) {} // if a transaction was active, roll it back. If not, catch the resulting exception
          throw $e;
        }
      }
      else if ($this->mode == "mysqli")
      {
        //$driver = new mysqli_driver();
        //$driver->report_mode = MYSQLI_REPORT_STRICT;

        try
        {
          if (!is_null($ParamArray))
          {
            foreach ($ParamArray as $Value)
            {
              if ($ParamString != "") $ParamString .= ",";
              if (!is_null($Value))
              {
                $Value = $this->DBConnection->escape_string($Value);
                if (!is_numeric($Value)) { $ParamString .= "'".$Value."'"; }
                else { $ParamString .= $Value; }
              }
              else $ParamString .= "null";
            }
          }
          
          $Sql = "CALL ".$SpcName."(".$ParamString.")";
          $ResSet = $this->DBConnection->multi_query($Sql);
          if (!$ResSet) throw new Exception($this->DBConnection->error);
//        $Stmt = $this->DBConnection->prepare($Sql); //requires mysqlnd driver, not installed on host
//        $Stmt->execute();
//        $Res = $Stmt->get_result();

          do
          {
            if ($Res = $this->DBConnection->store_result())
            {
              if ($ClassName != null)
              {
                $ObjectResults = array();
                while ($Row = $Res->fetch_array(MYSQLI_ASSOC))
                {
                  $Obj = new $ClassName;
                  $ObjectResults[] = Utils::ArrayToObject($Row, $Obj, true);
                }
                $Results[] = $ObjectResults;
              }
              $Res->free();
            }
          }
          while ($this->DBConnection->next_result());
        }
        catch (Exception $e)
        {
          //try { $this->RollBack(); } catch (Exception $e2) {} // if a transaction was active, roll it back. If not, catch the resulting exception
          throw $e;
        }
      }
    
      return $Results;
    }
    
  // begin transaction
  private function Begin()
  {
    $this->DbConnection->beginTransaction();
  }

  // commit transaction
  private function Commit()
  {
    $this->DbConnection->commit();
  }

  // rollback transaction
  private function Rollback()
  {
    $this->DbConnection->rollBack();
  }

  /*************
  Category
  **************/
  public function GetCategories($CatId = null, $PostId = null)
  {
    $Params = array("CatId" => $CatId, "PostId" => $PostId);
    $Results = $this->RunStoredProcedure("spc_GetCategories", $Params, "Category");
    return $Results[0];
  }
  
  public function AddCategory($Cat)
  {
    $Params = $this->SetCategoryParams($Cat);
    $Results = $this->RunStoredProcedure("spc_AddCategory", $Params);
  }
  
  public function UpdateCategory($Cat)
  {
    $Params = $this->SetCategoryParams($Cat);
    $Results = $this->RunStoredProcedure("spc_UpdateCategory", $Params);
  }
  
  public function DeleteCategory($CatId)
  {
    $Params = array("CatId" => $CatId);
    $Results = $this->RunStoredProcedure("spc_DeleteCategory", $Params);
  }
  
    private function SetCategoryParams($Cat)
    {
    $Params = get_object_vars($Cat);
    if ($Cat->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
      return $Params;
    }


  /*************
  PostBookmark
  **************/
  public function GetPostBookmarks($Id = null, $UserId = null)
  {
    $Params = array("Id" => $Id, "UserId" => $UserId);
    $Results = $this->RunStoredProcedure("spc_GetPostBookmarks", $Params, "PostBookmark");
    return $Results[0];
  }

  public function AddPostBookmark($Pbm)
  {
    $Params = $this->SetPostBookmarkParams($Pbm);
    $Results = $this->RunStoredProcedure("spc_AddPostBookmark", $Params);
  }

  public function DeletePostBookmark($PbmId = null, $UserId = null, $PostId = null)
  {
    $Params = array("PbmId" => $PbmId, "UsrId" => $UserId, "PstId" => $PostId);
    $Results = $this->RunStoredProcedure("spc_DeletePostBookmark", $Params);
  }

  private function SetPostBookmarkParams($Pbm)
  {
    $Params = get_object_vars($Pbm);
    if ($Pbm->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
    return $Params;
  }

  /*************
  UserBookmark
  **************/
  /*  public function GetUserBookmarks($UbmId = null)
  {
    $Params = array("UbmId" => $UbmId);
    $Results = $this->RunStoredProcedure("spc_GetUserBookmarks", $Params, "UserBookmark");
    return $Results[0];
  }

  public function AddUserBookmark($Ubm)
  {
    $Params = $this->SetUserBookmarkParams($Ubm);
    $Results = $this->RunStoredProcedure("spc_AddUserBookmark", $Params);
  }

  public function UpdateUserBookmark($Ubm)
  {
    $Params = $this->SetUserBookmarkParams($Ubm);
    $Results = $this->RunStoredProcedure("spc_UpdateUserBookmark", $Params);
  }

  public function DeleteUserBookmark($UbmId)
  {
    $Params = array("Id" => $UbmId);
    $Results = $this->RunStoredProcedure("spc_DeleteUserBookmark", $Params);
  }

    private function SetUserBookmarkParams($Ubm)
    {
    $Params = get_object_vars($Ubm);
    if ($Ubm->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
      return $Params;
}*/

  /*************
  Transaction
  **************/
  public function GetTransactions($TrnId = null, $UserId = null)
  {
    $Params = array("TrnId" => $TrnId, "UserId" => $UserId);
    $Results = $this->RunStoredProcedure("spc_GetTransactions", $Params, "Transaction");
    $Transactions = $Results[0];
    foreach ($Transactions as $Trn)
    {
      $Users = $this->GetUsers($Trn->GiverId, null, "UserBasicDetails");
      $Trn->Giver = $Users[0];
      $Users = $this->GetUsers($Trn->ReceiverId, null, "UserBasicDetails");
      $Trn->Receiver = $Users[0];
    }
    return $Transactions;
  }

  public function AddTransaction($Trn)
  {
    $Params = $this->SetTransactionParams($Trn);
    $Results = $this->RunStoredProcedure("spc_AddTransaction", $Params, "Transaction");
    return $Results[0];
  }

  public function UpdateTransaction($Trn)
  {
    $Params = $this->SetTransactionParams($Trn);
    $Results = $this->RunStoredProcedure("spc_UpdateTransaction", $Params);
  }

  public function ConfirmTransaction($TrnId = null, $UserId = null, $Uuid = null)
  {
    $Params = array("TrnId" => $TrnId, "UserId" => $UserId, "Uuid" => $Uuid);
    $Results = $this->RunStoredProcedure("spc_ConfirmTransaction", $Params);
  }

  public function DeleteTransaction($TrnId)
  {
    $Params = array("Id" => $TrnId);
    $Results = $this->RunStoredProcedure("spc_DeleteTransaction", $Params);
  }

    private function SetTransactionParams($Trn)
    {
      $Params = get_object_vars($Trn);
      if ($Trn->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
      unset($Params["UUid"]); //this is created by the DB and doesn't need to be inserted/updated
      unset($Params["Giver"]);
      unset($Params["Receiver"]); //these are convenience classes, don't submit to DB
      $Params["TransactionDateTime"] = Utils::GetUniversalDateString($Params["TransactionDateTime"]); //check formatting of date
      return $Params;
    }
    
  /*************
  Message
  **************/
  public function GetMessages($MsgId = null, $SenderId = null, $ReceiverId = null, $ReadFlag = null)
  {
    $Params = array("MsgId" => $MsgId, "SenderId" => $SenderId, "ReceiverId" => $ReceiverId, "ReadFlag" => $ReadFlag);
    $Results = $this->RunStoredProcedure("spc_GetMessages", $Params, "Message");
    $Messages = $Results[0];
    foreach ($Messages as $Msg)
    {
      $Users = $this->GetUsers($Msg->SenderId, null, "UserBasicDetails");
      $Msg->Sender = $Users[0];
      $Users = $this->GetUsers($Msg->ReceiverId, null, "UserBasicDetails");
      $Msg->Receiver = $Users[0];
    }
    return $Messages;
  }

  public function AddMessage($Msg)
  {
    $Output = new DataActionResult();
    $ValResult = $Msg->Validate();
    if ($ValResult === true)
    {
      $Params = $this->SetMessageParams($Msg);
      $Results = $this->RunStoredProcedure("spc_AddMessage", $Params);
      $Output->data = $Results[0];
    }
    else
    {
      $Output->success = false;
      $Output->message = Utils::ArrayToCommaSeparatedList($ValResult);
    }
    return $Output;
  }

  public function UpdateMessageRead($MsgId)
  {
    $Params = array("Id" => $MsgId);
    $Results = $this->RunStoredProcedure("spc_UpdateMessageRead", $Params);
  }

  public function UpdateMessageUnread($MsgId)
  {
    $Params = array("Id" => $MsgId);
    $Results = $this->RunStoredProcedure("spc_UpdateMessageUnread", $Params);
  }

  public function DeleteMessage($MsgId, $UserId)
  {
    $Params = array("MsgId" => $MsgId, "UserId" => $UserId);
    $Results = $this->RunStoredProcedure("spc_DeleteMessage", $Params);
  }

    private function SetMessageParams($Msg)
    {
      $Params = get_object_vars($Msg);
      if ($Msg->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
      unset($Params["ReadDateTime"]);
      unset($Params["SentDateTime"]);
      unset($Params["Sender"]);
      unset($Params["Receiver"]); //these two are just convenience objects, never get passed to the DB
      return $Params;
    }
    
    
  /*************
  User
  **************/
  public function GetUsers($UsrId = null, $SearchString = null, $ObjectType = "User")
  {
    $Params = array("UsrId" => $UsrId, "SearchString" => "%".$SearchString."%", "BasicOnly" => ($ObjectType == "User" ? false : true));
    $Results = $this->RunStoredProcedure("spc_GetUsers", $Params, $ObjectType); //ObjectType expects to be either "User" or "UserBasicDetails"
    return $Results[0];
  }

  public function AddUser($Usr)
  {
    $Output = new DataActionResult();
    $ValResult = $Usr->Validate();

    if ($ValResult === true)
    {    
      $Params = $this->SetUserParams($Usr);
      $Results = $this->RunStoredProcedure("spc_AddUser", $Params, "User");
      $Output->data = $Results[0];
    }
    else
    {
      $Output->success = false;
      $Output->message = Utils::ArrayToCommaSeparatedList($ValResult);
    }
    return $Output;
  }

  public function UpdateUser($Usr)
  {
    $Output = new DataActionResult();
    $ValResult = $Usr->Validate();

    if ($ValResult === true)
    {    
      $Params = $this->SetUserParams($Usr);
      $Output->data = $this->RunStoredProcedure("spc_UpdateUser", $Params);
    }
    else
    {
      $Output->success = false;
      $Output->message = Utils::ArrayToCommaSeparatedList($ValResult);
    }
    return $Output;
  }

  public function UpdateUserPassword($Usr)
  {
    //special case which requires custom validation of the User object:
    
    $Output = new DataActionResult();
    $Params = array("Id" => $Usr->Id, "Password" => $Usr->Password);
    $Output->data = $this->RunStoredProcedure("spc_UpdateUserPassword", $Params);
    return $Output;
  }

  public function ActivateUser($UUid)
  {
    //special case which requires custom validation of the User object:
    
    $Output = new DataActionResult();
    $Params = array("UUid" => $UUid);
    $Results = $this->RunStoredProcedure("spc_ActivateUser", $Params, "User");
    $Output->data = $Results[0];
    return $Output;
  }

  public function DeleteUser($UsrId)
  {
    $Params = array("Id" => $UsrId);
    $Results = $this->RunStoredProcedure("spc_DeleteUser", $Params);
  }

  public function VerifyUserLogin($Usr)
  {
    //TODO: special case which requires custom validation of the User object:
    
    $Params = array("Email" => $Usr->Email, "Username" => $Usr->Username, "Password" => $Usr->Password);
    $Results = $this->RunStoredProcedure("spc_VerifyUserLogin", $Params, "User");
    return $Results[0];
  }
  
  public function GetUserPassword($Identifier)
  {
    $Params = array("Identifier" => $Identifier);
    $Results = $this->RunStoredProcedure("spc_GetUserPassword", $Params, "User");
    return $Results[0];
  }

  private function SetUserParams($Usr)
  {
    $Params = get_object_vars($Usr);
    if ($Usr->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
    unset($Params["RolesList"]); //this is a list of other objects, not a parameter
	unset($Params["UUid"]);
    return $Params;
  }  


  /*************
  UserStats
  **************/
  public function GetUserStats($UserId = null)
  {
    $Params = array("UserId" => $UserId);
    $Results = $this->RunStoredProcedure("spc_GetUserStats", $Params, "UserStats");
    return $Results[0];
  }

  /*************
  TransactionNotifications
  **************/
  public function GetTransactionNotifications($UserId = null)
  {
    $Params = array("UserId" => $UserId);
    $Results = $this->RunStoredProcedure("spc_GetTransactionNotifications", $Params, "TransactionNotification");
    $Notifications = $Results[0];
    foreach ($Notifications as $Not)
    {
      $Users = $this->GetUsers($Not->OtherUserId);
      $Not->OtherUser = $Users[0];
    }
    return $Notifications;
  }


  /*************
  Post
  **************/
  public function GetPosts($PostId = null, $UserId = null)
  {
    $Params = array("PostId" => $PostId, "UserId" => $UserId, "SearchString" => null, "Wanted" => null, "Offered" => null, "DateFrom" => null, "CategoryIds" => null, "ShowExpired" => null, "LogSearch" => false);
    $Results = $this->RunStoredProcedure("spc_GetPosts", $Params, "Post");
    $Posts = $Results[0];
    foreach ($Posts as $Pst) $Pst->Categories = $this->GetCategories(null, $Pst->Id);
    return $Posts;
  }

  public function GetPostsBookmarked($UserId = null)
  {
    $Params = array("UserId" => $UserId);
    $Results = $this->RunStoredProcedure("spc_GetPostsBookmarked", $Params, "Post");
    $Posts = $Results[0];
    foreach ($Posts as $Pst) $Pst->Categories = $this->GetCategories(null, $Pst->Id);
    foreach ($Posts as $Pst)
    {
      $Users = $this->GetUsers($Pst->UserId, null, "UserBasicDetails");
      $Pst->User = $Users[0];
    }
    return $Posts;
  }

  public function AddPost($Pst)
  {
    $Output = new DataActionResult();
    $ValResult = $Pst->Validate();
    if ($ValResult === true)
    {
      $Params = $this->SetPostParams($Pst);
      $Results = $this->RunStoredProcedure("spc_AddPost", $Params);
      return true;
    }
     else return $ValResult;
  }

  public function UpdatePost($Pst)
  {
    $ValResult = $Pst->Validate();
    if ($ValResult === true)
    {
      $Params = $this->SetPostParams($Pst);
      $Results = $this->RunStoredProcedure("spc_UpdatePost", $Params);
      return true;
  }
  else return $ValResult;
  }

  public function DeletePost($PstId)
  {
    $Params = array("PstId" => $PstId);
    $Results = $this->RunStoredProcedure("spc_DeletePost", $Params);
  }

  private function SetPostParams($Pst)
  {
    $Params = get_object_vars($Pst);
    if ($Pst->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
    unset($Params["User"]); //this isn't passed to the DB, just a convenience object
    unset($Params["Categories"]); // this isn't passed to the DB, needs to be converted to a comma-separated list
    //these two are never passed to the DB, it populates them itself
    unset($Params["CreatedDate"]);
    unset($Params["UpdatedDate"]);
    $Params["ExpiryDate"] = Utils::GetUniversalDateString($Params["ExpiryDate"]); //check formatting of date
    $Params["CategoryIds"] = Utils::ObjectFieldToCommaSeparatedList($Pst->Categories, "Id");
    return $Params;
  }

  /*************
  Role
  **************/
  public function GetRoles($RolId = null)
  {
    $Params = array("RolId" => $RolId);
    $Results = $this->RunStoredProcedure("spc_GetRoles", $Params, "Message");
    return $Results[0];
  }

  public function AddRole($Rol)
  {
    $Params = $this->SetRoleParams($Rol);
    $Results = $this->RunStoredProcedure("spc_AddRole", $Params);
  }

  public function UpdateRole($Rol)
  {
    $Params = $this->SetRoleParams($Msg);
    $Results = $this->RunStoredProcedure("spc_UpdateRole", $Params);
  }

  public function DeleteRole($RolId)
  {
    $Params = array("Id" => $RolId);
    $Results = $this->RunStoredProcedure("spc_DeleteRole", $Params);
  }

    private function SetRoleParams($Rol)
    {
    $Params = get_object_vars($Rol);
    if ($Rol->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
      return $Params;
    }
    

  /*************
  SearchQuery
  **************/
  public function SearchPosts($Sqr)
  {
    $Params = $this->SetSearchQueryParams($Sqr);
    $Results = $this->RunStoredProcedure("spc_GetPosts", $Params, "Post");
    $Posts = $Results[0];
    foreach ($Posts as $Pst)
    {
      $Users = $this->GetUsers($Pst->UserId, null, "UserBasicDetails");
      $Pst->User = $Users[0];
    }
    return $Posts;
  }
  
  public function GetSearchQueries($SqrId = null)
  {
    $Params = array("SqrId" => $SqrId);
    $Results = $this->RunStoredProcedure("spc_GetSearchQueries", $Params, "SearchQuery");
    return $Results[0];
  }

  private function SetSearchQueryParams($Sqr)
  {
    $Sqr->SearchString = "%".$Sqr->SearchString."%";
    $Params = get_object_vars($Sqr);
    $Params = array_merge(array("PostId" => null, "UserId" => null), $Params);
    $Params["ShowExpired"] = false;
    $Params["DateFrom"] = Utils::GetUniversalDateString($Params["DateFrom"]); //check formatting of date
    if ($Sqr->Id <= 0) unset($Params["Id"]); //remove ID parameter if it's not set (for insert statements or searches returning multiple items)
    return $Params;
  }
}
?>
