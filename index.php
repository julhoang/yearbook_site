<?php
	session_start();
	
	$count = 0;

	if(empty($_SESSION ["isEditor"])){
		$_SESSION ["isEditor"] = false;
	}
	
	//checks if the user logs in or not
	try{
		if(isset($_POST["LOGIN"])){
			if($_POST['LOGIN'] == "LOG IN"){
				$_SESSION ["isEditor"] = true;
				unset($_POST['LOGIN']);
				$loggedIn = true;
			}elseif($_POST['LOGIN'] == "LOG OUT"){
				$_SESSION ["isEditor"] = false;
				unset($_POST['LOGIN']);
				$loggedIn = true;
			}else{
				$loggedIn = false;
			}
		}else{
			$loggedIn = false;
		}
	}catch(Exception $e){
	}
	
	$file = "galleryinfo.json";

	if (!file_exists($file)) {
		touch($file);
	}
	
	// read json file into array of strings
	$jsonstring = file_get_contents($file);
	//decode the string from json to PHP array
	$phparray = json_decode($jsonstring, true);

	
	$isEditor = $_SESSION ["isEditor"];

	include "header.inc";
	
	// Link image type to correct image loader and saver
	// - makes it easier to add additional types later on
	// - makes the function easier to read
	const IMAGE_HANDLERS = [
	    IMAGETYPE_JPEG => [
	        'load' => 'imagecreatefromjpeg',
	        'save' => 'imagejpeg',
	        'quality' => 100
	    ],
	    IMAGETYPE_PNG => [
	        'load' => 'imagecreatefrompng',
	        'save' => 'imagepng',
	        'quality' => 0
	    ],
	    IMAGETYPE_GIF => [
	        'load' => 'imagecreatefromgif',
	        'save' => 'imagegif'
	    ]
	];
	
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		// set this to false first -- when the form is all correct, it will be set to true again
		$GLOBALS['showGallery'] = false;	
		
		if(array_key_exists('resetJSON', $_POST)) {
			deleteJSON();
		} else if (array_key_exists('deleteUID', $_POST)) {
			deleteUID();
		} else if (array_key_exists('deleteAllEntries', $_POST)) {
			deleteJSONandImages();
		}
	
	} // if SERVER == POST

	// BUTTONS --------------------------------
	function deleteJSON() {
		// echo "FROM DELETE BUTTON";
		$file = "galleryinfo.json";
		
		if (file_exists($file)) {
			unlink(realpath($file));
		}

		$file2 = "allinfo.json";

		if (file_exists($file2)) {
			unlink(realpath($file2));
		}
	} // deleteJSON
	
	function deleteJSONandImages() {
		deleteJSON();
		$files = glob('uploadimages/*');
		foreach($files as $file){ // iterate files
			if(is_file($file)) {
				unlink($file); // delete file
			}
		}

		$tbfiles = glob('thumbnails/*');
		foreach($tbfiles as $tbfile){ // iterate files
			if(is_file($tbfile)) {
				unlink($tbfile); // delete file
			}
		}

	} // deleteJSONandImages
	
	function deleteUID() {
		$file = "identifier.txt";
		
		if (file_exists($file)) {
			unlink(realpath($file));
		}
	}  // deleteFolder


	function searchForId($id, $array) {
			
	   foreach ($array as $key => $val) {
	       if ($val['UID'] == $id) {
	           return $key;
	       }
	   }
	} // searchForID

	// ------------------------
	// PHP is responsible for uploading new images and create thumbnails
	// PHP prints the first appearance of the thumbs
	// When users hit the Filter or Sorting buttons --> JS takes over and handles all images
	// ------------------------
	function viewGallery() {

		global $isEditor;
		global $phparray;
		$file = "galleryinfo.json";
		
		if (file_exists($file)) {

			echo '<div class="grid-container">';
			// ------ HEADING ---------

			echo "<div class='header title'>";
			
			echo '<div id="school">';
			echo "<h1>YEARBOOK GALLERY 2021</h1>";
			echo "</div>";

			if ($isEditor == true) {
				
				echo '<div>';
				echo "<h2>MODERATOR GALLERY</h2>";
				echo "</div>";
				
				$sch = "search('" . 'all' . "')";
				
				echo '<div id=searchDiv>';
				echo '<input id="search" type="text" placeholder="Quick Look Up" name="search">';
     			echo '<span id="searchButton" onclick="' . $sch . '">Search</span>'; // search all
				echo "</div>";
				
				echo '<form action="index.php" method="POST">';
				echo '<input type="submit" name="LOGIN" id="loginout" class="masterButton" value="LOG OUT">';
				echo '</form>';

				echo '<div id="approval" class="masterButton">';		
				echo '<a id="yellowLink" href="approval.php"> <i class="gg-eye-alt"></i> <em>Awaiting Approval</em></a>';
				echo '</div>';
			
			} else {

				echo '<div>';
				echo "<h2>PUBLIC GALLERY</h2>";
				echo "</div>";
				
				$sch = "search('" . 'public' . "')";
				
				echo '<div id=searchDiv>';
				echo '<input id="search" type="text" placeholder="Quick Look Up" name="search">';
     			echo '<span id="searchButton" onclick="' . $sch . '">Search</span>'; // search all
				echo "</div>";
				
				echo '<form action="index.php" method="POST">';
				echo '<input type="submit" name="LOGIN" id="loginout" class="masterButton" value="LOG IN">';
				echo '</form>';

				echo '<div id="belowSchool" class="masterButton">';		
				echo '<a id="yellowLink" href="approval.php?form=true">Upload Image</a>';
				echo '</div>';
			}
			
			echo "</div>"; // close header

			//  LEFT -------
			echo "<div class='left'>";
		
			echo "</div>";
			// ------------


			// ---------- BODY ----------
			echo "<div class='middle'>";
			echo "<div id='mainBody'>";
			

			if ($phparray == NULL) {
				echo "Public Gallery Is So Empty. Upload an image!" . "<br>";
				echo '<div class="masterButton">';		
				echo '<a href="approval.php?form=true">Upload Image</a>';
				echo '</div>';
				if ($isEditor == true) {
					include "footer.inc";
				}
				return null;
			}

			if (isset($_GET['success'])) {
				echo "Your Form Has Been Submitted Successfully." . "<br>";
				echo "It Will Appear Here Once Reviewed." . "<br>";
			}
			
			
			echo '<div class="main middle" id="upperPanel">';
					

			// MODERATOR Heading
			if ($isEditor == true) {
     			echo "Filter by: ";
				echo '<span class="filter" id="all" onclick="updateVar(\'none\', \'all\')">All Images</span>';
				echo '<span class="filter" id="private" onclick="updateVar(\'none\', \'private\')">Private Images Only</span>';
				echo '<span class="filter" id="public" onclick="updateVar(\'none\', \'public\')">Public Images Only</span>';
				
				echo "<br><br><br>";
				
				echo '<div id="sort">';
				echo "Sort by: ";
					echo '<span class="filter" id="date" onclick="updateVar(\'date\', \'none\')">Earliest Submission</span>';
					echo '<span class="filter" id="fname" onclick="updateVar(\'fname\', \'none\')">First Name</span>';
					echo '<span class="filter" id="lname" onclick="updateVar(\'lname\', \'none\')">Last Name</span>';
				echo "</div>"; // close div sort
			
			// PUBLIC Heading
			} else {
				
				echo '<div id="sort">';
				echo "Sort by: ";
					echo '<span class="filter" id="date" onclick="updateVar(\'date\', \'public\')">Earliest Submission</span>';
					echo '<span class="filter" id="fname" onclick="updateVar(\'fname\', \'public\')">First Name</span>';
					echo '<span class="filter" id="lname" onclick="updateVar(\'lname\', \'public\')">Last Name</span>';
				echo "</div>"; // close div sort
			} // else
			
			echo "</div>"; // close div upperPanel

			echo "<br><br>";
			
			// for MODERATOR --> show "Download All Button"
			if ($isEditor == true) {
				echo '<div class="masterButton">';		
				echo '<a href="downloadall.php" download>Download All Images</a>';
				echo '</div>';
			}

			echo "<div class='picFrame' id='picFrame'>";
			
			// LOADING IMAGES
			// isEditor --> show all images
			// if not --> show public only
			if ($isEditor == true) {
			
				foreach ($phparray as $element) {
					
					$source = "thumbnails/" . $element['UID'] . "." . $element['imagetype'];
					$displayPrompt = "displayLightBox('" . $element['UID'] . "', 'none', 'all', 'true')";
					
					echo "<div class='imgBox'>";
					echo '<img class="thumb" id="' . ($element['UID']) . '"  src="'.$source.'" 
						alt="'.$element['UID'].'"
						onclick=" '.$displayPrompt.' ">';
					echo "<p>" . $element['fname'] . " " . $element['lname'] . "</p>";
					echo "</div>";
				
				} // foreach
				
			// PUBLIC GALLERY
			} else {
				
				foreach ($phparray as $element) {
					if ($element['privacy'] == "public") {
						$source = "thumbnails/" . $element['UID'] . "." . $element['imagetype'];
						$displayPrompt = "displayLightBox('" . $element['UID'] . "', 'none', 'public', 'true')";
						
						echo "<div class='imgBox'>";
						echo '<img class="thumb" id="' . ($element['UID']) . '"  src="'.$source.'" 
					  		alt="'.$element['UID'].'"
					  		onclick=" '.$displayPrompt.' ">';
					  	echo "<p>" . $element['fname'] . " " . $element['lname'] . "</p>";
						echo "</div>";
					}
				
				} // foreach
				
			} // else (public gallery)

			echo "</div>";


			// HTML CODE FOR LIGHTBOX - copy from CS11
			echo '<div id="lightbox" class="hidden"></div>';

			echo	'<div id="positionBigImage">';

			echo '<div id="boundaryBigImage" class="hidden">';
				echo '<span id="x" onclick=displayLightBox()><i class="gg-close"></i></span>';
				echo '<img id="bigImage" src="placeholder.png" alt="Test">';
			

				// HTML CODE FOR BUTTONS - only show with lightbox image
				echo '<div class="masterButton">';	
					echo "<span id='prev'><i class='gg-chevron-left-o'></i></span>";	
					echo '<a id="link" href="" download> DOWNLOAD </a>';
					echo "<span id='next'><i class='gg-chevron-right-o'></i></span>";
				echo '</div>';

				//populate the lightbox with the structure of the JSON data 
				echo "<div id='phpInfo' class='unhidden'>";		
					echo '<span id="imgID" class="unhidden">ID </span> ' . '<br>';
					echo '<span id="imgName" class="unhidden">Image Owner</span> ' . '<br>';
					echo '<span id="imgDesc" class="unhidden">Image Description</span> ' . '<br>';
					echo '<span id="imgTag" class="unhidden">Image Tag</span> ' . '<br>';
					
				//if user is the editor let them access the edit view	
				if($isEditor){
					echo '<button id="editJSONData" class="masterButton" onclick="displayEdit()">';
						echo 'Edit';
					echo '</button>';
				}
				
				echo "</div>"; // phpInfo

				echo '</div>'; // boundaryBigImage

			echo  '</div>'; // positionBigImage
  
			echo "<div></div>";


			echo "</div>"; 	// mainBody close div
			echo "</div>";	// close div middle

			// ----- End MIDDLE --------


			// RIGHT ---------
			echo "<div class='right'>";

			echo "</div>";
			// ---------------

			// RIGHT ---------
			echo "<div class='footer'>";
			if ($isEditor == true) {
				include "footer.inc";
			}
			echo "</div>";
			// ---------------

			

			echo "</div>";	// close grid container



		// if galleryinfo.json doesn't exist
		// then tell user to upload something
		} else {
			echo "Gallery Now Empty. Upload an Image";
			echo '<div class="masterButton">';		
			echo '<a href="approval.php?form=true">Upload Image</a>';
			echo '</div>';
		}
		
	} // viewGallery

	
	viewGallery();


	echo "<script src='myscript.js'></script>";
	echo "</html>";
	
?>