<?php include_once("components_include.php");

$TrnUuid = $_GET["t"];
$UsrUuid = $_GET["u"];

if ($TrnUuid != "")
{
	$db = MySQLConnection::GetInstance();
	$Result = $db->ConfirmTransaction(null, null, $TrnUuid);
	$Result = 1; //UPDATE THIS WHEN PROPER DB EXCEPTION HANDLING HAS BEEN ADDED
}
else if ($UsrUuid != "")
{
	$db = MySQLConnection::GetInstance();
	$Output = $db->ActivateUser($UsrUuid);
	$Usr = $Output->data[0];
	
    $Email = new Email();
    $Email->from = "help@levytimebank.org.uk";
    $Email->to = "help@levytimebank.org.uk";
    $Email->subject = "[Levy Timebank] New User Registration";
    $Email->body = "For information:<br><br>A new user has registered and activated their account on the Timebank.<br><br>The new user is: ".$Usr->FirstName." ".$Usr->LastName." (".$Usr->Username.").";
    $Email->Send();

	$Result = 2; //UPDATE THIS WHEN PROPER DB EXCEPTION HANDLING HAS BEEN ADDED
}
else
{
	$Result = 0;
}

include_once("header.php");
?>

</script>

<div class="row">
	<div class="12u">
		<section>
			<div id="confirm-exchange-success" <?php if ($Result != 1) echo 'class="hidden"'; ?> >
				<h2>Exchange Confirmed</h2>
				The exchange has been confirmed and your account has been updated - thanks!
				<br/><br/>
				<a href="index.php">Click here to go to the homepage and log in</a>
			</div>
			<div id="confirm-user-success" <?php if ($Result != 2) echo 'class="hidden"'; ?> >
				<h2>Account Activation Confirmed</h2>
				Your account has been activated - thanks!
				<br/><br/>
				<a href="index.php">Click here to go to the homepage and log in</a>
			</div>
			<div id="confirm-fail" <?php if ($Result != 0) echo 'class="hidden"'; ?>>
				<h2>Error</h2>
				Sorry, you've received this message because there seems to have been a problem: No valid confirmation ID was provided.
			</div>
		</section>
	</div>
</div>
<?php include_once("footer.php"); ?>

