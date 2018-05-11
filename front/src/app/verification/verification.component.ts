import {NgModule,Component,Pipe,OnInit,ViewContainerRef} from '@angular/core';
import {ReactiveFormsModule,FormsModule,FormGroup,FormControl,Validators,FormBuilder} from '@angular/forms';
import {BrowserModule} from '@angular/platform-browser';
import {platformBrowserDynamic} from '@angular/platform-browser-dynamic';
import { UserService } from '../services/index';
import { Router ,Params,NavigationEnd,ActivatedRoute} from '@angular/router';
import { ToastsManager } from 'ng2-toastr/ng2-toastr';

@Component({
  selector: 'app-verification',
  templateUrl: './verification.component.html',
  styleUrls: ['./verification.component.css']
})
export class VerificationComponent implements OnInit {
  logintext : string='Verify';
  errormsg:any;
  successmsg:any;
  verifyform: FormGroup;
  textverify: FormControl;
  email:string;
  prevurl:any;
  constructor(private userService:UserService,private router: Router,private route: ActivatedRoute,public toastr: ToastsManager) {

     this.email=(route.snapshot.queryParams['email']);
     if(route.snapshot.queryParams['prev']){
       this.prevurl = route.snapshot.queryParams['prev'];
     }
  }

  ngOnInit() {
  	this.createFormControls();
    this.createForm();

  }
  createFormControls() {
    this.textverify = new FormControl('', [
      Validators.required
    ]);

  }

  createForm() {
    this.verifyform = new FormGroup({
      textverify: this.textverify
    });
  }

verification(formdata:any){
  this.logintext="Verifing..";
       this.userService.verify(formdata,this.email)
    .subscribe(
      data =>{
        if(data.error)
        {
          this.logintext="Verify";
          this.errormsg=data.message;
        }
        else
        {
          this.logintext="Verify";
          if(data.gender == 'Female'){
            this.toastr.success('Your account has been verified successfully.', 'Success!');
          } else{
            this.toastr.success('Your account has been verified successfully.', 'Success!');
          }

          if(this.prevurl == 'login'){
            localStorage.setItem('currentUser', JSON.stringify(data.userinfo));
          }
          this.successmsg='Your account has been verified successfully and waiting for admin approval.';
          this.router.navigate(['']);
        }
      },
      error =>{
        this.errormsg="Sorry, something went wrong. Please try again";
        this.logintext="Verify";
      });
}
resendcode(emailid:string){
  //console.log(emailid);
    this.userService.resendcode(emailid)
    .subscribe(
      data =>{
        if(data.error)
        {
          this.errormsg=data.message;
        }
        else
        {
          console.log(data.status);
          this.successmsg='Your activation code has been sent to your registered mobile number.';
        }
      },
      error =>{
        this.errormsg="Sorry, something went wrong. Please try again";
      });
}

}
