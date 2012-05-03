<?
/**
 * editStory.php
 * The page that allows storytellers to edit and delete thier stories.
 * 
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
 * getStoryId()
 * function used to get the pk of the story you wish to edit.
 * this is retrieved from the POST variable passed into the page by the calling code in createStory.php
 */
function getStoryId()
{
	$id = $_POST["storyId"];
	return $id;
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
	
	$id = $_GET["storyId"];

	//craft query for story table
	$sqlStory = "SELECT fld_storyName FROM table_story WHERE pk_story_storyid = '$id'";
	//craft query for the text table
	$sqlText = "SELECT * FROM table_texts WHERE fk_story_storyid = '$id'";
	//craft query for the photo table
	$sqlPhoto = "SELECT * FROM table_photos WHERE fk_story_storyid = '$id'";

	//run and store queries against table contents
	$storyResult = mysql_query($sqlStory) or die ("Unable to retrieve a storyResult from the database " . mysql_error());
	$textResult = mysql_query($sqlText) or die ("Unable to retrieve a textResult from the database " . mysql_error());
	$photoResult = mysql_query($sqlPhoto) or die ("Unable to retrieve a photoResult from the database " . mysql_error());

	//story title
	$storyArray = mysql_fetch_array($storyResult);
	$storyName = $storyArray[0];

	//call function to display text blocks found
	if($textResult)
		displayTexts($textResult);	

	//call function to display photos that correspond to the story here
	if($photoResult)
		displayPhotos($photoResult);

}

/**
 * displayTexts()
 * function used to display each text block found in the story, wrapped in editable tags so it can be worked on by jeditable and updateTexts.php
 * arguments: $textResult, the resulting array from the query
 */
function displayTexts($textResult)
{

	while($row = mysql_fetch_array($textResult))
	{
		echo '<p><span class="editable">Text Block: </span><div id='. '"' . $row['pk_texts_textid'] . '"' . ' class ="edit_area">' . $row['fld_texts_textblock'] . '</div><!-- end firstName --></p>';
	}
}

/**
 * displayPhotos()
 * function used to display each photo found in the database corresponding to the story.
 * adds a form element and photograph for each found in database.
 * submits to the page to perform the updates, one form for each photo
 */
function displayPhotos($photoResult)
{
	while($row = mysql_fetch_array($photoResult))
	{
		//note that the hidden text field here is used pass the pk of the photo into the _POST['photoId'] variable
		//and the second is used to pass in the id of the story to which the photo belongs
		echo '<div style="color:red; border:1px dashed black; margin-bottom:5px;">';
		echo
		'
			<form action="'. $_SERVER["PHP_SELF"] .'" method="post"enctype="multipart/form-data">
				<label class = "editable" for="file">New Photo:</label>
				<br />
				<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
				<input name="userfile" type="file" id="userfile"> 
				<br />
				<input style="display:none;" type="text" name="photoId" value="'. $row['pk_photos_photoid'] .'"/>
				<input style="display:none;" type="text" name="storyId" value="'. $row['fk_story_storyid'] .'"/>
				<input type="submit" name="imageSubmit" value="Submit" />
			</form>
		';

		echo '<p>Current Photo:</p>';
		echo '<img height="100" width="100" src="'. $row['fld_photos_photopath'] .'">';
		echo '</div>';
	}
}


/**
 * Handle form submission.
 * Perform the changes to the table in the database as requested.
 * Redirects the page to itself in order to refresh pictures.
 */
if (isset($_POST["imageSubmit"]))//image upload form submitted
{	
	//check to see if the file is of the appropriate type, and is greater than 0 bytes
	if (($_FILES["userfile"]["type"] == "image/gif") 
	|| ($_FILES["userfile"]["type"] == "image/jpeg") 
	|| ($_FILES["userfile"]["type"] == "image/pjpeg")
	&& ($_FILES['userfile']['size'] > 0))
	{
		//set variables from form
		$fileName = $_FILES['userfile']['name'];
		$tmpName  = $_FILES['userfile']['tmp_name'];
		$fileSize = $_FILES['userfile']['size'];
		$fileType = $_FILES['userfile']['type'];
		
		//open file pointer and read the image content into a temporary variable
		$fp      = fopen($tmpName, 'r');
		$content = fread($fp, filesize($tmpName));
		$content = addslashes($content);
		fclose($fp);

		//in order to reduce filename collisions, create a name for the file based on the current time
		$time = gettimeofday(true);
		$plainFilename = $fileName;
		$newFileName = $time.$plainFilename;

		//is the filename a duplicate
		if (file_exists("uploads/storyPhotos/" . $newFileName))
		{
		    echo $newFileName . " already exists. ";
		}
		//if the name is unique, load it to the directory and insert the path into the database
		else
		{		
			//upload file to directory
			move_uploaded_file($_FILES["userfile"]["tmp_name"], "uploads/storyPhotos/" . $newFileName);

			//create sql
			$filePath = "uploads/storyPhotos/" . $newFileName;
			$id = $_POST["photoId"];

			$sql = "UPDATE table_photos SET fld_photos_photopath = '$filePath' WHERE pk_photos_photoid = '$id'";
			//send sql statement to database
			mysql_query($sql) or die ("Unable to update the record in the database " . mysql_error()); 

			//refresh page so the user can see thier new photo
			//this is done using the POST variable containing the pk of the story
			$id = getStoryId();
			$redirectUrl = 'Location: editStory.php?storyId=' . $id;
			header($redirectUrl);
		}
	}
	else
	{
		echo "file is not of appropriate type";
	}
}
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
			$('.edit').editable('scripts/updateTexts.php', {
				indicator : 'Saving...',
				tooltip   : 'Click to edit...',
				cancel    : 'Cancel',
				submit    : 'OK'
			});

			//text area edit
			$('.edit_area').editable('scripts/updateTexts.php', { 
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

						<div class="items" style="height:auto;">
							<?php
								//call the function to display the data from the database, formatted in editable regions. 
								retrieveRecords(); 
							?>
						</div><!-- end items -->

					</div><!-- end storyBrowser-->
					
				</div><!-- end mainContent -->
				
				
			</div><!-- end content (general across all pages) -->
		</div><!-- end contentContainer -->
		
		<p class="footer">© Phelan Vendeville</p>
	</body><!-- end body -->
</html>