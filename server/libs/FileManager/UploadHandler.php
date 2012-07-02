<?php

class UploadHandlerException extends Exception {
  
}

// --------------------------------------------------------------------------

/**
 * Upload Handler Class 
 *
 * @TODO: Abstract progress meter into its own class, and implement strategy
 * pattern here based on if APC is available, SESSION is available, etc.
 */
class UploadHandler
{
  const UNLIMITED = -1;

  /**
   * @var FileManager
   */
  private $fileMgr;

	// ------------------------------------------------------------------------   
  
  /**
   * Constructor
   * 
   * @param FileManager $filemgr 
   */
  public function __construct(FileManager $fileMgr) {
    
    $this->fileMgr = $fileMgr;

    if ( ! isset($_SESSION['upload_progress'])) {
        $_SESSION['upload_progress'] = array();
    }
    
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
   * @param string $id
   * A unique alphanumeric ID for the upload
   *
   * @param int $contentlength
   * Content length (in bytes)
   *
   * @return boolean
   */
  public function processUpload($path, $id, $contentlength)
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

      //Prepare the progress meter
      $this->cleanProgressMeter();

      //Set the progress meter to 0
      $this->setProgressMeter($id, 0);

      //Read and write
      $sizewritten = 0;
      while($chunk = fread($putdata, 8192)) {

        $sizewritten += strlen($chunk);
        file_put_contents($realpath, $chunk, FILE_APPEND);
        $this->setProgressMeter($id, $sizewritten);
      }

      //Clean up
      fclose($indata);
      fclose($outdata);
      unset($chunk);
  }
  
	// ------------------------------------------------------------------------   

  /**
   * Set Progress Meter for upload
   */
  private function setProgressMeter($id, $amount = 0) {
  
      if ( ! isset($_SESSION['upload_progress'][$path])) {
          $_SESSION['upload_progress'][$id] = array();
      }

      $_SESSION['upload_progress'][$id]['timestamp'] = time();
      $_SESSION['upload_progress'][$id]['amount'] = $amount;
  }

  // ------------------------------------------------------------------------   

  private function getProgressMeter($id) {

      if (isset($_SESSION['upload_progress'][$id])) {
          return $_SESSION['upload_progress'][$id];
      }
      else {
          return NULL;
      }
  }

  // ------------------------------------------------------------------------   

  /**
   * Clear any progresses out that are older than
   */
  private function cleanProgressMeter() {

      $now = time();
      $tokill = array();

      if (isset($_SESSION['upload_progress'])) {
          foreach($_SESSION['upload_progress'] as $id => $info) {
              if (($now - $info['timestamp'] > 30) && (int) $info->amount == 100) {
                  $tokill[] = "\$_SESSION['upload_progress'][{$id}]";
              }
          }

          call_user_func_array('unset', $tokill);
      }
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
   * @param type $idd 
   * @return object
   */
  public function getUploadStatus($id)
  {   
    $info = new stdClass;

    if (isset($_SESSION['upload_progress'][$id])) {
      $info->percent_uploaded = $_SESSION['upload_progress']['amount'];
      $info->upload_in_progress = ((int) $_SESSION['upload_progress']['amount'] >= 100) ? TRUE : FALSE;

    }
    else { //otherwise, we can't get information
      $info->upload_in_progress = FALSE;
    }
    
    return $info;
  }
  
	// ------------------------------------------------------------------------   
  
  private function uploadSecurityFilter($file)
  {  
    /* DO SECURITY STUFF HERE AND THROW A SECURITY EXCEPTION IF FAIL */

    //If all checks passed
    return TRUE;    
  }
  
	// ------------------------------------------------------------------------   

  private function throwUploadError($error) 
  {
    switch ($error)
    {
      case 1:	// UPLOAD_ERR_INI_SIZE
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