<?php
    require("db.php");

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

    // Execute query
    $sql = "SELECT * FROM Exams WHERE isMcq=1";
    $result = $conn->query($sql);
    $exams = [];
    while ($row = $result->fetch()){
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
        #examButton{
            border-radius: 20px;
            text-align: center;
            font-size: 20px;
            width: 120px;
            height: 40px;
            outline: none;
        }
        #examModalId{
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
        .examModal-content{
            background-color: #28322C;
            color: white;
            text-align: justify;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .welcome-message{
            position: relative;
            left: 220px;
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
                    <li id='logout'><a href="index.html">Home</a></li>
                    <li id='modalBtn'><a>Account Info</a></li>
                    <li id='logout'><a href='logIn.html'><span class="glyphicon glyphicon-log-in"></span> Sign Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="jumbotron">
        <div class="container text-center">
            <h1 id='username'>Welcome </h1>
        </div>
    </div>

    <?php 
        $latest = 0;
        $examDetails = "";
        foreach ($exams as $row){
            $examDetails .= "<p id='examDetails'><a href=\"studentPageResource/startExam.php?examid={$row['id']}\">{$row['subject']}</a> Due: " . date("d/m/Y H:i:s", $row['date']) . "</p>";
            if ($row['date'] > $latest)
                $latest = $row['date'];
        }
    ?>

    <div class="container-fluid text-center">
        <div class="row content">
            
            <div class="col-sm-8 text-left welcome-message">
                <p id="welcomeMessage"><?php echo "You have " . sizeof($exams) . " exams to attempt by " . date("d/m/Y", $latest) . "." ?></p>
                <hr>
                <!--show tests button-->
                <button id="examButton">Your Tests</button>
               
                <div id="examModalId">

                    <!-- Exam modal content -->
                    <div class="examModal-content">
                        <span class="close">&times;</span>
                        <h3>Here are your exam details:</h3>
                        <!--FOR ALEX Link names will come from examiner DB along with Due dates-->
                        <?php echo $examDetails; ?>
                    </div>
                </div>
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
        //listen for the auth status of user (whether they're signed in or out)
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
                });
                
            }
            else {
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
        
        var checkExams = document.getElementById("examButton");
        var examModal = document.getElementById('examModalId');
        checkExams.onclick = function(){
            examModal.style.display = "block";
        }
        span.onclick = function () {
            examModal.style.display = "none";
        }
        /*window.onclick = function (event) {
            if (event.target == examModal) {
                examModal.style.display = "none";
            }
        }*/

    
    </script>

</body>

</html>