<?php
if (isset($_POST["cmdSubmitted"]))
{
	$password = $_POST["txtPassword"];	
	
	$dataToHash = $password;
	$hashedPassword = hash('md5', $dataToHash);
	
	echo "<body>Hashed Password: ".$hashedPassword."</body>";
}


?>
<html>
	<body>
	<div id="formData">		
		<form action="<? print $_SERVER['PHP_SELF']; ?>" method="post">  
			<fieldset>
				<label for="txtPassword">Password:</label><br /><input type="password" name="txtPassword" id="txtPassword"/><br />
				<input class="inputSubmit" type="submit" name="cmdSubmitted" value="Submit" />
			</fieldset><!-- end form fieldset-->
		</form><!-- end form-->
	</div><!-- end formdata-->
	</body>
</html>