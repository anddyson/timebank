<?php
include_once("components_include.php");
  
$Action = $_POST["Action"];
$UserId = $_SESSION["UserId"];

$db = MySQLConnection::GetInstance();

if ($Action == "create-message")
{
  $Output = new DataActionResult();
  
  try
  {
    $Msg = new Message();
    $Msg = Utils::ArrayToObject($_POST, $Msg);
    $Msg->SenderId = $UserId;
    $Output = $db->AddMessage($Msg);

    if ($Output->success == true)
    {  
      $Users = $db->GetUsers($Msg->SenderId);
      $Sender = $Users[0];
      $Users = $db->GetUsers($Msg->ReceiverId);
      $Receiver = $Users[0];
  
      $Email = new Email();
      $Email->from = "info@levytimebank.org.uk";
      $Email->to = $Receiver->Email;
      $Email->subject = "[Levy Timebank] New Message";
      $Email->body = "Dear ".$Receiver->FirstName." ".$Receiver->LastName.",<br><br>You have a new message on Levy Timebank from ".$Sender->FirstName." ".$Sender->LastName." (".$Sender->Username.").<br><br><a href='http://".Utils::GetCurrentDomain()."'>Login here to view your message and reply</a>";
      $Email->Send();
    }
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
else if ($Action == "delete-message")
{
  $db->DeleteMessage($_POST["Id"], $UserId);
  echo "true";
  die;
}
else if ($Action == "read-message")
{
  $db->UpdateMessageRead($_POST["Id"]);
  $Results = $db->GetMessages($_POST["Id"]);
  echo json_encode($Results[0]);
  die;
}
else if ($Action == "get-message")
{
  $Results = $db->GetMessages($_POST["Id"]);
  echo json_encode($Results[0]);
  die;
}
else if ($Action == "mark-unread")
{
  $Results = $db->UpdateMessageUnread($_POST["Id"]);
  echo "true";
  die;
}
else
{
?>
<script type="text/javascript">
var supressMessageAutoCompleteChange = false;
  
$(function()
{
  //validate a message
  $("#form-edit-message").validate(
  {
        submitHandler: message_form_submit
        ,errorClass: "message"
        ,rules:
        {
          Subject: { required: true }
          ,Body: { required: true }
          ,ReceiverName: { required: true }
          ,ReceiverId: { required: true }
        }
        ,messages:
        {
          Subject: { required: "*" }
          ,Body: { required: "*" }
          ,ReceiverName: { required: "*" }
          ,ReceiverId: { required: "You must select a valid person from the list to send the message to" }

        }
  });
  
    // live search for other users
  $("#ReceiverName").autocomplete(
  {
    source: "searchusers.php",
    minLength: 2,
    select: function(event, ui) {
      $("#ReceiverId").val(ui.item.id);
      supressMessageAutoCompleteChange = true;   
    }
  });
  
  //clear the hidden field when the user starts to change the name
  $("#ReceiverName").change(function(event)
  {
    if (supressMessageAutoCompleteChange == false) { $("#ReceiverId").val(''); }
    supressMessageAutoCompleteChange = false;
  });
  
  function message_form_submit(form)
  {
    $("#edit-message-message").text('');
    $("#edit-message-progress").toggleClass("hidden");
    var data = $(form).serialize();
    $.post("message.php", data,
      function(result)
      {
        $("#edit-message-progress").toggleClass("hidden");
        result = $.parseJSON(result);
        if (result.success == true)
        {
          $("#form-edit-message")[0].reset();
          $(document).trigger("messagesent", "Message sent successfully");
          show_notification("Message Sent", "Your message was sent successfully");
        }
        else {  $("#edit-message-message").text(result.message); }
      }
    );    
  }
  
  //delete a message
  $("#message-delete").click(function(event)
  {
    $.post("message.php", { Action: "delete-message", Id: $("#readId").html() },
      function(result)
      {
        show_notification("Message Deleted", "Your message was deleted successfully");
        $(document).trigger("messagedeleted", "Message deleted successfully");
      }
    );
  });
  
  //reply to a message
  $("#message-reply").click(function(event)
  {
    set_edit_message_mode("reply");
  });
  
  //mark a message unread
  $("#message-markunread").click(function(event)
  {
    $.post("message.php", { Action: "mark-unread", Id: $("#readId").html() },
      function(result)
      {
        $(document).trigger("messageunread", "Message marked as unread");
      }
    );
  });
});

  function set_edit_message_mode(mode, id, receiver_id, receiver_name, subject)
  {
    switch (mode)
    {
      case "add":
        $("#form-edit-message").removeClass("hidden");
        $("#div-read-message").addClass("hidden");

        $("#edit-message-submit").val("Send");
        $("#edit-message-action").val("create-message");
        $("#Id").val('');
        if (receiver_id && receiver_name)
        {
          $("#ReceiverId").val(receiver_id);
          $("#ReceiverName").val(receiver_name);
        }
        if (subject) { $("#Subject").val(subject); }
        break;
      case "reply":
        $("#form-edit-message").removeClass("hidden");

        $("#edit-message-submit").val("Send");
        $("#edit-message-action").val("create-message");
        $("#Id").val('');

        $("#ReceiverId").val($("#readSenderId").html());
        $("#ReceiverName").val($("#readSenderName").html());
        $("#Subject").val('RE: ' + $("#readSubject").html());
        break;
      case "edit":
        $("#form-edit-message").removeClass("hidden");
        $("#div-read-message").addClass("hidden");

        $("#edit-message-submit").val("Update Message");
        $("#edit-message-action").val("update-message");

        $.post("message.php", { Action: "get-message", Id: id },
          function(result)
          {
            var msg = jQuery.parseJSON(result);
            $("#Id").val(msg.Id);
            $("#Id-label").html(msg.Id);
            $("#Subject").val(msg.Subject);
            $("#Body").val(msg.Body);
            $("#SentDateTime").val(msg.SentDateTime);
            $("#ReadFlag").val(msg.ReadFlag);
          }
        );
        break;
      case "read":
        $("#form-edit-message").addClass("hidden");
        $("#div-read-message").removeClass("hidden");
        $("#message-reply, #message-reply-separator, #message-delete-separator, #message-markunread").removeClass("hidden");

        $.post("message.php", { Action: "read-message", Id: id },
          function(result)
          {
            var msg = jQuery.parseJSON(result);
            $("#readId").html(msg.Id);
            $("#readSubject").html(msg.Subject);
            $("#readBody").html(msg.Body);
            $("#readSentDateTime").html(moment(msg.SentDateTime.replace(" ", "T")).format("ddd Do MMMM YYYY H:m"));
            $("#readReadFlag").html(msg.ReadFlag);
            $("#readSenderId").html(msg.SenderId);
            $("#readReceiverId").html(msg.ReceiverId);
            $("#readSenderName").html(msg.Sender.FirstName + ' ' + msg.Sender.LastName + ' (' + msg.Sender.Username + ')');
            $(document).trigger("messageread", "Message marked as read");
            $("#readReceiverName").html(msg.Receiver.FirstName + ' ' + msg.Receiver.LastName + ' (' + msg.Receiver.Username + ')');
          }
        );
        break; 
      case "sent":
        $("#form-edit-message").addClass("hidden");
        $("#div-read-message").removeClass("hidden");
        $("#message-reply, #message-reply-separator, #message-delete-separator, #message-markunread").addClass("hidden");

        $.post("message.php", { Action: "get-message", Id: id },
          function(result)
          {
            var msg = jQuery.parseJSON(result);
            $("#readId").html(msg.Id);
            $("#readSubject").html(msg.Subject);
            $("#readBody").html(msg.Body);
            $("#readSentDateTime").html(moment(msg.SentDateTime.replace(" ", "T")).format("ddd Do MMMM YYYY H:m"));
            $("#readReadFlag").html(msg.ReadFlag);
            $("#readSenderId").html(msg.SenderId);
            $("#readReceiverId").html(msg.ReceiverId);
            $("#readSenderName").html(msg.Sender.FirstName + ' ' + msg.Sender.LastName + ' (' + msg.Sender.Username + ')');
            $("#readReceiverName").html(msg.Receiver.FirstName + ' ' + msg.Receiver.LastName + ' (' + msg.Receiver.Username + ')');
          }
        );
        break; 
      default:
        break;
    }
  }
</script>
<div id="div-read-message" class="hidden">
  <h2><img class="middle" src="images/32/message.png">&nbsp;Message</h2>
  <span id="readId" class="hidden"></span>
  <span id="readSenderId" class="hidden"></span>
  <span id="readReceiverId" class="hidden"></span>
  <span id="readReadFlag" class="hidden"></span>
  <table class="grid-table">
    <tr><td>From:</td><td><b><span id="readSenderName"></span></b></td></tr>
    <tr><td>To:</td><td><b><span id="readReceiverName"></span></b></td></tr>
    <tr><td>Subject:</td><td><b><span id="readSubject"></span></b></td></tr>
    <tr><td>Sent:</td><td><span id="readSentDateTime"></span></td></tr>
  </table>
  <br/>
  <div class="message-body" id="readBody"></div>
  <br/><br/>
  <button id="message-reply" class="button">Reply</button><span id="message-reply-separator">&nbsp;&nbsp;</span>
  <button id="message-delete" class="button">Delete</button><span id="message-delete-separator">&nbsp;&nbsp;</span>
  <button id="message-markunread" class="button">Mark as Unread</button>
  <br/><br/>
</div>

<form id="form-edit-message" name="form-edit-message">
  <fieldset class="fieldset-table-style">
    <legend><img class="middle" src="images/32/message.png">&nbsp;Message</legend>
    <ul>
      <li><label class="field-label" for="ReceiverName">To:</label><input type="text" id="ReceiverName" name="ReceiverName" size="26">&nbsp;(Search for a name)</li>
      <li><label class="field-label" for="Subject">Subject:</label><input type="text" id="Subject" name="Subject" size="26" maxlength="100" /></li>
      <li><label class="field-label" for="Body">Message:</label><textarea id="Body" name="Body" cols="30" maxlength="4000" ></textarea></li>
    </ul>
    If you're responding to someone's post, tell them how you'd like to help,<br/>or how you'd like them to help you. <b>Don't</b> give out your address or contact details until you're ready to.
    <br/><br/>
    <input class="button" id="edit-message-submit" type="submit"  value="Send"/>
    &nbsp;&nbsp;
    <span id="edit-message-progress" class="hidden"><img src="css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
    <span class="message" id="edit-message-message"></span>
    <input type="text" id="ReceiverId" name="ReceiverId" class="invisible"/>
    <input type="hidden" id="edit-message-action" name="Action" value="create-message"/>
    <input type="hidden" id="Id" name="Id" value=""/>
  </fieldset>
</form>

<?php
}
?>
