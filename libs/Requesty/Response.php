<?php

namespace Requesty;

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
	private $http_headers = array();
  
  /**
   * HTTP Response Code
   * @var int
   */
	private $http_status_code = 200;
  
  /**
   * HTTP Status Text
   * @var string
   */
	private $http_status_text = 'OK';
  
  /**
   * HTTP MIME Content Type
   * @var string
   */
	private $http_content_type = 'text/html';
  
  /**
   * Output Content (payload)
   * @var string
   */
	private $output_content = NULL;
	
  /**
   * Output Type (self::CONTENT or self::FILEPATH)
   * @var int
   */
  private $output_type = self::CONTENT;
  
	// --------------------------------------------------------------		

  /**
   * Constructor
   * 
   * Optionally specify output, status, and content type upon construction
   * 
   * @param string $output
   * @param int $status_code
   * @param string $content_type 
   * @param int $output_type (defaults to content, not file
   */
  public function __construct($output = NULL, $status_code = NULL, $content_type = NULL, $output_type = self::CONTENT) {
    
    if ($output) {
      $this->set_output($output, $output_type);
    }
    
    if ($status_code) {
      $this->set_http_status($status_code);
    }
    
    if ($content_type) {
      $this->set_http_content_type($content_type);
    }
    
  }
  
	// --------------------------------------------------------------		
		
	/**
	 * Set HTTP Status Header
	 *
	 * License issues (came from CIv2.1.0)?
	 * 
	 * @access	public
	 * @param	int		the status code
	 * @param	string
	 * @return	void
	 * @link https://github.com/EllisLab/CodeIgniter/blob/develop/system/core/Common.php
	 */
	public function set_http_status($code = 200, $text = '')
	{
		$stati = array(
			200	=> 'OK',
			201	=> 'Created',
			202	=> 'Accepted',
			203	=> 'Non-Authoritative Information',
			204	=> 'No Content',
			205	=> 'Reset Content',
			206	=> 'Partial Content',

			300	=> 'Multiple Choices',
			301	=> 'Moved Permanently',
			302	=> 'Found',
			304	=> 'Not Modified',
			305	=> 'Use Proxy',
			307	=> 'Temporary Redirect',

			400	=> 'Bad Request',
			401	=> 'Unauthorized',
			403	=> 'Forbidden',
			404	=> 'Not Found',
			405	=> 'Method Not Allowed',
			406	=> 'Not Acceptable',
			407	=> 'Proxy Authentication Required',
			408	=> 'Request Timeout',
			409	=> 'Conflict',
			410	=> 'Gone',
			411	=> 'Length Required',
			412	=> 'Precondition Failed',
			413	=> 'Request Entity Too Large',
			414	=> 'Request-URI Too Long',
			415	=> 'Unsupported Media Type',
			416	=> 'Requested Range Not Satisfiable',
			417	=> 'Expectation Failed',
			422	=> 'Unprocessable Entity',

			500	=> 'Internal Server Error',
			501	=> 'Not Implemented',
			502	=> 'Bad Gateway',
			503	=> 'Service Unavailable',
			504	=> 'Gateway Timeout',
			505	=> 'HTTP Version Not Supported'
		);

		if ($code == '' OR ! is_numeric($code))
		{
			show_error('Status codes must be numeric', 500);
		}

		if (isset($stati[$code]) AND $text == '')
		{
			$text = $stati[$code];
		}

		if ($text == '')
		{
			throw new Exception('No status text available.  Please check your status code number or supply your own message text.', 500);
		}

		$this->http_status_code = $code;
		$this->http_status_text = $text;
	}
	
	// --------------------------------------------------------------		

	/**
	 * Set HTTP Content MIME Type
	 * 
	 * @param string $type 
	 */
	public function set_http_content_type($type)
	{
		$this->http_content_type = $type;
	}
	
	// --------------------------------------------------------------		
	
	/**
	 * Set a custom HTTP header
	 * 
	 * @param string $header_txt 
	 */
	public function set_http_header($header_txt)
	{
		$this->http_headers[] = $header_txt;
	}
	
	// --------------------------------------------------------------
	
	/**
	 * Set Output
	 * 
	 * @param string $output  Filename or actual content, depending on context
     * @param int $type (CONTENT or FILEPATH)
	 * Typically HTML text, but can be any UTF-8 Text
	 */
	public function set_output($output, $type = self::CONTENT)
	{		
    if ($type == self::FILEPATH)
      $this->output_content = realpath($output);
    else
      $this->output_content = $output;
    
    $this->output_type = $type;
	}

	// --------------------------------------------------------------		
	
	public function redirect($to, $code = '301')
	{
		$this->set_http_status($code);
		$this->set_http_header("Location: $to");
		$this->output_http_headers();
	}
	
	// --------------------------------------------------------------		

	/**
	 * GO - Generate the output
	 * 
	 * @param boolean $return
	 * If set to TRUE, will generate HTTP output but just return the output contents
	 * 
	 * @return null|string
	 * If $return is TRUE, this function will return the output string.  Otherwise NULL
	 */
	public function go($return = FALSE)
	{		
    switch ($this->output_type) {
      
      case self::CONTENT:
        
        ob_start();

        $this->output_http_headers();
        echo $this->output_content;		

        if ($return)
        {
          return ob_get_clean();
        }
        else
        {
          ob_end_flush();
        }
      break;
      case self::FILEPATH:
        
        if ($return) {
          ob_start();
          $this->output_http_headers();
          echo file_get_contents($this->output_content);
          return ob_get_clean();
        }
        else {
          $this->output_http_headers();
          $ofh = fopen($this->output_content, 'r');
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
	 * Output HTTP Headers
	 */
	public function output_http_headers()
	{
		//Set the status header
		$code = $this->http_status_code;
		$text = $this->http_status_text;
		
		$server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

		if (substr(php_sapi_name(), 0, 3) == 'cgi')
			header("Status: {$code} {$text}", TRUE);
		elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
			header($server_protocol." {$code} {$text}", TRUE, $code);
		else
			header("HTTP/1.1 {$code} {$text}", TRUE, $code);		
			
		//Output content type header
    header("Content-type: " . $this->http_content_type);
			
		//Output custom headers
		foreach($this->http_headers as $header)
			header($header);
	}	

}

/* EOF: output.php */