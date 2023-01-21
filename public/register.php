<?php
require "internals/errors_if_testing.php";
session_start();
require "internals/db_conn.php";

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {
    header("Location: index.php?error=You are already logged in");
    exit();
}

if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['name'])) {
    function validate($connection, $data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string($connection, $data);
        return $data;
    }

    $uname = validate($conn, $_POST['email']);
    $pass = validate($conn, $_POST['password']);
    $passhashed = password_hash(validate($conn, $_POST['password']), PASSWORD_DEFAULT);
    $name = validate($conn, $_POST['name']);

    if (empty($uname)) {
        header("Location: register.php?error=Email is required");
        exit();
    } else if (empty($pass)) {
        header("Location: register.php?error=Password is required");
        exit();
    } else if (empty($name)) {
        header("Location: register.php?error=Name is required");
    } else {
        $sql = "SELECT * FROM users WHERE user_name='$uname'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 0) {
            $sql = "INSERT INTO `users` (`id`, `user_name`, `password`, `name`) VALUES (NULL, '$uname', '$passhashed', '$name')";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                $_POST['email'] = $uname;
                $_POST['password'] = $pass;

                $uname = validate($conn, $_POST['email']);
                $pass = validate($conn, $_POST['password']);

                if (empty($uname)) {
                    header("Location: login.php?error=Email is required");
                    exit();
                } else if (empty($pass)) {
                    header("Location: login.php?error=Password is required");
                    exit();
                } else {
                    $sql = "SELECT * FROM users WHERE user_name='$uname'";

                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) === 1) {
                        $row = mysqli_fetch_assoc($result);
                        if ($row['user_name'] === $uname && password_verify($pass, $row['password'])) {
                            $_SESSION['user_name'] = $row['user_name'];
                            $_SESSION['name'] = $row['name'];
                            $_SESSION['id'] = $row['id'];
                            if (!empty($_GET['continue'])) {
                                header("Location: " . str_replace("amp;", "", urldecode($_GET['continue'])));
                                exit();
                            }
                            header("Location: index.php");
                            exit();
                        } else {
//                            header("Location: login.php?error=Incorrect Email or password");
                            echo $row['user_name'] . " " . $uname . " " . $row['password'] . " " . $pass;
                            exit("1");
                        }
                    } else {
                        header("Location: login.php?error=Incorrect Email or password");
                        exit();
                    }
                }
            }
        } else {
            header("Location: register.php?error=An account with that email already exists");
            exit();
        }
    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hackathon Metaverse</title>
    <style>
        @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css");

        html, body {
            height: 100vh;
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background: #1c1c1c;
            overflow: hidden;
        }

        .startup {
            z-index: 9998;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: #1a6bed;
        }

        #onboard {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 9999;
            transform: translate(-50%, -50%);
            width: 600px;
            max-width: 80vw;
            height: auto;
            max-height: 80vh;
            background: white;
            border-radius: 20px;
            text-align: center;
            padding: 20px;
            display: block;
        }

        input[type=text], input[type=password], input[type=email] {
            display: block;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
            width: 200px;
            height: 30px;
            padding: 2px;
            border-radius: 10px;
            border: 1px black;
            outline: 1px black;
            margin-bottom: 15px;
        }

        .continuebutton {
            position: relative;
            margin-left: auto;
            margin-right: auto;
            width: 150px;
            height: 40px;
            background: #1a6bed;
            border-radius: 10px;
            border: none;
            outline: none;
            color: white;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            display: block;
            margin-top: 10px;
        }

        .selectbox {
            position: relative;
            margin-left: auto;
            margin-right: auto;
            width: 300px;
            height: 50px;
            border-radius: 10px;
            border: none;
            outline: none;
            text-align: center;
            vertical-align: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            cursor: pointer;
            display: block;
            margin-top: 10px;
            transition: 1s;
            color: #1a6bed;
        }

        .selectbox:hover {
            background: #C6C6C6;
        }

        #newmeeting {
            display: none;
        }

        #existing {
            display: none;
        }
    </style>
</head>
<body>
<div class="startup">
    <div id="onboard">
        <form action="register.php<?= !empty($_GET['continue']) ? "?continue=" . urlencode(htmlspecialchars($_GET['continue'])) : "" ?>" method="post">
            <h1>Register</h1>
            <?php if (isset($_GET['error'])) { ?>
                <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php } ?>
            <p>Email</p>
            <input type="email" name="email" placeholder="Email" required>

            <p>Password</p>
            <input type="password" name="password" placeholder="Password" required>

            <p>Name</p>
            <input type="text" name="name" placeholder="Name" required>

            <button class="continuebutton" type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php<?= empty($_GET['continue']) ? "" : "?continue=" . urlencode(htmlspecialchars($_GET['continue'])) ?>">Login</a></p>
    </div>
</div>
</body>
</html>
