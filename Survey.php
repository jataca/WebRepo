<?php
session_start();

?>

<!DOCTYPE HTML>
<html>
<title>PHP Survey</title>
<head>
  <link rel="stylesheet" type="text/css" href="homestyles.css">
  <title>PHP Survey</title>
</head>
<body>
  <div class="wrapper">
      <ul >
          <li><a href="http://php-jcasperson.rhcloud.com/home.html">Home</a></li>
          <li><a href="http://php-jcasperson.rhcloud.com/aboutme.html">About Me</a></li>
          <li><a href="http://php-jcasperson.rhcloud.com/contact.html">Contact</a></li></li>
          <li><a href="">Assignments (Coming soon)</a></li>
      </ul>
  </div>
  <div class="center">
    <h1>Assignment 1: PHP Survey</h1>
  </div><br>

  <div class="center">

<form action="welcome.php" method="post">
  Name: <input type="text" name="name"><br>
  E-mail: <input type="text" name="email"><br><br>

  1.Gender: <br>
  <input type="radio" name="q1" value="male">Male<br>
  <input type="radio" name="q1" value="female">Female<br><br>

  2.Favorite color: <br>
  <input type="radio" name="q2" value="blue">Blue<br>
  <input type="radio" name="q2" value="green">Green<br>
  <input type="radio" name="q2" value="yellow">Yellow<br>
  <input type="radio" name="q2" value="orange">Orange<br>
  <input type="radio" name="q2" value="black">Black<br>
  <input type="radio" name="q2" value="white">White<br>
  <input type="radio" name="q2" value="brown">Brown<br>
  <input type="radio" name="q2" value="gray">Gray<br>
  <input type="radio" name="q2" value="red">Red<br><br>

  3.Star Wars or Star Trek?: <br>
  <input type="radio" name="q3" value="star wars">Star Wars<br>
  <input type="radio" name="q3" value="star trek">Star Trek<br>
  <input type="radio" name="q3" value="neither">Neither<br><br>

  4.Favorite operating system: <br>
  <input type="radio" name="q4" value="windows">Windows<br>
  <input type="radio" name="q4" value="linux">Linux<br>
  <input type="radio" name="q4" value="mac">Mac<br><br>

  5.Favorite super hero: <br>
  <input type="radio" name="q5" value="batman">Batman<br>
  <input type="radio" name="q5" value="superman">Superman<br>
  <input type="radio" name="q5" value="spiderman">Spiderman<br>
  <input type="radio" name="q5" value="black widow">Black Widow<br>
  <input type="radio" name="q5" value="other">other<br><br>

  <input type="submit">
</form>

<form action = "results.php" method="post">
  <input type="submit" value="View results">
</form>

</div>

<?php
  $visits = 0;
  $countKey = 'pageCount';
  if (isset($_SESSION[$countKey]))
  {
     $visits = $_SESSION[$countKey];
  }

  $visits++;

  $_SESSION[$countKey] = $visits;
  echo "You have been here $visits times<br />";

  if ($visits > 1){
    echo "<script type='text/javascript'>window.location.assign('results.php')</script>";
  }



?>

</body>
</html>
