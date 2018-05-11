import {NgModule,Component,Pipe,OnInit,Inject} from '@angular/core';
import {ReactiveFormsModule,FormsModule,FormGroup,FormControl,Validators,FormBuilder,AbstractControl} from '@angular/forms';
import {BrowserModule} from '@angular/platform-browser';
import {platformBrowserDynamic} from '@angular/platform-browser-dynamic';
import {MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import { UserService } from '../services/index';
import { Router, ActivatedRoute } from '@angular/router';

import { environment } from '../../environments/environment';

declare var jquery:any;
declare var $ :any;

@Component({
   selector: 'app-boysignup',
  templateUrl: './boysignup.component.html',
  styleUrls: ['./boysignup.component.css']
})
export class BoysignupComponent implements OnInit {
  // [ /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/]
  mask: any[] = ['+', '6', '1', '-', '0', /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/];
  registertext : string='Create Account';
  imageurl = environment.imageurl;
  errormsg:any;
  successmsg:any;
  myform: FormGroup;
  firstName: FormControl;
  lastName: FormControl;
  email: FormControl;
  password: FormControl;
  cpassword:FormControl;
  phone: FormControl;
  age: FormControl;
  sex: FormControl;
  location: FormControl;
  service: FormControl;
  aboutme:FormControl;
  file:FormControl;
  profiledata:any ={};
  uimages:any=[];

  customStyle = {
    selectButton: {
      "background": "url(http://54.218.127.55/projects/Dating/uploads/upload.png) no-repeat",
      "cursor": "pointer",
      "box-shadow":"none" ,
      "height": "48px",
      "width": "36px",
      "float":"inherit",
      "background-position": "center center"
    },
    clearButton: {
      "background-color": "#FFF",
      "border-radius": "25px",
      "color": "#000",
      "margin-left": "10px",
      "display":"none"
    },
    layout: {
      "background-color": "white",
      "border":"none"
    },
    previewPanel: {

    }
  }
  constructor(
    private userService:UserService,
    private route: ActivatedRoute,
    private router: Router,
   /* public dialogRef: MatDialogRef<BoysignupComponent>*/
    /*@Inject(MAT_DIALOG_DATA) public data: any*/
    ) {

   }

    ngOnInit() {
      //console.log(this.data.type);
    this.createFormControls();
    this.createForm();
  }
  createFormControls() {
    this.firstName = new FormControl('', Validators.required);
    this.lastName = new FormControl('');//, Validators.required
    this.age = new FormControl('', [Validators.required,Validators.min(18)]);
    this.phone = new FormControl('', [
      Validators.required
    ]);
    this.file=new FormControl('');
    this.email = new FormControl('', [
      Validators.required,
      Validators.pattern("^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")
    ]);
    this.password = new FormControl('', [
      Validators.required,
      Validators.minLength(8)
    ]);

     this.cpassword = new FormControl('', [
      Validators.required,
      Validators.minLength(8),
      passwordConfirming
    ]);
  }

  createForm() {
    this.myform = new FormGroup({
      firstName: this.firstName,
      lastName: this.lastName,
      email: this.email,
      password: this.password,
      cpassword: this.cpassword,
      file:this.file,
      age: this.age,
      phone: this.phone,
    });
  }

register(formdata:any){
  if(this.myform.valid){
  this.registertext="Creating..";
  console.log(this.profiledata);
  formdata.regtype='Male';
  formdata.images=this.profiledata;
  console.log(formdata);

  this.userService.create(formdata)
    .subscribe(
      data =>{
        if(data.error)
        {
          this.registertext="Create Account";
          this.errormsg=data.message;
        }
        else
        {
          this.successmsg=data.message;
          this.myform.reset();
          this.profiledata={};
          $(".img-ul-clear").trigger('click');
          this.registertext="Create Account";
          this.router.navigate(['/verify'],{queryParams:{'email':formdata.email},skipLocationChange: true});
        }
      },
      error =>{
        this.errormsg="Sorry, something wet wrong. Please try again";
        this.registertext="Create Account";
      });
    } else{
      console.log('else');
      this.validateAllFormFields(this.myform);
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

onUploadFinished(file: any) {
  //console.log(this.myform.value.price);
  //console.log(this.myform.value.enlock);
  var pimgobj:any={};
  pimgobj.data = JSON.stringify(file.src);
  pimgobj.price = 0;
  pimgobj.enlock = 0;
  this.profiledata[file.file.name]=pimgobj;

}

onRemoved(file: any) {

  // do some stuff with the removed file.
  delete this.profiledata[file.file.name];
  console.log(this.profiledata);
}

onUploadStateChanged(state: boolean) {
  //console.log(JSON.stringify(state));
}
 cancel(){
   // this.dialogRef.close();
  }
}

function passwordConfirming(c: AbstractControl): any {
        if(!c.parent || !c) return;
        const pwd = c.parent.get('password');
        const cpwd= c.parent.get('cpassword')

        if(!pwd || !cpwd) return ;
        if (pwd.value !== cpwd.value) {
            return { invalid: true };
        }
}
