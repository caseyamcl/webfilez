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
  private $filemgr;

	// ------------------------------------------------------------------------   
  
  /**
   * Constructor
   * 
   * @param FileManager $filemgr 
   */
  public function __construct(FileManager $filemgr, $rules = array()) {
    
    $this->filemgr = $filemgr;
    
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
   * Process uploads from an array of input files
   * 
   * @param string $path
   * Specify which path to upload files into relative to the Filemgr->basepath
   *
   * @return boolean
   */
  public function processUploads($path = '')
  {
    
    //Check destination
    $pathinfo = $this->filemgr->get_file_info($path);
    if ( ! $pathinfo->exists OR ! $pathinfo->is_dir) {
      throw new UploadHandlerException("Destination Error: Path ($path) does not exist or is not writable!");
    }
    
    //Process the files
    foreach($_FILES as $file) {
  
      //Check extension for allowed extensions
      //@TODO: THIS!
      
      //Check upload errors
      if ( ! is_uploaded_file($file['tmp_name'])) {
        $this->throwUploadError($file['error']);
      }
      
      //Process security checks
      $this->uploadSecurityFilter($file);
      
      //Move it along into the correct location
      return $this->filemgr->putFile($path, $file['name'], $file['tmp_name'], FileManager::PATH);
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
   * @param type $upload_id 
   * @return object
   */
  public function getUploadStatus($upload_id)
  { 
    //Attempt to set the headers to disallow caching for this type of request
    @header("Cache-Control: no-cache, must-revalidate");
    @header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    
    //If uplodprogress_get_info exists, we can get progress!
    if (is_callable('uploadprogress_get_info')) {
      
      $info = (object) uploadprogress_get_info($upload_id);
      
      if ($info && $info != 'null') {
        $info->progress_enabled = TRUE;
        $info->upload_in_progress = TRUE;
      }
      else {
        
        $info = (object) array(
          'progress_enabled' => TRUE,
          'upload_in_progress' => FALSE
        );
        
      }      
    }
    else { //otherwise, we can't get information
      
      $info = (object) array(
        'progress_enabled' => FALSE
      );
      
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