<?php

namespace watrbx;
use PDO;
set_error_handler(function ($severity, $message, $file, $line) {
    //$hello = new logging();
    //->errorwebhook('Including file: $file');
    throw new \ErrorException($message, $severity, $severity, $file, $line);
});

class sitefunctions {
    
    public $key = 'kzjdL3lbXc4ZpHP571VLUrbxWHCIeGEP';
    public $method = 'blowfish'; 
    public $iv = '53425628';
    
    public function encrypt($text){
        //$method = $this->method;
        $encrypted = openssl_encrypt($text, $this->method, $this->key, 0, $this->iv);
        return $encrypted;
    }
    
    public function decrypt($text){
        $decrypted = openssl_decrypt($text, $this->method, $this->key, 0, $this->iv);
        return $decrypted;
    }

    public function getuniverseinfo($gameid){

        include baseurl . "/conn.php";
        $siteinfo = $pdo->prepare("SELECT * FROM universes WHERE id = ?");
        $siteinfo->execute([$gameid]);
        return $siteinfo->fetch(PDO::FETCH_ASSOC);

    }
    public function getplaceinfo($placeid) {
        include baseurl . "/conn.php";
        $getplace = $pdo->prepare("SELECT * FROM `assets` WHERE `id` = ? AND `prodid` = ?");
        $getplace->execute([$placeid, "9"]);
        return $getplace->fetch(PDO::FETCH_ASSOC); // this automatically returns false if it cant find it. ez.
    }
    
    // https://stackoverflow.com/questions/14649645/resize-image-in-php - ty
    public function resize_image($file, $w, $h, $crop=FALSE) {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width-($width*abs($r-$w/$h)));
            } else {
                $height = ceil($height-($height*abs($r-$w/$h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w/$h > $r) {
                $newwidth = $h*$r;
                $newheight = $h;
            } else {
                $newheight = $w/$r;
                $newwidth = $w;
            }
        }
        $src = imagecreatefrompng($file);
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return imagepng($dst);
    }
    
    public function createjobid($data = null) {
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        // this is for generating job ids.
    }
    
    public function getusers() {
        include baseurl . "/conn.php";
        $stmt = $pdo->prepare("SELECT COUNT(*) as usrcount FROM `users`");
        $stmt->execute();
        return $stmt->fetchColumn(); 
    }
    
    public function getplaying() {
        include baseurl . "/conn.php";
        $stmt = $pdo->prepare("SELECT COUNT(*) as playcount FROM `activeplayers`");
        $stmt->execute();
        return $stmt->fetchColumn(); 
    }

    public function getgamecount() {
        include baseurl . "/conn.php";
        $stmt = $pdo->prepare("SELECT COUNT(*) as universecount FROM `universes`");
        $stmt->execute();
        return $stmt->fetchColumn(); 
    }

    public function getnewusers() {
        $day = 86400;
        $adayago = time() - $day;
        include baseurl . "/conn.php";
        $stmt = $pdo->prepare("SELECT COUNT(*) as newusercount FROM `users` WHERE regtime > ?");
        $stmt->execute([$adayago]);
        return $stmt->fetchColumn(); 
    }
    
    public function getsiteconf() {
        include baseurl . "/conn.php";
        $siteinfo = $pdo->prepare("SELECT * FROM config");
        $siteinfo->execute();
        return $siteinfo->fetch(PDO::FETCH_ASSOC);
        // so this should be like $siteconfig["sitebanner"] n shit
    }
    
    public function thisisafunction() {
        echo "I'm in the thick of it.";
    }
    
    public function set_message($message, $type = "error") {
        $message = array(
            "type" => $type,
            "message" => $message
        );
        
        $encoded = json_encode($message);
        $encrypted = $this->encrypt($encoded);
        setcookie("msg", $encrypted, time() + 500, '');
        return $encrypted;
    }
    
    public function generateClientTicket($id, $name, $charapp, $jobid, $privatekey) {
        $ticket = $id . "\n" . $jobid . "\n" . date('n\/j\/Y\ g\:i\:s\ A');
        
        openssl_sign($ticket, $sig, $privatekey, OPENSSL_ALGO_SHA1);
        $sig = base64_encode($sig);
        
        $ticket2 = $id . "\n" . $name . "\n" . $charapp . "\n". $jobid . "\n" . date('n\/j\/Y\ g\:i\:s\ A');
        openssl_sign($ticket2, $sig2, $privatekey, OPENSSL_ALGO_SHA1);
        $sig2 = base64_encode($sig2);
        
        $final = date('n\/j\/Y\ g\:i\:s\ A') . ";" . $sig2 . ";" . $sig;
        return $final;
        // robloxes format is.. really weird.
    }
    
    public function get_message(){
        if(isset($_COOKIE["msg"])){
            
            $msg = $_COOKIE["msg"];
            
            $decrypted = $this->decrypt($msg);
            $decoded = json_decode($decrypted, true);
            
            if($decoded["type"] == "error"){
                echo "<p id=\"errormsg\">". $decoded["message"] ."</p>";
                setcookie("msg", $msg, time() - 500, '');
            } elseif($decoded["type"] == "notice"){
                echo "<p id=\"errormsg\" style=\"background-color: #378db8;\">". $decoded["message"] ."</p>";
                setcookie("msg", $msg, time() - 500, '');
            } else {
                //throw new Exception('Invalid message type!');
            }
            
        } else {
            return false;
        }
    }
    
    public function genstring($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
}