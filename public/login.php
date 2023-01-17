<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require "internals/db_conn.php";

if (isset($_POST['email']) && isset($_POST['password'])) {

    function validate($connection, $data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string($connection, $data);
        return $data;
    }

    $uname = validate($conn, $_POST['email']);
    $pass = validate($conn, $_POST['password']);

    if (empty($uname)) {
        header("Location: login.php?error=Email is required");
        exit();
    }else if(empty($pass)){
        header("Location: login.php?error=Password is required");
        exit();
    }else{
        $sql = "SELECT * FROM users WHERE user_name='$uname'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if ($row['user_name'] === $uname && password_verify($pass, $row['password'])) {
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['id'] = $row['id'];
                if(isset($_GET['continue'])) {
                    header("Location: " . $_GET['continue']);
                    exit();
                }
                header("Location: index.php");
                exit();
            }else{
                header("Location: login.php?error=Incorrect Email or password");
                exit();
            }
        }else{
            header("Location: login.php?error=Incorrect Email or password");
            exit();
        }
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<body>
<form action="login.php<?= isset($_GET['continue']) ? "?continue=" . htmlspecialchars($_GET['continue']) : "" ?>" method="post">
    <h2>LOGIN</h2>
    <?php if (isset($_GET['error'])) { ?>
        <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php } ?>
    <label>Email</label>
    <input type="email" name="email" placeholder="Email" required><br>

    <label>Password</label>
    <input type="password" name="password" placeholder="Password" required><br>

    <button type="submit">Login</button>
</form>
<p>No account? <a href="register.php">Register</a></p>
</body>
</html>