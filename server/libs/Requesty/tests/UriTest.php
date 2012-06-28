<?php

require_once(__DIR__ . '/../Uri.php');

class UriTest extends PHPUnit_Framework_TestCase {

  private $val;

  // --------------------------------------------------------------

  public function testIsHttpsReturnsValidResult() {
    
    $this->assertFalse($this->get_uri_obj()->is_https());
    $this->assertTrue($this->get_uri_obj(TRUE)->is_https());
 
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryItemReturnsItemThatExists() {
   
    $this->assertEquals('thing', $this->get_uri_obj()->get_query_item('some'));
    $this->assertEquals('thing', $this->get_uri_obj()->get_query_item('another'));
    
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryItemReturnsFalseForNonexistentItem() {
    
    $this->assertFalse($this->get_uri_obj()->get_query_item('blarg'));    
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryItemReturnsFalseWhenNoQueryStringExists() {

    $this->assertFalse($this->get_uri_obj(FALSE, FALSE)->get_query_item('some'));
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryArrayReturnsCorrectArray() {
    
    $match = array('some' => 'thing', 'another' => 'thing');
    $this->assertEquals($match, $this->get_uri_obj()->get_query_array());
  }
 
  // --------------------------------------------------------------
 
  public function testGetQueryArrayReturnsEmptyArrayForNoQueryString() {

    $this->assertEmpty($this->get_uri_obj(FALSE, FALSE)->get_query_array());   
  }
  
  // --------------------------------------------------------------
   
  public function testGetQueryReturnsCorrectQueryString() {
    
    $http_info = $this->get_http_server_array();
    
    $this->assertEquals($http_info['QUERY_STRING'], $this->get_uri_obj()->get_query());
  }
  
  // --------------------------------------------------------------
  
  public function testGetQueryReturnsEmptyStringForNoQueryString() {
     
    $this->assertEmpty($this->get_uri_obj(FALSE, FALSE)->get_query_array());      
  }
  
  // --------------------------------------------------------------
   
  private function get_uri_obj($https = FALSE, $query_string = TRUE) {
    
    $http_data = ($https) ? $this->get_https_server_array() : $this->get_http_server_array();
    
    //Override query string if necessary
    if ( ! $query_string) {
      $http_data['QUERY_STRING'] = '';
      $http_data['REQUEST_URI'] = substr($http_data['REQUEST_URI'], 0, strpos($http_data['REQUEST_URI'], '?'));
    }
    
    return new Requesty\Uri($http_data);
  }
  
  // --------------------------------------------------------------

  private function get_http_server_array() {
    
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
 
  private function get_https_server_array() {
    
    $server_data = $this->get_http_server_array();
    $server_data['HTTPS'] = "on";
    $server_data['SERVER_PORT'] = "443";    
    $server_data['SSL_TLS_SNI'] = "192.168.5.5";
    
    return $server_data;
  }
}

/* EOF: UriTest.php */