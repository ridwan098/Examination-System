<?php

    require('../global/db.php');
    require('../global/util.php');

    $examid = getPostArg('examid');
    $userid = getPostArg('userid');

    $db = new Class_DB($servername,$username,$password);
    $db->connectToDb($dbname);
    $result = $db->executeQuery("DELETE FROM StudentExamRelation WHERE examId=? AND userId=?", [$examid, $userid]);
    if ($result->rowCount() > 0){
        echo 1;
    }
    else{
        echo 0;
    }

?>