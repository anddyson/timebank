<?php include_once("components_include.php");

$Action = $_POST["Action"];
$UserId = $_SESSION["UserId"];

$db = MySQLConnection::GetInstance();

if ($Action == "submit-transaction")
{
  $Output = new DataActionResult();

  if ($LoginStatus == "true")
  {
    try
    {
      $Trn = new Transaction();
      $Trn = Utils::ArrayToObject($_POST, $Trn);
      $OtherUserId = $_POST["OtherUserId"];
      $Users = $db->GetUsers($UserId);
      $User = $Users[0];
      $Users = $db->GetUsers($OtherUserId);
      $OtherUser = $Users[0];

      //populate some of the properties of the transaction that can't be automatically added
      $UserAction = $_POST["Select-action"];
      if ($UserAction == "gave")
      {
        $Trn->GiverId = $UserId;
        $Trn->ReceiverId = $OtherUserId;
        $Trn->GiverApproved = true;
        $Trn->GiverApprovedDateTime = date("Y-m-d H:i");
      }
      else
      {
        $Trn->GiverId = $OtherUserId;
        $Trn->ReceiverId = $UserId;
        $Trn->ReceiverApproved = true;
        $Trn->ReceiverApprovedDateTime = date("Y-m-d H:i");
      }
  
      $ValResult = $Trn->Validate();
      if ($ValResult === true)
      {
        $Result = $db->AddTransaction($Trn);
        $Trn = $Result[0];
        
        $Email = new Email();
        $Email->from = "info@levytimebank.org.uk";
        $Email->to = $OtherUser->Email;
        $EmailReceiverName = $OtherUser->FirstName;
        $EmailSenderDetails = $User->FirstName." ".$User->LastName." (".$User->Username.")";
        $Email->subject = "[Levy Timebank] Confirm Exchange";
        $ConfirmUrl = "http://".Utils::GetCurrentDomain()."/confirm.php?t=".$Trn->UUid;
        $Email->body = "Dear ".$EmailReceiverName.",<br><br>".$EmailSenderDetails." recorded on Levy Timebank that they ".$UserAction." ".$Trn->Hours." Hour".($Trn->Hours == 1 ? "" : "s")." ".($UserAction == "gave" ? "to" : "from" )." you on ".date("l jS F Y")." for ".$Trn->Description.".<br><br>Please click on the link below to confirm that the exchange took place and the number of hours is right. (If the date or description aren't quite right it doesn't matter so much).<br><br>Once you confirm, your Timebank balance will be updated with the right number of hours.<br><br>Click here to confirm: <a href='".$ConfirmUrl."'>".$ConfirmUrl."</a>";
        $EmailResult = $Email->Send();
      }
      else
      {
        $Output->success = false;
        $Output->message = Utils::ArrayToCommaSeparatedList($ValResult);
      }
    }
    catch (Exception $e)
    {
        $ErrorId = Logger::LogException($e);
        $Output->success = false;
        $Output->data = new FriendlyException($ErrorId, 500, $e);
        $Output->message = $Output->data->getMessage();
    }
  }
  else
  {
    $Output->success = false;
    $Output->data = new Exception("Sorry, you can't perform that action when you're not logged in", 600);
  }
  echo json_encode($Output);
  die;
}
else if ($Action == "confirm-transaction")
{
  $Output = new DataActionResult();
  try
  {
    $db->ConfirmTransaction($_POST["TrnId"], $UserId);
  }
  catch (Exception $e)
  {
    $ErrorId = Logger::LogException($e);
    $Output->success = false;
    $Output->data = new FriendlyException($ErrorId, 500, $e);
    $Output->message = $Output->data->getMessage();
  }
  echo json_encode($Output);
  die;
}
else if ($Action == "get-user-transactions")
{
  $Output = new DataActionResult();
  try
  {
    $Transactions = $db->GetTransactions(null, $UserId);
    $Output->data = $Transactions;
  }
  catch (Exception $e)
  {
    $ErrorId = Logger::LogException($e);
    $Output->success = false;
    $Output->data = new FriendlyException($ErrorId, 500, $e);
    $Output->message = $Output->data->getMessage();
  }
  echo json_encode($Output);
  die;
}
else
{
?>
<script type="text/javascript">
var supressTransactionAutoCompleteChange = false;

$(function()
{
  // live search for other users
  $("#OtherUserText").autocomplete(
  {
    source: "searchusers.php"
    ,minLength: 2
    ,delay: 200
    ,autofocus: true
    ,select: function(event, ui)
    {
      $("#OtherUserId").val(ui.item.id);
      supressTransactionAutoCompleteChange = true;
    }
  });
  
  //clear the hidden field when the user starts to change the name
  $("#OtherUserText").change(function(event)
  {
    if (supressTransactionAutoCompleteChange == false) { $("#OtherUserId").val(''); }
    supressTransactionAutoCompleteChange = false;
  });
    
  // change between give and receive
  $("#Select-action").change(function(event)
  {
    switch ($(this).val())
    {
      case "gave":
        $("#Label-preposition").text("to");
        break;
      case "received":
        $("#Label-preposition").text("from");
        break;
      default:
    }
  });
  // date field
  $("#TransactionDateTime").datepicker({ dateFormat: "dd-mm-yy", showButtonPanel: false, closeText: "Cancel" });
  //hours field
  $("#Hours").spinner();

  //submit a transaction
  $("#form-transaction").validate({
        submitHandler: transaction_form_submit
        ,errorClass: "message"
        ,rules:
        {
          Hours: { required: true }
          ,OtherUserText: { required: true }
          ,Description: { required: true }
          ,TransactionDateTime: { required: true }
          ,OtherUserId: { required: true }
        }
        ,messages:
        {
          Hours: { required: "*" }
          ,OtherUserText: { required: "*" }
          ,Description: { required: "*" }
          ,TransactionDateTime: { required: "*" }
          ,OtherUserId: { required: "You must select a valid person from the list to exchange with" }
        }
  });
  
  function transaction_form_submit(form)
  {
    $("#transaction-message").text('');
    $("#transaction-progress").toggleClass("hidden");
    var data = $(form).serialize();
    $.post("transaction.php", data,
      function(result)
      {
        $("#transaction-progress").toggleClass("hidden");
        result = $.parseJSON(result);
        if (result.success == true)
        {
          $("#form-transaction")[0].reset();
          show_notification("Exchange recorded", "Your exchange was saved - thanks. The other party will be asked to confirm the details");
          $(document).trigger("transactionsaved", "Exchange recorded successfully");
        }
        else { $("#transaction-message").text(result.message); }
      }
    );
  }
});
</script>

<form id="form-transaction" name="form-account-details">
  <fieldset class="fieldset-table-style-small">
    <legend><img class="middle" src="images/32/exchange.png">&nbsp;Record an exchange</legend>
    <ul>
      <li><label for="Select-action" class="field-label">I&nbsp;</label>
    <select id="Select-action" name="Select-action">
      <option value="gave">gave</option>
      <option value="received">received</option>
    </select>
    <input type="text" id="Hours" name="Hours" maxlength="3" size="5" value="1">
    &nbsp;hours&nbsp;
  </li>
    <li>
    <label for="OtherUserText" class="field-label"><span id="Label-preposition">to</span>&nbsp;</label>
    <input type="text" id="OtherUserText" name="OtherUserText">&nbsp;(Search for a name or username)
    <div id="live-search-results" class="hidden"></div>
    </li>
    <li>
      <label for="Description" class="field-label">&nbsp;for&nbsp;</label>
    <input type="text" id="Description" name="Description"/>&nbsp;(Describe the activity or exchange)
    </li>
    <li>
      <label for="TransactionDateTime" class="field-label">&nbsp;on&nbsp;</label>
    <input type="text" id="TransactionDateTime" name="TransactionDateTime"/>&nbsp;(Date in dd-mm-yyyy format) 
    </li>
  </ul>
    <input type="submit" class="button" id="Submit-transaction" value="Submit" />
    <input type="text" id="OtherUserId" name="OtherUserId" class="invisible" />
    <input type="hidden" id="PostId" name="PostId"/>
    <input type="hidden" id="Action" name="Action" value="submit-transaction"/>
    <span id="transaction-progress" class="hidden"><img src="css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
    <span class="message" id="transaction-message"></span>
  </fieldset>
</form>
<?php
}
?>
