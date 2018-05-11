import { Injectable }          from '@angular/core';
import { AngularFireDatabase } from 'angularfire2/database';
import { AngularFireAuth }     from 'angularfire2/auth';
import * as firebase from 'firebase';

import 'rxjs/add/operator/take';
import { BehaviorSubject } from 'rxjs/BehaviorSubject'

@Injectable()
export class MessagingService {

  messaging = firebase.messaging()
  currentMessage = new BehaviorSubject(null)

  constructor(private db: AngularFireDatabase, private afAuth: AngularFireAuth) { }


  updateToken(token) {
    this.afAuth.authState.take(1).subscribe(user => {
      if (!user) return;

      const data = { [user.uid]: token }
      this.db.object('fcmTokens/').update(data)
    })
  }

  // curl https://fcm.googleapis.com/fcm/send -H "Content-Type: application/json" -H "Authorization: key=AAAAnWrZ6Xs:APA91bFM1h9IKQYuFLdUUiw4r74Aqcv3ULZDuvWTAwehGe-XMMAt_u9fxFVi2g0Dp2hdnK8ujaGHPo7lXP8xzcuXKHr05phArFwKYgN3Q4SqeQ4hp2g7aM56QiUlMjdFI5g1uilJZEr6" -d '{ "notification": {"title": "Test title", "body": "Test Body", "click_action" : "https://angularfirebase.com"},"to" : "ezUwP4JV0R4:APA91bFmApWjEq0yx-L5IsHBDG4D5p4B-OBd2KQvpGOhs-0fRMBM4lYJBPpVgi4Bx5vf6NNEesLUBnYhuRygKVSwJ9FAIyJKAREQ6WeVcebo1anKIRMFZCutAkXRgRh5pCcJ8RODyNXh"}'

  getPermission() {
      this.messaging.requestPermission()
      .then(() => {
        console.log('Notification permission granted.');
        return this.messaging.getToken()
      })
      .then(token => {
        console.log(token)
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
