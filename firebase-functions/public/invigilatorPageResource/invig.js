const userid = document.getElementById('idx')
const report = document.getElementById('textreport')
const form = document.getElementById('form')
const errorElement = document.getElementById("error")



form.addEventListener('submit', (e) => {

    errorElement.innerText = "XXX"

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
})