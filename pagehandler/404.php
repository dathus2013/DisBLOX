<?php

use watergames\pagebuilder;
    $pagebuilder = new pagebuilder;
    $pagebuilder->set_page_name("404");
    $pagebuilder->buildheader();
    http_response_code(404);

    ?>
    <div id="main"> 
        <h1>404 - Not Found</h1>
        <h2>Server could not find the request resource.</h2>
    
        <?php 
        if (@include_once("../config.php")) {
            if (defined('FRAMEWORK_VER') && defined('FRAMEWORK_NAME')) {
                echo "<p><i>This server is running ". FRAMEWORK_NAME . " " .FRAMEWORK_VER ."</i></p>";
            }
        }
        ?>
        </div>
        <? $pagebuilder->get_snippet("footer"); ?>