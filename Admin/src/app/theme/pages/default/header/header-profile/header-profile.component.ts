import { Component, ComponentFactoryResolver, OnInit, ViewChild, ViewContainerRef, ViewEncapsulation, AfterViewInit } from '@angular/core';
import { FormsModule, Validator } from '@angular/forms';
import { Helpers } from '../../../../../helpers';

import { UserService } from "../../../../.././auth/_services/user.service";

import { AlertService } from "../../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../../auth/_directives/alert.component";

import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { Router } from "@angular/router";


@Component({
    selector: ".m-grid__item.m-grid__item--fluid.m-wrapper",
    templateUrl: "./header-profile.component.html",
    encapsulation: ViewEncapsulation.None,
})

export class HeaderProfileComponent implements OnInit {
    dpusername: string = '';
    model: any = {};
    generalmodel: any = {};
    footermodel: any = {};
    adminemail = '';
    adminid = 0;
    logoimg = '';
    clogoimg = '';
    cfavimg = '';
    useralldetails: any;
    faviconimg = '';
    paths = Helpers.logoFavPath;
    currentUser: any;
    uploadlogoimg: any = {};
    uploadfavimg: any = {};
    loading = false;
    uploadboyimg: any = {};
    uploadgirlimg: any = {};
    cboyimg = '';
    boyimg = '';
    cgirlimg = '';
    girlimg = '';
    subadmin: any;

    @ViewChild('alertUpdate', { read: ViewContainerRef }) alertUpdate: ViewContainerRef;
    @ViewChild('alertgenset', { read: ViewContainerRef }) alertgenset: ViewContainerRef;
    @ViewChild('alertfooter', { read: ViewContainerRef }) alertfooter: ViewContainerRef;

    constructor(private _userService: UserService,
        private cfr: ComponentFactoryResolver,
        private _script: ScriptLoaderService,
        private _alertService: AlertService,
        private _router: Router) {

    }
    ngOnInit() {

        if (localStorage.getItem("admin") !== null) {
            this.currentUser = JSON.parse(localStorage.getItem("admin"));
            var user = this.currentUser;
            //console.log(user);
            this.dpusername = user.fullName;
            this.adminemail = user.email;
            this.adminid = user.token;
            this.subadmin = user.subadmin;
        } else {
            this.adminid = 1;
        }

        if (!this.subadmin) {
            this._userService.getSiteInfo()
                .subscribe(
                data => {
                    //console.log(data);
                    this.clogoimg = data.sdata.logoimg;
                    this.logoimg = Helpers.logoFavPath + data.sdata.logoimg;
                    this.faviconimg = data.sdata.favimg;
                    this.cfavimg = Helpers.logoFavPath + data.sdata.favimg;

                    this.cboyimg = data.sdata.boyimg;
                    this.boyimg = Helpers.logoFavPath + data.sdata.boyimg;

                    this.cgirlimg = data.sdata.girlimg;
                    this.girlimg = Helpers.logoFavPath + data.sdata.girlimg;

                    this.generalmodel.supportemail = data.sdata.supportemail;
                    this.generalmodel.imgprice = data.sdata.imgprice;
                    this.generalmodel.highlightprice = data.sdata.highlightfees;
                    this.generalmodel.commission = data.sdata.commission;
                    this.generalmodel.dailyamount = data.sdata.dailyfees;
                    this.footermodel.fburl = data.sdata.fburl;
                    this.footermodel.tweeturl = data.sdata.tweeturl;
                    this.footermodel.instaurl = data.sdata.instaurl;
                    this.footermodel.youtubeurl = data.sdata.youtubeurl;
                    this.footermodel.dribbleurl = data.sdata.dribbleurl;
                    this.footermodel.linkedinurl = data.sdata.linkedinurl;
                    this.footermodel.googlepurl = data.sdata.googlepurl;
                    this.footermodel.blogurl = data.sdata.blogurl;
                    this.footermodel.fustitle = data.sdata.fustitle;
                    this.footermodel.fusdesc = data.sdata.fusdesc;
                    this.footermodel.cptext = data.sdata.cptext;
                },
                error => {

                });

            this._userService.getAdmininfo(this.adminid)
                .subscribe(
                data => {
                    //console.log(data);
                    this.model.username = data.sdata.username;
                    this.model.email = data.sdata.email;
                    this.model.password = '';
                    // $2a$10$16871370f7d89a0ba6ed9umCExl5CsOCP3HgYWcODOMQYK.XLaXgO
                },
                error => {

                }
                );
        } else {
            this._userService.getsubadmin(this.adminid)
                .subscribe(
                data => {
                    //console.log(data);
                    this.model.username = data.sdata.name;
                    this.model.email = data.sdata.email;
                    this.model.password = '';
                    // $2a$10$16871370f7d89a0ba6ed9umCExl5CsOCP3HgYWcODOMQYK.XLaXgO
                },
                error => {

                }
                );
        }
    }

    saveProfile() {
        Helpers.setLoading(true);
        this.loading = true;
        this.model.adminid = this.currentUser.token;

        if (this.subadmin) {
            this._userService.updatesubprofile(this.model)
                .subscribe(
                data => {
                    this._userService.getsubadmin(this.adminid)
                        .subscribe(
                        data => {
                            //console.log(data);

                            this.model.password = '';
                            Helpers.setLoading(false);
                            this.loading = false;
                            this.showAlert('alertUpdate');
                            this._alertService.success("Profile updated");
                        },
                        error => {
                            Helpers.setLoading(false);
                            this.loading = false;
                            this.showAlert('alertUpdate');
                            this._alertService.error("Somthing went wrong");
                        });
                },
                error => {
                    Helpers.setLoading(false);
                    this.loading = false;
                    this.showAlert('alertUpdate');
                    this._alertService.error("Somthing went wrong");
                });
        } else {
            this._userService.updateProfile(this.model)
                .subscribe(
                data => {
                    this._userService.getAdmininfo(this.adminid)
                        .subscribe(
                        data => {
                            //console.log(data);
                            this.model.username = data.sdata.username;
                            this.model.email = data.sdata.email;

                            Helpers.setLoading(false);
                            this.loading = false;
                            this.showAlert('alertUpdate');
                            this._alertService.success("Profile updated");
                        },
                        error => {
                            Helpers.setLoading(false);
                            this.loading = false;
                            this.showAlert('alertUpdate');
                            this._alertService.error("Somthing went wrong");
                        });
                },
                error => {
                    Helpers.setLoading(false);
                    this.loading = false;
                    this.showAlert('alertUpdate');
                    this._alertService.error("Somthing went wrong");
                });

        }

    }

    saveGenSets() {
        Helpers.setLoading(true);
        this.loading = true;
        this.generalmodel.adminid = this.currentUser.token;

        this.generalmodel.logoimg = this.uploadlogoimg;
        this.generalmodel.logoimgold = this.clogoimg;

        this.generalmodel.favimg = this.uploadfavimg;
        this.generalmodel.favimgold = this.faviconimg;

        this.generalmodel.boydefimg = this.uploadboyimg;
        this.generalmodel.boydefimgold = this.cboyimg;

        this.generalmodel.girldefimg = this.uploadgirlimg;
        this.generalmodel.girldefimgold = this.cgirlimg;

        this._userService.updateGenSets(this.generalmodel)
            .subscribe(
            data => {
                this._userService.getSiteInfo()
                    .subscribe(
                    data => {
                        //console.log(data);
                        this.clogoimg = data.sdata.logoimg;
                        this.logoimg = Helpers.logoFavPath + data.sdata.logoimg;
                        this.faviconimg = data.sdata.favimg;
                        this.cfavimg = Helpers.logoFavPath + data.sdata.favimg;

                        this.cboyimg = data.sdata.boyimg;
                        this.boyimg = Helpers.logoFavPath + data.sdata.boyimg;

                        this.cgirlimg = data.sdata.girlimg;
                        this.girlimg = Helpers.logoFavPath + data.sdata.girlimg;

                        this.generalmodel.supportemail = data.sdata.supportemail;

                        for (var i = 0; i < 2; i++) {
                            $(".img-ul-clear").eq(i).trigger('click');
                        }
                        Helpers.setLoading(false);
                        this.loading = false;
                        this.showAlert('alertgenset');
                        this._alertService.success("Settings updated");
                    },
                    error => {
                        Helpers.setLoading(false);
                        this.loading = false;
                        this.showAlert('alertgenset');
                        this._alertService.error("Somthing went wrong");
                    });


            },
            error => {
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertgenset');
                this._alertService.error("Somthing went wrong");
            });

    }

    saveFooter() {
        Helpers.setLoading(true);
        this.loading = true;
        this.footermodel.adminid = this.currentUser.token;

        this._userService.updateFooter(this.footermodel)
            .subscribe(
            data => {
                this._userService.getSiteInfo()
                    .subscribe(
                    data => {
                        //console.log(data);
                        this.footermodel.fburl = data.sdata.fburl;
                        this.footermodel.tweeturl = data.sdata.tweeturl;
                        this.footermodel.instaurl = data.sdata.instaurl;
                        this.footermodel.youtubeurl = data.sdata.youtubeurl;
                        this.footermodel.dribbleurl = data.sdata.dribbleurl;
                        this.footermodel.linkedinurl = data.sdata.linkedinurl;
                        this.footermodel.googlepurl = data.sdata.googlepurl;
                        this.footermodel.blogurl = data.sdata.blogurl;
                        this.footermodel.fustitle = data.sdata.fustitle;
                        this.footermodel.fusdesc = data.sdata.fusdesc;
                        this.footermodel.cptext = data.sdata.cptext;

                        Helpers.setLoading(false);
                        this.loading = false;
                        this.showAlert('alertfooter');
                        this._alertService.success("Settings updated");
                    },
                    error => {
                        Helpers.setLoading(false);
                        this.loading = false;
                        this.showAlert('alertfooter');
                        this._alertService.error("Something went wrong");
                    });


            },
            error => {
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertfooter');
                this._alertService.error("Something went wrong");
            });

    }

    onUploadFinishedLogo(file: any) {
        console.log('file:' + file.file);

        this.uploadlogoimg[file.file.name] = JSON.stringify(file.src);
    }

    onRemovedLogo(file: any) {

        // do some stuff with the removed file.
        delete this.uploadlogoimg[file.file.name];
        console.log(this.uploadlogoimg);
    }

    onUploadStateChangedLogo(state: boolean) {
        console.log('stat' + JSON.stringify(state));
    }

    onUploadFinishedFav(file: any) {
        console.log(file.file);
        this.uploadfavimg[file.file.name] = JSON.stringify(file.src);
    }

    onRemovedFav(file: any) {

        // do some stuff with the removed file.
        delete this.uploadfavimg[file.file.name];
        console.log(this.uploadfavimg);
    }

    onUploadStateChangedFav(state: boolean) {
        console.log(JSON.stringify(state));
    }

    onUploadFinishedPb(file: any) {
        console.log(file.file);
        this.uploadboyimg[file.file.name] = JSON.stringify(file.src);
    }

    onRemovedPb(file: any) {

        // do some stuff with the removed file.
        delete this.uploadboyimg[file.file.name];
        console.log(this.uploadboyimg);
    }

    onUploadStateChangedPb(state: boolean) {
        console.log(JSON.stringify(state));
    }

    onUploadFinishedGp(file: any) {
        console.log(file.file);
        this.uploadgirlimg[file.file.name] = JSON.stringify(file.src);
    }

    onRemovedGp(file: any) {

        // do some stuff with the removed file.
        delete this.uploadgirlimg[file.file.name];
        console.log(this.uploadgirlimg);
    }

    onUploadStateChangedGp(state: boolean) {
        console.log(JSON.stringify(state));
    }

    showAlert(target) {
        this[target].clear();
        let factory = this.cfr.resolveComponentFactory(AlertComponent);
        let ref = this[target].createComponent(factory);
        ref.changeDetectorRef.detectChanges();
    }

}
