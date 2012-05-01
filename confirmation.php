<?php
//*****^^^ This script performs the confirmation function and updates the database. ^^^*****//

//establish a connection
$connectId = mysql_connect("webdb.uvm.edu","pvendevi_admin","bj9GOhOOElyn6d3Z");
if (!$connectId)
  {
  	die('Could not connect: ' . mysql_error());
  }
mysql_select_db("PVENDEVI_Tales", $connectId);

//get confirmationid from page
$id = $_GET["confirmationId"]; 

//query creation for both tables
$searchQuery1 = "SELECT fld_user_confirmationHash FROM table_user WHERE fld_user_confirmationHash = '$id'";

//query the user table
$result1 = mysql_query($searchQuery1) 
	or die ("Unable to retrieve a record from the database " . mysql_error());

//if the query returns a result for table_user, then perform this if statement
if (mysql_num_rows($result1) >= 1)
{	
	//update the user table field called fld_user_confirmed to reflect confirmation
	$sqlUpdate = "UPDATE table_user SET fld_user_confirmed = '1' WHERE fld_user_confirmationHash = '$id'";
	mysql_query($sqlUpdate) 
		or die ("Unable to change the field in the table " . mysql_error());
	
	/*
	print " Table_User \n";	
	$tables1 = mySql_fetch_row($result1);
	foreach($tables1 as $table)
	{
		print $table . "\n";
	}*/
}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>C O N F I R M | T A L E S</title>
		<meta name="generator" content="TextMate http://macromates.com/" />
		<meta name="author" content="Phelan Vendeville" />
		<meta name="description" content="Confirmation page for Tales, final project for CS148." />
	
		<link rel="stylesheet"
	  		href="style.css"
	  		type="text/css" />
		
		<link href='http://fonts.googleapis.com/css?family=Belleza' rel='stylesheet' type='text/css'/><!--google font embedding-->
		
	</head><!--end head -->
	
	<body class="bodyReset">
		<div id="header">
			<div id="title">t &nbsp; &nbsp; a &nbsp; &nbsp; l &nbsp; &nbsp; e &nbsp; &nbsp; s</div><!-- end title-->
		</div><!-- end header -->
		<div id="contentContainer">
			<div id="content">
				<p class="confirmationText">Thank you for confirming. You may now use the site.</p>
				<p class="confirmationText"><a href="logon.php">Log In Here.</a></p>
			</div><!-- end form content-->
		</div><!-- end formContainer -->
	<p class="footer">© Phelan Vendeville</p>
	</body><!-- end body -->
</html>