<?php
    require('../global/db.php');
    require('../global/util.php');

    session_start();

    $db = new Class_DB($servername, $username, $password);
    $db->connectToDb($dbname);

    $email = getPostArg("email");
    
    $query = "DELETE FROM Users WHERE email=?";
    $result = $db->executeQuery($query, [$email]);
    if ($_SESSION['email'] == $email){
        $_SESSION['auth'] = false;
        $_SESSION['email']= null;
    }
?>