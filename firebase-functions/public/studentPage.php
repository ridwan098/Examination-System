<?php
require("global/db.php");
require("global/util.php");

session_start();

if (!isSessionLoggedIn()) {
    header("Location: index.html");
}

// Connect to sql db
$db = new Class_DB($servername, $username, $password);
$db->connectToDb($dbname);

$time = time();

// Execute query
$sql = "SELECT e.*, u.name FROM StudentExamRelation ser, Exams e, Users u 
            WHERE ser.userId=? 
            AND e.examinerId = u.id
            AND ser.examId = e.id
            AND (e.date+e.timerLength) > ?";
$result = $db->executeQuery($sql, [$_SESSION['userid'], $time]);
$exams = [];
while ($row = $result->fetch()) {
    $exams[] = $row;
}

$conn = null;
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Page</title>
    <link rel="stylesheet" type="text/css" href="logIn.css">
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
                <a class="navbar-brand" href="index.html">Higher Exam</a>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="index.html">Home</a></li>
                    <li id='modalBtn'><a>Account Info</a></li>
                    <li id='logout'><a href='logIn.html'><span class="glyphicon glyphicon-log-in"></span> Sign Out</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <?php
    if (sizeof($exams) == 0) {
        $examDetails = "<p>No exams to show</p>";
        $welcome = "You have 0 exams to attempt!";
    } else {
        $latest = 0;
        $examDetails = "";
        foreach ($exams as $row) {
            $examDetails .= "<p><a href=\"studentPageResource/startExam.php?examid={$row['id']}\">{$row['subject']}</a> Due: " . date("d/m/Y H:i:s", $row['date']) . " (Examiner: {$row['name']})</p>";
            if ($row['date'] > $latest)
                $latest = $row['date'];
        }
        $welcome = "You have " . sizeof($exams) . " exam(s) to attempt by " . date("d/m/Y", $latest);
    }
    ?>
    <div class="jumbotron">
        <div class="container text-center">
            <h1>Student Page</h1>
            <p id='username'>Welcome </p>
        </div>
    </div>





    <div class="container-fluid text-center">
        <div class="row content">
            <div class="col-sm-2">
                <div class="well sidenav">
                    <div class="well">
                        <p id="profile"><a>My Profile</a></p>
                        <img src="avatar.png" class="img-circle" height="65" width="65" alt="Avatar">
                    </div>

                    <div class="well">
                        <a href="#">
                            <p>View Grades</p>
                        </a>
                    </div>

                </div>

                <div class="well">
                    <h4>Change Font Size</h4>

                    <!--changing font size-->
                    <div class="fontSizeClass">
                        <p><a href="#">Font sizes:</a></p>
                        <div class="well">
                            <span style="cursor: pointer;" class="label label-info" onclick="changeFontSize('normal')">Normal</span>
                        </div>
                        <div class="well">
                            <span style="cursor: pointer;" class="label label-warning" onclick="changeFontSize('large')">Medium</span>
                        </div>
                        <div class="well">
                            <span style="cursor: pointer;" class="label label-success" onclick="changeFontSize('extra')">Large</span>
                        </div>
                    </div>
                    <hr>
                </div>

            </div>




            <div class="col-sm-8 text-left">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="well">
                            <p>You are able to view exams on this page. To take an exam, simply click on the name of the course/subject and you will be directed to the exam screen. Each exam has a timer and will close once the timer has completed.</p>
                            <p>You are also able to change the font size of the text on the exam details section. Simply choose from: "Normal, Medium or Large" and your text size will be adjusted accordingly.</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default text-left">
                            <div class="panel-body">
                                <button type="button" class="btn btn-default btn-sm">
                                    Exams
                                </button>
                            </div>
                        </div>

                        <div class="panel panel-primary">
                            <div class="panel-heading">Exam Details</div>
                            <div class="panel-footer">
                                <!-- Exam content -->
                                <div class="exam-content">
                                    <h3>Here are your exam details:</h3>
                                    <div class='examsClass'>
                                        <!--FOR ALEX Link names will come from examiner DB along with Due dates-->
                                        <?php
                                        echo $examDetails;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="well">
                            <p>If no exams are displayed, this means that you have no exams to sit. However, if you suspect there to be an error, please contact your examiner or whoever is responsible for writing the paper.</p>
                        </div>
                    </div>
                </div>
            </div>





            <div class="col-sm-2">
                <div class="well sidenav">
                    <div class="thumbnail">
                        <p><strong>Upcoming Exams:</strong></p>
                        <!-- Amount of Exams -->
                        <p id="welcomeMessage"><?php echo $welcome; ?></p>

                        <br>
                        <button class="btn btn-danger">Days Left</button>
                    </div>
                    <div class="alert alert-success fade in">
                        <a href="#" class="close1" data-dismiss="alert" aria-label="close">×</a>
                        <p><strong>Attention!</strong></p>
                        It is important to complete exams before deadline. All exams have a deadline to be completed by.
                    </div>
                    <div class="well">
                        Notes:
                        <p><textarea style="width: 100%;"></textarea></p>

                        <p style="color:blue;" contenteditable="true" placeholder="write here there"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>








    <!-- The Modal for account details -->
    <div id="myModal" class="modal modal-dialog">

        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Here are your account details:</h3>
            <p id='accountDetails'></p>
        </div>

    </div>

    <!-- The Modal for account details -->
    <div id="myModal1" class="modal modal-dialog">

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
                    user.admin = idTokenResult.claims.admin
                    setupUI(user);
                })
                console.log('User logged in: ', user)

            } else {
                console.log('User logged out');
                location.replace('index.html');
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

        //modal for user info
        // Get the modal
        var modal = document.getElementById("myModal1");
        // Get the button that opens the modal
        var btn = document.getElementById("profile");
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



        //modal for user info
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


        //modal for delete user account
        // Get the modal
        var modal1 = document.getElementById("deleteUserModal");
        // Get the button that opens the modal
        var btn1 = document.getElementById("deleteUserBtn");
        // Get the <span> element that closes the modal
        var span1 = document.getElementsByClassName("close1")[0];
        btn1.onclick = function() {
            modal1.style.display = "block";
        }
        span1.onclick = function() {
            modal1.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal1) {
                modal1.style.display = "none";
            }
        }





        //add admin clous function
        const adminForm = document.getElementById('adminActions');
        adminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            var adminEmail = document.getElementById('adminEmail').value;
            var role = document.getElementById('userRole').value;
            db.collection('users').get().then((snapshot) => {
                snapshot.docs.forEach(doc => {

                    if (doc.data().email === adminEmail) {
                        if (role === 'admin') {
                            console.log('In admin')
                            const addAdminRole = functions.httpsCallable('addAdminRole');
                            addAdminRole({
                                email: adminEmail
                            }).then(result => {
                                console.log(result);
                            });
                        }
                        db.collection('users').doc(doc.data().userID).set({
                            email: doc.data().email,
                            password: doc.data().password,
                            userLevel: role,
                            username: doc.data().username,
                            userID: doc.data().userID
                        }).then(() => {
                            console.log(role)
                            document.getElementById('errorMessage').innerHTML = '<br />' + "Success! " + doc.data().email + " has been set to " + role + '<br />';
                        }).catch(err => {
                            document.getElementById('errorMessage').innerHTML = "There was an error signing you up: " + err.message + '<br />';
                        });
                    }

                })
            });
        });


        //when admin returns to page
        function returnTopage() {
            location.replace('adminPage.html');
        }
    </script>
    <!-- commrnt out the drop down <menu></menu>
make a function that chnages the vlaues on the database Only
once this is working, make an if statement and have thee existing code for admin
underneath the brach for if the select is admin -->

    <script name="font">
        function changeFontSize(fontSize) {
            examsId = document.getElementsByClassName("examsClass")[0];
            fontSizeClass = document.getElementsByClassName("fontSizeClass")[0];
            //examinersId = document.getElementById("examiners-id");
            normal = 20;
            large = 25;
            extra = 30;
            if (fontSize == "normal") {
                examsId.style.fontSize = normal;
                fontSizeClass.style.fontSize = normal;
                //  examinersId.style.fontSize = normal;
            } else if (fontSize == "large") {
                examsId.style.fontSize = large;
                fontSizeClass.style.fontSize = large;
                //  examinersId.style.fontSize = large;
            } else if (fontSize == "extra") {
                examsId.style.fontSize = extra;
                fontSizeClass.style.fontSize = extra;
                //  examinersId.style.fontSize = extra;
            }
        }
    </script>
</body>

</html>