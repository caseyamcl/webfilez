/**
 * Get the queue state
 */
function get_queue_state() {
  
  return queue_state;
  
}

// -----------------------------------------------------------------------------

/**
 * Set queue states
 * 
 * Allowed states:
 * - ready (queue is ready for use)
 * - processing (a file is being uploaded)
 * - cancelling (a file is being uploaded, but cancel after the next one)
 * - lock (queue is being re-ordered or drag/dropped)
 * - pause (processing, but paused)
 */
function set_queue_state(state) {
  
  //Check if its an allowed state
  var allowed_states = new Array('ready', 'processing', 'lock', 'pause', 'cancelling');
  if ($.inArray(state, allowed_states) == -1) {
    throw state + " is not a valid state!";
  }
  
  //Global variable
  queue_state = state;
  
}

// -----------------------------------------------------------------------------

/**
 * Get the number of items in the queue
 */
function get_queue_count() {

  return $('#upload_queue').children('li').length;
}

// -----------------------------------------------------------------------------

/**
 * Initialize queue (get the files from local object-store state)
 */
function initialize_queue() {
    
  //Set initial state
  set_queue_state('ready');
    
  //Create the global queue_items array
  queue_items = new Array();
  
  //@TODO: Implement local storage support
  //Read the local cookie (or local storage) 
  //Add all the items to the list
  
  //Update the text
  update_queue_status_txt();
}

// -----------------------------------------------------------------------------

/**
 * Shift an item off the queue
 * 
 * @return string An array item, or FALSE if no more array items
 */
function queue_shift() {
  
  //Return false for empty queue
  if (get_queue_count() == 0) {
    return false;
  }

  //Get the item
  var item = $('#upload_queue').children('li:first');
  var key = item.attr('title');
  var arritem = queue_items[key];
  
  //Remove both the html item and the array item
  delete queue_items[item.attr('title')];
  item.remove();  
  
  //Update the window
  update_queue_status_txt();
  if ($('#upload_queue').children('li').length == 0) {
    $('#upload_queue').addClass('nofiles');
  }

  return {'fileobject': arritem, 'key': key};
}

// -----------------------------------------------------------------------------

function queue_append(file_object) {
  queue_add(file_object, 'append');
}

// -----------------------------------------------------------------------------

function queue_prepend(file_object) {
  queue_add(file_object, 'prepend');
}

// -----------------------------------------------------------------------------

/**
 * Append item to the queue
 */
function queue_add(file_object, prepOrApp) {

  //Get the md5
  var key = hex_md5(serialize(file_object));

  if (queue_items[key] != undefined) {
    debug(file_object.name + " is already queued for upload");
    return;
  }

  //Append the file_object to the queue using its md5 as the key
  queue_items[key] = file_object;
    
  //Debug Message
  debug("Added new item with key: " + key);
    
  //Add the item to the <li> queue
  var toAdd = "<li title='"+ key +"'>" + file_object.name + "</li>";

  if (prepOrApp == 'prepend') {
    $('#upload_queue').prepend(toAdd);
  }
  else {
    $('#upload_queue').append(toAdd);
  }
  $('#upload_queue').removeClass('nofiles');
  update_queue_status_txt();
}

// -----------------------------------------------------------------------------

/**
 * Update the status text for the queue manager
 */
function update_queue_status_txt() {
  
  $('#queue_status').html(get_queue_count() + " items queued.");
  
}

// -----------------------------------------------------------------------------

/**
 * Queue drag file handler
 */
function queue_drag_handler(e) {
   e.preventDefault();
   set_queue_state('lock');
   $('#upload_queue').css('border', '3px dotted red');
}

// -----------------------------------------------------------------------------

/**
 * Queue drag handler for exiting
 */
function queue_dragexit_handler(e) {
   e.preventDefault();  
   set_queue_state('ready');
   $('#upload_queue').css('border', 'none');
}

// -----------------------------------------------------------------------------

/**
 * Queue drop handler
 */
function queue_drop_handler(e) {
  e.preventDefault();
  if (e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files) {

    $.each(e.originalEvent.dataTransfer.files, function(k,v) {
      queue_append(v);
    });
    
    set_queue_state('ready');
    $('#upload_queue').css('border', 'none');
  }
}

// -----------------------------------------------------------------------------

/**
 * Queue file button handler
 */
 function queue_file_button_handler(e) {

  set_queue_state('lock');

  var fileList = $(this).prop('files');
  $.each(fileList, function(k, v) { 
    queue_append(v);
  });
  $(this).attr('value', '');

  set_queue_state('ready');
 }

/* EOF: queuemgr.js */