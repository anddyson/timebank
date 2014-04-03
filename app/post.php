<?php
include_once("components_include.php");

function getCategoriesFromPost()
{
  $cats = array();
  //work out which categories have been selected
  foreach (array_keys($_POST) as $key)
  {
    if (preg_match("/cat_(\d+)/", $key, $matches))
    {
      $cat = new Category();
      $cat->Id = $matches[1];
      $cats[] = $cat;
    }
  }
  
  return $cats;
}

$Action = $_POST["Action"];
$UserId = $_SESSION["UserId"];

$db = MySQLConnection::GetInstance();

if ($Action == "create-post")
{
  $Pst = new Post();
  $Pst = Utils::ArrayToObject($_POST, $Pst);
  $Pst->UserId = $UserId;
  $Pst->Categories = getCategoriesFromPost();
  $Result = $db->AddPost($Pst);
  echo json_encode($Result);
  die;
}
else if ($Action == "update-post")
{
  $Pst = new Post();
  $Pst = Utils::ArrayToObject($_POST, $Pst);
  $Pst->UserId = $UserId;
  $Pst->Categories = getCategoriesFromPost();
  $Result = $db->UpdatePost($Pst);
  echo json_encode($Result);
  die;
}
else if ($Action == "delete-post")
{
  $db->DeletePost($_POST["PstId"]);
  echo "true";
  die;
}
else if ($Action == "get-post")
{
  $Results = $db->GetPosts($_POST["Id"]);
  echo json_encode($Results[0]);
  die;
}
else
{
  $Categories = $db->GetCategories();
?>
<script type="text/javascript">
$(function()
{
  //validate a post
  $("#form-edit-post").validate(
  {
        submitHandler: post_form_submit
        ,errorClass: "message"
        ,rules:
        {
          Heading: { required: true }
          ,Description: { required: true }
          ,Type: { required: true }
          ,ExpiryDate: { required: true }
          ,Terms: { required: true }
        }
        ,messages:
        {
          Heading: { required: "*" }
          ,Description: { required: "*" }
          ,Type: { required: "*" }
          ,ExpiryDate: { required: "*" }
          ,Terms: { required: "*" }
        }
  });
  
  //submit a post
  function post_form_submit(form)
  {
    $("#edit-post-message").text('');
    $("#edit-post-progress").toggleClass("hidden");
    var data = $(form).serialize();
    $.post("post.php", data,
      function(result)
      {
        $("#edit-post-progress").toggleClass("hidden");
        result = $.parseJSON(result);
        $("#edit-post-message").text(result);
        if (result === true)
        {
          $("#form-edit-post")[0].reset();
          show_notification("Post Saved", "Your post was saved successfully");
          $(document).trigger("postsaved", "Post saved");
        }
        else
        {
          var msg = '';
          for (val in result) { msg += result[val] + '; '; }
          $("#edit-post-message").text(msg);
        }
      }
    );    
  }

  $("#ExpiryDate").datepicker({ dateFormat: "dd-mm-yy", showButtonPanel: false, closeText: "Cancel" });
  
  $("#ExpiryDateSelect").change(function()
  {
    $("#ExpiryDate").val(moment(getSelectedDate($("#ExpiryDateSelect").val(), 'future')).format("DD-MM-YYYY"));
  });
});

  function set_edit_post_mode(mode, id)
  {
  $("#edit-post-message").text('');
  
    switch (mode)
    {
      case "add":
        $("#edit-post-submit").val("Create Post");
        $("#post-form-title").html("Create Post");
        $("#edit-post-action").val("create-post");
        $("#Id-li").addClass('hidden');
        $("#Id").val('');
        $("#Heading").val('');
        $("#Description").val('');
        $("#Type").val(1);
        $("#ExpiryDate").val('');
        $("#ExpiryDateSelect").val('0');
        break;
      case "edit":
        $("#edit-post-submit").val("Update Post");
        $("#post-form-title").html("Update Post");
        $("#edit-post-action").val("update-post");

        $.post("post.php", { Action: "get-post", Id: id },
          function(result)
          {
            var post = jQuery.parseJSON(result);
            $("#Id").val(post.Id);
            $("#Id-li").removeClass('hidden');
            $("#Id-label").html(post.Id);
            $("#Heading").val(post.Heading);
            $("#Description").val(post.Description);
            $("#Type").val(post.Type);
            $("#ExpiryDateSelect").val(0);
            $("#ExpiryDate").val(moment(post.ExpiryDate).format("DD-MM-YYYY"));
            $("#post-categories input").prop('checked', false);
        for (var i = 0; i < post.Categories.length; i++)
      {
        $("#post-categories #cat_" + post.Categories[i].Id).prop('checked', true);
      }
            
          }
        );
        break;
      default:
        break;
    }
  }
</script>

<form id="form-edit-post" name="form-edit-post">
  <fieldset class="fieldset-table-style">
    <legend><img class="middle" src="images/32/post.png">&nbsp;<span id="post-form-title">Create Post</span></legend>
    <ul>
      <li id="Id-li" class="hidden"><label class="field-label" for="Id">Id:</label><span id="Id-label">&nbsp;</span></li>
      <li><label class="field-label" for="Type">Type:</label><select id="Type" name="Type" title="Choose whether you've got something to offer, or you're asking for something"><option value="1" >Wanted</option><option value="2" >Offered</option></select></li>
      <li><label class="field-label" for="Heading">Heading:</label><input type="text" id="Heading" name="Heading" size="26" maxlength="100" title="Write something snappy to say what you need / what you're offering"/></li>
      <li><label class="field-label" for="Description">Description:</label><textarea id="Description" name="Description" rows="5" cols="40" maxlength="1000" title="A longer description that goes into more detail (but please DON'T give out your contact details here)" ></textarea></li>
      <li><label class="field-label" for="ExpiryDateSelect">Expires:</label>
        <select id="ExpiryDateSelect" name="ExpiryDateSelect" title="When your post expires, it won't be visible to others any more">
            <option value="0" selected="selected">(Select)</option>
            <option value="1" >2 weeks</option>
            <option value="2" >4 weeks</option>
            <option value="3" >3 months</option>
            <option value="4" >6 months</option>
            <option value="5" >12 months</option>
        </select>
        <input type="text" id="ExpiryDate" size="26" name="ExpiryDate" value="" />
        
      </li>
      <li>
        <span title="Choose a category or categories that best fits your post. This helps people to find it more easily.">Categories:</span>
        <table id="post-categories" class="grid-table">
        <?php
        $rowcount = 0;
        foreach ($Categories as $Cat)
        {
          if (++$rowcount == 1) echo '<tr>';
          echo '<td><input type="checkbox" id="cat_'.$Cat->Id.'" name="cat_'.$Cat->Id.'" value="'.$Cat->Id.'" />'.$Cat->Name.'</td>';
          if ($rowcount == 3)
          {
            echo '</tr>';
            $rowcount = 0;
          }
        }
        ?>
        </table>
      </li>
    </ul>

  <input type="checkbox" id="Terms" name="Terms" />&nbsp;<label for="Terms">I agree that I will be solely responsible for the content of any request or offer that<br/> I post on this website. I will not hold the owner of this website responsible for any losses <br/>or damages to myself or to others that may result directly or indirectly from any listings <br/>that I post here.</label>
  <br/><br/>
    <input type="hidden" id="edit-post-action" name="Action" value="create-post"/>
    <input type="hidden" id="Id" name="Id" value=""/>
    <input class="button" id="edit-post-submit" type="submit"  value="Create Post"/>
    &nbsp;&nbsp;
    <span id="edit-post-progress" class="hidden"><img src="css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
    <span class="message" id="edit-post-message"></span>
  </fieldset>
</form>

<?php
}
?>
