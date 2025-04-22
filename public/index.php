<?php

use watrlabs\authentication;
use watrbx\sitefunctions;
$useragent = $_SERVER['HTTP_USER_AGENT'] ?? "no user agent"; // I do wanna limit no user agents but the arbiter doesnt have one so it bugs out

if(strpos($useragent, "python")){ // i should improve this (maybe make like a disallowed list)
    http_response_code(403);
    die("Bro GE-");
}

if(strpos($useragent, "python-requests")){ // i should improve this (maybe make like a disallowed list)
    http_response_code(403);
    die("Bro GE-");
}

$disallowedips = array("zTMifMgsirCTRC8wt00=");

require '../pagehandler/route.php';
require '../pagehandler/handler.php';

$router = new router();
$handler = new handler($router);

ob_start();

try {
    try {
        
        //include("hi");
    //to add routes, make a file in the routes folder then copy the template and use that.
    //then use addhandler to add your routing here!

    if(isset($_POST["maintkey"])){
        if($_POST["maintkey"] == $maintkey){
            setcookie("access", "youhaveaccess", time() + 3900);
            header("Location: /");
        } else {
            $mainerror = "Incorrect key.";
        }
    }

    if(!isset($_COOKIE["access"])){
        if($maintenance == true){
            require("../pagehandler/maintenance.php");
            die();
        }
    }
    

    $handler->addhandler("WebHandler");
    $handler->addhandler("BootstrapHandler");
    $handler->addhandler("ClientHandler");
    $handler->addhandler("AdminHandler");
    $handler->addhandler("MatchHandler");
    $handler->addhandler("DataPersHandler");
    $handler->addhandler("ApiHandler");
    
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $router->dispatch($requestUri, $requestMethod);
    
    try {
        include(baseurl . "/conn.php");
        
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $ifclient;
        if(strpos($useragent, "Roblox")){
            $ifclient = 1;
        } else {
            $ifclient = 0;
        }
        
        if(isset($_COOKIE["watrbxcookie"]) || isset($_COOKIE["_ROBLOSECURITY"])){
            $auth = new authentication();
            $user = $auth->getuserinfo($_COOKIE["watrbxcookie"] ?? $_COOKIE["_ROBLOSECURITY"]); // this should work I think...
            if($user !== false){
                $user = $user["id"];
            } else {
                $user = 0;  
            }
        } else {
            $user = 0;
        }
        
        $sitefunc = new sitefunctions();
        $ip2 = $sitefunc->encrypt($ip);
        
        $analytics = $pdo->prepare("INSERT INTO logs (ip, page, method, isclient, user) VALUES (:ip, :page, :method, :isclient, :user)");
        $analytics->bindParam(':ip', $ip, PDO::PARAM_STR);
        $analytics->bindParam(':page', $requestUri, PDO::PARAM_STR);
        $analytics->bindParam(':method', $requestMethod, PDO::PARAM_STR);
        $analytics->bindParam(':isclient', $ifclient, PDO::PARAM_STR);
        $analytics->bindParam(':user', $user, PDO::PARAM_STR);
        $analytics->execute();
        
        $ipbanned = $pdo->prepare("SELECT * FROM ipban WHERE ip = ?");
        $ipbanned->execute([$ip2]);
        $isbanned = $ipbanned->fetch(PDO::FETCH_ASSOC);
        
        $ipbanned = $pdo->prepare("SELECT * FROM ipban WHERE ip = ?");
        $ipbanned->execute([$ip]);
        $isbanned = $ipbanned->fetch(PDO::FETCH_ASSOC);
        
        if($isbanned){
            ob_clean();
            http_response_code(403);
            die("You have been ip banned for <i>".$isbanned["reason"]."</i>.");
        }
        
        
        
    } catch(PDOException $e){
        // do nuthin. analytics and whatnot dont matter too much....
        die($e); // 4 debug...
    }
    

    } catch (ErrorException $e) {
        $router->reporterror($e);
        ob_clean();
        http_response_code(500);
        require("../pagehandler/500.php");
    //echo "\n<!-- \n" . $e ."\n-->";
    //echo "<pre>" . $e . "<pre>";

    // change this based off how you wanna handle errors
    // eventually a debug switch will configure all of this
    }
} catch(PDOException $e){
    $router->reporterror($e);
    ob_clean();
    http_response_code(500);
    require("../pagehandler/500.php");
}
ob_end_flush();
