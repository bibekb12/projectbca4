<?php
include_once 'db.php';

if($_SERVER['REQUEST_METHOD']=="POST")
{
    $id=intval($_POST['id']);
    $itemcode=$_POST['itemcode'];
    $itemname=$_POST['name'];
    $itemdesciption=$_POST['description'];
    $status=$_POST['status'];
    $price=floatval($_POST['price']);

    // var_dump($_POST);
    // die;

    if(!empty($id))
    {
        $query="UPDATE items SET itemcode='$itemcode',itemname='$itemname',description='$itemdesciption',status='$status',sell_price='$price' where id=$id";
    }
    else
    {
        $query="INSERT INTO items (itemcode,itemname,description,status,sell_price) VALUES ('$itemcode','$itemname','$itemdesciption','$status','$price')";
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
header('Location: index.php');
exit();
?>