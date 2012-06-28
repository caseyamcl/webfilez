<?php

namespace Requesty;

/**
 * ErrorWrapper Class converts PHP errors into exceptions 
 */
class ErrorWrapper {

  // --------------------------------------------------------------		

  /**
   * Statically invoke the ErrorWrapper
   */
  public static function invoke() {
    $that = new ErrorWrapper();
    return $that->setup();
  }
  
  // --------------------------------------------------------------		

  /**
   * Setup the error handling
   * 
   * @return \Requesty\ErrorWrapper 
   */
  public function setup() {

    //Set it up
    //ini_set('display_errors', 'Off');
    //error_reporting(-1);
    
    //Set the error handler to this
    set_error_handler(array($this, 'handle_error'));
    
    //Register a shutdown function
    register_shutdown_function(array($this, 'handle_shutdown'));
    
    return $this;
  }

	// --------------------------------------------------------------		
  
  public function __destruct() {
    restore_error_handler();
    restore_exception_handler();
  }
  
	// --------------------------------------------------------------		

  public function handle_error($errno, $errstr, $errfile, $errline, $errcontext = NULL) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
  } 
  
	// --------------------------------------------------------------		
  
  public function handle_shutdown() {
    
    $error = error_get_last();
    
    if ($error) {
      call_user_func(array($this, 'handle_error'), $error['type'], $error['message'], $error['file'], $error['line']);
    }
  }
}

/* EOF: ErrorWrapper */