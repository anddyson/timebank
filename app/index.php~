<?php include_once("header.php");

$db = MySQLConnection::GetInstance();
$Query = new SearchQuery();
$Query->Wanted = true;
$Query->Offered = true;
$Categories = $db->GetCategories();
$Query->CategoryIDs = Utils::ObjectFieldToCommaSeparatedList($Categories, "Id");
$Posts = $db->SearchPosts($Query);
$Bookmarks = null;
  
if ($LoginStatus == "true")
{
  $UserId = $_SESSION["UserId"];
  $Bookmarks = $db->GetPostBookmarks(null, $UserId);
}
?>

<script type="text/javascript">
var bookmarks;

$(function()
{
  //check login status and show the right page elements as appropriate
  if (isLoggedIn() == true) {
    $("#search-link").removeClass("hidden");
    bookmarks = <?php echo json_encode($Bookmarks); ?>;
    refreshAfterLogin();
  }
  else { refreshAfterLogout(); }
  
  //display most recent posts
  var posts = <?php echo json_encode($Posts); ?>;
  //divide the results into wanted and offered, to display in two different columns
  var wanted = '';
  var offered = '';
  var wantedcount = 1;
  var offeredcount = 1;
  var maxcount = 5;
  for (i = 0; i < posts.length; i++)
  {
    var post = posts[i];
    if (post.Type == 1 && wantedcount <= maxcount)
    {
      wanted += render_post(post);
      wantedcount++;
    }
    else if (post.Type == 2 && offeredcount <= maxcount)
    {
      offered += render_post(post);
      offeredcount++;
    }
  }
  $("#posts-wanted").html(wanted);
  $("#posts-offered").html(offered);

  function render_post(post)
  {
    //check whether post is bookmarked by the user
    bookmarked = false;
    if (isLoggedIn() == true)
    {
      for (j = 0; j < bookmarks.length; j++) { if (post.Id == bookmarks[j].PostId) { bookmarked = true; break; } }
    }
    var html = '<div class="post"><h4><img class="middle" src="images/24/post.png">&nbsp;' + post.Heading + '</h4><p>' + post.Description + '</p><footer class="controls">' + (isLoggedIn() == true ? '<button class="button post-respond" pstuserid="' + post.UserId + '" pstusername="' + post.User.FirstName + ' ' + post.User.LastName + ' (' + post.User.Username + ')' + '" pstheading="' + post.Heading + '">Respond</button>&nbsp;&nbsp;<button pstid="' + post.Id + '" class="button' + (bookmarked == false ? ' post-bookmark">Bookmark' : ' post-un-bookmark">Remove Bookmark') + '</button>&nbsp;&nbsp;Posted by <a href="#">' + post.User.Username + '</a>' : ' Sign up or Log in to reply to this post!') + '</footer></div>';
    return html;
  }

  //reply to a post
  $("#posts").on("click", ".post-respond", function(event)
  {
    set_edit_message_mode("add", null, $(this).attr('pstuserid'), $(this).attr('pstusername'), $(this).attr('pstheading'));
    show_popup("#message-popup");
  });

  //bookmark a post
  $("#posts").on("click", ".post-bookmark", function(event)
  {
    $(this).removeClass("post-bookmark");
    $(this).addClass("post-un-bookmark");
    $(this).html("Remove Bookmark");
    $.post("postbookmark.php", { Action: "add-post-bookmark", PostId: $(this).attr('pstid') },
      function(result)
      {
        show_notification("Post Bookmarked", "You can find it anytime on the search page");
      }
    );
  });

  //un-bookmark a post
  $("#posts").on("click", ".post-un-bookmark", function(event)
  {
    $(this).addClass("post-bookmark");
    $(this).removeClass("post-un-bookmark");
    $(this).html("Bookmark");
    $.post("postbookmark.php", { Action: "delete-post-bookmark", PostId: $(this).attr('pstid') },
      function(result)
      {
        show_notification("Bookmark Removed", "It won't appear in your Bookmarks list anymore");
      }
    );
  });

  //refresh the display when a message has been sent
  $(document).on("messagesent", function(e, message)
  {
      hide_popup("#message-popup");
      refresh_stats();
  });
});
  
function refreshAfterLogin()
{
  $("#div-login").addClass("hidden");
  $(".post footer").removeClass("hidden");
  $("#div-my-timebank").removeClass("hidden");
  refresh_stats();
}
  
function refreshAfterLogout()
{
  $("#div-login").removeClass("hidden");
  $("#div-my-timebank").addClass("hidden");
}
</script>

<div id="modalpopupbackground" class="hidden"></div>
<div id="message-popup" class="modalpopup hidden">
  <section>
    <div class="modalpopupclose" id="message"><input type="button" class="button" value='X' /></div>
    <?php include_once("message.php"); ?>
  </section>
</div>

        <div class="row">
          <div class="6u">
            <section>
              <h2>Do yourself a favour!</h2>
              <img class="left" src="themes/<?php echo $THEME; ?>/images/general/4.png" />
              <p><strong>Levy TimeBank </strong>is a brand new idea in Levenshulme, Manchester to get local people involved in swapping an hour of their time for an hour of some one else's.<p>
        <p><a href="register.php">Sign up</a> to get involved and exchange your time and skills with others in Levenshulme. Everyone's effort is valued equally - as time! Use our site to easily find others to swap with, and keep a record of your time credits.</p>
              <footer class="controls">
                <a href="handbook.php" class="button" >Find out more in our handbook</a>
                &nbsp;&nbsp;
                <a href="http://www.timebanking.org/" class="button" target="_blank">Visit Timebanking UK</a>
                &nbsp;&nbsp;
				<a href="http://twitter.com/LevyTimebank" class="button" target="_blank"><img src="themes/<?php echo $THEME; ?>/images/twitter.png"/>&nbsp;Follow @LevyTimebank</a>
              </footer>
              <div class="clear"></div>
            </section>
          </div>

          <div class="6u">
            <div id="div-login">
              <section>
                <?php include_once("login.php");?>
              </section>
            </div>
            <div id="div-my-timebank">
              <section>
                      <?php include_once("stats.php"); ?>
                <footer class="controls">
                  <a href="mytimebank.php" class="button">Details...</a>
                </footer>
              </section>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="12u" id="posts">
            <section>
              <div class="row" id="recent-posts">
                <div class="6u">
                  <h2>Recent Wanted</h2>
                  <div id="posts-wanted">
                  </div>
              </div>
              <div class="6u">
                  <h2>Recent Offered</h2>
                  <div id="posts-offered">
                  </div>
              </div>
            </div>
              <a id="search-link" href="search.php" class="button hidden">More Posts...</a>&nbsp;
            </section>
          </div>
        </div>
<?php include_once("footer.php"); ?>
