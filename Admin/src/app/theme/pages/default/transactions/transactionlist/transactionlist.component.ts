import { Component, OnInit, AfterViewInit } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { FormsModule, Validator, NgModel } from '@angular/forms';
import { Router, RouterLink, NavigationEnd } from "@angular/router";

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./transactionlist.component.html",
    styles: []
})
export class TransactionlistComponent implements OnInit {

    users = [];
    currentUrl = '';
    uid: any;
    model: any = {};

    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _router: Router) {

        this._userService.getAllUsers()
            .subscribe(
            data => {
                //console.log(data);
                if (data.error == false) {
                    this.users = data.sdata;
                }

            },
            error => {

            });
        _router.events.subscribe((event: any) => {
            if (event instanceof NavigationEnd) {
                this.currentUrl = event.url;
                //console.log(this.currentUrl);
                let urldata = this.currentUrl.split("/");
                console.log(urldata);
                let pid = urldata[2];
                console.log('tlist', pid);
                this.uid = pid;
            }
        });
    }

    ngOnInit() {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }

        if (this.uid == 'undefined' || this.uid == undefined || this.uid === undefined) {

            this.model.userval = 0;
        } else {

            this.model.userval = this.uid;
        }




    }

    ngAfterViewInit() {
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/transactionlist.js');
    }

}
