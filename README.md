# yearbook_site

README

--
My first dynamic website with PHP.
It is a full fledge photo submission site that is used for Yearbook management.


PUBLIC/MODERATOR VIEW Handling Files:

![site roadmap](https://github.com/julhoang/yearbook_site/blob/imagegalleryfoo.png?raw=true)

1. index.php:
	- Main page for Public/Moderator View
	- Handles functions for Power Buttons (resetJSON, deleteUID, deleteAllEntries)
	- Set up the main page - when Public/Moderator view first load up
	- Load images from galleryinfo.json and display them

	- Doesn't handle Form Submission (approval.php does it)

2. myscript.js:
	- Utilise fetch from multiple php files (sortjson.php, readjson.php)
	- When users click on the Filter/Sorting button, the function is handle here (load new images, update buttons)
	- Handles the Search function
	- Handles the lightbox function
	- Handles the Edit function

	--> Basically all functioning in Public/Moderator views are processed in this file

3. galleryinfo.json:
	- storing all images information of Approved images

4. readjson.php:
	- read info from galleryinfo.json
	- use as a filter for access type: all, public, private
	- return json file of filered data entry

5. sortjson.php:
	- read info from galleryinfo.json
	- use as a sorting on: UID, first name, last name
	- return json file of sorted data entry

6. downloadall.php and HZIP.php:
	- Zip all images and Download them

7. footer.inc:
	- is a form
	- contains all the Power Buttons (resetJSON, deleteUID, deleteAllEntries)

8. header.inc:
	- contains script for HTML head

9. style.css:
	- contains styling CSS for all pages (Public/Moderator/Awaiting Approval)


AWAITING APPROVAL VIEWS Handling Files:

1. approval.php:
	- Main page for Awaiting Approval View
	- Set up the main page
	- Error check Form Submission then direct user back to Public Gallery page
	- Process form (updateJSON, assignUID, create images and thumbnails, etc)
	- Approved images are updated to galleryinfo.json 
	- Load non-approved images from allinfo.json and display them

2. approvescript.js:
	- Utilise fetch from multiple php files (sortapprove.php, readapprove.php)
	- When users click on the Filter/Sorting button, the function is handle here (load new images, update buttons)
	- Handles the Search function
	- Handles the lightbox function
	- Handles the Approve function (calling updatejson.php)

3. updatejson.php:
 	- Handles delete, approval, approveAll images
 	- Update allinfo.json
 	- Automatically direct back to Awaiting Approval View

4. readapprove.php:
	- read info from allinfo.json
	- use as a filter for access type: all, public, private
	- return json file of filered, not-yet-approved data entry

5. sortapprove.php:
	- read info from allinfo.json
	- use as a sorting on: UID, first name, last name
	- return json file of sorted, not-yet-approved data entry

6. allinfo.json:
	- storing ALL info (including approved AND non-approved images)

7. approveheader.inc:
	- contains script for HTML head

8. form.inc:
	- A form for user to upload images
	- Data processed in approval.php

9. indentifier.txt:
	- Keep track of all UID

10. editJSON.php:
	- saves the new json data to galleryinfo.json and allinfo.json
	- delets a photo and the associated json
	- links back to index.php

11. updateInfo.php:
	-reads in allinfo.json
	-stores it as an array

Temporary Link to website: http://142.31.53.220/~juma/web1/index.php
