<?php
include_once 'db.php';

if($_SERVER['REQUEST_METHOD']=="POST")
{
    $id=intval($_POST['id']);
    $itemcode=$_POST['itemcode'];
    $itemname=$_POST['itemname'];
    $itemdesciption=$_POST['description'];
    $staus=$_POST['status'];

    if(!empty($id))
    {
        $query="UPDATE items SET itemcode='$itemcode',name='$itemname',description='$itemdesciption',status='$staus' where id=$id";
    }
    else
    {
        $query="INSERT INTO items (itemcode,name,description,status) VALUES ('$itemcode','$itemname','$itemdesciption','$status')";
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