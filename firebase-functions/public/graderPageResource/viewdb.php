<?php
    require("../global/db.php");
    try {
        $conn = new PDO("mysql:host=$servername;dbname=higherexam", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }
    catch(PDOException $e)
    {
        die("Connection failed: " . $e->getMessage());
    }
    $queries = array(
        "Users",
        "Student",
        "Examiner",
        "Grader",
        "Exams",
        "StudentExamRelation",
        "FinishedExam",
        "ExamQuestions",
        "McqQuestion",
        "CompletedQuestions"
    );

    $tables = [];
    foreach ($queries as $q){
        $result = $conn->query("SELECT * FROM $q");
        $tables[$q] = [];
        while ($row = $result->fetch()){
            $tables[$q][] = $row;
        }
    }
    $conn = null;

?>

<html>
    <head>
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td, th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }
        </style>
    </head>
    <body>
        <?php 
            foreach ($tables as $tname => $table){
                echo "<h3>$tname</h3>";
                echo "<table><tr>";
                foreach ($table[0] as $rowname => $data){
                    echo "<th>$rowname</th>";
                }
                echo "</tr>";
                foreach ($table as $row){
                    echo "<tr>";
                    foreach ($row as $rowname => $data){
                        echo "<td>$data</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        ?>
    </body>
</html>