<?php
	session_start(); //continue the session
	$username = $_SESSION['username']; //get session username
	
    include ("validation_functions.php");

	//establish a connection to the database
	include ("connect.php");
	//get the id of the div you are passing in from jeditable
	$id = $_POST["id"];
	//echo $id . "\n";
	
	//get the value that has been changed
	$value = $_POST["value"];
	//echo $value;

	updateSql($id, $value);

	//need a way to pass in the textid

	/**
	 * updateSql()
	 * Used to update the appropriate record in the text table.
	 * Uses string operations to find the pk id of the text block, and update that record in the texts table.
	 */
	function updateSql($id, $value)
	{
		//craft sql
		$sql = "UPDATE table_texts SET fld_texts_textblock = '$value' WHERE pk_texts_textid = '$id'";
		//send sql statement to database
		mysql_query($sql) or die ("Unable to update the record in the database " . mysql_error());

		echo $value;
	}
?>