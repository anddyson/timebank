<?php
include_once("components_include.php");

$Action = $_POST["Action"];
$UserId = $_SESSION["UserId"];

$db = MySQLConnection::GetInstance();

if ($Action == "refresh-posts")
{
  $Posts = $db->GetPosts(null, $UserId);
  echo json_encode($Posts);
  die;
}
else if ($Action == "refresh-notifications")
{
  $Notifications = $db->GetTransactionNotifications($UserId);
  echo json_encode($Notifications);
  die;
}
else if ($Action == "refresh-messages")
{
  $NewMessages = $db->GetMessages(null, null, $UserId, false);
  $OldMessages = $db->GetMessages(null, null, $UserId, true);
  $SentMessages = $db->GetMessages(null, $UserId, null, null);
  $Messages = array($NewMessages, $OldMessages, $SentMessages);
  echo json_encode($Messages);
  die;
}
else
{
  $Posts = $db->GetPosts(null, $UserId);
  $NewMessages = $db->GetMessages(null, null, $UserId, false);
  $OldMessages = $db->GetMessages(null, null, $UserId, true);
  $SentMessages = $db->GetMessages(null, $UserId, null, null);
  $Notifications = $db->GetTransactionNotifications($UserId);
  include_once("header.php");
?>

<script type="text/javascript">
$(function()
{
  //run at startup to process existing data from the server
  render_post_list(<?php echo json_encode($Posts); ?>);
  render_notification_list(<?php echo json_encode($Notifications); ?>);
  var newmessages = <?php echo json_encode($NewMessages); ?>;
  var oldmessages = <?php echo json_encode($OldMessages); ?>;
  var sentmessages = <?php echo json_encode($SentMessages); ?>;
  render_message_lists(newmessages, oldmessages, sentmessages);
  
  /************/
  //set up event handlers
  
  //create a new post
  $("#create-post").click(function(event)
  {
    show_popup("#edit-post-popup");
    set_edit_post_mode("add");
  });

  //create a new message
  $("#create-message").click(function(event)
  {
    show_popup("#message-popup");
    set_edit_message_mode("add");
  });
  
  //create a new transaction
  $("#create-transaction").click(function(event)
  {
    show_popup("#new-transaction-popup");
  });  
  
  //view previous transactions
  $("#view-transactions").click(function(event)
  {
    refresh_transactions();
    show_popup("#transactions-popup");
  });
  
  //edit a post
  $("#posts").on("click", ".post-edit-link", function(event)
  {
    event.preventDefault();
    show_popup("#edit-post-popup");
    set_edit_post_mode("edit", $(this).attr('postid'));
  });

  //"Are you sure" type dialog box - initial setup.
  $("#dialog").dialog({ modal: true, title: "Confirm Delete", autoOpen: false });
  $("#transaction-dialog").dialog({ modal: true, title: "Confirm Exchange", autoOpen: false });
    
  //delete a post
  $("#posts").on("click", ".post-delete-link", function(event)
  {
    event.preventDefault();
    var postid = $(this).attr('postid');
    $("#dialog").dialog('option', 'buttons', {
      "OK" : function()
      {
        $.post("post.php", { Action: "delete-post", PstId: postid },
          function(result)
          { 
            $("#dialog").dialog("close");
            refresh_posts();
          }
        );
      },
      "Cancel" : function() { $(this).dialog("close"); }
    });
    $("#dialog").dialog("open");    
  });

  //view a message
  $("#messages").on("click", ".message-view-link", function(event)
  {
    event.preventDefault();
    show_popup("#message-popup");
    set_edit_message_mode("read", $(this).attr('msgid'));
  });
  
  //view a sent message
  $("#sent-messages").on("click", ".message-view-link", function(event)
  {
    event.preventDefault();
    show_popup("#message-popup");
    set_edit_message_mode("sent", $(this).attr('msgid'));
  });

    $("#notifications").on("click", ".confirm-transaction-link", function(event)
    {
      event.preventDefault();
      var trnid = $(this).attr('trnid');
      $("#transaction-dialog").dialog('option', 'buttons', {
      "OK" : function()
      {
        $.post("transaction.php", { Action: "confirm-transaction", TrnId: trnid },
          function(result)
          {
            result = $.parseJSON(result);
            if (result.success == true)
            {
              $("#transaction-dialog").dialog("close");
              refresh_notifications();
              refresh_stats();
            }
            else { alert(result.message); }
          }
        );
      },
      "Cancel" : function() { $(this).dialog("close"); }
      });
      $("#transaction-dialog").dialog("open");    
    });

  $("#messages-button").click(
    function(event)
    {
      $("#messages").removeClass("hidden");
      $("#sent-messages").addClass("hidden");
      $("#messages-button").attr("disabled", "disabled");
      $("#sent-messages-button").removeAttr("disabled");
    }
  );

  $("#sent-messages-button").click(
    function(event)
    {
      $("#messages").addClass("hidden");
      $("#sent-messages").removeClass("hidden");
      $("#sent-messages-button").attr("disabled", "disabled");
      $("#messages-button").removeAttr("disabled");
    }
  );
  
  //refresh the display when a message has been sent
    $(document).on("messagesent", function(e, message)
    {
        hide_popup("#message-popup");
        refresh_messages();
        refresh_stats();
    });

    $(document).on("messageread", function(e, message)
    {
      refresh_messages();
      refresh_stats();
    });

    $(document).on("messagedeleted", function(e, message)
    {
      hide_popup("#message-popup");
      refresh_messages();
      refresh_stats();
    });

    $(document).on("messageunread", function(e, message)
    {
      refresh_messages();
      refresh_stats();
    });

    //refresh the display when a post has been created/updated
    $(document).on("postsaved", function(e, message)
    {
        hide_popup("#edit-post-popup");
        refresh_stats();
        refresh_posts();
    });

    //refresh the display when a transaction has been submitted
    $(document).on("transactionsaved", function(e, message)
    {
        hide_popup("#new-transaction-popup");
        refresh_stats();
    });

});

  function render_post(post)
  {
    var html = '<div class="post"><h4>' + (post.Type == 1 ? 'Wanted' : 'Offered') + ': ' + post.Heading + '</h4><a href="#" postid="' + post.Id + '" class="button post-edit-link">Edit</a>&nbsp;&nbsp;<a href="#" postid="' + post.Id + '" class="button post-delete-link">Remove</a>&nbsp;&nbsp;Added: ' + moment(post.CreatedDate).format('Do MMM YYYY') + '. Expires: ' + moment(post.ExpiryDate).format('Do MMM YYYY') + '</footer></div>';
    return html;
  }
  
  function refresh_posts()
  {
    $("#posts").fadeToggle();
    $("#posts-update-progress").toggleClass("hidden");
    $.post("mytimebank.php", { Action: "refresh-posts"},
      function(result)
      {
        var posts = jQuery.parseJSON(result);
        $("#posts-update-progress").toggleClass("hidden");
        render_post_list(posts);
        $("#posts").fadeToggle();
      }
    );
  }
  
  function render_post_list(posts)
  {
    var html = '';
    for (var i = 0; i < posts.length; i++)
    {
      html += render_post(posts[i]);
    }
    $("#posts").html(html);      
  }
  
  function refresh_notifications()
  {
    $("#notifications").fadeToggle();
    $("#notifications-update-progress").toggleClass("hidden");
    $.post("mytimebank.php", { Action: "refresh-notifications"},
      function(result)
      {
        var nots = jQuery.parseJSON(result);
        $("#notifications-update-progress").toggleClass("hidden");
        render_notification_list(nots);
        $("#notifications").fadeToggle();
      }
    );
  }
  
  function render_notification_list(notifications)
  {
    var html = '';
    for (var i = 0; i < notifications.length; i++)
    {
      html += render_notification(notifications[i]);
    }
    if (html != '') { $("#notifications").html(html); }
    else { $("#notifications").html('No new exchanges to confirm'); }
  }

  function refresh_messages()
  {
    $("#messages-wrapper").fadeToggle();
    $("#messages-update-progress").toggleClass("hidden");
    $.post("mytimebank.php", { Action: "refresh-messages"},
      function(result)
      {
        var messages = jQuery.parseJSON(result);
        $("#messages-update-progress").toggleClass("hidden");
        render_message_lists(messages[0], messages[1], messages[2]);
        $("#messages-wrapper").fadeToggle();
      }
    );
  }
  
  function render_message_lists(newmessages, oldmessages, sentmessages)
  {
    var html = '';
    for (var i = 0; i < newmessages.length; i++) { html += render_message(newmessages[i]); }
    for (var i = 0; i < oldmessages.length; i++) { html += render_message(oldmessages[i]); }
    if (html != '') { $("#messages").html(html); }
    else { $("#messages").html("You haven't got any messages"); }

    html = '';
    for (var i = 0; i < sentmessages.length; i++) { html += render_sent_message(sentmessages[i]); }
    if (html != '') { $("#sent-messages").html(html); }
    else { $("#sent-messages").html("You haven't sent any messages"); }
  }

  function render_notification(notification)
  {
    var html = '<li><a href="#" class="confirm-transaction-link" trnid="' + notification.TransactionId + '" >Please confirm that you' + (notification.Type == 1 ? ' gave ' : ' received ') + notification.Hours + ' hour' + (notification.Hours > 1 ? 's' : '') + (notification.Type == 1 ? ' to ' : ' from ') + notification.OtherUser.FirstName + ' ' + notification.OtherUser.LastName + ' (' + notification.OtherUser.Username + ') for ' + notification.Description + ' on ' + moment(notification.TransactionDateTime).format("Do MMM YYYY") + '</a></li>';
    return html;
  }
  
  function render_message(msg)
  {
    var html = '<li>' + (msg.ReadFlag == false ? '<b>' : '') + '<a href="#" class="message-view-link" msgid="' + msg.Id + '">From ' + msg.Sender.FirstName + ' ' + msg.Sender.LastName + ' (' + msg.Sender.Username + ') on ' + moment(msg.SentDateTime).format("Do MMM YYYY") + ': ' + msg.Subject + '</a>' + (msg.ReadFlag == false ? '</b>' : '') + '</li>';
    return html;
  }
  
  function render_sent_message(msg)
  {
    var html = '<li><a href="#" class="message-view-link" msgid="' + msg.Id + '">To ' + msg.Receiver.FirstName + ' ' + msg.Receiver.LastName + ' (' + msg.Receiver.Username + ') on ' + moment(msg.SentDateTime).format("Do MMM YYYY") + ': ' + msg.Subject + '</a></li>';
    return html;
  }
  
  function refresh_transactions()
  {
    $("#transactions-update-progress, #tbl-previous-transactions").toggleClass("hidden");
    $.post("transaction.php", { Action: "get-user-transactions"},
      function(result)
      {
        var result = $.parseJSON(result);
        $("#transactions-update-progress, #tbl-previous-transactions").toggleClass("hidden");

        if (result.success === true)
        {
          var transactions = result.data;
          $("#tbl-previous-transactions").find("tr:gt(0)").remove();
          var html = '';
          for (var i = 0; i < transactions.length; i++)
          {
            html += render_transaction(transactions[i]);
          }
          $("#tbl-previous-transactions").find("tr").after(html);
        }
        else { alert(result.message); }
      }
    );
  }
  
  function render_transaction(trn)
  {
    var html = '<tr><td>' + trn.Id + '</td><td>' + moment(trn.TransactionDateTime).format('DD/MM/YYYY') + '</td><td>' + trn.Hours + '</td><td>' + trn.Giver.Username + '</td><td>' + trn.Receiver.Username + '</td><td>' + trn.Description + '</td><td>' + (trn.GiverApproved == true && trn.ReceiverApproved == true ? "Yes" : "Pending") + '</td></tr>';
    return html;
  }

</script>

<div id="modalpopupbackground" class="hidden"></div>
<div id="edit-post-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="edit-post"><input type="button" class="button" value='X' /></div>
    <?php include_once("post.php"); ?>
  </section>
</div>
<div id="message-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="message"><input type="button" class="button" value='X' /></div>
    <?php include_once("message.php"); ?>
  </section>
</div>
<div id="transactions-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="transactions"><input type="button" class="button" value='X' /></div>
     <h2><img class="middle" src="images/32/exchange.png">&nbsp;My Previous Exchanges</h2>
     <div id="transactions-update-progress" class="hidden"><img src="css/images/progress.gif" height="15" />&nbsp;Updating...<br/><br/></div>
    <table id="tbl-previous-transactions" class="form-table">
      <tr><th>Id</th><th>Date</th><th>Hours</th><th>From</th><th>To</th><th>For</th><th>Approved?</th></tr>
    </table>
  </section>
</div>
<div id="new-transaction-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="new-transaction"><input type="button" class="button" value='X' /></div>
    <?php include_once("transaction.php"); ?>
  </section>
</div>

<div id="dialog">Are you sure you want to delete?</div>
<div id="transaction-dialog">Confirm this exchange?</div>

<div class="row">
  <div class="7u">
    <div class="5grid">
        <section>
          <b>I want to...</b>&nbsp;&nbsp;
          <button class="button" id="create-transaction">Record an exchange</button>
          &nbsp;&nbsp;&nbsp;
          <button class="button" id="create-post">Create a Post</button>
          &nbsp;&nbsp;&nbsp;
          <button class="button" id="create-message">Write a Message</button>
          &nbsp;&nbsp;&nbsp;
          <button class="button" id="view-transactions">View My Exchanges</button>
        </section>
      <div class="row">
        <div class="12u">
          <section>
            <div class="left sectionheading"><img class="middle" src="images/32/exchange.png">&nbsp;Confirm Exchanges</div>
            <div id="notifications-update-progress" class="right hidden"><img src="css/images/progress.gif" height="15" />&nbsp;Updating...<br/><br/></div>
            <div class="clear"></div>
            <br/><br/><br/>
            <ul class="link-list" id="notifications">
            </ul>
          </section>
        </div>
      </div>
      <div class="row">
        <div class="12u">
          <section>
            <div class="left sectionheading"><img class="middle" src="images/32/message.png">&nbsp;Messages</div>
            <div id="messages-update-progress" class="right hidden"><img src="css/images/progress.gif" height="15" />&nbsp;Updating...<br/><br/></div>
            <div class="clear"></div>
            <br/><br/><br/>
            <button id="messages-button" class="button" disabled="disabled">Inbox</button>
            &nbsp;&nbsp;
            <button id="sent-messages-button" class="button">Sent Messages</button>
            <br/><br/>
            <div id="messages-wrapper">
            <ul class="link-list" id="messages">
            </ul>
            <ul class="link-list hidden" id="sent-messages">
            </ul>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
  <div class="5u">
    <div class="5grid">
      <div class="row">
        <div class="12u">
          <section>
            <?php include_once("stats.php"); ?>
          </section>
        </div>
      </div>
      <div class="row">
        <div class="12u">
          <section>
            <div class="left sectionheading"><img class="middle" src="images/32/post.png">&nbsp;My Posts</div>
            <div id="posts-update-progress" class="right hidden"><img src="css/images/progress.gif" height="15" />&nbsp;Updating...<br/><br/></div>
            <div class="clear"></div>
            <br/><br/><br/>
            <div id="posts">
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
}
include_once("footer.php");
?>
