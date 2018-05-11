import { Component, OnInit, ViewEncapsulation, AfterViewInit, Injectable, ViewContainerRef } from '@angular/core';
import { Helpers } from '../../../../helpers';
import { ScriptLoaderService } from '../../../../_services/script-loader.service';
import { Router } from "@angular/router";

import { UserService } from "../../../../auth/_services/user.service";

import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { ToastsManager } from 'ng2-toastr/ng2-toastr';

@Component({
    selector: ".m-grid__item.m-grid__item--fluid.m-wrapper",
    templateUrl: "./index.component.html",
    encapsulation: ViewEncapsulation.None,
})
@Injectable()
export class IndexComponent implements OnInit, AfterViewInit {

    years = [];
    adminearnings = 0;
    totalregusers = 0;
    visitors = 0;

    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _router: Router,
        public toastr: ToastsManager,
        public vcr: ViewContainerRef) {

        this.toastr.setRootViewContainerRef(vcr);

        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }
    }
    ngOnInit() {

        var cyear = (new Date()).getFullYear();
        for (var i = 0; i < 5; i++) {
            this.years.push(cyear);
            cyear--;
        }

        this._userService.adminearnings()
            .subscribe(
            data => {
                //console.log(data);

                if (data.error == false) {
                    this.adminearnings = data.sdata.total;
                }


            },
            error => {

            });

        this._userService.visitors()
            .subscribe(
            data => {
                //console.log(data);

                if (data.error == false) {
                    this.visitors = data.sdata.total;
                }


            },
            error => {

            });

    }
    ngAfterViewInit() {
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/dashboard.js');

        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/gethomeusers.js');

        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/vendors/custom/flot/flot.bundle.js',
            'assets/app/js/earningchart.js');

    }

    //   sendNotification(){
    //     //console.log('click');
    //     //this.toastr.success('Sent', 'Success!');
    //     this.socket.emit('onclick-new-user','new user on click');
    //
    //  }
    //  sendToAdmin(){
    //    //console.log('click');
    //    //this.toastr.success('Sent', 'Success!');
    //    this.socket.emit('admin-new-user','new user register');
    //
    // }

}
