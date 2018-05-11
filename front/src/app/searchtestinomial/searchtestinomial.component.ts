import { Component, OnInit, Inject } from '@angular/core';
import {MatDialog, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import { UserService } from '../services/index';

@Component({
  selector: 'app-searchtestinomial',
  templateUrl: './searchtestinomial.component.html',
  styleUrls: ['./searchtestinomial.component.css']
})
export class SearchtestinomialComponent implements OnInit {
  mask: any[] =[ /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/];
model:any={};
userid:any;
errormsg='';
successmsg='';
subtext='Search';
testinomials:any=[];
  constructor(public dialogRef: MatDialogRef<SearchtestinomialComponent>,@Inject(MAT_DIALOG_DATA) public data: any, private userService:UserService) {

    this.userid = data.gid;
    //console.log(this.userid);

  }

  ngOnInit() {
  }

  submittestinomy(form:any){
    this.subtext='Searching';
    this.model.userid = this.userid;
    this.userService.searchtestinomy(this.model)
      .subscribe(
        data => {
          //console.log(data);
          if(data.error){
            this.errormsg='Someting went wrong, please try again.';
            this.successmsg='';
            this.subtext='Search';
          } else{
            this.errormsg='';

            this.testinomials = data.testimonials;
            this.successmsg=data.testimonials.length+' testinomials found.';
            this.subtext='Search';
            //this.dialogRef.close(this.model);
          }

        },
        error => {
          this.errormsg='Someting went wrong, please try again.';
          this.successmsg='';
          this.subtext='Search';
        });
  }

  cancel(){
    this.dialogRef.close(2);
  }

}
