<?php
    require("../db.php");

    if (isset($_POST['examid'])){
        
        $examId = $_POST['examid'];
        $studentId = 123456789;

        // Connect to sql db
        try {
            $conn = new PDO("mysql:host=$servername;dbname=higherexam", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            die("Connection failed: " . $e->getMessage());
        }

        // Execute query for finished exam
        $query = "INSERT INTO FinishedExam (examId, studentId, marked)
                VALUES (?, ?, 0)";
        $result = $conn->prepare($query);
        $result->execute([$examId, $studentId]);

        // Execute queries for answers
        $query = "INSERT INTO CompletedQuestions (examqId, finExamId, answer)
                VALUES ";
        $needcomma = false;
        $values = [];
        foreach ($_POST as $key => $value){
            if (is_numeric($key)){
                if ($needcomma){
                    $query .= ",";
                }
                $query .= "(?, ?, ?)";
                $values[] = $key;
                $values[] = $conn->lastInsertId();
                $values[] = $value;
                $needcomma = true;
            }
        }
        $result = $conn->prepare($query);
        $result->execute($values);
        echo "1";
        $conn = null;
    }
?>