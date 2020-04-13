<?php

    require("../global/db.php");
    
    $NUM_PAPERS_DISPLAY = 5;

    $db = new Class_DB($servername, $username, $password);
    $db->connectToDb("higherexam");

    // query to get list of unmarked exams
    $sql = "SELECT * FROM FinishedExam fe, Exams e, Users u
            WHERE fe.marked = 0
            AND fe.examId = e.id 
            AND fe.userId = u.id";
    $result = $db->executeQuery($sql);
    $unmarkedExams = [];
    while($row = $result->fetch()) {
        $unmarkedExams[] = $row;
    }

    // query to get list of marked exams
    $sql = "SELECT fe.*, e.*,u.*,(
                SELECT (SUM(cq.markReceived) / SUM(eq.maxMarks))*100 AS totalMarks
                FROM ExamQuestions eq, CompletedQuestions cq
                WHERE eq.examId = fe.examId
                AND cq.finExamId = fe.finishedId
                AND cq.examqId = eq.examqId) AS totalMarks 
            FROM FinishedExam fe, Exams e, Users u
            WHERE fe.marked = 1
            AND fe.examId = e.id
            AND fe.userId = u.id";
    $result = $db->executeQuery($sql);
    $markedExams = [];
    while ($row = $result->fetch()){
        $markedExams[] = $row;
    }

    // get total number of marks for paper
    /*$sql = "SELECT fe.finishedId, (
                SELECT (SUM(cq.markReceived) / SUM(eq.maxMarks))*100 AS totalMarks
                FROM ExamQuestions eq, CompletedQuestions cq
                WHERE eq.examId = fe.examId
                AND cq.finExamId = fe.finishedId
                AND cq.examqId = eq.examqId) AS totalMarks
            FROM FinishedExam fe
            WHERE fe.marked=1";*/
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grader Page</title>
    <link rel="stylesheet" type="text/css" href="../logIn.css">
    <script src="https://www.gstatic.com/firebasejs/7.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.10.0/firebase-firestore.js"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        /* Remove the navbar's default margin-bottom and rounded borders */
        .navbar {
            margin-bottom: 0;
            border-radius: 0;
            background-color: #28322C;
            border-color: #28322C;
        }

        /* The Modal (background) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        /* Modal Content */
        .modal-content {
            background-color: #28322C;
            color: white;
            text-align: justify;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        /* The Close Button */
        .close {
            color: lightgreen;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .paperRow:hover{
            cursor: pointer;
        }

        #adminBtn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 99;
            font-size: 18px;
            border: none;
            outline: none;
            background-color: grey;
            color: white;
            cursor: pointer;
            padding: 15px;
            border-radius: 4px;
        }

        #adminBtn:hover {
            background-color: #555;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="../index.html">Higher Exam</a>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav navbar-right">
                    <li id='logout'><a href="../index.html">Home</a></li>
                    <li id='modalBtn'><a>Account Info</a></li>
                    <li id='logout'><a href='../logIn.html'><span class="glyphicon glyphicon-log-in"></span> Sign Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="jumbotron">
        <div class="container text-center">
            <h1>Grader Page</h1>
            <p id='username'>Welcome </p>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row content">
            <div id="sidenav" class="col-sm-3 sidenav">
                <h4>Page Navigation</h4>
                <ul class="nav nav-pills nav-stacked">
                    <li class="active"><a href="#welcome">Welcome</a></li>
                    <li><a href="#mark">Pending Papers</a></li>
                    <li><a href="#archive">Paper Archive</a></li>
                </ul><br>
            </div>

            <div class="col-sm-8">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default text-left">
                            <div class="panel-body">
                                <!-- Text here -->
                                <h4 id='welcome'>Welcome</h4>
                                <p>This is the grader page. This page can be used to view and grade any papers that are still pending,
                                    or can be used to view previously marked papers in the paper archive.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>

                <div id='mark'>
                    <h3>Exams ready to be marked</h3>
                    <p>
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th>Subject</th>
                                <th>Student Name</th>
                                <th>Student Email</th>
                            </tr>
                            <?php 
                                for ($i = 0; $i < sizeof($unmarkedExams); $i++){
                                    echo "<tr id=\"unmarked$i\" class=\"paperRow\" onclick=\"window.location='gradePaper.php?id={$unmarkedExams[$i]['finishedId']}'\"";
                                    if ($i >= $NUM_PAPERS_DISPLAY){
                                        echo "style='display:none;' >";
                                    }
                                    else{
                                        echo ">";
                                    }
                                    echo "<td>{$unmarkedExams[$i]['subject']}</td>";
                                    echo "<td>{$unmarkedExams[$i]['name']}</td>";
                                    echo "<td>{$unmarkedExams[$i]['email']}</td>";
                                    echo "</tr>";
                                }
                            ?>
                        </table>
                    </p>
                    <button onclick="toggleHiddenRows(this, 'unmarked');" id="previous" type="button" class="btn btn-primary btn-sm">
                                    Show More
                    </button>
                </div>
                <hr>
                <div id='archive'>
                    <h3>Marked exams</h3>
                    <p>
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th>Subject</th>
                            <th>Student Name</th>
                            <th>Student Email</th>
                            <th>Grade (%)</th>
                        </tr>
                        <?php 
                            for ($i = 0; $i < sizeof($markedExams); $i++){

                                // format number properly for printing
                                $totalMarks = number_format($markedExams[$i]['totalMarks'], 2, '.', '');

                                echo "<tr id=\"marked$i\" class=\"paperRow\" onclick=\"window.location='gradePaper.php?id={$markedExams[$i]['finishedId']}'\" ";
                                if ($i >= $NUM_PAPERS_DISPLAY){
                                    echo "style='display:none;' >";
                                }
                                else{
                                    echo ">";
                                }
                                echo "<td>{$markedExams[$i]['subject']}</td>";
                                echo "<td>{$markedExams[$i]['name']}</td>";
                                echo "<td>{$markedExams[$i]['email']}</td>";
                                echo "<td>{$totalMarks}</td>";
                                echo "</tr>";
                            }
                        ?>
                    </table>
                    </p>
                    <button onclick="toggleHiddenRows(this,'marked');" id="previous" type="button" class="btn btn-primary btn-sm">
                                    Show More
                    </button>
                </div>
                <hr>
            </div>
        </div>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">

        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Here are your account details:</h3>
            <p id='accountDetails'></p>
        </div>

    </div>

    <script>
        function toggleHiddenRows(btn, id){
            for (var i = <?php echo $NUM_PAPERS_DISPLAY; ?>; ; i++){
                var el = document.getElementById(id + "" + i);
                if (el){
                    if (el.style.display == "none"){
                        el.style.display = "";
                    }
                    else{
                        el.style.display = "none";
                    }
                }
                else{
                    break;
                }
            }

            if (btn.innerHTML.includes("Show More")){
                btn.innerHTML = "Show Less";
            }
            else{
                btn.innerHTML = "Show More";
            }
        }
    </script>

    <button class='adminOnly' onclick="returnTopage()" id="adminBtn" title="Go to top">Admin Page</button>

    <script src="https://www.gstatic.com/firebasejs/5.6.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.6.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.6.0/firebase-firestore.js"></script>
    <script>
        // Your web app's Firebase configuration
        var firebaseConfig = {
            apiKey: "AIzaSyDiUrWvr_XfF3o-YVinV_D9JuKXJpWbPaI",
            authDomain: "examination-system-f53f3.firebaseapp.com",
            databaseURL: "https://examination-system-f53f3.firebaseio.com",
            projectId: "examination-system-f53f3",
 
        };
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);

        // maek auth and firestore references
        const auth = firebase.auth();
        const db = firebase.firestore();

        //update firestore settings
        db.settings({ timestampsInSnapshots: true });



    </script>

    <script>
        //log out 
        const logout = document.querySelector('#logout');
        logout.addEventListener('click', (e) => {
            e.preventDefault();
            auth.signOut().then(() => {
                alert('User signed out')
            })
        });
        const accountDetails = document.querySelector('.accountDetails');
        // for some reason, user is logged in from the start
        //listen for the auth status of user (whether theyre signed in or out)
        auth.onAuthStateChanged(user => {
            if (user) {
                user.getIdTokenResult().then(idTokenResult => {
                    user.admin = idTokenResult.claims.admin;
                    setupUI(user);
                })
                console.log('User logged in: ', user)

            } else {
                console.log('User logged out');
                location.replace('../index.html');
            }
        });

        const adminItems = document.querySelectorAll('.adminOnly');
        const setupUI = (user) => {
            //<div>password: ${doc.data().password} </div>
            if (user) {
                if (user.admin) {
                    //document.getElementById("adminBtn").style.display = "block";
                    //adminItems[1].style.display = 'block';
                    for (i = 0; i < adminItems.length; i++) {
                        adminItems[i].style.display = 'block';
                    }

                }
                //acount info 
                db.collection('users').doc(user.uid).get().then(doc => {
                    const name = `<span>${doc.data().username}</span>`;
                    const html = `
                <div>email: ${user.email} </div>
                <div>username: ${doc.data().username} </div>
                <div>user level: ${doc.data().userLevel} </div>
                `;
                    document.getElementById('accountDetails').innerHTML += html;
                    document.getElementById('username').innerHTML += name;
                    if (doc.data().userLevel == "admin"){
                        for (i = 0; i < adminItems.length; i++) {
                            adminItems[i].style.display = 'block';
                        }   
                    }
                });

            }
            else {
                for (i = 0; i < adminItems.length; i++) {
                    adminItems[i].style.display = 'none';
                }
                accountDetails.innerHtml = '';
                document.getElementById('username').innerHTML;
            }
        }

        // Get the modal
        var modal = document.getElementById("myModal");
        // Get the button that opens the modal
        var btn = document.getElementById("modalBtn");
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        btn.onclick = function () {
            modal.style.display = "block";
        }
        span.onclick = function () {
            modal.style.display = "none";
        }
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        //when admin returns to page
        function returnTopage() {
            location.replace('../adminPage.html');
        }
    </script>

</body>

</html>