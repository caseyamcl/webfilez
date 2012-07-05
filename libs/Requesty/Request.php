<?php

namespace Requesty;

/**
 * HTTP Client Class
 * 
 * @author Casey McLaughlin
 */
class Request
{
	/**
	 * @var \Browscap $browscap
	 */
	private $browscap;
	
  /**
   * @var array $http_req_headers 
   */
  private $http_req_headers = array();
  
	// --------------------------------------------------------------		

	/**
	 * Constructor
	 * 
	 * @param Browscap $browscap 
	 */
	public function __construct(\Browscap $browscap)
	{
		//Load dependencies
		$this->browscap = $browscap;
    
        //Load custom headers
        $this->http_req_headers = $this->parse_request_headers();
	}
	
	// --------------------------------------------------------------		

	/**
	 * Get the name of the browser
	 * 
	 * @return string 
	 */
	public function get_browser()
	{
		return $this->get_user_agent()->Browser;
	}

	// --------------------------------------------------------------		
	
	/**
	 * Get the client's IP address
	 * 
	 * @return string
	 */
	public function get_ip_address()
	{
		return $_SERVER['REMOTE_ADDR'];
	}

	// --------------------------------------------------------------		
	
	/**
	 * Get the client's browser (or other HTTP client) version
	 * 
	 * @return float
	 */
	public function get_version()
	{
		return (float) $this->get_user_agent()->Version;
	}

    // --------------------------------------------------------------		
  
    /**
     * Get the request header or the default (if the header is not set)
     * 
     * @param string $header_key 
     * @return string|null
     */
    public function get_header($header_key, $default = NULL) {
    
        return (isset($this->http_req_headers[$header_key]))
            ? $this->http_req_headers[$header_key] : $default;
    }
  
    // --------------------------------------------------------------       

    public function get_headers()
    {
        return $this->http_req_headers;
    }

    // --------------------------------------------------------------       

    public function get_method() {
        return $_SERVER['REQUEST_METHOD'];
    }
    

	// --------------------------------------------------------------		
	
	/**
	 * Get the user agent
	 * 
	 * @param boolean $raw 
	 * If TRUE, will return the raw user agent
	 * 
	 * @return string|array|object
	 * Return an object or array of key/value pairs if $raw = FALSE, otherwise a string
	 */
	public function get_user_agent($raw = FALSE, $as_array = TRUE)
	{		
		return ($raw) ? $_SERVER['HTTP_USER_AGENT'] : $this->browscap->getBrowser(NULL, $as_array);
	}
	
	// --------------------------------------------------------------		

	/**
	 * Returns TRUE if is CLI, FALSE if otherwise
	 * 
	 * @return boolean
	 */
	public function is_cli()
	{
		return (php_sapi_name() == 'cli');
	}
	
	// --------------------------------------------------------------		

	/**
	 * Returns TRUE if is an AJAX request, FALSE if otherwise
	 * 
	 * @return boolean
	 */
	public function is_ajax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');		
	}
	
	// --------------------------------------------------------------		

	/**
	 * Return an array of preferred languages from the client
	 * 
	 * @param boolean $include_weights
	 * @return array
	 */
	public function get_languages($include_weights = FALSE)
	{
		//Get accepted languages from HTTP header
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
  		$langs = $this->_unserialize_header($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    else
      $langs = array();
				
		//Return them
		return ($include_weights) ? $langs : array_keys($langs);
	}
	
	// --------------------------------------------------------------

	/**
	 * Return an array of accepted encodings from the client
	 * 
	 * @return array
	 */
	public function get_accepted_encodings()
	{
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
      return array_keys($this->_unserialize_header($_SERVER['HTTP_ACCEPT_ENCODING']));
    else
      return array();
	}
	
	// --------------------------------------------------------------		

	/**
	 * Return an array of accepted charsets the client accepts
	 * 
	 * @param boolean $include_weights
	 * @return array
	 */
	public function get_accepted_charsets($include_weights = FALSE)
	{
    if (isset($_SERVER['HTTP_ACCEPT_CHARSET']))
  		$charsets = $this->_unserialize_header($_SERVER['HTTP_ACCEPT_CHARSET']);
    else
      $charsets = array();
		
		return ($include_weights) ? $charsets : array_keys($charsets);
	}
	
	// --------------------------------------------------------------		

	/**
	 * Return an array of accepted types the client accepts
	 * 
	 * @param boolean $include_weights
	 * @return array
	 */
	public function get_accepted_types($include_weights = FALSE)
	{
    if (isset($_SERVER['HTTP_ACCEPT']))
  		$types = $this->_unserialize_header($_SERVER['HTTP_ACCEPT']);
    else
      $types = array();
		
		return ($include_weights) ? $types : array_keys($types);
	}

	// --------------------------------------------------------------		

  /**
   * Negotiate a content-type, language, etc. from a request header
   *
   * The $requested paramater should be in the format:
   * key is type/language/whatever
   * value is the weight (between 0 and 1)
   * 
   * Example:
   *  array(
   *    'en-us' => 1
   *    'en'    => 0.8
   *    'de'    => 0.5
   *    '*'     => 0
   *  ); 
   * 
   * 
   * @param array $requested  Requesed Items (name is key / value is weight)
   * @param array $available  Available items, in descending priority order
   * @param string|boolean $default  The default to send back if no match.  FALSE if not set
   * If NULL, send back the first item in the $available array
   */
  public function negotiate($requested, $available, $default = FALSE) {
    
    //Manipulate the requested array
    asort($requested);
    $requested = array_reverse($requested);
    
    //Look for a match
    foreach($requested as $item => $weight) {
      if (in_array($item, $available))
        return $item;      
    }
    
    //No match - Go with the first item in avaialble if '*' or '*/*' sent
    if ( ! $default && (in_array('*', array_keys($requested)) OR in_array('*/*', array_keys($requested)))) {
      return array_shift($available);
    }
    else {
      return $default;
    }
  }  
  	
  // --------------------------------------------------------------		
  
  private function parse_request_headers() {
    
    $headers = array();
    
    foreach($_SERVER as $key => $value) {
      if (substr($key, 0, 5) == 'HTTP_') {
        
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
      }
    }
    
    return $headers;    
  }
  
	// --------------------------------------------------------------		
  
	/**
	 * Unserialize a Header
	 * 
	 * @param string $header 
	 * @return array
	 */
	private function _unserialize_header($header)
	{
		$items = array_map('trim', explode(',', $header));
    
		//Output
		$output = array();
		
		foreach($items as $item)
		{     
			if (strpos($item, ';') !== FALSE)
				list($val, $weight) = array_map('trim', explode(';', $item));
			else
				list($val, $weight) = array($item, 'q=1');
			
			$weight = substr($weight, 2); //pop 'q=' off the front of the quality
			
			$output[$val] = $weight;
		}

		//Sort desc by weight
		asort($output);
		$output = array_reverse($output);
		
		return $output;
	}
}

/* EOF: Request.php */