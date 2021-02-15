<?php

	if(!isset($_SERVER['HTTP_REFERER'])){
		// redirect them to your desired location
		header('location: index.php');
		exit;
    }

	// awaiting approval file
	$file = "galleryinfo.json";

	if (!file_exists($file)) {
		touch($file);
	}//if
	
	$jsonstring = file_get_contents($file); // read json file into array of strings
	$phparray = json_decode($jsonstring, true); //decode the string from json to PHP array
	
	$returnData = []; // elements of the array are added to this to make a new array
	$length = count($phparray); // how many elements are in $phparray
	
	if(!empty($_POST['submit'])){// if the user made changes to the input construct a new element with the changes in it
		for($i = 0; $i < $length; $i++){
			if("ID: " . $phparray[$i]["UID"] == $_POST["imgUIDEdit"]){
				$constructor = [];
				$constructor += ["fname"=>$_POST["fname"]];
				$constructor += ["lname"=>$_POST["lname"]];
				$constructor += ["description"=>$_POST["description"]];
				$constructor += ["tag"=>$_POST["tag"]];
				$constructor += ["copyRight"=>$phparray[$i]["copyRight"]];
				$constructor += ["privacy"=>$phparray[$i]["privacy"]];
				$constructor += ["UID"=>$phparray[$i]["UID"]];
				$constructor += ["imagetype"=>$phparray[$i]["imagetype"]];
				$constructor += ["isApproved"=>"true"];

				$returnData [] = $constructor;
			}else{// saves the non edited elements
				$returnData [] = $phparray[$i];
			}//else
		}//for
	}else{// if the user deleteed an element unlink the photoes and don't save the JSON data
		for($i = 0; $i < $length; $i++){
			if("ID: " . $phparray[$i]["UID"] == $_POST["imgUIDEdit"]){
				unlink(realpath("thumbnails/" . $phparray[$i]["UID"] . "." . $phparray[$i]["imagetype"]));
				unlink(realpath("uploadimages/" . $phparray[$i]["UID"] . "." . $phparray[$i]["imagetype"]));
			}else{
				$returnData [] = $phparray[$i];
			}
		}
	}	
	
	// encode the returnData array to json 
	$jsoncode = json_encode($returnData, JSON_PRETTY_PRINT);
	
	// write the jsoncode to the allinfo.json file
	file_put_contents($file, $jsoncode);
	
	//updates allinfo.json
	$fileA = "allinfo.json";
	$jsonstring = file_get_contents($fileA); // read json file into array of strings
	$allPhparray = json_decode($jsonstring, true); //decode the string from json to PHP array
	
	/*
	$newPhparray = [];
	$countAll = count(allPhparray);
	$found = false;
	
	for($i = 0; $i < $countAll; $i++){
		for($j = 0; $j < $length; $j++){
			if($allPhparray[$i]["UID"] == $returnData[$j]["UID"]){
				$newPhparray [] = $returnData[$j];
				$found = true;
				break
			}
		}
		
		if(!$found){
			newPhparray [] = $allPhparray[$i];
		}
		$found = false;
	}
	*/
	
	//merges a the two arrays together
	$newPhparray = $allPhparray + $returnData;
	$constructNewArray = [];
	
	//removes the elementfrom the JSON
	if(!empty($_POST["Delete"])){
		$countAll = count($newPhparray);
		for($i = 0; $i < $countAll; $i++){
			if("ID: " . $newPhparray[$i]["UID"] != $_POST["imgUIDEdit"]){
				$constructNewArray [] = $newPhparray[$i];
			}
		}
	}
	/*
	echo"<pre>";
	var_dump($constructNewArray);
	echo"</pre>";
	
	echo $countAll;
	*/
	
	//saves the json to allinfo
	$jsoncode = json_encode($constructNewArray, JSON_PRETTY_PRINT);
	file_put_contents($fileA, $jsoncode);
	
	//clears post
	unset($_POST);

    //redirect to approval.php and **force it to reload gallery
    header("Location: index.php");
?>