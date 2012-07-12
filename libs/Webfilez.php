<?php

class WebfilezNotAuthorizedException
{
    /* pass */
}
   
// ------------------------------------------------------------------------
   
/**
 * Webfilez Main Execution Class
 */
class Webfilez {

    /**
     * @var FileManager
     */
    private $fileMgr;

    /**
     * @var Reqresp\Request
     */
    private $request;

    /**
     * @var Reqresp\Response
     */
    private $response;

    /**
     * @var Reqresp\Url
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
        Reqresp\ErrorWrapper::invoke();
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
        catch (WebfilezNotAuthorizedException $e) {
            $this->response->setStatus('401');

            if ($this->request->isAjax) {
                $this->response->setBody(json_encode(array('msg' => 'Not Authorized')));
            }
            else {
                $this->response->setBody("Not Authorized");
            }
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

        $this->request = new Reqresp\Request();
        $this->response =  new Reqresp\Response();
        $this->url = new Reqresp\Uri();

        //Second Tier
        $this->fileMgr = new FileManager($this->getFolder(), array(), (boolean) $this->config->autobuild);
        $this->uploadHandler = new UploadHandler($this->fileMgr, $this->config->slow);
    }

    // ------------------------------------------------------------------------

    /**
     * Callback to get the folder
     *
     * @return string
     */
    private function getFolder()
    {
        if ($this->config->foldercallbackfile) {
            include_once($this->config->foldercallbackfile);
        }

        if ( ! $this->config->foldercallback) {
            throw new Exception("Folder Callback undefined!  Did you set it in the configuration?");
        }

        return call_user_func($this->config->foldercallback);
    }

    // ------------------------------------------------------------------------

    /**
     * Route the request and build the response using the Requesty Libraries
     */
    private function route() {

        //Getting upload status? Path: uploadstatus?id=##
        if ($this->url->path(1) == 'uploadstatus' && $this->url->query('id') !== false) {

            //Attempt to set the headers to disallow caching for this type of request
            $this->response->setHeader("Cache-Control: no-cache, must-revalidate");
            $this->response->setHeader("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  

            //Get the data
            $respData = json_encode($this->uploadHandler->getUploadStatus($this->url->query('id')));
            
            //Set the output
            $this->response->setBody($respData);
        }

        //Getting server configuration?  Path: serverconfig?item=all or ?item=someitem
        elseif ($this->url->path(1) == 'serverconfig' && $this->url->query('item') !== false) {
            $this->routeServerConfig($this->url->query('item'));
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

        $path     = $this->url->path;
        $realpath = $this->fileMgr->resolveRealPath($path);
        $exists   = is_readable($realpath);
        $isDir    = ($exists & is_dir($realpath) OR $this->url->query('isdir') == true);

        switch($this->request->method) {

            case 'PUT':

                //Determine upload ID - Try header first, then query array
                $fileUploadID = $this->request->header('Uploadfileid') ?: $this->url->query('id');

                if ( ! $exists OR ($this->request->header('Overwrite') ?: $this->url->query('overwrite'))) {
                    $output = $this->uploadHandler->processUpload($path, $_SERVER['CONTENT_LENGTH'], $fileUploadID);
                    $this->response->setBody(json_encode($output));
                }
                else {
                    $this->response->setStatus();
                    $this->response->setBody(json_encode(array('msg' => 'File already exists')));
                }

            break;
            case 'POST':

                if ($exists) {
                    //Get the new name from the input.
                    //If no match, copy the file using the filePut and then delete the old one
                }
                else {
                    $this->response->setStatus(404);
                    $this->response->setBody(json_encode(array('msg' => 'File not found')));
                }

            break;
            case 'DELETE':


            break;
            case 'GET': //GET will be the only method that supports HTML output
            default:

                if ( ! $isDir && $exists && $this->url->query('contents')) {
                    $this->response->setHeader('Content-type: application/octet-stream');
                    $this->response->setBody($realpath, Reqresp\Response::FILEPATH);                    
                }
                elseif ( ! $this->request->isAjax) {
                    $this->response->setBody($this->loadInterface());
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
        if ($exists) {

            //Get the object
            $theObj = ($isDir)
                ? $this->fileMgr->getDir($path)
                : $this->fileMgr->getFile($path);

            //Output it
            $this->response->setBody(json_encode($theObj));
        }
        else { //Not exists
            $this->response->setHeader(404);
            $this->response->setBody(json_encode(array('msg' => 'File or folder not found')));
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
          $this->response->setBody(json_encode($config));
      }
      elseif (isset($config[$item])) {
          $this->response->setBody(json_encode(array($item => $config[$item])));
      }
      else {
          $this->response->setHeader(404);
          $this->response->setBody(json_encode(array('msg' => 'Configuration setting not found')));
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
        //Variables
        $templateVars = array(
            'baseurl'     => rtrim($this->url->baseurl, '/'),
            'currentpath' => $this->url->path,
            'currenttype' => is_dir($this->fileMgr->resolveRealPath($this->url->path)) ? 'dir' : 'file'
        );

        //Do the output
        $ds = DIRECTORY_SEPARATOR;
        $html =  file_get_contents(BASEPATH . "..{$ds}assets{$ds}html{$ds}template.html");
        foreach ($templateVars as $search => $repl) {
            $html = str_replace('{' . $search . '}', $repl, $html);
        }

        //Replace anything between <? tags 
        $html = preg_replace("/<\?(.+?)\?>(\n+)?/s", '', $html);
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