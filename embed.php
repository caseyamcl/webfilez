<?php

/**
 * @file Webfilez Main Server-Side Embed Function
 */

// =============================================================================

function embedWebfilezCss($webfilezUrl) {

    $ds = DIRECTORY_SEPARATOR;
    $cssDir = __DIR__ . $ds . 'assets' . $ds . 'css' . $ds;
    $cssUrl = rtrim($webfilezUrl, '/') . '/assets/css/';

    foreach(scandir($cssDir) as $file) {

        if (substr($file, -4) == '.css') {
            printf("<link rel='stylesheet' type='text/css' href='%s' />", $cssUrl . $file);
        }

    }

}

// -----------------------------------------------------------------------------

function embedWebfilezHtml($webfilezUrl) {

    $ds = DIRECTORY_SEPARATOR;
    require_once(__DIR__ . $ds . 'libs' . $ds . 'Webfilez.php');
    echo Webfilez::embed($webfilezUrl);
}

/* EOF: embed.php */