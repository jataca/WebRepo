<html>
<body>
  <?php
  $myfile = fopen("testfile.txt", "w") or die("Unable to open file!");
  fwrite($myfile, $_POST["name"]);
  ?>
  Welcome <?php echo $_POST["name"]; ?><br>
  Your email address is: <?php echo $_POST["email"]; ?>
  fclose($myfile);
</body>
</html>
