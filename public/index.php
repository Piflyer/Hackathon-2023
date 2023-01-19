<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!(isset($_SESSION['id']) && isset($_SESSION['user_name']))) {
    header("Location: login.php?error=Please login to continue&continue=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}
$error = false;
if (isset($_GET['room'])) {
    $room = $_GET['room'];
    if (!preg_match("/^[0-9]{5}$/", $room)) {
        $error = "Invalid room ID";
    }
    require "internals/db_conn.php";
    $sql = "SELECT * FROM rooms WHERE id='$room'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        if (mysqli_num_rows($result) === 0) {
            $error = "Invalid room ID";
        }
    } else {
        $error = "Internal error";
    }
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['pass'])) {
    $password = htmlspecialchars($_GET['pass']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hackathon Metaverse</title>
    <style>
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

        input[type=text] {
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
        <h1>Virtual Hangout</h1>
        <div id="screen1">
            <p>Hangout with your friends in a private playground!</p>
            <button onclick="existingmeeting()" class="selectbox">
                Join an existing meeting.
            </button>

            <button onclick="newmeeting()" class="selectbox">
                Create a new meeting
            </button>
        </div>
        <div id="existing">
            <form method="post" action="meeting.php">
                <p id="existing-code">Enter an existing meeting room join code:</p>
                <input required name="room" type="text" id="room" placeholder="Meeting Room">
                <p id="password-prompt">Enter password:</p>
                <input required name="password" type="text" id="password" placeholder="Password"
                       value="<?= $password ?>">
                <p>Enter your name:</p>
                <input required name="username" type="text" id="username-overlay" value="<?= $_SESSION['name']; ?>"
                       placeholder="Your Name"/>
                <p>Choose a color for your avatar.</p>
                <input required type="color" name="colour" id="colour">
                <button class="continuebutton" type="submit" class="continuebutton">Join Meeting</button>
            </form>
        </div>
        <div id="newmeeting">
            <form method="post" action="meeting.php">
                <p>Your meeting room code:</p>
                <input required name="room" type="text" id="room-new" placeholder="Meeting Room">
                <p>Enter your name:</p>
                <input required name="username" type="text" id="username-overlay-new" value="<?= $_SESSION['name']; ?>"
                       placeholder="Your Name"/>
                <p>Choose a color for your avatar.</p>
                <input required type="color" name="colour" id="colour-new">
                <input type="hidden" name="password" id="hidden-password">
                <button class="continuebutton" type="submit" class="continuebutton">Start Meeting</button>
            </form>
        </div>


        <script>
            function generateMeetingCode() {
                var code = "";
                var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                for (var j = 0; j < 3; j++) {
                    for (var i = 0; i < 4; i++) {
                        code += possible.charAt(Math.floor(Math.random() * possible.length));
                    }
                    if (j < 2) {
                        code += "-";
                    }
                }
                return code;
            }

            function existingmeeting() {
                document.getElementById("screen1").style.display = "none";
                document.getElementById("existing").style.display = "block";
            }

            function newmeeting() {
                fetch("internals/create_room.php")
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.error) {
                            alert("Error creating room: " + data.error);
                            return;
                        }
                        document.getElementById('room-new').value = data.id;
                        document.getElementById("room-new").readOnly = true;
                        document.getElementById("screen1").style.display = "none";
                        document.getElementById("newmeeting").style.display = "block";
                        document.getElementById("hidden-password").value = data.pass;
                    });
            }

            function getUrlParams() {
                var match;
                var pl = /\+/g;  // Regex for replacing addition symbol with a space
                var search = /([^&=]+)=?([^&]*)/g;
                var decode = function (s) {
                    return decodeURIComponent(s.replace(pl, ' '));
                };
                var query = window.location.search.substring(1);
                var urlParams = {};

                match = search.exec(query);
                while (match) {
                    urlParams[decode(match[1])] = decode(match[2]);
                    match = search.exec(query);
                }
                return urlParams;
            }
            <?php
            if (!$error) {
                if (isset($_GET['room'])) {
                    echo 'existingmeeting();';
                    if (!empty($_GET['room'])) {
                        echo '
                            document.getElementById("room").style.display = "none";
                            document.getElementById("existing-code").style.display = "none";';
                    }
                    if(!empty($password)) {
                        echo '
                            document.getElementById("password").style.display = "none";
                            document.getElementById("password-prompt").style.display = "none";';
                    }
                }
            } else {
                echo "alert('Error: $error');";
            }
            ?>
            document.getElementById("colour").value = '#' + (Math.random() * 0xFFFFFF << 0).toString(16).padStart(6, '0');
            document.getElementById("colour-new").value = '#' + (Math.random() * 0xFFFFFF << 0).toString(16).padStart(6, '0');

            document.getElementById("room").value = typeof getUrlParams().room == "undefined" ? "" : getUrlParams().room;
        </script>
    </div>
</div>
</body>
</html>