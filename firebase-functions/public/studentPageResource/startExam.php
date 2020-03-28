<?php

    require("../db.php");

    $examId = $_GET['examid'];

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

    // Execute query for exam data
    $query = "SELECT * FROM Exams e WHERE e.id = ?";
    $result = $conn->prepare($query);
    $result->execute([$examId]);
    $examData = $result->fetch();
    if (!$examData){
        die('failed to find exam');
    }

    // Execute query for question data
    $query = "SELECT * FROM ExamQuestions eq, McqQuestion mcq
            WHERE eq.examId = ?
            AND mcq.examqId = eq.examqId";
    $result = $conn->prepare($query);
    $result->execute([$examId]);
    $questionData = [];
    while ($row = $result->fetch()){
        $questionData[] = $row;
    }
    $conn = null;

?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Exam</title>
    <link rel="stylesheet" type="text/css" href="logIn.css">
    <script src="https://www.gstatic.com/firebasejs/7.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.10.0/firebase-firestore.js"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
    .fontSizeClass{
      position: relative;
      top: 50px;
      left: 800px;
      text-align: center;
      font-size: 20px;
      width: 400px;
    }
    .questions-form{
      position: relative;
      top:80px;
      left: 400px;
      font-size: 20px;
      width: 400px;
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
    .navbar {
      margin-bottom: 0;
      border-radius: 0;
      background-color: #28322C;
      border-color: #28322C;
    }
    #countdown{
      position:relative;
      top: -20px;
      left: 20px;
      font-size: 20px;
      margin-top: 0px;
    }
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

    <!--changing font size-->
    <div class="fontSizeClass">
      <h3>Font Size:</h3>
      <button class="normalSizedButton fontButtonClass" onclick="changeFontSize('normal')">Normal</button>
      <button class="largeSizedButton fontButtonClass" onclick="changeFontSize('large')">Large</button>
      <button class="extraSizedButton fontButtonClass" onclick="changeFontSize('extra')">Extra Large</button>
    </div>
    <!--timer for how long exam will last-->
    <p id="countdown"></p>
    <!--Questions and  will be passed onto this tag below 'FOR ALEX'-->
    <form class="questions-form" action="">
        <?php 
            $qnum = 1;

            // display html for each mcq question
            foreach ($questionData as $q){
                $answers = explode("\0", $q['fakeOptions']);
                $answers[] = $q['answer'];
                shuffle($answers);
                echo "
                <div class='questions-tag'>  
                    <strong>Question $qnum: {$q['question']}</strong><br>
                    <div class='answers' style='position:relative;left: 20px;'>";
                foreach ($answers as $a){
                    echo "
                        <input type='radio' id='$a' name='Q$qnum' value='$a'>
                        <span for='$a'>$a</span><br>";
                }
                echo "</div></div>";
                $qnum += 1;
            }
        ?>
      <input type="submit" value="Submit Exam"style="position:relative;top:20px;left: 150px; border-radius: 10px;">
    </form>

    

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
                // Mention number of exams to do and a little message for the student, NOTE: number and date variable will come from the examiner database
                    var welcomeMessage = "You have " + "[NUMBER VARIABLE]" + " to attempt by " + "[DATE VARIABLE]"+"." ;
                    document.getElementById('welcomeMessage').innerHTML = welcomeMessage;
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
    <script name="font">
      function changeFontSize(fontSize){
            fontClass = document.getElementsByClassName("fontSizeClass");
            questionsTag = document.getElementsByClassName("questions-tag");
        if(fontSize=="normal"){
            fontClass[0].style.fontSize = "20px";
            questionsTag[0].style.fontSize = "20px";
            countdown.style.fontSize = "20px";
        }else if(fontSize=="large"){
            fontClass[0].style.fontSize = "25px";
            questionsTag[0].style.fontSize = "25px";
            countdown.style.fontSize = "25px";
        }else if(fontSize=="extra"){
            fontClass[0].style.fontSize = "30px";
            questionsTag[0].style.fontSize = "30px";
            countdown.style.fontSize = "30px";
        }
      }
  </script>
  <script>
    // The date and time we're counting down to will come from the examiner as well, and replace the figures in the variable "countdownDate"
    var countDownDate = new Date().getTime() + <?php echo $examData['timerLength'] * 1000; ?>;

    // Update the count down every 1 second
    var x = setInterval(function() {

        // Get today's date and time
        var now = new Date().getTime();

        // Find the distance between now and the count down date
        var examOver = countDownDate - now;

        // Time calculations for minutes and seconds

        var hours = Math.floor((examOver % (1000 * 60 * 60 * 24) / (1000 * 60 * 60)));
        var minutes = Math.floor((examOver % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((examOver % (1000 * 60)) / 1000);

        // Output the result in an element with id="countdown"
        var countdown = document.getElementById("countdown");
        countdown.innerHTML = "<b>Time remaining: " + hours + "h " + minutes + "m " + seconds + "s </b>";


        // If the count down is over, write some text 
        if (examOver < 0) {
        clearInterval(x);
        document.getElementById("countdown").innerHTML = "EXPIRED";
        }
    }, 500);
  </script>
</body>

</html>
