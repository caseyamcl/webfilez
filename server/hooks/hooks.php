<?php

/**
 * @file Hooks for jFile
 * 
 * Replace these with your own hooks
 */

// ----------------------------------------------------------------

/**
 * Get User Folder
 * 
 * Determines the base path to use for the jFile interface
 * 
 * @return string    A full path to use
 * @throws RuntimeException If the path can't be determined, an Exception is thrown
 */
function get_user_folder() {
  
  return '/home/casey/uploadtest/';
}


/* EOF: hooks.php */