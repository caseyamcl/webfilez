<?php

require_once(__DIR__ . '/../Reqresp/Response.php');

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
    $this->assertInstanceOf('Reqresp\Response', new Reqresp\Response);
  }

  // --------------------------------------------------------------

  public function testSetStatusSetsDefaultMessageForKnownCode() {

    $obj = new ReqResp\Response();
    $obj->setStatus('413');
    $this->assertEquals('Request Entity Too Large', $obj->statusText);

  }

  // --------------------------------------------------------------

  public function testSetStatusSetsCustomMessageWhenSpecified() {

    $obj = new ReqResp\Response();
    $obj->setStatus('413', 'Too Big');
    $this->assertEquals('Too Big', $obj->statusText);

  }

  // --------------------------------------------------------------

  public function testSetStatusThrowsExceptionForNonnumericCode() {

    $obj = new ReqResp\Response();
    $this->setExpectedException("InvalidArgumentException");
    $obj->setStatus('barg');
  }

  // --------------------------------------------------------------

  public function testSetStatusThrowsExceptionForUnknownCode() {

    $obj = new ReqResp\Response();
    $this->setExpectedException("InvalidArgumentException");
    $obj->setStatus('599');

  }

  // --------------------------------------------------------------

  public function testSetHeaderAddsHeaderToArray() {

    $obj = new ReqResp\Response();
    $obj->setHeader("Custom-Header: value");
    $this->assertContains("Custom-Header: value", $obj->headers);

  }

  // --------------------------------------------------------------

  public function testSetBodySetsBodyAsString() {

    $obj = new ReqResp\Response();
    $obj->setBody("<h1>Hello</h1>");
    $this->assertEquals("<h1>Hello</h1>", $obj->contentBody);
  }

  // --------------------------------------------------------------

  public function testSetContentTypeSetsContentTypeAsString() {
    $obj = new ReqResp\Response();
    $obj->setContentType("text/plain");
    $this->assertEquals('text/plain', $obj->contentType);
  }

  // --------------------------------------------------------------

  public function assertDefaultStatusIs200OK() {
    $obj = new ReqResp\Response();
    $this->assertEquals(200, $obj->statusCode);
    $this->assertEquals('OK', $obj->statusText);
  }

  // --------------------------------------------------------------

  public function testDefaultContentTypeIsTextHtml() {

    $obj = new ReqResp\Response();
    $this->assertEquals('text/html', $obj->contentType);
  }

  // --------------------------------------------------------------

  public function testOutputHeadersProducesExpectedHeaderList() {

    $obj = new ReqResp\Response();
    $obj->setStatus(201);
    $obj->setHeader("Custom-Header: value");
    $obj->setHeader("Another-Custom: anotherval");

    $expArray = array(
      array('HTTP/1.1 201 Created', true, 201),
      array('Content-type: text/html'),
      array('Custom-Header: value'),
      array('Another-Custom: anotherval')
    );

    $this->assertEquals($expArray, $obj->printHeaders(true));

  }

  // --------------------------------------------------------------

  public function testGoProducesExpectedOutputForContentOutput() {

    $obj = new ReqResp\Response();
    $obj->setBody("abc123");
    $this->assertEquals('abc123', $obj->go(true));
  }

  // --------------------------------------------------------------

  public function testGoProducesExpectedOutputForFileOutput() {

    $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'reqresptest_outfile.tmp';
    file_put_contents($filePath, 'def456');
    $obj = new ReqResp\Response();
    $obj->setBody($filePath, ReqResp\Response::FILEPATH);
    $this->assertEquals('def456', $obj->go(true));
    unlink($filePath);
  }

}

/* EOF: ResponseTest.php */
