import { Component, OnInit, Inject } from '@angular/core';
import {MatDialog, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import { UserService } from '../services/index';

@Component({
  selector: 'app-addtestinomial',
  templateUrl: './addtestinomial.component.html',
  styleUrls: ['./addtestinomial.component.css']
})
export class AddtestinomialComponent implements OnInit {
  mask: any[] =[ /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/];
model:any={};
userid:any;
errormsg='';
successmsg='';
  constructor(public dialogRef: MatDialogRef<AddtestinomialComponent>,@Inject(MAT_DIALOG_DATA) public data: any, private userService:UserService) {

    this.userid = data.userid;

  }

  ngOnInit() {
  }

  submittestinomy(form:any){
    this.model.userid = this.userid;
    this.userService.addtestinomy(this.model)
      .subscribe(
        data => {
          //console.log(data);
          if(data.error){
            this.errormsg='Someting went wrong, please try again.';
            this.successmsg='';
          } else{
            this.errormsg='';
            this.successmsg='Testinomial added successfully.';

            this.dialogRef.close(this.model);
          }

        },
        error => {
          this.errormsg='Someting went wrong, please try again.';
          this.successmsg='';
        });
  }

  cancel(){
    this.dialogRef.close(0);
  }

}
