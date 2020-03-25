<?php

    require("db.php");

    $qid = $_POST['qid'];
    $mark = $_POST['mark'];
    $comment = $_POST['comment'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=higherexam", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
        die(0);
    }
    $sql = "UPDATE CompletedQuestions 
            SET markReceived=?, comment=?
            WHERE id=?";
    $result = $conn->prepare($sql);
    $result->execute([$mark, $comment, $qid]);
    $conn = null;

    echo 1;
?>