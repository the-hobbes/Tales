<?php
	//*****^^^ This script performs the validation and updating process for changing the information in a user's profile ^^^*****//
	//user.php passes in POST data from the jeditable library, and this data is then validated
	//if the data is invalid, a message is displayed. 
	//if the data is valid, then the update is performed and the user sees the changes enacted. 
	
	session_start(); //continue the session
	$username = $_SESSION['username']; //get session username
	
    include ("validation_functions.php");

	//establish a connection to the server
	$connectId = mysql_connect("webdb.uvm.edu","pvendevi_admin","bj9GOhOOElyn6d3Z");
	if (!$connectId)
	  {
	  	die('Could not connect: ' . mysql_error());
	  }
	mysql_select_db("PVENDEVI_Tales", $connectId);

	//get the id of the div you are passing in from jeditable
	$id = $_POST["id"];
	//echo $id;
	
	//get the value that has been changed
	$value = $_POST["value"];
	//echo $value;

	//special case for email
	if($id == "email")
	{
		if($value == "") //if email is empty...
		{
	        echo "Cannot be empty";
	    } 
		else //if email is not empty
		{
	        $valid = verifyEmail($value);
	
	        if (!$valid)//if email is invalid...
			{ 
	            echo "Input must be letters and numbers, spaces, dashes and ' only.";
	        }
			else //if email is valid...
			{
				//update record and echo $value
				//craft sql
				$sql = "UPDATE table_user SET fld_user_email = '$value' WHERE pk_user_username = '$username'";
				//send sql statement to database
				mysql_query($sql) or die ("Unable to update the email address in the database " . mysql_error());
				//show new email value
				echo $value;
			}
	    }
	}
	//for all other input
	else
	{
		if($value == "") //if the changed value is empty...
		{
	        echo "Cannot be empty";
	    }
		else //if the changed value is not empty
		{
	        $valid = verifyText($value);
	        if (!$valid)//if changed value is invalid...
			{ 
	            echo "Input must be letters and numbers, spaces, dashes and ' only.";
	        }
			else //if changed value is valid...
			{
				//update record and echo $value
				$field = fieldName($id);//find out what type of field name this is so the query can be made
				
				//craft sql
				$sql = "UPDATE table_user SET $field = '$value' WHERE pk_user_username = '$username'";
				//send sql statement to database
				mysql_query($sql) or die ("Unable to update the record in the database " . mysql_error());
				//show new field value
				echo $value;
			}
	    }
	}
	
	/**
	 * fieldName()
	 * checks the $id variable and determines what kind of field is in it.
	 * This is then used to determine what the appropriate field name in the database is to be updated.
	 * Takes in $id, whose value is the name of the div passed in with $value. Div names correspond loosely to field names.
	 * Returns a string value called $field, containing the attribute value of the field in the database. 
	 */
	function fieldName($id)
	{
		//using switch statement to select between the pre-built values
		$field = "";
		
		switch($id)
		{
			case "firstName":
				$field = "fld_user_firstname";
				break;
			case "lastName":
				$field = "fld_user_lastname";
				break;
			case "picture":
				$field = "fld_user_photoid";
				break;
			case "bio":
				$field = "fld_user_biography";
				break;
			default:
				break;
		}
		return $field;
	}
?>

