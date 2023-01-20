<?php
header("Content-Type: application/javascript; charset=UTF-8");
$_GET['name'] = htmlspecialchars($_GET['name']);
?>
/**
 * Setup the Networked-Aframe scene component based on query parameters
 */

AFRAME.registerComponent('dynamic-room', {
    init: function () {
        console.log("Room: " + "<?= $_GET['room']; ?>");

        window.hasMic = false;
        window.hasCam = false;
        window.dynamicRoomComponent = this;
        checkDeviceSupport((data) => {
            console.log("Capabilities: ");
            console.log(data);
            window.hasMic = data.hasMicrophone;
            console.log(data.hasMicrophone);
            window.hasCam = data.hasWebcam;
            console.log(data.hasWebcam);

            console.log("Has cam: " + hasCam);
            console.log("Has mic: " + hasMic);

            easyrtc.joinRoom("<?= $_GET['room']; ?>");
            const networkedComp = {
                room: "<?= $_GET['room']; ?>",
                debug: true,
                audio: hasMic,
                onConnect: onConnecth,
                adapter: "easyrtc",
                video: hasCam,
                serverURL: "https://winterwonderland.azurewebsites.net"
            };
            console.info('Init networked-aframe with settings:', networkedComp);
            console.log(window.dynamicRoomComponent.el);
            console.log("setting it", window.dynamicRoomComponent.el.setAttribute('networked-scene', networkedComp));
            document.body.addEventListener('clientConnected', function (evt) {
                onConnecth();
            });
            window.dynamicRoomComponent.el.emit("connect", null, false);
        });

    },
});
let cameraEnabled = true;
let micEnabled = true;
let screenEnabled = false;

const cameraButton = document.getElementById('cameraaction');
const micButton = document.getElementById('micaction');
const screenButton = document.getElementById("screenshareaction");
let first = true;
function onConnecth() {
    if (!first) {
        console.log("something went horribly wrong");
    } else {
        document.getElementById('player').setAttribute('player-info', 'name', "<?= $_GET['name']; ?>");
        NAF.connection.adapter.enableCamera(!cameraEnabled);
        NAF.connection.adapter.enableMicrophone(!micEnabled);
        console.log("First Load");
        cameraEnabled = !cameraEnabled;
        micEnabled = !micEnabled;
        cameraButton.style.background = cameraEnabled ? '#303030' : '#db2e2e';
        cameraButton.innerHTML = cameraEnabled ? "<i class=\"bi bi-camera-video-fill\"></i>" : "<i class=\"bi bi-camera-video-off\"></i>";
        micButton.style.background = micEnabled ? '#303030' : '#db2e2e';
        micButton.innerHTML = micEnabled ? "<i class=\"bi bi-mic-fill\"></i>" : "<i class=\"bi bi-mic-mute-fill\"></i>";
        document.getElementById("miccaminfo").style.display = "none";
        first = false;
        console.log(first);
        finishLoad();
    }
}

function disableVideo() {

    const stream = NAF.connection.adapter.getMediaStream();
    stream.getTracks().forEach(track => {
        if (track.kind === 'video') {
            track.stop();
        }
    })

}
function finishLoad() {
    console.log('onConnect');
    console.log('onConnect', new Date());
    cameraButton.onclick = function () {
        NAF.connection.adapter.enableCamera(!cameraEnabled);
        cameraEnabled = !cameraEnabled;
        cameraButton.style.background = cameraEnabled ? '#303030' : '#db2e2e';
        cameraButton.innerHTML = cameraEnabled ? "<i class=\"bi bi-camera-video-fill\"></i>" : "<i class=\"bi bi-camera-video-off\"></i>";
        console.log("video", cameraEnabled);
    };
    micButton.onclick = function () {
        NAF.connection.adapter.enableMicrophone(!micEnabled);
        micEnabled = !micEnabled;
        micButton.style.background = micEnabled ? '#303030' : '#db2e2e';
        micButton.innerHTML = micEnabled ? "<i class=\"bi bi-mic-fill\"></i>" : "<i class=\"bi bi-mic-mute\"></i>";
        console.log("mic", micEnabled);
    };
}
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
