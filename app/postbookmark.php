<?php
include_once("components_include.php");
  
$Action = $_POST["Action"];
$UserId = $_SESSION["UserId"];
$db = MySQLConnection::GetInstance();

if ($Action == "add-post-bookmark")
{
  $Pbm = new PostBookmark();
  $Pbm = Utils::ArrayToObject($_POST, $Pbm);
  $Pbm->UserId = $UserId;
  $Result = $db->AddPostBookmark($Pbm);
  echo json_encode($Result);
  die;
}
else if ($Action == "delete-post-bookmark")
{
  $Result = $db->DeletePostBookmark($_POST["PbmId"], $UserId, $_POST["PostId"]);
  echo json_encode($Result);
  die;
}
else if ($Action == "get-post-bookmark")
{
  $Bookmarks = $db->GetPostBookmarks($_Post["Id"]);
  echo json_encode($Bookmarks[0]);
  die;
}
else if ($Action == "get-post-bookmarks")
{
  $Bookmarks = $db->GetPostBookmarks(null, $UserId);
  echo json_encode($Bookmarks);
  die;
}
else if ($Action == "get-posts-bookmarked")
{
  $Posts = $db->GetPostsBookmarked($UserId);
  echo json_encode($Posts);
  die;
}
?>
