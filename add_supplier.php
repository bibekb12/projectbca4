<?php
include_once 'db.php';

$id = "";
$row = ["name" => "", "status" => "Y"];

// if (isset($_GET['id'])) {
//     $id = intval($_GET['id']);
//     $result = $conn->query("SELECT id, name, contact, panno, status FROM suppliers WHERE id = $id");
    
//     if ($result && $result->num_rows > 0) {
//         $row = $result->fetch_assoc();
//     } else {
//         echo "No supplier found with the given ID.";
//     }
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $suppliername = $conn->real_escape_string($_POST['suppliername']);
    $pannumber = $conn->real_escape_string($_POST['pannumber']);
    $status = $conn->real_escape_string($_POST['status']);
    $contactnumber = $conn->real_escape_string($_POST['contactno']);

    if (!empty($id)) 
    {
        $sql = "UPDATE suppliers SET name = '$suppliername', contact = '$contactnumber',panno = '$pannumber', status = '$status' WHERE id = $id";
    } 
    else 
    {
        $sql = "INSERT INTO suppliers (name, contact, panno, status) VALUES ('$suppliername', '$contactnumber', '$pannumber', '$status')";
    }

    if ($conn->query($sql) === TRUE) {
        echo $id ? "Supplier updated" : "New supplier added";
        header("Location: suppliersetup.php");
        exit();
    } else 
    {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
