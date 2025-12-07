<?php
$host = '127.0.0.1';
$db   = 'CarRentalCompany'; 
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

//DSN
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

//PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
