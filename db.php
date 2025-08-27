<?php
$dbservername="db";
$dbusername="root";
$dbpassword="root";
$database="inventory";

$conn=new mysqli($dbservername,$dbusername,$dbpassword,$database);

if ($conn->connect_error)
{
    die("connection failed: ".$conn->connect_error);
}
?>