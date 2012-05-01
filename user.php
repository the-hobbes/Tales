<?php

/**
 * This is the code that creates the user and storyteller account pages.
 * The page detects whether the user is logged in or not via the session variable 'loggedIn', and redirects the user to the logon page
 * if the user is not.
 * It then creates the header.
 * The content area is created based on the user type, either (either user or storyteller).
 * The content area contains navigation and content parts of the page, including the account modification editable regions.
 * The footer is then created.
 */

 	session_start(); //start the session

	//establish a connection
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

			//load page according to username. call functions to create page parts here.
			writeHeader();
			writeContent($username);
			writeFooter();
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
	
/**** %%%%%%%%% password reset section %%%%%%%%% ****/

	//grab password values from the submitted form
	$oldPassword = $_POST["txtOldPassword"];
	$newPassword = $_POST["txtNewPassword"];
	$retypePassword = $_POST["txtRetypePassword"];

	//create array to hold error messages
	$errorMsg = array();

	if (isset($_POST["cmdSubmitted"])) //password reset form submitted
	{
		//if the form has been submitted, so the following
		if($oldPassword=="")
		{
	        $errorMsg[]="Please enter your current password.";
	    }
		else //the old password field is filled
		{
			//create sql statement
			$sql = "SELECT fld_user_password FROM table_user WHERE pk_user_username = '$username'";
			//query the user table
			$result = mysql_query($sql) 
				or die ("Unable to retrieve a record from the database " . mysql_error());
			//put record into an array
			$passwordArray = mysql_fetch_array($result);
			//hash the entered password
			$hashedPassword = hash('md5', $oldPassword);

			if($passwordArray[0] == $hashedPassword) //check for match of original password and database password
			{
				//echo "thats it, you got it";

				if($newPassword=="")//then check for existence of new password
				{
					$errorMsg[]="Please enter your new password.";
				}
				else
				{
					if($retypePassword=="")//check for existence of confirmed new password
					{
						$errorMsg[]="Please enter password confirmation.";
					}
					else//if they are all filled
					{
						if($newPassword != $retypePassword)//do the new passwords match?
						{
							$errorMsg[]="Passwords do not match";
						}
						else//perform the password change
						{
							//hash the new password
							$newPassword = hash('md5', $newPassword);
							//create sql statement
							$sql = "UPDATE table_user SET fld_user_password = '$newPassword' WHERE pk_user_username = '$username'";
							//send sql statement to database
							mysql_query($sql) or die ("Unable to update the email address in the database " . mysql_error());

							//display a success message
							$loaded = true;
							$msg = "password successfully changed";
							$message = "<li style='color: #1B60E0'>". $msg . "</li>\n";
						}
					}
				}
			}
			else//the original password and stored password aren't the same
			{
				$errorMsg[]="Your current password is incorrect.";
			}
		}

		if($errorMsg)
		{
			//if there is an error, then set $loaded to true so it can be used further down
			$loaded = true;
			//message is the combined error messages
			$message = "";
	        foreach($errorMsg as $err)
			{
				$message .= "<li style='color: #ff6666'>" . $err . "</li>\n";
	        }
	    }
	}

/**** %%%%%%%%% END password section %%%%%%%%% ****/

/****%%%%% image upload section %%%%%****/
if (isset($_POST["imageSubmit"]))//image upload form submitted
{	
	//check to see if the file is of the appropriate type, and is greater than 0 bytes
	if (($_FILES["userfile"]["type"] == "image/gif") 
	|| ($_FILES["userfile"]["type"] == "image/jpeg") 
	|| ($_FILES["userfile"]["type"] == "image/pjpeg")
	&& ($_FILES['userfile']['size'] > 0))
	{
		echo "file is of appropriate type ";
		
		//set variables from form
		$fileName = $_FILES['userfile']['name'];
		$tmpName  = $_FILES['userfile']['tmp_name'];
		$fileSize = $_FILES['userfile']['size'];
		$fileType = $_FILES['userfile']['type'];
		
		//open file pointer and read the image content into a variable
		$fp      = fopen($tmpName, 'r');
		$content = fread($fp, filesize($tmpName));
		$content = addslashes($content);
		fclose($fp);
		//if magic quotes
		if(!get_magic_quotes_gpc())
		{
		    $fileName = addslashes($fileName);
		}

		//is the filename a duplicate
		if (file_exists("uploads/" . $_FILES["userfile"]["name"]))
		{
		    echo $_FILES["userfile"]["name"] . " already exists. ";
		}
		//if the name is unique, load it to the directory and insert the path into the database
		else
		{
			//resize temporary image
			//resizeImage();
			
			//upload file to directory
			move_uploaded_file($_FILES["userfile"]["tmp_name"], "uploads/" . $_FILES["userfile"]["name"]);

			//create sql
			$filePath = "uploads/" . $_FILES["userfile"]["name"];
			$query = "UPDATE table_user SET fld_user_photoid = '$filePath' WHERE pk_user_username = '$username'";
			mysql_query($query) or die('Error, query failed: '. mysql_error());
						
			//retrieve the image to display it
			//create sql statement
			$sql = "SELECT fld_user_photoid FROM table_user WHERE pk_user_username = '$username'";
			//query the user table
			$result = mysql_query($sql) 
				or die ("Unable to retrieve a record from the database " . mysql_error());
			//put record into an array
			$photoArray = mysql_fetch_array($result);

			$img = $photoArray[0];
			
			//refresh page so the user can see thier new id
			echo '<script type="text/javascript" charset="utf-8">location.reload(true)</script>';
		}
	}
	else
	{
		echo "file is not of appropriate type";
	}
}
/****%%%%% END image upload section %%%%%****/

/**
 * writeHeader()
 * function to write the header portion of the page.
 * includes css and jquery references.
 * necessitates use of escape character (\) for single quote elements
 */
function writeHeader()
{
	echo
	'
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

					<title>A C C O U N T | T A L E S</title>
					<meta name="generator" content="TextMate http://macromates.com/" />
					<meta name="author" content="Phelan Vendeville" />
					<meta name="description" content="User page for Tales, final project for CS148." />

					<link rel="stylesheet"
				  		href="style.css"
				  		type="text/css" />

					<link href="http://fonts.googleapis.com/css?family=Belleza" rel="stylesheet" type="text/css"/><!--google font embedding-->
					<script 
						src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript" charset="utf-8">
					</script><!-- jquery-->

					<script
						src="http://www.appelsiini.net/projects/jeditable/jquery.jeditable.js">
					</script><!-- jeditable -->

					<script type="text/javascript" charset="utf-8">
					//using jeditable to edit the database results in place.
					//essentially treats each region as its own form, then submits to save.php, which performs validations and updates to the database

					$(document).ready(function() 
					{    
						//single line area
						$(\'.edit\').editable(\'scripts/save.php\', {
							indicator : \'Saving...\',
							tooltip   : \'Click to edit...\',
							cancel    : \'Cancel\',
							submit    : \'OK\'
						});

						//text area edit
						$(\'.edit_area\').editable(\'scripts/save.php\', { 
						     type      : \'textarea\',
					         cancel    : \'Cancel\',
					         submit    : \'OK\',
					         //indicator : \'<img src="img/indicator.gif">\',
					         tooltip   : \'Click to edit...\'
						     });

					 });
					</script><!-- end jeditable functions. makes elements with edit class editable. Sends form to a php page for server processing-->
				</head><!--end head -->
	';//end echo
}//end writeHeader
	
/**
 * writeContent()
 * function used to create the content area of the page.
 * takes in the username of the logged in user as an argument
 */
function writeContent($user)
{
	$server = $_SERVER['PHP_SELF'];
	echo 
		'
		<body class="bodyReset">
			<div id="header">
				<div id="title">t &nbsp; &nbsp; a &nbsp; &nbsp; l &nbsp; &nbsp; e &nbsp; &nbsp; s</div><!-- end title-->
			</div><!-- end header -->
		
		<div id="contentContainer">
			<div id="content">
			
				<!-- <p style="text-align: center;"> '. $user . ' you are logged in. Sweet.</p> -->
				
				
				<!-- User Navigation -->	
		';
				
		//**check the type of account, and call the appropriate function to display content**/
			$sql = "SELECT fld_user_adminlevel FROM table_user WHERE pk_user_username = '$user'";
			$result = mysql_query($sql)
				or die ("Unable to retrieve a record from the database " . mysql_error());
			$retrievedArray = mysql_fetch_array($result); //get an array from a resource record
			$retrievedType = array_values($retrievedArray); //get the values from the array
			$accountType = $retrievedType[0]; //1 = user, 2 = storyteller
		
			if($accountType == 1)
			{
				userNav($user);
			}
			elseif($accountType == 2)
			{
				storytellerNav($user);
			}
			else
				echo "problem with account type";
		//**end account type checking and selection **//
		
		
		
	echo'		
			</div><!-- end content-->
		</div><!-- end contentContainer -->
		';
}//end writeContent

/**
 * userNav()
 * used to create the navigation for a user account
 * takes in as an argument the user generating the session.
 */
function userNav($user)
{		
	$img = displayImage($user);
	
	echo 
	'
		<div id ="userNavigation">
			<div id = "profilePicture"><img height="100" width="100" src="'. $img . '" /></div> <!-- end profilePicture -->
			<ul class="navBar">
				<li class="liFormat"><a href="user.php">Account|</a></li>
				<li class="liFormat"><a href="saved.php">Saved|</a></li>
				<li class="liFormat"><a href="browse.php">Browse|</a></li>
				<li class="liFormat"><a href="logon.php?logout=true">Logout</a></li><!--use the logon page to destroy the session-->
			</ul><!-- end navBar -->
		</div> <!-- end navigation -->
	';
	
	userForm($user);
}//end usernav

/**
 * storytellerNav()
 * used to create the navigation for a storyteller account
 * takes in as an argument the user generating the session.
 */
function storytellerNav($user)
{
	$img = displayImage($user);
	echo 
	'
		<div id ="navigation">
			<div id = "profilePicture"><img height="100" width="100" src="'. $img . '" /></div> <!-- end profilePicture -->
			<ul class="navBar">
				<li class="liFormat"><a href="user.php">Account|</a></li>
				<li class="liFormat"><a href="createStory.php">Stories|</a></li>
				<li class="liFormat"><a href="logon.php?logout=true">Logout</a></li><!--use the logon page to destroy the session--> 				
			</ul><!-- end navBar -->
		</div> <!-- end navigation -->
	';
	
	storytellerForm($user);
}//end storyteller nav

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
 * writeFooter()
 * function used to write the footer and closing information of the page
 */
function writeFooter()
{
	echo 
		'
			<p class="footer">© Phelan Vendeville</p>
			</body><!-- end body -->
			</html>
		';
}//end writefooter

/**
 * userForm()
 * function to create the form for a user account. Called from userNav()
 * This function writes out the fields we wish the user to be able to modify in the database.
 * We use the jquery library 'jeditable' to perform in-page editing. Each clickable area is a form, which submits to save.php
 * save.php performs validation and updates. 
 * 
 * Takes in as an argument the user generating the session, to be passed to the sql crafting function
 * (note that the user and storyteller form functions are separate in case more specific functionality needs to be added to either account)
 */
function userForm($user)
{
	$resultArray = craftSql($user); //get array of user editable regions from database
	
	echo
	'
		<div id ="dataBox">
			<div class ="items">
				<p><span class="editable">First Name: </span><div id="firstName" class ="edit">' . $resultArray[0] . '</div><!-- end firstName --></p>
				<p><span class="editable">Last Name: </span><div id="lastName" class ="edit">' . $resultArray[1] . ' </div><!-- end lastName --></p>
				<p><span class="editable">Email Address: </span><div id="email" class ="edit">' . $resultArray[3] . ' </div><!-- end email --></p>
				<p><span class="editable">Bio: </span><div id="bio" class ="edit_area">' . $resultArray[4] . ' </div><!-- end bio --></p>
				
				<!--***** Reset Password Part *****-->
				<p>
					<form action="'. $_SERVER["PHP_SELF"] .'" method="post">
					<fieldset>
							<label class = "editable" for="file">Reset Password:</label><br />
							<label for="txtOldPassword">Current Password:</label><br /><input type="password" name="txtOldPassword" id="txtOldPassword"/><br />
							<label for="txtNewPassword">New Password:</label><br /><input type="password" name="txtNewPassword" id="txtNewPassword"/><br />
							<label for="txtRetypePassword">Confirm Password:</label><br /><input type="password" name="txtRetypePassword" id="txtRetypePassword"/><br />
							<input class="inputSubmit" type="submit" name="cmdSubmitted" value="Submit" />
						</fieldset>
					</form><!-- end password form -->
				</p>
				<!--***** END Reset Password Part *****-->
				
				<!--***** Photo ID Part *****-->
				<form action="'. $_SERVER["PHP_SELF"] .'" method="post"enctype="multipart/form-data">
					<label class = "editable" for="file">Profile Picture:</label>
					<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
					<input name="userfile" type="file" id="userfile"> 
					<br />
					<input type="submit" name="imageSubmit" value="Submit" />
				</form>
				<!--***** END Photo ID Part *****-->	
			
			</div><!-- end items -->
			
			<div id="errors">
				<?php
					//print out the error message
					echo $message;

					//display the div containing the error messages (setting visibility) if there are any errors
					if ($loaded)
					{
						echo \'<script type="text/javascript">document.getElementById("errors").style.display = "block";</script>\';
					}	
				?>
			</div><!-- end errors-->
			
		</div><!-- end dataBox -->
	';
	
	//****PASSWORD RESET IS NEXT****//
}//end userform

/**
 * storytellerForm()
 * function to create the form for a storyteller account, called from storytellerNav()
 * This function writes out the fields we wish the user to be able to modify in the database.
 * We use the jquery library 'jeditable' to perform in-page editing. Each clickable area is a form, which submits to save.php
 * save.php performs validation and updates.  
 * 
 * Takes in as an argument the user generating the session, to be passed to the sql crafting function
 * (note that the user and storyteller form functions are separate in case more specific functionality needs to be added to either account)
 */
function storytellerForm($user)
{
	$resultArray = craftSql($user);//get array of user editable regions from database
		
	echo
	'
		<div id ="dataBox">
			<div class ="items">
				<p><span class="editable">First Name: </span><div id="firstName" class ="edit">' . $resultArray[0] . '</div><!-- end firstName --></p>
				<p><span class="editable">Last Name: </span><div id="lastName" class ="edit">' . $resultArray[1] . ' </div><!-- end lastName --></p>
				<p><span class="editable">Email Address: </span><div id="email" class ="edit">' . $resultArray[3] . ' </div><!-- end email --></p>
				<p><span class="editable">Bio: </span><div id="bio" class ="edit_area">' . $resultArray[4] . ' </div><!-- end bio --></p>
				
				<!--***** Reset Password Part *****-->
				<p>
					<form action="'. $_SERVER["PHP_SELF"] .'" method="post">
					<fieldset>
							<label class = "editable" for="file">Reset Password:</label><br />
							<label for="txtOldPassword">Current Password:</label><br /><input type="password" name="txtOldPassword" id="txtOldPassword"/><br />
							<label for="txtNewPassword">New Password:</label><br /><input type="password" name="txtNewPassword" id="txtNewPassword"/><br />
							<label for="txtRetypePassword">Confirm Password:</label><br /><input type="password" name="txtRetypePassword" id="txtRetypePassword"/><br />
							<input class="inputSubmit" type="submit" name="cmdSubmitted" value="Submit" />
						</fieldset>
					</form><!-- end password form -->
				</p>
				<!--***** END Reset Password Part *****-->
				
				<!--***** Photo ID Part *****-->
				<form action="'. $_SERVER["PHP_SELF"] .'" method="post"enctype="multipart/form-data">
					<label class = "editable" for="file">Profile Picture:</label>
					<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
					<input name="userfile" type="file" id="userfile"> 
					<br />
					<input type="submit" name="imageSubmit" value="Submit" />
				</form>
				<!--***** END Photo ID Part *****-->	
			
			</div><!-- end items -->
			
			<div id="errors">
				<?php
					//print out the error message
					echo $message;

					//display the div containing the error messages (setting visibility) if there are any errors
					if ($loaded)
					{
						echo "<script type="text/javascript">document.getElementById("errors").style.display = "block";</script>";
					}	
				?>
			</div><!-- end errors-->
			
		</div><!-- end dataBox -->
	';
	
	//****PASSWORD RESET IS NEXT****//
}//end storyteller form

/**
 * craftSql()
 * function to craft and run the sql statements necessary to retrieve the user editable regions for use in the userForm and storytellerForm
 * functions.
 * 
 * Takes in as an argument the user generating the session so that the appropriate fields may be retrieved from the user table in the 
 * database.
 * Returns an array containing the values returned from the sql query.
 */
function craftSql($user)
{
	$sql = "SELECT fld_user_firstname, fld_user_lastname, fld_user_photoid, fld_user_email, fld_user_biography FROM table_user WHERE pk_user_username = '$user'";

	$result = mysql_query($sql)
		or die ("Unable to retrieve a record from the database " . mysql_error());
		
	$fieldArray = mysql_fetch_array($result); //get an array of the values from the database

	return $fieldArray;
}//end craftsql
?>