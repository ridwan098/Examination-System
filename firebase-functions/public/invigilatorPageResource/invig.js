const reportList = document.getElementById('reportList');


// create element and render reports
function renderReport(doc) {
    let li = document.createElement('li');

    let report = document.createElement('span');
    let br = document.createElement('br');
    let studentUsername = document.createElement('span');
    let time = document.createElement('span');

    li.setAttribute('data-id', doc.id);
    report.textContent = doc.data().report;
    studentUsername.textContent = doc.data().studentUserName;
    time.textContent = doc.data().time;

    li.appendChild(report);
    li.appendChild(studentUsername);
    li.appendChild(time);
    console.log(report, studentUsername, time, li)

    reportList.appendChild(li);

}
db.collection('invigilatorReport').get().then((snapshot) => {
    snapshot.docs.forEach(doc => {
        renderReport(doc)
    });
});