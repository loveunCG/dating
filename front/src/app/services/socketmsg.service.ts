import { Injectable } from '@angular/core';
import { Socket } from 'ng-socket-io';

@Injectable()
export class ChatService {

    constructor(private socket: Socket) { }

    sendMessage(msg: string, toid:any, fromid:any){
        var data = {msg:msg, to:toid, from:fromid};
        this.socket.emit("send-message", data);
    }

    getMessage() {
        return this.socket.fromEvent("receivedmsg").map( data => data );
    }

    genmsg() {
      return this.socket.fromEvent("message").map( data => data );
    }

    connectuser(uid){
      this.socket.emit("user-connected", {userid: uid});
    }

    connectsuccess(){
      return this.socket.fromEvent('connection-successful').map(data=>data);
    }
}
