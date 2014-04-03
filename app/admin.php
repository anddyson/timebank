<?php include_once("header.php");
//TEMPORARY HACK
if ($_SESSION["Role"] != "Admin") header('Location:index.php');
else if (Utils::GetCurrentFileName() == "admin.php") header('Location:admin-users.php'); //ANOTHER TEMPORARY HACK
?>

<div class="row">
	<div class="2u" id="account-sidebar">
		<section>
			<ul class="link-list" id="admin-menu">
				<li><a href="admin-users.php" class="button" id="select-users">Manage Users</a></li>
				<li><a href="admin-categories.php" class="button" id="select-categories">Manage Categories</a></li>
				<li><a href="admin-print.php" class="button" id="select-printouts" >Printouts</a></li>
				<li><a href="admin-posts.php" class="button" id="select-posts">Moderate Posts</a></li>
				<li><a href="admin-transactions.php" class="button" id="select-transactions">View Exchanges</a></li>
				<li><a href="admin-logs.php" class="button" id="select-transactions">View Error Logs</a></li>
			</ul>
		</section>
	</div>
	<div class="10u" id="functions">
		<section>
