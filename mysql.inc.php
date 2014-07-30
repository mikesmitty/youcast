<?php

require_once 'config.inc.php';

$db_conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db_conn->connect_errno) {
    echo "Failed to connect to MySQL: " . $db_conn->connect_error;
}
