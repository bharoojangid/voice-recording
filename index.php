<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Recording Demo in HTML - Bharoo Jangid</title>
    <link rel='dns-prefetch' href='//fonts.googleapis.com' />
    <link rel='dns-prefetch' href='//s.w.org' />
    <link rel="canonical" href="https://www.bharoojangid.in/" />
    <link rel='shortlink' href='https://www.bharoojangid.in/' />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="RecordRTC.js"></script>
<body>


<div class="site-contact">
    <div style="width: 100%;max-width: 500px;margin: auto;">

        <div class="panel-group">
            <div class="panel panel-warning">
                <div class="panel-heading">Live Recording Demo in HTML</div>
                <div class="panel-body text-center">

                    <b class="text-red ">Use microphone for recording.</b<br><br>

                    <div>
                        <audio controls autoplay playsinline></audio>
                    </div>
                    <hr>

                    <div id="record_btn_control" style="display: none">
                        <button id="btn-start-recording" class="btn btn-success">Start Recording</button>
                        <button id="btn-stop-recording" class="btn btn-danger" disabled>Stop Recording</button>
                        <button id="btn-save-recording" class="btn btn-warning" disabled>Download</button>
                    </div>

                    <br>
                   <marquee><div>Powered By : <a href="https://bharoojangid.in" target="_blank">Bharoo Jangid</a> </div></marquee>
                </div>
            </div>

        </div>
    </div>
</div>


<script>

    var load_rtc_interval = setInterval(function () {
        if (typeof RecordRTC == 'function') {
            clearInterval(load_rtc_interval);
            document.getElementById('record_btn_control').removeAttribute('style');

        }
    }, 1000);
    var audio = document.querySelector('audio');
    function captureMicrophone(callback) {
        if (microphone) {
            callback(microphone);
            return;
        }
        if (typeof navigator.mediaDevices === 'undefined' || !navigator.mediaDevices.getUserMedia) {
            alert('This browser does not supports WebRTC getUserMedia API.');
            if (!!navigator.getUserMedia) {
                alert('This browser seems supporting deprecated getUserMedia API.');
            }
        }
        navigator.mediaDevices.getUserMedia({
            audio: isEdge ? true : {
                echoCancellation: false
            }
        }).then(function (mic) {
            callback(mic);
        }).catch(function (error) {
            alert('Unable to capture your microphone. Please check console logs.');
            console.error(error);
        });
    }

    function replaceAudio(src) {
        var newAudio = document.createElement('audio');
        newAudio.controls = true;
        newAudio.autoplay = true;

        if (src) {
            newAudio.src = src;
        }

        var parentNode = audio.parentNode;
        parentNode.innerHTML = '';
        parentNode.appendChild(newAudio);

        audio = newAudio;
    }

    function stopRecordingCallback() {
        replaceAudio(URL.createObjectURL(recorder.getBlob()));

        btnStartRecording.disabled = false;

        setTimeout(function () {
            if (!audio.paused) return;

            setTimeout(function () {
                if (!audio.paused) return;
                audio.play();
            }, 1000);

            audio.play();
        }, 300);

        audio.play();
        btnSaveRecording.disabled = false;


    }

    var isEdge = navigator.userAgent.indexOf('Edge') !== -1 && (!!navigator.msSaveOrOpenBlob || !!navigator.msSaveBlob);
    var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

    var recorder; // globally accessible
    var microphone;

    var btnStartRecording = document.getElementById('btn-start-recording');
    var btnStopRecording = document.getElementById('btn-stop-recording');
    var btnSaveRecording = document.getElementById('btn-save-recording');


    btnStartRecording.onclick = function () {
        this.disabled = true;
        this.style.border = '';
        this.style.fontSize = '';

        if (!microphone) {
            captureMicrophone(function (mic) {
                microphone = mic;

                if (isSafari) {
                    replaceAudio();

                    audio.muted = true;
                    audio.srcObject = microphone;

                    btnStartRecording.disabled = false;
                    btnStartRecording.style.border = '1px solid red';
                    btnStartRecording.style.fontSize = '150%';

                    alert('Please click startRecording button again. First time we tried to access your microphone. Now we will record it.');
                    return;
                }

                click(btnStartRecording);
            });
            return;
        }

        replaceAudio();

        audio.muted = true;
        audio.srcObject = microphone;

        var options = {
            type: 'audio',
            numberOfAudioChannels: isEdge ? 1 : 2,
            checkForInactiveTracks: true,
            bufferSize: 16384
        };

        if (isSafari || isEdge) {
            options.recorderType = StereoAudioRecorder;
        }

        if (navigator.platform && navigator.platform.toString().toLowerCase().indexOf('win') === -1) {
            options.sampleRate = 48000; // or 44100 or remove this line for default
        }

        if (isSafari) {
            options.sampleRate = 44100;
            options.bufferSize = 4096;
            options.numberOfAudioChannels = 1;
        }

        if (recorder) {
            recorder.destroy();
            recorder = null;
        }
        options.numberOfAudioChannels = 1;
        options.recorderType = StereoAudioRecorder;
        recorder = RecordRTC(microphone, options);

        recorder.startRecording();

        btnStopRecording.disabled = false;
    };

    btnStopRecording.onclick = function () {
        this.disabled = true;

        microphone.stop();
        microphone = null;

        recorder.stopRecording(stopRecordingCallback);
    };


    btnSaveRecording.onclick = function () {
        // this.disabled = true;
        if (!recorder || !recorder.getBlob()) return;

        if (isSafari) {
            recorder.getDataURL(function (dataURL) {
                SaveToDisk(dataURL, getFileName('mp3'));
            });
            return;
        }

        var blob = recorder.getBlob();
        var file = new File([blob], getFileName('mp3'), {
            type: 'audio/mp3'
        });
        // invokeSaveAsDialog(file);

        var formData = new FormData();
        formData.append('new_voice_file', file);
        formData.append('new_voice_file_name',getFileName('mp3'));

        $.ajax({
            url: 'save.php',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function(response) {
                console.log(response);
                console.log(response.url);

                response = JSON.parse(response);
                console.log(response);


                if(response.success){
                    alert('File Successfully Generated.');
                     var a = document.createElement("a");
                     a.href = response.url;
                     a.setAttribute("download", response.file_name);
                     a.click();
                     a.remove();

                }else{
                    alert(response.error);
                }
            },
            error: function(response) {
                alert('File not downloading, please try again.');
            }
        });

    };

    function click(el) {
        el.disabled = false; // make sure that element is not disabled
        var evt = document.createEvent('Event');
        evt.initEvent('click', true, true);
        el.dispatchEvent(evt);
    }

    function getRandomString() {
        if (window.crypto && window.crypto.getRandomValues && navigator.userAgent.indexOf('Safari') === -1) {
            var a = window.crypto.getRandomValues(new Uint32Array(3)),
                token = '';
            for (var i = 0, l = a.length; i < l; i++) {
                token += a[i].toString(36);
            }
            return token;
        } else {
            return (Math.random() * new Date().getTime()).toString(36).replace(/\./g, '');
        }
    }

    function getFileName(fileExtension) {
        var d = new Date();
        var year = d.getFullYear();
        var month = d.getMonth();
        var date = d.getDate();
        return 'bj-audio-' + year + month + date + '-' + getRandomString() + '.' + fileExtension;
    }

    function SaveToDisk(fileURL, fileName) {
        console.log(fileURL);
        // for non-IE
        if (!window.ActiveXObject) {
            var save = document.createElement('a');
            save.href = fileURL;
            save.download = fileName || 'unknown';
            save.style = 'display:none;opacity:0;color:transparent;';
            (document.body || document.documentElement).appendChild(save);

            if (typeof save.click === 'function') {
                save.click();
            } else {
                save.target = '_blank';
                var event = document.createEvent('Event');
                event.initEvent('click', true, true);
                save.dispatchEvent(event);
            }

            (window.URL || window.webkitURL).revokeObjectURL(save.href);
        }

        // for IE
        else if (!!window.ActiveXObject && document.execCommand) {
            var _window = window.open(fileURL, '_blank');
            _window.document.close();
            _window.document.execCommand('SaveAs', true, fileName || fileURL)
            _window.close();
        }
    }
</script>


<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-65727978-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-65727978-1');
</script>


</body>
</html>
