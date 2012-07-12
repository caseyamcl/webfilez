<?php

require_once(__DIR__ . '/../Reqresp/Uri.php');

class UriTest extends PHPUnit_Framework_TestCase {

  private $val;

  // --------------------------------------------------------------

  public function testIsHttpsReturnsValidResult() {
    
    $this->assertFalse($this->getObj()->https);
    $this->assertTrue($this->getObj(TRUE)->https);
 
  }
  
  // --------------------------------------------------------------
  
  public function testAllValuesSetUponInstantiation() {
    $obj = $this->getObj();
    $this->assertFalse($obj->https);
    $this->assertEquals('http', $obj->protocol);
    $this->assertEquals(80, $obj->port);
    $this->assertEquals('192.168.5.5', $obj->hostname);
    $this->assertEquals('192.168.5.5', $obj->hostIP);
    $this->assertEquals('/casey-sandbox/scratchpad/', $obj->basepath);
    $this->assertEquals('some/path', $obj->path);
    $this->assertEquals('some=thing&another=thing', $obj->query);
    $this->assertEquals('http://192.168.5.5/casey-sandbox/scratchpad/', $obj->baseurl);
    $this->assertEquals('http://192.168.5.5/casey-sandbox/scratchpad/test.php/', $obj->appurl);
    $this->assertEquals('http://192.168.5.5/casey-sandbox/scratchpad/test.php/some/path', $obj->currenturl);
    $this->assertEquals('http://192.168.5.5/casey-sandbox/scratchpad/test.php/some/path?some=thing&another=thing', $obj->fullurl);
  }

  // --------------------------------------------------------------

  public function testImmutable() {

  }

  // --------------------------------------------------------------

  public function testGetQueryItemReturnsItemThatExists() {
   
    $this->assertEquals('thing', $this->getObj()->query('some'));
    $this->assertEquals('thing', $this->getObj()->query('another'));
    
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryItemReturnsFalseForNonexistentItem() {
    
    $this->assertFalse($this->getObj()->query('blarg'));    
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryItemReturnsFalseWhenNoQueryStringExists() {

    $this->assertFalse($this->getObj(FALSE, FALSE)->query('some'));
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryArrayReturnsCorrectArray() {
    
    $match = array('some' => 'thing', 'another' => 'thing');
    $this->assertEquals($match, $this->getObj()->query());
  }
 
  // --------------------------------------------------------------
 
  public function testGetQueryArrayReturnsEmptyArrayForNoQueryString() {

    $this->assertEmpty($this->getObj(FALSE, FALSE)->query);   
  }
  
  // --------------------------------------------------------------
   
  public function testGetQueryReturnsCorrectQueryString() {
    
    $http_info = $this->getHttpServerArray();
    
    $this->assertEquals($http_info['QUERY_STRING'], $this->getObj()->query);
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryReturnsEmptyStringForNoQueryString() {
     
    $this->assertEmpty($this->getObj(FALSE, FALSE)->query);      
  }
  
  // --------------------------------------------------------------

  public function testGetPathReturnsFullPathArray() {
    $obj = $this->getObj();
    $this->assertEquals(array(1 => 'some', 2 => 'path'), $obj->path());
  }

  // --------------------------------------------------------------

  public function testGetPathReturnsSegmentIndexedWithValue() {
    $obj = $this->getObj();
    $this->assertEquals('some', $obj->path(1));
    $this->assertEquals('path', $obj->path(2));
    $this->assertFalse($obj->path(3));
    $this->assertFalse($obj->path(4));
  }

  // --------------------------------------------------------------
   
  private function getObj($https = FALSE, $querystring = TRUE) {
    
    $httpData = ($https) ? $this->getHTTPSServerArray() : $this->getHttpServerArray();
    
    //Override query string if necessary
    if ( ! $querystring) {
      $httpData['QUERY_STRING'] = '';
      $httpData['REQUEST_URI'] = substr($httpData['REQUEST_URI'], 0, strpos($httpData['REQUEST_URI'], '?'));
    }
    
    return new Reqresp\Uri($httpData);
  }
  
  // --------------------------------------------------------------

  private function getHttpServerArray() {
    
    $server_data = array();
    
    $server_data['HTTP_HOST'] = "192.168.5.5";
    $server_data['HTTP_USER_AGENT'] = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2";
    $server_data['HTTP_ACCEPT'] = "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $server_data['HTTP_ACCEPT_LANGUAGE']= "en-us,en;q=0.5";
    $server_data['HTTP_ACCEPT_ENCODING']= "gzip, deflate";
    $server_data['HTTP_ACCEPT_CHARSET'] = "ISO-8859-1,utf-8;q=0.7,*;q=0.3";
    $server_data['HTTP_CONNECTION']= "keep-alive";
    $server_data['HTTP_COOKIE']= "";
    $server_data['PATH']= "/usr/local/bin:/usr/bin:/bin";
    $server_data['SERVER_SIGNATURE'] = "<address>Apache/2.2.20 (Ubuntu) Server at 192.168.5.5 Port 80</address>";
    $server_data['SERVER_SOFTWARE'] = "Apache/2.2.20 (Ubuntu)";
    $server_data['SERVER_NAME'] = "192.168.5.5";
    $server_data['SERVER_ADDR'] = "192.168.5.5";
    $server_data['SERVER_PORT'] = "80";
    $server_data['REMOTE_ADDR'] = "192.168.5.10";
    $server_data['DOCUMENT_ROOT'] = "/var/www";
    $server_data['SERVER_ADMIN'] = "webmaster@localhost";
    $server_data['SCRIPT_FILENAME'] = "/var/www/casey-sandbox/scratchpad/test.php";
    $server_data['REMOTE_PORT'] = "55932";
    $server_data['GATEWAY_INTERFACE'] = "CGI/1.1";
    $server_data['SERVER_PROTOCOL'] = "HTTP/1.1";
    $server_data['REQUEST_METHOD'] = "GET";
    $server_data['QUERY_STRING'] = "some=thing&another=thing";
    $server_data['REQUEST_URI'] = "/casey-sandbox/scratchpad/test.php/some/path?some=thing&another=thing";
    $server_data['SCRIPT_NAME'] = "/casey-sandbox/scratchpad/test.php";
    $server_data['PHP_SELF'] = "/casey-sandbox/scratchpad/test.php/some/path";
    $server_data['REQUEST_TIME'] = 1330720994;
     
    return $server_data;
  }
  
  // --------------------------------------------------------------
 
  private function getHTTPSServerArray() {
    
    $server_data = $this->getHttpServerArray();
    $server_data['HTTPS'] = "on";
    $server_data['SERVER_PORT'] = "443";    
    $server_data['SSL_TLS_SNI'] = "192.168.5.5";
    
    return $server_data;
  }
}

/* EOF: UriTest.php */