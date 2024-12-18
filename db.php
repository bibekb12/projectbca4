<?php
$servername="localhost";
$username="root";
$password="";

$conn=new mysqli($servername,$username,$password);

if ($conn->connect_error)
{
    die("connection failed: ".$conn->connect_error);
}
else
{
header("location:dashboard.php");
//echo "connection succeded";
}
?>