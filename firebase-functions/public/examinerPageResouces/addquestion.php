<?php

    require("../global/db.php");
    require("../global/util.php");

    $examid = getPostArg('examid');
    $question = getPostArg('question');
    $type = getPostArg('type');
    $marks = getPostArg("marks");
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
    $db->executeQuery("INSERT INTO ExamQuestions (examId,question,type,maxMarks)
                        VALUES (?,?,?,?)", [$examid, $question, $type, $marks]);

    if ($type == 1){
        $db->executeQuery("INSERT INTO McqQuestion VALUES (?,?,?)", [$db->getLastInsertId(), $fakeSql, $answer]);
    }

    echo 1;
?>