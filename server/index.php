<?php

/**
 * @file Webfilez Main Server-Side Execution File 
 */

class Webfilez {

  /**
   * @var Slim
   */
  private $app;
  
  /**
   * @var FileManager
   */
  private $filemanager;
  
  /**
   * @var UploadHandler
   */
  private $uploadhandler;
  
  // ------------------------------------------------------------------------
  
  /**
   * JFile Main Exeuction Runner 
   */
  public static function main() {
    $that = new Jfile();
    $that->run();
  }
  
  // ------------------------------------------------------------------------
  
  /**
   * JFile Main Execution Script
   */
  private function run() {
    
    //Basepath
    define('BASEPATH' , __DIR__ . DIRECTORY_SEPARATOR);
    
    //Load files
    require(BASEPATH . 'libs/Slim/Slim.php');
    require(BASEPATH . 'libs/FileManager/FileManager.php');
    require(BASEPATH . 'libs/FileManager/UploadHandler.php');    
    require(BASEPATH . 'hooks/hooks.php');
    
    //Load Slim App
    $this->app = new Slim();
    
    //Get the folder to work from
    if ( !is_callable('get_user_folder')) {
      throw new Exception("get_user_folder API hook not defined!");
    }
    $user_folder = get_user_folder();
    
    //Load the File Manager and upload handler
    $this->filemanager = new FileManager($user_folder);
    $this->uploadhandler = new UploadHandler($this->filemanager);
    
    //Build routes
    $this->app->get('/', function() {

      //If AJAX, just send back some information about the environment
      die("Load Client");

    });

    $this->app->post('/upload', function() {
      die("Uploading!");
    });

    $this->app->get('/uploadstatus/:id', function($upload_id) {
      die("Get Upload Status!");
    });

    $this->app->get('/serverinfo', function() {
      die("Get server info object!");
    });

    $this->app->map('/files(/:path)', function($path = NULL) {
      die("Will do stuff based on the HTTP verb and path ($path)");
    })->via('GET', 'POST', 'PUT', 'DELETE');

    /*
    * Go!
    */
    $this->app->run();    
  }
  
}

// =============================================================================

//go!
Jfile::main();

/* EOF: index.php */