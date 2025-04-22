<?php

use watergames\pagebuilder;
    $pagebuilder = new pagebuilder;
    $pagebuilder->set_page_name("403");
    $pagebuilder->buildheader();
    http_response_code(403);

    ?>
    <div id="main"> 
        <h1>403 - Not Found</h1>
        <h2>Sorry! You don't have the right authorization to access this page.<br>Try again later maybe?</h2>
    
        <?php 
        if (@include_once("../config.php")) {
            if (defined('FRAMEWORK_VER') && defined('FRAMEWORK_NAME')) {
                echo "<p><i>This server is running ". FRAMEWORK_NAME . " " .FRAMEWORK_VER ."</i></p>";
            }
        }
        ?>
        </div>
        <? $pagebuilder->get_snippet("footer"); ?>