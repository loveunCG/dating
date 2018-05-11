import { Component, OnInit,ViewChild,OnDestroy, ElementRef, NgZone } from '@angular/core';
import { Router, ActivatedRoute,NavigationEnd, Params } from '@angular/router';
import { UserService } from '../services/index';
import { ScriptLoaderService } from '../services/script-loader.service';
import { environment } from '../../environments/environment';
import {DomSanitizer, SafeResourceUrl, SafeUrl} from '@angular/platform-browser';
import {BrowserModule} from '@angular/platform-browser';
import {platformBrowserDynamic} from '@angular/platform-browser-dynamic';
import {ReactiveFormsModule,FormsModule,FormGroup,FormControl,Validators, Validator, FormBuilder} from '@angular/forms';

import {MatPaginator, MatSort, MatTableDataSource} from '@angular/material';
import {MatSortModule} from '@angular/material/sort';
import {MatDatepickerModule, MatDatepickerInputEvent} from '@angular/material/datepicker';

import {Observable} from 'rxjs/Observable';
import {merge} from 'rxjs/observable/merge';
import {of as observableOf} from 'rxjs/observable/of';
import {catchError} from 'rxjs/operators/catchError';
import {map} from 'rxjs/operators/map';
import {startWith} from 'rxjs/operators/startWith';
import {switchMap} from 'rxjs/operators/switchMap';

import { HttpClient } from '@angular/common/http';

import {Http,Headers,RequestOptions,Response} from '@angular/http';

import { MatDialog } from '@angular/material';

import { UploadvideoComponent } from '../uploadvideo/uploadvideo.component';

import { AddtestinomialComponent } from '../addtestinomial/addtestinomial.component';

import { MatTabChangeEvent } from '@angular/material';

import { ToastsManager } from 'ng2-toastr/ng2-toastr';

import { AgmCoreModule, MapsAPILoader } from '@agm/core';
import {} from '@types/googlemaps';

declare var jquery:any;
declare var $ :any;

@Component({
  selector: 'app-edit-profile',
  templateUrl: './edit-profile.component.html',
  styleUrls: ['./edit-profile.component.css'],
  entryComponents: [
    UploadvideoComponent
  ]
})
export class EditProfileComponent implements OnInit {
   mask: any[] = ['+', '6', '1', '-', '0', /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/];

   model:any={};
   datemodel:any={};
   buttonstat:boolean=true;
   totalpay = 0;
   userwallet = 0;
   earnings = 0;
	 firstimage : any;
	 sliderimages : any[]=[];
   profileinfo:any={};
   images:any;
   oldprofilepics:any;
   test:number=4;
   rowheight:number=300;
   innerWidth:any;
   imageurl = environment.imageurl;
   testimonials:any;
   comments:any=[];
   complaints:any;
   videos:String[]=[];
   sendvideos:any[]=[];
   packages=[];
   currentUrl:any;
   girlid:any;
   registertext : string='Save';
   errormsg:any;
   successmsg:any;
   showpass:boolean=false;
   langs: string[] = [
    'Female',
    'Male',
  ];
  uimages:any=[];
  myform: FormGroup;
  firstName: FormControl;
  lastName: FormControl;
  username:FormControl;
  weight:FormControl;
  height:FormControl;
  gender:FormControl;
  skype:FormControl;
  whatsapp:FormControl;
  viber:FormControl;
  wechat:FormControl;
  email: FormControl;
  password: FormControl;
 /* cpassword:FormControl;*/
  phone: FormControl;
  age: FormControl;
  sex: FormControl;
  location: FormControl;
  locstate: FormControl;
  service: FormControl;
  aboutme:FormControl;
  file:FormControl;
  enlock:FormControl;
  price:FormControl
  pausetime:FormControl;
  highlight:FormControl;
  profiledata:any ={};
  jsinc=0;
  weightvals=[];
  heightvals=[];
  tlength = 0;
  psel='';
  imgerror = false;
  timeleft = '';
  interval:any;
  imgprice:any;
  highlightfees:any;

  heights = [];

  selectedIndex:any;

  customStyle = {
    selectButton: {
      "background": "",
      "cursor": "pointer",
      "box-shadow":"none" ,
      "background-color":"#f8f8f8",
      "border":"#d0d0d0 dashed 1px",
      "color":"#9b9b9b"
    },
    clearButton: {
      "background-color": "#FFF",
      "border-radius": "25px",
      "color": "#000",
      "margin-left": "10px",
      "display":"none"
    },
    layout: {
     "border":"none",
     "background":"#fff"
    },
    previewPanel: {

    }
  }

  @ViewChild("hsearch") public searchElementRef: ElementRef;

  addresscomponents:any;
  loclat:any;
  loclong:any;
  suburb = '';

   //Transaction Log

  displayedColumns = ['trdate', 'trtime', 'type', 'money','remark'];

  transactiondata : GetTransactionData | null;

  dataSource = new MatTableDataSource();

  resultsLength = 0;
  isLoadingResults = true;
  isRateLimitReached = false;

  maxDate = new Date();

  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild(MatSort) sort: MatSort;


  constructor(private userService:UserService,
              private scriptLoaderService:ScriptLoaderService,
              private route: ActivatedRoute,
              private router: Router,
              private sanitizer: DomSanitizer,
              public dialog: MatDialog,
              public toastr: ToastsManager,
              public http:HttpClient,
              private mapsAPILoader: MapsAPILoader,
              private ngZone: NgZone) {
                if (localStorage.getItem("currentUser") === null) {
                    this.router.navigate(['login']);
                }

    router.events.subscribe((event: any) => {
          if (event instanceof NavigationEnd ) {
            this.currentUrl=event.url;
            var tgid = this.currentUrl.replace('/edit-girlprofile/','');
            tgid = tgid.split(';');
            this.girlid=tgid[0];
          }
        });


    // if(route.snapshot.queryParams['openpayment']){
		//     this.selectedIndex=2;
	  // } else if(route.snapshot.queryParams['openpreview']){
		//     this.selectedIndex=1;
	  // } else{
    //     this.selectedIndex=0;
    // }

    this.route.params.subscribe((params: Params) => {
      //console.log(params);
      // this will be called every time route changes
      // so you can perform your functionality here
      if(params['openpayment']){
  		    this.selectedIndex=2;
  	  } else if(params['openpreview']){
  		    this.selectedIndex=1;
  	  } else{
          this.selectedIndex=0;
      }
    });
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
        var componentForm = {
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
        let st_number= '';
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

        this.myform.controls['location'].setValue(loc);
        this.myform.controls['locstate'].setValue(state);

      });
    });
  });
}
  changepassword(){
    //console.log(this.showpass);
    if(this.showpass == true){
      this.showpass = false;
      this.myform.controls['password'].setValue('');
    } else if(this.showpass == false){
      this.showpass = true;
    }
    //console.log(this.showpass);
  }
 applyFilter() {
    var fromdate = this.datemodel.fromdate;
    var todate = this.datemodel.todate;

    console.log(fromdate, todate);

    this.paginator.pageIndex = 0;

    // this.getLoadTable();

  }

  addEvent(type: string, event: MatDatepickerInputEvent<Date>) {
   // this.events.push(`${type}: ${event.value}`);
   this.paginator.pageIndex = 0;

   this.loadTable();
  }

  lockunlock(i, event){
    // console.log(event.target.checked);
    // console.log('before',this.images[i]);
    this.images[i].lock = event.target.checked;
    // console.log('after',this.images[i]);

  }

  ngOnInit() {

    this.userService.getsetting().subscribe(
      data=>{
        this.imgprice = data.sdata.imgprice;
        this.highlightfees = data.sdata.highlightfees;
      }, error=>{

      }
    );

    for(var weight = 25;weight<201;weight++){
      this.weightvals.push(weight);
    }
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

    this.girlprofileinfo(this.girlid);
     this.createFormControls();
    this.createForm();
    this.myform.controls['pausetime'].setValue(false);
    this.innerWidth = window.innerWidth;
    if (innerWidth < 991) {
      this.test = 2;
      this.rowheight = 285;
    }
    if (innerWidth < 767) {
      this.test = 1;
      this.rowheight = 295;
    }
    this.model.oldp = 0;
    this.model.oldeval = 0;

this.loadTable();
  }

  loadTable(){
    // Transation Log starts
    console.log('before');
    this.transactiondata = new GetTransactionData(this.http);
    console.log('after');
    // If the user changes the sort order, reset back to the first page.
    // this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

    this.getLoadTable();
    //Transation Log ends
  }

  getLoadTable(){
    merge(this.paginator.page)
      .pipe(
        startWith({}),
        switchMap(() => {
          this.isLoadingResults = true;
          var fromdate:any;
          var finalfrom = 'empty';
          var todate:any;
          var finalto = 'empty';
          if(this.datemodel.fromdate){
            fromdate = new Date(this.datemodel.fromdate);
            var curr_date = fromdate.getDate();
            var curr_month = fromdate.getMonth() + 1; //Months are zero based
            var curr_year = fromdate.getFullYear();
            finalfrom = curr_date+'-'+curr_month+'-'+curr_year;
          }
          if(this.datemodel.todate){
            todate = this.datemodel.todate;
            fromdate = new Date(this.datemodel.todate);
            var curr_date = fromdate.getDate();
            var curr_month = fromdate.getMonth() + 1; //Months are zero based
            var curr_year = fromdate.getFullYear();
            finalto = curr_date+'-'+curr_month+'-'+curr_year;
          }
          return this.transactiondata!.getData(
            this.sort.active, this.sort.direction, this.paginator.pageIndex, this.paginator.pageSize, this.girlid, finalfrom, finalto);
        }),
        map(data => {
          // Flip flag to show that loading has finished.
          this.isLoadingResults = false;
          this.isRateLimitReached = false;
          this.resultsLength = data.total_count;
          console.log('resultsLength sec',this.resultsLength);
          return data.items;
        }),
        catchError(() => {
          this.isLoadingResults = false;
          // Catch if the API has reached its rate limit. Return empty data.
          this.isRateLimitReached = true;
          return observableOf([]);
        })
      ).subscribe(data => this.dataSource.data = data);
  }

  createFormControls() {
    this.firstName = new FormControl('', Validators.required);
    this.lastName = new FormControl('');//, Validators.required
    this.username=new FormControl('');//,Validators.required
    this.gender=new FormControl('',Validators.required);
    this.age = new FormControl('', Validators.required);
    this.sex = new FormControl('');
    this.location = new FormControl('', Validators.required);
    this.locstate = new FormControl('', Validators.required);
    this.service = new FormControl('', Validators.required);
    this.aboutme=new FormControl('',Validators.required);
    this.weight= new FormControl('');
    this.height= new FormControl('');
    this.pausetime=new FormControl('false');
    this.highlight=new FormControl('');
    this.skype= new FormControl('');
    this.whatsapp= new FormControl('');
    this.viber= new FormControl('');
    this.wechat= new FormControl('');
    this.enlock= new FormControl('');
    this.price= new FormControl('');
    this.file=new FormControl('');
    this.phone = new FormControl('', [Validators.required]);
    this.email = new FormControl('', [
      Validators.required,
      Validators.pattern("^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")
    ]);
    this.password = new FormControl('', [Validators.minLength(8)]);
    /*
     this.cpassword = new FormControl('', [
      Validators.required,
      Validators.minLength(8)
    ]);*/
  }

  createForm() {
    this.myform = new FormGroup({
      firstName: this.firstName,
      lastName: this.lastName,
      username:this.username,
      email: this.email,
      password: this.password,
      age: this.age,
      /*cpassword: this.cpassword,*/
      phone: this.phone,
      sex: this.sex,
      location: this.location,
      locstate: this.locstate,
      service: this.service,
      aboutme: this.aboutme,
      weight:this.weight,
      height:this.height,
      skype:this.skype,
      whatsapp:this.whatsapp,
      viber:this.viber,
      wechat:this.wechat,
      gender:this.gender,
      file:this.file,
      enlock:this.enlock,
      price:this.price,
      pausetime:this.pausetime,
      highlight:this.highlight
    });
  }

 showimage(index :number){
        this.firstimage=this.sliderimages[index].image;
 }

 ptimestat(){
   console.log(this.myform.controls['pausetime'].value);

   var ptime = this.myform.controls['pausetime'].value;
   if(ptime){
     var check = confirm("This will remove your profile from front and no one will be able to search you, Are you sure you want to pause the timer?");
     if(check){
       this.userService.changehighlight(this.girlid, ptime).subscribe(
         data=>{
           if(data.error){
             this.myform.controls['pausetime'].setValue(false);
             this.toastr.error('Could not update at this moment');
           } else{
             this.timeleft = 'Your profile is not live.';
             clearInterval(this.interval);
           }
         }, error=>{
           this.myform.controls['pausetime'].setValue(false);
           this.toastr.error('Could not update at this moment');
         }
       );
     } else{
       this.myform.controls['pausetime'].setValue(false);
     }
   } else{
     console.log('start');
     this.userService.changehighlight(this.girlid, ptime).subscribe(
        data=>{
          if(data.error){
            this.myform.controls['pausetime'].setValue(true);
            this.toastr.error('Could not update at this moment');
          } else{
            this.startcountdown(data.data.uptime, data.data.curdate);
          }

        }, error=>{
          this.myform.controls['pausetime'].setValue(true);
          this.toastr.error('Could not update at this moment');
        }
     );

   }
 }

girlprofileinfo(id:number){
  this.userService.getgirlprofile(id)
  .subscribe(
      data =>{
        if(data.error){

        }
        else{
          this.profileinfo=data.sdata;
          this.testimonials=data.testimonials;
          this.tlength = this.testimonials.length;
          this.comments=data.comments;

          this.complaints=data.complaints;
          this.oldprofilepics=this.profileinfo.profile_pic;
          this.images=this.profileinfo.profile_pic;
          this.userwallet = this.profileinfo.walletamount;
          this.earnings = this.profileinfo.earnings;

          this.timeleft = this.profileinfo.pausedat_time;
          if(this.profileinfo.pausetime == 1 && this.profileinfo.updated_time){
            //console.log('updatetime',this.profileinfo.updated_time);
            this.startcountdown(this.profileinfo.updated_time, this.profileinfo.curdate);
          } else if(this.profileinfo.pausedat_time != ''){
            //console.log('pausedtime',this.profileinfo.pausedat_time);
            // this.timeleft = this.profileinfo.pausedat_time;
            this.timeleft = 'Your profile is not live.';
            clearInterval(this.interval);
          } else{
            //console.log('timeleft','here');
            this.timeleft = 'Your profile is not live.';
          }
          //console.log(this.timeleft);
          //this.profiledata=this.profileinfo.profile_pic;
          //console.log(this.images);
          if(this.images.length > 0){
            this.firstimage=(this.imageurl+ this.images[0].image);
          }

          this.myform.controls['firstName'].setValue(this.profileinfo.first_name);
          this.myform.controls['lastName'].setValue(this.profileinfo.last_name);
          this.myform.controls['username'].setValue(this.profileinfo.username);
          this.myform.controls['email'].setValue(this.profileinfo.email);
          this.myform.controls['age'].setValue(this.profileinfo.age);
          this.myform.controls['phone'].setValue(this.profileinfo.phone);
          this.myform.controls['sex'].setValue(this.profileinfo.sexual);
          this.myform.controls['location'].setValue(this.profileinfo.location);
          this.myform.controls['locstate'].setValue(this.profileinfo.state);
          this.suburb = this.profileinfo.suburb;
          this.loclat = this.profileinfo.lat;
          this.loclong = this.profileinfo.lon;
          this.myform.controls['service'].setValue(this.profileinfo.service_location);
          this.myform.controls['aboutme'].setValue(this.profileinfo.aboutme);
          this.myform.controls['weight'].setValue(this.profileinfo.weight);
          this.myform.controls['height'].setValue(this.profileinfo.height);
          this.myform.controls['skype'].setValue(this.profileinfo.skype);
          this.myform.controls['whatsapp'].setValue(this.profileinfo.whatsapp);
          this.myform.controls['viber'].setValue(this.profileinfo.viber);
          this.myform.controls['wechat'].setValue(this.profileinfo.wechat);
          this.myform.controls['gender'].setValue(this.profileinfo.gender);
          if(this.profileinfo.pausetime == 1){
            this.myform.controls['pausetime'].setValue(false);
          } else{
            this.myform.controls['pausetime'].setValue(true);
          }

          this.myform.controls['highlight'].setValue(this.profileinfo.highlight);

          this.userService.getPackages(this.profileinfo.gender)
            .subscribe(
              data =>{
                this.packages = data.sdata.packages;
              },
              error =>{

              });
          //this.videos=this.profileinfo.videos;
          //this.sendvideos = this.profileinfo.videos;
          this.sendvideos = [];
          for(let element of this.profileinfo.videos){
            var video:any={};
            video.videosource = element.source;
            video.type = element.type;
            this.sendvideos.push(video);
          }
          console.log(this.sendvideos);

          this.videos = [];

          this.sliderimages = [];
          for(let element of this.profileinfo.videos)
                  {
                    var video:any={};
                    video.videosource =this.sanitizer.bypassSecurityTrustResourceUrl(element.source);
                    video.type = element.type;
                    this.videos.push(video);
                  }
                  console.log(this.videos);
          if(this.images.length > 0)
          {
            console.log(this.images);
            for(let image of this.images)
            {
              let simg:any={};
              simg.image = this.imageurl + image.image;
              simg.price = image.price;
              simg.lock = image.lock;
              this.sliderimages.push(simg);
            }
          }
        }
      },
      error =>{

      });
  }

  startcountdown(datestart, curdate){
    var starttime = datestart;
    // console.log('starttime',starttime);
    // Set the date we're counting down to
    var countDownDate = new Date(starttime).getTime();
    // console.log('startfrom',countDownDate)
    var now = new Date(curdate).getTime();
    // Update the count down every 1 second
    this.interval = setInterval(function() {

        // Get todays date and time
        now = now+1000;
        // var now = new Date().getTime();
        // console.log('now',now);
        // Find the distance between now an the count down date
        var distance = countDownDate - now;
        // console.log(distance)
        // 41960987
        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Output the result in an element with id="demo"
        // this.timeleft = hours + "h "
        // + minutes + "m " + seconds + "s ";
        var tleft = hours + "h " + minutes + "m " + seconds + "s ";
        $('#timeleft').text(tleft+' Left');
        // console.log(tleft);
        // If the count down is over, write some text
        // if (distance < 0) {
        //     clearInterval(x);
        //     document.getElementById("demo").innerHTML = "EXPIRED";
        // }
    }, 1000);
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

  update(formdata:any){
    // console.log(this.myform)
    if(this.myform.valid){
//console.log(this.myform.value.enlock);
    for (var key in this.profiledata) {
      // skip loop if the property is from prototype
      //console.log(this.profiledata[key]);
      //this.profiledata[key].price = this.myform.value.price;
      this.profiledata[key].enlock = this.myform.value.enlock;
    }
    //console.log('after');
    //console.log(this.profiledata);

    if(this.imgerror == false){
    this.registertext="Saving..";
  formdata.images=this.profiledata;
  formdata.oldimages=this.images;
  //console.log(this.videos);
  //console.log(this.sendvideos);
  var videos = [];

  for(var i=0;i<this.sendvideos.length;i++)
          {
            var video:any={};
            video.source = this.sendvideos[i].videosource;
            video.type = this.sendvideos[i].type;

            videos.push(video);
          }
formdata.videos = videos;
formdata.dateremains = $('#timeleft').text();

formdata.suburb = this.suburb;
formdata.lat = this.loclat;
formdata.lon = this.loclong;

       this.userService.updateprofile(formdata,this.girlid)
    .subscribe(
      data =>{
        if(data.error)
        {
          this.registertext="Save";
          this.errormsg='';
          this.toastr.error(data.message);
          this.successmsg = '';
        }
        else
        {
          this.myform.controls['price'].setValue('');
          this.myform.controls['enlock'].setValue(false);
          this.successmsg='';
          this.toastr.success(data.message);
          this.errormsg = '';
          this.showpass = false;
          this.myform.controls['password'].setValue('');
          //this.myform.reset();
          //this.profiledata={};
          $(".img-ul-clear").trigger('click');

          for(var i=0;i<2;i++){
              $(".img-ul-clear").eq( i ).trigger('click');
          }
          this.girlprofileinfo(this.girlid);

          this.registertext="Save";
        }
      },
      error =>{
        this.errormsg='';
        this.registertext="Save";
        this.toastr.error("Sorry, something went wrong. Please try again");
      });
    //console.log(formdata);
  } else{
  this.toastr.error('Please enter price amount for locked image');
}
} else{
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
  imagedata.price=this.imgprice;
  imagedata.enlock=this.myform.value.enlock;
  //console.log(imagedata);
  //this.myform.controls['price'].setValue('');
  this.myform.controls['enlock'].setValue(false);
  this.profiledata[file.file.name]=imagedata;
  console.log(this.profiledata);
}

addimages(){
  console.log(this.profiledata);

  for (var key in this.profiledata) {
    // skip loop if the property is from prototype
    //console.log(this.profiledata[key]);
    //this.profiledata[key].price = this.myform.value.price;
    this.profiledata[key].enlock = this.myform.value.enlock;
  }

  var newimg:any={};
  newimg.images = this.profiledata;
  this.userService.changeimage(newimg).subscribe(
    data=>{
      if(data.newimage){
        this.images.push(data.imgsare);
        this.sliderimages = [];
        if(this.images.length > 0)
        {
          console.log(this.images);
          for(let image of this.images)
          {
            let simg:any={};
            simg.image = this.imageurl + image.image;
            simg.price = image.price;
            simg.lock = image.lock;
            this.sliderimages.push(simg);
          }
        }
        for(var i=0;i<2;i++){
            $(".img-ul-clear").eq( i ).trigger('click');
        }
      }
    }, error=>{

    }
  );

}

onRemoved(file: any) {

  // do some stuff with the removed file.
  delete this.profiledata[file.file.name];
  //console.log(this.profiledata);
}

onUploadStateChanged(state: boolean) {
  //console.log(state);
  if(state == false){
    if(this.sliderimages.length == 5){
      this.toastr.error('Only 5 images are allowed, please remove an image to add a new one.');
      console.log('clear',this.sliderimages.length);
      $(".img-ul-clear").trigger('click');
    }

  }
  //console.log(this.sliderimages.length);
  //console.log(JSON.stringify(state));
}

firstiUpload(file: any) {
//console.log(this.myform.value.price);
//console.log(this.myform.value.enlock);
let imagedata:any={};
var newimg:any = {};
var fimages:any = {};
console.log(this.images[0]);
var oldimgp = this.images[0].price;
var oldimgl = this.images[0].lock;

imagedata.data=JSON.stringify(file.src);
imagedata.price=oldimgp;
imagedata.enlock=oldimgl;
//console.log(imagedata);

newimg[file.file.name]=imagedata;
fimages.images = newimg;
this.userService.changeimage(fimages)
  .subscribe(
    data =>{
      if(data.error)
      {
        this.errormsg="Sorry, something went wrong. Please try again";
      }
      else{
        this.errormsg="";
        console.log(data);
        this.images.splice(0,1,data.imgsare);
        this.firstimage=(this.imageurl+ this.images[0].image);
      }
    },
    error =>{

    });
$(".img-ul-clear").eq( 0 ).trigger('click');
}

firstiRemoved(file: any) {

// do some stuff with the removed file.
delete this.profiledata[file.file.name];
//console.log(this.profiledata);
}

firstiStateChange(state: boolean) {
//console.log(JSON.stringify(state));
}

 cancel(){
    //this.dialogRef.close();
  }
  fileChange($event) {
    //this.myform.controls['file'].setValue($event.target.files);
   // console.log($event.target.files);
}
onResize(event : any) {
    const element = event.target.innerWidth;
    if (element < 991) {
      this.test = 2;
      this.rowheight = 285;
    }
    if (element < 767) {
      this.test = 1;
      this.rowheight = 295;
    }

  }

  deleteimg(val){
    var r = confirm("Are you sure you want to delete this image?");
    if(r == true){
      this.images.splice(val, 1);
      this.sliderimages = [];
      for(let image of this.images)
      {
        let simg:any={};
        simg.image = this.imageurl + image.image;
        simg.price = image.price;
        simg.lock = image.lock;
        this.sliderimages.push(simg);
      }
    } else{

    }
  }

  deletevideo(val){
    var r = confirm("Are you sure you want to delete this video?");
    if(r == true){
      this.videos.splice(val, 1);
      this.sendvideos.splice(val,1)
    } else{

    }
  }

  openuploadvideo(){
    const dialogRef = this.dialog.open(UploadvideoComponent,{
   		// data: {type:'regtype'},
    		height: '600px'
    	});
   	dialogRef.afterClosed().subscribe(result => {
      console.log(result);
      if(result !== undefined){
        //console.log(this.videos);
        var videourl:any;
        var svurl = '';
        if(result.videotype == 'youtube'){
          if(result.videolink.includes('embed')){
            svurl = result.videolink;
            videourl = this.sanitizer.bypassSecurityTrustResourceUrl(result.videolink);
          } else if(result.videolink.includes('watch')){
            //https://www.youtube.com/watch?v=lQx6YBtQZbw
            var vurl = result.videolink.split('v=');
            var vl = 'https://www.youtube.com/embed/'+vurl[1];
            svurl = vl;
            videourl = this.sanitizer.bypassSecurityTrustResourceUrl(vl);
          } else{
            var link = result.videolink.split('/');
            //console.log(link);
            var vl = 'https://www.youtube.com/embed/'+link[3];
            svurl = vl;
            videourl = this.sanitizer.bypassSecurityTrustResourceUrl(vl);
            //https://youtu.be/lQx6YBtQZbw
          }
        }
        if(result.videotype == 'vimeo'){
          if(result.videolink.includes('player')){
            svurl = result.videolink;
            videourl = this.sanitizer.bypassSecurityTrustResourceUrl(result.videolink);
          } else{
            var links = result.videolink.split('/');
            console.log(links.length);
            console.log(links);
            if(links.length > 4){
              var vl = 'https://player.vimeo.com/video/'+links[5];
              svurl = vl;
              videourl = this.sanitizer.bypassSecurityTrustResourceUrl(vl);
            } else{
              //console.log(links);
              var vl = 'https://player.vimeo.com/video/'+links[3];
              svurl = vl;
              videourl = this.sanitizer.bypassSecurityTrustResourceUrl(vl);
            }
          }
        }
        if(result.videotype == 'upload'){
          svurl = result.videolink;
          videourl = this.sanitizer.bypassSecurityTrustResourceUrl(result.videolink);
        }
        var nvobj:any={};
        var svobj:any={};

        nvobj.type = result.videotype;
        nvobj.videosource = videourl;
        svobj.type = result.videotype;
        svobj.videosource = svurl;

        this.videos.push(nvobj);
        this.sendvideos.push(svobj);
        console.log(this.videos);
        //https://www.youtube.com/watch?v=C0DPdy98e4c
        //https://vimeo.com/56282283
      }
   	});
  }

  deletecomplaint(pos){
    var c = confirm("Sure you want to delete this complaint?");
    if(c == true){
      var cid = this.complaints[pos].id;
      this.userService.deletecomplaint(cid).subscribe(
        data=>{
          if(data.error){
            this.toastr.error(data.message);
          } else{
            this.toastr.success(data.message);
            this.complaints.splice(pos, 1);
          }

        }, error=>{
          this.toastr.error('Something went wrong');
        }
      );
    }
  }

  deletecomment(pos){
    var c = confirm("Sure you want to delete this comment?");
    if(c == true){
      var cid = this.comments[pos].id;
      this.userService.deletecomment(cid).subscribe(
        data=>{
          if(data.error){
            this.toastr.error(data.message);
          } else{
            this.toastr.success(data.message);
            this.comments.splice(pos, 1);
          }

        }, error=>{
          this.toastr.error('Something went wrong');
        }
      );
    }
  }

  opentestinomial(){

    const dialogRef = this.dialog.open(AddtestinomialComponent,{
   		 data: {userid:this.girlid},
    		height: '600px'
    	});

      dialogRef.afterClosed().subscribe(result => {
        this.userService.getTestimonials(this.girlid)
          .subscribe(
            data =>{
              if(data.error)
              {

              }
              else{
                this.testimonials=data.testimonials;
                this.tlength = this.testimonials.length;
              }
            },
            error =>{

            });
        //this.testimonials.push(result.testimonials);
      });
  }

  selectpackage(id, amount, bonus){
    this.jsinc = this.jsinc+1;
    this.buttonstat = false;
    this.totalpay = this.totalpay - this.model.oldp;
    this.totalpay = this.totalpay + parseFloat(amount);
    this.model.oldp = parseFloat(amount);

    $('#bonus').val(bonus);

    if(this.jsinc == 1){
      this.scriptLoaderService.load('app-edit-profile', 'assets/js/paymetinc.js');
    }

    var oldp = $('#oldpsel').val();
    if(oldp != ''){
      $('#packagediv'+oldp).removeClass('selected');
    }
    $('#packagediv'+id).addClass('selected');

    $('#oldpsel').val(id);
  }

  addextra(){
    this.totalpay = this.totalpay-this.model.oldeval;
    if(this.model.manualamount > 0){
      this.totalpay = this.totalpay+parseFloat(this.model.manualamount);
      this.model.oldeval = parseFloat(this.model.manualamount);
      this.jsinc = this.jsinc+1;
      if(this.jsinc == 1){
        this.scriptLoaderService.load('app-edit-profile', 'assets/js/paymetinc.js');
      }
    }

  }

  addhighlight(){
    console.log(this.model.highlightpay);
    if(this.model.highlightpay){
      this.totalpay = this.totalpay+this.highlightfees;
    } else{
      this.totalpay = this.totalpay-this.highlightfees;
    }
    this.myform.controls['highlight'].setValue(this.model.highlightpay);
  }

  loadpayjs(event: MatTabChangeEvent){
    // this.router.navigate(['/edit-girlprofile/'+this.girlid]);
    //alert('load');
    //console.log(event);
    this.jsinc = 0;
    $('#paymenterror').html('');
    $('#paymentsuccess').html('');
    this.model.highlightpay=false;
    this.model.manualamount='';
    this.totalpay=0;
    this.model.oldp=0;
    this.model.oldeval=0;
    var oldp = $('#oldpsel').val();
    if(oldp != ''){
      $('#packagediv'+oldp).removeClass('selected');
    }
  }

  loadinnerpay(event: MatTabChangeEvent){
    this.jsinc = 0;
    $('#paymenterror').html('');
    $('#paymentsuccess').html('');
    this.model.highlightpay=false;
    this.model.manualamount='';
    this.totalpay=0;
    this.model.oldp=0;
    this.model.oldeval=0;
    var oldp = $('#oldpsel').val();
    if(oldp != ''){
      $('#packagediv'+oldp).removeClass('selected');
    }
    console.log(event.index, event.index);
    if(event.index == 1){
      this.loadTable();
    }
  }

  ngOnDestroy(){
    clearInterval(this.interval);
  }

}

export interface ApiReturn {
  items: TableForm[];
  total_count: number;
}

export interface TableForm {
  trdate: string;
  trtime: string;
  type: string;
  money: string;
  remark: string;
}

export class GetTransactionData {
  constructor(private http: HttpClient) {}

  getData(sort: string, order: string, page: number, perpage:any, userid:any, fromdate:any, todate:any): Observable<ApiReturn> {
    //const href = 'https://api.github.com/search/issues';
    const requestUrl = environment.apiUrl+'usertransactions/'+sort+'/'+order+'/'+page+'/'+perpage+'/'+userid+'/'+fromdate+'/'+todate;

    return this.http.get<ApiReturn>(requestUrl);
  }
}
