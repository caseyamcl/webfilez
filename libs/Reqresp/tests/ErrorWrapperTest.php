<?php

require_once(__DIR__ . '/../Reqresp/ErrorWrapper.php');

class ErrorWrapperTest extends PHPUnit_Framework_TestCase {
  
  public function testConstructSucceeds() {

    $ewrapper = new Reqresp\ErrorWrapper();
    $this->assertInstanceOf('\\Reqresp\\ErrorWrapper', $ewrapper); 

  }

  public function testErrorGetsConvertedToException() {
    $ewrapper = new Reqresp\ErrorWrapper();
    $ewrapper->setup();

    $this->setExpectedException('ErrorException');
    $y = 5 / 0; //divide by zero
  }  
}

/* EOF: UriTest.php */