<?php
$user = 'root';
$pass = '';
$server = '127.0.0.1';
$db = 'user';

$dsn = "mysql:host=$server;dbname=$db;charset=utf8";

try {
    $pdo = new \PDO($dsn, $user, $pass);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    // For mysql to return utf8 characters correctly you must call this after connecting
    $pdo->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die('Could not connect to the database:<br/>' . $e);
}

// Set attributes so that pdo will return numbbers for numbers insteand of strings . 
// EXample if a column is 1.1 it will be a number instead of "1.1" when the front end gets it.
$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$DBConfig = array(
    'dbs.options' => array(
        'user' => array(
            'driver'   => 'pdo_mysql',
            'pdo' => $pdo
        ),
        'test' => array(
            'driver'   => 'pdo_mysql',
            'pdo' => $pdo
        )
    )
);
