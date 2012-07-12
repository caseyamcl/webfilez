<?php

namespace Reqresp;

/**
 * HTTP Client Class
 * 
 * @author Casey McLaughlin
 */
class Request
{
    /**
     * @var string
     */
    protected $clientIP;

    /**
     * @var boolean
     */
    protected $isAjax;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var array
     */
    protected $acceptedLanguages;

    /**
     * @var array
     */
    protected $acceptedCharsets;

    /**
     * @var array
     */
    protected $acceptedEncodings;

    /**
     * @var array
     */
    protected $acceptedContentTypes;

    /**
     * @var int
     */
    protected $contentLength;

    /**
     * @var array
     */
    protected $headers;

    // --------------------------------------------------------------        

    /**
     * Constructor
     * 
     * @param array $serverData
     * null unless testing
     */
    public function __construct($serverData = null)
    {    
        $this->init($serverData);
    }
     
    // --------------------------------------------------------------

    /** 
     * Magic method to retrieve protected properties
     */  
    public function __get($val) {

        return $this->$val;
    }

    // --------------------------------------------------------------        

    /**
     * Get all headers
     *
     * Attempt to use the built-in Apache function, but fall back
     * on (mostly reliable) manual method if necessary
     *
     * @return array
     */
    private function determineHeaders($serverData)
    {
        if (is_callable('getallheaders')) {
            $headers = getallheaders();
        }
        else {
            $headers = array();
            foreach ($serverData as $name => $value) {
               if (substr($name, 0, 5) == 'HTTP_')  {
                   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
               }
           }
        }

        //Set the headers property to an empty array to start
        $this->headers = array();

        //Headers are case-insensitive, so set them all to lower-case
        foreach($headers as $k => $v) {
            $this->headers[strtolower($k)] = $v;
        }
    }

    // --------------------------------------------------------------        

    /**
     * Retrieve a header or return a default value
     *
     * @param string $key
     * @param string $default
     * @return string|false
     */
    public function header($key = null, $default = false)
    {
        $key = strtolower($key);
        return (isset($this->headers[$key])) ? $this->headers[$key] : $default;
    }

    // --------------------------------------------------------------        

    /**
     * Negotiate contentType, language, encoding, or charset from request
     *
     * 
     * @param string $which
     * 'contentType', 'language', 'encoding', or 'charsets
     *
     * @param array $available
     * Available items, in descending priority order
     *
     * @param string|boolean $default
     * The default to send back if no match (false).
     * If there is a '*', false will evaluate to the first item in $available
     */
    public function negotiate($which, $available, $default = false) {
    
        if ( ! in_array((string) $which, array('contentType', 'encoding', 'charset', 'language'))) {
            throw new \InvalidArgumentException("Invalid value to negotiate");
        }

        $paramName = 'accepted' . ucfirst($which) . 's';
        $requested = $this->$paramName;

        //Manipulate the requested array
        asort($requested);
        $requested = array_reverse($requested);

        //Check available
        if (empty($available)) {
            return false;
        }
        
        //Look for a match
        foreach($requested as $item => $weight) {
            if (in_array($item, $available)) {
                return $item;      
            }
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

    /**
     * Initialize data - Runs only once
     *
     * @param array $serverData
     */
    private function init($serverData = null)
    {
        //Default to server array for most data
        if (is_null($serverData)) {
            $serverData = $_SERVER;
        }

        //Basics
        $this->clientIP      = isset($serverData['REMOTE_ADDR']) ? $serverData['REMOTE_ADDR'] : NULL;
        $this->method        = isset($serverData['REQUEST_METHOD']) ? $serverData['REQUEST_METHOD'] : NULL;
        $this->userAgent     = isset($serverData['HTTP_USER_AGENT']) ? $serverData['HTTP_USER_AGENT'] : NULL;
        $this->isAjax        = (isset($serverData['HTTP_X_REQUESTED_WITH']) && $serverData['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
        $this->contentLength = isset($serverData['CONTENT_LENGTH']) ? $serverData['CONTENT_LENGTH'] : FALSE;

        //Accepted Charsets
        $this->acceptedCharsets = (isset($_SERVER['HTTP_ACCEPT_CHARSET']))
            ? $this->unserializeHeader($_SERVER['HTTP_ACCEPT_CHARSET'])
            : array();

        //Accepted Types
        $this->acceptedContentTypes = (isset($_SERVER['HTTP_ACCEPT']))
            ? $this->unserializeHeader($_SERVER['HTTP_ACCEPT'])
            : array();

        //Accepted Encodings
        $this->acceptedEncodings = (isset($serverData['HTTP_ACCEPT_ENCODING']))
            ? array_keys($this->unserializeHeader($_SERVER['HTTP_ACCEPT_ENCODING']))
            : array();

        //Accepted Languages
        $this->acceptedLanguages = (isset($serverData['HTTP_ACCEPT_LANGUAGE']))
            ? $this->unserializeHeader($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            : array();

        //Parse Headers
        $this->determineHeaders($serverData);
    }
 
    // --------------------------------------------------------------        
  
    /**
     * Unserialize a Header
     * 
     * @param string $header 
     * @return array
     */
    private function unserializeHeader($header)
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