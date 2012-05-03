<?php
session_start(); //begin logon session

//*****^^^ This script performs the logon function for the site. ^^^*****//
//first, it checks for the existence of the user in the database (searching the user table for the unique username)
//then, if the user exists, it checks to see if md5(password) == stored hash
//if the values are equivalent, the session variable is set to logged on and passed to the next page
//if the values are not, then an error div pops up informing the user of thier error
//should be a link to reset password at the bottom of the page

//establish a connection to the database
include ("scripts/connect.php");

//global variables
$username = "";
$password = "";
$errorMsg = array();

//is the user calling logout from another page?
$logout = $_GET["logout"];
//if the user is logging out, destroy the session
if($logout == true)
{
	session_destroy();
}

//if the form has been submitted, do the following
if (isset($_POST["cmdSubmitted"]))
{
	$username = $_POST["txtUsername"];
	$password = $_POST["txtPassword"];	
	
	//pass in username and password to authentication function, get back error or confimation (confirmation == 1)
	$authenticated = authenticateUser($username, $password);
	
	//if $authenticated != 1, then not authenticated... else, authenticated
	if($authenticated == 1)
	{
		print "successfully authenticated";	
		
		//set logon session variable
		$_SESSION['loggedIn'] = "true";
		//set user session variable
		$_SESSION['username'] = $username;
		//redirect to user page using javascript, as info has been sent to browser and header will not work
		echo "<script>location.href='user.php'</script>";
		//kill session and remove variables
		//session_destroy();
	}
	else
	{
		$loaded = true; //show the error message
	}
		
}

/**
 * authenticateUser
 * function to verify the user and password match and are valid
 * Takes in the username and password as arguments
 * Returns 1 if authentication succeeds
 * returns an error message if authentication fails
 */
function authenticateUser($username, $password)
{	
	//check and see if user has confirmed thier registeration first
	$register = "SELECT fld_user_confirmed FROM table_user WHERE pk_user_username = '$username'";
	$resultRegister = mysql_query($register) 
		or die ("Unable to retrieve a record from the database " . mysql_error());
	$numberOfRowsRegister = mysql_num_rows($resultRegister);
	
	if ($numberOfRowsRegister >= 1)
	{
		$retrievedRegisterArray = mysql_fetch_array($resultRegister);
		$retrievedRegistration = array_values($retrievedRegisterArray);
		if($retrievedRegistration[0] != 1)
		{
			$error = "Please Register First";
			$message = printError($error);
			return $message;
		}

	}
	//create sql statement
	$sql = "SELECT fld_user_password FROM table_user WHERE pk_user_username = '$username'";
	
	//query the user table
	$result = mysql_query($sql) 
		or die ("Unable to retrieve a record from the database " . mysql_error());
	$numberOfRows = mysql_num_rows($result);
	
	//make the check if there is such a username 
	if ($numberOfRows >= 1)
	{	
		//if there is such a username, check the hash value of the password against the stored value
		$dataToHash = $password;
		$hashedPassword = hash('md5', $dataToHash); //generate password
		
		$retrievedArray = mysql_fetch_array($result); //get an array from a resource record
		$retrievedPassword = array_values($retrievedArray); //get the values from the array
		
		//echo "stored hash: " . $retrievedPassword[0] . "\n";
		//echo "generated hash: " . $hashedPassword;

		if($hashedPassword == $retrievedPassword[0])
		{
			//echo "passwords match";
			//TODO: NEED TO CHECK CONFIRMATION HERE AS WELL
			return 1;
		}
		else
		{
			//echo "password do not match";
			$error = "Incorrect Password";
			$message = printError($error);
			return $message;
		}
	}
	//if there isn't a username that matches
	else
	{
		//echo "no such username";
		$error = "No such username found";
		$message = printError($error);
		return $message;
	}
	
	return 0;
}

/**
 * printError
 * function to display any errors generated during authentications.
 * Takes in the error as an argument.
 * Returns the formatted message.
 */
function printError($error)
{
	//if there are errors, display them
	//echo $error;
	
	//if there is an error, then set $loaded to true so it can be used further down
	$authenticated .= "<li style='color: #ff6666'>" . $error . "</li>\n";
	
	return($authenticated);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>L O G O N | T A L E S</title>
		<meta name="generator" content="TextMate http://macromates.com/" />
		<meta name="author" content="Phelan Vendeville" />
		<meta name="description" content="Logon page for Tales, final project for CS148." />
	
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
				
				<div id="formData">		
					<form action="<? print $_SERVER['PHP_SELF']; ?>" method="post">  
						<fieldset>
							<label for="txtUsername">Username:</label><br /><input type="text" name="txtUsername" id="txtUsername" <? print "value='$username'"; ?>/><br />
							<label for="txtPassword">Password:</label><br /><input type="password" name="txtPassword" id="txtPassword"/><br />
							<input class="inputSubmit" type="submit" name="cmdSubmitted" value="Submit" />
						</fieldset><!-- end form fieldset-->
					</form><!-- end form-->
					<p><a href="resetPassword.php">Forgot Password?</a></p>
				</div><!-- end formdata-->
				
			</div><!-- end form content-->
		</div><!-- end formContainer -->
		<div id="errors">
			<?php
				echo $authenticated;

				//display the div containing the error messages (setting visibility) if there are any errors
				if ($loaded)
				{
					echo '<script type="text/javascript">document.getElementById("errors").style.display = "block";</script>';
				}	
				//echo "</ul>";
			?>
		</div><!-- end errors-->
	<p class="footer">© Phelan Vendeville</p>
	</body><!-- end body -->
</html>