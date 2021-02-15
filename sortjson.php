<?php

 if(!isset($_SERVER['HTTP_REFERER'])){
    // redirect them to your desired location
    header('location: index.php');
    exit;
 }

 // read json file into array of strings
 $jsonstring = file_get_contents("galleryinfo.json");
 
 // save the json data as a PHP array
 $phparray = json_decode($jsonstring, true);
 
 // use GET to determine type of sort
 // fname, lname, date
   if (isset($_GET["type"])){
    $type = $_GET["type"];
   } else {
    $type = "none"; 
   }
    
    $returnData = $phparray;

    // Function to sort UID
    function array_sort($array, $on, $order=SORT_ASC) {
      $new_array = array();
      $sortable_array = array();

      if (count($array) > 0) {
          foreach ($array as $k => $v) {
              if (is_array($v)) {
                  foreach ($v as $k2 => $v2) {
                      if ($k2 == $on) {
                          $sortable_array[$k] = $v2;
                      }
                  }
              } else {
                  $sortable_array[$k] = $v;
              }
          }

          switch ($order) {
              case SORT_ASC:
                  asort($sortable_array);
              break;
              case SORT_DESC:
                  arsort($sortable_array);
              break;
          }

          foreach ($sortable_array as $k => $v) {
              $new_array[$k] = $array[$k];
          }
      } // array_sort

      return $new_array;
    }
 

  if ($type == "fname" || $type == "lname") {
    # Sort array - The flags SORT_NATURAL & SORT_FLAG_CASE are required to make the
    # sorting case insensitive.
    foreach ($phparray as $key => $row) {
        $sort_by[$key] = $row[$type];
    }

    array_multisort($sort_by, SORT_ASC, SORT_NATURAL|SORT_FLAG_CASE, $phparray);
  } else {
    array_sort($phparray, 'UID', SORT_ASC); // Sort by oldest first
  }
  
   
  // update returnData
  // copy information into a new array with new index assigned
    $returnData = [];
   
    foreach($phparray as $entry) {
       $returnData[] = $entry;     
    } // foreach


// encode the php array to json 
   $jsoncode = json_encode($returnData, JSON_PRETTY_PRINT);
   echo($jsoncode);

?>