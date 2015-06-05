<?php
$myfile = fopen("testfile.txt", "w") or die("Unable to open file!");
fwrite($myfile, $_POST["name"]);
fwrite($myfile, "WHAT");
fclose($myfile);
?>
<html>
<body>

  Welcome <?php echo $_POST["name"]; ?><br>
  Your email address is: <?php echo $_POST["email"]; ?>
</body>
</html>
