var config{
    apiKey: "AIzaSyDiUrWvr_XfF3o-YVinV_D9JuKXJpWbPaI",
    authDomain: "examination-system-f53f3.firebaseapp.com",
    databaseURL: "https://examination-system-f53f3.firebaseio.com",
    projectId: "examination-system-f53f3"
};

firfebase.initializeApp(config)

var messagesRef = firebase.database().ref('messages');


const userid = document.getElementById('idx')
const report = document.getElementById('textreport')
const form = document.getElementById('form')
const errorElement = document.getElementById("error")



form.addEventListener('submit', (e) => {

    e.preventDefault()

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
        errorElement.innerText = messages.join(", ")
    }
    else{
        e.preventDefault()
        var name = getInputVal('idx')
        var report = getInputVal('textreport')
        saveMessage(name, report)
        e.preventDefault()
        errorElement.innerText.set("Report submitted")
        e.preventDefault()
    }



})

function getInputVal(id){

    return document.getElementById(id).value;

}


function saveMessage(name, report){
    var newMessageRef = messagesRef.push();
    newMessageRef.set({name: name,
    report: report})
}
