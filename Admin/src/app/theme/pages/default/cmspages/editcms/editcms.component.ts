import { Component, OnInit, AfterViewInit, ComponentFactoryResolver, ViewChild, ViewContainerRef, ViewEncapsulation } from '@angular/core';

import { UserService } from "../../../../.././auth/_services/user.service";
import { CmspageService } from "../../../../.././auth/_services/cmspage.service";

import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { FormsModule, Validator } from '@angular/forms';
import { Router, RouterLink, NavigationEnd } from "@angular/router";

import { AlertService } from "../../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../../auth/_directives/alert.component";

import { Helpers } from '../../../../../helpers';

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./editcms.component.html",
    encapsulation: ViewEncapsulation.None,
})
export class EditcmsComponent implements OnInit {

    model: any = {};
    loading = false;
    mask: any[] = [/[0-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/];

    uploadpath = Helpers.logoFavPath + 'cmspages';

    currentpostimg = '';
    currentbannerimg = '';
    currentheaderimg = '';

    uploadheaderimg = {};
    uploadbannerimg = {};
    uploadpostimg = {};

    currentUrl = '';
    pageid: any = '';

    contacttrue = false;

    @ViewChild('alertAddUser', { read: ViewContainerRef }) alertAddUser: ViewContainerRef;

    constructor(private _userService: UserService,
        private _cmspageService: CmspageService,
        private _script: ScriptLoaderService,
        private _alertService: AlertService,
        private cfr: ComponentFactoryResolver,
        private _router: Router) {

        _router.events.subscribe((event: any) => {
            if (event instanceof NavigationEnd) {
                this.currentUrl = event.url;
                //console.log(this.currentUrl);
                let urldata = this.currentUrl.split("/");
                //console.log(urldata);
                let pid = urldata[3];
                this.pageid = pid;
            }
        });

    }

    ngOnInit() {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }
        if (this.pageid == 2) {
            this.contacttrue = true;
        }
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/demo/default/custom/components/forms/widgets/bootstrap-markdown.js');
        (<any>$('[data-provide="markdown"]')).markdown();

        Helpers.setLoading(true);
        this.loading = true;
        this._cmspageService.getById(this.pageid)
            .subscribe(
            data => {
                //console.log(data);
                this.model = data.sdata;
                this.currentpostimg = this.model.pimg;
                this.currentbannerimg = this.model.bannerimg;
                this.currentheaderimg = this.model.headerimg;

                Helpers.setLoading(false);
                this.loading = false;

            },
            error => {
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                this._alertService.error("Somthing went wrong");
            });

    }

    addUser(form: any) {
        Helpers.setLoading(true);
        this.loading = true;

        this.model.pid = this.pageid;

        this.model.postimg = this.uploadpostimg;
        this.model.oldpostimg = this.currentpostimg;

        this.model.headerimg = this.uploadheaderimg;
        this.model.oldheaderimg = this.currentheaderimg;

        this.model.bannerimg = this.uploadbannerimg;
        this.model.oldbannerimg = this.currentbannerimg;

        this._cmspageService.update(this.model)
            .subscribe(
            data => {
                //console.log(data);
                this.showAlert('alertAddUser');
                if (data.code == 1) {
                    this._alertService.success("Page updated successfully");
                    for (var i = 0; i < 3; i++) {
                        $(".img-ul-clear").eq(i).trigger('click');
                    }

                    //window.location.reload();
                    this.currentpostimg = data.postimg;
                    this.currentheaderimg = data.headerimg;
                    this.currentbannerimg = data.bannerimg;

                }

                if (data.code == 3) {
                    this._alertService.error("Something went wrong");
                }
                Helpers.setLoading(false);
                this.loading = false;
            },
            error => {
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                this._alertService.error("Somthing went wrong");
            });
    }

    showAlert(target) {
        this[target].clear();
        let factory = this.cfr.resolveComponentFactory(AlertComponent);
        let ref = this[target].createComponent(factory);
        ref.changeDetectorRef.detectChanges();
    }

    genHandle(val) {
        let alink = val.toLowerCase().replace(/[^A-Z0-9-]/ig, "-");
        this.model.handle = alink;
    }

    onUploadFinishedHeader(file: any) {
        console.log(file.file);
        this.uploadheaderimg[file.file.name] = JSON.stringify(file.src);

    }

    onRemovedHeader(file: any) {

        // do some stuff with the removed file.
        delete this.uploadheaderimg[file.file.name];
        console.log(this.uploadheaderimg);
    }

    onUploadStateChangedHeader(state: boolean) {
        console.log(JSON.stringify(state));
    }

    onUploadFinishedBanner(file: any) {
        console.log(file.file);
        this.uploadbannerimg[file.file.name] = JSON.stringify(file.src);

    }

    onRemovedBanner(file: any) {

        // do some stuff with the removed file.
        delete this.uploadbannerimg[file.file.name];
        console.log(this.uploadbannerimg);
    }

    onUploadStateChangedBanner(state: boolean) {
        console.log(JSON.stringify(state));
    }

    onUploadFinishedPost(file: any) {
        console.log(file.file);
        this.uploadpostimg[file.file.name] = JSON.stringify(file.src);

    }

    onRemovedPost(file: any) {

        // do some stuff with the removed file.
        delete this.uploadpostimg[file.file.name];
        console.log(this.uploadpostimg);
    }

    onUploadStateChangedPost(state: boolean) {
        console.log(JSON.stringify(state));
    }

}
