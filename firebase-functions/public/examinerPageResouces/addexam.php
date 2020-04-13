<?php
    require('../global/util.php');
    require('../global/db.php');

    session_start();

    if (!isset($_SESSION['auth']) || !$_SESSION['auth']){
        die(0);
    }

    $mname = getPostArg('mname');
    $date = getPostArg('date');
    $time = getPostArg('time');
    $timestamp = strtotime($date . " " . $time);
    $length = strtotime(getPostArg('length')) - strtotime('TODAY');

    $db = new Class_DB($servername,$username,$password);
    $db->connectToDb($dbname);
    $db->executeQuery("INSERT INTO Exams (authorId,subject,courseCode,isMcq,timerLength,date,examinerId)
                        VALUES (1,?,1,1,?,?,?)", [$mname, $length, $timestamp, $_SESSION['userid']]);

    echo $db->getLastInsertId();
?>