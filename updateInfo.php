<?php
	
	if(!isset($_SERVER['HTTP_REFERER'])){
		// redirect them to your desired location
		header('location: index.php');
		exit;
    }
	
	// awaiting approval file
	$file = "allinfo.json";

	if (!file_exists($file)) {
		touch($file);
	}
	
	$jsonstring = file_get_contents($file); // read json file into array of strings
	$phparray = json_decode($jsonstring, true); //decode the string from json to PHP array
	$returnData = $phparray;
	
	$returnData = [];


?>