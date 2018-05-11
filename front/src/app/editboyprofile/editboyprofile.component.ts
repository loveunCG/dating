import { Component, OnInit,ViewChild } from '@angular/core';
import { Router, ActivatedRoute,NavigationEnd, Params } from '@angular/router';
import { UserService } from '../services/index';
import { environment } from '../../environments/environment';
import {DomSanitizer, SafeResourceUrl, SafeUrl} from '@angular/platform-browser';
import {BrowserModule} from '@angular/platform-browser';
import {platformBrowserDynamic} from '@angular/platform-browser-dynamic';
import {ReactiveFormsModule,FormsModule,FormGroup,FormControl,Validators,FormBuilder} from '@angular/forms';
import {MatPaginator, MatSort, MatTableDataSource} from '@angular/material';
import {MatDatepickerModule} from '@angular/material/datepicker';

import {Observable} from 'rxjs/Observable';
import {merge} from 'rxjs/observable/merge';
import {of as observableOf} from 'rxjs/observable/of';
import {catchError} from 'rxjs/operators/catchError';
import {map} from 'rxjs/operators/map';
import {startWith} from 'rxjs/operators/startWith';
import {switchMap} from 'rxjs/operators/switchMap';
import { MatDialog } from '@angular/material';

import { HttpClient } from '@angular/common/http';

import { UploadvideoComponent } from '../uploadvideo/uploadvideo.component';

import { AddtestinomialComponent } from '../addtestinomial/addtestinomial.component';
import { MatTabChangeEvent } from '@angular/material';
import { ScriptLoaderService } from '../services/script-loader.service';

import { ToastsManager } from 'ng2-toastr/ng2-toastr';

declare var jquery:any;
declare var $ :any;

@Component({
  selector: 'app-edit-profile',
  templateUrl: './editboyprofile.component.html',
  styleUrls: ['./editboyprofile.component.css'],
  entryComponents: [
    UploadvideoComponent
  ]
})
export class EditboyprofileComponent implements OnInit {
   mask: any[] =[ /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/];
   packages=[];
   model:any={};
   datemodel:any={};
   buttonstat:boolean=true;
   totalpay = 0;
   userwallet = 0;
   jsinc=0;

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
   comments:any;
   videos:String[]=[];
   sendvideos:String[]=[];
   currentUrl:any;
   girlid:number;
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
  service: FormControl;
  aboutme:FormControl;
  file:FormControl;
  enlock:FormControl;
  price:FormControl
  pausetime:FormControl;
  highlight:FormControl;
  profiledata:any ={};

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
              public http:HttpClient) {
                if (localStorage.getItem("currentUser") === null) {
                    this.router.navigate(['login']);
                }

    router.events.subscribe((event: any) => {
          if (event instanceof NavigationEnd ) {
            this.currentUrl=event.url;
            var tgid = this.currentUrl.replace('/edit-boyprofile/','');
            tgid = tgid.split(';');
            this.girlid=tgid[0];
          }
        });
  	//this.firstimage='./assets/images/Webp.jpg';
   //this.sliderimages=['./assets/images/Webp.jpg','./assets/images/Webp1.jpg','./assets/images/Webp3.jpg','./assets/images/Webp1.jpg']
    // Transation Log

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
ngAfterViewInit() {
    // this.dataSource.paginator = this.paginator;
    // this.dataSource.sort = this.sort;
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

  ngOnInit() {
    this.boyprofileinfo(this.girlid);
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
  }

  createFormControls() {
    this.firstName = new FormControl('', Validators.required);
    this.lastName = new FormControl('');//, Validators.required

    this.gender=new FormControl('',Validators.required);
    this.age = new FormControl('', Validators.required);

    this.pausetime=new FormControl('false');
    this.highlight=new FormControl('');

    this.enlock= new FormControl('', Validators.required);
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

      email: this.email,
      password: this.password,
      age: this.age,
      /*cpassword: this.cpassword,*/
      phone: this.phone,

      gender:this.gender,

      pausetime:this.pausetime,
      highlight:this.highlight
    });
  }

  applyFilter() {
     var fromdate = this.datemodel.fromdate;
     var todate = this.datemodel.todate;

     console.log(fromdate, todate);

     this.paginator.pageIndex = 0;

     this.getLoadTable();

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

 showimage(index :number)
      {
        this.firstimage=this.sliderimages[index];
      }
boyprofileinfo(id:number)
{
  this.userService.getboyprofile(id)
    .subscribe(
      data =>{
        if(data.error)
        {

        }
        else{
          this.profileinfo=data.sdata;
          this.testimonials=data.testimonials;
          this.comments=data.comments;
          this.oldprofilepics=this.profileinfo.profile_pic;
          this.images=this.profileinfo.profile_pic;
          this.userwallet = this.profileinfo.walletamount;
          //this.profiledata=this.profileinfo.profile_pic;
          //console.log(this.images);
          if(this.images.length > 0)
          {
            this.firstimage=(this.imageurl+ this.images[0].image);

          }
          // console.log(this.images);
          this.myform.controls['firstName'].setValue(this.profileinfo.first_name);
          this.myform.controls['lastName'].setValue(this.profileinfo.last_name);

          this.myform.controls['email'].setValue(this.profileinfo.email);
          this.myform.controls['age'].setValue(this.profileinfo.age);
          this.myform.controls['phone'].setValue(this.profileinfo.phone);

          this.myform.controls['gender'].setValue(this.profileinfo.gender);
          this.myform.controls['pausetime'].setValue(this.profileinfo.pausetime);
          this.myform.controls['highlight'].setValue(this.profileinfo.highlight);

          this.userService.getPackages(this.profileinfo.gender)
            .subscribe(
              data =>{
                this.packages = data.sdata.packages;
              },
              error =>{

              });
          //this.videos=this.profileinfo.videos;
          this.sendvideos = this.profileinfo.videos;

          // for(let element of this.profileinfo.videos)
          //         {
          //           element.url =this.safeurl.(element.url);
          //           this.sendvideos.push(element);
          //         }
          //console.log(this.sendvideos);
          this.videos = [];
          this.sliderimages = [];
          for(let element of this.profileinfo.videos)
                  {
                    element.url =this.sanitizer.bypassSecurityTrustResourceUrl(element.url);
                    this.videos.push(element);
                  }
                  //console.log(this.videos);
          if(this.images.length > 0)
          {
            //console.log(this.images);
            for(let image of this.images)
            {
              this.sliderimages.push(this.imageurl+ image.image);
            }
          }
        }
      },
      error =>{

      });
  }
  update(formdata:any)
  {
    if(this.myform.valid){
    this.registertext="Saving..";
  formdata.images=this.profiledata;
  formdata.oldimages=this.images;
  formdata.videos = this.sendvideos;
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
          this.successmsg='';
          this.toastr.success(data.message);
          this.errormsg = '';
          this.showpass = false;
          this.myform.controls['password'].setValue('');
          //this.myform.reset();
          //this.profiledata={};
          $(".img-ul-clear").trigger('click');
          this.registertext="Save";

          for(var i=0;i<2;i++){
              $(".img-ul-clear").eq( i ).trigger('click');
          }

          this.boyprofileinfo(this.girlid);


        }
      },
      error =>{
        this.errormsg='';
        this.toastr.error("Sorry, something went wrong. Please try again");
        this.registertext="Save";
      });
    //console.log(formdata);
  } else{
  console.log(this.myform);
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
  imagedata.price=this.myform.value.price;
  imagedata.enlock=this.myform.value.enlock;
  //console.log(imagedata);
  this.myform.controls['price'].setValue('');
  this.myform.controls['enlock'].setValue(false);
  this.profiledata[file.file.name]=imagedata;
}

onRemoved(file: any) {

  // do some stuff with the removed file.
  delete this.profiledata[file.file.name];
  //console.log(this.profiledata);
}

onUploadStateChanged(state: boolean) {
  //console.log(JSON.stringify(state));
}

firstiUpload(file: any) {
//console.log(this.myform.value.price);
//console.log(this.myform.value.enlock);
let imagedata:any={};
var newimg:any = {};
var fimages:any = {};
console.log(this.images[0]);
var oldimgp = 0;
var oldimgl = 0;
if(this.images[0]){
  oldimgp = this.images[0].price;
  oldimgl = this.images[0].lock;
} else{
  oldimgp = 0;
  oldimgl = 0;
}


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
        this.sliderimages.push(this.imageurl+ image.image);
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
      //console.log(result);
      if(result !== undefined){
        //console.log(this.videos);
        var videourl:any;
        var svurl = result.videolink;
        if(result.videotype == 'youtube'){
          if(result.videolink.includes('embed')){
            videourl = this.sanitizer.bypassSecurityTrustResourceUrl(result.videolink);
          } else if(result.videolink.includes('watch')){
            //https://www.youtube.com/watch?v=lQx6YBtQZbw
            var vurl = result.videolink.split('v=');
            var vl = 'https://www.youtube.com/embed/'+vurl[1];
            videourl = this.sanitizer.bypassSecurityTrustResourceUrl(vl);
          } else{
            var link = result.videolink.split('/');
            //console.log(link);
            var vl = 'https://www.youtube.com/embed/'+link[3];
            videourl = this.sanitizer.bypassSecurityTrustResourceUrl(vl);
            //https://youtu.be/lQx6YBtQZbw
          }
        }
        if(result.videotype == 'vimeo'){
          if(result.videolink.includes('player')){
            videourl = this.sanitizer.bypassSecurityTrustResourceUrl(result.videolink);
          } else{
            var links = result.videolink.split('/');
            //console.log(links.length);
            //console.log(links);
            if(links.length > 4){
              var vl = 'https://player.vimeo.com/video/'+links[5];
              videourl = this.sanitizer.bypassSecurityTrustResourceUrl(vl);
            } else{
              //console.log(links);
              var vl = 'https://player.vimeo.com/video/'+links[3];
              videourl = this.sanitizer.bypassSecurityTrustResourceUrl(vl);
            }
          }
        }
        if(result.videotype == 'upload'){
          videourl = this.sanitizer.bypassSecurityTrustResourceUrl(result.videolink);
        }
        var nvobj:any={};
        var svobj:any={};

        nvobj.type = result.videotype;
        nvobj.url = videourl;
        svobj.type = result.videotype;
        svobj.url = svurl;

        this.videos.push(nvobj);
        this.sendvideos.push(svobj);
        //console.log(this.videos);
      }
   	});
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
      this.totalpay = this.totalpay+10;
    } else{
      this.totalpay = this.totalpay-10;
    }
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
