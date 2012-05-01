<?
/**
 * createStory.php
 * The page that allows storytellers to create new stories.
 * Uses javascript class Story to create a new object of type story, and provides methods by which to modify said object. 
 * Form submits to self for validation before adding story objects to the database.
 *
 * This code also shows the stories the storyteller who is currently logged in has created. This is shown by default, until a new story is created which then
 * overlaps the table listing the stories. 
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
 * if (isset($_POST["cmdSubmitted"]))
 * 
 * is the _POST variable set to a form submission?
 * if so, validate the form data, and update the database.
 * 
 * Creates new story record and adds to table_story (auto generated pk_story_storyid, needs fk_storyteller_username, and fld_storyName)
 * 
 * Then, using the pk of the newly created story table, inserts each text item into the table_texts (auto generated pk_texts_textid,
 * fk_story_storyid, and fld_texts_textblock).
 * 
 * Then, using the pl of the story table again, uploads each file to the uploads/storyPhotos directory, and adds a record into the table_photos 
 * (auto pk_photos_photoid, fk_story_storyid, fld_photos_photopath). Photos are added to an array of files and then iterated through, like
 * the texts.
 */
if (isset($_POST["cmdSubmitted"]))
{
	$username = $_SESSION['username'];
	
	$storyId ="";

	//title
	if(!empty($_POST["txtTitle"]))
	{
		validateTitle($username);
		//get the last record inserted into the database, and use it as the id of the new story
		$storyId = mysql_insert_id();
	}
	
	//text blocks
	if(!empty($_POST["myInputs"]))
	{
		validateText($storyId, $username);
	}

	
	//photos
	if($_FILES['photoFiles']['size'][0] > 1)
	{
		validatePhotos($storyId, $username);
	}
}
/**
 * validateTitle()
 * function to validate the title of the submitted story
 * arguments: username of the user who is logged in.
 */
function validateTitle($username)
{
	$title = $_POST["txtTitle"];
	//sanitize title text input
	if(!get_magic_quotes_gpc())
	{
	    $title = addslashes($title);
	}
	//craft and run query to insert record into the database
	$sql = "INSERT INTO table_story (fk_storyteller_username, fld_storyName) VALUES ('$username', '$title')";
	mysql_query($sql) or die ("Unable to insert a record into the database: " . mysql_error());
}

/**
 * validateText()
 * function to validate the text blocks of the submitted story
 * arguments: $storyId, the auto generated id of the last inserted record
 * 			  $username, the user who is logged in
 */
function validateText($storyId, $username)
{
	//get text blocks
	$arrayTextInputs = $_POST["myInputs"];
	//sanitize text input
	foreach ($arrayTextInputs as $eachInput) 
	{
	    if(!get_magic_quotes_gpc())
		{
		    $eachInput = addslashes($eachInput);
		}
		
		if($eachInput == "")
		{
			$errorMsg[] = "Textbox cannot be blank";
		}
		else
		{
			$sql = "INSERT INTO table_texts (fk_story_storyid, fk_storyteller_username, fld_texts_textblock) VALUES ('$storyId', '$username', '$eachInput')";
			mysql_query($sql) or die ("Unable to insert a textbox into the database: " . mysql_error());
		}
	}
}

/**
 * validatePhotos()
 * function to validate the photo files of the submitted story
 * 
 * arguments: $storyId, the auto generated id of the last inserted record
 * 			  $username, the user who is logged in
 * 
 *  Photos are put into the global array _FILES. This array contains four sub arrays. These sub arrays are:
 * 	name, tmp_name, size, and type. 
 * 	In each of these arrays, there will be as many entries as there were photos uploaded. Therefore, in order to access each photo,
 * 	iteration through these arrays is needed.
 */
function validatePhotos($storyId, $username)
{
	//number of photos submitted
	$index = count($_FILES['photoFiles']['name']);
	
	//get photos
	for($i = 0; $i < $index; $i++) 
	{	
		//print $_FILES['photoFiles']['name'][$i] . "\n";
		
		//process and upload image. Save path to database.
		//set variables from file item taken from form
		$fileName = $_FILES['photoFiles']['name'][$i];
		$tmpName  = $_FILES['photoFiles']['tmp_name'][$i];
		$fileSize = $_FILES['photoFiles']['size'][$i];
		$fileType = $_FILES['photoFiles']['type'][$i];
		
		//open file pointer and read the image content into a variable
		$fp      = fopen($tmpName, 'r');
		$content = fread($fp, filesize($tmpName));
		$content = addslashes($content);
		fclose($fp);

		//in order to reduce filename collisions, create a name for the file based on the current time
		$time = gettimeofday(true);
		$plainFilename = $_FILES['photoFiles']["name"][$i];
		$newFileName = $time.$plainFilename;
		
		//is the filename a duplicate
		if (file_exists("uploads/storyPhotos/" . $newFileName))
		{
		    echo $_FILES['photoFiles']["name"] . " already exists. ";
		}
		//if the name is unique, load it to the directory and insert the path into the database
		else
		{	
			//upload file to directory
			move_uploaded_file($_FILES['photoFiles']["tmp_name"][$i], "uploads/storyPhotos/" . $newFileName);
			
			//create sql
			$filePath = "uploads/storyPhotos/" . $newFileName;

			$sql = "INSERT INTO table_photos (fk_story_storyid, fk_storyteller_username, fld_photos_photopath) VALUES ('$storyId', '$username', '$filePath')";
			mysql_query($sql) or die('Error, photo insert failed: '. mysql_error());
		}	
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
		
		<script type="text/javascript" charset="utf-8">
			/**
			* Class Story (javascript)
			* Supports creation of objects of type story. Includes methods for adding/editing aspects of object type story, such as 
			* text areas, pictures, music, and videos.
			*
			* In JavaScript, the function serves as the constructor of the object; therefore, there is no need to explicitly define a
			* constructor method. Every action declared in the class gets executed at the time of instantiation.
			*
			* https://developer.mozilla.org/en/Introduction_to_Object-Oriented_JavaScript
			*/
			function Story()
			{
				//add story listener
				//creates a form with save and cancel buttons
				//form action submits to self (php) to update database
				
				//show the form
				document.getElementById('storyForm').style.display = "block"; 
				
				//counters for each type of object that may be created
				var textCounter = 0;
				var pictureCounter = 0;
				
				//for ease of use, this object is set equal to s
				var s = this;
				
				/**
				* makeText (public method of Story class)
				* applies to an object of type story (story.makeText(divName))
				* arguments: takes in the name of the div the text will be nested under
				* creates a div box, labeled sequentially, and applies the formatTextbox class for formatting purposes
				* each new textarea input will be stored in the array, myInputs[] so it may be accessed later
				*/
				Story.prototype.makeText = function(divName)  
				{  
					//create new div for textarea
					var newDiv = document.createElement('div');
					//give the new div an id based on its sequence number and a classname for formatting
					newDiv.id = "textBox" + textCounter;
					newDiv.className = "formatTextbox";
					//add a label to the div and designate a textarea for it
					newDiv.innerHTML = "Text Block " + (textCounter + 1) + " <br><textarea name='myInputs[]'>";
					document.getElementById(divName).appendChild(newDiv); //nest the new div under divName
					textCounter++; //increase the sequence counter
					//use scrollTo plugin to scroll to newly added div
					$.scrollTo(newDiv, {duration:1000}); 
				};
				
				/**
				* makeImage (public method of Story class)
				* applies to an object of type story (story.makeImage(divName))
				* arguments: takes in the name of the div the image will be nested under
				* creates a div box with file input for uploading photos for stories
				*/	
				Story.prototype.makeImage = function(divName)  
				{  
					//alert ('photo');
					var photoInstance = "photoInstance";
					
					//create new div for photo upload
					var newDiv = document.createElement('div');
					//give the new div an id based on its sequence number and a classname for formatting
					newDiv.id = "photoBox" + pictureCounter;
					newDiv.className = "formatPhotobox";				
					//add the label and input parameters for the new div
					newDiv.innerHTML = "Photo " + (pictureCounter + 1) + " <br /><input type='hidden' name='MAX_FILE_SIZE' value='2000000'>"
					+ "<input name='photoFiles[]' type='file' />";
					//nest the new div under divName 
					document.getElementById(divName).appendChild(newDiv); 
				  	pictureCounter++;//increase the sequence counter
					//use scrollTo plugin to scroll to newly added div
					$.scrollTo(newDiv, {duration:500});
				};			
				//music click listener
				Story.prototype.makeMusic = function()  
				{  
				  	alert ('text');  
				};
				//video click listener
				Story.prototype.makeVideo = function()  
				{  
				  	alert ('text');  
				};
			}
			
			/**
			* kickoff by making a new story object when clicked
			*/
			function kickoff()
			{
				s = new Story(); //working story object

				//when the new story button is clicked, hide the table displaying the user's stories
				document.getElementById('storyBrowser').style.display = "none"; 
			}
			
			function cancelButton() 
			{
				//window.location.href("www.google.com");
				window.location = 'createStory.php';
			}
		</script>
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
					<ul id = "itemNavigation">
						<li class= "addItem" onclick="kickoff()"><b>Add Story</b></li><br />
						<li class= "addItem" onclick="try{s.makeImage('dynamicInput')}catch(e){}">Add Image</li><br />
						<li class= "addItem" onclick="try{s.makeText('dynamicInput')}catch(e){}">Add Text</li><br />
						<li class= "addItem">Add Music</li><br />
						<li class= "addItem">Add Video</li>
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
						<?	
							//******include TableRow parent class******//
						    include ("scripts/TableRow.php");	

						    /**
						     * Class StoryTableRow
						     * extends parent class TableRow
						     * Used to create instances of objects that are used to write the rows of the storyteller table.
						     */
						    class StoryTableRow extends TableRow
						    {
						    	/**
							     * writeData()
							     * Overrides writeData() function from parent class. 
							     * This version of the function adds the primary key of the story to the href, so it can be passed to the editing page.
							     * Public function used to first format and then print a table row, using the instance of TableRow class.
							     * Called from client code. 
							     */
							    public function writeData()
							    {
							    	//set root hyperlink
							    	$this->hyperlink = "editStory.php?storyId=";
							    	
							    	//declare function variables
							    	$firstCell = "";
							    	$secondCell = "";
							    	$thirdCell = "";

							    	//echo $this->rowNumber;
							    	//echo $this->hyperlink;

							    	//open row tag and setup class based on counter remainder (odd or even)
							    	if((($this->rowNumber) % 2) == 1)
							    		$this->row .= "<tr class = 'odd'>";
							    	else
							    		$this->row .= "<tr class = 'even'>";

							    	//setup cells
							    	$this->firstCell = '<td><a href="' . $this->hyperlink . $this->storyId . '">' . $this->storyId . '</a></td>';
							    	$this->secondCell = '<td><a href="' . $this->hyperlink . $this->storyId . '">' . $this->authorName . '</a></td>';
							    	$this->thirdCell = '<td><a href="' . $this->hyperlink . $this->storyId . '">' . $this->storyName . '</a></td>';

							    	//append rows to row variable
							    	$this->row .= $this->firstCell;
							    	$this->row .= $this->secondCell;
							    	$this->row .= $this->thirdCell;

							    	//close row tag
							    	$this->row .= "</tr>";

							    	//return row variable to caller for printing
							    	return $this->row;

							    	//return $this->rowNumber % 2;
							    	//return $this->rowNumber;
							    }
						    }

							/**
							 * showStories()
							 * Function used to display all of the stories a user has written.
							 * Writes the framework of the table, then calls writeRows() to populate with database information.
							 */
							function showStories()
							{
								$username = $_SESSION['username'];
								//retrieve all stories
								$sql = "SELECT * FROM table_story WHERE fk_storyteller_username = '$username'";
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
									<caption>Your Stories</caption>
									
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
										$instance = new StoryTableRow($row, $counter);
	
										//set a variable equal to the result of the writeData() function called from the instance
										$printObject = $instance->writeData();

										//write out that data in the variable (a table row)
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
					</div><!-- end storyBrowser-->
					
				</div><!-- end mainContent -->
				
				
			</div><!-- end content (general across all pages) -->
		</div><!-- end contentContainer -->
		
		<p class="footer">© Phelan Vendeville</p>
	</body><!-- end body -->
</html>