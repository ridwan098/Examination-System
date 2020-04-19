<?php

require("../global/util.php");
require("../global/db.php");

$NUM_STUDENTS_DISPLAY = 5;

session_start();

if (!isSessionLoggedIn() || !isset($_GET['examid'])) {
    header("Location: ../index.html");
}

$examid = $_GET['examid'];

$db = new Class_DB($servername, $username, $password);
$db->connectToDb($dbname);

$result = $db->executeQuery("SELECT * FROM Exams WHERE id=?", [$examid]);
$exam = $result->fetch();
if (!$exam) {
    header("Location: ../examinerPage.html");
}

$result = $db->executeQuery("SELECT * FROM ExamQuestions eq WHERE examId=?", [$examid]);
$examq = [];
while ($row = $result->fetch()) {
    $examq[] = $row;
}

$result = $db->executeQuery("SELECT * FROM StudentExamRelation ser, Users u WHERE ser.examId=? AND u.id=ser.userId", [$examid]);
$students = [];
while ($row = $result->fetch()){
    $students[] = $row;
}

?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editing paper....</title>
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
                    <li><a href="../index.html">Home</a></li>
                    <li id='modalBtn'><a>Account Info</a></li>
                    <li id='logout'><a href='../logIn.html'><span class="glyphicon glyphicon-log-in"></span> Sign Out</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>


    <div class="jumbotron">
        <div class="container text-center">
            <h1>Previous Paper</h1>
            <p id='username'>On this page, you can change details of a paper: </p>
        </div>
    </div>

    <div class="container-fluid text-center">
        <div class="row content">
            <div class="col-sm-2 sidenav">
                <h4> </h4>

                <p><a href="editPaper.php">Back</a></p>
            </div>
            <div class="col-sm-8 text-left">
                <h1>Welcome</h1>
                <p>Here you will be able to edit previously written questions.</p>
                <hr>
                <h3>Paper Info</h3>
                <?php
                echo "<h5>Subject: {$exam['subject']}</h5>";
                echo "<h5>Start Time: " . date("d/m/Y H:i", $exam['date']) . "</h5>";
                echo "<h5>Duration: " . gmdate("H:i", $exam['timerLength']) . "</h5>";
                ?>
                <form action="createPaper.php" method="get">
                    <input type="hidden" name="examid" value=<?php echo "'$examid'"; ?>>
                    <button class='btn'>Edit Paper Info</button>
                </form>
                <hr>
                <h3>Students</h3>
                <div id='studentTable' <?php echo 'style="height:' . min(50 + (sizeof($students) * 50), 300) . 'px;overflow:auto;"'; ?> >
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th>Student Name</th>
                            <th>Student Email</th>
                            <th></th>
                        </tr>
                        <?php 
                            for ($i = 0; $i < sizeof($students); $i++){
                                echo "<tr id=\"unmarked$i\" class=\"paperRow\" >";
                                echo "<td>{$students[$i]['name']}</td>";
                                echo "<td>{$students[$i]['email']}</td>";
                                echo "<td><button onclick='removeStudent(\"studentTable\", \"unmarked$i\", $examid, {$students[$i]['id']});' class='btn btn-sm btn-danger' style='height:100%'>Remove</button></td>";
                                echo "</tr>";
                            }
                        ?>
                    </table>
                </div>
                <form action="addingStudent.php" method="get">
                    <input type="hidden" name="examid" value=<?php echo "'$examid'"; ?>>
                    <button class="btn">Add Student to Exam</button>
                </form>
                <hr>
                <h3>Questions:</h3>
                <p>Please select the question you wish to edit:</p>
                <form action="multipleCQ.php" method="get">
                    <input type="hidden" name="examid" <?php echo "value='$examid'"; ?>>
                    <?php
                    $i = 1;
                    foreach ($examq as $q) {
                        echo '<input type="radio" id="mcq' . $i . '" name="qid" value="' . $q['examqId'] . '" required>';
                        echo '<label for="mcq' . $i . '">Q' . $i . ': ' . $q['question'] . '</label><br>';
                        $i++;
                    }
                    ?>
                    <button class='btn'>Edit Question</button><br />
                </form>
                <br>
                <form action="multipleCQ.php" method="get">
                    <input type="hidden" name="examid" value=<?php echo "'$examid'"; ?>>
                    <button class='btn'>Add New Question</button>
                </form>
                <hr>
            </div>
            <div class="col-sm-2 sidenav">

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
        let numStudents = <?php echo sizeof($students); ?>;

        function removeStudent(table, row, examId, studentId){
            var post = "examid=" + encodeURIComponent(examId) + "&userid=" + encodeURIComponent(studentId);

            var xhttp = new XMLHttpRequest();
            xhttp.open("POST", "removestudent.php", false);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(post);

            if (xhttp.responseText == 1){
                var element = document.getElementById(row);
                element.parentNode.removeChild(element);
                numStudents--;
                var table = document.getElementById(table);
                table.style.height = Math.min(50 + (numStudents * 50), 300) + "px";
            }
            else{
                alert("Failed to remove student");
            }
        }
    </script>

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
        db.settings({
            timestampsInSnapshots: true
        });
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
                console.log('User logged in: ', user)
                setupUI(user);
            } else {
                console.log('User logged out');
                location.replace('../index.html');
            }
        });


        const setupUI = (user) => {
            //<div>password: ${doc.data().password} </div>
            if (user) {
                //acount info 
                db.collection('users').doc(user.uid).get().then(doc => {
                    const name = `<span>${doc.data().username} </span>`;
                    const html = `
                <div>email: ${user.email} </div>
                <div>username: ${doc.data().username} </div>
                <div>user level: ${doc.data().userLevel} </div>
                `;
                    document.getElementById('accountDetails').innerHTML += html;
                    document.getElementById('username').innerHTML += name;
                });

            } else {
                accountDetails.innerHtml = '';
                document.getElementById('username').innerHTML

            }
        }

        // Get the modal
        var modal = document.getElementById("myModal");
        // Get the button that opens the modal
        var btn = document.getElementById("modalBtn");
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        btn.onclick = function() {
            modal.style.display = "block";
        }
        span.onclick = function() {
            modal.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>

</html>