<?php 
include_once("admin.php");
echo nl2br(file_get_contents("../logs/log.txt"));
include_once("admin-footer.php");
?>
