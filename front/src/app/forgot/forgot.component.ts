import {NgModule,Component,Pipe,OnInit} from '@angular/core';
import {ReactiveFormsModule,FormsModule,FormGroup,FormControl,Validators,FormBuilder} from '@angular/forms';
import {BrowserModule} from '@angular/platform-browser';
import {platformBrowserDynamic} from '@angular/platform-browser-dynamic';
import { UserService } from '../services/index';
@Component({
  selector: 'app-forgot',
  templateUrl: './forgot.component.html',
  styleUrls: ['./forgot.component.css']
})
export class ForgotComponent implements OnInit {
	logintext : string='Request';
  errormsg:any;
  successmsg:any;
  forgotform: FormGroup;
  email: FormControl;
  constructor(private userService:UserService) { }

  ngOnInit() {
  	this.createFormControls();
    this.createForm();
  }
  createFormControls() {
    this.email = new FormControl('', [
      Validators.required,
      Validators.pattern("^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")
    ]);
   
  }

  createForm() {
    this.forgotform = new FormGroup({
      email: this.email
    });
  }

forgot(formdata:any){
  this.logintext="Submiting..";
       this.userService.forgot(formdata)
    .subscribe(
      data =>{
        if(data.error)
        { 
          this.logintext="Request";
          this.errormsg='Email was not registered';
        }
        else
        {
          this.successmsg='Email sent to registered id.please check there to recover password';
          this.forgotform.reset();
          this.logintext="Request";
        }
      },
      error =>{
        this.errormsg="Sorry, something went wrong. Please try again";
        this.logintext="Request";
      });
}

}
