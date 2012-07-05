<?php

/*
 * Webfilez Config
 */
$config['autobuild']          = true;
$config['slow']               = false;
$config['foldercallback']     = 'testgetfolder';
//$config['foldercallbackfile'] = '';

function testgetfolder() {
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'webfileztest' . DIRECTORY_SEPARATOR;
}

/* EOF: config.php */