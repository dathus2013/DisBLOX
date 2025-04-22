<?php

namespace swordrunning;

class gameserver {
    
    public static function guidv4($data = null) {
            $data = $data ?? random_bytes(16);
            assert(strlen($data) == 16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
            // this is for generating job ids.
    }
    
    public static function getRunning() {
        global $pdo;
        //global $arbiterKey;
        
        include(baseurl . '/conn.php');
        $getgames = $pdo->query("SELECT * FROM games");
        $allgames = $getgames->fetchAll();
        
        $games = [];
        
        foreach ($allgames as $server) {
            $games[] = array(
                "place" => htmlspecialchars($server['place']),
                "port" => htmlspecialchars($server['port']),
            );
        }
        
        return json_encode($games);
    }
    
    public static function getTopQueue() {
        global $pdo;
        //global $arbiterKey;
        
        include(baseurl . '/conn.php');
        $getgames = $pdo->query("SELECT * FROM jobs WHERE status = 0"); // status 0 is unstarted or in queue
        $allgames = $getgames->fetchAll();
        
        return json_encode($allgames);
    }
    
    public static function deleteTopQueue() {
        global $pdo;
        //global $arbiterKey;
        
        include(baseurl . '/conn.php');
        $getgames = $pdo->query("SELECT * FROM jobs");
        $allgames = $getgames->fetchAll();
        
        foreach ($allgames as $server) {
            $id = $server['id'];
            $delete = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
            $delete->execute([$id]);
            break;
        }
    }
    
    public static function openServer($place,$port,$jobId) {
        global $pdo;
        include(baseurl . '/conn.php');
        $newThingy = $pdo->prepare("INSERT INTO games (place, port, jobId) VALUES (?, ?, ?)");
        $newThingy->execute([$place,$port, $jobId]);
    }
    public static function closeServer($place) {
        global $pdo;
        global $arbiterKey;
        gameserver::sendUDP("closegame/".$place."/".$arbiterKey);
    }
    public static function checkStatus() {
        global $arbiterKey;
        if(!gameserver::sendUDP("ping/".$arbiterKey)){
            return false;
        } else {
            return true;
        }
    }
    public static function addToQueue($place) {
        global $pdo;
        include(baseurl . '/conn.php');
        
        $newThingy = $pdo->prepare("SELECT * FROM jobs WHERE placeid = ? AND status = 0");
        $newThingy->execute([$place]);
        $allgames = $newThingy->fetch();
        
        if(!$allgames){ // checks if the place is already in queue and if so, skip.
            $jobid = guidv4();
            $type = 1;
            $newThingy = $pdo->prepare("INSERT INTO jobs (jobid, jobtype, placeid) VALUES (?, ?, ?)");
            $newThingy->execute([$jobid, $type, $place]); // ye this should work
        }
    }
    public static function sendUDP($message) {
        global $arbiterIp;
        global $arbiterPort;
        $socket = stream_socket_client("udp://".arbiterIp.":".arbiterPort."", $errno, $errstr);
        if (!$socket) {
            return $socket;
        }
        
        stream_socket_sendto($socket, $message);
    
        fclose($socket);
    }
}
?>