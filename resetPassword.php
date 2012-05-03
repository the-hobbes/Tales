<?php
//*****^^^ This form sends an email message to the user containing a temporary random password. ^^^*****//

//establish a connection to the database
include ("scripts/connect.php");

/**
 * if the form is submitted, do the following:
 */
if (isset($_POST["cmdSubmitted"]))
{
	//get email from form
	$email = $_POST["txtEmail"];
	
	//create sql statement
	$sql = "SELECT fld_user_email FROM table_user WHERE fld_user_email = '$email'";
	//query the user table
	$result = mysql_query($sql) 
		or die ("Unable to retrieve a record from the database " . mysql_error());
	
	//make the check if there is such a username 
	$numberOfRows = mysql_num_rows($result);
	if ($numberOfRows >= 1)
	{
		//retrieve email from database
		$retrievedArray = mysql_fetch_array($result); //get an array from a resource record
		$retrievedEmail = array_values($retrievedArray); //get the values from the array		
		$verifiedEmail = $retrievedEmail[0]; //set email to the email retrieved
		
		//now the email has been verified as extant, generate the password
		$tempPassword = createRandomPassword(8);
		
		//now we have the password, update the database
		updatePassword($verifiedEmail, $tempPassword);
		
		//now we have updated the database, generate the email and send it
		sendEmail($verifiedEmail, $tempPassword);
	}
	else
	{
		//notify user that so such email address exists
		//$error = "<p class='confirmationText'>that email doesn't exist in our records</p>";
		//printError($error);
	}
}

/**
 * updatePassword
 * Function that updates the database with hash of the new, randomly generated password. 
 * Takes in the password as an argument.
 * Takes in the email as an argument.
 * (note, the password in the database must be the md5 hash of the new password, in order for logon to work)
 */
function updatePassword($email, $password)
{
	//create hash of password
	$dataToHash = $password;
	$hashedPassword = hash('md5', $dataToHash);
	
	//craft sql
	$sql = "UPDATE table_user SET fld_user_password = '$hashedPassword' WHERE fld_user_email = '$email'";
	
	//send sql statement to database
	mysql_query($sql) or die ("Unable to update the password in the database " . mysql_error());
}

/**
 * sendEmail
 * Function used to create and send an email to the user containing a randomly generated password
 * Takes in the password as an argument
 * Takes in the email as an argument.
 */
function sendEmail($email, $password)
{
	/* recipients */
	$to = $email;

    /* subject */
    $subject = "Reset your lost Tales password.";

    //get date and time
    $Todays_Date=strftime("%x");
    $Current_Time=strftime("%X");

    /* message */
    $message = "<html><head><title>Confirmation</title></head><body><p>This password was generated at your request on ";
    $message .= $Todays_Date . ". Please change your password once you have successfully logged on.</p>";
    $message .= "<p>Temporary Password: " . $password . "</p>";
	$message .= "<p><a href='http://www.uvm.edu/~pvendevi/cs148/final/logon.php'>Log on to Tales</a></p>";
	
	/* To send HTML mail, you can set the Content-type header. */
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

	/* additional headers */
    $headers .= "From: Tales, a website by Phelan Vendeville <tales@uvm.edu>\r\n";
	
	// and now mail it
    $blnMail = mail($to, $subject, $message, $headers);

	//now redirect to logon page
	header('Location: logon.php');
}
	
/**
 * createRandomPassword
 * Function used to generate the random temporary password to be emailed to the user.
 * Takes the length of the password as an argument.
 * Returns the n-length, randomly generated password.
 */
function createRandomPassword($length)
{
	$password = "";
	$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ"; //all possible characters for password
	$maxlength = strlen($possible); //length of the string of all possible characters
	
	//cut length down if it is more than the length of the string of all possible characters
	if ($length > $maxlength) 
	{
	      $length = $maxlength;
	}
	
	$currentCharacterNumber = 0; //current number of characters in the password
	
	//add random characters to $password until $length is reached
	while ($currentCharacterNumber < $length) 
	{ 
	    //pick a random character from the possible ones
		$char = substr($possible, mt_rand(0, $maxlength-1), 1);

		//check if we already used this character in the password
		if (!strstr($password, $char)) 
		{ 
			//if not, add it onto the end of the current password
			$password .= $char;
			//increase the counter by one
			$currentCharacterNumber++;
		}
	}
	
	return $password; 
}
	
	

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>R E S E T | T A L E S</title>
		<meta name="generator" content="TextMate http://macromates.com/" />
		<meta name="author" content="Phelan Vendeville" />
		<meta name="description" content="Reset password page for Tales, final project for CS148." />
	
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
				
				<p class="confirmationText">Forgot your password? Enter your email address and follow the instructions sent to you.</p>

				<div id="formData">		
					<form action="<? print $_SERVER['PHP_SELF']; ?>" method="post">  
						<fieldset>
							<label for="txtEmail">Email:</label><br /><input type="text" name="txtEmail" id="txtEmail"/><br />
							<input class="inputSubmit" type="submit" name="cmdSubmitted" value="Submit" />
						</fieldset><!-- end form fieldset-->
					</form><!-- end form-->
				</div><!-- end formdata-->
				
			</div><!-- end form content-->
		</div><!-- end formContainer -->
	<p class="footer">© Phelan Vendeville</p>
	</body><!-- end body -->
</html>