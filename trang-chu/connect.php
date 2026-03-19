<?php

$servername = 'localhost';
$username = 'root';
$password = '';
$database = 'webpc'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$servername;dbname=$database;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
    PDO::ATTR_EMULATE_PREPARES   => false,                  
];

try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
  
    error_log("Lỗi kết nối CSDL: " . $e->getMessage());
   
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}
?>