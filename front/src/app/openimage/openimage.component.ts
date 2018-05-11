import { Component, OnInit, Inject } from '@angular/core';
import {MatDialog, MatDialogRef, MAT_DIALOG_DATA} from '@angular/material';

@Component({
  selector: 'app-openimage',
  templateUrl: './openimage.component.html',
  styleUrls: ['./openimage.component.css']
})
export class OpenimageComponent implements OnInit {

openimg:any;

  constructor(public dialogRef: MatDialogRef<OpenimageComponent>,@Inject(MAT_DIALOG_DATA) public data: any) {
    console.log(data);
    this.openimg = data.image;
  }

  ngOnInit() {
    setTimeout(()=>{ this.close() }, 30000);
  }

  close(){
    this.dialogRef.close();
  }
}
