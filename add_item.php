<?php
include_once 'db.php';

if($_SERVER['REQUEST_METHOD']=="POST")
{
    $id=intval($_POST['id']);
    $itemcode=$_POST['itemcode'];
    $itemname=$_POST['itemname'];
    $itemdesciption=$_POST['description'];
    $status=$_POST['status'];
    $price=floatval($_POST['price']);

    if(!empty($id))
    {
        $query="UPDATE items SET itemcode='$itemcode',name='$itemname',description='$itemdesciption',status='$status',price='$price' where id=$id";
    }
    else
    {
        $query="INSERT INTO items (itemcode,name,description,status,price) VALUES ('$itemcode','$itemname','$itemdesciption','$status','$price')";
    }
    if ($conn->query($query)===TRUE)
    {
        echo $id ? "Record Updated Successfully" : "Record Inserted Successfully";
        header ("location:itemsetup.php");
        exit();
    }
    else
    {
        echo "Error".$query."<br>". $conn->error;
    }
}
$conn->close();
?>