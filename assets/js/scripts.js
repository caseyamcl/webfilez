
$(document).ready(function() {

  /*
   * Globals
   */

  //server_url defined in index.php
  debug_mode = true;

  //Current directory
  //@TODO: Make this know if we are looking at a file
  //  may need to have PHP report this
  currpath = (window.location.href).substr(server_url.length) + '/';

  /*
   * Initial Setup
   */
  interface_check_required_elements();
  setup_layout();
  initialize_queue();
  initialize_filemgr();
  

  
  /*
   * Event handlers
   */
  
  //Window resize...
  $(window).resize(setup_layout);
  
  //Drag files over queue
  $('#upload_queue').bind('dragenter', queue_drag_handler);
  $('#upload_queue').bind('dragover', queue_drag_handler);
  $('#upload_queue').bind('dragexit', queue_dragexit_handler);

  //Drop files into queue
  $('#upload_queue').bind('drop', queue_drop_handler);  
  
  //Start processing queue
  $('#current_upload').bind('click', queue_process);
});

// -----------------------------------------------------------------------------

/**
 * Setup layout
 */
function setup_layout() {
  
  //Calculate the height of the main container and resize it
  var windowheight = $(window).height();
  var minusheight = 1; //extra px
  minusheight += $('body > header').outerHeight();
  minusheight += $('body > footer').outerHeight();
  var containerheight = windowheight - minusheight;
  
  //Set the file manager and the container height to the correct px
  $('body > #main').height(containerheight);
  $('body > #main > #filemgr').height(containerheight);
  $('body > #main > #uploader').height(containerheight);
  
  //Set the upload queue to the correct height
  var uploader_height = containerheight;
  uploader_height -= $('#main > #uploader #upload_queue').siblings().outerHeight() + 60;
  $('body > #main > #uploader #upload_queue').height(uploader_height);
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