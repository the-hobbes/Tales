<?
/**
 * saved.php
 * The page that allows users to view all stories, and save/remove favorites.
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
	//******include TableRow class to create table row objects, for use in the display table.******//
    include ("scripts/TableRow.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>B R O W S E | T A L E S</title>
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
					
					<div id="storyBrowser">
						<?					
							/**
							 * showStories()
							 * Function used to display all of the stories in the database.
							 * Writes the framework of the table, then calls writeRows() to populate with database information.
							 */
							function showStories()
							{
								//retrieve all stories
								$sql = "SELECT * FROM table_story";
								$result = mysql_query($sql) or die ("Unable to retrieve a record from the database " . mysql_error());
								
								//make the table, in sections
								writeTableShell();
								writeRows($result);
								closeTableShell();
							}

							showStories();

							/**
							 * writeTableShell()
							 * Function used to create the shell of the table, such as table tags, headings, title, and footer.
							 */
							function writeTableShell()
							{
								echo 
								'
									<table summary="Table pulled from database">
									<caption>Available Stories</caption>
									
									<thead>
										<tr>
											<th scope="col">Story ID</th>
											<th scope="col">Author Name</th>
											<th scope="col">Story Name</th>
										</tr><!-- End column headers -->
									</thead><!-- end header -->

									<tfoot>
										<tr>
											<!--<th scope="row">Footer</th>-->
											<!--<td colspan="2">Footer Data</td>-->
											<td colspan="3"></td>
										</tr><!-- end column footers -->
									</tfoot><!-- end footer -->

									<tbody>
								';
							}

							/**
							 * writeRows()
							 * Function used to write each row of the table, populated with information from the database.
							 * takes in the record of the sql query as an argument.
							 * Objects from class TableRow are used here. An object of this class is created for each row in the result object.
							 * These TableRow objects contain all of the necessary information and formatting for a table row, to display all available
							 * stories and pertienent story data.
							 */
							function writeRows($result)
							{
								$counter = 0;

								//display the story information from the database, using tablerow objects
									while($row = mysql_fetch_array($result)) 
									{
										//create object, instance of table row class
										$instance = new TableRow($row, $counter);
										
										//set variable equal to the result of the writeData() function called from the instance
										$printObject = $instance->writeData();

										//write out that data (a table row)
										echo $printObject;

										//increase counter
										$counter++;

										//destroy object
										unset($instance);
									}
							}

							/**
							 * closeTableShell()
							 * function used to output closing tags of the table.
							 */
							function closeTableShell()
							{
								echo 
								'
									</tbody><!-- end table body -->
									</table><!--end table-->
								';
							}
						?>
					</div><!-- end storyBrowser -->
					

				</div><!-- end mainContent -->
				
				
			</div><!-- end content (general across all pages) -->
		</div><!-- end contentContainer -->
		
		<p class="footer">© Phelan Vendeville</p>
	</body><!-- end body -->
</html>