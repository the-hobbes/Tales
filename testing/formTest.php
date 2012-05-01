<?php

//**** TESTING CODE, NOT PRODUCTION *****//

session_start(); //continue the session

//establish a connection
$connectId = mysql_connect("webdb.uvm.edu","pvendevi_admin","bj9GOhOOElyn6d3Z");
if (!$connectId)
  {
  	die('Could not connect: ' . mysql_error());
  }
mysql_select_db("PVENDEVI_Tales", $connectId);

//CHANGE when actually implementing (not hardcoded)
$username = $_SESSION['username'];

$sql = "SELECT fld_user_firstname, fld_user_lastname, fld_user_photoid, fld_user_email, fld_user_biography FROM table_user WHERE pk_user_username = '$username'";

$result = mysql_query($sql)
	or die ("Unable to retrieve a record from the database " . mysql_error());
$fieldArray = mysql_fetch_array($result); //get an array of the values from the database

//$fieldArray = mysql_fetch_assoc($result); //associative array of the values from the database.
/*
foreach($fieldArray as $field)
{
	echo "<p class ='edit'>". $field . "</p>";
}
*/

/**** password section ****/

//grab password values from the submitted form
$oldPassword = $_POST["txtOldPassword"];
$newPassword = $_POST["txtNewPassword"];
$retypePassword = $_POST["txtRetypePassword"];

//create array to hold error messages
$errorMsg = array();

if (isset($_POST["cmdSubmitted"]))
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

/****%%%%% image upload section %%%%%****/

if (isset($_POST["imageSubmit"]))
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
		if (file_exists("upload/" . $_FILES["userfile"]["name"]))
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
		}
	}
	else
	{
		echo "file is not of appropriate type";
	}
}

/**
 * resizeImage()
 * a function to resize the profile image so it will fit in the div
 * takes in the filename of the image to be resized as an argument
 * returns the converted image
 */
function resizeImage($width, $height, $target)
{
	//takes the larger size of the width and height and applies the formula. Your function is designed to work with any image in any size.
	if ($width > $height) 
	{
		$percentage = ($target / $width);
	} 
	else 
	{
		$percentage = ($target / $height);
	}

	//gets the new value and applies the percentage, then rounds the value

	$width = round($width * $percentage);
	$height = round($height * $percentage);
	
	//returns the new sizes in html image tag format...this is so you can plug this function inside an image tag so that it will set the image 
	//to the correct size, without putting a whole script into the tag.
	
	return "width=\"$width\" height=\"$height\"";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>U S E R | T A L E S</title>
		<meta name="generator" content="TextMate http://macromates.com/" />
		<meta name="author" content="Phelan Vendeville" />
		<meta name="description" content="User page for Tales, final project for CS148." />

		<link rel="stylesheet"
	  		href="style.css"
	  		type="text/css" />

		<link href="http://fonts.googleapis.com/css?family=Belleza" rel="stylesheet" type="text/css"><!--google font embedding-->
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
		         indicator : '<img src="img/indicator.gif">',
		         tooltip   : 'Click to edit...'
			     });
		 });
		</script><!-- end jeditable functions. makes elements with edit class editable. Sends form to a php page for server processing-->
		
		
	</head><!--end head -->
	
	<body>
		<div class ="items">

			<p><div id="firstName" class ="edit"><?php echo $fieldArray[0]; ?></div><!-- end firstName --></p>
			<p><div id="lastName" class ="edit"><?php echo $fieldArray[1]; ?></div><!-- end lastName --></p>
	<!--		<p><div id="picture" class ="edit"><?php echo $fieldArray[2]; ?></div></p>			 -->
			<p><div id="email" class ="edit"><?php echo $fieldArray[3]; ?></div><!-- end email --></p>
			<p><div id="bio" class ="edit_area"><?php echo $fieldArray[4]; ?></div><!-- end bio --></p>
			<p>
				<form action="<? print $_SERVER['PHP_SELF']; ?>" method="post">
				<fieldset>
						<label for="txtOldPassword">Current Password:</label><br /><input type="password" name="txtOldPassword" id="txtOldPassword"/><br />
						<label for="txtNewPassword">New Password:</label><br /><input type="password" name="txtNewPassword" id="txtNewPassword"/><br />
						<label for="txtRetypePassword">Confirm Password:</label><br /><input type="password" name="txtRetypePassword" id="txtRetypePassword"/><br />
						<input class="inputSubmit" type="submit" name="cmdSubmitted" value="Submit" />
					</fieldset>
				</form><!-- end password form -->
			</p>
			
	<!--***** PHOTO ID PART *****-->
			<form action="<? print $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
				<label for="file">Filename:</label>
				<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
				<input name="userfile" type="file" id="userfile"> 
				<br />
				<input type="submit" name="imageSubmit" value="Submit" />
			</form>
			
			<p><img src="<?php echo $img; ?>" /></p>
			
	<!--***** END PHOTO ID PART *****-->		
		</div><!-- end items -->

		<div id="errors">
			<?php
				//print out the error message
				echo $message;

				//display the div containing the error messages (setting visibility) if there are any errors
				if ($loaded)
				{
					echo '<script type="text/javascript">document.getElementById("errors").style.display = "block";</script>';
				}	
			?>
		</div><!-- end errors-->		
	</body>
</html>