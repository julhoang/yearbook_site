<?php

	if(!isset($_SERVER['HTTP_REFERER'])){
		// redirect them to your desired location
		header('location: index.php');
		exit;
    }

	$file = "allinfo.json";

	if (!file_exists($file)) {
		touch($file);
	}
	
	// read json file into array of strings
	$jsonstring = file_get_contents($file);
	//decode the string from json to PHP array
	$waitingArray = json_decode($jsonstring, true);


	include "approveheader.inc";

	// define variables and set to empty values
	$general = $fname = $lname = $filename = $description = $tag = $copyRight = $privacy = "";
	$generalERR = $fnameERR = $lnameERR = $filenameERR = $descriptionERR = $tagERR = $copyRightERR = $privacyERR = "";
	$errorCount = 0;
	$showGallery = true;
	$generalERR = "* All fields are required *";
	$target_dir = "uploadimages/";
	$uploadOk = 1;
	$UID = 0;
	$imagetype = "";

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

	// ERROR CHECKING THE FORM SUBMISSION
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
		// set this to false first -- when the form is all correct, it will be set to true again
		$GLOBALS['showGallery'] = false;	
		
		if(array_key_exists('resetJSON', $_POST)) {
			deleteJSON();

		} else if (array_key_exists('deleteEntry', $_POST)) {
			deleteEntry();

		} else if (array_key_exists('viewG', $_POST)) {
			$GLOBALS['showGallery'] = true;
			viewGallery();
		} else if (array_key_exists('deleteUID', $_POST)) {
			deleteUID();
		} else if (array_key_exists('deleteAllEntries', $_POST)) {
			deleteJSONandImages();
		} else {
			
			if (empty($_POST["fname"])) {
				$GLOBALS['fnameERR'] = "* First name is required";
				$GLOBALS['errorCount'] = $GLOBALS['errorCount'] + 1;
			} else {
				$GLOBALS['fnameERR'] = "";
				$fname = test_input($_POST["fname"]);
			}
			
			if (empty($_POST["lname"])) {
				$lnameERR = "* Last name is required";
				$GLOBALS['errorCount'] = $GLOBALS['errorCount'] + 1;
			} else {
				$lnameERR = "";
				$lname = test_input($_POST["lname"]);
			}
				
			uploadImage();	// error check and upload image
			
			if ($GLOBALS['filenameERR'] != "") {
				$GLOBALS['errorCount'] = $GLOBALS['errorCount'] + 1;
			}
				
			if (empty($_POST["description"])) {
				$descriptionERR = "* Description is required";
				$GLOBALS['errorCount'] = $GLOBALS['errorCount'] + 1;
			} else {
				$descriptionERR = "";
				$description = test_input($_POST["description"]);
			}
			
			$tag = test_input($_POST["tag"]);
			
			
			 if (empty($_POST["copyRight"])) {
				$copyRightERR = "* A copyright option is required";
				$GLOBALS['errorCount'] = $GLOBALS['errorCount'] + 1;
			 } else {
				$copyRightERR = "";
				$copyright = test_input($_POST["copyRight"]);
			 }

			 if (empty($_POST["privacy"])) {
				$privacyERR = "* You must select an option.";
				$GLOBALS['errorCount'] = $GLOBALS['errorCount'] + 1;
			 } else {
				$privacyERR = "";
				$GLOBALS['privacy'] = test_input($_POST["privacy"]);
			 }
			
			$GLOBALS['generalERR'] = "";
			// echo "Error Count:". $errorCount;
			// echo "UploadOK: " . $uploadOk;
			// echo "showGallery boolean: " . $showGallery;
		} // else
	
	} // if SERVER == POST

	if ($errorCount > 0 || $uploadOk == 0 || isset($_GET['form']) == true) {
		include "form.inc";
		die();
	} 

	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	} // test_input


	function assignUID() {
	//	echo "Assinging UID" . "<br>";

		$file = "identifier.txt";
		global $UID;
		
		// if file doesn't exist, create it using "touch"
		if (!file_exists($file)) {
			touch($file);
		}

		// read json file into array of strings
		$jsonstring = file_get_contents($file);
		
		//decode the string from json to PHP array
		$UIDarray = json_decode($jsonstring, true);
		
		if ($UIDarray == null) {
			$UIDarray [] = 1;
		} else {
			$UIDarray [] = end($UIDarray) + 1;
		}


		$UID = end($UIDarray);
		
		// encode the UID array to formatted json
		$jsoncode = json_encode($UIDarray, JSON_PRETTY_PRINT);
		
		file_put_contents($file, $jsoncode);
		return $UID;
	} // assignUID
	

	function addToJSON(){
		
		$file = "allinfo.json";
		global $UID;
		global $imagetype;
		global $waitingArray;
		
		// if file doesn't exist, create it using "touch"
		if (!file_exists($file)) {
			touch($file);
		}
		
		// add form submission to data
		unset($_POST['submit']); // remove submit button
		$_POST += [ "UID" => $UID ];
		$_POST += [ "imagetype" => $imagetype];
		$_POST += [ "isApproved" => false];
		
		$waitingArray [] = $_POST;
		
		// encode the php array to formatted json
		$jsoncode = json_encode($waitingArray, JSON_PRETTY_PRINT);
		
		// write the json to the file
		file_put_contents($file, $jsoncode);
		
	} // addToJSON

	function uploadImage() {
		
		$GLOBALS['filenameERR'] = "";
		global $imagetype;
		global $target_dir;
		global $uploadOk;


		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		$imagetype = $imageFileType;
		
		// error checking below
		// check if file is an image or just blank
		try {
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
		} catch (Exception $e) {
		}
		
		if($check !== false) {
			//echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		} else {
			// echo "File is not an image.";
			$GLOBALS['filenameERR'] = "File is not an image.";
			$uploadOk = 0;
			return null;
		}
		
		// create uploadedimages (folder) if it doesn't exist
		if (!is_dir($target_dir)) {
			mkdir($target_dir);
			echo "<br>" . $target_dir . " created! <br>";
		} // if
		
		// Check if file already exists
		if (file_exists($target_file)) {
		
			$GLOBALS['filenameERR'] = " Sorry, file already exists.";
			$uploadOk = 0;
			return null;
		}

		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 4194304) {
			// echo "Sorry, your file is too large.";
			$GLOBALS['filenameERR'] = " Sorry, your file is too large.";
			$uploadOk = 0;
			return null;
		} else if ($_FILES["fileToUpload"]["size"] < 100) {
			$GLOBALS['filenameERR'] = " * Choose a file";
			return null;
		}

		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {	echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			$GLOBALS['filenameERR'] = " Sorry, only JPG, JPEG & PNG files are allowed.";
			$uploadOk = 0;
			return null;
		}
	
		// if everything is ok, try to upload file WITH NEW FILE NAME (UID)
		if ($uploadOk == 1) {
			$name = assignUID();
			$extension = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION);
	
			$imagetype = $extension;
			
			$filepath = "uploadimages" . "/" . $name."." . $extension;
			

			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $filepath)) {
				$uploadOk = 1;

			} else {
				$uploadOk = 0;
				return null;
			}
		} 
		
	} // uploadImage

	function updateGalleryInfo() {
		$file = "galleryinfo.json";
		
		global $waitingArray;

		if ($waitingArray == NULL) {
			echo "waitingArray NULL";
			return null;
		}
		
		// if file doesn't exist, create it using "touch"
		if (!file_exists($file)) {
			touch($file);
		}
		
		// read json file into array of strings
		$jsonstring = file_get_contents($file);
		//decode the string from json to PHP array
		$phparray = json_decode($jsonstring, true);

		$returnData = [];
   
	    foreach($waitingArray as $entry) {
	       if ($entry['isApproved'] == true) {
	       	 //unset($entry['isApproved']);
	       	 array_push($returnData, $entry);
	       }   
	    } // foreach

		
		// encode the php array to formatted json
		$jsoncode = json_encode($returnData, JSON_PRETTY_PRINT);
		
		// write the json to the file
		file_put_contents($file, $jsoncode);
	}


	// only showing NON-APPROVED IMAGES
	function viewGallery() {
		updateGalleryInfo();


		global $isEditor;
		global $waitingArray;
		$file = "allinfo.json";


		echo '<div class="grid-container">';
			// ------ HEADING ---------

		echo "<div class='header title'>";
		
		echo '<div id="school">';
		echo "<h1>YEARBOOK GALLERY 2021</h1>";
		echo "</div>";

		echo '<div>';
		echo "<h2>AWAITING APPROVAL GALLERY</h2>";
		echo "</div>";

		echo '<div id="approval" class="masterButton">';		
		echo '<a id="yellowLink" href="index.php">MODERATOR GALLERY
		</a>';
		echo '</div>';


		echo "<div id='searchDiv'>";
		$sch = "search('" . 'all' . "')";
			echo '<input id="search" type="text" placeholder="Quick Look Up" name="search">';
			echo '<span id="searchButton" onclick="' . $sch . '">Search</span>'; // search all
		echo "</div>";

		echo "</div>"; // close header


		// ---------- BODY ----------
		echo "<div class='middle'>";
		echo "<div id='mainBody'>";
		
		if ($waitingArray == NULL) {
			echo "All Images have been approved. Return to MODERATOR GALLERY" . "<br>";
			echo '<div class="masterButton">';		
			echo '<a href="index.php">Moderator Main Page</a>';
			echo '</div>';

			echo '<div class="masterButton">';		
			echo '<a href="approval.php?form=true">Upload Form</a>';
			echo '</div>';

			include "footer.inc";
			return null;
		}

		

		echo '<div class="main middle" id="upperPanel">';
			
				echo '<br><br>';
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

		echo "</div>"; // close div upperPanel

		echo "<br><br>";

		// THUMBNAIL CODES------

		// create folder to store thumbnails
		$dir = "uploadimages/";
		$thumbnail_dir = "thumbnails/";
		
		if (!is_dir($thumbnail_dir)) {
			mkdir($thumbnail_dir);
		} // if

		$imgAll = glob($dir . "*"); // access all files in uploadimages
		
		foreach($imgAll as $image) {
			$imgNames = explode("/", $image); // use $imgNames[1]		
			$thumb = $thumbnail_dir . $imgNames[1];	
			// loop through image folder to create thumbnail		
			createThumbnail($image, $thumb, 150, 150);
		}
		
		$thumbsAll = glob($thumbnail_dir . "*");

		echo '<div class="masterButton">';	
		echo '<a href="updatejson.php?acceptall=true">Approve All</a>';
		echo '</div>';


		echo "<div class='picFrame' id='picFrame'>";
		
		foreach ($waitingArray as $element) {
			
			if ($element['isApproved'] == false) {
				$source = "thumbnails/" . $element['UID'] . "." . $element['imagetype'];
				$displayPrompt = "displayLightBox('" . $element['UID'] . "', 'none', 'all', 'true')";

				echo "<div class='imgBox'>";
				echo '<img class="thumbApprove" id="' . ($element['UID']) . '"  src="'.$source.'" 
					alt="'.$element['UID'].'"
					onclick=" '.$displayPrompt.' ">';
				
				echo "<p>" . $element['fname'] . " " . $element['lname'] . "</p>";
				
				echo '<a class="deleteBtn" href="updatejson.php?delete=' . $element['UID'] . '"><span>Delete</span></a>';
				echo '<a class="approveBtn" href="updatejson.php?approve=' . $element['UID'] . '"><span>Approve</span></a>';
				echo "</div>";	
			}
			
		} // foreach

		echo "</div>";


		// HTML CODE FOR LIGHTBOX - copy from CS11
		echo '<div id="lightbox" class="hidden"></div>';

		echo	'<div id="positionBigImage">';

		echo '<div id="boundaryBigImage" class="hidden">';
			echo '<span id="x" onclick=displayLightBox()><i class="gg-close"></i></span>';
			echo '<img id="bigImage" src="0.png" alt="Test">';
		

			// HTML CODE FOR BUTTONS - only show with lightbox image
			
			echo '<div class="masterButton">';	
				echo "<span id='prev'><i class='gg-chevron-left-o'></i></span>";	
				echo '<a id="link" href="" download> DOWNLOAD </a>';
				echo "<span id='next'><i class='gg-chevron-right-o'></i></span>";
			echo '</div>';

			echo "<div id='imgInfo'>";
			echo '<span id="imgID" class="unhidden">ID </span> ' . '<br>';
			echo '<span id="imgName" class="unhidden">Image Owner</span> ' . '<br>';
			echo '<span id="imgDesc" class="unhidden">Image Description</span> ' . '<br>';
			echo '<span id="imgTag" class="unhidden">Image Tag</span> ' . '<br>';

			echo "</div>"; // close imginfo
			echo '</div>'; // boundaryBigImage

		echo  '</div>'; // positionBigImage

		echo "<div></div>";

		echo "<script src='approvescript.js'></script>";

		echo "</div>"; 	// mainBody close div
		
		echo "</div>";	// close div middle

		// ----- End MIDDLE --------


		// RIGHT ---------
		echo "<div class='right'>";

		echo "</div>";
		// ---------------


		echo "</div>";	// close grid container


	} // viewGallery
	
	/**
	 * @param $src - a valid file location
	 * @param $dest - a valid file target
	 * @param $targetWidth - desired output width
	 * @param $targetHeight - desired output height or null
	 */
	function createThumbnail($src, $dest, $targetWidth, $targetHeight = null) {

	    // 1. Load the image from the given $src
	    // - see if the file actually exists
	    // - check if it's of a valid image type
	    // - load the image resource

	    // get the type of the image
	    // we need the type to determine the correct loader
	    $type = exif_imagetype($src);

	    // if no valid type or no handler found -> exit
	    if (!$type || !IMAGE_HANDLERS[$type]) {
	        return null;
	    }

	    // load the image with the correct loader
	    $image = call_user_func(IMAGE_HANDLERS[$type]['load'], $src);

	    // no image found at supplied location -> exit
	    if (!$image) {
	        return null;
	    } 


	    // 2. Create a thumbnail and resize the loaded $image
	    // - get the image dimensions
	    // - define the output size appropriately
	    // - create a thumbnail based on that size
	    // - set alpha transparency for GIFs and PNGs
	    // - draw the final thumbnail

	    // get original image width and height
	    $width = imagesx($image);
	    $height = imagesy($image);

	    // define defaults for thumbnail
	    $startX = 0;
	    $startY = 0;
	    $src_w = $width;
	    $src_h = $height;


	    // maintain aspect ratio when no height set
	    if ($targetHeight == null) {

	        // get width to height ratio
	        $ratio = $width / $height;

	        // if is portrait
	        // use ratio to scale height to fit in square
	        if ($width > $height) {
	            $targetHeight = floor($targetWidth / $ratio);
	        }
	        // if is landscape
	        // use ratio to scale width to fit in square
	        else {
	            $targetHeight = $targetWidth;
	            $targetWidth = floor($targetWidth * $ratio);
	        }
	    }  else {
	      // Calculate starting positions and copy area 
	      // to not distort the thumbnail image ratio.
	      // get width to height ratio
	      $ratio = $width / $height;
	      $targetRatio = $targetWidth / $targetHeight;
	      $combinedRatio = $ratio / $targetRatio;

	      // Cut X wise in source to avoid thumb image distortion
	      if ($combinedRatio >= 1) {
	        $thumbRatio = $height / $targetHeight;
	        $startX = ($width - ($thumbRatio * $targetWidth)) / 2;
	        $src_w = ($thumbRatio * $targetWidth);
	        $startY = 0;
	        $src_h = $height;
	      }
	      // Cut Y wise in source to avoid thumb image distortion
	      else {
	        $thumbRatio = $width / $targetWidth;
	        $startY = ($height - ($thumbRatio * $targetHeight)) / 2;
	        $src_h = ($thumbRatio * $targetHeight);
	        $startX = 0;
	        $src_w = $width;
	      }
	    }

	    // create duplicate image based on calculated target size
	    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

	    // set transparency options for GIFs and PNGs
	    if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {

	        // make image transparent
	        imagecolortransparent(
	            $thumbnail,
	            imagecolorallocate($thumbnail, 0, 0, 0)
	        );

	        // additional settings for PNGs
	        if ($type == IMAGETYPE_PNG) {
	            imagealphablending($thumbnail, false);
	            imagesavealpha($thumbnail, true);
	        }
	    }

	    // copy entire source image to duplicate image and resize
	   /* imagecopyresampled(
	        $thumbnail,
	        $image,
	        0, 0, 0, 0,
	        $targetWidth, $targetHeight,
	        $width, $height
	    );*/

	imagecopyresampled(
	        $thumbnail,
	        $image,
	        0, 0, $startX, $startY,
	        $targetWidth, $targetHeight,
	        $src_w, $src_h
	    );


	    // 3. Save the $thumbnail to disk
	    // - call the correct save method
	    // - set the correct quality level

	    // save the duplicate version of the image to disk
	    return call_user_func(
	        IMAGE_HANDLERS[$type]['save'],
	        $thumbnail,
	        $dest,
	        IMAGE_HANDLERS[$type]['quality']
	    );
	} // createThumbnail



	if ($showGallery == true || isset($_GET['done']) == true) {
		viewGallery();
	} else if ($errorCount == 0 && $generalERR != "* All fields are required *" && $uploadOk == 1) {
		addToJSON();
		//viewGallery();
		header('Location: index.php');
	} else if ($errorCount > 0 || $showGallery == false){
		include "form.inc";
	} // if

?>