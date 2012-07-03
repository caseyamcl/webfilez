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

        //Session
        session_start();
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
        $configdir = BASEPATH . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        $this->config = new Configula\Config($configdir);

        $cachedir = sys_Get_temp_dir();
        $this->request = new Requesty\Request(new Browscap($cachedir));

        $this->response =  new Requesty\Response();
        $this->url = new Requesty\Uri();

        //Second Tier
        $this->fileMgr = new FileManager($this->getFolder(), array(), (boolean) $this->config->autobuild);
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

        //Getting upload status? Path: uploadstatus?id=##
        if ($this->url->get_segment(1) == 'uploadstatus' && $this->url->get_query_item('id') !== false) {

            //Attempt to set the headers to disallow caching for this type of request
            $this->response->set_http_header("Cache-Control: no-cache, must-revalidate");
            $this->response->set_http_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  

            //Get the data
            $respData = json_encode($this->uploadHandler->getUploadStatus($this->url->get_query_item('id')));

            //Set the output
            $this->response->set_output($respData);
        }

        //Getting server configuration?  Path: serverconfig?item=all or ?item=someitem
        elseif ($this->url->get_segment(1) == 'serverconfig' && $this->url->get_query_item('item') !== false) {
            $this->routeServerConfig($this->url->get_query_item('item'));
        }

        //Default action will be to assume we are getting a resource (any other path)
        else {
            $respData = $this->routeFile();
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Main router for file-based actions
     */
    private function routeFile() {

        $path     = $this->url->get_path_string();
        $realpath = $this->fileMgr->resolveRealPath($path);
        $exists   = is_readable($realpath);
        $isDir    = ($exists & is_dir($realpath) OR $this->url->get_query_item('isdir') == true);

        switch($this->request->get_method()) {

            case 'PUT':

                //Determine upload ID - Try header first, then query array
                $fileUploadID = $this->request->get_header('Uploadfileid') ?: $this->request->get_query_item('id');

                //@TODO: Add mechanism to allow for overwrite!
                if ( ! $exists) {
                    $output = $this->uploadHandler->processUpload($path, $_SERVER['CONTENT_LENGTH'], $fileUploadID);
                    $this->response->set_output(json_encode($output));
                }
                else {
                    $this->response->set_http_status();
                    $this->response->set_output(json_encode(array('msg' => 'File already exists')));
                }

            break;
            case 'POST':

                if ($exists) {
                    //Get the new name from the input.
                    //If no match, copy the file using the filePut and then delete the old one
                }
                else {
                    $this->response->set_http_status(404);
                    $this->response->set_output(json_encode(array('msg' => 'File not found')));
                }

            break;
            case 'DELETE':


            break;
            case 'GET': //GET will be the only method that supports HTML output
            default:

                if ( ! $this->request->is_ajax()) {
                    $this->response->set_output($this->loadInterface());
                }
                else {
                    $this->routeGetFile($path, $realpath, $exists, $isDir);
                }

            break;
        }
    }

    // ------------------------------------------------------------------------

    private function routeGetFile($path, $realpath, $exists, $isDir) {

        //Stream the file
        if ( ! $isDir && $exists && $this->url->get_segment('contents')) {
            $this->response->set_output($realpath, Requesty\Response::FILEPATH);
        }
        elseif ($exists) {

            //Get the object
            $theObj = ($isDir)
                ? $this->fileMgr->getDir($path)
                : $this->fileMgr->getFile($path);

            //Output it
            $this->response->set_output(json_encode($theObj));
        }
        else { //Not exists
            $this->response->set_http_status(404);
            $this->response->set_output(json_encode(array('msg' => 'File or folder not found')));
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Get Server configuration
     *
     * @param string $item
     */
    private function routeServerConfig($item) {

      $config = array('a' => 1, 'b' => 2);
      if ($item == 'all') {
          $this->response->set_output(json_encode($config));
      }
      elseif (isset($config[$item])) {
          $this->response->set_output(json_encode(array($item => $config[$item])));
      }
      else {
          $this->response->set_http_status(404);
          $this->response->set_output(json_encode(array('msg' => 'Configuration setting not found')));
      }
    }

    // ------------------------------------------------------------------------

    /**
     * Load the interface HTML to download to a browser
     *
     * @return string
     */
    private function loadInterface()
    {
        //Set the baseurl variable
        $baseurl = rtrim($this->url->get_base_url_path(), '/');

        //Do the output
        ob_start();
        include(BASEPATH . '..' . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'template.php');
        $html =  ob_get_clean();
        $html = str_replace('{baseurl}', $baseurl, $html);
        return $html;
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