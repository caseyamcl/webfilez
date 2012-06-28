<?php

class FileManagerIOException extends Exception 
{
	/* Pass */
}

// --------------------------------------------------------------------------

/**
 * User File Manager Class
 *
 * @author Casey McLaughlin
 */
class FileManager
{  
    const DATA = 1;
    const PATH = 2;
  
    /**
     * @var The mode for writing files
     */
    private $fileMode = 0600;

    /**
     * @var The mode for writing directories
     */
    private $dirMode = 0700;

    /**
     * @var string  The path to use (with trailing slash)
     */
    private $basepath;

    /**
     * @var array  The extensions to work with (empty means all)
     */
    private $exts = array();

    // ------------------------------------------------------------------------   

    /**
     * Constructor
     *
     * @param string $path
     * Path to the folder we'll be managing
     *
     * @param array $exts
     * Array of extensions to allow.  Empty array (default) means all
     *
     * @param boolean $autobuild
     * If TRUE, the class will attempt to automatically create the directory
     *
     * @throws FileManagerIOException
     * If the folder doesn't exist
     */
    public function __construct($path, $exts = array(), $autobuild = false)
    {
        if ( ! is_dir($path) && $autobuild) {
            @mkdir($path, 0700, true);
        }

        if ( ! is_writable($path))
            throw new FileManagerIOException("The path '$path' does not exist or is not writable!");

        $this->basepath = $this->normalizePath(realpath($path), false) . DIRECTORY_SEPARATOR;
        $this->exts = (array) $exts;
    }
  
	// ------------------------------------------------------------------------

    /**
     * Put a directory into the system
     *
     * @param string $dirPath
     * @param boolean $mkParents
     * @return string  The full directory path
     * @throws FileManagerIOException
     */
    public function putDir($dirPath, $mkParents = false)
    {
        $fullDirPath = $this->resolveRealPath($dirPath);

        if (file_exists($fullDirPath)) {
            throw new FileManagerIOException("The directory '$dirPath' already exists!");
        }

        if (@mkdir($fullDirPath, $this->dirMode, $mkParents)) {
            return $fullDirPath;
        }
        else {
            throw new FileManagerIOException("Error creating directory!");
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Get a filelist for a directory
     *
     * Returns an array of files (one level deep).  The key will be the
     * filename, and the path will be the relative path to the basepath
     *
     * Returns FALSE if the directory does not exist
     *
     * @TODO: Make this work in Windows!
     *
     * @param string $dirPath
     * @param boolean $hidden
     * @return array|boolean
     */
    public function getDir($dirPath, $hidden = true) {

        $dirPath = $this->normalizePath($dirPath);
        $fullDirPath = $this->resolveRealPath($dirPath);
        if ( ! file_exists($fullDirPath)) {
            return false;
        }

        $output = array();
        foreach (scandir($fullDirPath) as $file) {

            if ($file == '.' OR $file == '..') {
                continue;
            }

            $output[$file] = $dirPath . DIRECTORY_SEPARATOR . $file;
        }

        return $output;
    }

    // ------------------------------------------------------------------------

    /**
     * Delete a directory
     *
     * @param boolean $deleteContents  If TRUE, will recursively delete everything
     * @return boolean
     */
    public function deleteDir($dirPath, $deleteContents = false) {

        $fullDirPath = $this->resolveRealPath($dirPath);

        if ( ! file_exists($fullDirPath)) {
            throw new FileManagerIOException("The directory '$dirPath' does not exist!");
        }

        //Get the contents
        $contents = $this->getDir($dirPath);

        //Delete contents if specified
        if (! empty($contents) && $deleteContents) {
            
            foreach($contents as $filepath) {
                if (is_file($this->resolveRealPath($filepath)))
                    $this->deleteFile($filepath);
                else
                    $this->deleteDir($filepath, true);
            }
        } 
        elseif (! empty($contents)) {
            throw new FileManagerIOException("The directory '$dirPath' is not empty!");
        }
        
        //Delete this directory
        return @rmdir($fullDirPath);
    }
        
    // ------------------------------------------------------------------------

    /**
     * Put a file into the system
     * 
     * @param string $filepath       (relative to the basepath)
     * @param string $dataOrPath     Data to write, filepath to copy from
     * @param boolean $overwrite     If true, will overwrite existing file
     * @param int $mode              Can be self::DATA or self::PATH
     * @return string                The relative path for the file once it has been created
     */
    public function putFile($filepath, $dataOrPath, $overwrite = false, $mode = self::DATA)
    {
        $fullFilePath = $this->resolveRealPath($filepath);

        //Parent directory exists?
        if ( ! file_exists(dirname($fullFilePath)) OR ! is_dir(dirname($fullFilePath))) {
            throw new FileManagerIOException(sprintf("The directory %s does not exist!", dirname($fullFilePath)));
        }

        //Parent directory writable?
        if ( ! is_writable(dirname($fullFilePath))) {
            throw new FileManagerIOException(sprintf("The directory %s is not writable!", dirname($fullFilePath)));
        }

        //Exists without overwrite?
        if (file_exists($fullFilePath) && ! $overwrite) {
            throw new FileManagerIOException("The file '$filepath' already exists!");
        } 

        switch($mode) {

            case self::DATA:
                file_put_contents($fullFilePath, $dataOrPath);
            break;
            case self::PATH;

                $fih = @fopen($dataOrPath, 'r');
                $foh = @fopen($fullFilePath, 'w');
                if ( ! $fih) {
                    throw new FileManagerIOException("Unable to read source file: {$dataOrPath}!");
                }

                while ( ! feof($fih)) {
                    fwrite($foh, fread($fih, 8192));
                }

                fclose($fih);
                fclose($foh);

            break;
            default:
                throw new InvalidArgumentException("Invalid mode ($mode)!");
        }

        return $this->normalizePath($filepath);
    }
    
	// ------------------------------------------------------------------------

    /**
     * Get a File
     *
     * Returns stat() on a file, along with its relpath
     *
     * @param string $filepath         (relative to the basepath)
     * @return string
     */
    public function getFile($filepath) 
    {
        $fullFilePath = $this->resolveRealPath($filepath);

        if ( ! is_readable($fullFilePath)) {
            throw new FileManagerIOException("The file '$filepath' does not exist or is not readable!");
        }

        $info = stat($fullFilePath);
        $info[] = $this->normalizePath($filepath);
        $info[] = $fullFilePath;
        $info['relpath'] = $this->normalizePath($filepath);
        $info['realpath'] = $fullFilePath;

        return $info;
    }

    // ------------------------------------------------------------------------

    /**
     * Stream a file to output
     *
     * @param string $filepath
     * @param int $pos
     */
    public function streamFile($filepath, $pos = 0) {

        $fullFilePath = $this->resolveRealPath($filepath);

        if ( ! is_readable($fullFilePath)) {
            throw new FileManagerIOException("The file '$filepath' does not exist or is not readable!");
        }

        if (is_dir($fullFilePath)) {
            throw new FileManagerIOException("The file '$filepath' is a directory!");
        }

        $fh = fopen($fullFilePath, 'r');
        while ( ! feof($fh)) {
            echo fread($fh, 8192);
        }

        fclose($fh);
    }

    // ------------------------------------------------------------------------

    /**
     * Delete a File
     *
     * @param string $filepath
     * @return boolean
     */
    public function deleteFile($filepath)
    {
        $fullFilePath = $this->resolveRealPath($filepath);
        if ( ! is_readable($fullFilePath)) {
            throw new FileManagerIOException("The file '$filepath' does not exist or is not readable!");
        }

        return @unlink($fullFilePath);
    }

    // ------------------------------------------------------------------------

    /**
     * Get the real system path a file from a relative path
     *
     * Does not check if the file actually exists, but instead simply returns
     * the calculated path
     *
     * @param string $path
     * @return string
     */
    public function resolveRealPath($path) {
        return $this->basepath . $this->normalizePath($path);
    }

    // ------------------------------------------------------------------------

    /**
     * Normalize a given path
     *
     * Will add a trailing slash if one does not exist, and will reduce
     * any double slashes
     *
     * @param string $path 
     * @param boolean $removeFrontSlash  If TRUE, will remove the beginning slash
     * @return string
     */
    private function normalizePath($path, $removeFrontSlash = true)
    {
        //Remove front slash?
        if ($removeFrontSlash) {
            $path = ltrim($path, '/');
        }

        $ds = DIRECTORY_SEPARATOR;

        //Ensure support for WinBLOWS (haha j/k)
        $path = str_replace('/', $ds, $path);

        //Remove trailing slash
        $path = rtrim($path, $ds);

        //Reduce double slashes
        $rgds = ($ds != "\\") ? '\/' : "\\";
        $path = preg_replace("/$rgds{2,}/", '/', $path);

        return $path;
    }
}

/* EOF: FileManager.php */