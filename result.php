<?php
session_start();

?>

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


  echo "<br>" . "<br>";
  $visits = 0;
  $countKey = 'pageCount';
  if (isset($_SESSION[$countKey]))
  {
	   $visits = $_SESSION[$countKey];
  }

  $visits++;

  $_SESSION[$countKey] = $visits;

if ($visits == 1){

  if ($_POST['q1'] == 'male') {
    $array[0] += 1; // male
  }
  else {
    $array[1] += 1; // female
  }

  if ($_POST['q2'] == 'blue') {
    $array[2] += 1; // blue
  }
  elseif ($_POST['q2'] == 'green'){
    $array[3] += 1; // green
  }
  elseif ($_POST['q2'] == 'yellow'){
    $array[4] += 1; // yellow
  }
  elseif ($_POST['q2'] == 'orange'){
    $array[5] += 1; // orange
  }
  elseif ($_POST['q2'] == 'black'){
    $array[6] += 1; // black
  }
  elseif ($_POST['q2'] == 'white'){
    $array[7] += 1; // white
  }
  elseif ($_POST['q2'] == 'brown'){
    $array[8] += 1; // brown
  }
  elseif ($_POST['q2'] == 'gray'){
    $array[9] += 1; // gray
  }
  else {
    $array[10] += 1; // red
  }

  if ($_POST['q3'] == 'star wars') {
    $array[11] += 1; // star wars
  }
  elseif ($_POST['q3'] == 'star trek'){
    $array[12] += 1; // star trek
  }
  else {
    $array[13] += 1; // neither
  }

  if ($_POST['q4'] == 'windows') {
    $array[14] += 1; // windows
  }
  elseif ($_POST['q4'] == 'linux'){
    $array[15] += 1; // linux
  }
  else {
    $array[16] += 1; // mac
  }

  if ($_POST['q5'] == 'batman') {
    $array[17] += 1; // batman
  }
  elseif ($_POST['q5'] == 'superman'){
    $array[18] += 1; // superman
  }
  elseif ($_POST['q5'] == 'spiderman'){
    $array[19] += 1; // spiderman
  }
  elseif ($_POST['q5'] == 'black widow'){
    $array[20] += 1; // black widow
  }
  else {
    $array[21] += 1; // other
  }


}

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
  echo "Neither: " . $array[13] . "<br> . <br>";
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
