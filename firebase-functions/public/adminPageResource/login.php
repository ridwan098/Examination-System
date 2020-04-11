<?php
    require('../global/db.php');
    require('../global/util.php');

    session_start();

    $db = new Class_DB($servername, $username, $password);
    $db->connectToDb($dbname);

    $email = getPostArg("email");
    $pass = getPostArg("password");
    
    $query = "SELECT * FROM Users WHERE email=? AND password=?";
    $result = $db->executeQuery($query, [$email, $pass]);
    $user = $result->fetch();
    if ($user){
        $_SESSION['auth'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['type'] = $user['type'];
        $_SESSION['userid'] = $user['id'];
        echo 1;
    }
    else{
        $_SESSION['auth'] = false;
        die(0);
    }
?>