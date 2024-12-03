<?php
    $dsn = 'pgsql:host=localhost;dbname=soccer';
    $username = 'postgres';
    $password = '123';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
?>
