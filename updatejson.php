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


	if (isset($_GET["delete"])){

	    $targetUID = (int)$_GET["delete"];
	    
	    foreach($phparray as $entry) {
	    	if ($entry['UID'] !== $targetUID) {
	    		$returnData[] = $entry;   
	    	} else {
	    		$imgPath = "uploadimages/" . $entry['UID'] . "." . $entry['imagetype'];
	    		$thumbPath = "thumbnails/" . $entry['UID'] . "." . $entry['imagetype'];

	    		if (file_exists($imgPath)) {
	    			unlink($imgPath);
	    			unlink($thumbPath);
	    		}
	    	}    
	    } // foreach

    } else if (isset($_GET["approve"])){
   		$targetUID = (int)$_GET["approve"]; 
   	//	echo "targetUID: " . $targetUID;

   		foreach($phparray as $entry) {
	    	
	    	if ($entry['UID'] == $targetUID) {
	    		$entry['isApproved'] = true;
	    	} 

	    	$returnData[] = $entry;  	    	   
	    } // foreach
    
    } else if (isset($_GET["acceptall"])){

   		foreach($phparray as $entry) {	    	
	    	$entry['isApproved'] = true; 
	    	$returnData[] = $entry;  	    	   
	    } // foreach
    }


	// encode the returnData array to json 
    $jsoncode = json_encode($returnData, JSON_PRETTY_PRINT);
	
    // write the jsoncode to the allinfo.json file
    file_put_contents($file, $jsoncode);

   // redirect to approval.php and **force it to reload gallery

    // echo $jsoncode;
   header("Location: approval.php?done=true");

?>