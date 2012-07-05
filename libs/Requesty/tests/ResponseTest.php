<?php

require_once(__DIR__ . '/../Response.php');

class ResponseTest extends PHPUnit_Framework_TestCase {
  
  // --------------------------------------------------------------
  
  function setUp()
  {
    parent::setUp();
  }

  // --------------------------------------------------------------

  function tearDown()
  {
    parent::tearDown();
  } 
  
  // --------------------------------------------------------------

  public function testInstantiateAsObjectSucceeds() {
    
    $this->assertInstanceOf('Requesty\Response', new Requesty\Response);
  }

}

/* EOF: ResponseTest.php */
