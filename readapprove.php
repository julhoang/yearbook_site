<?php

 if(!isset($_SERVER['HTTP_REFERER'])){
     // redirect them to your desired location
     header('location: index.php');
     exit;
 }

 // read json file into array of strings
 $jsonstring = file_get_contents("allinfo.json");
 
 // save the json data as a PHP array
 $phparray = json_decode($jsonstring, true);

 
 // use GET to determine type of access
 if (isset($_GET["access"])){
  $access = $_GET["access"];
 } else {
  $access = "all"; 
 }
 
  // pull public or private only or return all
  // NOTE: to make this more secure, if $access == "private" or "all"
  // you would also check that an editor is logged in.
  $returnData = [];
  if ($access != "all") { 
   foreach($phparray as $entry) {
    // var_dump($entry);
      if ($entry["privacy"] == $access && $entry['isApproved'] == false) {
         $returnData[] = $entry;  
      }      
   } // foreach
  } else {
      foreach($phparray as $entry) {
        if ($entry['isApproved'] == false) {
           $returnData[] = $entry;  
        }      
      } // foreach
  }

// encode the php array to json 
 $jsoncode = json_encode($returnData, JSON_PRETTY_PRINT);
 echo ($jsoncode);



?>