<?php
$servername="localhost";
$username="root";
$password="";
$database="inventory";

$conn=new mysqli($servername,$username,$password,$database);

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