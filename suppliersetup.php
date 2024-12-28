<?php
include 'db.php';
$id="";
$suppliername="";
$pannumber="";
$suppliercontact="";
$status="Y";

if(isset($_GET['id'])){
    $id=intval($_GET['id']);
    $result=$conn->query("select id,name,contact,panno,status,created_at from suppliers where id=$id");

    if($result && $result->num_rows>0)
    {
        $supplier=$result->fetch_assoc();
        $suppliername=$supplier['name'];
        $status=$supplier['status'];
        $pannumber=$supplier['panno'];
        $suppliercontact=$supplier['contact'];
    }
    else
    {
        echo "No supplier found";
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Setup</title>
</head>
<body>
    <h1> Supplier setup</h1>
    <form method="POST" action="add_supplier.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <label>Supplier Name: </label>
        <input type="text" name="suppliername" value="<?php echo $suppliername; ?>" placeholder="suppliername" required>
        <label>Contact No: </label>
        <input type="text" name="contactno" value="<?php echo $suppliercontact; ?>" placeholder="contact" >
        <label>Pan No: </label>
        <input type="text" name="pannumber" value="<?php echo $pannumber; ?>" placeholder="pan number">
        <label>
            <input type="radio" name="status" value="Y"<?php echo ($status==='Y')? 'checked':'';?>>Active</label>
        <label>
            <input type="radio" name="status" value="N"<?php echo ($status==='N')? 'checked':'';?>>Inactive</label>
        <button type="submit">Save</button>
</form>
    <h2> List of all supplier </h2>
    <table border="1">
        <tr>
            <td>Id</td>
            <td>Supplier Name</td>
            <td>Supplier Contact</td>
            <td> Supplier Pan No</td>
            <td> Supplier Status</td>
            <td>Action</td>
</tr>
<?php
$result=$conn->query("select id,name,contact,panno,status,created_at from suppliers");
if($result && $result->num_rows>0)
{
    while($row=$result->fetch_assoc())
    {
        $status=($row['status']==='Y')?'Active':'Inactive';
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['contact']}</td>
            <td>{$row['panno']}</td>
            <td>$status</td>
            <td><a href='suppliersetup.php?id={$row['id']}'>Edit</a></td>
        </tr>";
    }
}
    else
    {
        echo "<tr><td colspan='6' align='center'>No supplier found</td></tr>";
    }

?>
</table>
<br><a href="setup.php"> Back </a>
</body>
</html>
