/**
 * @file Interface Manipulators
 * 
 * Wrappers around jquery manipulation functions to update interface elements.
 *
 */

function interface_check_required_elements() {
 
  var required_elements = new Array(
    'filemgr',
    'uploader',
    'current_upload_filename',
    'current_upload_status',
    'upload_start',
    'queue_status',
    'upload_queue'
  );
    
  var missing_elements = new Array();
  
  for (i = 0; i < required_elements.length; i++) {
    
    var elem = required_elements[i];
    
    if ($('#' + elem).length == 0) {
      missing_elements.push(elem);
    }
  }
  
  if (missing_elements.length > 0) {
    throw "Missing Required Interface Elements in Template: " + missing_elements.join(', ');
 } else {
    return true;
  }

}

// -----------------------------------------------------------------------------

function interface_update_content(element_id, content) {
  
  if ($('#' + element_id).length > 0) {
    $('#' + element_id).html(content);
  }
  
}

// -----------------------------------------------------------------------------

function interface_add_class(element_id, class_name) {

  if ($('#' + element_id).length > 0) {
    if ( ! $('#' + element_id).hasClass(class_name)) {
      $('#' + element_id).addClass(class_name);
    }
  }
} 

// -----------------------------------------------------------------------------

function interface_remove_class(element_id, class_name) {
  
  if ($('#' + element_id).length > 0) {
    if ($('#' + element_id).hasClass(class_name)) {
      $('#' + element_id).removeClass(class_name);
    }
  }  
}

// -----------------------------------------------------------------------------

function interface_show(element_id) {
  
  if ($('#' + element_id).length > 0) {
    $('#' + element_id).show();    
  }    
  
}

// -----------------------------------------------------------------------------

function interface_hide(element_id) {
  
  if ($('#' + element_id).length > 0) {
    $('#' + element_id).hide();    
  }   
}

/* EOF: interface.js */