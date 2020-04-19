<?php
    require('../global/util.php');
    require('../global/db.php');

    session_start();

    if (!isSessionLoggedIn()){
        die(0);
    }

    $mname = getPostArg('mname');
    $date = getPostArg('date');
    $time = getPostArg('time');
    $timestamp = strtotime($date . " " . $time);
    $length = strtotime(getPostArg('length')) - strtotime('TODAY');

    $db = new Class_DB($servername,$username,$password);
    $db->connectToDb($dbname);

    // check if there is exams with the same name
    $query = "SELECT id FROM Exams WHERE subject=?";
    if (isset($_POST["examid"])){
        $examid = $_POST['examid'];
        $query .= " AND id=?";
        $result = $db->executeQuery($query, [$mname, $examid]);
    }
    else{
        $result = $db->executeQuery($query, [$mname]);
    }
    $exams = [];
    while ($row = $result->fetch()){
        $exams[] = $row;
    }

    if (sizeof($exams) > 0){
        echo -1;
        die();
    }

    if (isset($examid)){
        $db->executeQuery("UPDATE Exams SET subject=?,timerLength=?,date=? WHERE id=?", [$mname, $length, $timestamp, $examid]);
        echo 1;
    }
    else{
        $db->executeQuery("INSERT INTO Exams (authorId,subject,courseCode,isMcq,timerLength,date,examinerId)
                        VALUES (1,?,1,1,?,?,?)", [$mname, $length, $timestamp, $_SESSION['userid']]);

        echo $db->getLastInsertId();
    }
?>