<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dcs";

// Créer la connexion
$pdo = new PDO("mysql:host=".$servername.";dbname=".$dbname, $username, $password);

?>