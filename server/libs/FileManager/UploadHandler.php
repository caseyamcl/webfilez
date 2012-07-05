<?php

class UploadHandlerException extends Exception {
    
}

// --------------------------------------------------------------------------

/**
 * Upload Handler Class 
 */
class UploadHandler
{
    const UNLIMITED = -1;

    /**
     * @var FileManager
     */
    private $fileMgr;

    /**
     * @var ProgressStrategyInterface
     */
    private $progress;

    // ------------------------------------------------------------------------   
    
    /**
     * Constructor
     * 
     * @param FileManager $filemgr 
     */
    public function __construct(FileManager $fileMgr)
    {    
        $this->fileMgr = $fileMgr;

        //Load Libraries (Autoload?)
        $ds = __DIR__ . DIRECTORY_SEPARATOR . 'ProgressStrategies' . DIRECTORY_SEPARATOR;
        require_once($ds . 'ProgressStrategyInterface.php');
        require_once($ds . 'FileProgressStrategy.php');

        //Load progress interface
        $this->progress = $this->setProgressStrategy();
    }
     
    // ------------------------------------------------------------------------   
     
    /**
     * Determine the max upload filesize in bytes
     *
     * Will return self::UNLIMITED (-1) if there is none
     * 
     * @return int
     */
    public function getUploadMaxFilesize()
    {
        //0 = Unlimited
        $max = 0;
        
        //Convertval Function
        $convertval = function($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val)-1]);

            switch($last) {
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            return $val;
        };
        
        //Get the max upload filesize
        $maxVals = array(
            'max_upload' => $convertval(ini_get('upload_max_filesize')),
            'max_post'   => $convertval(ini_get('post_max_size')),
            'mem_limit'  => $convertval(ini_get('memory_limit')) 
        );
        
        //Find the actual max of the bunch
        foreach($maxVals as $val) {
            
            if ($val > $max) {
                $max = $val;
            }
        }
        
        return ($max > 0) ? $max : self::UNLIMITED;
    }
    
    // ------------------------------------------------------------------------   
    
    /**
     * Process upload from PHP PUT Input Stream
     *
     * @param string $path
     * Specify which path to upload files into relative to the Filemgr->basepath
     *
     * @param int $contentlength
     * Content length (in bytes)
     *
     * @param string $id
     * A unique alphanumeric ID for the upload
     *
     * @return boolean
     */
    public function processUpload($path, $contentlength, $id = NULL)
    {
            //Check destination by getting the real path, and then trying to put
            //an empty file in using the FileMgr
            $path = $this->fileMgr->putFile($path, '', false);
            $realpath = $this->fileMgr->resolveRealPath($path);
            
            //Set the base file name
            $filename = basename($realpath);

            //Open the PUT input
            $indata = fopen('php://input', 'r');
            $outdata = fopen($realpath, 'a');

            //Clean the progress meter
            $this->progress->clean();

            //Set the progress meter to 0
            if ($id) {
                    $this->progress->setProgress($id, $contentlength, 0);
            }

            //Read and write
            $sizewritten = 0;
            while($chunk = fread($indata, 8192)) {

                    $sizewritten += strlen($chunk);
                    file_put_contents($realpath, $chunk, FILE_APPEND);

                    if ($id) {
                        $this->progress->setProgress($id, $contentlength, $sizewritten);
                    }

                    //Test - DELETE ME
                    sleep(2);
            }

            //Clean up
            fclose($indata);
            fclose($outdata);
            unset($chunk);
    }

    // ------------------------------------------------------------------------   
    
    /**
     * Get the status for an upload
     * 
     * If the UploadProgress PECL extension is installed, this method can be
     * called asynchronously during file uploads to report on file upload status.
     * 
     * If not, then it simply returns an object that indicates the progress in
     * in-status
     * 
     * @param type $id
     * @return object
     */
    public function getUploadStatus($id)
    {   
        $info = $this->progress->getProgress($id);

        //If can't find info...
        if ( ! $info) { 
            $info = (object) array('noprogress' => true);
        }
        
        return $info;
    }
    
    // ------------------------------------------------------------------------   
    
    /**
     * Perform security checks on uploaded files
     *
     * @param string $path
     */
    private function uploadSecurityFilter($path)
    {  
        /* DO SECURITY STUFF HERE AND THROW A SECURITY EXCEPTION IF FAIL */

        //If all checks passed
        return TRUE;    
    }
    
    // ------------------------------------------------------------------------

    /**
     * Set the progress meter strategy either manually or automatically
     *
     * @param string $strategy
     * @return ProgressStrategyInterface
     */
    private function setProgressStrategy($strategy = NULL)
    {
        //Determine strategy based on functionality
        if (is_null($strategy)) {

            //@TODO: Add strategies and decisions here!
            $strategy = 'FileProgressStrategy';
        }

        if ( ! class_exists($strategy)) {
            throw new Exception("Progress strategy {$strategy} does not exist");
        }

        return new $strategy;
    }

    // ------------------------------------------------------------------------   
    /**
     * Throw Upload Error
     *
     * Helper function to throw an error
     *
     * @param int $error
     * @throws UploadHandlerException
     */
    private function throwUploadError($error) 
    {
        switch ($error)
        {
            case 1:    // UPLOAD_ERR_INI_SIZE
                throw new UploadHandlerException('Uploaded File Exceeds Allowed Size Limit');
            case 2: // UPLOAD_ERR_FORM_SIZE
                throw new UploadHandlerException('Uploaded File Exceeds Allowed POST Size Limit');
            case 3: // UPLOAD_ERR_PARTIAL
                throw new UploadHandlerException('Partial File Uploaded');
            case 4: // UPLOAD_ERR_NO_FILE
                throw new UploadHandlerException('No File Selected');
            case 6: // UPLOAD_ERR_NO_TMP_DIR
                throw new UploadHandlerException('No Temporary Directory is Set to Upload Files To');
            case 7: // UPLOAD_ERR_CANT_WRITE
                throw new UploadHandlerException('Unable to Write Temporary File Upload File');
            case 8: // UPLOAD_ERR_EXTENSION
                throw new UploadHandlerException('Extension is Not Allowed by System');
            default:
                throw new UploadHandlerException('No File Selected');
        }  
    }
}

/* EOF: UploadHandler.php */