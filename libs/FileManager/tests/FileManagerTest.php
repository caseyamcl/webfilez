<?php

require_once('../FileManager.php');

class FileManagerTest extends PHPUnit_Framework_TestCase {

  private $content_path;

  // --------------------------------------------------------------

  function setUp()
  {
    parent::setUp();

    $ds = DIRECTORY_SEPARATOR;
    $this->content_path = sys_get_temp_dir() . $ds . 'phpunit_filemgr_test_' . time();

    //Setup fake content directory
    mkdir($this->content_path);

    //Fake front page
    file_put_contents($this->content_path . $ds . 'content.php', "<p>Front Html</p>");
    file_put_contents($this->content_path . $ds . 'meta.json', json_encode(array('title' => 'Front Page')));

    //Fake some_content page
    mkdir($this->content_path . $ds . 'some_content');
    file_put_contents($this->content_path . "{$ds}some_content{$ds}" . 'content.php', "<p>Some Content Html</p>");
    file_put_contents($this->content_path . "{$ds}some_content{$ds}" . 'meta.json', json_encode(array('title' => 'Some Content')));

    //Fake some_content/subcontent page
    mkdir($this->content_path . $ds . 'some_content' . $ds . 'subcontent');
    file_put_contents($this->content_path . "{$ds}some_content{$ds}subcontent{$ds}" . 'content.php', "<p>Subcontent Html</p>");
    file_put_contents($this->content_path . "{$ds}some_content{$ds}subcontent{$ds}" . 'meta.json', json_encode(array('title' => 'Subcontent')));

    //Fake some_other_content page
    mkdir($this->content_path . $ds . 'some_other_content');
    file_put_contents($this->content_path . "{$ds}some_other_content{$ds}" . 'content.php', "<p>Some Other Content Html</p>");
    file_put_contents($this->content_path . "{$ds}some_other_content{$ds}" . 'meta.json', json_encode(array('title' => 'Some Other Content')));


  }

  // --------------------------------------------------------------

  function tearDown()
  {    
    $ds = DIRECTORY_SEPARATOR;

    //Delete everything...
    $dne = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'doesnotexist*';
    $cp = $this->content_path;
    `rm -rf {$cp} && rm -rf {$dne}`;

    parent::tearDown();
  } 

  // --------------------------------------------------------------

  public function testInstantiateAsObjectSucceeds() {

    $obj = new FileManager($this->content_path);
    $this->assertInstanceOf('FileManager', $obj);
  }

  // --------------------------------------------------------------

  public function testTestDirectoriesAndFilesAreCorrectlySetup() {

    $ds = DIRECTORY_SEPARATOR;

    $this->assertFileExists($this->content_path . $ds . 'content.php');
    $this->assertFileExists($this->content_path . $ds . 'meta.json');
    $this->assertFileExists($this->content_path . $ds . 'some_content' . $ds . 'content.php');
    $this->assertFileExists($this->content_path . $ds . 'some_content' . $ds . 'meta.json');
    $this->assertFileExists($this->content_path . $ds . 'some_content' . $ds . 'subcontent' . $ds . 'content.php');
    $this->assertFileExists($this->content_path . $ds . 'some_content' . $ds . 'subcontent' . $ds . 'meta.json');
    $this->assertFileExists($this->content_path . $ds . 'some_other_content' . $ds . 'content.php');
    $this->assertFileExists($this->content_path . $ds . 'some_other_content' . $ds . 'meta.json');    
  }
  
  // --------------------------------------------------------------

  public function testInstantationFailsForNonExistentDirectoryAndNoAutobuild() {

    $this->setExpectedException('FileManagerIOException');
    $nonExistentPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'doesnotexist' . time();
    $obj = new FileManager($nonExistentPath);
  }

  // --------------------------------------------------------------

  public function testInstantiationSucceedsAndAutobuildsWhenAutobuildEnabled() {

    $nonExistentPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'doesnotexist' . time();
    $obj = new FileManager($nonExistentPath, array(), true);
    rmdir($nonExistentPath);
  }

  // --------------------------------------------------------------

  public function testInstantiationFailsForNonWritableDirectory() {

    $this->setExpectedException('FileManagerIOException');
    $nonExistentPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'doesnotexist' . time();
    mkdir($nonExistentPath);

    chmod($nonExistentPath, 600);
    $obj = new FileManager($nonExistentPath. DIRECTORY_SEPARATOR . 'test');

    chmod($nonExistentPath, 777);
    rmdir($nonExistentPath);
  }

  // --------------------------------------------------------------

  public function testNormalizePathManipulatesStringsCorrectly() {

    $method = new ReflectionMethod('FileManager', 'normalizePath');
    $method->setAccessible(true);

    $this->assertEquals('somewhere/to/something', $method->invoke(new FileManager($this->content_path), '/somewhere/to/something'));
    $this->assertEquals('somewhere/to/something', $method->invoke(new FileManager($this->content_path), '//somewhere//to/something'));
    $this->assertEquals('somewhere/to/something', $method->invoke(new FileManager($this->content_path), '//somewhere//to/something//'));
    $this->assertEquals('/somewhere/to/something', $method->invoke(new FileManager($this->content_path), '//somewhere//to/something//', false));
  }

  // --------------------------------------------------------------

  public function testResolveRealPathReturnsTheRealPath() {

    $obj = new FileManager($this->content_path);

    $expected = $this->content_path . DIRECTORY_SEPARATOR . 'somewhere/to/something';
    $this->assertEquals($expected, $obj->resolveRealPath('somewhere/to/something'));
  }

  // --------------------------------------------------------------

  public function testPutDirWorksForDirWithExistentParentDir() {

    $dirname = 'testdir';
    $obj = new FileManager($this->content_path);
    $obj->putDir($dirname);
    $this->assertFileExists($this->content_path . DIRECTORY_SEPARATOR . $dirname);
    rmdir($this->content_path . DIRECTORY_SEPARATOR . $dirname);
  }

  // --------------------------------------------------------------

  public function testPutDirFailsForDirWithoutExistentParentDir() {

    $dirname = 'testdir/subdir';
    $obj = new FileManager($this->content_path);

    $this->setExpectedException('FileManagerIOException');
    $obj->putDir($dirname);   
  }

  // --------------------------------------------------------------

  public function testPutDirSucceedsRecursivelyForDirWithoutParentDir() {

    $dirname = 'testdir/subdir';
    $obj = new FileManager($this->content_path);
    $obj->putDir($dirname, true);
    $this->assertFileExists($this->content_path . DIRECTORY_SEPARATOR . $dirname);
    rmdir($this->content_path . DIRECTORY_SEPARATOR . $dirname);
    rmdir($this->content_path . DIRECTORY_SEPARATOR . 'testdir');
  }

  // --------------------------------------------------------------

  public function testPutDirThrowsExceptionForExistentDir() {

    $caught = FALSE;

    try { 
      $dirname = 'testdir';
      $obj = new FileManager($this->content_path);
      $obj->putDir($dirname);
      $obj->putDir($dirname);
    } catch (FileManagerIOException $e) {
      $caught = TRUE;
    }

    rmdir($this->content_path . DIRECTORY_SEPARATOR . $dirname);

    if ( ! $caught) {
      $this->fail("Expected FileManagerIOException not caught!");
    }    
  }

  // --------------------------------------------------------------

  public function testDeleteDirThrowsExceptionForNonExistentDir() {

    $this->setExpectedException("FileManagerIOException");
    $obj = new FileManager($this->content_path);
    $obj->deleteDir('doesnotexist');
  }

  // --------------------------------------------------------------

  public function testDeleteDirSucceedsForEmptyDir() {

    $obj = new FileManager($this->content_path);
    $obj->putDir('doesexist');
    $obj->deleteDir('doesexist');

    $this->assertFalse(file_exists($this->content_path . DIRECTORY_SEPARATOR . 'doesexist'));
  }

  // --------------------------------------------------------------

  public function testDeleteDirFailsForNonEmptyDir() {

    $obj = new FileManager($this->content_path);
    $this->setExpectedException("FileManagerIOException");
    $obj->deleteDir('some_content');
  }

  // --------------------------------------------------------------

  public function testDeleteDirSucceedsForRecursiveNonEmptyDir() {

    $obj = new FileManager($this->content_path);
    $obj->deleteDir('some_content', TRUE);

    $this->assertFalse(file_exists($this->content_path . DIRECTORY_SEPARATOR . 'some_content'));
  }

  // --------------------------------------------------------------

  public function testGetDirReturnsFileListForExistentDir() {

    $obj = new FileManager($this->content_path);

    $match = array(
      'content.php' => (object) array('path' => 'some_content/content.php', 'name' => 'content.php', 'type' => 'file'),
      'meta.json'   => (object) array('path' => 'some_content/meta.json', 'name' => 'meta.json', 'type' => 'file'),
      'subcontent'  => (object) array('path' => 'some_content/subcontent', 'name' => 'subcontent', 'type' => 'dir'),
    );

    $this->assertEquals($match, $obj->getDir('some_content'));
  } 

  // --------------------------------------------------------------

  public function testGetDirReturnsFalseForNonExistentDir() {
    $obj = new FileManager($this->content_path);
    $this->assertFalse($obj->getDir('doesnotexistatall'));
  }

  // --------------------------------------------------------------

  public function testGetFileReturnsCorrectStatInfo() {

    $obj = new FileManager($this->content_path);
    $this->assertEquals(30, count($obj->getFile('some_content/content.php')));
  }

  // --------------------------------------------------------------

  public function testGetFileThrowsExceptionForNonExistentFile() {

    $obj = new FileManager($this->content_path);
    $this->setExpectedException('FileManagerIOException');
    $obj->getFile('some_content/doesntexist.php');    
  } 

  // --------------------------------------------------------------

  public function testDeleteFileReturnsTrueForExistentFile() {

    $obj = new FileManager($this->content_path);
    $this->assertTrue($obj->deleteFile('some_content/meta.json'));

  } 

  // --------------------------------------------------------------

  public function testDeleteFileThrowsExceptionForNonExistentFile() {

    $obj = new FileManager($this->content_path);
    $this->setExpectedException('FileManagerIOException');
    $obj->deleteFile('some_content/doesntexist.php');     
  }

  // --------------------------------------------------------------

  public function testPutFileSucceedsForNewFile() {

    $obj = new FileManager($this->content_path);
    $rval = $obj->putFile('somefile.txt', 'abc123');
    $this->assertEquals('somefile.txt', $rval);
    $this->assertFileExists($this->content_path . DIRECTORY_SEPARATOR . 'somefile.txt');
  }

  // --------------------------------------------------------------

  public function testPutFileThrowsExceptionForExistingFile() {

    $obj = new FileManager($this->content_path);
    $obj->putFile('somefile.txt', 'abc123');
    $this->setExpectedException('FileManagerIOException');
    $obj->putFile('somefile.txt', 'abc123');
  }

  // --------------------------------------------------------------

  public function testPutFileThrowsExceptionForNonExistentDir() {
    $obj = new FileManager($this->content_path);
    $this->setExpectedException('FileManagerIOException');
    $obj->putFile('nonexistentdir/somefile.txt', 'abc123'); 
  }

  // --------------------------------------------------------------

  public function testPutFileReturnsRelativePathForExistingFileWithOverrideOption() {

    $obj = new FileManager($this->content_path);
    $obj->putFile('somefile.txt', 'abc123');
    $obj->putFile('somefile.txt', 'def678', true);
    $this->assertEquals('def678', file_get_contents($this->content_path . DIRECTORY_SEPARATOR . 'somefile.txt'));
  }

  // --------------------------------------------------------------
   
  public function testPutFileThrowsExceptionForNonWritableDirectory() {

    $obj = new FileManager($this->content_path);
    mkdir($this->content_path . DIRECTORY_SEPARATOR . 'somedir');
    chmod($this->content_path . DIRECTORY_SEPARATOR . 'somedir', 0500);

    $this->setExpectedException('FileManagerIOException');
    $obj->putFile('somedir' . DIRECTORY_SEPARATOR . 'somefile.txt', 'abc123');

    chmod($this->content_path . DIRECTORY_SEPARATOR . 'somedir', 0600);
  }

  // --------------------------------------------------------------

  public function testPutFileCopiesFileSuccesfully() {

    $obj = new FileManager($this->content_path);
    $testFile = $this->content_path . DIRECTORY_SEPARATOR . 'some_content' . DIRECTORY_SEPARATOR . 'content.php';
    $obj->putFile('somefile.txt', $testFile, false, FileManager::PATH);
    $this->assertEquals('<p>Some Content Html</p>', file_get_contents($this->content_path . DIRECTORY_SEPARATOR . 'somefile.txt'));
  }

  // --------------------------------------------------------------

  public function testPutFileThrowsExceptionWhenTryingToCopyNonExistentFile() {

    $obj = new FileManager($this->content_path);
    $testFile = $this->content_path . 'some_content' . DIRECTORY_SEPARATOR . 'nopedoesntexist.php';
    $this->setExpectedException('FileManagerIOException');
    $obj->putFile('somefile.txt', $testFile, false, FileManager::PATH);
  }

  // --------------------------------------------------------------

  public function testPutFileThrowsExceptionForInavlidMode() {
    $obj = new FileManager($this->content_path);
    $testFile = $this->content_path . 'some_content' . DIRECTORY_SEPARATOR . 'nopedoesntexist.php';
    
    $this->setExpectedException("InvalidArgumentException");
    $obj->putFile('somefile.txt', $testFile, false, 3);
  }

  // --------------------------------------------------------------

  public function testStreamFileProducesOutput() {

      $obj = new FileManager($this->content_path);

      ob_start();
      $obj->streamFile('some_content/content.php');
      $result = ob_get_clean();

      $this->assertEquals('<p>Some Content Html</p>', $result);
  }

  // --------------------------------------------------------------

  public function testStreamFileThrowsExceptionForNonExistentFile() {

    $obj = new FileManager($this->content_path);
    $this->setExpectedException('FileManagerIOException');
    $obj->streamFile('doesnotexisttruely');
  }

  // --------------------------------------------------------------

  public function testStreamFileThrowsExceptinoForDirectory() {

    $obj = new FileManager($this->content_path);
    $this->setExpectedException('FileManagerIOException');
    $obj->streamFile('some_content');   
  }
}

/* EOF: FileManagerTest.php */