<?php

/**
 * @file Webfilez Main Server-Side Embed Function
 */

// =============================================================================

/**
 * Embed Webfilez CSS
 *
 * Echos the CSS <link rel='...' /> HTML to output
 *
 * @param string $webfilezUrl
 */
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

/**
 * Embed Webfilez HTML
 *
 * Echos the HTML embed code to output
 *
 * @param string $webfilezUrl
 * @param string|null $folder
 */
function embedWebfilezHtml($webfilezUrl, $folder = null) {

    $ds = DIRECTORY_SEPARATOR;
    require_once(__DIR__ . $ds . 'libs' . $ds . 'Webfilez.php');
    echo Webfilez::embed($webfilezUrl, $folder);
}

/* EOF: embed.php */