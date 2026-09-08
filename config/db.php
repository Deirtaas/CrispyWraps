<?php
$host = 'sql307.infinityfree.com';
$dbname = 'if0_42852262_crispywraps'; // Fixed database name
$user = 'if0_42852262';
$pass = '09126308280';
$port = '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
session_start();
?>