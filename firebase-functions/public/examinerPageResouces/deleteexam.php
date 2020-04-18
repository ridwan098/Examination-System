<?php

    require("../global/util.php");
    require("../global/db.php");

    session_start();

    if (!isSessionLoggedIn()){
        die(0);
    }

    $examid = getPostArg("examid");

    $db = new Class_DB($servername,$username,$password);
    $db->connectToDb($dbname);
    $db->executeQuery("DELETE FROM Exams WHERE id=?", [$examid]); 

    echo 1;
?>