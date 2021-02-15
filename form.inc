

  <body>

	<div class="fullscreen">
		<div class="focusScreen">
			<div id="titleHeading">
				<h3> Mount Douglas Grad 2021 Gallery</h3>
				<h4> Photo Submission Page </h4>
			</div>
			<!-- header ends here -->
			
			<!-- include an external JavaScript file -->
    		<script src="approvescript.js"></script>
			
			<!-- Form starts here -->
			<div class="wrapFont">
				<form method="post" action='approval.php' enctype="multipart/form-data">
				
					<p>
						<p2><span id="general" class="error"><?php echo $generalERR;?></span><br></p2>
					  <!-- textbox for user's names -->
						<div class="section">
						  <label for="fname">First Name</label><br>
						  <span id="fname" class="error"><?php echo $fnameERR;?></span><br>
						  <input type="text" id="fname" name="fname" value="<?php echo $fname;?>">  
						</div>
					  
						<div class="section">
						  <label for="lname">Last Name</label><br>
						  <span class="error"><?php echo $lnameERR;?></span><br>
						  <input type="text" id="lname" name="lname" value="<?php echo $lname;?>">		 
						</div>
						
					 
						<!-- a file upload field -->
						<div class="section">
							<label for="uploadFile">Upload Your Image</label><br>
							<span class="error"><?php echo $filenameERR;?></span><br>
							<input type="file" name="fileToUpload" id="fileToUpload" class="inputfile" data-multiple-caption="{count} files selected" multiple />
							<label for="fileToUpload">Choose a file</label>						
						</div>
					
						<!-- a textarea for a description of the photo -->
						<div class="section">
							<label for="description">Describe Your Photo</label><br>
							<span class="error"> <?php echo $descriptionERR;?></span><br>
							<textarea id="description" name="description" placeholder="What is happening in your photo?" rows="4" cols="50" ><?php echo $description;?></textarea>
						</div>
					  
						<!-- a textarea for tagging the the photo -->
						<div class="section"> 
							<label for="tag">Add Tags To Your Photo</label><br>
							<span class="error"><?php echo $tagERR;?></span><br>
							<textarea name="tag" id="tag" placeholder="e.g. baby, first christmas, santa, etc." rows="2" cols="50"><?php echo $tag;?></textarea>
						</div>
					  
						<!-- checkbox for copyRight permission -->
						<div class="section">
							<label for="copyRight">Confirm Your Copyright</label> <br> <br>
							<input type="checkbox" name="copyRight" value="agreed" <?php if(isset($_POST['copyRight'])) echo "checked='checked'"; ?>>
							<label for="copyRight"> I retain the copyright to this image and release it for use on this site.</label> <br>
							<span class="error"><?php echo $copyRightERR;?></span><br>
						</div>

						<!-- radio buttons for private/public -->
						<div class="section">
							<label for="preference">Please Indicate Your Privacy Preference For The Photo</label><br>
							<span class="error"><?php echo $privacyERR;?></span><br>
							<input type="radio" id="public" name="privacy" <?php if (isset($privacy) && $privacy=="private") echo "checked";?> value="private">
							<label for="public">Private</label><br>
							<input type="radio" id="private" name="privacy" <?php if (isset($privacy) && $privacy=="public") echo "checked";?> value="public">
							<label for="private">Public</label><br>
						</div>
		
						<!-- final submit button -->
						<div class="masterButton" id="formSubmit">			
							 <input id="uploadImage" type="submit" value="Upload Image" name="submit">
						</div> 

						<div class="masterButton" id="smallerButton">		
							<a href="index.php">Go to Public Gallery</a>
						</div>

					</p>	
				</form>
			</div>
		</div>
	</div>	

	<!-- include an external JavaScript file -->
    <script src="approvescript.js"></script>
	
  </body>
  
