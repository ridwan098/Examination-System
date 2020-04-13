<?php

    require('../global/util.php');
    require('../global/db.php');

    $examid = getPostArg('examid');
    $email = getPostArg('student');

    $db = new Class_DB($servername,$username,$password);
    $db->connectToDb($dbname);
    $db->executeQuery("INSERT INTO StudentExamRelation
                        SELECT ?, id FROM Users WHERE email=?", [$examid, $email]);

    echo 1;
?>