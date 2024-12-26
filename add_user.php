<?php

        $username = $_POST['username'];
        $password = $_POST['password'];
        $active = $_POST['active'];

        // Database connection
        $servername="localhost";
        $dbusername="root";
        $dbpassword="";
        $database="inventory";

        $conn=new mysqli($servername,$dbusername,$dbpassword,$database);

        // Check connection
        if ($conn->connect_error) 
        {
            die("Connection failed: " . $conn->connect_error);
        }

        $id='';
        $user=["username"=>"","status"=>"Y"];

        if (isset($_GET['id']))
        {
            $id=intval($_GET['id']);
            $result=$conn->query("select id,username,status from users where id=$id");
            $user=$result->fetch_assoc();
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST") 
        {
            $id=intval($_POST['id']);
            $username = $_POST['username'];
            $password = $_POST['password'];
            $active = $_POST['active'];
            if ($id)
            {
                if(!empty($password))
                {
                   $sql=("UPDATE users SET username='$username', password='$password', active='$active',updated_at=NOW() where id=$id");
                    if ($conn->query($sql) === TRUE) 
                    {
                        echo "New user updated successfully";
                        header("Location: usersetup.php"); 
                        exit();
                    } 
                }
                else
                {
                    $sql=("UPDATE users SET username='$username', active='$active', updated_at=NOW() where id=$id");
                    if ($conn->query($sql) === TRUE) 
                    {
                        echo "New user updated successfully";
                        header("Location: usersetup.php"); 
                        exit();
                    } 
                }
            }
            else
            {
            $sql = "INSERT INTO users (username, password, status) VALUES ('$username', '$password', '$active')";
            if ($conn->query($sql) === TRUE) 
                {
                    echo "New user added successfully";
                    header("Location: usersetup.php"); 
                    exit();
                }
            }
        }
        $conn->close();
        
?>