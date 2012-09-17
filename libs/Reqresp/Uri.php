<?php

namespace Reqresp;

/**
 * URI Class
 *
 * Detects the current URI as best as possible, and returns information
 * about it.  Useful for MVC frameworks, or any other URI-driven routing-based
 * application.
 *
 * @author Casey McLaughlin
 */
class Uri
{
    /**
     * @var boolean
     */
    protected $https;

    /**
     * @var string
     */
    protected $protocol;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $hostIP;

    /**
     * @var string
     */
    protected $basepath;

    /**
     * @var string
     */
    protected $scriptname;

    /**
     * String of path segments
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $query;

    /**
     * URL to application
     * @var string
     */
    protected $baseurl;

    /**
     * Site URL, including controller script, if in path
     * @var string
     */
    protected $appurl;

    /**
     * Full URL to current page
     * @var string
     */
    protected $currenturl;

    /**
     * Current URL, including query string
     * @var string
     */
    protected $fullurl;

    // --------------------------------------------------------------

    /**
     * Constructor
     *
     * @param array $serverData  Keep null unless testing
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
     * Return path segment by index, or false if not set
     *
     * Path segments are indexed starting at 1, not 0
     *
     * @param int $seg
     * @return string|boolean
     */
    public function path($seg = null)
    {
        $segments = array();
        foreach(explode('/', $this->path) as $v) {
            $segments[count($segments)+1] = $v;
        }

        if (is_null($seg)) {
            return $segments;
        }
        else {
            return (isset($segments[$seg])) ? $segments[$seg]: false;
        }
    }

    // --------------------------------------------------------------

    /**
     * Return a specific query item, or false if not exists
     *
     * @param string $key
     * @return string|boolean
     */
    public function query($key = null) {

        $q = array();
        parse_str($this->query, $q);

        if (is_null($key)) {
            return $q;
        }
        else {
            return (isset($q[$key])) ? $q[$key] : false;
        }
    }

    // --------------------------------------------------------------

    /**
     * Attempt to automaticlly detect URL infos - Runs only once
     *
     * Tested In: Apache 2.2
     *
     * @param $serverData  Passed in from constructor
     */
    private function init($serverData = null)
    {
        //Use global most of the time
        if (is_null($serverData)) {
            $serverData = $_SERVER;
        }

        //Get the protocol and port
        $this->port     = (strpos($serverData['HTTP_HOST'], ':'))
            ? end(explode(':', $serverData['HTTP_HOST']))
            : $serverData['SERVER_PORT'];
        $this->protocol = ($this->port == 443 || ( ! empty($serverData['HTTPS']) && $serverData['HTTPS'] == 'on')) ? 'https' : 'http';
        $this->https    = ($this->protocol == 'https');

        //Get the server name
        $this->hostname = (isset($serverData['SERVER_NAME'])) ? $serverData['SERVER_NAME'] : $serverData['HTTP_HOST'];
        $this->hostIP   = $serverData['SERVER_ADDR'];

        //Get the base URL path & script name
        $scriptname     = basename($serverData['SCRIPT_FILENAME']);
        $this->basepath = str_replace($scriptname, '', $serverData['SCRIPT_NAME']);

        //Set the script name.
        if (strpos($serverData['REQUEST_URI'], $scriptname) !== false) {
            $this->scriptname = $scriptname;
        }
        else {
            $this->scriptname = '';
        }

        //Set the request_uri
        $reqURI = explode('?', $serverData['REQUEST_URI'], 2);
        $reqURI = $this->reduceDoubleSlashes(array_shift($reqURI));

        //The query string
        $this->query = $serverData['QUERY_STRING'];

        //Get the PATH
        $pathinfo = substr($reqURI, strlen($this->basepath . $this->scriptname));

        $segments = array();
        if ( ! empty($pathinfo)) {
            $arr = array_values(array_filter(explode('/', $pathinfo)));
            for($i = 0; $i < count($arr); $i++)
                $segments[($i+1)] = $arr[$i];
        }
        $this->path = implode('/', $segments);

        //Build the baseurl and currenturl
        if (($this->protocol == 'https' && $this->port != 443) OR ($this->protocol == 'http' && $this->port != 80))
            $port = ':' . $this->port;
        else
            $port = '';

        $this->baseurl = $this->reduceDoubleSlashes($this->protocol . '://' . $this->hostname . $port . '/' . $this->basepath . '/');
        $this->appurl = $this->reduceDoubleSlashes($this->baseurl . $this->scriptname . '/');

        $this->currenturl = $this->reduceDoubleSlashes($this->appurl . '/' . $this->path);
        $this->fullurl    = ( ! empty($this->query)) ? $this->currenturl . '?' . $serverData['QUERY_STRING'] : $this->currenturl;
    }

    // --------------------------------------------------------------

    /**
     * Reduces all sets of double slashes down to a single slash
     *
     * @param string $str
     * @return string
     */
    private function reduceDoubleSlashes($str)
    {
        if (preg_match("/^[a-zA-Z0-9-_]+?\:\/\//", $str, $matches) > 0) {
            $pre = substr($str, 0, strlen($matches[0]));
            $str = substr($str, strlen($matches[0]));
        }
        else {
            $pre = '';
        }

        while(strpos($str, '//')) {
            $str = str_replace("//", '/', $str);
        }

        return $pre . $str;
    }

}

/* EOF: Uri.php */