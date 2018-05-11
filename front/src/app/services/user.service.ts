import {Injectable} from '@angular/core';
import {Http,Headers,RequestOptions,Response} from '@angular/http';
import { environment } from '../../environments/environment';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/map';

@Injectable()
export class UserService {
   apiurl = environment.apiUrl;
   constructor(private http: Http) {}

   create(user: any) {
      return this.http.post(this.apiurl + 'register', user,this.jwt()).map((response: Response) => response.json());
   }

   checkvisitor(){
     return this.http.get('http://freegeoip.net/json/?callback').map((response: Response) => response.json());
   }

   addvisitor(ip:any){
     return this.http.post(this.apiurl + 'addvisitor', {ip:ip},this.jwt()).map((response: Response) => response.json());
   }

   login(user: any)
   {
     return this.http.post(this.apiurl + 'login', user,this.jwt()).map((response: Response) => response.json());
   }

   updatelogin(uid:any){
     return this.http.post(this.apiurl + 'updatelogin', {uid:uid},this.jwt()).map((response: Response) => response.json());
   }

    forgot(user: any)
   {
     return this.http.post(this.apiurl + 'forgot', user,this.jwt()).map((response: Response) => response.json());
    }
    verify(user: any,email)
   {
     return this.http.post(this.apiurl + 'verify',{textverify:user.textverify,email:email},this.jwt()).map((response: Response) => response.json());
    }
    resendcode(email:string)
    {
     return this.http.post(this.apiurl + 'resendcode',{email:email},this.jwt()).map((response: Response) => response.json());
    }
    getlisting(state:string, suburb:any,name:any,weight:any,height:any,lat:any,long:any,radius:any)
    {
     return this.http.post(this.apiurl + 'profilelist',{state:state, name:name, weight:weight, height:height, lat:lat, long:long, radius:radius, suburb:suburb},this.jwt()).map((response: Response) => response.json());
    }
    getsuburbs(state:string)
    {
     return this.http.get(this.apiurl + 'getsuburbs/'+state).map((response: Response) => response.json());
    }
    checkWallet(userid:any){
      return this.http.get(this.apiurl + 'checkunlockstat/'+userid).map((response: Response) => response.json());
    }
    payForUnlcok(userid:any, amount:any, unlockid:any){
      return this.http.post(this.apiurl + 'payforunlock',{id:userid, amount:amount, unlockid:unlockid},this.jwt()).map((response: Response) => response.json());
    }
    gethighlight()
    {
     return this.http.get(this.apiurl + 'highlightprofile').map((response: Response) => response.json());
    }
    recentlisting()
    {
     return this.http.get(this.apiurl + 'recentlisting').map((response: Response) => response.json());
    }
    getsetting()
    {
      return this.http.get(this.apiurl + 'setting').map((response: Response) => response.json());
    }
    getcmspages(){
       return this.http.get(this.apiurl + 'cmslist').map((response: Response) => response.json());
    }
    getgirlprofile(id:number){
       return this.http.post(this.apiurl + 'girl',{id:id},this.jwt()).map((response: Response) => response.json());
    }
    deletecomplaint(id:any){
      return this.http.get(this.apiurl + 'deletecomplaint/'+id).map((response: Response) => response.json());
    }
    deletecomment(id:any){
      return this.http.get(this.apiurl + 'deletecomment/'+id).map((response: Response) => response.json());
    }
    getPackages(gender:any){
      return this.http.get(this.apiurl + 'getfrontpackages/'+gender,this.jwt()).map((response: Response) => response.json());
    }
    getboyprofile(id:number){
       return this.http.post(this.apiurl + 'boy',{id:id},this.jwt()).map((response: Response) => response.json());
    }
    updateprofile(user: any,girlid:number) {
      return this.http.post(this.apiurl + 'updateprofile/' + girlid, user,this.jwt()).map((response: Response) => response.json());
   }
   changehighlight(girlid:any, stat:any){
      return this.http.post(this.apiurl + 'changehighlight', {uid:girlid,stat:stat}, this.jwt()).map((response: Response) => response.json());
   }

   getunreadcount(uid:any){
     return this.http.get(this.apiurl + 'getunreadcount/'+uid,this.jwt()).map((response: Response) => response.json());
   }

   changeimage(imgdata){
     return this.http.post(this.apiurl + 'changefirstimg', imgdata,this.jwt()).map((response: Response) => response.json());
   }

   uploadVideo(data:any){
     return this.http.post(this.apiurl + 'uploadvideo', data,this.jwt()).map((response: Response) => response.json());
   }

   addtestinomy(testinomy:any){
     return this.http.post(this.apiurl + 'addtestinomy', testinomy,this.jwt()).map((response: Response) => response.json());
   }

   searchtestinomy(data:any){
     return this.http.post(this.apiurl + 'searchtestinomy', data,this.jwt()).map((response: Response) => response.json());
   }

   getTestimonials(userid:any){
     return this.http.get(this.apiurl + 'tesinomials/'+userid).map((response: Response) => response.json());
   }

   addcomment(data:any){
     return this.http.post(this.apiurl + 'addcomment', data,this.jwt()).map((response: Response) => response.json());
   }
   addcomplaint(data:any){
     return this.http.post(this.apiurl + 'addcomplaint', data,this.jwt()).map((response: Response) => response.json());
   }

   getcomments(userid:any){
     return this.http.get(this.apiurl + 'getcomments/'+userid).map((response: Response) => response.json());
   }
   getcomplaints(userid:any){
     return this.http.get(this.apiurl + 'getcomplaints/'+userid).map((response: Response) => response.json());
   }

   checkchatroom(fromid:any, toid:any){
     var tmp = {fromid:fromid, toid:toid};
     console.log(tmp);
     return this.http.post(this.apiurl + 'checkchatroom', tmp,this.jwt()).map((response: Response) => response.json());
   }

   getallchats(fromid:any){
     return this.http.get(this.apiurl + 'getuserchats/'+fromid).map((response: Response) => response.json());
   }

   getmessages(chatid:any, fromid:any){
     return this.http.get(this.apiurl + 'getmessages/'+chatid+'/'+fromid).map((response: Response) => response.json());
   }

   sendnewmessage(chatid:any, toid:any, fromid:any, msg:any){
     var sendmsg = {chatid:chatid, toid:toid, fromid:fromid, msg:msg};
     return this.http.post(this.apiurl + 'sendmessage', sendmsg,this.jwt()).map((response: Response) => response.json());
   }

   updatemsgstat(chatid:any){
     return this.http.get(this.apiurl + 'updatechatmsg/'+chatid).map((response: Response) => response.json());
   }

   checklastfive(chatid:any, fromid:any){
     return this.http.get(this.apiurl + 'checklastfive/'+chatid+'/'+fromid).map((response: Response) => response.json());
   }

   getserviceLocation () {
     return this.http.get(this.apiurl + 'getservice').map((response: Response) => response.json());
   }

   // private helper methods

   private jwt() {
      // create authorization header with jwt token
     // let currentUser = JSON.parse(localStorage.getItem('currentUser'));
     // if (currentUser && currentUser.apiKey) {

     // }
      let headers = new Headers({
            Accept : "application/json; charset=utf-8",
            "Content-Type": "text/plain; charset=utf-8"
         });
         return new RequestOptions({
            headers: headers
         });
   }
}
