import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router, ActivatedRoute,NavigationEnd } from '@angular/router';
import { FormControl, FormsModule, ReactiveFormsModule } from "@angular/forms";

import { ChatService } from '../services/socketmsg.service';
import { UserService } from '../services/user.service';

import { ToastsManager } from 'ng2-toastr/ng2-toastr';

import 'rxjs/add/observable/interval';
import { Observable } from 'rxjs/Observable';

declare var jquery:any;
declare var $ :any;

@Component({
  selector: 'app-message-detail',
  templateUrl: './message-detail.component.html',
  styleUrls: ['./message-detail.component.css']
})
export class MessageDetailComponent implements OnInit {

  currentUrl:any;
  touserid:any;
  fromuserid:any;
  chatid : number=0;
  chatlist:any=[];
  chatmessages:any=[];
  msgmodel:any={};
  gotnewmsg='';
  tousername='';
  openedchatid:any=0;
  lastmsg:any;
  timermsgs:any;
  height: any;
  usergender:any;
  checkdisabled:boolean=false;
  msgcounter=0;
  chatindex=0;
  firstid=0;
  loadtime = 0;

  constructor(private chatservice:ChatService, private router: Router, private userService:UserService, public toastr: ToastsManager) {

    if (localStorage.getItem("currentUser") === null) {
        this.router.navigate(['login']);
    }

    var currentUser = JSON.parse(localStorage.getItem("currentUser"));
    var user = currentUser;
    this.fromuserid = user.id;
    this.usergender = user.gender;

    router.events.subscribe((event: any) => {
      if (event instanceof NavigationEnd ) {
        this.currentUrl=event.url;
        var touserid=this.currentUrl.replace('/messages/','');
        this.touserid = parseInt(touserid);
        this.firstid = parseInt(touserid);
        // console.log('touserid', this.touserid);
      }
    });

    chatservice.genmsg().subscribe(
      data=>{
        // console.log('conn ok',data);
      }
    );

    chatservice.connectuser(this.fromuserid);
    chatservice.connectsuccess().subscribe(
      data=>{
        // console.log('connok');
      }
    );


  }

  ngOnInit() {

    this.checkChatRoom(this.fromuserid, this.touserid);

    this.chatservice.getMessage().subscribe(
      data=>{
        // console.log('msgis:', data);
        if(this.openedchatid > 0){
          this.getmessages(this.chatid, this.fromuserid);
          this.timermsgs.unsubscribe();
          this.timermsgs = Observable.interval(60000).subscribe(
            (val) => {
              console.log('called');
              this.getmessages(this.chatid, this.fromuserid);
              this.updatemsgstat();
              this.msgcounter=0;
            }
          );
          this.checkdisabled = false;
        }
        if(this.usergender == 'Male'){
          this.msgcounter = 0;
        }
      }
    );

    this.timermsgs = Observable.interval(60000).subscribe(
      (val) => {
        console.log('called');
        this.getmessages(this.chatid, this.fromuserid);
        // this.chatmessages = [];
        // this.updatemsgstat();
        // this.msgcounter=0;


      }
    );

  }

  ngAfterViewInit(){
    var containerClass=".message_list";
    var msgdetail=".message_detail";
    var convo = ".coversation";

    var windowHeight = $(window).height()-300;


    $(containerClass).height(windowHeight);
    $(msgdetail).height(windowHeight);
    $(convo).height(windowHeight-200);
    console.log(windowHeight);

    $( window ).resize(function() {
    var windowHeight = $(window).height() ;

    $(containerClass).height(windowHeight);
    $(msgdetail).height(windowHeight);
    $(convo).height(windowHeight-200);
    console.log(windowHeight);
    // var windowHeight = $(window).height() + 250;
    // $('#testDiv').slimScroll({ height: windowHeight });
    });
  }

  startchat(chatid, toid, toname, chatindex){
    this.chatid = chatid;
    this.touserid = toid;
    this.tousername = toname;
    this.chatindex = chatindex;
    // console.log('new');
    this.openedchatid = chatid;
    this.getmessages(chatid, this.fromuserid);

  }

  updatemsgstat(){
    this.userService.updatemsgstat(this.chatid).subscribe(
      data=>{
        if(!data.error){
          // console.log('all read')
        } else{
          console.log('error')
        }
      }
    );
  }

  checkChatRoom(fromid, toid){
    this.userService.checkchatroom(fromid, toid).subscribe(
      data=>{
        if(data.error == false){
          this.chatid = data.chatid;
          this.getAllChats(fromid);

          this.getmessages(this.chatid, fromid);
          this.openedchatid = this.chatid;
          this.checklastfive();
          if(this.loadtime == 0){
            this.updatemsgstat();
            this.loadtime++;
          }
        } else{
          this.toastr.error('Can not get the details at the moment, please try again.');
        }
      }, error=>{
        this.toastr.error('Can not get the details at the moment, please try again.');
      }
    );

  }

  getAllChats(fromid){
    // console.log('allchats');
    this.userService.getallchats(fromid).subscribe(
      data=>{
        if(data.error == false){
          // for(var ci=0;ci<data.chatsare.length;ci++){
          //   this.chatlist.push(data.chatsare[ci]);
          // }
          // console.log('data chats',data.chatsare);
          this.chatlist = data.chatsare;
          // console.log(this.chatlist.length);
          for(var cc=0;cc<this.chatlist.length;cc++){
            // console.log('checking',this.chatlist[cc].id);
            if(this.chatlist[cc].id == this.chatid){
              this.tousername = this.chatlist[cc].tousername;
            }
          }

        }
        // console.log(this.chatlist)
      }
    );
  }

  getmessages(chatid, fromid){
    this.userService.getmessages(chatid, fromid).subscribe(
      data=>{
        if(data.error == false){
          this.chatmessages = [];
          for(var mi=0;mi<data.messages.length;mi++){
            this.chatmessages.push({sdate:data.messages[mi].sdate, message:data.messages[mi].message, flag:data.messages[mi].flag});
          }
          // this.chatmessages = data.messages;
          // this.updatemsgstat();
          var msg = this.chatmessages[this.chatmessages.length-1].message;
          console.log(msg);
          console.log(msg.length);
          if(msg.length > 0){
            this.chatlist[this.chatindex].lastmsg = msg.substring(0,66);
          } else{
            this.chatlist[this.chatindex].lastmsg = ' ';
          }



        } else{
          this.chatmessages = [];
          this.chatlist[this.chatindex].lastmsg = ' ';

        }
      }, error=>{

      }
    );

  }

  checklastfive(){
    if(this.usergender == 'Male'){
      this.userService.checklastfive(this.chatid, this.fromuserid).subscribe(
        data=>{
          if(data.error){
            console.log('error')
          } else{
            this.checkdisabled = data.stat;
          }
        }
      );
    }
  }

  sendmessage(){
    console.log('msgcounter before', this.msgcounter);
    if(this.usergender == 'Male'){
      if(this.msgcounter<=4){
        if(this.msgmodel.sendnewmsg){
          var msg = this.msgmodel.sendnewmsg;
          this.chatservice.sendMessage(this.msgmodel.sendnewmsg, this.touserid, this.fromuserid);
          this.userService.sendnewmessage(this.chatid, this.touserid, this.fromuserid, msg).subscribe(
            data=>{
              if(data.error == false){
                this.msgcounter++;
                console.log('msgcounter after', this.msgcounter);
                this.chatmessages.push({sdate:data.data.sdate, message:data.data.message, flag:1});
                // console.log('msgup', this.chatmessages);
                this.msgmodel.sendnewmsg = '';
                this.chatlist[this.chatindex].lastmsg = msg.substring(0,66);
                // console.log('updatemsg');
              }
            }, error=>{
              console.log('msg not added');
            }
          );
        }
      } else{
        this.checkdisabled = true;
      }

    } else{
      if(this.msgmodel.sendnewmsg){
        var msg = this.msgmodel.sendnewmsg;
        this.chatservice.sendMessage(this.msgmodel.sendnewmsg, this.touserid, this.fromuserid);
        this.userService.sendnewmessage(this.chatid, this.touserid, this.fromuserid, msg).subscribe(
          data=>{
            if(data.error == false){
              this.chatmessages.push({sdate:data.data.sdate, message:data.data.message, flag:1});
              // console.log('msgup', this.chatmessages);
              this.msgmodel.sendnewmsg = '';
              this.chatlist[this.chatindex].lastmsg = msg.substring(0,66);
            }
          }, error=>{
            console.log('msg not added');
          }
        );
      }
    }

  }

  ngOnDestroy(){
    this.openedchatid=0;

    this.timermsgs.unsubscribe();
  }

}
