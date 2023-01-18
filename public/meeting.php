<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "internals/db_conn.php";

foreach ($_POST as $key => $value) {
    $_POST[$key] = htmlspecialchars(mysqli_escape_string($conn, $value), ENT_QUOTES);
}
if(!(isset($_POST['room']) && isset($_POST['username']) && isset($_POST['colour']) && isset($_POST['password']))) {
    header("Location: index.php?error=Invalid request");
}
//if(!preg_match("/^[0-9]{5}$/", $_POST['room'])) {
//    header("Location: index.php?error=Invalid room ID");
//}
if(!preg_match("/^#[a-fA-F0-9]{6}$/", $_POST['colour'])) {
    header("Location: index.php?error=Invalid colour");
}
$sql = "SELECT * FROM rooms WHERE id='" . $_POST['room'] . "'";
$result = mysqli_query($conn, $sql);
if($result) {
    if(mysqli_num_rows($result) === 0) {
        header("Location: index.php?error=Room not found");
    }
    else {
        $row = mysqli_fetch_assoc($result);
        if($row['password'] !== $_POST['password']) {
            header("Location: index.php?error=Incorrect password");
        }
    }
} else {
    exit("Internal error");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hackathon Metaverse</title>
    <style>
        @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css");
      html, body {
        height: 100vh;
        margin: 0;
        padding: 0;
        font-family: sans-serif;
          background: #1c1c1c;
      }
      .video-container {
          width: calc(100% - 30px);
          /*width: calc(100% - 430px);*/
          height: calc(100% - 100px);
          margin-top: 15px;
          margin-left: 15px;
          margin-right: 15px;
          display: inline-block;
          border-radius: 20px;
          overflow: hidden;
          z-index: 0;
          display: inline-block;
          background: #303030;
          transtion: all 0.75s;
      }
      .controlpanel{
          float: right;
          width: calc(20% - 15px);
          height: calc(100% - 30px);
          margin-top: 15px;
          margin-right: 15px;
          overflow: hidden;
          position: relative;
          display: inline-block;
      }
      .uservideo{
          width: 100%;
          max-height: calc((100vh - 250)/ 4);
          margin-bottom: 10px;
          border-radius: 20px;
          background: red;
      }
      .dialogbox{
          position: absolute;
          bottom: 0;
          height: 200px;
          width: 100%;
          border-radius: 15px;
          background: #303030;
      }
      .a-enter-vr-button {
          display: none;
      }
      .startup{
          z-index: 9998;
          position: fixed;
          width: 100vw;
          height: 100vh;
          background: #1a6bed;
      }
      #onboard{
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 9999;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 400px;
            background: white;
            border-radius: 20px;
            text-align: center;
            padding: 20px;
          display: block;
      }
      .continuebutton {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 50px;
            background: #1a6bed;
            border-radius: 10px;
            border: none;
            outline: none;
            color: white;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            display: block;
      }
      .useractionbox{
          height: 70px;
          width: 100%;
          bottom: 0;
      }
      .callactionbutton{
          display: inline-block;
          width: 45px;
          height: 45px;
          padding: 2.5px;
          color: white;
          border-radius: 75px;
          outline: none;
            border: none;
          margin-left: 5px;
            margin-right: 5px;
          position: relative;
      }
      .meetingInfo{
          postion: relative;
          float: left;
          display: inline-block;
          line-height: 50px;
          width: auto;
          height: 50px;
          font-size: 18px;
          color: white;
          margin-left: 15px;
      }
      .warningbox {
          postion: relative;
          float: right;
          display: inline-block;
          line-height: 50px;
          width: auto;
          height: 50px;
          font-size: 18px;
          color: white;
          margin-left: 15px;
      }
      .chatbox {
          postion: relative;
          width: 375px;
          display: none;
          height: 100%;
          padding: 5px;
          margin-top: 15px;
          margin-right: 15px;
          height: calc(100% - 110px);
          float: right;
          border-radius: 15px;
          background: #303030;
          z-index: 9999;
          color: white;
      }
      .messageinput{
          postion: absolute;
            width: calc(100% - 30px);
            height: 30px;
            border-radius: 10px;
            border: none;
            outline: none;
            padding-left: 5px;
            padding-right: 5px;
            padding-top: 2.5px;
            padding-bottom: 2.5px;
          background: #464646;
          color: white;
          text-align: left;
          display: block;
          outline: none;
          border: none;
          margin-top: 5px;
            bottom: 0px;
      }
      .textbox{
          postion: relative;
          margin-left: 10px;
          font-size: 18px;
          line-height: 23px;
          overflow: scroll;
          width: calc(100% - 20px);
          height: calc(100% - 100px);
      }
      .miccaminfo {
          position: fixed;
          z-index: 9999;
            width: 300px;
          padding: 10px;
          border-radius: 15px;
          bottom: 100px;
          font-size: 20px;
          text-align: center;
          vertical-align: center;
            color: white;
          height: 70px;
          backdrop-filter: blur(3px);
          background: rgba(70, 70, 70, 0.5);
          left: 50%;
          transform: translateX(-50%);
      }
        .chatnotif {
            position: fixed;
            z-index: 9999;
            width: auto;
            max-width: 300px;
            padding: 10px;
            border-radius: 15px;
            bottom: 100px;
            font-size: 20px;
            text-align: center;
            vertical-align: center;
            color: white;
            height: auto;
            backdrop-filter: blur(4px);
            background: rgba(68, 68, 68, 0.75);
            left: 50%;
            transform: translateX(-50%);
            display: none;
        }
    </style>
    <script src="https://aframe.io/releases/1.4.1/aframe.min.js"></script>
    <script src="js/socket.io.slim.js"></script>
    <script src="https://unpkg.com/aframe-environment-component@1.3.1/dist/aframe-environment-component.min.js"></script>
    <script src="https://unpkg.com/aframe-randomizer-components@^3.0.1/dist/aframe-randomizer-components.min.js"></script>
    <script src="https://wwback.vestal.tk/easyrtc/easyrtc.js"></script>
    <script src="dist/networked-aframe.js"></script>
    <script src="js/simple-navmesh-constraint.component.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/c-frame/aframe-particle-system-component@master/dist/aframe-particle-system-component.min.js"></script>
    <script src="js/gun.component.js"></script>
    <script src="js/forward.component.js"></script>
    <script src="js/remove-in-seconds.component.js"></script>
    <script>
        //function getUrlParams() {
        //    // var match;
        //    // var pl = /\+/g;  // Regex for replacing addition symbol with a space
        //    // var search = /([^&=]+)=?([^&]*)/g;
        //    // var decode = function (s) { return decodeURIComponent(s.replace(pl, ' ')); };
        //    // var query = window.location.search.substring(1);
        //    // var urlParams = {};
        //    //
        //    // match = search.exec(query);
        //    // while (match) {
        //    //     urlParams[decode(match[1])] = decode(match[2]);
        //    //     match = search.exec(query);
        //    // }
        //    // return urlParams;
        //    return JSON.parse('<?php //= json_encode($_POST); ?>//');
        //}
        //if(!(getUrlParams().colour && getUrlParams().username && verifyRoomURL(getUrlParams().room))){
        //    window.location.href = "./index.php";
        //}
        window.ntExample = {
            randomColor: () => {
                return '#' + new THREE.Color(Math.random(), Math.random(), Math.random()).getHexString();
            }
        };
        // Hypnos-phi/aframe-extras@37fd255 is https://github.com/n5ro/aframe-extras/pull/373
        // Also waiting https://github.com/n5ro/aframe-extras/pull/377 to be merged
        // Redefine the alias for now:
        THREE.Math = THREE.MathUtils;
        AFRAME.components["networked-scene"].schema.connectOnLoad.default = false;

        AFRAME.registerComponent('player-info', {
            // notice that color and name are both listed in the schema; NAF will only keep
            // properties declared in the schema in sync.
            schema: {
                name: { type: 'string',
                    default: 'user-' + Math.round(Math.random() * 10000) },
                color: {
                    type: 'color', // btw: color is just a string under the hood in A-Frame
                    default: window.ntExample.randomColor()
                }
            },

            init: function () {
                // this.schema.name.default = getUrlParams().username;
                this.obj = this.el.getObject3D("mesh");
                console.log(this.el);
                this.head = this.el.querySelector('.head');
                console.log("obj", this.obj);
                console.log("obj", this.head)
                this.nametag = this.el.querySelector('.nametag');
                console.log("nametag", this.nametag)

                this.ownedByLocalUser = this.el.id === 'player';
                if (this.ownedByLocalUser) {
                    this.data.name = "<?= $_POST['username']; ?>";
                }
            },

            // here as an example, not used in current demo. Could build a user list, expanding on this.
            listUsers: function () {
                console.log(
                    'userlist',
                    [...document.querySelectorAll('[player-info]')].map((el) => el.components['player-info'].data.name)
                );
            },

            newRandomColor: function () {
                this.el.setAttribute('player-info', 'color', window.ntExample.randomColor());
            },

            update: function () {
                console.log("update", "avatar")
                if(this.head){
                    this.head.addEventListener('model-loaded', (e) => {
                        console.log('this.head exists!');
                        console.log(this.el.querySelector('.head').getObject3D("mesh"));
                        let meshg = this.el.querySelector('.head').getObject3D("mesh");
                        var material = new THREE.MeshLambertMaterial({
                            color: this.data.color,
                        });

                        meshg.traverse(function (node) {
                            // change only the mesh nodes
                            if (node.type != "Mesh") return;
                            // apply material and clean up
                            let tmp = node.material
                            node.material = material
                            tmp.dispose()
                        })
                    });
                }
                if (this.nametag) this.nametag.setAttribute('value', this.data.name);
            }
        });
    </script>
    <script src="js/spawn-in-circle.component.js"></script>
</head>
<body>
<div id="miccaminfo" class="miccaminfo">Mic and camera access will be enabled once someone joins the room.</div>
<div class="chatnotif" id="chatnotif"></div>
<div class="video-container">
    <a-scene embedded
             networked-scene="onConnect: onConnect;
serverURL: https://wwback.vestal.tk;"
             dynamic-room
            gltf-model="dracoDecoderPath: https://www.gstatic.com/draco/v1/decoders/"
             renderer="colorManagement: true">
        <!-- Avatar -->
        <a-entity position="0 7 0" particle-system="preset: snow" particleCount="20000" maxAge="2"></a-entity>
        <a-assets>
            <a-entity id="mute-img" src="https://cdn-icons-png.flaticon.com/512/189/189653.png"></a-entity>
            <a-assets-item id="navmesh" src="assets/navmesh.glb"></a-assets-item>
            <a-entity id="avatar" src="/assets/avatar.glb" ></a-entity>
        <template id="avatar-template">
            <a-entity class="avatar" player-info networked-audio-source >
                <a-entity gltf-model="#avatar" class="head" scale="0.75 0.75 0.75" position="0 -1 0.25">
                </a-entity>
                <a-text
                        class="nametag"
                        value="NOT WORKING"
                        rotation="0 180 0"
                        position=".25 -.65 0"
                        side="double"
                        scale=".5 .5 .5"
                ></a-text>
                <a-plane
                        color="#FFF"
                        width="1"
                        height=".75"
                        position="0 .6 .2"
                        material="side: front"
                        networked-video-source
                ></a-plane>
<!--                <a-plane-->
<!--                        color="#FFF"-->
<!--                        width="1"-->
<!--                        height=".75"-->
<!--                        position="0 1.8 .2"-->
<!--                        material="side: front"-->
<!--                        networked-video-source="streamName: screen"-->
<!--                ></a-plane>-->
                <a-plane
                        color="#FFF"
                        width="1"
                        height=".75"
                        position="0 .6 .2"
                        material="side: back"
                        networked-video-source
                ></a-plane>
            </a-entity>
        </template>
            <!-- Bullet -->
            <template id="bullet-template">
                <a-entity>
                    <a-sphere class="bullet" scale="0.1 0.1 0.1" color="#fff"></a-sphere>
                </a-entity>
            </template>
        <a-asset-item id="camp" src="assets/camp.glb"></a-asset-item>
        <!-- /Templates -->
        </a-assets>

        <a-entity id="rig">
            <a-entity
                    id="player"
                    networked="template:#avatar-template;attachTemplateToLocal:false;"
                    camera
                    position="0 1.6 0"
                    spawn-in-circle="radius:3"
                    look-controls
                    simple-navmesh-constraint="navmesh:.navmesh;fall:0.5;height:1.65;"
                    gun="bulletTemplate:#bullet-template"
                    wasd-controls="acceleration:15;">
            </a-entity>
        </a-entity>
<!--        <a-entity>-->
<!--            <a-plane-->
<!--                    color="#FFF"-->
<!--                    width="8"-->
<!--                    height="4.5"-->
<!--                    position="14 3 15"-->
<!--                    material="side: back"-->
<!--                    networked-video-source="streamName: screen"-->
<!--        </a-entity>-->
<!--        <a-plane-->
<!--                color="#FFF"-->
<!--                width="8"-->
<!--                height="4.5"-->
<!--                position="12 3 15"-->
<!--                material="side: back"-->
<!--                networked-->
<!--                networked-video-source="streamName: screen"-->
<!--        ></a-plane>-->

<!--        <a-entity environment="preset:arches"></a-entity>-->
<!--        <a-entity environment="preset: forest; groundColor: #445; grid: cross"></a-entity>-->
        <a-gltf-model src="#navmesh" class="navmesh" position="0 0 0" scale="1 1 1" navmesh="false: true" visible="false"></a-gltf-model>
        <a-entity gltf-model="#camp" position="0 0 0" scale="1 1 1" shadow="cast: true"></a-entity>
        <a-sky color="#8cd7de"></a-sky>
        <a-entity light="type:ambient;intensity:0.75"></a-entity>
        <a-entity light="type: directional; color: #FFF; intensity: 0.6" position="-0.5 1 1"></a-entity>
        <a-entity light="type: directional;
                   castShadow: true;
                   intensity: 0.8;
                  position='-5 3 1.5'"></a-entity>
    </a-scene>
</div>
<div class="chatbox">
    <h2 style="margin-left: 10px; display: inline-block; margin-bottom: 5px;">Messages:</h2>
    <div class="textbox" id="textbox"></div>
    <input class="messageinput" type="text" id="message" placeholder="Enter message" style="margin-left: 10px; display: inline-block">
</div>
<div class="useractionbox">
    <div class="meetingInfo" id="meetinginfo">
    </div>
    <div style="width: 300px; height: 50px; justify-content: center; margin-left: auto; margin-right: auto; margin-top: 15px;">
<!--        <button class="callactionbutton" style="background: #db2e2e" id="micaction"><i class="bi bi-mic-mute"></i></button>-->
<!--        <button class="callactionbutton" style="background: #db2e2e" id="cameraaction"><i class="bi bi-camera-video-off"></i></button>-->
        <button class="callactionbutton" style="background: #868686" id="micaction"><i class="bi bi-exclamation-triangle"></i></button>
        <button class="callactionbutton" style="background: #868686" id="cameraaction"><i class="bi bi-exclamation-triangle"></i></button>
        <button class="callactionbutton" style="background: #303030" id="chatlauncher" onclick="toggleChat()"><i class="bi bi-chat-left-text"></i></button>
<!--        <button class="callactionbutton" style="background: #db2e2e" id="screenshareaction"><i class="bi bi-projector"></i></button>-->
        <button class="callactionbutton" style="background: #303030" id="sharebutton" onclick="shareMeetingInfo()"><i class="bi bi-share-fill"></i></button>
        <button class="callactionbutton" style="background: #db2e2e" id="leavebutton" onclick="leave(); window.location.href = 'index.php';"><i class="bi bi-box-arrow-left"></i></button>
    </div>
</div>
<script>
    let unread = false;
    let ascene = document.querySelector('a-scene');
    const chatbox = document.getElementsByClassName("chatbox");
    function markasunread() {
        unread = false;
        document.getElementById("chatlauncher").innerHTML = "<i class=\"bi bi-chat-left-text\"></i>";
        document.getElementById("chatnotif").style.display = "none";
        document.getElementById("chatnotif").innerHTML = "";
    }

    function toggleChat() {
        markasunread();
        if(chatbox[0].style.display === "none" || chatbox[0].style.display === "") {
            document.getElementsByClassName("chatbox")[0].style.display = "block";
            document.getElementsByClassName("video-container")[0].style.setProperty('width', 'calc(100% - 430px)');
        } else {
            document.getElementsByClassName("chatbox")[0].style.display = "none";
            document.getElementsByClassName("video-container")[0].style.setProperty('width', 'calc(100% - 30px)');
        }
        ascene.resize();
    }
    easyrtc.setOnError(function(errorObject){
        console.log("easyrtc error: " + errorObject.errorText);
    });
    function shareMeetingInfo() {
        document.getElementById("chatnotif").innerHTML = "Share this meeting link with your friends: <a href='http://" + window.location.host + "/index.php?room=<?= $_POST['room']; ?>'>http://" + window.location.host + "/index.php?room=<?= $_POST['room']; ?></a>";
        document.getElementById("chatnotif").style.display = "block";
        setTimeout(function() {
            document.getElementById("chatnotif").style.display = "none";
            document.getElementById("chatnotif").innerHTML = "";
        }, 5000);
    }
    function chatNotif(user, message){
        console.log("chatNotif");
    if(chatbox[0].style.display === "none" || chatbox[0].style.display === "") {
        console.log("Message: " + message);
        document.getElementById("chatnotif").innerHTML = user + ": " + message;
        document.getElementById("chatnotif").style.display = "block";
        unread = true;
        setTimeout(function() {
            document.getElementById("chatnotif").style.display = "none";
            document.getElementById("chatnotif").innerHTML = "";
            }, 5000);
        }
    else {
        console.log("No Notification");
        unread = false;

    }
    }
    // see issue https://github.com/networked-aframe/networked-aframe/issues/267
    NAF.schemas.getComponentsOriginal = NAF.schemas.getComponents;
    NAF.schemas.getComponents = (template) => {
        if (!NAF.schemas.hasTemplate('avatar-template')) {
            NAF.schemas.add({
                template: 'avatar-template',
                components: [
                    'position',
                    'rotation',
                    'player-info'
                ]
            });
        }
        const components = NAF.schemas.getComponentsOriginal(template);
        return components;
    };
        NAF.schemas.add({
            template: '#avatar-template',
            components: [
                'position',
                'rotation',
                {
                    selector: '.head',
                    component: 'material',
                    property: 'color'
                },
                {
                    selector: '.nametag',
                    component: 'value'
                },
                'player-info'
            ]
        });

    //CHAT

    function leave() {
        var scene = document.querySelector('a-scene');
        if (scene.hasAttribute('networked-scene')) {
            scene.removeAttribute('networked-scene');
        } else {
            // scene.setAttribute('networked-scene', 'debug:true;room:disconnect-' + roomIndex + ';adapter:wseasyrtc');
            // console.log('Joining room: ' + roomIndex);
            // roomIndex++;
        }
    }
    const textbox = document.querySelector('#textbox');
    const messageinput = document.querySelector("#message");
    const chatuser = "<?= $_POST['username']; ?>";
    NAF.connection.subscribeToDataChannel("chat", (senderId, dataType, data, targetId) => {console.log("msg", data, "from", senderId)
        console.log("msg", data, "from", senderId)
        textbox.innerHTML += data.user + ": " + data.txt + '<br>'
        textbox.scrollTop = textbox.scrollHeight;
        chatNotif(data.user, data.txt);
        if (unread == true) {
            document.getElementById("chatlauncher").innerHTML = "<i class='bi bi-chat-left-text-fill' style='color: #1a6bed'></i>";
        }
        if (unread == false) {
            markasunread();
        }
    });
    messageinput.addEventListener("keyup", function(event) {
        if (event.keyCode === 13 && messageinput.value != "") {
            event.preventDefault();
            textbox.innerHTML += "You: " + messageinput.value + '<br>'
            textbox.innerHTML += ""
            NAF.connection.broadcastDataGuaranteed("chat", {user: chatuser, txt: messageinput.value});
            messageinput.value = "";
            textbox.scrollTop = textbox.scrollHeight;
        }
    });
    document.getElementById("meetinginfo").innerHTML = "<?= $_POST['room']; ?>";
    document.getElementById('player').setAttribute('player-info', 'color', "<?= $_POST['colour']; ?>");
    // 'user-' + Math.round(Math.random() * 10000)
    document.getElementById('player').setAttribute('player-info', 'name', "<?= $_POST['username']; ?>");
</script>
<script src="js/dynamic-room.component.js.php?room=<?= $_POST['room'] ?>"></script>
</body>
</html>