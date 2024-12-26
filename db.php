<?php
$dbservername="localhost";
$dbusername="root";
$dbpassword="";
$database="inventory";

$conn=new mysqli($dbservername,$dbusername,$dbpassword,$database);

if ($conn->connect_error)
{
    die("connection failed: ".$conn->connect_error);
}
?>