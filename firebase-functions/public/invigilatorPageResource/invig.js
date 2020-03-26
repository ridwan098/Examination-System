
const userid = document.getElementById('idx')
const report = document.getElementById('textreport')
const form = document.getElementById('form')
const errorElement = document.getElementById("error")

form.addEventListener('submit', submitform);

function submitform(e) {

    e.preventDefault()

    let messages = []

    if (userid.value.length != 8) {
        messages.push("UserID must have 8 characters")
    }

    if (userid.value === "" || userid.value == null) {
        messages.push("UserID is required")
    }

    if (report.value.length < 100) {
        messages.push("Report must have atleast 100 characters.")
    }

    if (messages.length > 0) {
        errorElement.innerText = messages.join(", ")
    }

    if (messages.length == 0) {
        var name = getInputVal('idx')
        var report = getInputVal('textreport')
        saveMessage(name, report)
        errorElement.innerText.set("Report submitted")
    }

}

function getInputVal(id) {
    return document.getElementById(id).value;
}


function saveMessage(name, report) {
    var newMessageRef = messagesRef.push();
    newMessageRef.set({
        name: name,
        report: report
    })
}



