<!DOCTYPE html>

<html>
<head>
    </style>
    </head>
    <body>
<?php
  //get data
$firstName = $_GET['firstName'];
$lastName = $_GET['lastName'];
$studentId = $_GET['studentId'];
$skillLevel = $_GET['skillLevel'];
$instrument = $_GET['instrument'];
$location = $_GET['location'];
$room = $_GET['room'];
$time = $_GET['time'];
$firstName2 = $_GET['firstName2'];
$lastName2 = $_GET['lastName2'];
$studentId2 = $_GET['studentId2'];
$skillLevel2 = $_GET['skillLevel2'];
$instrument2 = $_GET['instrument2'];


echo "<b>Registered Performers:</b><br/><br/>";
  //write to file
$file = fopen("test.txt","a");
if ($firstName2 ==="" && $firstName != "")
{
   fwrite($file,
          "        
        Performers Name: <b>$firstName $lastName</b> <br/>
        Performers Student ID: <i>$studentId</i><br/>
        Performers Skill Level: <i>$skillLevel</i><br/>
        Performers Instrument: <i>$instrument</i><br/>
        Location: <i>$location</i>, Room: <i>$room</i><br/>
        Time: <i>$time</i>
        <hr/>"         
       );
}
else if($firstName != "")
{
fwrite($file,
       "1st Performers Name: <b>$firstName $lastName </b><br/>
        1st Performers Student ID: <i>$studentId</i><br/>
        1st Performers Skill Level: <i>$skillLevel</i><br/>
        1st Performers Instrument: <i>$instrument</i><br/>
        2nd Performers Name: <b>$firstName2 $lastName2</i></b><br/>
        2nd Performers Student ID: <i>$studentId2</i><br/>
        2nd Performers Skill Level: <i>$skillLevel2</i><br/>
        2nd Performers Instrument: <i>$instrument2</i><br/>
        Location: <i>$location</i>, Room: <i>$room</i><br/>
        Time: <i>$time</i><br/>
        <hr/>"
       );
}
fclose($file);

// read file
$file = fopen("test.txt","r");
echo fread($file,filesize("test.txt"));
fclose($file);


?>

</body>
</html>