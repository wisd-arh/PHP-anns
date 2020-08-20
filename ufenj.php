<?php
define("csFileStore", '../www/foto/');
//define("csFileStore", 'C:\Inetpub\wwwroot\foto\\');
define("cWidth", 300);
define("cHeight", 225);

define("capWidth", 100);
define("capHeight", 28);
define("maxlen", 5000);


function FileProcessor(&$fuid, $fname) {
  if (!strlen($fname)) return 0;

  if ($_FILES[$fname]['size'] == 0) return 0;

  $fuid = NewFUID();
  SaveFileStream(csFileStore . $fuid, $fname);
  LoadBitmap(csFileStore . $fuid);
}

function SaveFileStream($fuid, $fname) {
  $tmp = $_FILES[$fname]['tmp_name'];
  @rename($tmp, $fuid);
}

function LoadBitmap($fuid) {
  $im = @imagecreatefromjpeg($fuid); /* Attempt to open */
  chmod($fuid, 0777);
  list($width, $height) = @getimagesize($fuid);

  if (($width > cWidth) or ($height > cHeight)) {
    $w = $width;
    $h = $height;
    $deltaw = 0;
    $deltah = 0;
    if (abs($w/$h - cWidth/cHeight) > 0.01) { // бесконечная дробь
      if ($w/$h > cWidth/cHeight) {
//Изменяем ширину.
        $w = round($h*cWidth/cHeight);
        $deltaw = -round(($width-$w) / 2);
      } else {
// Изменяем высоту
        $h = round($w/(cWidth/cHeight));
        $deltah = -round($height-$h) / 2;
      }
    }
    unlink($fuid);
    $dst_image = imagecreatetruecolor(cWidth, cHeight);
    @imagecopyresampled($dst_image, $im, 0, 0, -$deltaw, -$deltah, cWidth, cHeight, $w, $h);

    @imagedestroy($im);

    if (@!imagejpeg ($dst_image, $fuid, 95)) {
      @imagedestroy($dst_image);
      return 0;
    } else {
      chmod($fuid, 0644);
      @imagedestroy($dst_image);
      return 1;
    }
  }
  @imagedestroy($im);
}

function DelCappa($capfname) {
  try {
    unlink("../www/" . $capfname);
  } catch(Exception $e) {

  }

}

function NewCappaFName(){
  $s = "foto/";
  $s = $s . rand(0, 9);
  $s = $s . rand(0, 9);
  $s = $s . rand(0, 9);
  $s = $s . rand(0, 9);
  $s = $s . rand(0, 9);
  $s = $s . rand(0, 9);
  $s = $s . rand(0, 9);
  $s = $s . '.jpg';
  return $s;
}

function NewCappaNum(){
  $s = "";
  for ($i=0; $i<=4; $i++) {
    $s .= rand(0, 9);
  }
  return $s;
}

function p($str) {
  echo "$str<br>";
}

function NewCappa(&$cappa, &$fname) {

  $cappa = NewCappaNum();
  $b = imagecreatetruecolor(capWidth, capHeight);

  imagefilledrectangle($b, 0, 0, capWidth, capHeight, rand(0, 1000));

  $step = 0;
  for ($i = 0; $i <=4; $i++) {
    imagettftext($b, 14, 0, 10+rand(0, 2) + $step, 20 + rand(0, 2), 0xFFFFFF - rand(0, 1000), '/var/www/ba2175/veder.net.ru/cgi-bin/arial.ttf', $cappa{$i});
    $step+=15;
  }

  for ($i = 0; $i <=50; $i++) {
    imagesetpixel($b, rand(0, capWidth), rand(0, capHeight), 0xFFFFFF - rand(0, 1000));
  }

  $fname = NewCappaFName();
  if (!imagejpeg ($b, "../www/" . $fname, 95))
    return 0;
  else {
    chmod("../www/" . $fname, 0644);
    return 1;
  }
}

?>