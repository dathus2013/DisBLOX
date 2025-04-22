<?php
require_once '../pagehandler/route.php';

class handler {
    protected $router;

    public function __construct($router) {
        $this->router = $router;
    }

    public function addhandler($handler) {
        $handlerFile = "../routes/$handler.php"; // TODO - Add a debug option for all of this
        if (file_exists($handlerFile)) {
            require_once $handlerFile;
            $functionName = "setup" . $handler . "Routes";
            if (function_exists($functionName)) {
                call_user_func($functionName, $this->router);
            } else {
                throw new Exception("Function $functionName does not exist in $handlerFile.");
            }
        } else {
            throw new Exception("Handler file $handlerFile does not exist.");
        }
    }
    
    
}
