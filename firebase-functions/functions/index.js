const functions = require('firebase-functions');

// // Create and Deploy Your First Cloud Functions
// // https://firebase.google.com/docs/functions/write-firebase-functions
//
// exports.helloWorld = functions.https.onRequest((request, response) => {
//  response.send("Hello from Firebase!");
// });


// http request 1
exports.randomNumber = functions.https.onRequest((request, response) => {
    const number = Math.round(Math.random() * 100);
    console.log(number);
    response.send(number.toString());
});

// http request 2
exports.toTheDojo = functions.https.onRequest((request, response) => {
    response.redirect('https://www.bbc.co.uk');
});


exports.sayHello = functions.https.onCall((data, context) => {
    console.log('You just pressed the button');
    return `hello, ninjas`;
});