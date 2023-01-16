/**
 * Setup the Networked-Aframe scene component based on query parameters
 */

AFRAME.registerComponent('dynamic-room', {
    init: function () {
        var el = this.el;
        var params = this.getUrlParams();

        if (!params.room) {
            window.alert('Please add a room name in the URL, eg. ?room=myroom');
        }
        if (!verifyRoomURL(params.room)) {
            window.location.href = '/index.html';
        }
        console.log("Room: " + params.room);

        // "
        // debug: true;
        // adapter: easyrtc;
        // audio: true;
        // onConnect: onConnect;
        // video: true;
        // "
        var networkedComp = {
            room: params.room,
            debug: true,
            audio: true,
            onConnect: onConnecth,
            adapter: "easyrtc",
            video: true
        };
        console.info('Init networked-aframe with settings:', networkedComp);
        console.log(this.el);
        console.log("setting it", this.el.setAttribute('networked-scene', networkedComp));
        document.body.addEventListener('clientConnected', function (evt) {
            onConnecth();
        });
        this.el.emit("connect", null, false);
    },

    getUrlParams: function () {
        var match;
        var pl = /\+/g;  // Regex for replacing addition symbol with a space
        var search = /([^&=]+)=?([^&]*)/g;
        var decode = function (s) { return decodeURIComponent(s.replace(pl, ' ')); };
        var query = window.location.search.substring(1);
        var urlParams = {};

        match = search.exec(query);
        while (match) {
            urlParams[decode(match[1])] = decode(match[2]);
            match = search.exec(query);
        }
        return urlParams;
    }
});
let cameraEnabled = true;
let micEnabled = true;
let screenEnabled = false;

const cameraButton = document.getElementById('cameraaction');
const micButton = document.getElementById('micaction');
const screenButton = document.getElementById("screenshareaction");
let first = true;
function onConnecth() {
    if(!first) {
    console.log("something went horribly wrong");
    }
    else {
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

function disableVideo(){

    const stream = NAF.connection.adapter.getMediaStream();
    stream.getTracks().forEach(track => {
        if (track.kind === 'video') {
            track.stop();
        }
    })

}
function finishLoad(){
    console.log('onConnect');
    document.getElementById('player').setAttribute('player-info', 'name', getUrlParams().username);
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