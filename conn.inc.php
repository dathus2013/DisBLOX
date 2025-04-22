<?php
try {
// conn.php
    $pdo = new PDO("mysql:host=localhost;dbname=databasename;charset=utf8mb4", "username", "password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    ob_clean();
    http_response_code(500);
    die("Database is dead! Please come back later.");
}