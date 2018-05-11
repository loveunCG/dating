import { Component, OnInit, Inject } from '@angular/core';
import {MatDialog, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';
import { UserService } from '../services/index';

@Component({
  selector: 'app-uploadvideo',
  templateUrl: './uploadvideo.component.html',
  styleUrls: ['./uploadvideo.component.css']
})
export class UploadvideoComponent implements OnInit {
model:any={};
def:boolean;
videofile:any={};
errormsg='';
successmsg='';

  constructor(public dialogRef: MatDialogRef<UploadvideoComponent>,@Inject(MAT_DIALOG_DATA) public data: any, private userService:UserService) {
    //console.log(data);
  }

  ngOnInit() {
    this.model.videotype = 'upload';
    this.def=true;
  }
  onNoClick(): void {
    this.dialogRef.close('returndata');
  }

  addvideo(form:any){
    if(this.model.videotype=='upload'){
    console.log(this.videofile);
    this.userService.uploadVideo(this.videofile)
      .subscribe(
        data => {
          //console.log(data);
          if(data.error){
            this.errormsg='Someting went wrong, please try again.';
            this.successmsg='';
          } else{
            this.errormsg='';
            this.successmsg='Video uploaded successfully.';
            this.model.videolink = data.upvlink;
            this.dialogRef.close(this.model);
          }

        },
        error => {
          this.errormsg='Someting went wrong, please try again.';
          this.successmsg='';
        });

      } else{
        this.dialogRef.close(this.model);
      }
  }

  changeVt(val){
    //console.log(val);
      if(val == 'upload'){
        this.def = true;
      } else{
        this.def = false;
      }
  }

  onFileChange(event) {
    let reader = new FileReader();
    let fileobj:any={};
    if(event.target.files && event.target.files.length > 0) {
      let file = event.target.files[0];
      reader.readAsDataURL(file);
      reader.onload = () => {
        fileobj.name = file.name;
        fileobj.type = file.type;
        fileobj.value = reader.result.split(',')[1];
        this.videofile = fileobj;
      };
    }
  }

  cancel(){
    this.dialogRef.close(undefined);
  }

}
