<?php
require_once('engine.php');

$link = connect($hostname, $username, $password, $database);

header('Location: ' . config($link, 'connect') . config($link, 'domain'));

mysqli_close($link);
?>