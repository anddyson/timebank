<?php
include_once("objectvalidation.php");

class FriendlyException extends Exception
{
  public function __construct($errorId = 0, $code = 0, Exception $previous = null)
  {
    $message = "Sorry! An error has occurred. The error has been logged with the following ID: ".$errorId.". Please email us - help@levytimebank.org.uk - and quote this ID.";
    parent::__construct($message, $code, $previous);
  }
}

//structure for returning the result of a data query or action. Can hold return values (e.g. result set, object, string) as well
class DataActionResult
{
  public $success = true;
  public $message = "";
  public $data = null;
}

class User
{
  public $Id = null;
  public $FirstName = null;
  public $LastName = null;
  public $Email = null;
  public $Phone = null;
  public $Address = null;
  public $Postcode = null;
  public $IsApproved = false;
  public $LastLoginDateTime = null;
  public $ReminderCount = 0;
  public $IsActive = false;
  public $Password = null;
  public $Username = null;
  public $UUid = null;
  public $RolesList = array();
  
  private $Validator;

  public function Validate()
  {
    $this->Validator = new ObjectValidator();
    $this->Validator->addValidation("FirstName","req","Please fill in First Name");
    $this->Validator->addValidation("LastName","req","Please fill in Last Name");
    $this->Validator->addValidation("Email","req","Please fill in Email");
    $this->Validator->addValidation("Email","email","Please submit a valid email address");
    $this->Validator->addValidation("Phone","req","Please fill in Phone");
    $this->Validator->addValidation("Address","req","Please fill in Address");
    $this->Validator->addValidation("Postcode","req","Please fill in Postcode");
    $this->Validator->addValidation("Username","req","Please fill in Username");
    $this->Validator->addValidation("FirstName","maxlen=100","First Name cannot be more than 100 characters");
    $this->Validator->addValidation("LastName","maxlen=100","Last Name cannot be more than 100 characters");
    $this->Validator->addValidation("Email","maxlen=100","Email cannot be more than 100 characters");
    $this->Validator->addValidation("Address","maxlen=500","Address cannot be more than 500 characters");
    $this->Validator->addValidation("Postcode","maxlen=10","Postcode cannot be more than 10 characters");
    $this->Validator->addValidation("Phone","maxlen=100","Phone cannot be more than 100 characters");
    $this->Validator->addValidation("Username","maxlen=100","Username cannot be more than 100 characters");
    $this->Validator->addValidation("Password","maxlen=50","Password cannot be more than 100 characters");
    $this->Validator->addValidation("Password","minlen=5","Password cannot be less than 5 characters");
  
    if ($this->Validator->ValidateObject($this))
    {
      return true;
    }
    else
    {
        $error_hash = $this->Validator->GetErrors();
        return $error_hash;
    }
  }
}

//for security, only holds the simplest bits of info, which can safely be output as JSON. Never receives input, so no validation needed
class UserBasicDetails
{
  public $Id = null;
  public $FirstName = null;
  public $LastName = null;
  public $Username = null;
}

class UserStats
{
  public $Id = null; //User Id
  public $Hours = 0; // can be + or -
  public $WantedCount = 0;
  public $OfferedCount = 0;
  public $UnreadMessages = 0;
  public $TransactionsPendingIncoming = 0;
  public $TransactionsPendingOutgoing = 0;
}

class Role
{
  public $Id = null;
  public $Name = null;
  public $Priority = null;
  public $UsersList = array();
}

class Post
{
  public $Id = null;
  public $Heading = null;
  public $Description = null;
  public $UserId = null; // make an alias of $User->Id
  public $User = null;
  public $CreatedDate = null;
  public $UpdatedDate = null;
  public $Type = null; // wanted = 1 / offered = 2
  public $ExpiryDate = null;
  public $Categories;
  
  private $Validator;
  
  public function __construct()
  {
    $this->User = new User();
    $this->Categories = array();
  }
  
  public function Validate()
  {
    $this->Validator = new ObjectValidator();
    $this->Validator->addValidation("Heading","req","Please fill in a Heading");
    $this->Validator->addValidation("Description","req","Please fill in a Description");
    $this->Validator->addValidation("ExpiryDate","req","Please fill in an Expiry Date");
    $this->Validator->addValidation("Type","req","Please specify what type of post it is");
    $this->Validator->addValidation("ExpiryDate","date","The expiry date is not a valid date (dd-mm-yyyy)");
    $this->Validator->addValidation("Type","lt=3","The post type must be 1 or 2");
    $this->Validator->addValidation("Type","gt=0","The post type must be 1 or 2");
    $this->Validator->addValidation("Name","maxlen=100","Heading cannot be more than 100 characters");
    $this->Validator->addValidation("Description","maxlen=1000","Description cannot be more than 1000 characters");
    

    if ($this->Validator->ValidateObject($this))
    {
      //this should be a custom validator, but there isn't time
      if (count($this->Categories) == 0)
      {
        return array("Categories" => "ERROR: You must select at least one category");
      }
      else
      {
        return true;
      }
    }
    else
    {
        $error_hash = $this->Validator->GetErrors();
        return $error_hash;
    }
  }
}
  
class PostBookmark
{
  public $Id = null;
  public $UserId = null;
  public $PostId = null;
}

class Category
{
  public $Id = null;
  public $Name = null;

  private $Validator;

  public function Validate()
  {
    $this->Validator = new ObjectValidator();
    $this->Validator->addValidation("Name","req","Please fill in Name");
    $this->Validator->addValidation("Name","maxlen=100","Name cannot be more than 100 characters");

    if ($this->Validator->ValidateObject($this))
    {
      return true;
    }
    else
    {
        $error_hash = $this->Validator->GetErrors();
        return $error_hash;
    }
  }
}

class Transaction
{
  public $Id = null;
  public $GiverId = null; // make an alias of $Giver->Id
  public $Giver = null;
  public $ReceiverId = null; // make an alias of $Receiver->Id
  public $Receiver = null;
  public $PostId = null;
  public $Hours = null;
  public $Description = null;
  public $GiverApproved = false;
  public $ReceiverApproved = false;
  public $GiverApprovedDateTime = null;
  public $ReceiverApprovedDateTime = null;
  public $TransactionDateTime = null;
  public $IsDisputed = false;
  public $DisputeRaisedDateTime = null;
  public $DisputeRaisedById = null;
  public $DisputeResolvedDate = null;
  public $DisputeResolvedById = null;
  public $DisputeNotes = null;
  public $UUid = null;

  private $Validator;

  public function Validate()
  {
    $this->Validator = new ObjectValidator();
    $this->Validator->addValidation("GiverId","req","Please supply the Id of the Giver");
    $this->Validator->addValidation("ReceiverId","req","Please supply the Id of the Receiver");
    $this->Validator->addValidation("Hours","req","Please fill in the number of Hours");
    $this->Validator->addValidation("GiverId","num","GiverId must be a number");
    $this->Validator->addValidation("ReceiverId","num","ReceiverId must be a number");
    $this->Validator->addValidation("Hours","num","Hours must be a number");
    $this->Validator->addValidation("PostId","num","PostId must be a number");
    $this->Validator->addValidation("Description","req","Please fill in the Description");
    $this->Validator->addValidation("Description","maxlength=500","Description cannot be more than 500 characters");
    $this->Validator->addValidation("TransactionDateTime","req","Please fill in the date of the exchange");
    $this->Validator->addValidation("TransactionDateTime","date","The date of the exchange is not a valid date (dd-mm-yyyy)");
    if ($this->IsDisputed == true) //should this be within a custom validator??
    {
      $this->Validator->addValidation("DisputeNotes","req","Please fill in Dispute Notes");
      $this->Validator->addValidation("DisputeNotes","maxlength=100","Dispute Notes cannot be more than 4000 characters");
      $this->Validator->addValidation("DisputeRaisedDateTime","req","Please fill in Dispute Raised Date");
      $this->Validator->addValidation("DisputeRaisedDateTime","date","Dispute Raised Date is not a valid date (dd-mm-yyyy)");
      $this->Validator->addValidation("DisputeRaisedById","req","Please supply the Id of the user who raised the dispute");
      $this->Validator->addValidation("DisputeRaisedById","num","DisputeRaisedById must be a number");
    }

    if ($this->Validator->ValidateObject($this))
    {
      return true;
    }
    else
    {
        $error_hash = $this->Validator->GetErrors();
        return $error_hash;
    }
  }
}

class TransactionNotification
{
  public $TransactionId = null;
  public $OtherUserId = null; // make an alias of $OtherUser->Id
  public $OtherUser = null;
  public $Type;
  public $Hours = null;
  public $Description = null;
  public $TransactionDateTime = null;
  public $Uuid = null;

  public function __construct()
  {
    $this->OtherUser = new User();
  }
}
  
class Message
{
  public $Id = null;
  public $SenderId = null; // make an alias of $Sender->Id
  public $Sender = null;
  public $ReceiverId = null; // make an alias of $Receiver->Id
  public $Receiver = null;
  public $Subject = null;
  public $Body = null;
  public $SentDateTime = null;
  public $PostId = null;
  public $ReadDateTime = null;
  public $ReadFlag = false;

  public function __construct()
  {
    $this->Sender = new User();
    $this->Receiver = new User();
  }

  private $Validator;

  public function Validate()
  {
    $this->Validator = new ObjectValidator();
    $this->Validator->addValidation("SenderId","req","Please supply the Id of the Sender");
    $this->Validator->addValidation("ReceiverId","req","Please supply the Id of the Receiver");
    $this->Validator->addValidation("Subject","req","Please fill in the Subject");
    $this->Validator->addValidation("Body","req","Please fill in the message body");
    $this->Validator->addValidation("SenderId","num","SenderId must be a number");
    $this->Validator->addValidation("ReceiverId","num","ReceiverId must be a number");
    $this->Validator->addValidation("PostId","num","PostId must be a number");
    $this->Validator->addValidation("Message","maxlen=200","Subject cannot be more than 200 characters");
    $this->Validator->addValidation("Body","maxlen=4000","Body cannot be more than 4000 characters");

    if ($this->Validator->ValidateObject($this))
    {
      return true;
    }
    else
    {
        $error_hash = $this->Validator->GetErrors();
        return $error_hash;
    }
  }  
}

class SearchQuery
{
  public $Id = null;
  public $SearchString = null;
  public $Wanted = true;
  public $Offered = true;
  public $DateFrom = null;
  public $CategoryIDs = null;
  public $LogSearch = false;

  private $Validator;

  public function Validate()
  {
    $this->Validator = new ObjectValidator();
    $this->Validator->addValidation("DateFrom","date","The date is not a valid date (dd-mm-yyyy)");
    $this->Validator->addValidation("SearchString","maxlen=200","Search text cannot be more than 200 characters");

    if ($this->Validator->ValidateObject($this))
    {
      return true;
    }
    else
    {
        $error_hash = $this->Validator->GetErrors();
        return $error_hash;
    }
  }
}
?>
