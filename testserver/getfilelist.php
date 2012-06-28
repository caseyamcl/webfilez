<?php

$upload_folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'webfileztest' . DIRECTORY_SEPARATOR;
if ( ! file_exists($upload_folder)) {
    mkdir($upload_folder);
}

echo json_encode(array_values(array_diff(scandir($upload_folder), array('.', '..'))));

/* EOF: getfilelist.php */