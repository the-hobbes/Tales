<?php
//*****^^^ This script is depricated. register.php contains all of this data. ^^^*****//


$connectId = mysql_connect("webdb.uvm.edu","pvendevi_admin","bj9GOhOOElyn6d3Z");
if (!$connectId)
  {
  	die('Could not connect: ' . mysql_error());
  }
mysql_select_db("PVENDEVI_Tales", $connectId);

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
// initialize my global variables
//
$username="";
$firstName="";
$lastName = "";
$email="";
$password="";
$retypedPassword="";
$selectedAccountType="";
$accountType="";

//preset the page loaded to false (this is for the display of the error messages)
$loaded = false;
//are we debugging?
$debug = false;

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
// if form has been submitted, validate the information
if (isset($_POST["cmdSubmitted"])){
    // initialize my variables to the forms posting  
    $username = $_POST["txtUsername"];  
    $firstName = $_POST["txtFirstName"];
    $lastName = $_POST["txtLastName"];
    $email = $_POST["txtEmail"];
    $password = $_POST["txtPassword"];
	$retypedPassword = $_POST["txtRetypedPassword"];
	$selectedAccountType = $_POST["userType"];
	//print $selectedAccountType;
	
	//take care of any html characters including quotes (double and single)
    $username = htmlentities($username, ENT_QUOTES);
    $firstName = htmlentities($firstName, ENT_QUOTES);
    $lastName = htmlentities($lastName, ENT_QUOTES);
    $email = htmlentities($email, ENT_QUOTES);
    $password = htmlentities($password, ENT_QUOTES);
    $retypedPassword = htmlentities($retypedPassword, ENT_QUOTES);

    include ("scripts/validation_functions.php");
    $errorMsg=array();

	//hash password
	/* hash for url generation (combination of username and zipcode)*/
	$dataToHash = $password;
	$hashedPassword = hash('md5', $dataToHash);

	//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
	// begin testing each form element 
	    if($username=="")
		{
	        $errorMsg[]="Please enter your username";
	    } 
		else 
		{
	        $valid = verifyAlphaNum ($username); /* test for non-valid  data */
	        if (!$valid)
			{ 
	            $errorMsg[]="First Name must be letters and numbers, spaces, dashes and ' only.";

	        }
	    }

		if($firstName==""){
	        $errorMsg[]="Please enter your First Name";

	    } else {
	        $valid = verifyAlphaNum ($firstName); /* test for non-valid  data */
	        if (!$valid){ 
	            $errorMsg[]="First Name must be letters and numbers, spaces, dashes and ' only.";
	        }
	    }

	    if($lastName==""){
	        $errorMsg[]="Please enter your Last Name";
	    } else {
	        $valid = verifyAlphaNum ($lastName); /* test for non-valid  data */
	        if (!$valid){ 
	            $errorMsg[]="Last Name must be letters and numbers, spaces, dashes and ' only.";
	        }
	    }

	    if($email==""){
	        $errorMsg[]="Please enter your Email Address";
	    } elseif (!verifyEmail($email)){
	        $errorMsg[]="Email must be in a valid format";
	    }
		
		//set variables for tables and fields of those tables
		$targetTable = "table_user";
		$targetPK = "pk_user_username";
		$tarketEmail = "fld_user_email";
		$tarketFirstName = "fld_user_firstname";
		$tarketLastName = "fld_user_lastname";
		$targetPassword = "fld_user_password";
		$targetConfirmationUrl = "fld_user_confirmationHash";
		$accountType = "fld_user_adminlevel";
		
		//change admin level according to radio button
		if($selectedAccountType == 1)
		{
			//user level is represented by 1
			$adminLevel = 1;
		}
		else
		{
			//storyteller level is represented by 2
			$adminLevel = 2;
		}
		
		//**check to see if username is already in the database. NO DUPLICATE PRIMARY KEYS**//
		//make a string containing the query. Selects all fields (should just be one) from the table where the username matches
		$queryStoryUsername = "SELECT * FROM $targetTable WHERE $targetPK = '$username'";
		$usernameStoryResult = mysql_query($queryStoryUsername);
		
		//if a result has been returned, error out.
		if (mysql_num_rows($usernameStoryResult) >= 1) 
		{
			$errorMsg[]="That username exists already";
		}
	
		//must verify passwords are the same
		if($password==""){
			$errorMsg[]="Please enter a password";
		}elseif($password != $retypedPassword){
			$errorMsg[]="Passwords do not match";
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

		else 
		{ 
		//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
		// form is valid now we need to save information to a file (logfile) so the database can be recreated if necessary
			if($loaded == false)
			{	
				/* generate unique confirmation url */
				//timestamp
				$date = date_create();
				$timeStamp = date_timestamp_get($date);
				//hash username and timestamp together
				$hashMe = $username . $timeStamp;
				$urlGenerated = hash('md5', $hashMe);
				
				//begin logfile construction
				$sql = "INSERT INTO ";
		        $sql .= "$targetTable SET "; //name of table
		        $sql .= "$targetPK ='$username', ";
		        $sql .= "$tarketFirstName ='$firstName', ";
		        $sql .= "$tarketLastName ='$lastName', ";
		        $sql .= "$tarketEmail ='$email', ";
				$sql .= "$targetPassword = '$hashedPassword',";
				$sql .= "$targetConfirmationUrl = '$urlGenerated',";
				$sql .= "$selectedAccountType = '$adminLevel'";
				
		        $myFilePath="logfiles/";
		        $myFileName="sqlLog.txt";
				//create file
		        $myPointer=fopen($myFilePath.$myFileName, "a+"); //a+ means read/write(appendable)

		        $sql .= "\n"; // end of line

				//write to file
		        fputs($myPointer,$sql);
				//close file
		        fclose($myPointer);
		
				//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
				// now we need to insert the information into the mysql table

				//craft sql insert query 
				$strSql = "INSERT INTO ";
		        $strSql .= "$targetTable SET "; //name of table
		        $strSql .= "$targetPK ='$username', ";
		        $strSql .= "$tarketFirstName ='$firstName', ";
		        $strSql .= "$tarketLastName='$lastName', ";
		        $strSql .= "$tarketEmail ='$email', ";
				$strSql .= "$targetPassword='$hashedPassword',";
				$strSql .= "$targetConfirmationUrl = '$urlGenerated',";
				$strSql .= "$accountType = '$adminLevel'";
				

				//now insert the record for the tblUsers
				mysql_query($strSql) 
					or die ("Unable to insert a record into table_user " . mysql_error());

				//close the connection to the database
				mysql_close($connectId);
				
				//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
				//now we need to craft and send the confirmation email
				
				/* recipients */
				$to = $email;

		        /* subject */
		        $subject = "Confirmation of your Tales Registration.";

		        //get date and time
		        $Todays_Date=strftime("%x");
		        $Current_Time=strftime("%X");

		        /* message */
		        $message = "<html><head><title>Confirmation</title></head><body><p>This information reflects the form you filled out on ";
		        $message .= $Todays_Date . ", please keep this copy for your records.</p>";
		        $message .= "<p>Username: " . $username . "</p>";
		        $message .= "<p>First Name: " . $firstName . "</p>";
		        $message .= "<p>Last Name: " . $lastName . "</p>";
		        $message .= "<p>Email: " . $email . "</p>";
				$message .= "<p>Please follow the following URL to activate your membership:</p>";
				$message .= "<p><a href='http://www.uvm.edu/~pvendevi/cs148/final/confirmation.php?confirmationId=" . $urlGenerated . "'>Follow me please.</a></p>";


		        /* To send HTML mail, you can set the Content-type header. */
		        $headers  = "MIME-Version: 1.0\r\n";
		        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

		        /* additional headers */
		        $headers .= "From: Tales, a website by Phelan Vendeville <tales@uvm.edu>\r\n";

		        // and now to mail it
		        $blnMail=mail($to, $subject, $message, $headers);
		
				//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
				//now redirect to thank you page
				header('Location: thanks.php');
			}
		}
}
?>