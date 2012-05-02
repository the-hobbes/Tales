<?
/**
 * saved.php
 * The page that allows users to view thier saved stories.
 */
session_start(); //start the session

//establish a connection to the database
include ("scripts/connect.php");

//if the session variable is set, proceed
if(isset($_SESSION['loggedIn']))
{
	//if the session variable is true...
	if($_SESSION['loggedIn'] == 'true')
	{
		$username = $_SESSION['username'];
		$img = displayImage($username); //display the profile picture
	}
	//if the session variable is not true, redirect to the logon page
	else
	{
		header('Location: logon.php');
	}
}
//if the session variable is not set, redirect to logon page
else
{
	header('Location: logon.php');
}

/**
 * displayImage()
 * function to retrieve the profile image for display
 * takes in the logged on user as an argument
 * returns the image path
 */
function displayImage($user)
{
	//retrieve the image to display it
	//create sql statement
	$sql = "SELECT fld_user_photoid FROM table_user WHERE pk_user_username = '$user'";
	//query the user table
	$result = mysql_query($sql) 
		or die ("Unable to retrieve a record from the database " . mysql_error());
	//put record into an array
	$photoArray = mysql_fetch_array($result);

	$img = $photoArray[0];
		
	return $img;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>S A V E D | T A L E S</title>
		<meta name="generator" content="TextMate http://macromates.com/" />
		<meta name="author" content="Phelan Vendeville" />
		<meta name="description" content="Logon page for Tales, final project for CS148." />
	
		<link rel="stylesheet"
	 		href="style.css"
	 		type="text/css" />
		
		<link href='http://fonts.googleapis.com/css?family=Belleza' rel='stylesheet' type='text/css'/><!--google font embedding-->
		<!-- include jquery and jquery easing libraries-->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="http://gsgd.co.uk/sandbox/jquery/easing/jquery.easing.1.3.js" type="text/javascript" charset="utf-8"></script>
		<script src="scripts/jquery.scrollTo-1.4.2-min.js" type="text/javascript" charset="utf-8"></script>
	</head>

	<body>
		<div id="header">
			<div id="title">t &nbsp; &nbsp; a &nbsp; &nbsp; l &nbsp; &nbsp; e &nbsp; &nbsp; s</div><!-- end title-->
		</div><!-- end header -->
		<div id="contentContainer">
			<div id="content">
				<div id ="savedNavigation">
					<div id = "profilePicture"><img height="100" width="100" src="<?php echo $img ?>" /></div> <!-- end profilePicture -->
					<ul class="navBar">
						<li class="liFormat"><a href="user.php">Account|</a></li>
						<li class="liFormat"><a href="saved.php">Saved|</a></li>
						<li class="liFormat"><a href="browse.php">Browse|</a></li>
						<li class="liFormat"><a href="logon.php?logout=true">Logout</a></li>
					</ul><!-- end navBar -->
				</div> <!-- end navigation -->
				<div class="spacer"></div><!-- spacer to shove the border under the picture -->

				<div id ="mainContent">
					

					

				</div><!-- end mainContent -->
				
				
			</div><!-- end content (general across all pages) -->
		</div><!-- end contentContainer -->
		
		<p class="footer">© Phelan Vendeville</p>
	</body><!-- end body -->
</html>