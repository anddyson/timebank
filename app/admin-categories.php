<?php
include_once("components_include.php");

$Action = $_POST["Action"];

$Db = MySQLConnection::GetInstance();

if ($Action == "edit-category")
{
  $Results = $Db->GetCategories($_POST["Id"]);
  $Cat = $Results[0];
  echo json_encode($Cat);
  die;
}
else if ($Action == "update-category")
{
  $Cat = new Category();
  $Cat = Utils::ArrayToObject($_POST, $Cat);
  $Db->UpdateCategory($Cat);
  echo "true";
  die;
}
else if ($Action == "add-category")
{
  $Cat = new Category();
  $Cat = Utils::ArrayToObject($_POST, $Cat);
  $Db->AddCategory($Cat);
  echo "true";
  die;
}
else if ($Action == "delete-category")
{
  $Db->DeleteCategory($_POST["Id"]);
  echo "true";
  die;
}
else if ($Action == "update-list")
{
  $Categories = $Db->GetCategories();
  echo json_encode($Categories);
  die;
}
else
{
  $Categories = $Db->GetCategories();
  include_once("admin.php");
?>

<script type="text/javascript">
$(function()
{
  //go to add category form
  $("#add-category").click(function() { showCategoriesEdit('add'); });

  //"Are you sure" type dialog box - initial setup.
    $("#dialog").dialog({ modal: true, title: "Confirm Delete", autoOpen: false });
  
  // insert/update a category
  $("#form-categories-edit").submit(function(event)
  {
    event.preventDefault();
        $("#category-edit-progress").toggleClass("hidden");
    var data = $(this).serialize();
    $.post("admin-categories.php", data,
      function(result)
      {
            $("#category-edit-progress").toggleClass("hidden");
        showCategoriesList();
      }
    );

  });

  //close the form without adding/updating anything
  $("#cancel-category-edit").click(function() { showCategoriesList(); });
  
  //go to edit category form
  $("#tbl-categories-list").on("click", ".category-edit-link", function(event)
  {
    event.preventDefault();
    
    $.post("admin-categories.php", { Action: "edit-category", Id: $(this).attr('catid') },
      function(result)
      {
        var cat = jQuery.parseJSON(result);
        showCategoriesEdit('edit', cat);
      }
    );
  });

  //delete a category
  $("#tbl-categories-list").on("click", ".category-delete-link", function(event)
  {
    event.preventDefault();
    var catid = $(this).attr('catid');

    $("#dialog").dialog('option', 'buttons', {
      "OK" : function()
      {
        $.post("admin-categories.php", { Action: "delete-category", Id: catid },
          function(result)
          { 
            $("#dialog").dialog("close");
            refreshCategoriesList();
          }
        );
      },
      "Cancel" : function() { $(this).dialog("close"); }
    });
        $("#dialog").dialog("open");    
  });
  
  //show the add/edit categories form
  function showCategoriesEdit(status, cat)
  {
    if (status == 'add')
    {
      $("#category-edit-action").val("add-category");
      $("#submit-category-edit").val("Add");
      $("#Id").val('');
      $("#Id-label").html('&nbsp;');
      $("#Name").val('');
    }
    else if (status == 'edit')
    {
      $("#category-edit-action").val("update-category");
      $("#submit-category-edit").val("Update");
      $("#Id").val(cat.Id);
      $("#Id-label").html(cat.Id);
      $("#Name").val(cat.Name);
    }

    $("#div-categories-edit").removeClass("hidden");
    $("#div-categories-list").addClass("hidden");

  }
  
  //show the list of categories (and refresh)
  function showCategoriesList()
  {
    $("#div-categories-edit").addClass("hidden");
    $("#div-categories-list").removeClass("hidden");
    refreshCategoriesList();
  }

  //get fresh list of categories from the server
  function refreshCategoriesList()
  {
    $("#tbl-categories-list").find("tr:gt(0)").remove();
    $.post("admin-categories.php", { Action: "update-list"},
      function(result)
      {
        drawCategoriesList(jQuery.parseJSON(result));
      }
    );
  }
  
  //render the data as rows of a HTML table with links etc
  function drawCategoriesList(categories)
  {
    var html = "";
    for (var i = 0; i < categories.length; i++)
    {
      html += "<tr><td>" + categories[i].Name + "</td><td><a class='category-edit-link' catid='" + categories[i].Id + "' href='#'>Edit</a></td><td><a class='category-delete-link' catid='" + categories[i].Id + "' href='#'>Delete</a></td></tr>";
    }
    $("#tbl-categories-list").find("tr").after(html);
  }

  //executed the first time the page is loaded
  var categories = <?php   echo json_encode($Categories); ?>;
  drawCategoriesList(categories);
});
</script>

<div id="div-categories-list">
  <h2>Manage Categories</h2>
  <button class="button" id="add-category" type="button">Add Category >></button>
  <br/><br/>
  <form name="form-categories-list" id="form-categories-list">
    <table id="tbl-categories-list" class="form-table">
    <tbody>
      <tr><th>Category</th><th colspan="2">Action</th></tr>
    </tbody>
    </table>
  </form>
</div>
<div id="div-categories-edit" class="hidden">
  <form name="form-categories-edit" id="form-categories-edit">
    <fieldset class="fieldset-table-style">
      <legend>Add / Edit Category</legend>
      <ul>
        <li><label class="field-label" >Id:</label><span id="Id-label">&nbsp;</span></li>
        <li><label class="field-label" for="Name">Category Name:</label><input type="text" size="26" id="Name" name="Name" maxlength="100" value=""/></li>
      <ul>

      <input type="hidden" id="Id" name="Id" value=""/>
      <input type="hidden" name="Action" id="category-edit-action" value="add-category"/>
      <input type="submit" class="button" id="submit-category-edit" value="Add" />
      &nbsp;&nbsp;
      <input type="button" class="button" id="cancel-category-edit" value="Cancel"/>
      &nbsp;&nbsp;
      <span id="category-edit-progress" class="hidden"><img src="css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
      <span class="message" id="category-edit-message"></span>
    </fieldset>
  </form>
</div>
<div id="dialog">Are you sure you want to delete this Category?</div>
<?php
  include_once("admin-footer.php");
}
?>
