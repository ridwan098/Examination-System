<?php
    require("../global/util.php");
    require("../global/db.php");

    session_start();

    if (!isSessionLoggedIn()) {
        header("Location: index.html");
    }
    if (!isset($_GET['examid'])){
        header("Location: ../examinerPage.html");
    }
    $examid = $_GET['examid'];

    $numFakeAnswers = 0;

    $editing = false;
    if (isset($_GET['qid'])) {
        $editing = true;
        $qid = $_GET['qid'];

        $db = new Class_DB($servername, $username, $password);
        $db->connectToDb($dbname);

        $sql = "SELECT * FROM ExamQuestions eq, McqQuestion mcq
                WHERE eq.examqId=?
                AND eq.examqId=mcq.examqId";
        $result = $db->executeQuery($sql, [$qid]);
        $question = $result->fetch();
        if (!$question){
            header("Location: ../examinerPage.html");
        }
    }
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
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
                    <li id='logout'><a href="../index.html">Home</a></li>
                    <li id='modalBtn'><a>Account Info</a></li>
                    <li id='logout'><a href='../logIn.html'><span class="glyphicon glyphicon-log-in"></span> Sign Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid text-center">
        <div class="row content">
            <div class="col-sm-2 sidenav">
                <p><a href="#">Link</a></p>
                <p><a href="#">Link</a></p>
                <p><a href="#">Link</a></p>
            </div>
            <div class="col-sm-8 text-left">
                <h1>Multiple Choice Question</h1>
                <form onsubmit="postForm(this, 'question', questionPosted); return false;" action="addquestion.php" method="post" id='question'>
                    <?php
                        if ($editing){
                            echo '<input type="hidden" name="qid" value="'.$qid.'">';
                        }
                    ?>
                    <input type="hidden" name="examid" value=<?php echo '"' . $examid . '"'; ?> >
                    <input type="hidden" name="type" value=1 >
                    <h5>Please enter a multiple choice question here along with the correct and fake answers.</h5>
                    <?php

                        echo '<textarea name="question" type="input" placeholder="Question" class="input" required>';
                        if ($editing){
                            echo $question['question'];
                        }
                        echo '</textarea>';
                    ?>
                    <h5>Correct Answer:</h5>
                    <input name="answer" form="question" class='input'
                        placeholder='Enter answer here...'
                        <?php if ($editing) { echo 'value="' . $question['answer'] . '"'; } ?>
                        required
                    >
                    <h5>Other Answers:</h5>
                    <div id='fakeAnswers'>
                        <?php 
                            if ($editing){
                                $answers = explode("\0", $question['fakeOptions']);
                                foreach ($answers as $a){
                                    if ($a == "") continue;
                                    $numFakeAnswers++;
                                    echo '<input name="fakeAnswer' . $numFakeAnswers . '" form="question" class="input" placeholder="Enter answer here..." value="' . $a . '">';
                                }
                            }
                            else{
                                echo '<input name="fakeAnswer1" form="question" class="input" placeholder="Enter answer here..." required>';
                            }
                        ?>
                    </div>
                    <button type="button" onclick='addFormInput("fakeAnswers")' class='btn'>Add Another Answer</button>
                    <h5>Marks:</h5><input type="number" name="marks" form="question" class='input'
                        placeholder='Enter marks here...' 
                        <?php if ($editing) { echo 'value="' . $question['maxMarks'] . '"'; } ?>
                        required>
                    <button type="submit" class='btn'>
                        <?php
                            if ($editing){
                                echo "Save Changes";
                            }
                            else{
                                echo "Submit Question";
                            }
                    ?>
                    </button><br/>
                    <hr />
                </form>

                <?php
                    if (!$editing){
                        echo '<p id="addtext" style="visibility: hidden">Question successfully added. Continue adding more questions or <a href="addingStudent.php?examid=' . $examid . '">add students to the exam</a>.</p>';
                    }
                ?>
                <hr>
            </div>
            <div class="col-sm-2 sidenav">
                <div class="well">
                    <p>ADS</p>
                </div>
                <div class="well">
                    <p>ADS</p>
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

    <script>
        let fakeAnswers = <?php echo $numFakeAnswers; ?>;
        let editing = <?php print($editing ? "true" : "false"); ?>;

        function addFormInput(id){
            fakeAnswers++;
            var node = document.createElement("input");
            node.setAttribute("name", "fakeAnswer" + fakeAnswers);
            node.setAttribute("form", "question");
            node.setAttribute("class", "input");
            node.setAttribute("placeholder", "Enter answer here...");

            var div = document.getElementById(id);
            div.appendChild(document.createElement("br"));
            div.appendChild(node);
        }

        var questionPosted = function (caller, response) {
            
            caller.disabled = false;
            if (response == 1){
                alert("Success!");
                if (!editing){
                    var link = document.getElementById("addtext");
                    link.style ="";
                }
                else{
                    document.location = <?php echo "'editquestion.php?examid=$examid'"; ?>;
                }
            }
            else{
                alert("Failed to add question");
            }
        }

        function postForm(caller, formId, callback){
            caller.disabled = true;

            var form = document.getElementById(formId);
            var inputs = Array.from(form.elements).filter(e => e.getAttribute("name"));

            // compile data
            var data = "";
            for (var i = 0; i < inputs.length; i++){

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

            }
            else {
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
    </script>

</body>

</html>