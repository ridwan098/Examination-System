var firebaseConfig = {
    apiKey: "AIzaSyDiUrWvr_XfF3o-YVinV_D9JuKXJpWbPaI",
    authDomain: "examination-system-f53f3.firebaseapp.com",
    databaseURL: "https://examination-system-f53f3.firebaseio.com",
    projectId: "examination-system-f53f3",

};

//firebase.initializeApp(firebaseConfig);

//var messagesRef = firebase.database().ref('messages');


const userid = document.getElementById('idx')
const report = document.getElementById('textreport')
const form = document.getElementById('form')
const errorElement = document.getElementById("error")

form.addEventListener('submit', (e) => {

    e.preventDefault();

    let messages = []

    if(userid.value.length != 8 ){
        messages.push("UserID must have 8 characters")
    }
    
    if(userid.value ==="" || userid.value == null){
        messages.push("UserID is required")
    }

    if(report.value.length < 100) {
        messages.push("Report must have atleast 100 characters.")
    }

    if (messages.length > 0){
        e.preventDefault()
        errorElement.innerText = messages.join(", ")
    }

    if (messages.length === 0){
        //saveMessage(userid, report)
        errorElement.innerText("Report Submitted")
    }
})


//function getInputVal(id){
//    return document.getElementById(id).value
//}

//function saveMessage(userid, report){
  //  var newMessageRef = messageRef.push();
    //newMessageRef.set({
      //  userid: userid,
        //report: report
 //   })

//}