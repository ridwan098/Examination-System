<?php

    require("db.php");

    try {
        $conn = new PDO("mysql:host=$servername;dbname=higherexam", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
        die(0);
    }

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

    $conn = null;

    function markQuestion($conn, $qid, $mark, $comment){
        $sql = "UPDATE CompletedQuestions 
                SET markReceived=?, comment=?
                WHERE id=?";
        $result = $conn->prepare($sql);
        $result->execute([$mark, $comment, $qid]);
    }
?>