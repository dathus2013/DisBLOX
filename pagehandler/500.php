<?php

    use watergames\pagebuilder;
    use watrbx\sitefunctions;
    use watrlabs\authentication;
    try {
    $sitefunc = new sitefunctions();
    
    $auth = new authentication;
    $pagebuilder = new pagebuilder;
    $pagebuilder->set_page_name("Internal Server Error");
    $pagebuilder->buildheader();
    
    
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>500 - Internal Server Error.</title>
        <style>
        </style>
    </head>
    <body>
        <div id="main">
            <h1>500 - Internal Server Error.</h1>
            <hr>
            <h2>The server could not complete the request.</h2>
            <p><i>If you are the webmaster, please check website logs if you have the feature enabled.</i></p>
            <?php 
            if (@include_once("../config.php")) {
                if (defined('FRAMEWORK_VER') && defined('FRAMEWORK_NAME')) {
                    echo "<p><i>This server is running ". FRAMEWORK_NAME . " " .FRAMEWORK_VER ."</i></p>";
                }
            }
            ?>
        </div>
<? $pagebuilder->get_snippet("footer"); ?>
<? } catch(ErrorException $e){ die("500 - Internal Server Error"); } ?>