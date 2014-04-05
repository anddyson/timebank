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
  $("#DateFrom").val(moment().subtract('weeks', 4).format("DD-MM-YYYY")); //set the search date to the default

  $("#form-search").submit(function(event)
  {
    event.preventDefault();
    $("#search-message").text('');
    $("#search-progress").toggleClass("hidden");
    var data = $(this).serialize();
    $.post("admin-print.php", data,
      function(result)
      {
        $("#posts-wanted").html('');
        $("#posts-offered").html('');

        result = jQuery.parseJSON(result);
        $("#search-progress").toggleClass("hidden");
        if (result == false)
        {
          $("#search-message").text('No matching posts were found. Please try again.');
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

          $("#posts-wanted").html(wanted);
          $("#posts-offered").html(offered);
        }
      }
    );
  });
  
  $("#SearchDate").change(function()
  {
    var dat = getSelectedDate($("#SearchDate").val(), 'past');
    if (dat != null) { $("#DateFrom").val(moment(dat).format("DD-MM-YYYY")); }
    else { $("#DateFrom").val(''); }
  });

  function render_post(post)
  {
  var html = '<div class="post"><h4>' + post.Heading + '</h4><p>' + post.Description + '</p><footer class="controls">Posted by <a href="#">' + post.User.Username + '</a> on ' + moment(post.CreatedDate).format('MMMM Do YYYY') + '</footer></div>';
  return html;
  }
  
  $("#print-button").click(function() { window.print(); } );
  $("#print-preview-button").click(togglePrintPreview);

});

function togglePrintPreview()
{
	var currCSS = $("#print-css")[0];
    if(currCSS.media == 'all') currCSS.media = 'print';
    else currCSS.media = 'all';
}    

</script>

<div class="row">
 <input type="button" class="button" id="print-button"  value="Print The Posts"/>
 &nbsp;&nbsp;
 <input type="button" class="button" id="print-preview-button" value="Print Preview"/>
</div>
<div class="row" id="search-options">
  <div class="12u">
    <section>
      <form id="form-search" name="form-search">
        <fieldset>
          <legend>Print Recent Posts</legend>
            <label for="SearchDate" id="SearchDateLabel">Listed in:&nbsp;</label>
            <select id="SearchDate" name="SearchDate">
                <option value="1" >Last 2 weeks</option>
                <option value="2" selected="selected" >Last 4 weeks</option>
                <option value="3" >Last 3 months</option>
                <option value="4" >Last 6 months</option>
                <option value="5" >Last  12 months</option>
                <option value="6" >Anytime</option>
            </select>
            <input type="hidden" name="Action" value="Search"/>
            <input type="hidden" id="DateFrom" name="DateFrom" value=""/>
            &nbsp;&nbsp;
            <input type="submit" class="button" id="submit-search" value="Display"/>
            &nbsp;&nbsp;
            <span id="search-progress" class="hidden"><img src="themes/<?php echo $THEME; ?>/css/images/progress.gif" height="15" />&nbsp;Updating, please wait...</span>
            <span class="message" id="search-message"></span>
        </fieldset>
      </form>
    </section>
  </div>
</div>
<br/>
<div class="row" id="results">
	<div class="row" id="print-posts-header">
  	<h2>Levy Timebank - Recent Posts</h2>
  	</div>
  	<div class="row">
  <div class="6u" id="posts">
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
</div>

<?php
	include_once("admin-footer.php");
}
?>
