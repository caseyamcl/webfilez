<?php

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

if (@$_GET['id']) {
  
  if (is_callable('uploadprogress_get_info')) {
    $output = json_encode(uploadprogress_get_info($_GET['id']));

    if ( ! $output OR $output == 'null') {
      $output = json_encode(array('upload_id' => $_GET['id'], 'pending' => '1'));
    }    
  }
  else {
    $output = json_encode(array('upload_id' => $_GET['id'], 'noprogress' => '1'));
  }
    
  echo $output;
  exit();  
}

/* EOF: uprogress.php */