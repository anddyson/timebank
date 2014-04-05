<?php
include_once("components_include.php");

$Action = $_POST["Action"];
$UserId = $_SESSION["UserId"];

$db = MySQLConnection::GetInstance();
$Categories = $db->GetCategories();

if ($Action == "Search")
{
  $Output = new DataActionResult();

  try
  {
    $Query = new SearchQuery();
    $Query = Utils::ArrayToObject($_POST, $Query);
    $Query->Wanted = ($_POST["Wanted"] ? true : false);
    $Query->Offered = ($_POST["Offered"] ? true : false);
    $Query->CategoryIDs = "";
    $Query->LogSearch = true;
    $count = 0;
    //work out which categories have been selected
    foreach (array_keys($_POST) as $key)
    {
      if (preg_match("/cat_(\d+)/", $key, $matches))
      {
        if ($count > 0) $Query->CategoryIDs .= ",";
        $Query->CategoryIDs .= $matches[1];      
        $count++;
      }
    }
  
    $Output->data = $db->SearchPosts($Query);
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
  try
  {
    //initial search of 6 months posts
    $db = MySQLConnection::GetInstance();
    $Query = new SearchQuery();
    $Query = Utils::ArrayToObject($_POST, $Query);
    $Query->Wanted = true;
    $Query->Offered = true;
    $Query->DateFrom = date('d-m-Y', strtotime("-6 months"));
    $Categories = $db->GetCategories();
    $Query->CategoryIDs = Utils::ObjectFieldToCommaSeparatedList($Categories, "Id");
    $Posts = $db->SearchPosts($Query);
    $Bookmarks = $db->GetPostBookmarks(null, $UserId);
  }
  catch (Exception $e)
  {
    $ErrorId = Logger::LogException($e);
    throw new FriendlyException($ErrorId, 500, $e);
  }  

  include_once("header.php");
?>

<script type="text/javascript">
var bookmarks;
$(function()
{
  //initialise
  bookmarks = <?php echo json_encode($Bookmarks); ?>;
  render_search_results(<?php echo json_encode($Posts); ?>);
  $("#DateFrom").val(moment().subtract('months', 6).format("DD-MM-YYYY")); //set the search date to the default
  
  if (isLoggedIn() == false)
  {
    $("#show-bookmarks, #create-post").addClass("hidden");
  }

  //various options
  $("#show-search").click(function(event)
  {
    $("#search-options").removeClass("hidden");
  });
  
  $("#show-bookmarks").click(function(event)
  {
    $("#initial-message, #search-options").addClass("hidden");
    $("#bookmark-search-message").text('');
    $("#bookmark-search-progress").toggleClass("hidden");
    $.post("postbookmark.php", { Action: "get-posts-bookmarked" },
      function(result)
      {
        $("#posts-wanted").html('');
        $("#posts-offered").html('');

        result = jQuery.parseJSON(result);
        $("#bookmark-search-progress").toggleClass("hidden");
        if (result == false)
        {
          $("#bookmark-search-message").text("You haven't bookmarked anybody's posts yet.");
        }
        else { render_search_results(result); }
      }
    );
  });
  
  $("#create-post").click(function(event)
  {
    show_popup("#post-popup");
  });
  
  //do a search
  $("#form-search").submit(function(event)
  {
    event.preventDefault();
    $("#results").fadeToggle();
    $("#initial-message").addClass("hidden");
    $("#search-message").text('');
    $("#search-progress").toggleClass("hidden");
    var data = $(this).serialize();
    $.post("search.php", data,
      function(result)
      {
        $("#posts-wanted").html('');
        $("#posts-offered").html('');

        result = jQuery.parseJSON(result);
        $("#search-progress").toggleClass("hidden");
        if (result.success == true)
        {
          if (result.data.length == 0){ $("#search-message").text('No matching posts were found. Please try again.'); }
          else { render_search_results(result.data); }
        }
        else { $("#search-message").text(result.message); }
        $("#results").fadeToggle();
      }
    );
  });
  
  $("#SearchDate").change(function()
  {
    var dat = getSelectedDate($("#SearchDate").val(), 'past');
    if (dat != null) { $("#DateFrom").val(moment(dat).format("DD-MM-YYYY")); }
    else { $("#DateFrom").val(''); }
  });
  
  //send a message about a post
  $("#posts-wanted, #posts-offered").on("click", ".post-respond", function(event)
  {
    set_edit_message_mode("add", null, $(this).attr('pstuserid'), $(this).attr('pstusername'), $(this).attr('pstheading'));
    show_popup("#message-popup");
  });
  
  //bookmark a post
  $("#posts-wanted, #posts-offered").on("click", ".post-bookmark", function(event)
  {
    $(this).removeClass("post-bookmark");
    $(this).addClass("post-un-bookmark");
    $(this).html("Remove Bookmark");
    $.post("postbookmark.php", { Action: "add-post-bookmark", PostId: $(this).attr('pstid') },
      function(result)
      {
        show_notification("Post Bookmarked", "You can find it anytime on the search page");
        //ADD BOOKMARK TO JS BOOKMARKS COLLECTION
      }
    );
  });

  //un-bookmark a post
  $("#posts-wanted, #posts-offered").on("click", ".post-un-bookmark", function(event)
  {
    $(this).addClass("post-bookmark");
    $(this).removeClass("post-un-bookmark");
    $(this).html("Bookmark");
    $.post("postbookmark.php", { Action: "delete-post-bookmark", PostId: $(this).attr('pstid') },
      function(result)
      {
        show_notification("Bookmark Removed", "It won't appear in your Bookmarks list anymore");
        //REMOVE BOOKMARK FROM JS BOOKMARKS COLLECTION
      }
    );
  });
  
  
  //refresh the display when a message has been sent
  $(document).on("messagesent", function(e, message)
  {
      hide_popup("#message-popup");
  });
  
  function render_search_results(posts)
  {
    //divide the results into wanted and offered, to display in two different columns
    var wanted = '';
    var offered = '';
    for (var i = 0; i < posts.length; i++)
    {
      var post = posts[i];
      if (post.Type == 1) { wanted += render_post(post); }
      else { offered += render_post(post); }
    }

    $("#posts-wanted").html(wanted);
    $("#posts-offered").html(offered);
  }
  
  function render_post(post)
  {
    //check whether post is bookmarked by the user
    bookmarked = false;
    if (isLoggedIn() == true)
    {
      for (j = 0; j < bookmarks.length; j++) { if (post.Id == bookmarks[j].PostId) { bookmarked = true; break; } }
    }
    var html = '<div class="post"><h4><img class="middle" src="images/24/post.png">&nbsp;' + post.Heading + '</h4><p>' + post.Description + '</p><footer class="controls">' + (isLoggedIn() == true ? '<button class="button post-respond" pstuserid="' + post.UserId + '" pstusername="' + post.User.FirstName + ' ' + post.User.LastName + ' (' + post.User.Username + ')' + '" pstheading="' + post.Heading + '">Respond</button>&nbsp;&nbsp;<button pstid="' + post.Id + '" class="button' + (bookmarked == false ? ' post-bookmark">Bookmark' : ' post-un-bookmark">Remove Bookmark') + '</button>&nbsp;&nbsp;Posted by <a href="#">' + post.User.Username + '</a>' : '<a href="register.php">Sign up</a> or <a href="index.php">Log in</a> to reply to this post!' ) + '</footer></div>';
    return html;
  }
});
</script>

<div id="modalpopupbackground" class="hidden"></div>
<div id="message-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="message"><input type="button" class="button" value='X' /></div>
    <?php include_once("message.php"); ?>
  </section>
</div>
<div id="post-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="post"><input type="button" class="button" value='X' /></div>
    <?php include_once("post.php"); ?>
  </section>
</div>

<div class="row">
  <div class="12u">
        <section>
          <span id="initial-message">Below are posts added in the last 6 months. You can change this by searching.<br/><br/></span>
          <b>I want to...</b>&nbsp;&nbsp;
          <button class="button" id="show-search">Search for more posts</button>
          &nbsp;&nbsp;&nbsp;
          <button class="button" id="show-bookmarks">View my bookmarked posts</button>
          &nbsp;&nbsp;&nbsp;
          <button class="button" id="create-post">Create my own post</button>
          <br/><br/>
          <span id="bookmark-search-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
          <span class="message" id="bookmark-search-message"></span>
        </section>
  </div>
</div>
<div class="row hidden" id="search-options">
  <div class="12u">
    <section>
      <form id="form-search" name="form-search">
        <fieldset>
          <legend><img class="middle" src="images/32/search.png">&nbsp;Find Posts</legend>
          <div class="row">
          <div class="6u">
            Search for&nbsp;<input type="text" id="SearchString" name="SearchString" size="30" maxlength="200"/>&nbsp;&nbsp;<i>(Leave blank for "anything")</i><br/><br/>
            Show Wanted&nbsp;&nbsp;<input type="checkbox" name="Wanted" value="Wanted" checked="checked" />&nbsp;&nbsp;
            Show Offered&nbsp;&nbsp;<input type="checkbox" name="Offered" value="Offered" checked="checked" /><br/><br/>
            <label for="SearchDate" id="SearchDateLabel">Listed in:&nbsp;</label>
            <select id="SearchDate" name="SearchDate">
                <option value="1" >Last 2 weeks</option>
                <option value="2" >Last 4 weeks</option>
                <option value="3" >Last 3 months</option>
                <option value="4" selected="selected">Last 6 months</option>
                <option value="5" >Last  12 months</option>
                <option value="6" >Anytime</option>
            </select>
          </div>
          <div class="6u">
            Categories:<br/>
            <table class="grid-table">
            <?php
            $rowcount = 0;
            foreach ($Categories as $Cat)
            {
              if (++$rowcount == 1) echo '<tr>';
              echo '<td><input type="checkbox" id="cat_'.$Cat->Id.'" name="cat_'.$Cat->Id.'" value="'.$Cat->Id.'" checked="checked" />'.$Cat->Name.'</td>';
              if ($rowcount == 4)
              {
                echo '</tr>';
                $rowcount = 0;
              }
            }
            ?>
            </table>
          </div>
          </div>
            <input type="hidden" name="Action" value="Search"/>
            <input type="hidden" id="DateFrom" name="DateFrom" value=""/>
            <input type="submit" class="button" id="submit-search" value="Search"/>
            &nbsp;&nbsp;
            <span id="search-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
            <span class="message" id="search-message"></span>
        </fieldset>
      </form>
    </section>
  </div>
</div>
<div class="row" id="results">
  <div class="6u">
    <section>
      <h2>Wanted</h2>
      <div id="posts-wanted">
      </div>
    </section>
  </div>
  <div class="6u">
    <section>
      <h2>Offered</h2>
      <div id="posts-offered">
      </div>
    </section>
  </div>
</div>
<?php
  include_once("footer.php");
}
?>
