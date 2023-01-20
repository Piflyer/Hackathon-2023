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
$password = "";
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
                fetch("internals/create_room.json.php")
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
                    if (!empty($password)) {
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

            function checkDeviceSupport(callback) {
                var hasMicrophone = false;
                var hasSpeakers = false;
                var hasWebcam = false;

                var isMicrophoneAlreadyCaptured = false;
                var isWebcamAlreadyCaptured = false;

                if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                    // Firefox 38+ seems having support of enumerateDevicesx
                    navigator.enumerateDevices = function (callback) {
                        navigator.mediaDevices.enumerateDevices().then(callback);
                    };
                }

                var MediaDevices = [];
                var isHTTPs = location.protocol === 'https:';
                var canEnumerate = false;


                if (typeof MediaStreamTrack !== 'undefined' && 'getSources' in MediaStreamTrack) {
                    canEnumerate = true;
                } else if (navigator.mediaDevices && !!navigator.mediaDevices.enumerateDevices) {
                    canEnumerate = true;
                }

                if (!canEnumerate) {
                    callback({
                        "hasMicrophone": false,
                        "hasSpeakers": false,
                        "hasWebcam": false,
                        "isHTTPs": isHTTPs,
                        "canEnumerateDevices": false
                    });
                    return;
                }

                if (!navigator.enumerateDevices && window.MediaStreamTrack && window.MediaStreamTrack.getSources) {
                    navigator.enumerateDevices = window.MediaStreamTrack.getSources.bind(window.MediaStreamTrack);
                }

                if (!navigator.enumerateDevices && navigator.enumerateDevices) {
                    navigator.enumerateDevices = navigator.enumerateDevices.bind(navigator);
                }

                if (!navigator.enumerateDevices) {
                    if (callback) {
                        callback();
                    }
                    return;
                }

                MediaDevices = [];
                navigator.enumerateDevices(function (devices) {
                    devices.forEach(function (_device) {
                        var device = {};
                        for (var d in _device) {
                            device[d] = _device[d];
                        }

                        if (device.kind === 'audio') {
                            device.kind = 'audioinput';
                        }

                        if (device.kind === 'video') {
                            device.kind = 'videoinput';
                        }

                        var skip;
                        MediaDevices.forEach(function (d) {
                            if (d.id === device.id && d.kind === device.kind) {
                                skip = true;
                            }
                        });

                        if (skip) {
                            return;
                        }

                        if (!device.deviceId) {
                            device.deviceId = device.id;
                        }

                        if (!device.id) {
                            device.id = device.deviceId;
                        }

                        if (!device.label) {
                            device.label = 'Please invoke getUserMedia once.';
                            if (!isHTTPs) {
                                device.label = 'HTTPs is required to get label of this ' + device.kind + ' device.';
                            }
                        } else {
                            if (device.kind === 'videoinput' && !isWebcamAlreadyCaptured) {
                                isWebcamAlreadyCaptured = true;
                            }

                            if (device.kind === 'audioinput' && !isMicrophoneAlreadyCaptured) {
                                isMicrophoneAlreadyCaptured = true;
                            }
                        }

                        if (device.kind === 'audioinput') {
                            hasMicrophone = true;
                        }

                        if (device.kind === 'audiooutput') {
                            hasSpeakers = true;
                        }

                        if (device.kind === 'videoinput') {
                            hasWebcam = true;
                        }

                        // there is no 'videoouput' in the spec.

                        MediaDevices.push(device);
                    });

                    if (callback) {
                        callback(
                            {
                                "hasWebcam": hasWebcam,
                                "hasMicrophone": hasMicrophone,
                                "hasSpeakers": hasSpeakers,
                                "isHTTPs": isHTTPs,
                                "canEnumerateDevices": canEnumerate
                            }
                        );
                    }
                });
            }

            checkDeviceSupport((data) => {
                if (!data.hasWebcam) {
                    let warnEle = document.createElement("p");
                    warnEle.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> No webcam detected.';
                    document.getElementById("onboard").appendChild(warnEle);
                }
                if (!data.hasMicrophone) {
                    let warnEle = document.createElement("p");
                    warnEle.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> No microphone detected.';
                    document.getElementById("onboard").appendChild(warnEle);
                }
            });
        </script>
    </div>
</div>
</body>
</html>