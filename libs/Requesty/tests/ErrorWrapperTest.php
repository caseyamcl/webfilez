<?php

require_once(__DIR__ . '/../ErrorWrapper.php');

class ErrorWrapperTest extends PHPUnit_Framework_TestCase {
  
  public function testConstructSucceeds() {
    
    $ewrapper = new Requesty\ErrorWrapper();
    $this->assertInstanceOf('\\Requesty\\ErrorWrapper', $ewrapper);
    
  }
  
}

/* EOF: UriTest.php */