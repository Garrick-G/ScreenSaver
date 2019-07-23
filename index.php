<!doctype html>
<?php
  $page = $_SERVER['PHP_SELF'];
  $time = 1800;
?>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>The HTML5 Herald</title>
  <meta name="description" content="The HTML5 Herald">
  <meta name="author" content="SitePoint">
  <meta http-equiv="refresh" content="<?php echo $time ?>;URL='<?php echo $page ?>'">
  <link rel="stylesheet" type="text/css" href="main.css" />
  <script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src = "main.js"></script>
  <script type="text/javascript">
  <?php
  $arrayOfFiles = array();
  $arrContextOptions=array(
    "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
      ),
    );
  $ini = parse_ini_file('config.ini',true);
  $colorsArray = array();
  foreach($ini as $key => $value){
    $color = $value['color'];
    $response = file_get_contents($value['URL'], false, stream_context_create($arrContextOptions));
    file_put_contents( $key.'.ics', $response);
    $tempFile = ($key.'.ics');
    $arrayOfFiles[$key] = ($tempFile);
    $colorsArray[$key] = ($color);
  }
    ?>
  </script>
</head>

<body>
  <div class="fade">
  </div>
  <div class="dateTime"></div>
  <div class="test-flex flex-container">
  </div>
  <div class="calendar_tasks flex-row">
  <?php

  require 'calendar.php';
  calTestFunc($arrayOfFiles, $colorsArray);
  ?>
  </div>
  <?php
  function imageCreateFromAny($filepath) {
      $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
      $allowedTypes = array(
          1,  // [] gif
          2,  // [] jpg
          3,  // [] png
          6   // [] bmp
      );
      if (!in_array($type, $allowedTypes)) {
          return false;
      }
      else{
      return true;
    }
  }
  $randImg = array();
  $dir = 'images';
  $iterator = new DirectoryIterator($dir);
  foreach ($iterator as $fileinfo) {
      if (!$fileinfo->isDot()) {
        if(imageCreateFromAny(($dir.'\\'.$fileinfo->getFilename()))){
            $randImg[] = $fileinfo->getFilename();
        }
      }
  }
  shuffle($randImg);
   ?>
  <script type="text/javascript">

      var imgArray = [];
      <?php for($x = 0; $x < count($randImg); $x++) {?>
        imgArray.push('<?php echo $randImg[$x]?>');
        <?php } ?>
  var dir = 'images/';
  var index = 0;
  function slideShow(){
    var tempImage = new Image();
    tempImage.src = dir+imgArray[index];
    var height = tempImage.height;
    var width = tempImage.width;
    if(portrait.matches){
      document.getElementsByClassName('fade')[0].style.backgroundSize = "contain";
    }
    if(height > width ){
      if(query.matches){
        document.getElementsByClassName('fade')[0].style.backgroundSize = "100vw 100vh";
      }
      else{
        document.getElementsByClassName('fade')[0].style.backgroundSize = "contain";
      }
    }
    else{
      if(query.matches){
        document.getElementsByClassName('fade')[0].style.backgroundSize = "contain";
      }
      else{
        document.getElementsByClassName('fade')[0].style.backgroundSize = "100vw 100vh";
      }
    }
    document.getElementsByClassName('fade')[0].style.backgroundImage = "url('"+dir+imgArray[index]+"')";
    index++;
    if(index >= imgArray.length){index = 0;}
    setTimeout(slideShow, 5000);
  }
    var query = window.matchMedia("(max-width: 600px)");
    var portrait = window.matchMedia("(orientation: portrait)");
    slideShow();
    function currentDate(){
      <?php
      $currentDate = new DateTime();
      $currentDate -> setTimeZone(new DateTimeZone('America/Chicago'));
      $currentDt = $currentDate->format('l, M d');
      $currentTime = $currentDate->format('h:ia');
      date_default_timezone_set('America/Chicago'); // CDT
      $current_date = date('l, M d');
      $current_time = date('h:ia');
      ?>
      //$(".dateTime").html('<h1 class="time"><?php echo $current_time;?></h1>'+'<h2 class="date"><?php echo $current_date; ?></h2>');
    }
  </script>
</body>
</html>
