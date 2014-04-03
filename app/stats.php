<?php
include_once("components_include.php");

$UserId = $_SESSION["UserId"];
$Action = $_POST["Action"];

$db = MySQLConnection::GetInstance();
$UserStats = $db->GetUserStats($UserId);

if ($Action == "refresh-stats")
{
  echo json_encode($UserStats);
  die;
}
else
{
?>

<script type="text/javascript">
$(function()
{
  //stats
  var stats = <?php echo json_encode($UserStats); ?>;
  $("#stats").html(render_stats(stats[0]));
});

function refresh_stats()
{
  $("#stats").fadeToggle();
  $("#stats-update-progress").toggleClass("hidden");
  $.post("stats.php", { Action: "refresh-stats"},
    function(result)
    {
      var stats = jQuery.parseJSON(result);
      $("#stats-update-progress").toggleClass("hidden");
      $("#stats").html(render_stats(stats[0]));
      $("#stats").fadeToggle();
    }
  );
}

function render_stats(stats)
{
  var html = '<li><img class="middle" src="images/24/clock.png">&nbsp;You are <b>' + (stats.Hours >= 0 ? stats.Hours + ' hour' + (stats.Hours != 1 ? 's' : '') + ' in credit ' : (-stats.Hours) + ' hour' + (stats.Hours < -1 ? 's' : '') +  ' in debt!') + '</b></li>';
  html += '<li><img class="middle" src="images/24/exchange.png" >&nbsp;You need to confirm <b>' + stats.TransactionsPendingIncoming + ' exchange' + (stats.TransactionsPendingIncoming != 1 ? 's' : '') + '</b></li>';
  html += '<li><img class="middle" src="images/24/message.png">&nbsp;You have <b>' + stats.UnreadMessages + ' unread message' + (stats.UnreadMessages != 1 ? 's' : '') + '</b> from other users</li>';
  html += '<li><img class="middle" src="images/24/exchange2.png">&nbsp;Others need to confirm <b>' + stats.TransactionsPendingOutgoing + ' exchange' + (stats.TransactionsPendingOutgoing != 1 ? 's' : '') + '</b> that you submitted</li>';
  html += '<li><img class="middle" src="images/24/post.png" >&nbsp;You currently have <b>' + stats.WantedCount + ' Wanted</b> and <b>' + stats.OfferedCount + ' Offered</b> posts on display</li>';
  return html;
}
</script>

<div class="left sectionheading"><img class="middle" src="images/24/star.png">&nbsp;Vital Stats&nbsp;<img class="middle" src="images/24/star.png"></div>
<div id="stats-update-progress" class="right hidden"><img src="css/images/progress.gif" height="15" /></div>
<div class="clear"></div>
<br/><br/>
<ul class="link-list" id="stats">
</ul>

<?php
}
?>
