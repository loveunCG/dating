import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute,NavigationEnd } from '@angular/router';
import { UserService } from '../services/index';
import { environment } from '../../environments/environment';
import {DomSanitizer, SafeResourceUrl, SafeUrl} from '@angular/platform-browser';
import { MatDialog } from '@angular/material';
import { SearchtestinomialComponent } from '../searchtestinomial/searchtestinomial.component';

import {MatPaginator, MatSort, MatTableDataSource} from '@angular/material';
import {MatTabsModule} from '@angular/material/tabs';
import { MatTabChangeEvent } from '@angular/material';

import { ToastsManager } from 'ng2-toastr/ng2-toastr';

import { UnlockpictureComponent } from '../unlockpicture/unlockpicture.component';
import { OpenimageComponent } from '../openimage/openimage.component';

declare var jquery:any;
declare var $ :any;

@Component({
  selector: 'app-single-profile',
  templateUrl: './single-profile.component.html',
  styleUrls: ['./single-profile.component.css']
})
export class SingleProfileComponent implements OnInit {
	 firstimage : any;
	 sliderimages : any[]=[];
   profileinfo:any={};
   images:any;
   imageurl = environment.imageurl;
   testimonials:any;
   comments:any;
   complaints:any;
   videos:String[]=[];
   ifloggedin = false;
   userid:any;
   commentmodel:any={};
   complaintmodel:any={};
   currentUrl:any;
   girlid:number;
   addtext='Submit';
   commerror=false;
   comperror=false;
   namecomperror=false;
   namecommerror=false;
   imgprice:any;
   loggedintype:any;
   actopenimg:any;
   imgtimeout:any;
   opencomment=true;
   opencomplaint=false;

   ietrue:boolean=false;

  constructor(private userService:UserService,
              private route: ActivatedRoute,
              private router: Router,
              private sanitizer: DomSanitizer,
              public dialog: MatDialog,
              public toastr: ToastsManager) {
    router.events.subscribe((event: any) => {
          if (event instanceof NavigationEnd ) {
            this.currentUrl=event.url;
            this.girlid=this.currentUrl.replace('/girl/','');
          }
        });

        if(navigator.userAgent.indexOf(".NET4.0E") != -1 || navigator.userAgent.indexOf(".NET4.0C") != -1){
            // console.log(navigator.userAgent);
            this.ietrue = true;
        }
  	//this.firstimage='./assets/images/Webp.jpg';
   //this.sliderimages=['./assets/images/Webp.jpg','./assets/images/Webp1.jpg','./assets/images/Webp3.jpg','./assets/images/Webp1.jpg']

   let imgele:any={};
   imgele.image='';
   imgele.lock=1;
   this.firstimage=imgele;
}

  ngOnInit() {
    window.scrollTo(0, 0);
    this.userService.getsetting().subscribe(
      data=>{
        this.imgprice = data.sdata.imgprice;
      }, error=>{

      }
    );
    this.videos.length = 0;
    this.girlprofileinfo(this.girlid);
    if (localStorage.getItem("currentUser") === null) {
      this.ifloggedin = false;
    } else{
      this.ifloggedin = true;
      var balance = 0;
      var currentUser = JSON.parse(localStorage.getItem("currentUser"));
      var user = currentUser;
      this.userid = user.id;
      this.loggedintype = user.gender;
    }
  }
 showimage(index :number){
        this.firstimage=this.sliderimages[index];
      }

  unlockimg(){

    clearTimeout(this.imgtimeout);

    var price = this.imgprice;
    var unlockid = this.girlid;


    if (localStorage.getItem("currentUser") === null) {
        this.toastr.error('To Unlock Photo please Register and Login.');
    } else{
      var balance = 0;

      this.userService.checkWallet(this.userid)
     .subscribe(
       data =>{
         if(data.error){

         }
         else
         {
           balance = data.stat.amount;
           if(balance > price){
             const dialogRef = this.dialog.open(UnlockpictureComponent,{
                data: {uid:this.userid, amount:price, unlockid:unlockid, username:this.profileinfo.name},
                height: '250px',width:'30%'
              });
              dialogRef.afterClosed().subscribe(result => {
                //console.log(result);
                if(result == 1){
                  var openimg = '';

                  this.firstimage.lock=false;

                  openimg = this.firstimage.image;
                  setTimeout(()=>{ this.firstimage.lock=true }, 30000);

                  // const dialogRef = this.dialog.open(OpenimageComponent,{
                  //   data: {image:openimg},
                  //     height: '100%',width:'40%'
                  //   });
                  this.actopenimg = openimg;
                    $('#imgmodel').show();
                    this.imgtimeout = setTimeout(()=>{ $('#imgmodel').hide();this.actopenimg=''; }, 30000);
                }

              });

           } else{
             this.toastr.error('Not enough balance in wallet, add money to wallet by choosing a package');
           }
         }
       },
       error =>{
         this.toastr.error('Something went wrong');
       });


    }

  }

  closeimg(){
    $('#imgmodel').hide();
  }

  changetabs(event: MatTabChangeEvent){
    if(event.index == 0){
      this.opencomment = true;
      this.opencomplaint = false;
    } else{
      this.opencomment = false;
      this.opencomplaint = true;
    }
  }

girlprofileinfo(id:number){
  this.userService.getgirlprofile(id)
    .subscribe(
      data =>{
        if(data.error)
        {

        }
        else{
          this.profileinfo=data.sdata;
          this.testimonials=data.testimonials;
          this.comments=data.comments;
          this.complaints=data.complaints;
          //console.log(this.comments);
          this.images=this.profileinfo.profile_pic;

          let felement:any={};
          felement.image = this.imageurl+ this.images[0].image;
          felement.lock = this.images[0].lock;

          this.firstimage=felement;
          //console.log(this.firstimage);
          //this.videos=this.profileinfo.videos;
          //console.log(this.videos.length);
          for(let element of this.profileinfo.videos)
                  {
                    element.source =this.sanitizer.bypassSecurityTrustResourceUrl(element.source);
                    this.videos.push(element);
                  }
          //console.log(this.videos.length);

          for(let image of this.images)
          {
            //console.log(image.image);
            var imge:any={};

            imge.image = this.imageurl+ image.image;
            imge.lock = image.lock;

            this.sliderimages.push(imge);
          }
          //console.log(this.sliderimages);
        }
      },
      error =>{

      });
  }



  submitcomment(){
    var comm = this.commentmodel.comment;
    if(comm !== undefined){
      if(comm.length > 0){
        this.commerror = false;
        if(!this.ifloggedin){
          if(this.commentmodel.username !== undefined){
            if(this.commentmodel.username.length < 1){
              this.namecommerror = true;
              return;
            }
          } else{
            this.namecommerror = true;
            return;
          }
        }
        this.namecommerror = false;
          this.commentmodel.uid=this.userid;
          this.commentmodel.girlid = this.girlid;
          this.userService.addcomment(this.commentmodel)
            .subscribe(
              data =>{
                if(data.error)
                {
                    this.toastr.error('Something went wrong');
                }
                else{
                  this.toastr.success('Comment added successfully');
                  this.commentmodel.comment = '';
                  this.commentmodel.username = '';
                  this.userService.getcomments(this.girlid).subscribe(
                    data=>{
                      if(data.error){

                      } else{
                        this.comments = data.comments;
                      }
                    },error=>{

                    }
                  );
                }
              },
              error =>{
                this.toastr.error('Something went wrong');
              });

      } else{
        this.commerror = true;
      }
    } else{
      this.commerror = true;
    }
  }

  submitcomplaint(){
    var comm = this.complaintmodel.complaint;
    if(comm !== undefined){
      if(comm.length > 0){
          this.comperror = false;
          if(!this.ifloggedin){
            if(this.complaintmodel.username !== undefined){
              if(this.complaintmodel.username.length < 1){
                this.namecomperror = true;
                return;
              }
            } else{
              this.namecomperror = true;
              return;
            }
          }
          this.namecomperror = false;
          this.complaintmodel.uid=this.userid;
          this.complaintmodel.girlid = this.girlid;
          this.userService.addcomplaint(this.complaintmodel)
            .subscribe(
              data =>{
                if(data.error)
                {
                  this.toastr.error('Something went wrong');
                }
                else{
                  this.toastr.success('Complaint submitted');
                  this.complaintmodel.complaint = '';
                  this.complaintmodel.username = '';
                  this.userService.getcomplaints(this.girlid).subscribe(
                    data=>{
                      if(data.error){

                      } else{
                        this.complaints = data.complaints;
                      }
                    }, error=>{

                    }
                  );
                }
              },
              error =>{
                this.toastr.error('Something went wrong');
              });
      } else{
        this.comperror = true;
      }
    } else{
      this.comperror = true;
    }
  }

  opensearch(){
    const dialogRef = this.dialog.open(SearchtestinomialComponent,{
   		 data: {gid:this.girlid},
    		height: '600px'
    	});
  }
}
