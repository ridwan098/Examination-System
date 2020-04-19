<?php
    require('../global/util.php');
    require('../global/db.php');

    $db = new Class_DB($servername, $username, $password);
    $db->connectToDb($dbname);

    $type = strtolower(getPostArg('type'));
    $email = getPostArg('email');
    $password = getPostArg('password');
    $name = getPostArg('name');

    $sql = "INSERT INTO Users (type,name,email,password) VALUES (?,?,?,?)";
    $db->executeQuery($sql, [$type,$name,$email,$password]);

    if ($db->getExecuteResult()){
        echo 1;
    }
    else {echo 0;}
?>