<!DOCTYPE HTML>
<html>
<head>
<title>Hello World</title>
<link rel="stylesheet" type="text/css" href="homestyles.css">

</head>
<body>
  <div class="wrapper">
      <ul >
          <li><a href="http://php-jcasperson.rhcloud.com/home.html">Home</a></li>
          <li><a href="http://php-jcasperson.rhcloud.com/aboutme.html">About Me</a></li>
          <li><a href="http://php-jcasperson.rhcloud.com/contact.html">Contact</a></li></li>
          <li><a href="http://php-jcasperson.rhcloud.com/assignments.html">Assignments</a></li>
      </ul>
  </div>
  <?php

  $myfile = fopen("ch09.txt", "r") or die("Unable to open file!");
  $array = file("ch09.txt", FILE_IGNORE_NEW_LINES);
  fclose($myfile);


  echo "Male: " . $array[0] . "<br>";
  echo "Female: " . $array[1] . "<br>" . "<br>";
  echo "Blue: " . $array[2] . "<br>";
  echo "Green: " . $array[3] . "<br>";
  echo "Yellow: " . $array[4] . "<br>";
  echo "Orange: " . $array[5] . "<br>";
  echo "Black: " . $array[6] . "<br>";
  echo "White: " . $array[7] . "<br>";
  echo "Brown: " . $array[8] . "<br>";
  echo "Gray: " . $array[9] . "<br>";
  echo "Red: " . $array[10] . "<br>" . "<br>";
  echo "Star Wars: " . $array[11] . "<br>";
  echo "Star Trek: " . $array[12] . "<br>";
  echo "Neither: " . $array[13] . "<br>" . "<br>";
  echo "Windows: " . $array[14] . "<br>";
  echo "Linux: " . $array[15] . "<br>";
  echo "Mac: " . $array[16] . "<br>" . "<br>";
  echo "Batman: " . $array[17] . "<br>";
  echo "Superman: " . $array[18] . "<br>";
  echo "Spiderman: " . $array[19] . "<br>";
  echo "Black Widow: " . $array[20] . "<br>";
  echo "Other: " . $array[21] . "<br>" . "<br>";
  echo $_SESSION['count'] . "<br>";


  $out = fopen("ch09.txt", "w");
  foreach($array as $line){
    $line .= "\n";
    fwrite($out, $line);
}

  ?>
</body>
</html>
