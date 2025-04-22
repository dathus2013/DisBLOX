<?php

// route handler....
require("../config.php");
use watergames\pagebuilder;
$pagebuilder = new pagebuilder;
//$pagebuilder->set_page_name("Home");
//$pagebuilder->buildheader();


class router {
    public $routes = [];
    
    public function reporterror($error){
        http_response_code(500);
        require("logging.php");
        logerror($error);
        return $error;
    }
    
    public function weblog($log) {
        require("logging.php");
        weblog($error);
        return true;
    }

    public function get($uri, $callback) {
        try {
            $this->routes['GET'][$uri] = $callback;
        } catch (ErrorException $e) {
            require("500.php");
            $this->reporterror($e);
        }
    }

    public function post($uri, $callback) {
        try {
            $this->routes['POST'][$uri] = $callback;
        } catch (ErrorException $e) {
            require("500.php");
            $this->reporterror($e);
        }
    }
    
    public function head($uri, $callback) {
        try {
            $this->routes['HEAD'][$uri] = $callback;
        } catch (ErrorException $e) {
            require("500.php");
            $this->reporterror($e);
        }
    }

    public function put($uri, $callback) {
        try {
            $this->routes['PUT'][$uri] = $callback;
        } catch (ErrorException $e) {
            require("500.php");
            $this->reporterror($e);
        }
    }
    
    public function del($uri, $callback) {
        try {
            $this->routes['DELETE'][$uri] = $callback;
        } catch (ErrorException $e) {
            require("500.php");
            $this->reporterror($e);
        }
    }

    public function group($prefix, $callback, $middleware = null) {
        $previousroutes = $this->routes;
        $this->routes = [];

        call_user_func($callback, $this);

        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $uri => $routecallback) {
                $composedcallback = $routecallback;
                if ($middleware) {
                    $composedcallback = function(...$params) use ($middleware, $routecallback) {
                        $middlewareResult = call_user_func_array($middleware, $params);
                        if ($middlewareResult !== false) {
                            call_user_func_array($routecallback, $params);
                        }
                    };
                }
                $previousroutes[$method][$prefix . $uri] = $composedcallback;
            }
        }

        $this->routes = $previousroutes;
    }

    public function dispatch($uri, $method) {
        foreach ($this->routes[$method] as $routeuri => $callback) {
            $pattern = preg_replace('#\{[^\}]+\}#', '([^/]+)', $routeuri);
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                array_shift($matches);
                return call_user_func_array($callback, $matches);
            }
        }
        try {
            require("404.php");
        } catch (ErrorException $e) {
            try {
                require("500.php");
                $this->reporterror($e);
            } catch (ErrorException $e) {
                $this->reporterror($e);
                die("500: Internal Server Error");
            }
        }
    }
}