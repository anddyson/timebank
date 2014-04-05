<?php
include_once("components_include.php");

$Action = $_POST["Action"];

$Db = MySQLConnection::GetInstance();

if ($Action == "edit-user")
{
  $Results = $Db->GetUsers($_POST["Id"]);
  $Usr = $Results[0];
  echo json_encode($Usr);
  die;
}
else if ($Action == "update-user")
{
  $Output = new DataActionResult();
  
  try
  {
    $Usr = new User();
    $Usr = Utils::ArrayToObject($_POST, $Usr);
    if ($_POST["IsApproved"]) $Usr->IsApproved = true;
    if ($_POST["IsActive"]) $Usr->IsActive = true;
    if ($Usr->Password == "") $Usr->Password = null; //otherwise it'll reset the password unintentionally
    $Output = $Db->UpdateUser($Usr);
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
else if ($Action == "add-user")
{
  $Output = new DataActionResult();
  
  try
  {
    $Usr = new User();
    $Usr = Utils::ArrayToObject($_POST, $Usr);
    $Db->AddUser($Usr);
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
else if ($Action == "delete-user")
{
  $Db->DeleteUser($_POST["Id"]);
  echo "true";
  die;
}
else if ($Action == "update-list")
{
  $UserList = $Db->GetUsers();
  echo json_encode($UserList);
  die;
}
else
{
  $UserList = $Db->GetUsers();
  include_once("admin.php");
?>

<script type="text/javascript">
$(function()
{
  //executed the first time the page is loaded
  drawUsersList(<?php echo json_encode($UserList); ?>);

  //go to add user form
  //$("#add-user").click(function() { showUsersEdit('add'); });

  //"Are you sure" type dialog box - initial setup.
    $("#dialog").dialog({ modal: true, title: "Confirm Delete", autoOpen: false });
  
  // validate user details
  $("#form-users-edit").validate({
        submitHandler: users_edit_form_submit
        ,errorClass: "message"
        ,rules:
        {
          Password: { minlength: 8 }
          ,Password2: { equalTo: "#Password" }
          ,FirstName: "required"
          ,LastName: "required"
          ,Email: "required email"
          ,Phone: "required"
          ,Address: "required"
          ,Postcode: "required"
          ,Terms: "required"
        }
        ,messages:
        {
          Password: { minlength: jQuery.format("Password must be at least {0} characters long") }
          ,Password2: "Passwords do not match"
          ,FirstName: "*"
          ,LastName: "*"
          ,Email: { required: "*", email: "Enter a valid email address" }
          ,Phone: { required: "*" }
          ,Address: "*"
          ,Postcode: "*"
          ,Terms: "*"
        }
  });
  
  //handler for submitting a user insert/edit
  function users_edit_form_submit(form)
  {
    $("#user-edit-progress").toggleClass("hidden");
    $("#user-edit-message").text('');
    var data = $(form).serialize();
    $.post("admin-users.php", data,
      function(result)
      {
        $("#user-edit-progress").toggleClass("hidden");
        result = $.parseJSON(result);
        if (result.success == true)
        {
          showUsersList();
        }
        else { $("#user-edit-message").text(result.message); }
      }
    );
  }

  //close the form without adding/updating anything
  $("#cancel-user-edit").click(function() { showUsersList(); });

  //go to edit user form
  $("#tbl-users-list").on("click", ".user-edit-link", function(event)
  {
    event.preventDefault();
    
    $.post("admin-users.php", { Action: "edit-user", Id: $(this).attr('userid') },
      function(result)
      {
        var usr = jQuery.parseJSON(result);
        showUsersEdit('edit', usr);
      }
    );
  });

  //delete a user
  $("#tbl-users-list").on("click", ".user-delete-link", function(event)
  {
    event.preventDefault();
    var userid = $(this).attr('userid');

    $("#dialog").dialog('option', 'buttons', {
      "OK" : function()
      {
        $.post("admin-users.php", { Action: "delete-user", Id: userid },
          function(result)
          { 
            $("#dialog").dialog("close");
            refreshUsersList();
          }
        );
      },
      "Cancel" : function() { $(this).dialog("close"); }
    });
        $("#dialog").dialog("open");    
  });
  
  //show the add/edit users form
  function showUsersEdit(status, usr)
  {
      $("#user-edit-message").text('');

    if (status == 'add')
    {
      $("#user-edit-action").val("add-user");
      $("#submit-user-edit").val("Add");
      $("#Id").val('');
      $("#Id-label").html('&nbsp;');
      $("#Username").val('');
    }
    else if (status == 'edit')
    {
      $("#user-edit-action").val("update-user");
      $("#submit-user-edit").val("Update");
      $("#Id").val(usr.Id);
      $("#Id-label").html(usr.Id);
      $("#Username").val(usr.Username);
      $("#Username-label").html(usr.Username);
      $("#FirstName").val(usr.FirstName);
      $("#LastName").val(usr.LastName);
      $("#Email").val(usr.Email);
      $("#Phone").val(usr.Phone);
      $("#Address").val(usr.Address);
      $("#Postcode").val(usr.Postcode);
      $("#IsApproved").prop('checked', (usr.IsApproved == 1 ? true : false));
      $("#IsActive").prop('checked', (usr.IsActive == 1 ? true : false));
      $("#LastLoginDateTime").val(usr.LastLoginDateTime);
      $("#LastLogin-label").html((!usr.LastLoginDateTime ? "Never" : usr.LastLoginDateTime));
    }

    $("#div-users-edit").removeClass("hidden");
    $("#div-users-list").addClass("hidden");
  }
  
  //show the list of users (and refresh)
  function showUsersList()
  {
    $("#div-users-edit").addClass("hidden");
    $("#div-users-list").removeClass("hidden");
    refreshUsersList();
  }

  //get fresh list of users from the server
  function refreshUsersList()
  {
    $("#tbl-users-list").find("tr:gt(0)").remove();
    $.post("admin-users.php", { Action: "update-list"},
      function(result)
      {
        drawUsersList(jQuery.parseJSON(result));
      }
    );
  }
  
  //render the data as rows of a HTML table with links etc
  function drawUsersList(users)
  {
    var html = "";
    for (var i = 0; i < users.length; i++)
    {
      html += "<tr><td>" + users[i].Username + "</td><td>" + users[i].FirstName + " " + users[i].LastName + "</td><td><a class='user-edit-link' userid='" + users[i].Id + "' href='#'>Edit</a></td><td><a class='user-delete-link' userid='" + users[i].Id + "' href='#'>Delete</a></td><td>" + (users[i].IsApproved == 1 ? "" : "Not yet approved") + "</td></tr>";
    }
    $("#tbl-users-list").find("tr").after(html);
  }
});
</script>

<div id="div-users-list">
  <h2>Manage Users</h2>
<!--  <button class="button" id="add-user" type="button">Add User >></button>
  <br/><br/>-->
  <form name="form-users-list" id="form-users-list">
    <table id="tbl-users-list" class="form-table">
    <tbody>
      <tr><th>Username</th><th>Full Name</th><th colspan="2">Action</th><th>Status</th></tr>
    </tbody>
    </table>
  </form>
</div>
<div id="div-users-edit" class="hidden">
  <form name="form-users-edit" id="form-users-edit">
    <fieldset class="fieldset-table-style">
      <legend>Edit User</legend>
      <ul>
        <li><label class="field-label" >Id:</label><span id="Id-label">&nbsp;</span></li>
        <li><label class="field-label" for="Username">Username:</label><span id="Username-label">&nbsp;</span></li>
        <li><label class="field-label" for="FirstName">First Name:</label><input type="text" size="26" id="FirstName" name="FirstName" maxlength="100" value=""/></li>
        <li><label class="field-label" for="LastName">Last Name:</label><input type="text" size="26" id="LastName" name="LastName" maxlength="100" value=""/></li>
        <li><label class="field-label" for="Email">Email:</label><input type="text" size="26" id="Email" name="Email" maxlength="100" value=""/></li>
        <li><label class="field-label" for="Phone">Phone:</label><input type="text" size="26" id="Phone" name="Phone" maxlength="100" value=""/></li>
        <li><label class="field-label" for="Address">Address:</label><textarea id="Address" name="Address" cols="30" maxlength="500" ></textarea></li>
        <li><label class="field-label" for="Postcode">Postcode:</label><input type="text" size="26" id="Postcode" name="Postcode" maxlength="10" value=""/></li>
        <li><label class="field-label" for="IsApproved">Approved?</label><input type="checkbox" id="IsApproved" name="IsApproved" /></li>
        <li><label class="field-label" for="IsApproved">Currently Active?</label><input type="checkbox" id="IsActive" name="IsActive" /></li>
        <li><label class="field-label" for="LastLogin-label">Last Logged In:</label><span id="LastLogin-label">&nbsp;</span></li>
        <li><label class="field-label" for="Password">Reset Password:</label><input type="password" size="26" id="Password" name="Password" maxlength="100" value=""/>(Leave blank to keep existing password)</li>
        <li><label class="field-label" for="Password2">Confirm Password:</label><input type="password" size="26" id="Password2" name="Password2" maxlength="100" value=""/></li>
      <ul>

      <input type="hidden" id="Id" name="Id" value=""/>
      <input type="hidden" id="Username" name="Username" value=""/>
      <input type="hidden" name="Action" id="user-edit-action" value="add-user"/>
      <input type="submit" class="button" id="submit-user-edit" value="Add" />
      &nbsp;&nbsp;
      <input type="button" class="button" id="cancel-user-edit" value="Cancel"/>
      &nbsp;&nbsp;
      <span id="user-edit-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
      <span class="message" id="user-edit-message"></span>
    </fieldset>
  </form>
</div>
<div id="dialog">Are you sure you want to delete this User?</div>
<?php
  include_once("admin-footer.php");
}
?>
