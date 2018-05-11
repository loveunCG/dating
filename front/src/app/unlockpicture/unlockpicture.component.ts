import { Component, OnInit, Inject } from '@angular/core';
import {MatDialog, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import { UserService } from '../services/index';

@Component({
  selector: 'app-unlockpicture',
  templateUrl: './unlockpicture.component.html',
  styleUrls: ['./unlockpicture.component.css']
})
export class UnlockpictureComponent implements OnInit {
info:any={};
errormsg = '';
successmsg = '';
imgprice:any;

  constructor(public dialogRef: MatDialogRef<UnlockpictureComponent>,@Inject(MAT_DIALOG_DATA) public data: any, private userService:UserService) {
      this.info = data;
      this.userService.getsetting().subscribe(
        data=>{
          this.imgprice = data.sdata.imgprice;
        }, error=>{

        }
      );
  }

  ngOnInit() {
    //console.log(this.info);

  }

  confirm(){
    this.userService.payForUnlcok(this.info.uid, this.info.amount, this.info.unlockid)
      .subscribe(
        data => {
          //console.log(data);
          if(data.error){
            this.errormsg='Someting went wrong, please try again.';
            this.successmsg='';
          } else{
            this.errormsg='';
            this.successmsg='Payment successful';
            this.dialogRef.close(1);
          }

        },
        error => {
          this.errormsg='Someting went wrong, please try again.';
          this.successmsg='';
        });
  }

  cancel(){
    this.dialogRef.close(2);
  }

}
