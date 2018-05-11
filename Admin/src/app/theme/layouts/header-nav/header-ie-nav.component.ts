import { Component, OnInit, ViewEncapsulation, AfterViewInit, Injectable, ViewContainerRef, ViewChild } from '@angular/core';
import { Helpers } from '../../../helpers';

import { UserService } from "../../../auth/_services/user.service";

import { ToastsManager } from 'ng2-toastr/ng2-toastr';

import { ScriptLoaderService } from '../../.././_services/script-loader.service';

// import { MessagingService } from "../../.././_services/messaging.service";
import { Router, RouterLink, NavigationEnd } from "@angular/router";

declare let mLayout: any;

@Component({
    selector: "app-header-ie-nav",
    templateUrl: "./header-nav.component.html",
    encapsulation: ViewEncapsulation.None,
})
@Injectable()
export class HeaderNavIeComponent implements OnInit, AfterViewInit {
    username: string = '';
    adminemail = '';
    adminid = 0;
    lastlogin = '';
    logoimg = '';
    msgstat = false;
    msgtext = '';
    message: any;
    notifications: any = {};
    total = 0;
    subadmin: any;
    prev: any;
    currentpage: any;
    currentUrl: any;

    constructor(private _userService: UserService, public toastr: ToastsManager, vcr: ViewContainerRef, private _script: ScriptLoaderService,
        // private msgService:MessagingService,
        private _router: Router) {
        this.toastr.setRootViewContainerRef(vcr);

        var currentReference = this;
        //currentReference.showSuccess();

        // socket.on('new-user-onclick', function(data){
        //   alert('this alert onclick'+data);
        //   $('#notifytext').val(data);
        //   $('#notify').trigger('click');
        // });
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }

        if (localStorage.getItem("admin") !== null) {
            var user = JSON.parse(localStorage.getItem("admin"));
            //console.log(user);
            this.username = user.fullName;
            this.adminemail = user.email;
            this.adminid = user.token;

            this.lastlogin = user.lastlogin;
            this.subadmin = user.subadmin;
            this.prev = user.prev;
        } else {
            this.adminid = 1;
            this.lastlogin = '';
        }

        _router.events.subscribe((event: any) => {
            if (event instanceof NavigationEnd) {
                this.currentUrl = event.url;
                //console.log(this.currentUrl);
                let urldata = this.currentUrl.split("/");
                // console.log(urldata);
                var cpage = urldata[1];
                cpage = cpage.replace('-', '');
                this.currentpage = cpage;

                if (this.subadmin) {
                    console.log(this.currentpage);
                    console.log(this.prev)
                    if (this.currentpage != "header" && this.currentpage != 'logout' && this.currentpage != 'login') {
                        console.log('reindex');
                        if (!this.prev[this.currentpage]) {
                            this._router.navigate(['/index']);
                        }
                    }
                }
            }

        });


        // if(!this.subadmin){
        //   this.msgService.getPermission()
        //       this.msgService.receiveMessage()
        //       this.message = this.msgService.currentMessage
        //
        //       this._userService.getNotifications(this.adminid, this.lastlogin)
        //           .subscribe(
        //           data => {
        //               //console.log(data.sdata);
        //               this.notifications = data.sdata;
        //               this.total = parseInt(this.notifications.newuser) + parseInt(this.notifications.newcomments);
        //               //console.log(this.notifications);
        //           },
        //           error => {
        //
        //           });
        // }
    }


    ngOnInit() {

        // this.msgService.getPermission();
        // this.msgService.receiveMessage();
        // this.message = this.msgService.currentMessage;

        console.log(this.message);

        this._userService.getSiteInfo()
            .subscribe(
            data => {
                //console.log(data);
                this.logoimg = Helpers.logoFavPath + data.sdata.logoimg;
            },
            error => {

            });

    }
    showSuccess() {
        console.log(this.toastr);
        this.toastr.success('You are awesome!', 'Success!');
    }

    successToast() {
        this.toastr.success('You are awesome!', 'Success!');
    }

    ngAfterViewInit() {

        mLayout.initHeader();
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/admin-notify.js');

    }

}
