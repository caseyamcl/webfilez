<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Uploader!</title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
  <link rel="stylesheet" href="css/main.css" />
</head>

<body>
  <header>
    <h1>Uploader</h1>
  </header>

  <div id="main" class="main">
    
    <section id="uploader">
      
      <div id="current_upload">
        <span id="current_upload_filename" style="display: none;">No Uploads in Progress</span>
        <span id="current_upload_status">No Uploads in Progress</span>
        <button id="upload_start">Go</button>
      </div>

      <p id="queue_status">
        No file queued
      </p>
      
      <ol id="upload_queue">
      </ol>
      
    </section>
    
    <section id="filemgr">
      <ul id='filelist'><li class='loading'>Loading...</li></ul>
    </section>
    
    
  </div>

  <footer>
    <p>Footer - Status Bar</p>
  </footer>
  
  <?php echo "<script>server_url = 'http://localhost/webfilez/testserver/';</script>"; ?>
  <script src="js/md5.js"></script>
  <script src="js/jquery.js"></script>
  <script src="js/interface.js"></script>
  <script src="js/queuemgr.js"></script>
  <script src="js/queueprocessor.js"></script>
  <script src="js/filemgr.js"></script>
  <script src="js/scripts.js"></script>
</body>

</html>