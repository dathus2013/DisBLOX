<?php

// WatrLabs php framework v0.0.1 alpha
// DO NOT CHANGE THESE!!!
define("FRAMEWORK_VER", "v0.0.1 alpha");
define("FRAMEWORK_NAME", "WatrKit"); 
define("baseurl", ""); // MAKE SURE YOU FILL THIS OUT! CLASSES WILL NOT WORK PROPERLY WITHOUT IT
define('domain', $_SERVER['SERVER_NAME']);

spl_autoload_register(function ($class_name) {
    $directory = baseurl . '/classes/';
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    $file = $directory . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
        //echo "<br>" . $file . " aquired";
    }
    else {
        //echo "<br>" . $file . " could not be found";
    }
});

// helps with detecting errors since php doesn't really have catch all
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    //ob_clean();
    //include("../pagehandler/500.php");
    //include_once("../pagehandler/logging.php");
    //die();
    
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// project definition config
define("project_name", "watrbx");
define("project_description", "2016 and 2013 ROBLOX private server.");
define("project_author", "WatrLabs + Czech Development");
define("project_version", "v0.0.1");

// configureable stuff yadadatrue
$maintenance = false;
$maintkey = "this should really be dynamic...";

//$guests = false; // this doesnt work ill fix it later.. (oops this in admin panel now!)

// watrbx specific

define("arbiterKey", "");
define("arbiterKeySite", "");
define("arbiterIp", "");
define("arbiterPort", "");
define("rccapikey", "");