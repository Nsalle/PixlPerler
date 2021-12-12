<html>
 <head>
  <title>PHP-Test</title>
 </head>
 <body>
   <center><h1>Tests de pixl pour perles HAMA</h1></center>
  <?php
  include 'Pixl.class.php';
  $imgName = "pika.jpg";
  $handle = fopen("C:/xampp/htdocs/".$imgName, "r+");
  $image = imagecreatefromjpeg("C:/xampp/htdocs/".$imgName);
  $width = imagesx($image);
  $height = imagesy($image);

  $size = 0;
  if ($width < $height)
    $size = $width;
  else
    $size = $height;
    // basé sur une grille de perles de 58x58 perles
  $pixl = (int) ($size / 58);
  echo "Chaque perle représentera ".$pixl."x".$pixl." pixels de l'image<br>";
  echo "<br>Width: ".imagesx($image)."<br>";
  echo "Height: ".imagesy($image)."<br>";
  $im2 = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => $pixl * 58, 'height' => $pixl * 58]);

  $loop = new Pixl();
  $colorCount = array();
  $colorCount = $loop->pixlLoop($pixl, $im2);
  imagedestroy($image);
   ?>
   <img src="http://localhost/<?php echo $imgName;?>" alt="<?php echo $imgName;?>">
   <!--<img src="http://localhost/testallo.jpg" alt="Image Pixl" width="580" height="580">-->
   <img src="http://localhost/testPixl.jpg" alt="Image Pixl" width="580" height="580">
   <img src="http://localhost/testAlgo.jpg" alt="Image Pixl" width="580" height="580">
 </body>
</html>