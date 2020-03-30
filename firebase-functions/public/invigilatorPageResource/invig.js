const reportList = document.getElementById('reportList');
const form = document.querySelector('#incidentRecord');

// create element and render reports
function renderReport(doc) {

    // ----------------------------------------------
    let br = document.createElement('br');
    let hr = document.createElement('hr');
    let page = document.createElement('span');

    let caption = document.createElement('h4'); //append caption
    let innerCaption = document.createElement('small');
    var t1 = document.createTextNode("PREVIOUS POSTS");
    innerCaption.appendChild(t1);
    caption.appendChild(innerCaption)

    // append hr

    let title = document.createElement('h3'); //append title
    title.textContent = doc.data().studentUserName;

    let date = document.createElement('h5')//append date
    let icons = document.createElement('span')
    icons.classList.add('glyphicon');
    icons.classList.add('glyphicon-time');
    let time = document.createElement('span');
    time.textContent = doc.data().time;
    var t2 = document.createTextNode(' Posted on ');
    date.appendChild(icons);
    date.appendChild(t2);
    date.appendChild(time);


    let mainIcons = document.createElement('h5')//mainIcons
    let glyph1 = document.createElement('span');
    glyph1.classList.add('label');
    glyph1.classList.add('label-success');
    var t3 = document.createTextNode('created');
    var t5 = document.createTextNode(' ');
    glyph1.appendChild(t3);
    let glyph2 = document.createElement('span');
    glyph2.classList.add('label');
    glyph2.classList.add('label-primary');
    var t4 = document.createTextNode('read');
    glyph2.appendChild(t4);
    mainIcons.appendChild(glyph1);
    mainIcons.appendChild(t5);
    mainIcons.appendChild(glyph2);

    // append break (br)
    // append break (br)

    let comment = document.createElement('p')//append comment
    comment.textContent = doc.data().report;

    page.appendChild

    page.appendChild(caption);
    page.appendChild(hr);
    page.appendChild(title);
    page.appendChild(date);
    page.appendChild(mainIcons);
    page.appendChild(comment);
    page.appendChild(br);
    page.appendChild(br);
    page.appendChild(br);

    reportList.appendChild(page);

}

function loadContent() {
    db.collection('invigilatorReport').get().then((snapshot) => {
        snapshot.docs.forEach(doc => {
            renderReport(doc)
        });
    });

}


loadContent();

//saving data
form.addEventListener('submit', (e) => {
    e.preventDefault();
    var today = new Date();
    const ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(today)
    const mo = new Intl.DateTimeFormat('en', { month: 'short' }).format(today)
    const da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(today)
    var date = da + ' ' + mo + ', ' + ye + '.';
    console.log(form.id.value, form.comment.value, date);

    db.collection('invigilatorReport').add({
        report: form.comment.value,
        studentUserName: form.id.value,
        time: date
    });
    alert('Your report has been logged');
    setTimeout(function () { location.reload(1); }, 1000);


});