<?php include_once("components_include.php");

$Action = $_REQUEST["Action"];
$message = "";

if ($Action == "submit-reminder")
{
  $Output = new DataActionResult();
  try
  {
    $db = MySQLConnection::GetInstance();
    $Result = $db->GetUserPassword($_POST["Identifier"]);
    $Usr = $Result[0];
    if (!is_null($Usr))
    {
      $Email = new Email();
      $Email->from = "help@levytimebank.org.uk";
      $Email->to = $Usr->Email;
      $Email->subject = "Levy Timebank Password Reminder";
      $Email->body = "Your password is ".$Usr->Password;
      $Email->Send();
    }
	else
	{
		$Output->success = false;
		$Output->message = "You did not enter a recognised username or email address";
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
elseif ($Action == "submit-login")
{
  $Usr = new User();
  $Usr->Password = $_POST["Password"];
  //user can login with either username or password, so supply both
  $Usr->Email = $_POST["Identifier"];
  $Usr->Username = $_POST["Identifier"];
  
  $db = MySQLConnection::GetInstance();
  $Result = $db->VerifyUserLogin($Usr);
  $Usr = $Result[0];
  if (!is_null($Usr))
  {
    $_SESSION["UserId"] = $Usr->Id;
    
    //TEMPORARY HACK
    if ($Usr->Username == "sysadmin")
    {
      $_SESSION["Role"] = "Admin";
      header('Location:admin.php');
    }
    else
    {
      $_SESSION["Role"] = "User";
      header('Location:mytimebank.php');
    }
  }
  else
  {
    $message = "Invalid details - please try again";
  }
}
elseif ($Action == "logout")
{
  unset($_SESSION["UserId"]);
  unset($_SESSION["Role"]);
  session_destroy();
  header('Location:index.php');
}
?>

<script type="text/javascript">
$(function()
{
  $("#reminder-link").click(function(event)
  {
    event.preventDefault();
    show_popup("#reminder-popup");
  });
  
  $("#form-reminder").submit(function(event)
  {
    event.preventDefault();
    
    $("#reminder-progress").toggleClass("hidden");
    $("#reminder-message").text('');
    var data = $(this).serialize();
    $.post("login.php", data,
      function(result)
      {
        $("#reminder-progress").toggleClass("hidden");
        result = $.parseJSON(result);
        if (result.success === true)
        {
          show_notification("Password reminder sent", "Please check your emails");
          hide_popup("#reminder-popup");
        }
        else { $("#reminder-message").text(result.message); }
      }
    );
});
});
</script>

<div id="reminder-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="reminder"><input type="button" class="button" value='X' /></div>
    <form id="form-reminder" name="form-reminder" method="POST">
      <fieldset class="fieldset-table-style-large">
        <legend>Password Reminder</legend>
        <p>Please enter your username, or the email address which is registered to your Timebank account.</p>
        <ul>
          <li><label class="field-label" for="Identifier">Username / Email:</label><input type="text" id="Identifier" size="40" name="Identifier" maxlength="100"/></li>
        </ul>
		We will send you an email with a password reminder in it
		<br/><br/>
        <input type="submit" class="button" id="submit-login" value="Get Password Reminder" />
        <input type="hidden" id="Action" name="Action" value="submit-reminder"/>
        &nbsp;&nbsp;
        <span id="reminder-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Requesting, please wait...</span>
        <span class="message" id="reminder-message"></span>
      </fieldset>
    </form>
  </section>
</div>

<form id="form-login" name="form-login" method="POST">
  <fieldset class="fieldset-table-style-large">
    <legend><img class="middle" src="themes/<?php echo $THEME; ?>/images/32/lock.png">&nbsp;Login</legend>
    <ul>
      <li><label class="field-label" for="Identifier">Username / Email:</label><input type="text" id="Identifier" name="Identifier" maxlength="100"/></li>
      <li><label class="field-label" for="Password">Password:</label><input type="Password" id="Password" name="Password" maxlength="50"/></li>
    </ul>
    <input type="submit" class="button" id="submit-login" value="Login" />
    <input type="hidden" id="Action" name="Action" value="submit-login"/>
    &nbsp;&nbsp;
    <span id="login-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Verifying your details, please wait...</span>
    <span class="message" id="login-message"><?php echo $message; ?></span>
    <br/><br/>
    Not a member?&nbsp;&nbsp;<a href="register.php">Sign up with us and start swapping your time!</a>
    <br/><br/>
    Forgotten your password? <a href="#" id="reminder-link">Click here to get a reminder</a>
  </fieldset>
</form>
