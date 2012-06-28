<?php

require_once('../libs/FileManager/FileManager.php');
require_once('../libs/FileManager/UploadHandler.php');

class UploadHandlerTest extends PHPUnit_Framework_TestCase {

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

  public function testInstantiateAsObjectSucceeds()
  {
    $this->assertInstanceOf('UploadHandler', $this->getObj());
  }

  // --------------------------------------------------------------

  public function testGetMaxFileSizeReturnsAnInt()
  {
    $obj = $this->getObj();
    $this->assertInternalType('int', $obj->getUploadMaxFilesize());
    $this->assertGreaterThan(-2, $obj->getUploadMaxFilesize());
  }

  // --------------------------------------------------------------

  public function testGetUploadStatusReturnsExpectedValue() {

    $obj = $this->getObj();

    $expected = (is_callable('uploadprogress_get_info'))
      ? (object) array('progress_enabled' => TRUE, 'upload_in_progress' => FALSE)
      : (object) array('progress_enabled' => FALSE); 

    $this->assertEquals($expected, $obj->getUploadStatus('1234'));
  }

  // --------------------------------------------------------------

  private function getObj()
  {
    $mockobj = $this->getMock('FileManager', array('__construct', 'putFile'), array(), '', FALSE);
    $mockobj->expects($this->any())->method('__construct');
    $mockobj->expects($this->any())->method('putFile')->will($this->returnValue('foo'));
    return new UploadHandler($mockobj);
  }

}


/* EOF: UploadHandlerTest.php */