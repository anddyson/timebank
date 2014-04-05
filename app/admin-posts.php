<?php
include_once("components_include.php");

$Action = $_POST["Action"];

if ($Action == "Search")
{
  $db = MySQLConnection::GetInstance();
  $Query = new SearchQuery();
  $Query = Utils::ArrayToObject($_POST, $Query);
  $Query->Wanted = true;
  $Query->Offered = true;
  $Categories = $db->GetCategories();
  $Query->CategoryIDs = Utils::ObjectFieldToCommaSeparatedList($Categories, "Id");
  $Posts = $db->SearchPosts($Query);
  echo json_encode($Posts);
}
else
{
  include_once("admin.php");
?>

<script type="text/javascript">
$(function()
{
  $("#ModerateDateFrom").val(moment().subtract('weeks', 4).format("DD-MM-YYYY")); //set the search date to the default

  $("#moderate-form-search").submit(function(event)
  {
    event.preventDefault();
    refresh_posts();
  });
  
  $("#ModerateSearchDate").change(function()
  {
    var dat = getSelectedDate($("#ModerateSearchDate").val(), 'past');
    if (dat != null) { $("#ModerateDateFrom").val(moment(dat).format("DD-MM-YYYY")); }
    else { $("#ModerateDateFrom").val(''); }
  });

  //"Are you sure" type dialog box - initial setup.
  $("#delete-post-dialog").dialog({ modal: true, title: "Confirm Delete", autoOpen: false });
    
  //delete a post
  $("#moderate-posts-wanted, #moderate-posts-offered").on("click", ".post-delete-link", function(event)
  {
    event.preventDefault();
    var postid = $(this).attr('postid');
    $("#delete-post-dialog").dialog('option', 'buttons', {
      "OK" : function()
      {
        $.post("post.php", { Action: "delete-post", PstId: postid },
          function(result)
          { 
            $("#delete-post-dialog").dialog("close");
            refresh_posts();
          }
        );
      },
      "Cancel" : function() { $(this).dialog("close"); }
    });
    $("#delete-post-dialog").dialog("open");    
  });

  //send a message about a post
  $("#moderate-posts-wanted, #moderate-posts-offered").on("click", ".post-respond", function(event)
  {
    set_edit_message_mode("add", null, $(this).attr('pstuserid'), $(this).attr('pstusername'), 'Message from moderator re: ' + $(this).attr('pstheading'));
    show_popup("#message-popup");
  });  
  
  //refresh the display when a message has been sent
  $(document).on("messagesent", function(e, message)
  {
      hide_popup("#message-popup");
  });
});

  function refresh_posts()
  {
    $("#moderate-search-message").text('');
    $("#moderate-search-progress").toggleClass("hidden");
    var data = $("#moderate-form-search").serialize();
    $.post("admin-posts.php", data,
      function(result)
      {
        $("#moderate-posts-wanted").html('');
        $("#moderate-posts-offered").html('');

        result = jQuery.parseJSON(result);
        $("#moderate-search-progress").toggleClass("hidden");
        if (result == false)
        {
          $("#moderate-search-message").text('No matching posts were found. Please try again.');
        }
        else
        {
          //divide the results into wanted and offered, to display in two different columns
          var wanted = '';
          var offered = '';
          for (var i = 0; i < result.length; i++)
          {
            var post = result[i];
            if (post.Type == 1) { wanted += render_post(post); }
            else { offered += render_post(post); }
          }

          $("#moderate-posts-wanted").html(wanted);
          $("#moderate-posts-offered").html(offered);
        }
      }
    );
  }

  function render_post(post)
  {
    var html = '<div class="post"><h4>' + post.Heading + '</h4><p>' + post.Description + '</p><footer class="controls"><button class="button post-respond" pstuserid="' + post.UserId + '" pstusername="' + post.User.FirstName + ' ' + post.User.LastName + ' (' + post.User.Username + ')' + '" pstheading="' + post.Heading + '">Send Message</button>&nbsp;&nbsp;<a href="#" postid="' + post.Id + '" class="button post-delete-link">Remove</a><br><br>Posted by <a href="#">' + post.User.Username + '</a> on ' + moment(post.CreatedDate).format('MMMM Do YYYY') + '</footer></div>';
    return html;
  }

</script>

<div id="message-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="message"><input type="button" class="button" value='X' /></div>
    <?php include_once("message.php"); ?>
  </section>
</div>
<div id="delete-post-dialog">Please confirm that this post is unsuitable and should be deleted?</div>

<div class="row" id="moderate-options">
  <div class="12u">
    <section>
      <form id="moderate-form-search" name="moderate-form-search">
        <fieldset>
          <legend>Moderate Posts</legend>
            <label for="ModerateSearchDate" id="ModerateSearchDateLabel">Listed in:&nbsp;</label>
            <select id="ModerateSearchDate" name="ModerateSearchDate">
                <option value="1" >Last 2 weeks</option>
                <option value="2" selected="selected" >Last 4 weeks</option>
                <option value="3" >Last 3 months</option>
                <option value="4" >Last 6 months</option>
                <option value="5" >Last  12 months</option>
                <option value="6" >Anytime</option>
            </select>
            <input type="hidden" name="Action" value="Search"/>
            <input type="hidden" id="ModerateDateFrom" name="ModerateDateFrom" value=""/>
            &nbsp;&nbsp;
            <input type="submit" class="button" id="moderate-submit-search" value="Display"/>
            &nbsp;&nbsp;
            <span id="moderate-search-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
            <span class="message" id="moderate-search-message"></span>
        </fieldset>
      </form>
    </section>
  </div>
</div>
<br/>
<div class="row" id="moderate-results">
    <div class="row">
  <div class="6u" id="moderate-posts">
    <section>
      <h2>Wanted</h2>
      <div id="moderate-posts-wanted">
      </div>
    </section>
  </div>
  <div class="6u">
    <section>
      <h2>Offered</h2>
      <div id="moderate-posts-offered">
      </div>
    </section>
  </div>
  </div>
</div>
<?php
  include_once("admin-footer.php");
}
?>
