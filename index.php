<?php

/**
 * @file Webfilez Main Server-Side Execution File
 */

// =============================================================================

//Require
$ds = DIRECTORY_SEPARATOR;
require_once(__DIR__ . $ds . 'libs' . $ds . 'Webfilez.php');

//Go!
Webfilez::main();

/* EOF: index.php */