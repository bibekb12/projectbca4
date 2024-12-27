<?php
include 'db.php';
$id="";
$itemcode="";
$itemname="";
$description="";
$status="Y";

if (isset($_GET['id']))
{
    $id=intval($_GET['id']);
    $result=$conn->query("select id,itemcode,name,description,status from items where id=$id");

    if($result && $result->num_rows>0)
    {
        $item=$result->fetch_assoc();
        $itemcode=$item['itemcode'];
        $itemname=$item['name'];
        $status=$item['status'];
        $description=$item['description'];
    }
    else
    {
        echo "No item found";
    }
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Setup</title>
</head>
<body>
    <h1> Item Setup</h1>
    <form method="POST" action="add_item.php">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <label> Item code: </label>
    <input type="text" name="itemcode" value="<?php echo $itemcode; ?>">
    <label> Item name: </label>
    <input type="text" name="itemname" value="<?php echo $itemname; ?>">
    <label> Description: </label>
    <input type="text" name="description" value="<?php echo $description; ?>">
    <label>
            <input type="radio" name="status" value="Y"<?php echo ($status==='Y')? 'checked':'';?>>Active</label>
        <label>
            <input type="radio" name="status" value="N"<?php echo ($status==='N')? 'checked':'';?>>Inactive</label>
        <button type="submit">Save</button>
</form>
    <h2>List of all items</h2>
    <table border="1">
        <tr>
            <td>Id</td>
            <td>Item Code</td>
            <td>Item Name</td>
            <td>Item Status</td>
            <td>Item Description</td>
            <td>Action</td>
</tr>
<?php
$result=$conn->query("select id,itemcode,name,status,description from items");
if($result && $result->num_rows>0)
{
    while($row=$result->fetch_assoc())
    {
        $status=($row['status']==='Y'?'Active':'Inactive');
        echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['itemcode']}</td>
        <td>{$row['name']}</td>
        <td>{$status}</td>
        <td>{$row['description']}</td>
        <td><a href='itemsetup.php?id={$row['id']}'>Edit</a></td>
        </tr>";
    }
}
    else
    {
        echo "<tr><td colspan='6'>No items found</td></tr>";
    }

?>
<table>
<br><a href="setup.php"> Back </a>
</body>
</html>