import {NgModule,Component,Pipe,OnInit,AfterViewInit,Inject,ViewChild,ElementRef,NgZone} from '@angular/core';
import {ReactiveFormsModule,FormsModule,FormGroup,FormControl,Validators,FormBuilder,AbstractControl} from '@angular/forms';
import {BrowserModule} from '@angular/platform-browser';
import {platformBrowserDynamic} from '@angular/platform-browser-dynamic';
import {MatDialog, MatDialogConfig, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../services/index';
import { Injectable } from '@angular/core';

// import { MessagingService } from "../services/messaging.service";

import { AgmCoreModule, MapsAPILoader } from '@agm/core';
import { ToastsManager } from 'ng2-toastr/ng2-toastr';
import {} from '@types/googlemaps';

import { SimpleNotificationsModule } from 'angular2-notifications';

import { environment } from '../../environments/environment';

declare var jquery:any;
declare var $ :any;

@Component({
  selector: 'app-signup',
  templateUrl: './signup.component.html',
  styleUrls: ['./signup.component.css']
})
// ['+', '1', ' ', '(', /[1-9]/, /\d/, /\d/, ')', ' ', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/]
export class SignupComponent implements OnInit {
  mask: any[] =['+', '6', '1', '-', '0', /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/];
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
  // location: FormControl;
  locstate: FormControl;
  searchControl: FormControl;
  service: FormControl;
  aboutme: FormControl;
  file: FormControl;
  enlock: FormControl;
  price: FormControl;
  profiledata: any = {};
  uimages: any = [];
  imgerror = false;
  heightvals = [];
  message: any;
  loclat: any;
  loclong: any;
  suburb = '';
  heights = [];
  serviceLocation = [];

  options:any = {
   	 	types: [],
    	componentRestrictions: { country: 'US' }
    };
    @ViewChild("hsearch") public searchElementRef: ElementRef;
    addresscomponents: any;
  customStyle = {
    selectButton: {
      "background": "url(http://localhost/dating/uploads/upload.png) no-repeat",
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
  constructor(private userService:UserService,
             private route: ActivatedRoute,
             private router: Router,
             private mapsAPILoader: MapsAPILoader,
             private ngZone: NgZone,public toastr: ToastsManager,
             // private msgService: MessagingService
    /*public dialogRef: MatDialogRef<SignupComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any*/
    ) {
      userService.getserviceLocation().subscribe(
        data=>{
          console.log(data)

          if(data.error == false){

            for (const key in data.location) {
              this.serviceLocation.push(data.location[key]['statename']);
            }

          } else {

          }
          console.log('this is location info ', this.serviceLocation)
        }, error => {

        }
      );
      // this.msgService.getPermission()
      //     this.msgService.receiveMessage()
      //     this.message = this.msgService.currentMessage

   }

    ngOnInit() {
     // console.log(this.data.type);
    this.createFormControls();
    this.createForm();

    var sq="'";
    var dq='"';

    for(var hf=4;hf<9;hf++){
      for(var hi=0;hi<13;hi++){
        var ch = hf+sq+hi+dq;
        this.heightvals.push(ch);
      }
    }

    for(var hes=123;hes<244;hes++){
      this.heights.push(hes);
    }

  }
  createFormControls() {
    this.firstName = new FormControl('', Validators.required);
    this.lastName = new FormControl('');//, Validators.required

    this.age = new FormControl('', [
      Validators.required,
      Validators.min(18)
    ]);
    this.sex = new FormControl('', Validators.required);
    // this.location = new FormControl('', Validators.required);
    this.locstate = new FormControl('', Validators.required);
    this.searchControl = new FormControl('', Validators.required);
    this.service = new FormControl('', Validators.required);
    this.aboutme=new FormControl('',Validators.required);
    this.enlock= new FormControl('');
    this.price= new FormControl('');
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
      age: this.age,
      cpassword: this.cpassword,
      phone: this.phone,
      sex: this.sex,
      // location: this.location,
      locstate: this.locstate,
      searchControl:this.searchControl,
      service: this.service,
      aboutme: this.aboutme,
      file:this.file,
      enlock:this.enlock,
      price:this.price
    });
  }
changeprice(){
  let priceval = this.myform.controls['enlock'].value;
  //console.log(priceval);
  let price = this.myform.controls['price'];

  if(priceval){
    if(this.myform.value.price>0){
      this.imgerror = false;
    } else{
      this.imgerror = true;
    }
  } else{
    this.imgerror = false;
  }
  //console.log(this.myform.controls['price']);

}
checkprice(){
  let priceval = this.myform.controls['enlock'].value;
  //console.log(priceval)
  if(priceval){
    if(this.myform.value.price>0){
      this.imgerror = false;
    } else{
      this.imgerror = true;
    }
  } else{
    this.imgerror = false;
  }
}
register(formdata:any){
  this.errormsg = '';
  this.successmsg='';
    if(this.myform.valid){
    console.log(this.profiledata);
    for (var key in this.profiledata) {
      // skip loop if the property is from prototype
      console.log(this.profiledata[key]);
      this.profiledata[key].price = this.myform.value.price;
      this.profiledata[key].enlock = this.myform.value.enlock;
    }
    console.log('after');
    console.log(this.profiledata);
    if(this.imgerror == false){
    this.registertext = "Creating..";
    //console.log(this.profiledata);
    formdata.regtype = 'Female';
    formdata.images = this.profiledata;
    if($.isEmptyObject(formdata.images)){
      this.registertext = "Create Account";
      this.errormsg = 'Please select atleast one image';

    } else{
      formdata.suburb = this.suburb;
      formdata.lat = this.loclat;
      formdata.long = this.loclong;
      this.userService.create(formdata)
      .subscribe(
        data => {
          if(data.error) {
            this.registertext = "Create Account";
            this.errormsg = data.message;
          }
          else{
            this.successmsg = data.message;
            this.errormsg = '';
            this.myform.reset();
            this.profiledata = {};
            $(".img-ul-clear").trigger('click');
            this.registertext = "Create Account";
            this.router.navigate(['/verify'],{queryParams:{'email':formdata.email},skipLocationChange: true});
          }
        },
        error =>{
          this.errormsg = "Sorry, something wet wrong. Please try again";
          this.registertext = "Create Account";
        });
      }
    }
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
  let imagedata:any={};
  imagedata.data=JSON.stringify(file.src);
  if(this.myform.value.enlock){
    if(this.myform.value.price>0){
      //imagedata.price=this.myform.value.price;
      //this.imgerror = false;
    } else{
      //this.imgerror = true;
      //imagedata.price=0;
      // for(var i=0;i <= $('.img-ul-x-mark').length;i++){
      //   $(".img-ul-x-mark").eq( i ).trigger('click');
      // }
    }
  } else{
    //imagedata.price=0;
  }

  imagedata.enlock = this.myform.value.enlock;
  console.log(imagedata);
  //this.myform.controls['price'].setValue('');
  this.myform.controls['enlock'].setValue(false);
  this.profiledata[file.file.name] = imagedata;
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
    //this.dialogRef.close();
  }
  fileChange($event) {
    //this.myform.controls['file'].setValue($event.target.files);
    console.log($event.target.files);
}

ngAfterViewInit(){
  this.mapsAPILoader.load().then(() => {
    //console.log('google places');
    if(typeof(google)!==undefined){
      //console.log('google');
    }
    let autocomplete = new google.maps.places.Autocomplete(this.searchElementRef.nativeElement, {
      types: ['(cities)']
    });
    autocomplete.setComponentRestrictions(
            {
              'country': ['au']
            }
         );
    autocomplete.addListener("place_changed", () => {
      this.ngZone.run(() => {
        //get the place result
        let place: google.maps.places.PlaceResult = autocomplete.getPlace();

        //verify result
        if (place.geometry === undefined || place.geometry === null) {
          return;
        }

        //console.log(place);
        let componentForm = {
            street_number: 'short_name',
            route: 'long_name',
            locality: 'long_name',
            administrative_area_level_1: 'long_name',
            administrative_area_level_2: 'short_name',
            country: 'long_name',
            postal_code: 'short_name',
        };
        let route = '';
        let nh = '';
        let loc = '';
        let state = '';
        let st_number = '';
        //let county = place.address_components[3].long_name ? place.address_components[3].long_name:'';

        let addr = '';

        for (var i = 0; i < place.address_components.length; i++) {


            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
                var val = place.address_components[i][componentForm[addressType]];

                if (addressType == 'locality') {
                    loc = val;
                }

                if (addressType == 'country') {

                }

                if (addressType == 'administrative_area_level_1') {
                  state = val;
                }

                if (addressType == 'administrative_area_level_2') {
                    nh = val;
                }

                if (addressType == 'street_number') {

                    st_number = val + ' ';
                }

                if (addressType == 'route') {
                    route = val+' ';
                }
            }
        }

        if (st_number) {
            addr = addr.concat(st_number);
        }

        if (route) {
            addr = addr.concat(route);
        }
        if (loc) {
            this.suburb = loc;
        }
        if (nh) {
            addr = addr.concat(nh);
        }

        this.loclat = place.geometry.location.lat();
        this.loclong = place.geometry.location.lng();

        this.myform.controls['searchControl'].setValue(loc);
        this.myform.controls['locstate'].setValue(state);

      });
    });
  });
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
