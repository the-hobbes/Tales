<?
/**
 * editStory.php
 * The page that allows storytellers to edit and delete thier stories.
 * 
 */
session_start(); //start the session

//establish a connection to the database
$connectId = mysql_connect("webdb.uvm.edu","pvendevi_admin","bj9GOhOOElyn6d3Z");
if (!$connectId)
  {
  	die('Could not connect: ' . mysql_error());
  }
mysql_select_db("PVENDEVI_Tales", $connectId);

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

/**
* retrieveRecords()
* retrieve records from the database.
* Uses the passed in story id to retrieve all records specific to that story.
* Then displays them, wrapping the editable regions in tags for jeditable and echoing them out to the page.
*/
function retrieveRecords()
{
	//get story id from page, as passed in
	$id = $_GET["storyId"];

	//craft query for story table
	$sqlStory = "SELECT fld_storyName FROM table_story WHERE pk_story_storyid = '$id'";
	//craft query for the text table
	$sqlText = "SELECT fld_texts_textblock FROM table_texts WHERE fk_story_storyid = '$id'";
	//craft query for the photo table
	$sqlPhoto = "SELECT fld_photos_photopath FROM table_photos WHERE fk_story_storyid = '$id'";

	//run and store queries against table contents
	$storyResult = mysql_query($sqlStory) or die ("Unable to retrieve a storyResult from the database " . mysql_error());
	$textResult = mysql_query($sqlText) or die ("Unable to retrieve a textResult from the database " . mysql_error());
	$photoResult = mysql_query($sqlPhoto) or die ("Unable to retrieve a photoResult from the database " . mysql_error());

	//story title
	$storyArray = mysql_fetch_array($storyResult);
	$storyName = $storyArray[0];

	//create string from storyarray, using character & as a delimeter
	$textString = "";

	while($row = mysql_fetch_array($textResult))
	{
			//echo $row['fld_texts_textblock'] . "&";
			$textString .= $row['fld_texts_textblock'] . "&";
	}

	//create string from photoResult, using character & as a delimeter
	$photoString = "";
	while($row = mysql_fetch_array($photoResult))
	{
		//echo $row['fld_photos_photopath'] . "&";
		$photoString .= $row['fld_photos_photopath'] . "&";
	}

	echo "<p>herp</p>";

	//THIS is what must be output (or at least the format) for the editable regions:
	// <div class ="items">
	// 	<p><span class="editable">First Name: </span><div id="firstName" class ="edit">' . $resultArray[0] . '</div><!-- end firstName --></p>
	// 	<p><span class="editable">Last Name: </span><div id="lastName" class ="edit">' . $resultArray[1] . ' </div><!-- end lastName --></p>
	// 	<p><span class="editable">Email Address: </span><div id="email" class ="edit">' . $resultArray[3] . ' </div><!-- end email --></p>
	// 	<p><span class="editable">Bio: </span><div id="bio" class ="edit_area">' . $resultArray[4] . ' </div><!-- end bio --></p>			
	// </div><!-- end items -->

	//need to use JSON to pass php variables to javascript
	//http://stackoverflow.com/questions/4885737/pass-php-array-to-javascript-function
	//echo '<script type="text/javascript"> storyTitle = new RetrievedStory("'. $storyName . ',' . $textString . ',' . $photoString . '")</script>';
}

/**
 * Handle form submission.
 * Perform the changes to the table as requested.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>C R E A T E | T A L E S</title>
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
		<script src="http://www.appelsiini.net/projects/jeditable/jquery.jeditable.js"></script><!-- jeditable -->
		
		<script type="text/javascript" charset="utf-8">
		/**
		  * document.ready()
		  * using jeditable to edit the database results in place.
		  * essentially treats each region as its own form, then submits to save.php, which performs validations and updates to the database
		  **/
		$(document).ready(function() 
		{    
			//single line area
			$('.edit').editable('scripts/save.php', {
				indicator : 'Saving...',
				tooltip   : 'Click to edit...',
				cancel    : 'Cancel',
				submit    : 'OK'
			});

			//text area edit
			$('.edit_area').editable('scripts/save.php', { 
			     type      : 'textarea',
		         cancel    : 'Cancel',
		         submit    : 'OK',
		         tooltip   : 'Click to edit...'
			     });

		 });
		</script><!-- end jeditable functions. makes elements with edit class editable. Sends form to a php page for server processing-->
	</head>
	<body>
		<div id="header">
			<div id="title">t &nbsp; &nbsp; a &nbsp; &nbsp; l &nbsp; &nbsp; e &nbsp; &nbsp; s</div><!-- end title-->
		</div><!-- end header -->
		<div id="contentContainer">
			<div id="content">
				<div id ="navigation">
					<div id = "profilePicture"><img height="100" width="100" src="<?php echo $img ?>" /></div> <!-- end profilePicture -->
					<ul class="navBar">
						<li class="liFormat"><a href="user.php">Account|</a></li>
						<li class="liFormat"><a href="createStory.php">Stories|</a></li>
						<li class="liFormat"><a href="logon.php?logout=true">Logout</a></li><!--use the logon page to destroy the session--> 				
					</ul><!-- end navBar -->
				</div> <!-- end navigation -->
			
				<div id ="mainContent">
					<ul id = "itemNavigation" style="border:none;">
					<!--
						<li class= "addItem" onclick="kickoff()"><b>Add Story</b></li><br />
						<li class= "addItem" onclick="try{s.makeImage('dynamicInput')}catch(e){}">Add Image</li><br />
						<li class= "addItem" onclick="try{s.makeText('dynamicInput')}catch(e){}">Add Text</li><br />
						<li class= "addItem">Add Music</li><br />
						<li class= "addItem">Add Video</li>
					-->
					</ul><!-- end itemNavigation -->
				
					<div id="storyForm">
						<form action="<? print $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
							<label for="txtTitle">Story Title</label><br />
							<input style="margin-bottom:20px;" type="text" name="txtTitle" id="txtTitle"/><br />
							<div id="dynamicInput"></div><!-- end dynamicInput (under which dynamic content is nested) -->
							<input style="margin-top:10px;"class="inputSubmit" type="submit" name="cmdSubmitted" value="Submit" />
							<input id="btntest" type="button" value="Cancel" onclick="cancelButton()" />						
						</form>
					</div><!-- end storyForm -->

					<div id ="storyBrowser">

						<?php
							//call the function to display the data from the database, formatted in editable regions. 
							retrieveRecords(); 
						?>
						
					</div><!-- end storyBrowser-->
					
				</div><!-- end mainContent -->
				
				
			</div><!-- end content (general across all pages) -->
		</div><!-- end contentContainer -->
		
		<p class="footer">© Phelan Vendeville</p>
	</body><!-- end body -->
</html>