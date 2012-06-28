<?php

foreach($_FILES as $file) {
  
  if ($file['error'] == UPLOAD_ERR_OK) {
   
    $tmp_name = $file['tmp_name'];
    $fname = $file['name'];
    
    $result = move_uploaded_file($tmp_name, '/home/casey/uploadtest/' . $fname);
    
    echo (TRUE === $result) ? 'Yay' : 'Boo';
  }
  else {
    echo 'TOOBIG!';
  }
}


/* EOF: upload.php */

