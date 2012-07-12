<?php

namespace Reqresp;

/**
 * HTTP Output Class
 */
class Response
{
    const CONTENT  = 1;
    const FILEPATH = 2;
  
    /**
     * Custom HTTP Headers
     * @var array
     */
    protected $headers = array();
  
    /**
     * HTTP Response Code
     * @var int
     */
    protected $statusCode = 200;
  
    /**
     * HTTP Status Text
     * @var string
     */
    protected $statusText = 'OK';
  
    /**
     * HTTP MIME Content Type
     * @var string
     */
    protected $contentType = 'text/html';
  
    /**
     * Output Content (payload)
     * @var string
     */
    protected $contentBody = null;
    
    /**
     * Output Source (self::CONTENT or self::FILEPATH)
     * @var int
     */
    protected $contentSource = self::CONTENT;
  
    // --------------------------------------------------------------        

    /**
    * Constructor
    * 
    * Optionally specify output, status, and content type upon construction
    * 
    * @param string $output
    * @param int $code
    * @param string $contentType 
    * @param int $source (defaults to content, not file)
    */
    public function __construct($output = null, $code = null, $contentType = null, $source = self::CONTENT)
    {
        if ($output) {
            $this->setBody($output, $source);
        }

        if ($code) {
            $this->setStatus($code);
        }

        if ($contentType) {
            $this->setContentType($contentType);
        }

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
     * Set HTTP Status Header
     * 
     * @access  public
     * @param   int
     * @param   string
     * @return  void
     * @link    https://github.com/EllisLab/CodeIgniter/blob/develop/system/core/Common.php
     */
    public function setStatus($code = 200, $text = '')
    {
        $stati = array(
            200    => 'OK',
            201    => 'Created',
            202    => 'Accepted',
            203    => 'Non-Authoritative Information',
            204    => 'No Content',
            205    => 'Reset Content',
            206    => 'Partial Content',

            300    => 'Multiple Choices',
            301    => 'Moved Permanently',
            302    => 'Found',
            304    => 'Not Modified',
            305    => 'Use Proxy',
            307    => 'Temporary Redirect',

            400    => 'Bad Request',
            401    => 'Unauthorized',
            403    => 'Forbidden',
            404    => 'Not Found',
            405    => 'Method Not Allowed',
            406    => 'Not Acceptable',
            407    => 'Proxy Authentication Required',
            408    => 'Request Timeout',
            409    => 'Conflict',
            410    => 'Gone',
            411    => 'Length Required',
            412    => 'Precondition Failed',
            413    => 'Request Entity Too Large',
            414    => 'Request-URI Too Long',
            415    => 'Unsupported Media Type',
            416    => 'Requested Range Not Satisfiable',
            417    => 'Expectation Failed',
            422    => 'Unprocessable Entity',

            500    => 'Internal Server Error',
            501    => 'Not Implemented',
            502    => 'Bad Gateway',
            503    => 'Service Unavailable',
            504    => 'Gateway Timeout',
            505    => 'HTTP Version Not Supported'
        );

        if ($code == '' OR ! is_numeric($code)) {
            throw new \InvalidArgumentException('Status codes must be numeric');
        }

        if (isset($stati[$code]) AND $text == '') {
            $text = $stati[$code];
        }

        if ($text == '') {
            throw new \InvalidArgumentException('No status text available.  Please check your status code number or supply your own message text.', 500);
        }

        $this->statusCode = $code;
        $this->statusText = $text;
    }
    
    // --------------------------------------------------------------        

    /**
     * Set HTTP Content MIME Type
     * 
     * @param string $type 
     */
    public function setContentType($type)
    {
        $this->contentType = (string) $type;
    }
    
    // --------------------------------------------------------------        
    
    /**
     * Set a custom HTTP header
     * 
     * @param string $headerString 
     */
    public function setHeader($headerString)
    {
        $this->headers[] = (string) $headerString;
    }
    
    // --------------------------------------------------------------
    
    /**
     * Set Output
     * 
     * @param string $output  Filename or actual content, depending on context
     * @param int $type (CONTENT or FILEPATH)
     * Typically HTML text, but can be any UTF-8 Text
     */
    public function setBody($output, $type = self::CONTENT)
    {        
        $this->contentBody = ($type == self::FILEPATH)
            ? realpath($output)
            : $this->contentBody = (string) $output;
        
        $this->contentSource = $type;
    }

    // --------------------------------------------------------------        
    
    /**
     * GO - Redirect
     */
    public function redirect($to, $code = '301')
    {
        $this->setStatus($code);
        $this->setHeader("Location: $to");
        $this->printHeaders();
    }
    
    // --------------------------------------------------------------        

    /**
     * GO - Generate the output
     * 
     * @param boolean $return
     * If set to true, will generate HTTP output but just return the output contents
     * 
     * @return null|string
     * If $return is true, this function will return the output string.  Otherwise null
     */
    public function go($return = false)
    {        
        switch ($this->contentSource) {
          
            case self::CONTENT:
            
                ob_start();

                $this->printHeaders();
                echo $this->contentBody;        

                if ($return) {
                    return ob_get_clean();
                }
                else {
                    ob_end_flush();
                }

            break;
            case self::FILEPATH:
            
                if ($return) {
                  ob_start();
                  $this->printHeaders();
                  echo file_get_contents($this->contentBody);
                  return ob_get_clean();
                }
                else {
                  $this->printHeaders();
                  $ofh = fopen($this->contentBody, 'r');
                  while( ! feof($ofh)) {
                    echo fread($ofh, 8192);
                  }
                  fclose($ofh);
                  return;
                }
            break;
            default:
                throw new \RuntimeException("Invalid output type defined");      
        }
    }
    
    // --------------------------------------------------------------        
    
    /**
     * GO - Output HTTP Headers
     *
     * Called automatically from $this->go(), so only call this
     * manually if you are not using that method to generate output
     *
     * @param boolean $return
     * If true, will return an array of headers, rather than set them in output
     *
     * @param array $serverData
     * null, except for testing
     */
    public function printHeaders($return = FALSE, $serverData = null)
    {
        //Default serverData
        if (is_null($serverData)) {
            $serverData = $_SERVER;
        }

        //Set the status header
        $code = $this->statusCode;
        $text = $this->statusText;
        
        $protocol = (isset($serverData['protocol'])) ? $serverData['protocol'] : false;

        if (substr(php_sapi_name(), 0, 3) == 'cgi')
            $outHeaders[] = array("Status: {$code} {$text}", true);
        elseif ($protocol == 'HTTP/1.1' OR $protocol == 'HTTP/1.0')
            $outHeaders[] = array($protocol." {$code} {$text}", true, $code);
        else
            $outHeaders[] = array("HTTP/1.1 {$code} {$text}", true, $code);        
            
        //Output content type header
        $outHeaders[] = array("Content-type: " . $this->contentType);
            
        //Output custom headers
        foreach($this->headers as $header) {
            $outHeaders[] = array($header);
        }

        //Return them if specififed
        if ($return) {
            return $outHeaders;
        }
    
        //Otherwise, print 'em'    
        foreach ($outHeaders as $hdr) {
            call_user_func_array('header', $hdr);
        }
    }    
}

/* EOF: output.php */