<?php
include_once("components_include.php");

$Action = $_POST["Action"];

$mysql = MySQLConnection::GetInstance();

if ($Action == "register")
{
  $Output = new DataActionResult();

  try
  {
    $recaptcha_resp = recaptcha_test();

    if ($recaptcha_resp->is_valid)
    {
      $Usr = new User();
      $Usr = Utils::ArrayToObject($_POST, $Usr);
      $Output = $mysql->AddUser($Usr);
      $Usr = $Output->data[0];
      
      $Email = new Email();
      $Email->from = "help@levytimebank.org.uk";
      $Email->to = $Usr->Email;
      $Email->subject = "[Levy Timebank] Account Activation";
      $ConfirmUrl = "http://".Utils::GetCurrentDomain()."/confirm.php?u=".$Usr->UUid;
      $Email->body = "Hi ".$Usr->FirstName."<br><br>Thanks for registering with Levy Timebank. Your username is: ".$Usr->Username.".<br><br>To complete your activation, please click on the following link:<br><br><a href='".$ConfirmUrl."'>".$ConfirmUrl."</a><br><br>(If clicking on the link does not work, please copy and paste the link into your web browser.)";
      $Email->Send();
    }
    else
    {
      $Output->success = false;
      $Output->message = "ERROR: Please enter the two security words correctly and try again";
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
else
{
  include_once("header.php");
  echo recaptcha_initialise();
?>
<script type="text/javascript">
$(function()
{
  $("#register-form").validate({
        submitHandler: register_form_submit
        ,errorClass: "message"
        ,rules:
        {
          Username: { required: true, minlength: 5, remote: { url: "searchusers.php", data: { action: "SearchUserName" } } }
          ,Password: { required: true, minlength: 8 }
          ,Password2: { equalTo: "#Password" }
          ,FirstName: "required"
          ,LastName: "required"
          ,Email: { required: true, email: true, remote: { url: "searchusers.php", data: { action: "SearchEmail" } } }
          ,Phone: "required"
          ,Address: "required"
          ,Postcode: "required"
          ,Terms: "required"
        }
        ,messages:
        {
            Username: { required: "*", minlength: jQuery.format("Username must be at least {0} characters long"), remote: "This username has already been taken - please try another" }
            ,Password: { required: "*", minlength: jQuery.format("Password must be at least {0} characters long") }
            ,Password2: "Passwords do not match"
            ,FirstName: "*"
            ,LastName: "*"
            ,Email: { required: "*", email: "Enter a valid email address", remote: "This email address has already been registered by another user" }
            ,Phone: { required: "*"}
            ,Address: "*"
            ,Postcode: "*"
            ,Terms: "*"
        }
  });
   
  $("#terms-link").click(function(event)
  {
    event.preventDefault();
    show_popup("#terms-popup");
  });
  
    function register_form_submit(form)
    {
        $("#register-progress").toggleClass("hidden");
         var data = $(form).serialize();
         $.post("register.php", data,
             function(result)
             {
               $("#register-progress").toggleClass("hidden");
               result = $.parseJSON(result);

               if (result.success == true)
               {
                   $("#register-form").addClass("hidden");
                   $("#register-thanks").removeClass("hidden");
               }
               else { $("#register-message").text(result.message); }
             }
         );
    }
});
</script>

<div class="row main-row">
  <div class="12u">
    <section>
    <form id="register-form" method="get" action="">
    <fieldset class="fieldset-table-style">
      <legend><img class="middle" src="themes/<?php echo $THEME; ?>/images/32/clock.png">&nbsp;Join Levy Timebank</legend>
      <br/>We just need a few details from you so that you can make the best use of what the timebank has to offer.<br/><br/>
      Data Protection: Levy Timebank will only keep and use your personal information as necessary for the efficient administration of the Timebank and will never pass your details to any third party without your consent. All information is stored securely on our servers (located in the UK) and is not accessible to others.
      <br/><br/>
      <ul>
      <li>
        <label class="field-label" for="FirstName">First Name:</label>
        <input id="FirstName" name="FirstName" size="26" maxlength="100" />
      </li>
      <li>
        <label class="field-label" for="LastName">Last Name:</label>
        <input id="LastName" name="LastName" size="26" maxlength="100" />
      </li>
      <li>
        <label class="field-label" for="Email">E-Mail Address:</label>
        <input id="Email" name="Email" size="26" title="You'll need a valid email address in order to complete your registration and to receive notifications when people respond to you on the timebank" />
      </li>
      <li>
        <label class="field-label" for="Phone">Phone:</label>
        <input id="Phone" name="Phone" size="26"  value="" />
      </li>
      <li>
        <label class="field-label" for="Address">Address:</label>
        <textarea id="Address" name="Address" cols="30" maxlength="500" ></textarea>
      </li>
      <li>
        <label class="field-label" for="Postcode">Postcode:</label>
        <input id="Postcode" name="Postcode" size="26" maxlength="10" />
      </li>
      <li>
        <label class="field-label" for="Username">Choose a Username:</label>
        <input id="Username" name="Username" size="26" maxlength="100" title="Choose a username that's meaningful and clear if you can - you can use it log in, and other timebank participants can use it to help identify you" />
      </li>
      <li>
        <label class="field-label" for="Username">Choose a Password:</label>
        <input type="password" id="Password" name="Password" size="26" maxlength="100" title="Try to choose a password that you'll remember but isn't easy for others to guess" />
      </li>
      <li>
        <label class="field-label" for="Username">Confirm your Password:</label>
        <input type="password" id="Password2" name="Password2" size="26" maxlength="100" />
      </li>
      <li>
        <label class="field-label">Type the two words:</label>
        <?php echo recaptcha_display(); ?>
      </li>
      </ul>
         <input type="checkbox" id="Terms" name="Terms" />&nbsp;<label for="Terms">I confirm that I have read and agree to the <a href="#" id="terms-link">Terms and Conditions</a> for using the Timebank</label>
      <br/><br/>
      <input class="submit button" type="submit"  value="Submit Application"/>
      &nbsp;&nbsp;
      <span id="register-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
      <span class="message" id="register-message"></span>
      <input type="hidden" id="Action" name="Action" value="register"/>
    </fieldset>
    </form>
    <div id="register-thanks" class="hidden">
      <b>Thanks for registering for the Timebank. Shortly you'll receive an email asking you to confirm your registration. Please click on the link in the email to activate your account. Happy Timebanking!</b>
      <br/><br/><p>P.S. If you don't get a confirmation email from us fairly soon, please check the Spam / Junk folder of your email account, in case our message has been filtered by your email system. This sometimes happens and is beyond our control. If you still do not receive an email, please email us at <a href="mailto:help@levytimebank.org.uk">help@levytimebank.org.uk</a> and we will activate your account for you.</p>
      <br/><br/>
      <a href="index.php">Click here to return to the home page</a>
    </div>
    </section>
  </div>

  <div id="modalpopupbackground" class="hidden"></div>
  <div id="terms-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="terms"><input type="button" class="button" value='X' /></div>
    <?php include_once("termsandconditions.php"); ?>
    </section>
  </div>
</div>
<?php
include_once("footer.php");
}
?>
