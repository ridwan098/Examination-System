<?php
    require("global/db.php");
    require("global/util.php");

    session_start();

    if (!isSessionLoggedIn()){
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
        .exam-content{
            position: fixed;
            left:450px;   
            text-align: left;
            
        }
        .examsClass{
            padding-left:2%;
            width: 700px;
            font-size: 20px;
        }
        .fontSizeClass{
            position: fixed;
            top: 60px;
            left: 950px;
            text-align: center;
            font-size: 20px;
            width: 300px;
            border: solid 1px black;
            border-radius: 10px;
        }
        .fontButtonClass{
            background: transparent;
            border-radius: 10px;
            border: solid 0px transparent;
            text-decoration: underline;
            outline: none;
        }
        .normalSizedButton{
            font-size: 20px;
        }
        .largeSizedButton{
            font-size: 25px;
        }
        .extraSizedButton{
            font-size: 30px;
        }
        h3{
            font-size: 30px;
        }
       /* #examiners-id{
            position:fixed;
            top:100px;
            left:20px;
            width:auto; 
            border:solid 1px black; 
            border-radius: 10px;
            font-size:20px;
        }*/
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

    <?php
        if (sizeof($exams) == 0){
            $examDetails = "<p>No exams to show</p>";
            $welcome = "You have 0 exams to attempt!";
        }
        else{
            $latest = 0;
            $examDetails = "";
            foreach ($exams as $row){
                $examDetails .= "<p><a href=\"studentPageResource/startExam.php?examid={$row['id']}\">{$row['subject']}</a> Due: " . date("d/m/Y H:i:s", $row['date']) . " (Examiner: {$row['name']})</p>";
                if ($row['date'] > $latest)
                    $latest = $row['date'];
            }
            $welcome = "You have " . sizeof($exams) . " exam(s) to attempt by " . date("d/m/Y", $latest);
        }
    ?>
    <div class="jumbotron">
        <div class="container text-center">
            <h1 id='username'>Welcome </h1>
            <p id="welcomeMessage"><?php echo $welcome; ?></p>
        </div>
    </div>
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
                
                <!--changing font size-->
                <div class="fontSizeClass">
                    <h3>Font Size:</h3>
                    <button class="normalSizedButton fontButtonClass" onclick="changeFontSize('normal')">Normal</button>
                    <button class="largeSizedButton fontButtonClass" onclick="changeFontSize('large')">Large</button>
                    <button class="extraSizedButton fontButtonClass" onclick="changeFontSize('extra')">Extra Large</button>
                </div>

    <!--Notice dropbox-->
<!-- <div id="C-O-C-button">
        <h4>Exam Code of Conduct</h4>
        This can be changed, remember to ask if it should come from examiner or have just one form of it.
        <p>Possession of unauthorised material at any time when under examination conditions is an
            assessment offence and can lead to expulsion from the institution. Check now to ensure you do not have
            any notes, mobile phones or unauthorised electronic devices on your person. If you do, raise your
            hand and give them to an invigilator immediately. It is also an offence to have any writing of any
            kind on your person, including on your body. If you are found to have hidden unauthorised material
            elsewhere, including toilets and cloakrooms it will be treated as being found in your possession.
            Unauthorised material found on your mobile phone or other electronic device will be considered
            the same as being in possession of paper notes. A mobile phone that causes a disruption in the
            exam is also an assessment offence.</p>
    </div>-->
    <!--Examiners coming from DB-->
    <!--<div id="examiners-id">
        <p><b>Examiners: </b></p>
    </div>-->

    <!-- The Modal -->
    <div id="myModal" class="modal">

        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Here are your account details:</h3>
            <p id='accountDetails'></p>
        </div>

    </div>


   <!-- <div id="C-O-C-button">
        <a href="#C-O-C">Code of Conduct</a>
    </div>-->

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
    <script name="font">
        function changeFontSize(fontSize){
              examsId = document.getElementsByClassName("examsClass")[0];
              fontSizeClass = document.getElementsByClassName("fontSizeClass")[0];
              //examinersId = document.getElementById("examiners-id");
              normal = 20;
              large = 25;
              extra = 30;
          if(fontSize=="normal"){
                examsId.style.fontSize = normal;
                fontSizeClass.style.fontSize = normal;
              //  examinersId.style.fontSize = normal;
          }else if(fontSize=="large"){
                examsId.style.fontSize = large;
                fontSizeClass.style.fontSize = large;
              //  examinersId.style.fontSize = large;
          }else if(fontSize=="extra"){
                examsId.style.fontSize = extra;
                fontSizeClass.style.fontSize = extra;
              //  examinersId.style.fontSize = extra;
          }
        }
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
    </script>

</body>

</html>
