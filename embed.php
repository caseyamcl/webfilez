<?php

/**
 * @file Webfilez Main Server-Side Embed Function
 */

// =============================================================================

function embedWebfilez($webfilezUrl) {

    $ds = DIRECTORY_SEPARATOR;
    require_once(__DIR__ . $ds . 'libs' . $ds . 'Webfilez.php');
    Webfilez::embed($webfilezUrl);
}

/* EOF: embed.php */