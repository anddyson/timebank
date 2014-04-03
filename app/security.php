<?php
session_start();
// check login session status
if (is_numeric($_SESSION["UserId"])) $LoginStatus = "true";
else $LoginStatus = "false";
?>
