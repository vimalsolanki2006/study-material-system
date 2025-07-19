<?php

//defining database variable for connection
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'study_materials_db');

/* Attempt to connect to MySQL/MariaDB database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);


//verifing that database connection is established or not 
if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

?>