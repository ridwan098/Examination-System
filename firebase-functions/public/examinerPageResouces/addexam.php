<?php

    require('../db.php');

    $mname = getPostArg('mname');
    $date = getPostArg('date');
    $time = getPostArg('time');
    $timestamp = strtotime($date . " " . $time);
    $length = strtotime(getPostArg('length')) - strtotime('TODAY');

    $db = new Class_DB($servername,$username, $password);
    $db->connectToDb("higherexam");
    $db->executeQuery("INSERT INTO Exams (authorId,subject,courseCode,isMcq,timerLength,date)
                        VALUES (1,?,1,1,?,?)", [$mname, $length, $timestamp]);

    echo $db->getLastInsertId();

    function getPostArg($name){
        return isset($_POST[$name]) ? $_POST[$name] : die(0);
    }
?>