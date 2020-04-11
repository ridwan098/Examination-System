<?php
    require('../global/util.php');
    require('../global/db.php');

    $mname = getPostArg('mname');
    $date = getPostArg('date');
    $time = getPostArg('time');
    $timestamp = strtotime($date . " " . $time);
    $length = strtotime(getPostArg('length')) - strtotime('TODAY');

    $db = new Class_DB($servername,$username,$password);
    $db->connectToDb($dbname);
    $db->executeQuery("INSERT INTO Exams (authorId,subject,courseCode,isMcq,timerLength,date)
                        VALUES (1,?,1,1,?,?)", [$mname, $length, $timestamp]);

    echo $db->getLastInsertId();
?>