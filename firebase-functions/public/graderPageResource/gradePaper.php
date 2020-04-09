<?php
    require("../db.php");

    $db = new Class_DB($servername,$username, $password);
    $db->connectToDb("higherexam");
    // Execute query
    $sql = "select * from FinishedExam fe, Exams e, Student s, Users u
    where fe.finishedId = ?
    AND fe.examId = e.id
    and fe.studentId = s.studentId
    and s.userId = u.id;";
    $result = $db->executeQuery($sql, [$_GET['id']]);
    $metaRow = $result->fetch();

    $sql = "select * from CompletedQuestions cq, ExamQuestions eq
    where cq.finExamId = ?
    and cq.examqId = eq.examqId";
    $result = $db->executeQuery($sql, [$_GET['id']]);
    $examqs = [];
    while($row = $result->fetch()){
        $examqs[] = $row;
    }
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

        .form-inline {
            align-items: center;
        }

        /* Add some margins for each label */
        .form-inline label {
            margin: 5px 10px 5px 0;
        }

        /* Style the input fields */
        .form-inline input {
            vertical-align: middle;
            margin: 5px 10px 5px 0;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
        }

        /* Style the submit button */
        .form-inline button {
            padding: 10px 20px;
            background-color: dodgerblue;
            border: 1px solid #ddd;
            color: white;
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
        <center>
            <?php
                echo "<h1>{$metaRow['subject']}</h1>";
                echo "<h3>Candidate Name: {$metaRow['name']}</h3>";
                echo "<h3>Candidate ID: {$metaRow['studentId']}</h3>";
            ?>
        </center>
    </div>

    <iframe style="display:none" name="hidden-form"></iframe>
    <div class="container-fluid">
        <div class="row content">
            <div id="sidenav" class="col-sm-3 sidenav">
                <div>
                    <h4>Browse pages</h4>
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="graderPage.php">Return to main page</a></li>
                    </ul><br>
                </div>
                <hr>
                <h4>Options</h4>
                <div id="saveAllForm">
                    <div class="checkbox">
                        <label>
                            <input id="finalSave" type="checkbox" title="Used when all marking is complete. This will finalize all changes and move the exam to the paper archives." name="final" value="1">Finalize</input>
                        </label>
                    </div>
                    <div class="form-group">
                        <button id="saveAll" class="btn btn-primary" onclick="saveAll(this)" type="submit">Save All</button>
                    </div>
                    <input type="hidden" name="examid" id="examid" value=<?php echo '"' . $_GET['id'] . '"'; ?> />
                </div>
            </div>
            <div class="col-sm-8">
                <div class="panel-group" id="accordion">
                    <?php

                        for ($i = 0; $i < sizeof($examqs); $i++){
                            echo '<div class="panel panel-default">
                                    <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$i.'">
                                        Question '. ($i+1). '</a>
                                    </h4>
                                    </div>
                                    <div id="collapse'.$i.'" class="panel-collapse collapse">
                                    <div class="panel-body">';
                            echo "<p><b>{$examqs[$i]['question']}</b></p>";
                            echo $examqs[$i]['answer'];
                            echo "<hr>";
                            $comment = htmlspecialchars($examqs[$i]['comment']);
                            echo "<form action=\"markQuestion.php\" target=\"hidden-form\" method=\"post\">
                                    <input type=\"hidden\" id=\"qid$i\" name=\"qid\" value=\"{$examqs[$i]['id']}\"/>
                                    <div class=\"form-group\">
                                        <label>Comments:</label>
                                        <textarea class=\"form-control\" autocomplete=\"off\" type=\"text\" id=\"comment$i\" name=\"comment\" placeholder=\"Type feedback here...\">$comment</textarea>
                                    </div>
                                    <div class=\"form-group\">
                                        <label>Marks (out of {$examqs[$i]['maxMarks']}):</label>
                                        <input class=\"form-control\" type=\"number\" min='0' max=\"{$examqs[$i]['maxMarks']}\" id=\"mark$i\" name=\"mark\" placeholder=\"Marks\" value=\"{$examqs[$i]['markReceived']}\"/>
                                    </div>
                                    <button type=\"submit\" class=\"btn btn-primary\">Save</button>
                                </form>";
                            echo '</div></div></div>';
                        }

                    ?>
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

    <button class='adminOnly' onclick="returnTopage()" id="adminBtn" title="Go to top">Admin Page</button>

    <script>
        var coll = document.getElementsByClassName("collapsible");
        var i;

        for (i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
    </script>

    <script>
        function saveAll(caller){

            caller.disabled = true;

            var finalize = document.getElementById("finalSave");
            var examid = document.getElementById("examid").value;

            // compile data
            var marks, comment, qid, i = 0;
            var data = "examid=" + examid + "&finalize=" + (finalize.checked ? "1" : "0") + "&";
            while ((marks = document.getElementById("mark" + i)) != null && 
            (comment = document.getElementById("comment" + i)) != null &&
            (qid = document.getElementById("qid" + i)) != null){
                data += "qid" + i + "=" + encodeURIComponent(qid.value);
                data += "&mark" + i + "=" + encodeURIComponent(marks.value);
                data += "&comment" + i + "=" + encodeURIComponent(comment.value) + "&";
                i++;
            }

            var xhr = new XMLHttpRequest();

            var postComplete = function (response) {
                if (response == 1)
                    alert("Save successfully completed!");
                else
                    alert("Failed to save changes");
                caller.disabled = false;
            }
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    postComplete(xhr.response);
                }
            }
            xhr.open("POST", "markQuestion.php", true);
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