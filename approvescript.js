// ---------------------------------
// THIS PART IS FOR UPDATING UPLOAD BUTTON IN FORM.INC
// DON'T TOUCH
var inputs = document.querySelectorAll( '.inputfile' );
Array.prototype.forEach.call( inputs, function( input )
{
  var label  = input.nextElementSibling,
    labelVal = label.innerHTML;

  input.addEventListener( 'change', function( e )
  {
    var fileName = '';
    if( this.files && this.files.length > 1 )
      fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
    else
      fileName = e.target.value.split( '\\' ).pop();

    if( fileName )
      label.innerHTML = fileName;
    else
      label.innerHTML = labelVal;
  });
});
// ---END OF BUTTON-------

// --- THIS PART IS FOR CHECKING IF isEDITOR HAS CHANGED? ------
// if yes --> reset all current stored information of "sorting" and "filter"
// var storedEditor = localStorage.getItem('isEditor');
// //console.log("check storedEditor: " + storedEditor);

// if (storedEditor !== isEditor) {
//   storedEditor = isEditor;
//   console.log("CHANGE!! NEW ACCESS: " + isEditor);
//   localStorage.setItem('isEditor', isEditor);

//   // reset stored sorting and filter
//   localStorage.setItem('sorting', "");
//   localStorage.setItem('filter', "");
// }

// THIS FUNCTION HELPS TO DETERMINE THE ACTIVITITY OF USER (copied from stackOverFlow haha)
// I'm trying to detect if user hits the reload button or not
// if user hits reload button --> also set stored "filter" and "sorting" to empty
function navigationType(){

    var result;
    var p;

    if (window.performance.navigation) {
        result=window.performance.navigation;
        if (result==255){result=4} // 4 is my invention!
    }

    if (window.performance.getEntriesByType("navigation")){
      try {
         p=window.performance.getEntriesByType("navigation")[0].type;
       } catch(error){
       }
      

       if (p=='navigate'){result=0}
       if (p=='reload'){result=1}
       if (p=='back_forward'){result=2}
       if (p=='prerender'){result=3} //3 is my invention!
    }

// Result description:
// 0: clicking a link, Entering the URL in the browser's address bar, form submission, Clicking bookmark, initializing through a script operation.
// 1: Clicking the Reload button or using Location.reload()
// 2: Working with browswer history (Bakc and Forward).
// 3: prerendering activity like <link rel="prerender" href="//example.com/next-page.html">
// 4: any other method.
    return result;
} // navigationType

var checkReload = navigationType();
//console.log("check user activity: " + checkReload);

// if user hits reload --> also reset "sorting" and "filter"
if (checkReload == 1) {
  // reset stored sorting and filter
  localStorage.setItem('sorting', "");
  localStorage.setItem('filter', "");
}
// ------- END OF CHECKING --------------------



// USE FOR SORTING AND FILTERING
// CHECK IF THERE IS UPDATE
// THEN SEND THE "ACCESS" and "SORT" to loadImage()
function updateVar(sorting, access) {
  
  var x = document.getElementsByClassName("filter");
  for (let i = 0; i < x.length; i++) {
    x[i].style.backgroundColor = "#BACFE4";
  }

  
  // Step 1: Recall the previous setting
  var tempSort = localStorage.getItem('sorting');
  var tempAccess = localStorage.getItem('filter');
  

  // Step 2: update tempAccess if user requests new filter
  if (access != "none") {
    tempAccess = access;
  }

  // if tempAccess = "", then set it to default
  // default for Moderator is "all"
  // default for Public is "public"
  if (tempAccess.length < 1) {
      tempAccess = "all";
    
  }

  // Step 3: update tempSorting if user requests new sorting
  if (sorting != "none") {
    tempSort = sorting;
  }  

  // if tempAccess = "", then set it to default
  // default for both Moderator and Public is "date"(= UID)
  if (tempSort.length < 1) {
    tempSort = "date";
  }

  // Step 4: update localStorage
  localStorage.setItem('sorting',tempSort);
  localStorage.setItem('filter', tempAccess);
  
  document.getElementById(tempSort).style.backgroundColor = "yellow";
  document.getElementById(tempAccess).style.backgroundColor = "yellow";

  // Step 5: call loadImage (printing image)
  loadImage(tempSort, tempAccess);
} // updateVar


// load the new sorted image gallery
function loadImage(sorting, access) {

  var searchButton = document.getElementById("searchButton");
  var searchTextArea = document.getElementById("search");
  
  searchButton.onclick = null;

  if (access == null || access == "none") {
    var tempAccess = localStorage.getItem('filter');
    searchButton.addEventListener('click', function () { 
            search(tempAccess) 
          });
    searchTextArea.placeholder = "Search in " + tempAccess;
    
  } else {
    searchButton.addEventListener('click', function () { 
            search(access) 
          });
    searchTextArea.placeholder = "Search in " + access;
  }

  searchTextArea.value = "";

  fetch("./sortapprove.php?type=" + sorting).
    then(function(resp){ 
      return resp.json();
    })

    .then(function(data){
      
      let i = 0;  // counter     
      let frame = document.getElementById("picFrame");

      while (frame.firstChild) {
        frame.removeChild(frame.firstChild);
      }

      for (i in data) {
  
        if ((access == "all" || data[i].privacy == access) && data[i].isApproved == false) {
          let img = new Image();
          var div = document.createElement("DIV");
          div.className = "imgBox";
          var imgCap = document.createElement("P");
          frame.appendChild(div);

          img.src = "thumbnails/" + data[i].UID + "." + data[i].imagetype;
          img.alt = data[i].description;
          img.id = data[i].UID;
          img.addEventListener('click', function () { 
            displayLightBox(this.id,sorting,access,"true") 
          });
          img.className = "thumbApprove";
          imgCap.innerHTML = data[i].fname + " " + data[i].lname;

          div.appendChild(img);
          div.appendChild(imgCap);
          
          var deleteButton = document.createElement("a");
          deleteButton.innerHTML = "DELETE";
          deleteButton.href = "updatejson.php?delete=" + data[i].UID;
          deleteButton.className = "deleteBtn";
          div.appendChild(deleteButton);
          

          var approveButton = document.createElement("a");
          approveButton.innerHTML = "APPROVE";
          approveButton.href = "updatejson.php?approve=" + data[i].UID;
          approveButton.className = "approveBtn";
          div.appendChild(approveButton);     
        }

      } // for
   
    }) // close function data

    .catch(function() {
      console.log("something wrong from load Image");
    });
        
} // loadSort

function search(access) {

  var x = document.getElementsByClassName("filter");
  for (let i = 0; i < x.length; i++) {
    x[i].style.backgroundColor = "#BACFE4";
  }

  // find and display images that match the search criteria
  let allFound = [];

  fetch("./readapprove.php?access=" + access).
    then(function(resp){ 
      return resp.json();
    })

    .then(function(data){

      var x = document.getElementById("search").value;
      let i = 0;

      var frame = document.getElementById("picFrame");

      while (frame.firstChild) {
        frame.removeChild(frame.firstChild);
      }

      for (i in data) {
        var upperDesc = data[i].description.toUpperCase();
        var upperTag = data[i].tag.toUpperCase();
        var upperFN = data[i].fname.toUpperCase();
        var upperLN = data[i].lname.toUpperCase();
        x = x.toUpperCase();

        if (upperFN == x || upperLN == x || upperDesc.includes(x) || upperTag.includes(x)) {
          
          // storing the array of info for the images that match search criteria
          // help for looping images purpose
          allFound.push(data[i]);
          let array = data[i]; 

          let img = new Image();
          var div = document.createElement("DIV");
          div.className = "imgBox";
          var imgCap = document.createElement("P");
          frame.appendChild(div);

          img.src = "thumbnails/" + data[i].UID + "." + data[i].imagetype;
          img.alt = data[i].description;
          img.id = data[i].UID;
          img.addEventListener('click', function () { 
            lightboxSearch(array, allFound, true) 
          });
          img.className = "thumbApprove";
          imgCap.innerHTML = data[i].fname + " " + data[i].lname;

          div.appendChild(img);
          div.appendChild(imgCap);
          
          var deleteButton = document.createElement("a");
          deleteButton.innerHTML = "DELETE";
          deleteButton.className = "deleteBtn";
          deleteButton.href = "updatejson.php?delete=" + data[i].UID;
          div.appendChild(deleteButton);
          

          var approveButton = document.createElement("a");
          approveButton.innerHTML = "APPROVE";
          approveButton.className = "approveBtn";
          approveButton.href = "updatejson.php?approve=" + data[i].UID;
          div.appendChild(approveButton);     
        } 
      }
      
        
    }) // close function data

    .catch(function() {
      console.log("error from search");
    });
    
} // search

// change the visibility of a divID
function changeVisibility (divID) {
  var element = document.getElementById(divID);

  // if element exists, toggle its class
  // between hidden and unhidden
  if (element) {
    element.className = (element.className == 'hidden')? 'unhidden':'hidden';
  } // if
} // changeVisibility



// Previous Image function
function before(ID, sorting, access) {
  var imgAssess = String(access);
  displayLightBox(ID, sorting, imgAssess, false);
}

// Next Image function
function next(ID, sorting, access) {
  var imgAssess = String(access);
  displayLightBox(ID, sorting, imgAssess, false);
}


// Function for LIGHTBOX
// Showing full size image
function displayLightBox(startID, sorting, access, needChange) {

   fetch("./sortapprove.php?type=" + sorting).
    then(function(resp){ 
      return resp.json();
    })

    .then(function(data){
      if (needChange == null) {
        needChange = "true";
      }
  
      
      // close button
      if (startID == null) {
        changeVisibility("lightbox");
        changeVisibility("boundaryBigImage");
        return null;
      }

      let i = 0;  // counter  
      let j = 0;   
      let main = document.getElementById("bigImage");
      var image = new Image();
      

      for (i in data) {

        if (data[i].UID == startID) {
          let index = parseInt(i);
          let max = data.length;
          image.src = "uploadimages/" + data[i].UID + "." + data[i].imagetype;
          image.alt = data[i].description;

          // this is anonymous function
          // force bigImage to preload so that we can
          // access its width so it will be centered
          image.onload = function() {

            document.getElementById("boundaryBigImage").style.width = '80%';

            var prevID;
            var nextID;

            // All Image
            // no need for looping for same access-type image
            if (access == "all") {

              for (let j = 1; j <= max; j++) {
                if (j <= index) {
                  prevID = data[index - j].UID;
                  if (data[index - j].isApproved == false) {
                    break;
                  }
                } else if (j > index) {
                  prevID = data[max - (j-index)].UID;
                  if (data[max- (j-index)].isApproved == false) {
                    break;
                  }
                } else if (data[0].UID == startID) {
                  prevID = startID;
                  break;
                }          
              } // for 


              for (let j = 1; j <= max; j++) {
                  if ((index+j) <= (max-1)) {
                    nextID = data[index + j].UID;
                    if (data[index + j].isApproved == false) {
                      break;
                    }
                  } else if ((index+j) > (max-1)) {
                    nextID = data[(index+j-max)].UID;
                    if (data[(index+j-max)].isApproved == false) {
                      break;
                    }
                  } else if (data[0].UID == startID) {
                    nextID = startID;
                    break;
                  } 
              } // for

            // when access = public/private
            } else {
              // loop to find the first previous ID that has same access type
              
                for (let j = 1; j <= max; j++) {
                  if (j <= index) {
                    prevID = data[index - j].UID;
                    if (data[index - j].privacy == access && data[index - j].isApproved == false) {
                      break;
                    }
                  } else if (j > index) {
                    prevID = data[max - (j-index)].UID;
                    if (data[max- (j-index)].privacy == access && data[max- (j-index)].isApproved == false) {
                      break;
                    }
                  } else if (data[0].UID == startID) {
                    prevID = startID;
                    break;
                  }        
                } // for
              

              // loop to find the first next ID that has same access type
              
                for (let j = 1; j <= max; j++) {

                  if ((index+j) <= (max-1)) {
                    nextID = data[index + j].UID;
                    if (data[index + j].privacy == access && data[index + j].isApproved == false) {
                      break;
                    }
                  } else if ((index+j) > (max-1)) {
                    nextID = data[(index+j-max)].UID;
                    if (data[(index+j-max)].privacy == access && data[(index+j-max)].isApproved == false) {
                      break;
                    }
                  } else if (data[0].UID == startID) {
                    nextID = startID;
                     break;
                  }
                } // for
            } // isEditor if-else

    
            // update all informaion of the image
            document.getElementById("imgID").style.display = "";
            document.getElementById("imgID").innerHTML = "ID: " + data[i].UID;
            document.getElementById("imgName").innerHTML = "Owner: " + data[i].fname + " " + data[i].lname;
            document.getElementById("imgDesc").innerHTML = "Description: " + data[i].description;
            document.getElementById("imgTag").innerHTML = "Tag: " + data[i].tag;

            var onclickPromp = "before(" + prevID + ",\'" + sorting + "\',\'" + access + "\')";
            var onclickPromp2 = "next(" + nextID + ",\'" + sorting + "\',\'" + access + "\')";

            // update download link to download 1 image only
            // this DOWNLOAD button is only available to Moderator
   
            document.getElementById('link').style.visibility = "visible";
            document.getElementById('link').href = image.src;
            document.getElementById('link').innerHTML = "DOWNLOAD";
            
            document.getElementById('prev').innerHTML = "<span onclick= " + onclickPromp + "><i class= 'gg-chevron-left-o' ></i></span>";
            document.getElementById('next').innerHTML = "<span onclick= " + onclickPromp2 + "><i class= 'gg-chevron-right-o' ></i></span>";
          };

          var bigImage = document.getElementById('bigImage');

          bigImage.src = image.src;
          bigImage.alt = image.src;

          if (needChange == "true") {
            changeVisibility('lightbox');
            changeVisibility('boundaryBigImage');
          }
          
          return null;
        } // if data[i] == startID
    
      } // for loop
  
    }) // close function data

    .catch(function() {
      console.log("something wrong from displayLightBox");
    });
  
} // displayLightBox

// displaylightBox for SEARCH
function lightboxSearch(allInfo, allFound, needChange) {
  let allFound2 = allFound;

  var image = new Image();

  image.src = "uploadimages/" + allInfo.UID + "." + allInfo.imagetype;
  image.alt = allInfo.description;

  // preload image
  image.onload = function() {
    let i = 0;

    document.getElementById("boundaryBigImage").style.width = '80%';

    let index;
    for (i in allFound) {
      if (allFound[i].UID == allInfo.UID) {
        index = parseInt(i);
        break;
      }
    }

    // finding next/previous image
    // store the entire array of info for those 2 images
    var prevInfo;
    var nextInfo;
    let max = allFound.length;

    if (index == 0) {
      prevInfo = allFound[max-1];
    } else {
      prevInfo = allFound[index-1];
    }

    if (index == max - 1) {
      nextInfo = allFound[0];
    } else {
      nextInfo = allFound[index +1];
    }
    
    // update all informaion of the image
    
    try{
      document.getElementById("imgID").style.display = "";
      document.getElementById("imgID").innerHTML = "ID: " + allInfo.UID;
      document.getElementById("imgName").innerHTML = "Owner: " + allInfo.fname + " " + allInfo.lname;
      document.getElementById("imgDesc").innerHTML = "Description: " + allInfo.description;
      document.getElementById("imgTag").innerHTML = "Tag: " + allInfo.tag;
    
    } catch(err) {// if the structure for the JSON does not exist make it
      var inside = '<span id="imgID" class="unhidden">ID' + allInfo.UID + '</span><br>' +
                 '<span id="imgName" class="unhidden">Owner: ' + allInfo.fname + " " + allInfo.lname + '</span><br>' +
                 '<span id="imgDesc" class="unhidden">Description: ' + allInfo.description + '</span><br>' + 
                 '<span id="imgTag" class="unhidden">Tag: ' + allInfo.tag + '</span><br>';
      
      //include button to acces edit mode if the user is an editor
      if(isEditor){
        inside += '<button id="editJSONData" class="masterButton"  onclick="displayEdit()">' + 'Edit' + '</button>';
      }
      
      document.getElementById("phpInfo").innerHTML = inside;
    } // close catch
      
      
    try{
      document.getElementById("editJSONData").onclick = function() { 
          displayEdit(allInfo.UID); 
        }
    } catch(err) {
      console.log(err);
    } // close catch
    
    document.getElementById('prev').innerHTML = "<span><i class= 'gg-chevron-left-o'></i></span>";
    document.getElementById('next').innerHTML = "<span><i class= 'gg-chevron-right-o'></i></span>";

    document.getElementById('prev').addEventListener('click', function () { 
            lightboxSearch(prevInfo,allFound, false) 
          });
    document.getElementById('next').addEventListener('click', function () { 
            lightboxSearch(nextInfo,allFound, false) 
          });
  }; // close image onload

  var bigImage = document.getElementById('bigImage');

  bigImage.src = image.src;
  bigImage.alt = image.src;

  if (needChange == true) {
    changeVisibility('lightbox');
    changeVisibility('boundaryBigImage');
  }
  
  
} // lightboxSearch






