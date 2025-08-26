<?php
$dbservername="sql213.infinityfree.com";
$dbusername="if0_39797069";
$dbpassword="zJ2pNvA3k6G";
$database="if0_39797069_inventory";

$conn=new mysqli($dbservername,$dbusername,$dbpassword,$database);

if ($conn->connect_error)
{
    die("connection failed: ".$conn->connect_error);
}
?>