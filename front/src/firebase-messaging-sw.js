importScripts("https://www.gstatic.com/firebasejs/4.10.0/firebase.js");
// importScripts('https://www.gstatic.com/firebasejs/3.1.0/firebase-auth.js');
// importScripts('https://www.gstatic.com/firebasejs/3.1.0/firebase-database.js');
// importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-messaging.js');

  // Initialize Firebase
  var config = {
    apiKey: "AIzaSyA4bQnf-dOVr9DZHGadMXm6sQImK-huQCM",
    authDomain: "datingwebsite-a9bda.firebaseapp.com",
    databaseURL: "https://datingwebsite-a9bda.firebaseio.com",
    projectId: "datingwebsite-a9bda",
    storageBucket: "datingwebsite-a9bda.appspot.com",
    messagingSenderId: "676102531451"
  };
  firebase.initializeApp(config);

  const messaging = firebase.messaging();

  messaging.setBackgroundMessageHandler(function(payload)   {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    // Customize notification here
    const notificationTitle = 'Background Message Title';
    const notificationOptions = {
      body: 'Background Message body.',
      icon: '/firebase-logo.png'
    };

    return self.registration.showNotification(notificationTitle,
        notificationOptions);
  });
