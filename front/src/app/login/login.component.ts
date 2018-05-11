import {NgModule,Component,Pipe,OnInit} from '@angular/core';
import {ReactiveFormsModule,FormsModule,FormGroup,FormControl,Validators,FormBuilder} from '@angular/forms';
import {BrowserModule} from '@angular/platform-browser';
import {platformBrowserDynamic} from '@angular/platform-browser-dynamic';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../services/index';
import {MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import { SignupComponent } from '../signup/signup.component';
import { BoysignupComponent } from '../boysignup/boysignup.component';
@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  logintext : string='Log In';
  errormsg:any;
  successmsg:any;
  loginform: FormGroup;
  email: FormControl;
  password: FormControl;
  returnUrl:string;
  constructor(private userService:UserService,
              public dialog: MatDialog,
              private route: ActivatedRoute,
              private router: Router
              /*public dialogRef: MatDialogRef<LoginComponent>*/
              ) { }

  ngOnInit() {
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';
  	 this.createFormControls();
    this.createForm();
  }
 createFormControls() {
    this.email = new FormControl(null, [
      Validators.required,
      Validators.pattern("^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")
    ]);
    this.password = new FormControl(null, [
      Validators.required
    ]);
  }

  createForm() {
    this.loginform = new FormGroup({
      email: this.email,
      password: this.password
    });
  }

login(formdata:any){
  if(this.loginform.valid){
  this.logintext="Signing In..";
       this.userService.login(formdata)
    .subscribe(
      data =>{
        if(data.error)
        {
          if(data.activation==false)
          {
            this.router.navigate(['/verify'],{queryParams:{'email':formdata.email, 'prev':'login'},skipLocationChange: true});
          }
          else
          {
            this.logintext="Log In";
            this.errormsg=data.message;
          }

        }
        else
        {
          let user = data;
            console.log(data);
            if (user && user.apiKey) {
              this.userService.updatelogin(user.id).subscribe(
                data=>{

                }, error=>{
                  console.log('Something went wrong');
                }
              );
              // store user details and jwt token in local storage to keep user logged in between page refreshes
              localStorage.setItem('currentUser', JSON.stringify(user));
            }
            this.router.navigate([this.returnUrl]);
          this.successmsg=data.message;
          this.loginform.reset();
          this.logintext="Log In";
        }
      },
      error =>{
        this.errormsg="Sorry, something went wrong. Please try again";
        this.logintext="Log In";
      });
    } else{
      this.validateAllFormFields(this.loginform);
    }
}

validateAllFormFields(formGroup: FormGroup) {
  Object.keys(formGroup.controls).forEach(field => {
    const control = formGroup.get(field);
    if (control instanceof FormControl) {
      control.markAsDirty({ onlySelf: true });
    } else if (control instanceof FormGroup) {
      this.validateAllFormFields(control);
    }
  });
}

 cancel(){
    //this.dialogRef.close();
  }
  /*opengirlRegister(regtype:string) {
    this.cancel();
   const dialogRef = this.dialog.open(SignupComponent,{
     data: {type:regtype},
      height: '600px'
    });
   dialogRef.afterClosed().subscribe(result => {
   });
 }
 openboyRegister(regtype:string) {
   this.cancel();
   const bdialogRef = this.dialog.open(BoysignupComponent,{
     data: {type:regtype},
      height: '450px'
    });
   bdialogRef.afterClosed().subscribe(result => {
   });
 }*/
}
