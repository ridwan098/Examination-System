<?php

    require("../global/db.php");

    $conn = new Class_DB($servername, $username, $password);
    $conn->connectToDb($dbname);

    if (isset($_POST['qid']) && isset($_POST['mark']) && isset($_POST['comment'])){
        $qid = $_POST['qid'];
        $mark = $_POST['mark'];
        $comment = $_POST['comment'];

        markQuestion($conn, $qid, $mark, $comment);
        echo 1;
    }
    else {
        $i = 0;
        while (isset($_POST["qid$i"]) && isset($_POST["mark$i"]) && isset($_POST["comment$i"])){
            $qid = $_POST["qid$i"];
            $mark = $_POST["mark$i"];
            $comment = $_POST["comment$i"];

            markQuestion($conn, $qid, $mark, $comment);
            $i++;
        }
        echo 1;
    }

    if (isset($_POST['examid']) && isset($_POST['finalize']) && $_POST['finalize'] == 1){
        $sql = "UPDATE FinishedExam
                SET marked=1
                WHERE finishedId=?";
        $conn->executeQuery($sql, [$_POST['examid']]);
    }

    $conn = null;

    function markQuestion($conn, $qid, $mark, $comment){
        if ($mark != ""){
            $sql = "UPDATE CompletedQuestions 
                    SET markReceived=?, comment=?
                    WHERE id=?";
            $conn->executeQuery($sql, [$mark, $comment, $qid]);
        }
        else{
            $sql = "UPDATE CompletedQuestions
                    SET comment = ?
                    WHERE id=?";
            $conn->executeQuery($sql, [$comment, $qid]);
        }
    }
?>