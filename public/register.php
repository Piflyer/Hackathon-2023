<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require "internals/db_conn.php";

if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['name'])) {
    function validate($connection, $data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string($connection, $data);
        return $data;
    }

    $uname = validate($conn, $_POST['email']);
    $pass = password_hash(validate($conn, $_POST['password']), PASSWORD_DEFAULT);
    $name = validate($conn, $_POST['name']);

    if (empty($uname)) {
        header("Location: register.php?error=Email is required");
        exit();
    }else if(empty($pass)){
        header("Location: register.php?error=Password is required");
        exit();
    }
    else if(empty($name)){
        header("Location: register.php?error=Name is required");
        }
    else
    {
        $sql = "SELECT * FROM users WHERE user_name='$uname'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 0) {
            $sql = "INSERT INTO `users` (`id`, `user_name`, `password`, `name`) VALUES (NULL, '$uname', '$pass', '$name')";
            $result = mysqli_query($conn, $sql);
            if($result) {
                header("Location: login.php?error=Your account has been created successfully, you can now login");
            }
            }else{
                header("Location: register.php?error=An account with that email already exists");
                exit();
            }
    }

    }

?>
<!DOCTYPE html>
<html lang="en">
<body>
<form action="register.php" method="post">
    <h2>REGISTER</h2>
    <?php if (isset($_GET['error'])) { ?>
        <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php } ?>
    <label>Email</label>
    <input type="email" name="email" placeholder="Email" required><br>

    <label>Password</label>
    <input type="password" name="password" placeholder="Password" required><br>

    <label>Name</label>
    <input type="text" name="name" placeholder="Name" required><br>

    <button type="submit">Register</button>
</form>
</body>
</html>
