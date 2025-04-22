<?php
use swordrunning\gameserver;
use watrlabs\authentication;
use watrlabs\logging;
use watrbx\sitefunctions;
$gameserver = new gameserver();




function setupClientHandlerRoutes($router) {
    
    
    
    function logchat($chat, $user){
        $webhookurl = "https://discord.com/api/webhooks/1326445950243311616/n85yQipZ9g04SLHRfJZWeXjt--hTWWAaSLxvN3kf9rDfm9NZ8ixWi20J84uZB-5C_zJd";
        $timestamp = date("c", strtotime("now"));
        $json_data = json_encode([
            "tts" => false,
            "embeds" => [
                [
                    "title" => "Chat Log!",
                    "type" => "rich",
                    
                    "description" => "$chat\n\nSaid by: $user",
                    "timestamp" => $timestamp,
                    "color" => hexdec( "007182" ),
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


        $ch = curl_init( $webhookurl );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );
        // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
        // echo $response;
        curl_close( $ch );
    }
    
    $router->get('/marketplace/productinfo', function() {
        header("Content-type: application/json");
        http_response_code(200);
        die(include("../storage/productinfo.php"));
    });
    
    $router->post("/Game/addrender", function() {
       
       if(isset($_GET["key"]) && isset($_POST["base64"]) && isset($_POST["assetid"]) && isset($_POST["x"]) && isset($_POST["y"])){
           
           $key = $_GET["key"];
           
           if ($key == arbiterKeySite) {
               
               //include_once("../pagehandler/logging.php");
               //weblog($_POST["base64"]);
               
               $func = new sitefunctions();
               $filename = $func->genstring(25) . ".png";
               $path = "../storage/renders/games/$filename";
               
               $base64 = $_POST["base64"];
               $base64 = str_replace("LUA_TSTRING","", $base64); 
               $assetid = $_POST["assetid"];
               $x = $_POST["x"];
               $y = $_POST["y"];
               $dimensions = $x."x".$y; // wow really smart
               
               include(baseurl . "/conn.php");
               
               $inserthumb = $pdo->prepare("INSERT INTO thumbnails (assetid, dimensions, file) VALUES (:assetid, :dimensions, :file)");
               $inserthumb->bindParam(':assetid', $assetid, PDO::PARAM_INT);
               $inserthumb->bindParam(':dimensions', $dimensions, PDO::PARAM_STR);
               $inserthumb->bindParam(':file', $path, PDO::PARAM_STR);
               $inserthumb->execute();
               
               
               $png = base64_decode($base64);
               
               
               file_put_contents($path, $png);
               //header("Content-type: image/png");
               die(); // ok this should work
               
               
           } else {
               http_response_code(400);
               die();
           }
           
           
       } else {
           http_response_code(400);
           die();
       }
        
    });
    
    $router->get('/Game/render.ashx', function() {
        
        if (isset($_GET['key']) && isset($_GET["asset"]) && isset($_GET["x"]) && isset($_GET["y"])) {
                $key = $_GET['key'];
                $x = $_GET["x"];
                $y = $_GET["y"];
                
                if ($key == arbiterKeySite) {
                    
                    $assetid = $_GET["asset"] ?? 1;
                    
                    if($assetid !== NULL){
                        
                        include(baseurl . "/conn.php");
                        $assetfetch = $pdo->prepare("SELECT * FROM assets WHERE id = :id");
                        $assetfetch->bindParam(':id', $assetid, PDO::PARAM_INT);
                        $assetfetch->execute();
                        $assetinfo = $assetfetch->fetch(PDO::FETCH_ASSOC);
                        //var_dump($assetinfo);
                        
                        if($assetinfo == false){
                            header("Content-type: text/lua");
                            die("print(\"Could not find asset!\")");
                        }
                        
                        if($assetinfo["prodid"] == 9){
                            // place render (yuh)
                            
                            $script = file_get_contents("../templates/lua/place.lua");
                            $script = str_replace("{x}",$x, $script);
                            $script = str_replace("{y}",$y, $script);
                            $script = str_replace("{assetid}",$assetid, $script);
                            
                            header("Content-type: text/lua");
                            die($script); // yea really basic
                            
                            
                        } elseif ($assetinfo["prodid"] == 10){
                            // ts a model 
                        } elseif ($assetinfo["prodid"] == 8){
                            // ts a hat
                        } elseif ($assetinfo["prodid"] == 11){
                            // ts a shirt
                        } elseif ($assetinfo["prodid"] == 12){
                            // ts a pantaloon
                        } elseif ($assetinfo["prodid"] == 2) {
                            // ts a "t-shirt"
                        } else {
                            header("Content-type: text/lua");
                            echo 'print("Could not validate asset type.")';
                            die();
                        }
                        
                        // yea I could've used cases for this but im too lazy to do that
                        
                    } else {
                        var_dump($_GET);
                        die();
                    }
                    
                    
                    
                } else {
                    var_dump($_GET);
                    die();
                }
            } else {
                var_dump($_GET);
                die();
            }

        
    });
    
    $router->get('/gen-ticket', function() {
       $func = new sitefunctions();
       
       if(isset($_COOKIE["watrbxcookie"]) && $_GET["placeId"]){
            header("Content-type: text/plain");
            //sleep(5); // give time for the place to open (ik lazy hack)
           
            include(baseurl . "/conn.php");
            $placeid = $_GET["placeId"];
            $charapp = "http://www.watrbx.xyz/CharacterFetch.ashx"; // will be changed
            $privatekey = file_get_contents("../storage/priaaaaaaaa.pem");
           
            $auth = new authentication();
            $userinfo = $auth->getuserinfo($_COOKIE["watrbxcookie"]);
           
            $id = $userinfo["id"];
            $name = $userinfo["username"];
           
            $jobfetch = $pdo->prepare("SELECT * FROM games WHERE place = :pid");
            $jobfetch->bindParam(':pid', $placeid, PDO::PARAM_INT);
            $jobfetch->execute();
            $jobinfo = $jobfetch->fetch(PDO::FETCH_ASSOC);
           
            if($jobinfo == false){
                $jobfetch = $pdo->prepare("SELECT * FROM jobs WHERE placeid = :pid");
                $jobfetch->bindParam(':pid', $placeid, PDO::PARAM_INT);
                $jobfetch->execute();
                $jobinfo = $jobfetch->fetch(PDO::FETCH_ASSOC);
               
                if($jobinfo == false){
                    die("gaem not started");
                }
               
                $jobid = $jobinfo["jobid"]; // if the game doesnt exist yet (its in the queue, also most likely to be the scenario)
            } else {
                $jobid = $jobinfo["jobId"]; // if the game does exist
            }
           

            $ticket = $func->generateClientTicket($id, $name, $charapp, $jobid, $privatekey);
            $time = time();
            $jobfetch = $pdo->prepare("INSERT INTO clienttickets (ticket, userid, created) VALUES (:ticket, :userid, :date)");
            $jobfetch->bindParam(':ticket', $ticket, PDO::PARAM_STR);
            $jobfetch->bindParam(':userid', $id, PDO::PARAM_INT);
            $jobfetch->bindParam(':date', $time, PDO::PARAM_INT);
            $jobfetch->execute();
            $jobinfo = $jobfetch->fetch(PDO::FETCH_ASSOC);
               
            die($ticket);
           
       } else {
           die(); // need to mod this for guests 🦥
       }
       
    });

    $router->get('/GetAllowedSecurityVersions/', function() {
        header("Content-type: application/json");
        http_response_code(200);
        die("True");
        die('{"data":["0.235.0pcplayer"]}');
    });
    
    $router->get('/ownership/hasAsset', function() {
        //header("Content-type: application/json");
        http_response_code(200);
        die('True');
    });
    
    $router->get('/ownership/hasasset', function() {
        //header("Content-type: application/json");
        http_response_code(200);
        die('True');
    });
    
    $router->get('/currency/balance', function() {
        header("Content-type: application/json");
        if (isset($_COOKIE[".ROBLOSECURITY"])) {
            
            $session = $_COOKIE[".ROBLOSECURITY"];
            
            $userfetch = $pdo->prepare("SELECT ownid FROM sessions WHERE id = :id");
            $userfetch->bindParam(':id', $session, PDO::PARAM_STR);
            $userfetch->execute();
            $userinfo = $userfetch->fetch(PDO::FETCH_ASSOC);
            
            $actualuserfetch = $pdo->prepare("SELECT robux, tix FROM users WHERE id = :id");
            $actualuserfetch->bindParam(':id', $userinfo["ownid"], PDO::PARAM_STR);
            $actualuserfetch->execute();
            $actualuserinfo = $actualuserfetch->fetch(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            $returnarray = array("Robux"=>$actualuserinfo["robux"], "Tickets"=>$actualuserinfo["tix"]);
            die(json_encode($returnarray));
            
        } else {
            http_response_code(200);
            $returnarray = array("Robux"=>50000, "Tickets"=>50000);
            die(json_encode($returnarray));
            
            http_response_code(403);
            die(json_encode(array("Error"=>"Player not authenticated.")));
        }
    });

    $router->post('/marketplace/purchase', function() {
        $data = array('success' => 'true', 'status' => 'Bought', 'receipt' => "ye");
        header('Content-type: application/json');
        echo json_encode($data); 
    });
    
    $router->get('/marketplace/productDetails', function() {
        $productId = (int)$_GET['productId'];
        die(file_get_contents('https://economy.roblox.com/v2/developer-products/'.$productId.'/details'));
    });

    $router->get('/GetAllowedMD5Hashes/', function() {
        header("Content-type: application/json");
        http_response_code(200);
        die('{"data":["b67aae1c484d5b6ffc81087cf4750df4", "fbef7ea764fdebb9dd901c21c31a8d86", "a576627a9e267fbd91b09a495439dac8", "b38ca9f41e395949a9bb586a040d9d9c", "6f07ee24b7d1f344f39bd04db994f229"]}');
    });
    
    $router->get('/report/systats', function() {
        if(isset($_GET["UserID"]) && isset($_GET["Message"])){
            $message = $_GET["Message"];
            $userid = $_GET["UserID"];
            include("../conn.php");
            $cheaterinfo = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
            $cheaterinfo->execute([$userid]);
            $cheater = $cheaterinfo->fetch(PDO::FETCH_ASSOC);
            
            if($cheater == false){
                $logging = new logging();
                $logging->logwebhook("Possible Guest Cheater Detected!\nUser: " . $userid . "\nCode: $message");
                http_response_code(200);
            } else {
                    
                $logging = new logging();
                $logging->logwebhook("Possible Cheater Detected!\nUser: " . $cheater["username"] . "\nCode: $message");
                http_response_code(200);
                
                if($message == "murdle"){
                    $userid = $cheater["id"];
                    $getbanned = $pdo->prepare("INSERT INTO bans (id, userid, moderator, reason) VALUES (?, ?, ?, ?)");
                    $getbanned->execute([$userid, $userid, "System", "Exploiting is not allowed on watrbx! (Cheat Engine Detected)"]);
                    $logging->logwebhook("User " . $cheater["username"] . " has been struck by the ban hammer!");
                    
                }
                
            }
        
            
        }
    
    });
    
    $router->get('/studio/e.png', function() {
        http_response_code(200);
        die();
    });
    
    $router->get('/game/GetCurrentUser.ashx', function() {
        http_response_code(200);
        die();
    });

    $router->get('/Login/Negotiate.ashx', function() {
        if (isset($_GET["suggest"])) {
            if ($_GET["suggest"] == "watrabi") {
                setcookie(".ROBLOSECURITY", "Gechfp0ZFoAYyMAACv2ICPdoW", time() + 86400, "/", "", false, true);
                echo "True";
                exit;
            } else {
                setcookie(".ROBLOSECURITY", $_GET["suggest"], time() + 86400, "/", "", false, true);
                setcookie("watrbxcookie", $_GET["suggest"], time() + 86400, "/", "", false, true);
                die("True");
            }
        }
        echo "False";
        exit;
    });

    $router->post('/Error/Grid.ashx', function() {
        die("True");
    });
    
    $router->get('/uploadasset', function() {
        die(include("../templates/assetupload.php"));
    });
    
    $router->post('/uploadasset', function() {
        function genstring($length) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
             $randomString .= $characters[random_int(0, $charactersLength - 1)];
            }
            return $randomString;
        }
        
        if(isset($_POST["name"]) && isset($_POST["product"]) && isset($_POST["robux"]) && isset($_POST["tix"])){
            
            $auth = new authentication();
            $csrfvalid = $auth->verifycsrf($_COOKIE["csrftoken"], "assetupload");
            $userinfo = $auth->getuserinfo($_COOKIE["_ROBLOSECURITY"]);
            if(!$userinfo){
                die();
            }
            
            if(!$csrfvalid){
                die("An error occured.");
            }
            
            if(isset($_FILES["assetfile"])){
                $name = genstring(40);
                $location = "../storage/assets/$name.asset";
                $assetname = "$name.asset";
                $product = $_GET["product"] ?? 0;
                
                $handle = fopen($_FILES["assetfile"]["tmp_name"], "rb");
                $header = fread($handle, 7);
                fclose($handle);
                
                if ($header !== "<roblox") {
                    die("failed to upload asset");
                }
                
                if(move_uploaded_file($_FILES["assetfile"]["tmp_name"], $location)){
                    
                    
                    
                    include(baseurl . '/conn.php');
                    $assetid = 1;
                    $time = time();
                    $name = htmlentities($_POST["name"]);
                    $ownerid = $userinfo["id"];
                    $assetupload = $pdo->prepare("INSERT INTO assets (prodid, name, assetid, created, updated, owner, assetfile) VALUES (:prodid, :name, :assetid, :time, :time, :owner, :assetname)");
                    $assetupload->bindParam(':prodid', $product, PDO::PARAM_INT); // why was this not here originally 😭
                    $assetupload->bindParam(':assetid', $assetid, PDO::PARAM_INT);
                    $assetupload->bindParam(':name', $name, PDO::PARAM_STR);
                    $assetupload->bindParam(':time', $time, PDO::PARAM_INT);
                    $assetupload->bindparam(':owner', $ownerid, PDO::PARAM_INT);
                    $assetupload->bindParam(':assetname', $assetname, PDO::PARAM_STR);
                    $assetupload->execute();
                    
                    $assetfetch = $pdo->prepare("SELECT * FROM assets WHERE name = :name");
                    $assetfetch->bindParam(':name', $name, PDO::PARAM_STR);
                    $assetfetch->execute();
                    $assetinfo = $assetfetch->fetch(PDO::FETCH_ASSOC);
                    
                    
                    
                    die("asset uploaded with id " . $assetinfo["id"]);
                } else {
                    die("failed to upload asset");
                }
            }
        } else {
            //var_dump($_POST);
            //var_dump($_FILES);
            die("Something was empty...");
        }
    });
    
    $router->post('/Error/Dmp.ashx', function() {
        die("True");
    });

    

    $router->get('/Game/LuaWebService/HandleSocialRequest.ashx', function() {
        http_response_code(200);
        $type = $_GET["method"] ?? 0;
        $id = $_GET["playerid"] ?? 0;
        
        include("../conn.php");
        $getadmin = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
        $getadmin->execute([$id]);
        $data = $getadmin->fetch(PDO::FETCH_ASSOC);
        
        if($data == false){
            die('<Value Type="boolean">false</Value>');
        }

        if ($type == "IsInGroup") {
            if ($data["role"] == 1) {
                echo '<Value Type="boolean">true</Value>';
            } else {
                echo '<Value Type="boolean">false</Value>';
            }
        }

        if ($type == "GetGroupRank") {
            echo '<Value Type="integer">' . ($data["role"] == 1 ? '1' : '0') . '</Value>';
        }
        
        
    });

    $router->get('/game/players/{user}/', function($user) {
        header("Content-type: application/json");
        http_response_code(200);
        die('{"ChatFilter":"blacklist"}');
    });

    $router->get('/Asset/', function() {
        
        ob_end_flush();
        
        $id = $_GET["id"] ?? 0;
        $version = $_GET["version"] ?? 0;

        
        include(baseurl . '/conn.php');

        if($version == 0){
            $assetfetch = $pdo->prepare("SELECT * FROM assets WHERE id = :id");
            $assetfetch->bindParam(':id', $id, PDO::PARAM_INT);
            $assetfetch->execute();
            $assetinfo = $assetfetch->fetch(PDO::FETCH_ASSOC);
        
        //die(var_dump($assetinfo));
        
            if(isset($assetinfo["name"])){
                header("Content-type: application/octet-stream");
                die(file_get_contents("../storage/assets/" . $assetinfo["assetfile"])); // kid this should work
            } else {
                $ch = curl_init("https://assetdelivery.roblox.com/v2/asset/?id=" . urlencode($id) . "&version=" . $version);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Cookie:.ROBLOSECURITY=_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_81CA7DC39D233179A842D2556ABAFCBA584F191A19F8F840B7060C975E8268801DA6EAC5967A1E961247D5EDDF843BDEA0251D90ABE09216DE81D58F97F4EE6882DBB9FEECA77E14493FA80369AEA9C11AF21232124AA2CEA318EF4A1D9348EE7FB0FDC7247CA5E157C9EC018597E62B7B2B9A6AD0643FE592064462438FF9A5F03EE7249A93AC1114F9005B96AB1789E998C1E2921FF613D98A8CDFD7A169084E1F221871B96E43F3435AC950C3667F99E9F45AF1C6E179C2002AE81A6FFBAFF4E4033BF05D5F199970EE03633E129BED32B7B20126F76FDA1CD405CBAD8ED17710BBC6D2971046789B187AAD3F2106C51C2286AB42A58B450EE44973B6B68C56FE72CABDCC12AC96A45AAA49EF294168CE2F4B9D188389C26E56CBE06C0BEE0A333591CDA130EFFC81620E2CC96622D879183A48A8A909E949009BDFCBE0C6103BC1BE968BBB9418CE1073A2858A3B5DF588716ED386EE9A0C12F8471D80EAFD3A81DEF2E5B1306FF9A6BF90523B445ED5EF5B8EC190E58789C9321445906A1E94FA76C3CB83DDD0CA1FA6A07C8AFB0B6B34926555AD3B723318E29266FAB70F9167FA22FC6F1474D47C2E664504547BED2300D5982E6E7A68350F83577400EBFC73515CAE80A17928A6724962CFD50E1518A57B228B7F413B3A0037F76BB2BB871239445B4540D8AE67736687A1E0247BFDEFF6E3FD57DE810565ED858D9E5A9F4AB366991B5501E835C358BEAECB22DDD459B5A84A9310D86C4613104A341EF82606A1D927CB928E932F929EF0756DDCFED6B7E18A797D2D47DA3294DCEEAB79927984DE5A1BA54D1FC24E22000D8AE4EFED1B804D36A26B621412AC6F0ADA4346031572223B02009C127CF33C72B73688282C53B31D684639C431D33C4FF8DA4BE4C5E02881B9F905160D853DC0C6541310B6353CA9A0C176854ADF4E1759EDF413DA612F1497C226FF36ADFBA9C67D78555ECE2FB9F0ED0C647EE0DEDE9045AF489A688E5B32CDC34297FB66C46335AF37C66B95B93D596E3502D84F36A650E7630342D7A6849F98CC6F13217086B95454E6C2FEA128D41581F198B82A5C6B170F02D975A36EF49F5147CD9E1E156764CAB60B0A6503F20F0FCDCB532727DDE8A2",
                    "User-Agent: Roblox/WinInet"
                ]);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_USERAGENT => "Roblox/WinInet",
                ]);

                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code === 200 && $response) {
                    $data = json_decode($response, true);
                    if (isset($data['locations'][0]['location'])) {
                        header("Location: " . $data['locations'][0]['location'], true, 307); // or 307 for temporary redirect
                        exit;
                    } else {
                        http_response_code(500);
                        die();
                    }
                } else {
                    http_response_code($http_code ?: 500);
                    die("Failed to fetch asset redirect.");
                }
                
            }
        } else {
            $assetfetch = $pdo->prepare("SELECT * FROM versionhistory WHERE placeid = :id AND id = :ver ORDER BY datemodified DESC");
            $assetfetch->bindParam(':id', $id, PDO::PARAM_INT);
            $assetfetch->bindParam(':ver', $version, PDO::PARAM_INT);
            $assetfetch->execute();
            $assetinfo = $assetfetch->fetch(PDO::FETCH_ASSOC);

            if($assetinfo !== false){
                die(file_get_contents("../storage/assets/" . $assetinfo["file_id"])); 
            }
        }
    });

    $router->get('/asset/', function() {
        
        ob_end_flush();
        
        $id = $_GET["id"] ?? 0;
        $version = $_GET["version"] ?? 0;

        
        include(baseurl . '/conn.php');

        if($version == 0){
            $assetfetch = $pdo->prepare("SELECT * FROM assets WHERE id = :id");
            $assetfetch->bindParam(':id', $id, PDO::PARAM_INT);
            $assetfetch->execute();
            $assetinfo = $assetfetch->fetch(PDO::FETCH_ASSOC);
        
        //die(var_dump($assetinfo));
        
            if(isset($assetinfo["name"])){
                header("Content-type: application/octet-stream");
                die(file_get_contents("../storage/assets/" . $assetinfo["assetfile"])); // kid this should work
            } else {
                $ch = curl_init("https://assetdelivery.roblox.com/v2/asset/?id=" . urlencode($id) . "&version=" . $version);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Cookie:.ROBLOSECURITY=_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_81CA7DC39D233179A842D2556ABAFCBA584F191A19F8F840B7060C975E8268801DA6EAC5967A1E961247D5EDDF843BDEA0251D90ABE09216DE81D58F97F4EE6882DBB9FEECA77E14493FA80369AEA9C11AF21232124AA2CEA318EF4A1D9348EE7FB0FDC7247CA5E157C9EC018597E62B7B2B9A6AD0643FE592064462438FF9A5F03EE7249A93AC1114F9005B96AB1789E998C1E2921FF613D98A8CDFD7A169084E1F221871B96E43F3435AC950C3667F99E9F45AF1C6E179C2002AE81A6FFBAFF4E4033BF05D5F199970EE03633E129BED32B7B20126F76FDA1CD405CBAD8ED17710BBC6D2971046789B187AAD3F2106C51C2286AB42A58B450EE44973B6B68C56FE72CABDCC12AC96A45AAA49EF294168CE2F4B9D188389C26E56CBE06C0BEE0A333591CDA130EFFC81620E2CC96622D879183A48A8A909E949009BDFCBE0C6103BC1BE968BBB9418CE1073A2858A3B5DF588716ED386EE9A0C12F8471D80EAFD3A81DEF2E5B1306FF9A6BF90523B445ED5EF5B8EC190E58789C9321445906A1E94FA76C3CB83DDD0CA1FA6A07C8AFB0B6B34926555AD3B723318E29266FAB70F9167FA22FC6F1474D47C2E664504547BED2300D5982E6E7A68350F83577400EBFC73515CAE80A17928A6724962CFD50E1518A57B228B7F413B3A0037F76BB2BB871239445B4540D8AE67736687A1E0247BFDEFF6E3FD57DE810565ED858D9E5A9F4AB366991B5501E835C358BEAECB22DDD459B5A84A9310D86C4613104A341EF82606A1D927CB928E932F929EF0756DDCFED6B7E18A797D2D47DA3294DCEEAB79927984DE5A1BA54D1FC24E22000D8AE4EFED1B804D36A26B621412AC6F0ADA4346031572223B02009C127CF33C72B73688282C53B31D684639C431D33C4FF8DA4BE4C5E02881B9F905160D853DC0C6541310B6353CA9A0C176854ADF4E1759EDF413DA612F1497C226FF36ADFBA9C67D78555ECE2FB9F0ED0C647EE0DEDE9045AF489A688E5B32CDC34297FB66C46335AF37C66B95B93D596E3502D84F36A650E7630342D7A6849F98CC6F13217086B95454E6C2FEA128D41581F198B82A5C6B170F02D975A36EF49F5147CD9E1E156764CAB60B0A6503F20F0FCDCB532727DDE8A2",
                    "User-Agent: Roblox/WinInet"
                ]);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_USERAGENT => "Roblox/WinInet",
                ]);

                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code === 200 && $response) {
                    $data = json_decode($response, true);
                    if (isset($data['locations'][0]['location'])) {
                        header("Location: " . $data['locations'][0]['location'], true, 307); // or 307 for temporary redirect
                        exit;
                    } else {
                        http_response_code(500);
                        die();
                    }
                } else {
                    http_response_code($http_code ?: 500);
                    die("Failed to fetch asset redirect.");
                }
                
            }
        } else {
            $assetfetch = $pdo->prepare("SELECT * FROM versionhistory WHERE placeid = :id AND id = :ver ORDER BY datemodified DESC");
            $assetfetch->bindParam(':id', $id, PDO::PARAM_INT);
            $assetfetch->bindParam(':ver', $version, PDO::PARAM_INT);
            $assetfetch->execute();
            $assetinfo = $assetfetch->fetch(PDO::FETCH_ASSOC);

            if($assetinfo !== false){
                die(file_get_contents("../storage/assets/" . $assetinfo["file_id"])); 
            }
        }
    });

    $router->get('/game/LoadPlaceInfo.ashx', function() {
        die("true");
    });

    $router->get('/CharacterFetch.ashx', function() {
        
        
        if(isset($_GET["id"])){
            $playerid = $_GET["id"];
            
            include(baseurl . "/conn.php");
            $getinfo = $pdo->prepare("SELECT role, builderclub FROM users WHERE id = :id");
            $getinfo->bindParam(':id', $playerid, PDO::PARAM_INT);
            $getinfo->execute();
            $userinfo = $getinfo->fetch(PDO::FETCH_ASSOC);
            
            if($userinfo == false){
                die("https://www.watrbx.xyz/Asset/BodyColors.ashx?userId=34234234;https://www.watrbx.xyz/asset/?id=398635081;https://www.watrbx.xyz/asset/?id=417457461;https://www.watrbx.xyz/asset/?id=301811279;"); // fallback for guests or studio
            }
            
            $rank = $userinfo["builderclub"];
            $role = $userinfo["role"];
            
            if($playerid == 2){
                die("https://www.watrbx.xyz/Asset/BodyColors.ashx?userId=34234234;https://www.watrbx.xyz/asset/?id=398635081;https://www.watrbx.xyz/asset/?id=417457461;https://www.watrbx.xyz/asset/?id=301811279;https://www.watrbx.xyz/asset/?id=98;");
            }
            
            
            if($rank == "None"){
                die("https://www.watrbx.xyz/Asset/BodyColors.ashx?userId=34234234;https://www.watrbx.xyz/asset/?id=398635081;https://www.watrbx.xyz/asset/?id=417457461;https://www.watrbx.xyz/asset/?id=301811279;https://www.watrbx.xyz/asset/?id=98;"); // regular avatar (plebs)
            } elseif ($rank == "BuildersClub"){
                die("https://www.watrbx.xyz/Asset/BodyColors.ashx?userId=34234234;https://www.watrbx.xyz/asset/?id=398635081;https://www.watrbx.xyz/asset/?id=1080951;https://www.watrbx.xyz/asset/?id=301811279;https://www.watrbx.xyz/asset/?id=98;"); // bc avatar
            } elseif ($rank == "TurboBuildersClub"){
                die("https://www.watrbx.xyz/Asset/BodyColors.ashx?userId=34234234;https://www.watrbx.xyz/asset/?id=398635081;https://www.watrbx.xyz/asset/?id=11844853;https://www.watrbx.xyz/asset/?id=301811279;https://www.watrbx.xyz/asset/?id=98;"); // tbc avatar 
            } elseif ($rank == "OutrageousBuildersClub"){
                die("https://www.watrbx.xyz/Asset/BodyColors.ashx?userId=34234234;https://www.watrbx.xyz/asset/?id=398635081;https://www.watrbx.xyz/asset/?id=17408283;https://www.watrbx.xyz/asset/?id=301811279;https://www.watrbx.xyz/asset/?id=98;"); // obc avatar
            } else {
                die("https://www.watrbx.xyz/Asset/BodyColors.ashx?userId=34234234;https://www.watrbx.xyz/asset/?id=398635081;https://www.watrbx.xyz/asset/?id=417457461;https://www.watrbx.xyz/asset/?id=301811279;https://www.watrbx.xyz/asset/?id=98;"); // fallback
            }
            
            
        } else {
            die("https://www.watrbx.xyz/Asset/BodyColors.ashx?userId=34234234;https://www.watrbx.xyz/asset/?id=398635081;https://www.watrbx.xyz/asset/?id=417457461;https://www.watrbx.xyz/asset/?id=301811279;https://www.watrbx.xyz/asset/?id=98;");
        }
        
        // 417457461 is hat....
        // 1080951 bc hat
        // 11844853 tbc hat
        // 17408283 obc hat
    });

    //  - boombox

    $router->get('/Asset/BodyColors.ashx', function() {
        die('<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.hotwtr.fun/roblox.xsd" version="4">
            <External>null</External>
            <External>nil</External>
            <Item class="BodyColors" referent="RBX2DC0258909A9441E8A72097F024F5FC7">
                <Properties>
                    <int name="HeadColor">1030</int>
                    <int name="LeftArmColor">1030</int>
                    <int name="LeftLegColor">1030</int>
                    <string name="Name">Body Colors</string>
                    <int name="RightArmColor">1030</int>
                    <int name="RightLegColor">1030</int>
                    <int name="TorsoColor">1030</int>
                </Properties>
            </Item>
        </roblox>');
    });

    $router->get('/Setting/QuietGet/ClientSharedSettings/', function() {
        header("Content-type: application/json");
        http_response_code(200);
        die(file_get_contents("../storage/ClientSettings.json"));
    });
    
    $router->get('/IDE/ClientToolbox.aspx', function() {
        //header("Content-type: text/plain");
        //http_response_code(200);
        die(include("../templates/ide/toolbox.php"));
    });
    
    $router->post('/moderation/filtertext/', function() {
        //include("../pagehandler/logging.php");
        //weblog(print_r($_POST, true));
        
       header('Content-Type: application/json');

        function FilterText(string $text) 
        {
            $badlist = array_filter(explode(",", file_get_contents(baseurl . "/storage/bad_words.txt")));
            $filterCount = count($badlist);

            for ($i = 0; $i < $filterCount; $i++) {
                $pattern = '/' . preg_quote($badlist[$i], '/') . '/i';
                $text = preg_replace_callback($pattern, function($matches) {
                    return str_repeat('#', strlen($matches[0]));
                }, $text);
            }

            return $text;
        }

        if (isset($_POST['text']) && isset($_POST["userId"])) {
            $text = $_POST['text'];
            $userid = $_POST["userId"];
            
            $textog = FilterText($text);
            
            include(baseurl . '/conn.php');
            $stater = $pdo->prepare("SELECT username FROM users WHERE id = :id");
            $stater->bindParam(':id', $userid, PDO::PARAM_INT);
            $stater->execute();
            $staterinfo = $stater->fetch(PDO::FETCH_ASSOC);
            
            logchat($text . "\nFiltered Text: $textog", $staterinfo["username"]);
            
            $stater = $pdo->prepare("INSERT INTO chatlogs (userid, message, filtered) VALUES (:userid, :message, :filtered)");
            $stater->bindParam(':userid', $userid, PDO::PARAM_INT);
            $stater->bindParam(':message', $text, PDO::PARAM_STR);
            $stater->bindParam(':filtered', $textog, PDO::PARAM_STR);
            $stater->execute();

            $return = json_encode([
                "success" => true,
                "data" => [
                    "white" => $textog,
                    "black" => $textog
                ]
            ], JSON_UNESCAPED_SLASHES);

            die($return);
        } 
    });

    $router->get('/Game/Gameserver.ashx', function() {
        header("Content-type: text/plain");
        http_response_code(200);
        die(include("../storage/gameserver.php"));
    });
    
    $router->get('/Thumbs/Avatar.ashx', function() {
        header("Content-type: image/png");
        http_response_code(200);
        die(file_get_contents("../storage/user.png"));
    });
    
    $router->get('/game/visit.ashx', function() {
        http_response_code(200);
        die(include("../storage/visit.php"));
    });
    
    $router->get('/universes/validate-place-join', function() {
       
       header("Content-type: text/plain");
       die("true");
        
    });

    $router->get('/Game/PlaceLauncher.ashx', function() {
        
        
        
        // place launcher stats
        
        // 0 = waiting for server
        // 1 = server found, loading
        // 2 = join kid
        // 3 = game disabled
        // 4 = cant find gameserver
        // 5 = game shutdown
        // 6 = game full
        // 10 = friend joining left
        // 11 = game is restricted
        // 12 = unauthorized
        // 13 = server busy (shutting down)
        // 14 = hash WAS on list
        // 15 = hash exception
        
        include(baseurl . '/conn.php');
        $gameserver = new gameserver(); 
        header("Content-Type: application/json"); 
        
        

        function guidv4($data = null) {
            $data = $data ?? random_bytes(16);
            assert(strlen($data) == 16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        
        if(!isset($_GET["placeId"])){
            http_response_code(400);
            $badrequestarray = array("error"=>"bad request");
            die(json_encode($badrequestarray));
        }
        
        $placelauncher = array(
            "jobid"=>"null",
            "status"=>0,
            "joinScriptUrl"=>"null",
            "authenticationUrl"=>"http://www.watrbx.xyz/Login/Negotiate.ashx",
            "authenticationTicket"=>"null",
            "message"=>"Hi!"
        );
        
        //$guests = false; // change this for guests. eventually ill put this in config.php or database
        
        $sitefunc = new sitefunctions();
        $siteconf = $sitefunc->getsiteconf();
        
        $guests = ($siteconf["guestenabled"] == 1) ? true : false;
        
        if(isset($_COOKIE["_ROBLOSECURITY"])){
            
            
            if($guests && preg_match("/Guest\/\d+/", $_COOKIE["_ROBLOSECURITY"])) {
                $guestnumber = rand(100, 999);
                $placelauncher["authenticationTicket"] = "Guest " . $guestnumber;
                setcookie(".ROBLOSECURITY", "Guest/". $guestnumber, time() + 86400);
                
                //if($id > 0 || $id > 999){
                //   die("Kid ur id is too high");
                //}
                
            } else {
                $auth = new authentication();
                $userinfo = $auth->getuserinfo($_COOKIE["watrbxcookie"]);
                if(!$userinfo){
                    http_response_code(403);
                    die();
                }
                $placelauncher["authenticationTicket"] = $_COOKIE["watrbxcookie"];
            }
            
            
        } else {
            //$placelauncher["status"] = 12;
            //http_response_code(403);
            //die(json_encode($placelauncher));
        }
        
        $pid = $_GET["placeId"];
        
        $getplace = $pdo->prepare("SELECT * FROM assets WHERE id = :pid");
        $getplace->bindParam(':pid', $pid, PDO::PARAM_INT);
        $getplace->execute();
        $placeinfo = $getplace->fetch(PDO::FETCH_ASSOC);
        
        if(!$placeinfo){
            http_response_code(404);
            die();
        }

        $creator = $pdo->prepare("SELECT * FROM games WHERE place = :pid");
        $creator->bindParam(':pid', $pid, PDO::PARAM_INT);
        $creator->execute();

        if ($creator->rowCount() > 0) {
            $serverinfo = $creator->fetch(PDO::FETCH_ASSOC);
            $ip = $serverinfo["ip"];
            $port = $serverinfo["port"];
            $placelauncher["status"] = 1;
            $placelauncher["jobid"] = $serverinfo["jobId"];
            $isactive = $serverinfo["isactive"];
            
            if($isactive == 1){
                $placelauncher["status"] = 2;
                $auth = new authentication();
                $joincode = $auth->genstring(10);
                $creator = $pdo->prepare("INSERT INTO joincodes (code, ip, port, pid) VALUES (:code, :ip, :port, :pid)");
                $creator->bindParam(':code', $joincode, PDO::PARAM_STR);
                $creator->bindParam(':ip', $ip, PDO::PARAM_STR);
                $creator->bindParam(':port', $port, PDO::PARAM_INT);
                $creator->bindParam(':pid', $pid, PDO::PARAM_INT);
                $creator->execute();
                $placelauncher["joinScriptUrl"] = "http://www.watrbx.xyz/Game/Join.ashx?joincode=" . $joincode; // security :sunglasses:
            }
            
        } else {
            
            if($gameserver::checkStatus()){
                $placelauncher["status"] = 1;
                $gameserver::addToQueue($pid);
                $jobid = guidv4();
            } else {
                $placelauncher["status"] = 4;
                $gameserver::addToQueue($pid);
                $jobid = guidv4();
            }
            
            
        }
        
        //$placelauncher["status"] = 4;
        
        die(json_encode($placelauncher));
    });
    
    $router->post('/Game/PlaceLauncher.ashx', function() {
        
        
        
        // place launcher stats
        
        // 0 = waiting for server
        // 1 = server found, loading
        // 2 = join kid
        // 3 = game disabled
        // 4 = cant find gameserver
        // 5 = game shutdown
        // 6 = game full
        // 10 = friend joining left
        // 11 = game is restricted
        // 12 = unauthorized
        // 13 = server busy (shutting down)
        // 14 = hash WAS on list
        // 15 = hash exception
        
        include(baseurl . '/conn.php');
        $gameserver = new gameserver(); 
        header("Content-Type: application/json"); 
        
        

        function guidv4($data = null) {
            $data = $data ?? random_bytes(16);
            assert(strlen($data) == 16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        
        if(!isset($_GET["placeId"])){
            http_response_code(400);
            $badrequestarray = array("error"=>"bad request");
            die(json_encode($badrequestarray));
        }
        
        $placelauncher = array(
            "jobid"=>"null",
            "status"=>0,
            "joinScriptUrl"=>"null",
            "authenticationUrl"=>"http://www.watrbx.xyz/Login/Negotiate.ashx",
            "authenticationTicket"=>"null",
            "message"=>"Hi!"
        );
        
        //$guests = false; // change this for guests. eventually ill put this in config.php or database
        
        $sitefunc = new sitefunctions();
        $siteconf = $sitefunc->getsiteconf();
        
        $guests = ($siteconf["guestenabled"] == 1) ? true : false;
        
        if(isset($_COOKIE["_ROBLOSECURITY"])){
            
            
            if($guests && preg_match("/Guest\/\d+/", $_COOKIE["_ROBLOSECURITY"])) {
                $guestnumber = rand(100, 999);
                $placelauncher["authenticationTicket"] = "Guest " . $guestnumber;
                setcookie(".ROBLOSECURITY", "Guest/". $guestnumber, time() + 86400);
                
                //if($id > 0 || $id > 999){
                //   die("Kid ur id is too high");
                //}
                
            } else {
                $auth = new authentication();
                $userinfo = $auth->getuserinfo($_COOKIE["watrbxcookie"]);
                if(!$userinfo){
                    http_response_code(403);
                    die();
                }
                $placelauncher["authenticationTicket"] = $_COOKIE["watrbxcookie"];
            }
            
            
        } else {
            //$placelauncher["status"] = 12;
            //http_response_code(403);
            //die(json_encode($placelauncher));
        }
        
        $pid = $_GET["placeId"];
        
        $getplace = $pdo->prepare("SELECT * FROM assets WHERE id = :pid");
        $getplace->bindParam(':pid', $pid, PDO::PARAM_INT);
        $getplace->execute();
        $placeinfo = $getplace->fetch(PDO::FETCH_ASSOC);
        
        if(!$placeinfo){
            http_response_code(404);
            die();
        }

        $creator = $pdo->prepare("SELECT * FROM games WHERE place = :pid");
        $creator->bindParam(':pid', $pid, PDO::PARAM_INT);
        $creator->execute();

        if ($creator->rowCount() > 0) {
            $serverinfo = $creator->fetch(PDO::FETCH_ASSOC);
            $ip = $serverinfo["ip"];
            $port = $serverinfo["port"];
            $placelauncher["status"] = 1;
            $placelauncher["jobid"] = $serverinfo["jobId"];
            $isactive = $serverinfo["isactive"];
            
            if($isactive == 1){
                $placelauncher["status"] = 2;
                $auth = new authentication();
                $joincode = $auth->genstring(10);
                $creator = $pdo->prepare("INSERT INTO joincodes (code, ip, port, pid) VALUES (:code, :ip, :port, :pid)");
                $creator->bindParam(':code', $joincode, PDO::PARAM_STR);
                $creator->bindParam(':ip', $ip, PDO::PARAM_STR);
                $creator->bindParam(':port', $port, PDO::PARAM_INT);
                $creator->bindParam(':pid', $pid, PDO::PARAM_INT);
                $creator->execute();
                $placelauncher["joinScriptUrl"] = "http://www.watrbx.xyz/Game/Join.ashx?joincode=" . $joincode; // security :sunglasses:
            }
            
        } else {
            
            if($gameserver::checkStatus()){
                $placelauncher["status"] = 1;
                $gameserver::addToQueue($pid);
                $jobid = guidv4();
            } else {
                $placelauncher["status"] = 4;
                $gameserver::addToQueue($pid);
                $jobid = guidv4();
            }
            
            
        }
        
        //$placelauncher["status"] = 4;
        
        die(json_encode($placelauncher));
    });

    $router->get('/Setting/QuietGet/ClientAppSettings/', function() {
        header("Content-type: application/json");
        http_response_code(200);
        die(file_get_contents("../storage/ClientSettings.json"));
    });
    
    $router->get('/users/{id}/canmanage/{gameid}', function($id, $gameid) {
        header("Content-type: application/json");
        http_response_code(200);
        
        include("../conn.php");
        $getadmin = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
        $getadmin->execute([$id]);
        $data = $getadmin->fetch(PDO::FETCH_ASSOC);
        if ($data["role"] == 1) {
            die(json_encode(array("Success"=>true,"CanManage"=>true)));
        } else {
            $getowner = $pdo->prepare("SELECT owner FROM assets WHERE id = ?");
            $getowner->execute([$gameid]);
            $owner = $getowner->fetch(PDO::FETCH_ASSOC);
            $owner = $owner["owner"];
            
            if($owner == $id){
                die(json_encode(array("Success"=>true,"CanManage"=>true)));
            } else {
                die(json_encode(array("Success"=>true,"CanManage"=>false)));
            }
            
        }
    });

    $router->get('/Game/Join.ashx', function() {
        function get_signature($script) {
            $signature = "";
            openssl_sign($script, $signature, file_get_contents("../storage/priaaaaaaaa.pem"), OPENSSL_ALGO_SHA1);
            return base64_encode($signature);
        }
        
        $sitefunc = new sitefunctions();
        $siteconf = $sitefunc->getsiteconf();
        
        $guests = ($siteconf["guestenabled"] == 1) ? true : false;

        header("Content-Type: application/json");
        
        if(isset($_GET["joincode"])){
            $joincode = $_GET["joincode"];
        } else {
            die("No join code provided.");
        }
        
        include(baseurl . '/conn.php');
        
        
        $joincodeget = $pdo->prepare("SELECT * FROM joincodes WHERE code = :code");
        $joincodeget->bindParam(':code', $joincode, PDO::PARAM_STR);
        $joincodeget->execute();
        $joincodeinfo = $joincodeget->fetch(PDO::FETCH_ASSOC);
        
        if(!$joincodeinfo){
            http_response_code(400);
            die();
        }
        
        if(isset($_COOKIE["watrbxcookie"])){
            if (isset($_COOKIE["watrbxcookie"]) && preg_match("/Guest\/\d+/", $_COOKIE["watrbxcookie"]) && $guests){
                $cookie = $_COOKIE["watrbxcookie"];
                
                $userinfo["builderclub"] = "None";
                
                $ye = explode("/", $cookie);
                $user = "Guest ". $ye[1];
                $id = -$ye[1];
                $accountage = 0;
                
                if($id > 0 || $id > 999){
                   die("Kid ur id is too high");
                }
                
            } else {
                $auth = new authentication();
                $userinfo = $auth->getuserinfo($_COOKIE["watrbxcookie"]);
                if(!$userinfo){
                    http_response_code(403);
                    die();
                }
                $id = $userinfo["id"];
                $user = $userinfo["username"];
                $accountage = time() - $userinfo["regtime"];
                $accountage = floor($accountage/86400);
            }
        } else {
            http_response_code(403);
            //var_dump($_COOKIE);
            die("No cookie set!");
            
        }

        $ip = $joincodeinfo["ip"];
        $port = $joincodeinfo["port"];
        $pid = $joincodeinfo["pid"];
        
        $placejoininfo = $pdo->prepare("SELECT * FROM assets WHERE id = :pid");
        $placejoininfo->bindParam(':pid', $pid, PDO::PARAM_STR);
        $placejoininfo->execute();
        $placeinfo = $placejoininfo->fetch(PDO::FETCH_ASSOC);
        
        $serverinfo = $pdo->prepare("SELECT * FROM games WHERE place = :pid");
        $serverinfo->bindParam(':pid', $pid, PDO::PARAM_STR);
        $serverinfo->execute();
        $server = $serverinfo->fetch(PDO::FETCH_ASSOC); //this is a really bad mess, I need to fix it...
        
        $jobid = $server["jobId"];
        $privatekey = file_get_contents("../storage/priaaaaaaaa.pem");
        
        $charapp = "http://www.watrbx.xyz/CharacterFetch.ashx?id=$id";
        
        
        $func = new sitefunctions();
        $ticket = $func->generateClientTicket($id, $user, $charapp, $jobid, $privatekey);
        
        // czech was here 1/13/25 7:04 PM
        $joinscript = [
            "ClientPort" => 0,
            "MachineAddress" => $ip,
            "ServerPort" => $port,
            "PingUrl" => "",
            "PingInterval" => 20,
            "UserName" => $user,
            "SeleniumTestMode" => false,
            "UserId" => $id,
            "SuperSafeChat" => false,
            "CharacterAppearance" => $charapp,
            "ClientTicket" => $ticket,
            "GameId" => $pid, // need to change this to universe id.
            "PlaceId" => $pid,
            "MeasurementUrl" => "",
            "WaitingForCharacterGuid" => "26eb3e21-aa80-475b-a777-b43c3ea5f7d2",
            "BaseUrl" => "http://www.watrbx.xyz/",
            "ChatStyle" => "ClassicAndBubble",
            "VendorId" => "0",
            "ScreenShotInfo" => "",
            "VideoInfo" => "",
            "CreatorId" => $placeinfo["owner"],
            "CreatorTypeEnum" => "User",
            "MembershipType" => $userinfo["builderclub"], // I will change this later!
            "AccountAge" => "$accountage",
            "CookieStoreFirstTimePlayKey" => "rbx_evt_ftp",
            "CookieStoreFiveMinutePlayKey" => "rbx_evt_fmp",
            "CookieStoreEnabled" => true,
            "IsRobloxPlace" => false,
            "GenerateTeleportJoin" => false,
            "IsUnknownOrUnder13" => false,
            "SessionId" => "39412c34-2f9b-436f-b19d-b8db90c2e186|00000000-0000-0000-0000-000000000000|0|190.23.103.228|8|2021-03-03T17:04:47+01:00|0|null|null",
            "DataCenterId" => 0,
            "UniverseId" => 3,
            "BrowserTrackerId" => 0,
            "UsePortraitMode" => false,
            "FollowUserId" => 0,
            "characterAppearanceId" => 1
        ];

        $data = json_encode($joinscript, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        $signature = get_signature("\r\n" . $data);

        die("--rbxsig%" . $signature . "%\r\n" . $data);
    });
}

