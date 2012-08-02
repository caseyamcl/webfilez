/**
 * @file Interface Manipulators
 * 
 * Wrappers around jquery manipulation functions to update interface elements.
 *
 */

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