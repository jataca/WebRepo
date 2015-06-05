<!DOCTYPE html>
<?php
//  $myfile = fopen("webdictionary.txt", "w") or die("Unable to open file!");
//  fwrite($myfile, $_POST["name"]);
//  fclose($myfile);
?>
<html>
<body>

  Welcome <?php echo $_POST["name"]; ?><br>
  Your email address is: <?php echo $_POST["email"]; ?>

  <?php
  $myfile = fopen("webdictionary2.txt", "r") or die("Unable to open file!");
  echo fread($myfile,filesize("webdictionary.txt"));
  fclose($myfile);
  ?>
</body>
</html>
