<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    $file_name = $_FILES['new_voice_file']['name'];
    $tmp_path  = $_FILES['new_voice_file']['tmp_name'];
    $file_size   = $_FILES['new_voice_file']['size'];

    if( $file_size > (1024 * 1024 * 3)){
        echo json_encode(['success' => false, 'error' => 'The file "'. $file_name .'" is too big. Its size cannot exceed 3 MB.']);
        die;
    }


    $upload_path =  dirname(__FILE__). '/upload/_temp_' . rand() . $file_name;


    $new_file_name = 'live_recording_'.date('Ymdhis').'_'.rand(1000,9999).'.mp3';

    $new_upload_path = dirname(__FILE__). '/upload/' . $new_file_name;

    $new_url = 'https://'. $_SERVER['HTTP_HOST'].'/voice-recording/upload/'. $new_file_name;

    if (move_uploaded_file($tmp_path, $upload_path)) {

        $cmd = "ffmpeg -i " . escapeshellarg($upload_path);
        $time = shell_exec($cmd . " 2>&1");

        $cmd = 'ffmpeg -i ' . $upload_path . ' -vn -ar 16000 -ac 1 -ab 64k -f mp3 ' . $new_upload_path;
        exec($cmd . ' 2>&1', $out, $ret);
        if ($ret) {
            echo json_encode(['success' => false, 'error' => 'There was a problem!']);
        } else {
            $cmd = "ffmpeg -i " . escapeshellarg($new_upload_path);
            $time = shell_exec($cmd . " 2>&1");
            $time = trim(trim(substr($time, strpos($time, 'Duration: ') + 10, 12)), ",");
            list($hms, $milli) = explode('.', $time);
            list($hours, $minutes, $seconds) = explode(':', $hms);
            $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
            if ($total_seconds > 0) {

                try{
                    @unlink($upload_path);
                }catch (Exception $exception){

                }
                echo json_encode(['success'=>true,'url'=> $new_url ,'file_name'=>$new_file_name]);
            }else{
                echo json_encode(['success'=>false,'error'=>'There was a problem! 1']);
            }
        }
        die;
    }
    else {
        echo json_encode(['success'=>false,'error'=>'There was a problem! 2']);
        die;
    }

    echo json_encode(['success'=>false,'error'=>'There was a problem! 3']);
    die;
