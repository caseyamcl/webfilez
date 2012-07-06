
/**
 * Process the Queue
 */
function queue_process() {

  //Get the next item in the queue.  If FALSE, then stop processing the queue
  if (get_queue_state() != 'cancelling') {
    set_queue_state('processing');
    interface_hide('upload_start');
  }
   
  var queue_item = queue_shift();
  
  if (queue_item != false && get_queue_state() == 'processing') {
    
    //Get the upload path from the currpath global combined
    //with the filename
    upload_path = currpath + queue_item.fileobject.name;

    //Upload the file    
    $.ajax({
      url: server_url + upload_path,
      data: queue_item.fileobject,
      cache: false,
      contentType: false,
      processData: false,
      type: 'PUT',
      beforeSend: function(jqXHR, settings) {
        
        //Set additional header for upload ID
        jqXHR.setRequestHeader("UploadFileId", queue_item.key);

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
    interface_show('upload_start');
  }
}

// -----------------------------------------------------------------------------

/**
 * Show progress bar during upload
 */
function queue_do_progress(upload_id) {
  
  if (queue_get_current_upload_key() == upload_id) {

    $.ajax({
      url: server_url + 'uploadstatus',
      type: 'GET',
      data: {'id': upload_id},
      dataType: 'json',
      success: function(data) {
      
        var curr_prog;
      
        //This is the response the server sends if there
        //is no progress functionality installed
        if (typeof data.noprogress != 'undefined') {
          curr_prog = 'Working...';
        }
        else if (typeof data.percent != 'undefined') {
          curr_prog = (Math.floor(data.percent * 100)) ;
        }
        else {
          curr_prog = 0;
        }
              
        $('#current_upload_status').html("<progress max='100' value='" + curr_prog + "' />");
        debug("Progress: " + curr_prog);
      }
    })

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