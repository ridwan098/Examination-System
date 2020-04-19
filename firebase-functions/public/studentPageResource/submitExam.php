<?php
    require("../global/db.php");

    if (isset($_POST['examid']) && isset($_POST['userid'])){
        
        $examId = $_POST['examid'];
        $studentId = $_POST['userid'];

        // Connect to sql db
        $db = new Class_DB($servername, $username, $password);
        $db->connectToDb($dbname);

        // Get list of questions we should have
        $query = "SELECT * FROM ExamQuestions WHERE examId=?";
        $result = $db->executeQuery($query, [$examId]);
        $questions = [];
        while ($row = $result->fetch()){
            $questions[] = $row;
        }

        // Execute query for finished exam
        $query = "INSERT INTO FinishedExam (examId, userId, marked)
                VALUES (?, ?, 0)";
        $result = $db->executeQuery($query, [$examId, $studentId]);

        // Execute queries for answers
        $query = "INSERT INTO CompletedQuestions (examqId, finExamId, answer)
                VALUES ";
        $needcomma = false;
        $values = [];
        foreach ($questions as $q){
            if ($needcomma){
                $query .= ",";
            }
            $query .= "(?,?,?)";
            $values[] = $q['examqId'];
            $values[] = $db->getLastInsertId();
            $values[] = isset($_POST[$q['examqId']]) ? $_POST[$q['examqId']] : 'Not answered';
            $needcomma = true;
        }
        /*foreach ($_POST as $key => $value){
            if (is_numeric($key)){
                if ($needcomma){
                    $query .= ",";
                }
                $query .= "(?, ?, ?)";
                $values[] = $key;
                $values[] = $db->getLastInsertId();
                $values[] = $value;
                $needcomma = true;
            }
        }*/
        $result = $db->executeQuery($query, $values);

        // Remove from database after student has sat exam
        $query = "DELETE FROM StudentExamRelation WHERE examId=? AND userId=?";
        $db->executeQuery($query, [$examId, $studentId]);

        echo "1";
    }
?>