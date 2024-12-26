<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>user setup</title>
</head>
<body>
    <h1> user setup</h1>
    <form method="POST" action="add_user.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="text" name="username" placeholder="username" required>
        <input type="password" name="password" placeholder="password">
        <label>
            <input type="radio" name="active" value="Y" checked> Active
        </label>
        <label>
            <input type="radio" name="active" value="N"> Inactive
        </label>
        <button type="submit"> <?php echo $id ? 'Update': 'Save'; ?> </button> 

    <h2> All Users List </h2>
    <table border="1">
        <tr>
            <th>Id</th>
            <th>Username</th>
            <th>  status</th>
            <th> created_at</th>
            <th>Action</th>
    </tr>
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

$result=$conn->query("select id,username,status,created_at from users");
while ($row=$result->fetch_assoc()){
    $status=$row['status']==='Y'?'Active':'Inactive';
    echo"<tr>
    <td>{$row['id']}</td>
    <td>{$row['username']}</td>
    <td>{$row['status']}</td>
    <td>{$row['created_at']}</td>
    <td>
    <a href='usersetup.php?id={$row['id']}'>Edit</a>
    </td>
    </tr>";
}
}
$conn->close();
?>
</table>
</body>
</html>