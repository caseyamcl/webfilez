<?php

require_once(__DIR__ . '/../Reqresp/Request.php');

class RequestTest extends PHPUnit_Framework_TestCase {

  private $origSERVERArray;
  
  // --------------------------------------------------------------
  
  function setUp()
  {
    parent::setUp();
    $this->origSERVERArray = $_SERVER;
  }

  // --------------------------------------------------------------

  function tearDown()
  {
    $_SERVER = $this->origSERVERArray;
    parent::tearDown();
  } 
  
  // --------------------------------------------------------------

  public function testInstantiateAsObjectSucceeds() {
    
    $obj = $this->getReqObj();
    $this->assertInstanceOf('Reqresp\Request', $obj);
  }

  // --------------------------------------------------------------
  
  public function testGetIpAddressMatchesTestIP() {
    
    $_SERVER['REMOTE_ADDR'] = '10.1.1.1';
    
    $obj = $this->getReqObj();
    $this->assertEquals($obj->clientIP, $_SERVER['REMOTE_ADDR']);
    
  }
  
  // --------------------------------------------------------------

  public function testIsAjaxReturnsCorrectResponse() {
    
    $obj = $this->getReqObj();
    $match = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    $this->assertEquals($obj->isAjax, $match);   
  }
  
  // --------------------------------------------------------------
  
  public function testGetLanguagesReturnsCorrectArray() {
    
    $this->overrideServerHTTPArray();
    $obj = $this->getReqObj();
    
    $weighted_array = array('en-us' => 1, 'en' => 0.5);    
    $this->assertEquals($obj->acceptedLanguages, $weighted_array);
  }
  
  // --------------------------------------------------------------
 
  public function testGetEncodingsReturnsCorrectArray() {
    
    $this->overrideServerHTTPArray();
    $obj = $this->getReqObj();
    
    $match = array('gzip', 'deflate');
    $this->assertEquals($obj->acceptedEncodings, $match);
    
  }
 
  // --------------------------------------------------------------
  
  public function testGetCharsetsReturnsCorrectArray() {
    
    $this->overrideServerHTTPArray();
    $obj = $this->getReqObj();    
    
    $weighted_array = array('ISO-8859-1' => 1, 'utf-8' => 0.7, '*' => 0.3);
    
    $this->assertEquals($obj->acceptedCharsets, $weighted_array);    
  }
  
  // --------------------------------------------------------------
  
  public function testGetTypesReturnsCorrectArray() {
    
    $this->overrideServerHTTPArray();
    $obj = $this->getReqObj();  
    
    $weighted_array = array(
      'text/html' => 1,
      'application/xhtml+xml' => 1,
      'application/xml' => 0.9,
      '*/*' => 0.8);
    
    $this->assertEquals($obj->acceptedContentTypes, $weighted_array); 
  }
  
  // --------------------------------------------------------------
  
  public function testNegotiateLanguagesWithMatch() {

    $avail_array = array('en', 'de');
    
    $this->overrideServerHTTPArray();
    $obj = $this->getReqObj();
    $result = $obj->negotiate('language', $avail_array);    
    $this->assertEquals($result, 'en');
  }
  
  // --------------------------------------------------------------
  
  public function testNegotiateLanguagesWithoutMatchAndStar() {
 
    $this->overrideServerHTTPArray();
    $_SERVER['HTTP_ACCEPT_LANGUAGE']= "en-us,en;q=0.5,*";

    $avail_array = array('jp', 'sw');

    $obj = $this->getReqObj();
    $result = $obj->negotiate('language', $avail_array);
    $this->assertEquals($result, 'jp');
    
  }
  
  // --------------------------------------------------------------
  
  public function testNegotiateLanguagesWithoutMatchAndNoStar() {
 
    $avail_array = array('jp', 'sw');    
    $obj = $this->getReqObj();
    $result = $obj->negotiate('language', $avail_array);
    
    $this->assertFalse($result);
  }
  
  // --------------------------------------------------------------

  public function testNegotiateThrowsExceptionForInvalidItem() {

    $obj = $this->getReqObj();
    $this->setExpectedException('InvalidArgumentException');
    $obj->negotiate('blargedyblarg', array('1', '2'));
  }

  // --------------------------------------------------------------

  public function testNegotiateReturnsFalseForEmptyAvailableArray() {

    $obj = $this->getReqObj();
    $this->assertFalse($obj->negotiate('language', array()));

  }

  // --------------------------------------------------------------
  
  public function testNegotiateLanguagesWithoutMatchAndNoStarAndDefaultVal() {
    
    $avail_array = array('jp', 'sw');
    $obj = $this->getReqObj();
    $result = $obj->negotiate('language', $avail_array, 'barf');
    
    $this->assertEquals($result, 'barf');
    
  }
  
  // --------------------------------------------------------------

  public function testGetHeaderReturnsResultCaseInsensitively() {

    $this->overrideServerHTTPArray();
    $obj = $this->getReqObj();
    
    $this->assertEquals('keep-alive', $obj->header('cOnNeCtion'));
  }

  // --------------------------------------------------------------

  public function testGetHeaderReturnsFalseForNonExistentHeader() {

    $this->overrideServerHTTPArray();
    $obj = $this->getReqObj();
    
    $this->assertFalse($obj->header('doesNotExist'));

  }

  // --------------------------------------------------------------

  public function testGetHeaderReturnsDefaultForNonExistentWithDefault() {

    $this->overrideServerHTTPArray();
    $obj = $this->getReqObj();
    
    $this->assertEquals('booyah', $obj->header('doesNotExist', 'booyah'));

  }

  // --------------------------------------------------------------
  
  private function getReqObj() {

    return new \Reqresp\Request();
  }

  // --------------------------------------------------------------
  
  private function overrideServerHTTPArray() {
    
    $_SERVER = array();
    $_SERVER['HTTP_HOST'] = "192.168.5.5";
    $_SERVER['HTTP_USER_AGENT'] = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2";
    $_SERVER['HTTP_ACCEPT'] = "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $_SERVER['HTTP_ACCEPT_LANGUAGE']= "en-us,en;q=0.5";
    $_SERVER['HTTP_ACCEPT_ENCODING']= "gzip, deflate";
    $_SERVER['HTTP_ACCEPT_CHARSET'] = "ISO-8859-1,utf-8;q=0.7,*;q=0.3";
    $_SERVER['HTTP_CONNECTION']= "keep-alive";
    $_SERVER['HTTP_COOKIE']= "";
    $_SERVER['PATH']= "/usr/local/bin:/usr/bin:/bin";
    $_SERVER['SERVER_SIGNATURE'] = "<address>Apache/2.2.20 (Ubuntu) Server at 192.168.5.5 Port 80</address>";
    $_SERVER['SERVER_SOFTWARE'] = "Apache/2.2.20 (Ubuntu)";
    $_SERVER['SERVER_NAME'] = "192.168.5.5";
    $_SERVER['SERVER_ADDR'] = "192.168.5.5";
    $_SERVER['SERVER_PORT'] = "80";
    $_SERVER['REMOTE_ADDR'] = "192.168.5.10";
    $_SERVER['DOCUMENT_ROOT'] = "/var/www";
    $_SERVER['SERVER_ADMIN'] = "webmaster@localhost";
    $_SERVER['SCRIPT_FILENAME'] = "/var/www/casey-sandbox/scratchpad/test.php";
    $_SERVER['REMOTE_PORT'] = "55932";
    $_SERVER['GATEWAY_INTERFACE'] = "CGI/1.1";
    $_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
    $_SERVER['REQUEST_METHOD'] = "GET";
    $_SERVER['QUERY_STRING'] = "some=thing&another=thing";
    $_SERVER['REQUEST_URI'] = "/casey-sandbox/scratchpad/test.php/some/path?some=thing&another=thing";
    $_SERVER['SCRIPT_NAME'] = "/casey-sandbox/scratchpad/test.php";
    $_SERVER['PHP_SELF'] = "/casey-sandbox/scratchpad/test.php/some/path";
    $_SERVER['REQUEST_TIME'] = 1330720994;    
  }
}

/* EOF: Request.php */
