<?php

    require('../global/util.php');
    require('../global/db.php');

    $examid = getPostArg('examid');
    $userid = getPostArg('student');

    $db = new Class_DB($servername,$username,$password);
    $db->connectToDb($dbname);
    $db->executeQuery("INSERT INTO StudentExamRelation
                        VALUES (?,?)", [$examid, $userid]);

    echo 1;
?>