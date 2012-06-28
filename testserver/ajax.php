<?php

$upload_folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'webfileztest' . DIRECTORY_SEPARATOR;
if ( ! file_exists($upload_folder)) {
    mkdir($upload_folder);
}

foreach($_FILES as $file) {
  

  if ($file['error'] == UPLOAD_ERR_OK) {
   
    $tmp_name = $file['tmp_name'];
    $fname = $file['name'];
    
    $result = move_uploaded_file($tmp_name, $upload_folder . $fname);
    
    echo (TRUE === $result) ? 'Yay' : 'Boo';
  }
  else {
    echo 'TOOBIG!';
  }
}


/* EOF: upload.php */

