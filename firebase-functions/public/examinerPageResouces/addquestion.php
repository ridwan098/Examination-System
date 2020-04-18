<?php

    require("../global/db.php");
    require("../global/util.php");

    $examid = getPostArg('examid');
    $question = getPostArg('question');
    $type = getPostArg('type');
    $marks = getPostArg("marks");
    $editing = false;
    if (isset($_POST["qid"])){
        $qid = $_POST['qid'];
        $editing = true;
    }

    if ($type == 1){
        $fakeOptions = [getPostArg("fakeAnswer1")];
        $i = 2;
        while (isset($_POST["fakeAnswer$i"])){
            $answer = $_POST["fakeAnswer$i"];
            if ($answer != ""){
                $fakeOptions[] = $answer;
            }
            $i++;
        }
        $answer = getPostArg("answer");

        $fakeSql = "";
        foreach ($fakeOptions as $option){
            $fakeSql .= $option . "\0";
        }
    }

    $db = new Class_DB($servername,$username, $password);
    $db->connectToDb("higherexam");

    if (!$editing){
        $db->executeQuery("INSERT INTO ExamQuestions (examId,question,type,maxMarks)
                            VALUES (?,?,?,?)", [$examid, $question, $type, $marks]);

        if ($type == 1){
            $db->executeQuery("INSERT INTO McqQuestion VALUES (?,?,?)", [$db->getLastInsertId(), $fakeSql, $answer]);
        }
    }
    else{
        $db->executeQuery("UPDATE ExamQuestions SET question=?, maxMarks=? WHERE examqId=?", [$question, $marks, $qid]);
        if ($type==1){
            $db->executeQuery("UPDATE McqQuestion SET fakeOptions=?, answer=? WHERE examqId=?", [$fakeSql, $answer, $qid]);
        }
    }

    echo 1;
?>