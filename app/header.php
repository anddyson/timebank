<?php
//ini_set('display_errors',1); //DEBUGGING MODE
ob_start(); //don't send headers to the browser straight away, in case we want to redirect (e.g. for login)

$THEME = "default"; //move this to db eventually
include_once("themes/".$THEME."/config/app_config.php");

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title><?php echo $APP_NAME; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="Levy TimeBank is a service in Levenshulme, Manchester to get local people involved in swapping an hour of their time for an hour of some one else's. Get involved and exchange your time and skills with others in Levenshulme. Everyone's effort is valued equally - as time! Use our site to easily find others to swap with, and keep a record of your time credits." />
    <meta name="keywords" content="Levy, Levenshulme, TimeBank, Time, Bank, Community, Sharing, Economy, Local, Volunteers, Good Neighbours, Neighbours, Manchester, Longsight, Burnage, Fallowfield, Heaton, Exchange" />
    <meta name="google-site-verification" content="Vw_SJFDb5JS9TR9vRkUZIcS__VEwF1dDcl_dT0Y17gs" />
    <link href="themes/<?php echo $THEME; ?>/css/fonts/font.css" rel="stylesheet"/>
    <link href="themes/<?php echo $THEME; ?>/css/jquery-ui/jquery-ui-1.9.2.custom.css" rel="stylesheet"/>
    <noscript>
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/5grid/core.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/5grid/core-desktop.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/5grid/core-1200px.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/5grid/core-noscript.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/style.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/style-desktop.css" />
    </noscript>
    <script src="themes/<?php echo $THEME; ?>/css/5grid/jquery.js"></script>
    <script src="themes/<?php echo $THEME; ?>/css/5grid/init.js?use=mobile,desktop,1000px&amp;mobileUI=1&amp;mobileUI.theme=none&amp;mobileUI.titleBarHeight=55&amp;mobileUI.openerWidth=66"></script>
    <!--[if IE 9]><link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/style-ie9.css" /><![endif]-->
    <!--[if lte IE 8]>
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/5grid/core.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/5grid/core-desktop.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/5grid/core-1200px.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/5grid/core-noscript.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/style.css" />
      <link rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/style-desktop.css" />
      <![endif]-->

    <link id="print-css" rel="stylesheet" href="themes/<?php echo $THEME; ?>/css/print.css" media="print" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <script src="js/utilities.js"></script>
    <script src="js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/moment.min.js"></script>
    <script type="text/javascript">
    $(function()
    {
      //set the menu highlight to highlight the current page
      var sPath = document.location.href;
      var sPage = '#' + sPath.substring(sPath.lastIndexOf('/') + 1);
      var sPage = sPage.substring(0, sPage.lastIndexOf('.'));
      if (sPage == '') sPage = "#index"; //covers case where URL ends at the folder name, with no filename specified, so we will have defaulted to index.php
      $(sPage).addClass("current-page-item");

    });
    
</script>
  </head>

<?php include_once("components_include.php");?>
  <body>
  
  <?php
    //check login status and output status to a hidden field, so the Javascript code can act accordingly
    if ($LoginStatus == "false")
    {
      $CurrentFile = Utils::GetCurrentFileName();
      if ($CurrentFile != "index.php" && $CurrentFile != "handbook.php" && $CurrentFile != "contact.php" && $CurrentFile != "register.php" && $CurrentFile != "transactionconfirm.php" && $CurrentFile != "confirm.php" && $CurrentFile != "search.php")
      {
        header("Location:index.php");
        die;
      }
    }
    echo '<input type="hidden" id="logged-in" value="'.$LoginStatus.'"/>';
  ?>

<script type="text/javascript">
$(function()
{
  $(".modalpopupclose").click(function(event)
  {
    hide_popup("#" + this.id + "-popup");
  });
});

function show_popup(id)
{
  var pt = window.center({ width: $(id).width(), height: $(id).height() });
  $(id).css("top", pt.y + "px");
  $(id).css("left", pt.x + "px");
  $(id + ", #modalpopupbackground").toggleClass("hidden");
}
  
function hide_popup(id)
{
  $(id + ", #modalpopupbackground").toggleClass("hidden");
}

function show_notification(heading, message)
{
  $("#notification").fadeIn(800).delay(5000).fadeOut(800);
  $("#notification").html('<h3>' + heading + '</h3>' + message);
}

function isLoggedIn()
{
  if ($("#logged-in").val() == 'true') { return true; }
  else { return false; }
}
</script>

  <!-- ********************************************************* -->
    <div id="notification" class="no-print" ></div>
    <div id="header-wrapper">
      <div class="5grid-layout">
        <div class="row">
          <div class="12u">
            <header id="header">
              <h1><a href="index.php" class="mobileUI-site-name"><?php echo $APP_NAME; ?></a></h1>
                
              <nav class="mobileUI-site-nav">
                <a href="index.php" id="index">Home</a>
                <?php if ($LoginStatus == "false") echo '<a href="register.php" id="register">Sign Up</a>'; ?>
                <a href="search.php" id="search" >Search</a>
                <?php if ($LoginStatus == "true") echo '<a href="mytimebank.php" id="mytimebank">My Timebank</a>'; ?>
                <?php if ($LoginStatus == "true") echo '<a href="useraccount.php" id="useraccount">My Account</a>'; ?>
                <a href="docs/timebank_handbook.pdf" target="_blank" id="handbook">Handbook</a>
                <a href="contact.php" id="contact">Contact Us</a>
                <?php if ($_SESSION["Role"] == "Admin") echo '<a href="admin.php" id="admin">Admin</a>'; ?>
                <?php if ($LoginStatus == "true") echo '<a href="login.php?Action=logout" id="logout">Logout</a>'; ?>
              </nav>
            </header>
          </div>
        </div>
      </div>
    </div>

        <div id="banner" class="no-print">
        <?php /*if ($LoginStatus == "true") {
          $Db = MySQLConnection::GetInstance();
          $Users = $Db->GetUsers($_SESSION["UserId"]);
          $User = $Users[0];*/
        ?>
          <!--<div id="user-display" style="float:left;padding-right:10px;">Welcome,<br/><b><?php echo $User->FirstName; ?></b></div>-->
        <?php //} ?>
        <!-- php this so it just loops through anything that's in the themes/thumbs folder and displays it automatically -->
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/cringle.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/timebank-clock.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/highstreet.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/stmarks.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/timebank-door.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/station.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/inspire.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/temple.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/timebank-shopping.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/street.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/bluebell.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/market.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/timebank-painting.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/alley.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/highfield.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/baths.jpg" height="50">
            <img src="themes/<?php echo $THEME; ?>/images/thumbs/library.jpg" height="50">
        </div>

    <!-- main page enclosing -->
    <div id="main">
        <div class="5grid-layout">
