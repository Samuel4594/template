<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>C to D Salvage</title>
	<link rel="shortcut icon" href="http://192.168.3.215/salvage/images/favicon.ico">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
	integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/main.css">
	<link href='https://fonts.googleapis.com/css?family=Play' rel='stylesheet' type='text/css'>
</head>

<body>
<div id = "info" style="display:none;"></div>

<!-- DONT INCLUDE ANY OF THE ABOVE CODE IN YOUR FILES -->

<?php

	$holding = "1true";
	
	if ($holding == "true"){		require('holding.php');				}
	else {
	
	
	if(empty($_REQUEST['displayPage']) || ($_REQUEST['displayPage'] == "index")){
		
		//192.168.3.215/salvage/index.php?page=
		
		if(empty($_REQUEST['page'])) {
			require('includes/log-in-contact.php'); 
			require('includes/header.php'); 
			require("home.php");
			require('includes/footer.php'); 
		}
		elseif($_REQUEST['page'] == "sign-in") {
			require('includes/log-in-contact.php'); 
			require("login.php");
			//require("signin.php");
			require('includes/footer.php'); 
		}
		elseif($_REQUEST['page'] == "contact") {
			require('includes/log-in-contact.php'); 
			require('includes/header.php');
			require("contact.php");
			require('includes/footer.php'); 
		}
		elseif($_REQUEST['page'] == "about") {
			require('includes/log-in-contact.php'); 
			require('includes/header.php');
			require("about.php");
			require('includes/footer.php'); 
		}
		elseif($_REQUEST['page'] == "error") {
			require("404.php");
		}
		elseif($_REQUEST['page'] == "email-success") {
			require('includes/log-in-contact.php'); 
			require('includes/header.php');
			require("includes/email-success.php");
			require('includes/footer.php'); 
		}
		elseif($_REQUEST['page'] == "admin") { 
			//require('includes/header.php');
			require('includes/log-in-contact.php'); 
			if(isset($_COOKIE['username'])) { 
				require("admin.php");
			}
			else {
				header("location: index.php");
			}
			//require('includes/footer.php'); 
		}
		elseif($_REQUEST['page'] == "additem") { 
			require('includes/log-in-contact.php');
			require('includes/header.php');
			require("apitest/AddItem.php");
			require('includes/footer.php'); 
		}
		elseif($_REQUEST['page'] == "additem_action") { 
			require('includes/log-in-contact.php');
			require("apitest/AddItem_action.php");
		}
		elseif($_REQUEST['page'] == "enditem") { 
			require('includes/log-in-contact.php');
			require("apitest/EndItem.php");
		}
		elseif($_REQUEST['page'] == "enditem_action") { 
			require('includes/log-in-contact.php');
			require("apitest/EndItem_action.php");
		}
		elseif($_REQUEST['page'] == "edititem") { 
			require('includes/log-in-contact.php');
			//require('includes/header.php');
			require("apitest/EditItem.php");
			//require('includes/footer.php'); 
		}
		elseif($_REQUEST['page'] == "edititem_action") { 
			require('includes/log-in-contact.php');
			require("apitest/EditItem_action.php");
			require('includes/get-item-data.php');
		}

	}
	
	
}//holding
	
?>


            
<!-- DONT INCLUDE ANY OF THE BELOW CODE IN YOUR FILES -->
<script src="moment.js"></script>
<script src
	<script src="humanize-duration.js"></script>
    <script>moment().format();</script>
    
	<script src='https://www.google.com/recaptcha/api.js'></script>
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/jquery.cookiebar.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    
    <script src="js/jquery.validate2.js"></script>
    <script src="js/additional-methods.js"></script>
    <script src="js/custom.js"></script>

   
</body>

</html>