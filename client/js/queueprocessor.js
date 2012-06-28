
/**
 * Process the Queue
 */
function queue_process() {

  //Get the next item in the queue.  If FALSE, then stop processing the queue
  if (get_queue_state() != 'cancelling') {
    set_queue_state('processing');
  }
   
  var queue_item = queue_shift();
  
  if (queue_item != false && get_queue_state() == 'processing') {
    
    //Upload the file
    var formdata = new FormData();
    formdata.append('UPLOAD_IDENTIFIER', queue_item.key);
    formdata.append(queue_item.fileobject.name, queue_item.fileobject);  
    
    $.ajax({
        url: server_url + 'ajax.php',
        data: formdata,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        beforeSend: function(jqXHR, settings) {
          
          //Show a message
          interface_show('current_upload_filename');
          interface_update_content('current_upload_filename', 'Uploading ' + queue_item.fileobject.name);
          
          //Set a flag to indicate the current uploading file
          queue_set_current_upload_key(queue_item.key);
          
          //Start the progress meter
          queue_do_progress(queue_item.key);
        },
        success: function(data){
          console.log("Uploaded..." + data);
          queue_clear_current_upload_key();
          filemgr_get_file_list();
        },
        error: function(jqXHR, errortext) {
          console.log(jqXHR);
          console.log(errortext);
          queue_clear_current_upload_key();
        },
        complete: function(jqXHR, textStatus) {
          
          //Revert message
          interface_update_content('current_upload_filename', 'No Uploads in Progress');
          interface_hide('current_upload_filename');
          
          //Recursive callback
          queue_process();
        }
    });    
        
  }
  else {
    set_queue_state('ready');
  }
}

// -----------------------------------------------------------------------------

/**
 * Show progress bar during upload
 */
function queue_do_progress(upload_id) {
  
  if (queue_get_current_upload_key() == upload_id) {
  
    var url = server_url + 'uprogress.php?id=' + upload_id;

    $.getJSON(url, function(data) {
      
      var curr_prog;
    
      if (typeof data.noprogress != 'undefined') {
        curr_prog = 'Working...'; //This is the response the server sends if there is no progress functionality installed
      }
      else if (typeof data.bytes_total != 'undefined') {
        curr_prog = Math.floor(100 * parseInt(data.bytes_uploaded) / parseInt(data.bytes_total));
      }
      else {
        curr_prog = 0;
      }
            
      $('#current_upload_status').html("<progress max='100' value='" + curr_prog + "' />");
      debug("Progress: " + curr_prog);
      
    });

    var caller = "queue_do_progress('" + upload_id + "')";
    setTimeout(caller, 750);
  }
  else {
    $('#current_upload_status').html("No Uploads in Progress");
  }
}

// -----------------------------------------------------------------------------

function queue_stop_processing() {
  
  //Cancel all files after the current upload
  set_queue_state('cancelling');
  
}

// -----------------------------------------------------------------------------

function queue_set_current_upload_key(key) {
  
  queue_current_upload_key = key;
}

// -----------------------------------------------------------------------------

function queue_clear_current_upload_key() {
  
  queue_current_upload_key = false;
  
}

// -----------------------------------------------------------------------------

function queue_get_current_upload_key() {
  
  if (typeof queue_current_upload_key != 'undefined') {
    return queue_current_upload_key;
  }
  else {
    return false;
  }
  
}