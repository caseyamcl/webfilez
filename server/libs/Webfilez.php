<?php

/**
 * Webfilez Main Execution Class
 */
class Webfilez {

    /**
     * @var FileManager
     */
    private $fileMgr;

    /**
     * @var Requesty\Request
     */
    private $request;

    /**
     * @var Requesty\Response
     */
    private $response;

    /**
     * @var Requesty\Url
     */
    private $url;

    /**
     * @var UploadHandler
     */
    private $uploadHandler;
  
    // ------------------------------------------------------------------------
    
    /**
     * JFile Main Exeuction Runner 
     */
    public static function main() {
        $that = new Webfilez();
        $that->run();
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Constructor
     */
    private function __construct() {

        //Basepath
        define('BASEPATH' , __DIR__ . DIRECTORY_SEPARATOR);
        
        //Autoloader
        spl_autoload_register(array($this, 'autoloader'), TRUE, TRUE);

        //Error Manager
        Requesty\ErrorWrapper::invoke();
    }

    // ------------------------------------------------------------------------
    
    /**
     * JFile Main Execution Script
     */
    private function run()
    {        
        //Route It!
        try {

            $this->loadLibraries();

            $this->route();
            $this->response->go();
        }
        catch (Exception $e) {

            //Catch 500 errors here
            throw $e;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Load Libraries into Memory
     *
     * The order in which things get loaded in here matters
     */
    private function loadLibraries()
    {
        //First Tier
        $cachedir = sys_Get_temp_dir();
        $this->request = new Requesty\Request(new Browscap($cachedir));

        $this->response =  new Requesty\Response();
        $this->url = new Requesty\Uri();

        //Second Tier
        $this->fileMgr = new FileManager($this->getFolder());
        $this->uploadHandler = new UploadHandler($this->fileMgr);
    }

    // ------------------------------------------------------------------------

    /**
     * Callback to get the folder
     */
    private function getFolder()
    {
        //@TODO - Make this actually work by calling a callback!
        //SET OUTPUT STATUS TO 401 if get back FALSE (folder not exist or unauthorized)
        return '/tmp/webfileztest';
    }

    // ------------------------------------------------------------------------

    /**
     * Route the request and build the response using the Requesty Libraries
     */
    private function route() {

        //Getting upload status?
        if ($this->url->get_segment(0) == 'uploadstatus' && $this->url->get_query_item('id') !== false) {    

            //Attempt to set the headers to disallow caching for this type of request
            $this->response->set_http_header("Cache-Control: no-cache, must-revalidate");
            $this->response->set_http_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  

            //Get the data
            $respData = $this->uploadHandler->getUploadStatus($this->url->get_query_item('id'));

            //Set the output
            $this->response->set_output($respData);
        }

        //Getting server configuration?
        elseif ($this->url->get_segment(0) == 'serverconfig' && $this->url->get_query_item('item') !== false) {
            $respData = $this->getServerConfig($this->url->get_query_item('item'));
        }

        //Default action will be to assume we are getting a resource
        else {
            $respData = $this->routeFile();
        }

        $this->response->set_output($respData);
    }

    // ------------------------------------------------------------------------

    /**
     * Main router for file-based actions
     */
    private function routeFile() {

        $path     = $this->url->get_path_string() ?: '[root]';
        $realpath = $this->fileMgr->resolveRealPath($path);
        $exists   = is_readable($realpath);
        $isDir    = ($exists & is_dir($realpath) OR $this->url->get_query_item('isdir') == true);

        switch($this->request->get_method()) {

            case 'PUT':


            break;
            case 'POST':


            break;
            case 'DELETE':


            break;
            case 'GET': //GET will be the only method that supports HTML output
            default:


            break;
        }

        return $path;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Server configuration
     *
     * @param string $item
     */
    private function getServerConfig($item) {

      $config = array('a' => 1, 'b' => 2);

      if ($item = 'all') {

      }

      return $config;
    }

    // ------------------------------------------------------------------------
    
    /**
     * PSR-0 Compliant Autoloader with hacks for non-PSR Compliant libraries
     *
     * @param string $classname
     */
    private function autoloader($classname)
    {
        $basepath = BASEPATH;
        $classname = ltrim($classname, '\\');
        $filename = '';

        if ($lnp = strripos($classname, '\\')) {
            $namespace = substr($classname, 0, $lnp);
            $classname = substr($classname, $lnp + 1);
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $filename .= str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';
        $fullfilename = $basepath . $filename;

        //PSR-0 Compliant - Good to go!
        if (is_readable($fullfilename)) {
            require_once($fullfilename);
            return;
        }

        //Not PSR-0 Compliant - Try the slow way
        foreach (array_diff(scandir(BASEPATH), array('.', '..')) as $fn) {
            $fullfilename = BASEPATH . $fn . DIRECTORY_SEPARATOR . $filename;
            if (is_readable($fullfilename)) {
                require_once($fullfilename);
                return;
            }
        }

    }

    // ------------------------------------------------------------------------

}


/* EOF: Webfilez.php */