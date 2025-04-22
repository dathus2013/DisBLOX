<?php
//include baseurl . "/conn.php";
use watrbx\sitefunctions;
$sitefunc = new sitefunctions();
    
?>

<div id="footer">
    <p><?=project_name?> - <? echo $sitefunc->getusers(); ?> Users - <a href="/info/privacy">Privacy</a> - <a href="/info/tos">Terms</a> - <a href="mailto:dmca@watrlabs.lol">DMCA</a>   |   We are in no way affiliated with ROBLOX.</p>
</div>

</body>
</html>
