<?php

function sendwebhook($message, $title = "Untitled") {
     $webhookurl = "https://discordapp.com/api/webhooks/1332439693614387271/vPb50vQw7igsrPAbfWADwFxcTfWSAVC8aYfTuvOZYphdMujc6RRuEWaDEIfnJt0S1Q1V";
        $timestamp = date("c", strtotime("now"));
        $json_data = json_encode([
            "tts" => false,
            "embeds" => [
                [
                    "title" => $title,
                    "type" => "rich",
                    
                    "description" => "$message",
                    "timestamp" => $timestamp,
                    "color" => hexdec( "007182" ),
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


        $ch = curl_init( $webhookurl );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );
        // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
        // echo $response;
        curl_close( $ch );
}

function logerror($error) {
    $errorlog = fopen("../storage/logs/error.log", "a");
    if($errorlog){
        fwrite($errorlog, $error  . "\n");
        fclose($errorlog);
        sendwebhook($error, "Site Error!");
        return true;
    } else {
        return false;
    }
}

function weblog($info) {
    $log = fopen("../storage/logs/website.log", "a");
    if($log){
        fwrite($log, $info . "\n");
        fclose($log);
        sendwebhook($info, "Site Log!");
        return true;
    } else {
        return false;
    }
}
