<?php

require_once(__DIR__ . '/../Request.php');

class RequestTest extends PHPUnit_Framework_TestCase {

  private $orig_server_array;
  
  // --------------------------------------------------------------
  
  function setUp()
  {
    parent::setUp();
    $this->orig_server_array = $_SERVER;
  }

  // --------------------------------------------------------------

  function tearDown()
  {
    $_SERVER = $this->orig_server_array;
    parent::tearDown();
  } 
  
  // --------------------------------------------------------------

  public function testInstantiateAsObjectSucceeds() {
    
    $obj = $this->get_request_obj();
    $this->assertInstanceOf('Requesty\Request', $obj);
  }
  
  // --------------------------------------------------------------
  
  /**
   * @depends testInstantiateAsObjectSucceeds
   */
  public function testGetBrowserReturnsCorrectObject() {
    
    $obj = $this->get_request_obj();
    $expected = $this->get_browscap_stub()->getBrowser()->Browser;
    $this->assertEquals($obj->get_browser(), $expected);
  }
  
  // --------------------------------------------------------------
  
  public function testGetIpAddressMatchesTestIP() {
    
    $_SERVER['REMOTE_ADDR'] = '10.1.1.1';
    
    $obj = $this->get_request_obj();
    $this->assertEquals($obj->get_ip_address(), $_SERVER['REMOTE_ADDR']);
    
  }
  
  // --------------------------------------------------------------

  public function testGetVersionMatchesTestVersion() {
    
    $obj = $this->get_request_obj();
    $this->assertEquals($obj->get_version(), (float) 10);
  }
  
  // --------------------------------------------------------------

  public function testIsCLIReturnsCorrectResponse() {
    
    $obj = $this->get_request_obj();
    $this->assertEquals($obj->is_cli(), (PHP_SAPI == 'cli'));
  }
  
  // --------------------------------------------------------------

  public function testIsAjaxReturnsCorrectResponse() {
    
    $obj = $this->get_request_obj();
    $match = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    $this->assertEquals($obj->is_ajax(), $match);   
  }
  
  // --------------------------------------------------------------
  
  public function testGetLanguagesReturnsCorrectArray() {
    
    $this->override_server_array_http_standard();
    $obj = $this->get_request_obj();
    
    $standard_array = array('en-us', 'en');
    $weighted_array = array('en-us' => 1, 'en' => 0.5);
    
    $this->assertEquals($obj->get_languages(), $standard_array);
    $this->assertEquals($obj->get_languages(TRUE), $weighted_array);
  }
  
  // --------------------------------------------------------------
 
  public function testGetEncodingsReturnsCorrectArray() {
    
    $this->override_server_array_http_standard();
    $obj = $this->get_request_obj();
    
    $match = array('gzip', 'deflate');
    $this->assertEquals($obj->get_accepted_encodings(), $match);
    
  }
 
  // --------------------------------------------------------------
  
  public function testGetCharsetsReturnsCorrectArray() {
    
    $this->override_server_array_http_standard();
    $obj = $this->get_request_obj();    
    
    $standard_array = array('ISO-8859-1', 'utf-8', '*');
    $weighted_array = array('ISO-8859-1' => 1, 'utf-8' => 0.7, '*' => 0.3);
    
    $this->assertEquals($obj->get_accepted_charsets(), $standard_array);
    $this->assertEquals($obj->get_accepted_charsets(TRUE), $weighted_array);    
  }
  
  // --------------------------------------------------------------
  
  public function testGetTypesReturnsCorrectArray() {
    
    $this->override_server_array_http_standard();
    $obj = $this->get_request_obj();  
    
    $standard_array = array('text/html', 'application/xhtml+xml', 'application/xml', '*/*');
    $weighted_array = array(
      'text/html' => 1,
      'application/xhtml+xml' => 1,
      'application/xml' => 0.9,
      '*/*' => 0.8);
    
    $this->assertEquals($obj->get_accepted_types(), $standard_array);
    $this->assertEquals($obj->get_accepted_types(TRUE), $weighted_array); 
  }
  
  // --------------------------------------------------------------
  
  public function testNegotiateLanguagesWithMatch() {

    $req_array = array('en-us' => 1, 'en' => 0.7, 'de' => 0.3, '*' => 0.1);
    $avail_array = array('en', 'de');
    
    $obj = $this->get_request_obj();
    $result = $obj->negotiate($req_array, $avail_array);
    
    $this->assertEquals($result, 'en');
  }
  
  // --------------------------------------------------------------
  
  public function testNegotiateLanguagesWithoutMatchAndStar() {
 
    $req_array = array('en-us' => 1, 'en' => 0.7, 'de' => 0.3, '*' => 0.1);
    $avail_array = array('jp', 'sw');
    
    $obj = $this->get_request_obj();
    $result = $obj->negotiate($req_array, $avail_array);
    
    $this->assertEquals($result, 'jp');
    
  }
  
  // --------------------------------------------------------------
  
  public function testNegotiateContentTypesWithoutMatchAndNoStar() {
 
    $req_array = array('en-us' => 1, 'en' => 0.7, 'de' => 0.3);
    $avail_array = array('jp', 'sw');
    
    $obj = $this->get_request_obj();
    $result = $obj->negotiate($req_array, $avail_array);
    
    $this->assertFalse($result);
  }
  
  // --------------------------------------------------------------
  
  public function testNegotiateContentTypesWithoutMatchAndNoStarAndDefaultVal() {
    
    $req_array = array('en-us' => 1, 'en' => 0.7, 'de' => 0.3);
    $avail_array = array('jp', 'sw');
    
    $obj = $this->get_request_obj();
    $result = $obj->negotiate($req_array, $avail_array, 'barf');
    
    $this->assertEquals($result, 'barf');
    
  }
  
  // --------------------------------------------------------------
  
  public function testGetHeadersReturnsEmptyArrayForIncompleteHttpRequests() {

    //Create custom request
    $_SERVER = array();
    $_SERVER['REDIRECT_STATUS'] = "200";
    $_SERVER['HTTP_0'] = "HTTP_HOST: 192.168.5.5";
    $_SERVER['HTTP_USER_AGENT'] = "Easy HTTP Client Tester (+http://www.urlhere.com/)";
    $_SERVER['PATH'] = "/usr/local/bin:/usr/bin:/bin";
    $_SERVER['SERVER_SIGNATURE'] = "<address>Apache/2.2.20 (Ubuntu) Server at 192.168.5.5 Port 80</address>";
    $_SERVER['SERVER_SOFTWARE'] = "Apache/2.2.20 (Ubuntu)";
    $_SERVER['SERVER_NAME'] = "192.168.5.5";
    $_SERVER['SERVER_ADDR'] = "192.168.5.5";
    $_SERVER['SERVER_PORT'] = "80";
    $_SERVER['REMOTE_ADDR'] = "192.168.5.5";
    $_SERVER['DOCUMENT_ROOT'] = "/home/casey/NetBeansProjects";
    $_SERVER['SERVER_ADMIN'] = "webmaster@localhost";
    $_SERVER['SCRIPT_FILENAME'] = "/home/casey/NetBeansProjects/casey-sandbox/projects/testapi/index.php";
    $_SERVER['REMOTE_PORT'] = "48943";
    $_SERVER['REDIRECT_URL'] = "/casey-sandbox/projects/testapi/";
    $_SERVER['GATEWAY_INTERFACE'] = "CGI/1.1";
    $_SERVER['SERVER_PROTOCOL'] = "HTTP/1.0";
    $_SERVER['REQUEST_METHOD'] = "GET";
    $_SERVER['QUERY_STRING'] = "";
    $_SERVER['REQUEST_URI'] = ' /casey-sandbox/projects/testapi/';
    $_SERVER['SCRIPT_NAME'] = "/casey-sandbox/projects/testapi/index.php";
    $_SERVER['PATH_INFO'] = "/";
    $_SERVER['PATH_TRANSLATED'] = "/home/casey/NetBeansProjects/index.php";
    $_SERVER['PHP_SELF'] = "/casey-sandbox/projects/testapi/index.php/";
    $_SERVER['REQUEST_TIME'] = "1333052626";
    $obj = $this->get_request_obj();  
    
    $this->assertEquals(array(), $obj->get_accepted_charsets());
    $this->assertEquals(array(), $obj->get_accepted_encodings());
    $this->assertEquals(array(), $obj->get_accepted_types());
    $this->assertEquals(array(), $obj->get_languages());
  }
  
  // --------------------------------------------------------------
  
  private function get_request_obj() {

    return new \Requesty\Request($this->get_browscap_stub());
  }

  // --------------------------------------------------------------
  
  private function get_browscap_stub() {
    
    return (class_exists('BrowscapStub')) ? new BrowscapStub : new Browscap;
  }
  
  // --------------------------------------------------------------
  
  private function override_server_array_http_standard() {
    
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
    $_SERVER[''] = "192.168.5.10";
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

// =============================================================================

if (class_exists('Browscap')) {

  class BrowscapStub extends Browscap {

    public function __construct() {
      //nuthin
    }

    public function getBrowser() {
      $browser_obj = "Tzo4OiJzdGRDbGFzcyI6Mjk6e3M6MTI6ImJyb3dzZXJfbmFtZSI7czo4MDoiTW96aWxsYS81LjAgKFgxMTsgVWJ1bnR1OyBMaW51eCB4ODZfNjQ7IHJ2OjEwLjAuMikgR2Vja28vMjAxMDAxMDEgRmlyZWZveC8xMC4wLjIiO3M6MTg6ImJyb3dzZXJfbmFtZV9yZWdleCI7czo1MjoiXm1vemlsbGEvNVwuMCBcKC4qbGludXguKlwpIGdlY2tvLy4qIGZpcmVmb3gvMTBcLi4qJCI7czoyMDoiYnJvd3Nlcl9uYW1lX3BhdHRlcm4iO3M6NDI6Ik1vemlsbGEvNS4wICgqTGludXgqKSBHZWNrby8qIEZpcmVmb3gvMTAuKiI7czo2OiJQYXJlbnQiO3M6MTI6IkZpcmVmb3ggMTAuMCI7czo4OiJQbGF0Zm9ybSI7czo1OiJMaW51eCI7czo1OiJXaW4zMiI7YjowO3M6NzoiQnJvd3NlciI7czo3OiJGaXJlZm94IjtzOjc6IlZlcnNpb24iO3M6NDoiMTAuMCI7czo4OiJNYWpvclZlciI7aToxMDtzOjY6IkZyYW1lcyI7YjoxO3M6NzoiSUZyYW1lcyI7YjoxO3M6NjoiVGFibGVzIjtiOjE7czo3OiJDb29raWVzIjtiOjE7czoxMDoiSmF2YVNjcmlwdCI7YjoxO3M6MTE6IkphdmFBcHBsZXRzIjtiOjE7czoxMDoiQ3NzVmVyc2lvbiI7aTozO3M6ODoiTWlub3JWZXIiO2k6MDtzOjU6IkFscGhhIjtiOjA7czo0OiJCZXRhIjtiOjA7czo1OiJXaW4xNiI7YjowO3M6NToiV2luNjQiO2I6MDtzOjE2OiJCYWNrZ3JvdW5kU291bmRzIjtiOjA7czo4OiJWQlNjcmlwdCI7YjowO3M6MTU6IkFjdGl2ZVhDb250cm9scyI7YjowO3M6ODoiaXNCYW5uZWQiO2I6MDtzOjE0OiJpc01vYmlsZURldmljZSI7YjowO3M6MTk6ImlzU3luZGljYXRpb25SZWFkZXIiO2I6MDtzOjc6IkNyYXdsZXIiO2I6MDtzOjEwOiJBb2xWZXJzaW9uIjtpOjA7fQ==";
      return unserialize(base64_decode($browser_obj));  
    }
  }
}
else
{
  class Browscap {
    
    public function __construct() {
      //nuthin
    }

    public function getBrowser() {
      $browser_obj = "Tzo4OiJzdGRDbGFzcyI6Mjk6e3M6MTI6ImJyb3dzZXJfbmFtZSI7czo4MDoiTW96aWxsYS81LjAgKFgxMTsgVWJ1bnR1OyBMaW51eCB4ODZfNjQ7IHJ2OjEwLjAuMikgR2Vja28vMjAxMDAxMDEgRmlyZWZveC8xMC4wLjIiO3M6MTg6ImJyb3dzZXJfbmFtZV9yZWdleCI7czo1MjoiXm1vemlsbGEvNVwuMCBcKC4qbGludXguKlwpIGdlY2tvLy4qIGZpcmVmb3gvMTBcLi4qJCI7czoyMDoiYnJvd3Nlcl9uYW1lX3BhdHRlcm4iO3M6NDI6Ik1vemlsbGEvNS4wICgqTGludXgqKSBHZWNrby8qIEZpcmVmb3gvMTAuKiI7czo2OiJQYXJlbnQiO3M6MTI6IkZpcmVmb3ggMTAuMCI7czo4OiJQbGF0Zm9ybSI7czo1OiJMaW51eCI7czo1OiJXaW4zMiI7YjowO3M6NzoiQnJvd3NlciI7czo3OiJGaXJlZm94IjtzOjc6IlZlcnNpb24iO3M6NDoiMTAuMCI7czo4OiJNYWpvclZlciI7aToxMDtzOjY6IkZyYW1lcyI7YjoxO3M6NzoiSUZyYW1lcyI7YjoxO3M6NjoiVGFibGVzIjtiOjE7czo3OiJDb29raWVzIjtiOjE7czoxMDoiSmF2YVNjcmlwdCI7YjoxO3M6MTE6IkphdmFBcHBsZXRzIjtiOjE7czoxMDoiQ3NzVmVyc2lvbiI7aTozO3M6ODoiTWlub3JWZXIiO2k6MDtzOjU6IkFscGhhIjtiOjA7czo0OiJCZXRhIjtiOjA7czo1OiJXaW4xNiI7YjowO3M6NToiV2luNjQiO2I6MDtzOjE2OiJCYWNrZ3JvdW5kU291bmRzIjtiOjA7czo4OiJWQlNjcmlwdCI7YjowO3M6MTU6IkFjdGl2ZVhDb250cm9scyI7YjowO3M6ODoiaXNCYW5uZWQiO2I6MDtzOjE0OiJpc01vYmlsZURldmljZSI7YjowO3M6MTk6ImlzU3luZGljYXRpb25SZWFkZXIiO2I6MDtzOjc6IkNyYXdsZXIiO2I6MDtzOjEwOiJBb2xWZXJzaW9uIjtpOjA7fQ==";
      return unserialize(base64_decode($browser_obj));  
    }   
  }
}

/* EOF: Request.php */
