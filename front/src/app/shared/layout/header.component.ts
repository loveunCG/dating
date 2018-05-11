import { Component,OnInit, Renderer, ViewChild} from '@angular/core';
import { MatDialog } from '@angular/material';
import { SignupComponent } from '../../signup/signup.component';
import { LoginComponent } from '../../login/login.component';
import { BoysignupComponent } from '../../boysignup/boysignup.component';
import { Router, ActivatedRoute,NavigationEnd } from '@angular/router';
import { UserService } from '../../services/index';
import { environment } from '../../../environments/environment';

import 'rxjs/add/observable/interval';
import { Observable } from 'rxjs/Observable';

@Component({
  selector: 'layout-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
  /*entryComponents: [
    SignupComponent,
    LoginComponent,
    BoysignupComponent
  ]*/
})

export class HeaderComponent implements OnInit{
setting:any = {};
currentUser:any;
iconclicked:boolean=false;
imageurl = environment.imageurl;
currentUrl='';
msgcount:any=0;
msgcounterint:any;
ismobileNav:boolean=false;

@ViewChild('openmenu') menu;

constructor( private route: ActivatedRoute, private router: Router, public userService:UserService, public renderer: Renderer) {
  router.events.subscribe((event: any) => {
    if (event instanceof NavigationEnd ) {
      this.currentUrl = event.url;
    }
  });
}

ngOnInit() {
     this.getsetting();
     this.ismobileNav = false;
     this.colseNav();

      this.renderer.listenGlobal('document', 'click', (event) => {
        // if(!this.menu.nativeElement.contains(event.target)){
        //   this.iconclicked = false;
        // } else{
        //   this.iconclicked = !this.iconclicked;
        // }

      });
      this.msgcounterint = Observable.interval(10000).subscribe(
        (val) => {
          if (localStorage.getItem("currentUser") === null) {

          } else{

            var user = JSON.parse(localStorage.getItem('currentUser'));

            this.userService.getunreadcount(user.id).subscribe(
              data=>{
                if(!data.error){
                  this.msgcount = data.data;
                }
              }, error=>{

              }
            );

          }
        }
      );
   }
getsetting(){
     this.userService.getsetting()
    .subscribe(
      data =>{
        if(data.error){}
        else
        {
         this.setting=data.sdata;
        // console.log(this.setting);
        }
      },
      error =>{});
   }
/*opengirlRegister(regtype:string) {
 	const dialogRef = this.dialog.open(SignupComponent,{
 		data: {type:regtype},
  		height: '600px'
  	});
 	dialogRef.afterClosed().subscribe(result => {
 	});
 }
 openboyRegister(regtype:string) {
   const bdialogRef = this.dialog.open(BoysignupComponent,{
     data: {type:regtype},
      height: '450px'
    });
   bdialogRef.afterClosed().subscribe(result => {
   });
 }

openLogin()
{
	const logindialogRef = this.dialog.open(LoginComponent,{
  height: '600px'
});
 	logindialogRef.afterClosed().subscribe(result => {
 	});
} */
/*check user login or not */
  isLoggedIn()
  {
    if (localStorage.getItem('currentUser')) {
      this.currentUser = JSON.parse(localStorage.getItem('currentUser'));
      return false;
    } else {
      return true;
    }
  }
  openmenu()
  {
    this.iconclicked=!this.iconclicked;
  }
     /* remove current user and redirect to home */
  logout() {
      // remove user from local storage to log user out
      localStorage.removeItem('currentUser');
      this.iconclicked=!this.iconclicked;
      this.router.navigate(['/']);
  }
  closemenu()
  {
    this.iconclicked=!this.iconclicked;
  }

  openprofile(){
    if(this.currentUser.gender=='Female'){
      this.router.navigate(['/edit-girlprofile/'+this.currentUser.id, {openpreview: true}]);
    } else{
      this.router.navigate(['/edit-boyprofile/'+this.currentUser.id, {openpreview: true}]);
    }
    this.closemenu();
  }

  openpayments(){
    if(this.currentUser.gender=='Female'){
      this.router.navigate(['/edit-girlprofile/'+this.currentUser.id, {openpayment: true}]);
    } else{
      this.router.navigate(['/edit-boyprofile/'+this.currentUser.id, {openpayment: true}]);
    }
    this.closemenu();
  }
  openNav () {
    this.ismobileNav = true;
  }
  colseNav () {
    this.ismobileNav = false;
  }
}
