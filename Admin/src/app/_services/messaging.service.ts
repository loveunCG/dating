import { Injectable } from '@angular/core';
// import { AngularFireDatabase } from 'angularfire2/database';
import { AngularFireAuth } from 'angularfire2/auth';
import * as firebase from 'firebase';

import 'rxjs/add/operator/take';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';

import { AuthenticationService } from "../auth/_services/authentication.service";

@Injectable()
export class MessagingService {

    messaging = firebase.messaging()
    currentMessage = new BehaviorSubject(null)

    constructor(
        // private db: AngularFireDatabase,
        private afAuth: AngularFireAuth, private _authService: AuthenticationService) { }


    updateToken(token) {
        console.log('updatetoken');
        this.afAuth.authState.take(1).subscribe(user => {
            if (!user) return;

        })
    }

    // curl https://fcm.googleapis.com/fcm/send -H "Content-Type: application/json" -H "Authorization: key=AAAAnWrZ6Xs:APA91bFM1h9IKQYuFLdUUiw4r74Aqcv3ULZDuvWTAwehGe-XMMAt_u9fxFVi2g0Dp2hdnK8ujaGHPo7lXP8xzcuXKHr05phArFwKYgN3Q4SqeQ4hp2g7aM56QiUlMjdFI5g1uilJZEr6" -d '{ "notification": {"title": "Test title", "body": "Test Body", "click_action" : "https://angularfirebase.com"},"to" : "ecuDYiw6ET0:APA91bGLYLWRUEAVqJA_nMQce0DzDsqG0GwGpNTuW4sRkmQ9KLyZFAc2ip9OTjnFhs0PN7izI4jZXl-jXk65nyUzlImb7I7vGw3JXxGHifc5uo4meDPQJuphMZpowdA76y1UlVBBx-R2"}'

    getPermission() {
        this.messaging.requestPermission()
            .then(() => {
                console.log('Notification permission granted.');
                return this.messaging.getToken()
            })
            .then(token => {
                console.log(token)
                if (localStorage.getItem("admin") !== null) {
                    var userlocal = JSON.parse(localStorage.getItem("admin"));
                    //console.log(userlocal);

                    var adminid = userlocal.token;
                    var userupdate = this._authService.updateToken(token, adminid);
                    console.log(userupdate);
                }
                this.updateToken(token)
            })
            .catch((err) => {
                console.log('Unable to get permission to notify.', err);
            });
    }

    receiveMessage() {
        this.messaging.onMessage((payload) => {
            console.log("Message received. ", payload);
            this.currentMessage.next(payload)
        });

        // this.messaging.setBackgroundMessageHandler((payload)=> {
        //   console.log('[firebase-messaging-sw.js] Received background message ', payload);
        //   // Customize notification here
        //   const notificationTitle = 'Background Message Title';
        //   const notificationOptions = {
        //     body: 'Background Message body.',
        //     icon: '/firebase-logo.png'
        //   };
        //
        //   //return this.messaging.registration.showNotification(notificationTitle, notificationOptions);
        // });

    }
}
