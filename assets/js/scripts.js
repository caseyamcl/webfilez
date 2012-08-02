
$(document).ready(function() {

  /*
   * Globals
   */

  //server_url defined in index.php
  debug_mode = true;

  //Current directory
  currpath = (window.location.href).substr(server_url.length) + '/';

  /*
   * Initial Setup
   */
  setup_layout();
  initialize_queue();
  initialize_filemgr();
  
  /*
   * Event handlers
   */
  
  //Window resize...
  $(window).resize(setup_layout);
  
  //Drag files over queue
  $('body').bind('dragenter dragover dragexit drop', function(e) { e.preventDefault(); });
  $('#upload_queue').bind('dragenter', queue_drag_handler);
  $('#upload_queue').bind('dragover', queue_drag_handler);
  $('#upload_queue').bind('dragexit', queue_dragexit_handler);

  //Drop files into queue
  $('#upload_queue').bind('drop', queue_drop_handler);  
  
  //Start processing queue
  $('#current_upload').bind('click', queue_process);

  //Add files using button
  $('#file_add_button').bind('change', queue_file_button_handler);

  //Click on file link
  $("#filemgr #filelist").on('click', 'li.file > a', filemgr_view_file);
  $('#filemgr').on('click', '#filedetails .filedetailsclose', filemgr_close_file);

  //click on a folder link in the manager
  $('#filemgr').on('click', 'li.dir > a', filemgr_open_dir);

  //click on a folder link in the breadcrumbs
  $('#filemgr').on('click', '#breadcrumbs li > a', filemgr_open_dir);

  //Click on delete link
  $('#filemgr').on('click', '.delete_link', filemgr_delete_file);

  //Click on add folder link
  $('#filemgr').on('click', '#mkdir', filemgr_add_dir);

  //Try to navigate away
  window.onbeforeunload = function() {
    if (get_queue_state() != 'ready') {
      alert("The queue is currently processing!  Navigating away from this page will destroy your current uploads.  Are you sure you want to do this?");
    }
    if (get_queue_count() > 0) {
      alert("If you leave this page, your files will not be uploaded.  Are you sure you want to do this?");
    }
  };

});

// -----------------------------------------------------------------------------

/**
 * Setup layout
 */
function setup_layout() {
  
  //Calculate the height of the main container and resize it
  var webfilezheight = $('#webfilez').height();
  var minusheight = 1; //extra px
  var containerheight = webfilezheight - minusheight;
  
  //Set the file manager and the container height to the correct px
  $('#webfilez #filemgr').height(containerheight);
  $('#webfilez #uploader').height(containerheight);
  
  //Set the upload queue to the correct height
  var uploader_height = containerheight;
  uploader_height = uploader_height - ($('#webfilez #upload_queue').siblings().outerHeight() + 90);
  $('#webfilez #uploader #upload_queue').height(uploader_height);
}

// -----------------------------------------------------------------------------

/**
 * Safe debug console logger
 */
function debug(item) {
  
  if (debug_mode == true) {
    console.log(item);
  }
} 

// -----------------------------------------------------------------------------

/**
 * Helper function to clean empty array items
 * From: http://stackoverflow.com/questions/281264/remove-empty-elements-from-an-array-in-javascript
 * Slight modification by Casey McL
 */
Array.prototype.clean = function(deleteValue) {

  if (typeof deleteValue == 'undefined') {
    deleteValue = '';
  }

  for (var i = 0; i < this.length; i++) {
    if (this[i] == deleteValue) {         
      this.splice(i, 1);
      i--;
    }
  }
  return this;
};

// -----------------------------------------------------------------------------

/**
 * Helper function to serialize an object in Javascript
 * From: http://www.dotnetfunda.com/articles/article763-serialize-object-in-javascript.aspx
 */
function serialize(obj)
{
  var returnVal;
  if(obj != undefined){
  switch(obj.constructor)
  {
   case Array:
    var vArr="[";
    for(var i=0;i<obj.length;i++)
    {
     if(i>0) vArr += ",";
     vArr += serialize(obj[i]);
    }
    vArr += "]"
    return vArr;
   case String:
    returnVal = escape("'" + obj + "'");
    return returnVal;
   case Number:
    returnVal = isFinite(obj) ? obj.toString() : null;
    return returnVal;    
   case Date:
    returnVal = "#" + obj + "#";
    return returnVal;  
   default:
    if(typeof obj == "object"){
     var vobj=[];
     for(attr in obj)
     {
      if(typeof obj[attr] != "function")
      {
       vobj.push('"' + attr + '":' + serialize(obj[attr]));
      }
     }
      if(vobj.length >0)
       return "{" + vobj.join(",") + "}";
      else
       return "{}";
    }  
    else
    {
     return obj.toString();
    }
  }
  }
  return null;
}

/* EOF: scripts.js */