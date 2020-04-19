<?php

require("../global/util.php");
require("../global/db.php");

session_start();

if (!isSessionLoggedIn()) {
    header("Location: ../index.html");
}

$userid = $_SESSION['userid'];

$db = new Class_DB($servername, $username, $password);
$db->connectToDb($dbname);

$sql = "SELECT e.*, ( 
                SELECT (COUNT(*) > 0) as warning
                FROM FinishedExam fe
                WHERE fe.examId=e.id) as warning
            FROM Exams e WHERE e.examinerId=?";

$result = $db->executeQuery($sql, [$userid]);
$exams = [];
while ($row = $result->fetch()) {
    $exams[] = $row;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Paper</title>
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

        .buttton {
            border: 1px;
            height: 75px;
            width: 200px;
            cursor: pointer;
            text-align: center;

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
            <h1>Remove Paper</h1>
            <p id='username'>Here, you can edit paper: </p>
        </div>
    </div>

    <div class="container-fluid text-center">
        <div class="row content">
            <div class="col-sm-2 sidenav">
                <h4> </h4>

                <p><a href="../examinerPage.html">Back to examiner page</a></p>

            </div>
            <div class="col-sm-8 text-left">
                <h1>Welcome</h1>
                <p>Here you will be able to delete previously written papers from the database.</p>
                <hr>
                <h3>Previous papers:</h3>

                <p>Please select the paper you wish to delete:</p>
                <?php
                if (sizeof($exams) > 0) {
                ?>
                    <form onsubmit="postForm(this, postComplete); return false;" action="deleteexam.php" method="post">
                        <?php
                        foreach ($exams as $exam) {
                            echo '<input type="radio" id="mcq" name="examid" value="' . $exam['id'] . '" required> ' . $exam['subject'];
                            // echo '<label for="mcq">' . $exam['subject'];
                            echo '</label><br>';
                        }
                        ?>
                        <br>
                        <button type="submit" class='btn'>Remove Paper</button>
                    </form>
                <?php
                } else {
                    echo "None to show";
                }
                ?>
            </div>
            <div class="col-sm-2 sidenav">

            </div>
        </div>
    </div>

    <script>
        <?php
        echo "let warnings = {};";
        foreach ($exams as $exam) {
            echo "warnings[" . $exam['id'] . "] = " . $exam['warning'] . ";";
        }
        ?>

        var postComplete = function(caller, response) {
            if (response == 1) {
                alert("Paper removed successfully!");
                location.reload();
            } else {
                alert("Failed to remove paper: " + response);
            }
        }

        function postForm(caller, callback) {
            caller.disabled = true;

            var form = caller;
            var inputs = Array.from(form.elements).filter(e => e.getAttribute("name"));

            // compile data
            var data = "";
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].name == "examid" && warnings[inputs[i].value]) {
                    if (!confirm("WARNING: This exam has been sat by at least one student, deleting this exam will also remove their answers and all other associated data. Would you like to continue?")) {
                        caller.disabled = false;
                        return;
                    }
                }
                data += inputs[i].name + "=" + encodeURIComponent(inputs[i].value) + "&";
            }

            var xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    callback(caller, xhr.response);
                }
            }
            xhr.open("POST", form.action, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(data);
        }
    </script>




    <!-- The Modal -->
    <div id="myModal" class="modal">

        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Here are your account details:</h3>
            <p id='accountDetails'></p>
        </div>

    </div>

    <!---      From here below -->

    <footer class="container-fluid text-center adminOnly" style='display:none;'>
        <p>Admin Page</p>
    </footer>

    <button class='adminOnly' onclick="returnTopage()" id="adminBtn" title="Go to top">Admin Page</button>

    <script src="https://www.gstatic.com/firebasejs/5.6.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.6.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.6.0/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.6.0/firebase-functions.js"></script>
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
        const functions = firebase.functions();

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
                user.getIdTokenResult().then(idTokenResult => {
                    user.admin = idTokenResult.claims.admin;
                    setupUI(user);
                })
                console.log('User logged in: ', user);

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
                    if (doc.data().userLevel == "admin") {
                        for (i = 0; i < adminItems.length; i++) {
                            adminItems[i].style.display = 'block';
                        }
                    }
                });

            } else {
                for (i = 0; i < adminItems.length; i++) {
                    adminItems[i].style.display = 'none';
                }
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


        //when admin returns to page
        function returnTopage() {
            location.replace('../adminPage.html');
        }
    </script>

</body>

</html>