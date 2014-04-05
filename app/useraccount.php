<?php
include_once("components_include.php");

$Action = $_POST["Action"];
$mysql = MySQLConnection::GetInstance();
$UserId = $_SESSION["UserId"];

if ($Action == "update-account-details")
{
  $Output = new DataActionResult();
  
  try
  {
    $Usr = new User();
    $Usr = Utils::ArrayToObject($_POST, $Usr);
    $Usr->Id = $UserId;
    $Output = $mysql->UpdateUser($Usr);
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
elseif ($Action == "update-password")
{
  $Usr = new User();
  $Usr = Utils::ArrayToObject($_POST, $Usr);
  $Usr->Id = $UserId;
  $mysql->UpdateUserPassword($Usr);
  echo "Password Changed.";
  die;
}
else
{
  $Results = $mysql->GetUsers($UserId);
  $Usr = $Results[0];
}
include_once("header.php");
?>


<script type="text/javascript">
$(function()
{
  $("#select-account-details").click(
    function(event)
    {
      $("#account-details,#account-update-password").toggleClass("hidden");
      $("#select-account-details").attr("disabled", "disabled");
      $("#select-change-password").removeAttr("disabled");
    }
  );

  $("#select-change-password").click(
    function(event)
    {
      $("#account-details,#account-update-password").toggleClass("hidden");
      $("#select-change-password").attr("disabled", "disabled");
      $("#select-account-details").removeAttr("disabled");
    }
  );
  
  $('#form-account-details').submit(function(event) {
    event.preventDefault();
    
    //validate
    $("#account-details-message").text('');
    var valid = true;

    var firstname = $("#FirstName").val();
    if (firstname == '')
    {
      $("#account-details-message").text('Please enter your first name.');
      valid = false;
        }
    var lastname = $("#LastName").val();
    if (lastname == '')
    {
      $("#account-details-message").text('Please enter your last name.');
      valid = false;
        }
    var email = $("#Email").val();
    if (email == '' || !ValidEmail(email))
    {
      $("#account-details-message").text('Please enter a valid email address.');
      valid = false;
        }
    var address = $("#Address").val();
    var maxlength = $("#Address").attr("maxlength"); //for browsers which don't natively support maxlength on textarea
    if (address.length > maxlength)
    {
      $("#account-details-message").text('Address is too long - please shorten it to ' + maxlength + ' characters or less.');
      valid = false;
    }
    if(valid == false) {return false;}

    //submit
    $("#account-details-progress").toggleClass("hidden");
    var data = $(this).serialize();
    $.post("useraccount.php", data,
      function(result)
      {
        $("#account-details-progress").toggleClass("hidden");
        result = $.parseJSON(result);
        if (result.success == true) { result.message = 'Details updated'; }
        $("#account-details-message").text(result.message);
      }
    );
  });

  $('#form-update-password').submit(function(event) {
    event.preventDefault();
    
    //validate
    $("#update-password-message").text('');

    var passwordVal = $("#Password").val();
    var checkVal = $("#Password2").val();
    var valid = true;
    
    if (passwordVal == '')
    {
      $("#update-password-message").text('Please enter a password.');
      valid = false;
        }
        else if (checkVal == '')
        {
      $("#update-password-message").text('Please re-enter your password.');
            valid = false;
        }
        else if (passwordVal != checkVal ) {
      $("#update-password-message").text('Passwords do not match. Please try again.');
            valid = false;
        }
        if(valid == false) {return false;}
        
    //submit
    $("#update-password-progress").toggleClass("hidden");
    var data = $(this).serialize();
    $.post("useraccount.php", data,
      function(result)
      {
            $("#update-password-progress").toggleClass("hidden");
        $("#update-password-message").text(result);
      }
    );
  });
});
</script>
        <div class="row main-row">
          <div class="3u" id="account-sidebar">
            <section>
              <ul class="link-list">
                <li><button class="button" id="select-account-details" disabled="disabled" style="width:100%" type="button">Account Details</button></li>
                <li><button class="button" id="select-change-password" style="width:100%" type="button">Change Password</button></li>
              </ul>
            </section>
          </div>
          <div class="9u" id="account-details">
            <section>
              <form name="form-account-details" id="form-account-details">
                <fieldset class="fieldset-table-style">
                  <legend><img class="middle" src="images/32/user.png">&nbsp;My Account Details</legend>
                  <ul>
                    <li><label class="field-label" for="FirstName">First Name:</label><input type="text" size="26" id="FirstName" name="FirstName" maxlength="100" value="<?php echo htmlspecialchars($Usr->FirstName); ?>"/></li>
                    <li><label class="field-label" for="LastName">Last Name:</label><input type="text" size="26" id="LastName" name="LastName" maxlength="100" value="<?php echo htmlspecialchars($Usr->LastName); ?>"/></li>
                    <li><label class="field-label" for="Email">Email Address:</label><input type="text" size="26" id="Email" name="Email" maxlength="100" value="<?php echo htmlspecialchars($Usr->Email); ?>"/></li>
                    <li><label class="field-label" for="Phone">Phone:</label><input type="text" size="26" id="Phone" name="Phone" maxlength="100" value="<?php echo htmlspecialchars($Usr->Phone); ?>"/></li>
                    <li><label class="field-label" for="Address">Address:</label><textarea id="Address" name="Address" maxlength="500" rows="4" cols="30"><?php echo htmlspecialchars($Usr->Address); ?></textarea></li>
                    <li><label class="field-label" for="Postcode">Postcode:</label><input type="text" size="26" id="Postcode" maxlength="50" name="Postcode" value="<?php echo htmlspecialchars($Usr->Postcode); ?>"/></li>
                  </ul>

                  <input type="hidden" name="IsApproved" value="<?php echo htmlspecialchars($Usr->IsApproved);?>"/>
                  <input type="hidden" name="LastLoginDateTime" value="<?php echo htmlspecialchars($Usr->LastLoginDateTime);?>"/>
                  <input type="hidden" name="ReminderCount" value="<?php echo htmlspecialchars($Usr->ReminderCount);?>"/>
                  <input type="hidden" name="IsActive" value="<?php echo htmlspecialchars($Usr->IsActive);?>"/>
                  <input type="hidden" name="Username" value="<?php echo htmlspecialchars($Usr->Username);?>"/>
                  <input type="hidden" name="Action" value="update-account-details"/>

                  <input type="submit" class="button" id="submit-account-details" value="Update" />
                  &nbsp;&nbsp;
                  <span id="account-details-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
                  <span class="message" id="account-details-message"></span>
                </fieldset>
              </form>
            </section>
          </div>
          <div class="hidden 9u" id="account-update-password">
            <section>
              <form name="form-update-password" id="form-update-password">
                <fieldset class="fieldset-table-style">
                  <legend><img class="middle" src="images/32/lock.png">&nbsp;Change Password</legend>
                  <ul>
                  <li><label class="field-label" for="Password">New Password:</label><input type="password" name="Password" id="Password" value=""/></li>
                  <li><label class="field-label" for="Password2">Confirm New Password:</label><input type="password" name="Password2" id="Password2" value=""/></li>
                  </ul>
                  <input type="submit" class="button" id="submit-update-password" value="Submit" />

                  <input type="hidden" name="Action" value="update-password"/>
                  &nbsp;&nbsp;
                  <span id="update-password-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
                  <span class="message" id="update-password-message"></span>
                </fieldset>
              </form>
            </section>
          </div>
        </div>

<?php include_once("footer.php"); ?>
