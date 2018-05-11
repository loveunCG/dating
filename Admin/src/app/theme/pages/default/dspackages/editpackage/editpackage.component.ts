import { Component, OnInit, AfterViewInit, ComponentFactoryResolver, ViewChild, ViewContainerRef, ViewEncapsulation } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { PackagesService } from "../../../../.././auth/_services/packages.service";

import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { FormsModule, Validator } from '@angular/forms';
import { Router, RouterLink, NavigationEnd } from "@angular/router";

import { AlertService } from "../../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../../auth/_directives/alert.component";

import { Helpers } from '../../../../../helpers';

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./editpackage.component.html",
    encapsulation: ViewEncapsulation.None,
})
export class EditpackageComponent implements OnInit {

    model: any = {};
    loading = false;
    bonus: any[] = [/[0-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/];

    packageid: any = '';
    currentUrl = '';

    @ViewChild('alertAddUser', { read: ViewContainerRef }) alertAddUser: ViewContainerRef;

    constructor(private _userService: UserService,
        private _packagesService: PackagesService,
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
                this.packageid = pid;
            }
        });
    }

    ngOnInit() {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }

        Helpers.setLoading(true);
        this.loading = true;
        this._packagesService.getById(this.packageid)
            .subscribe(
            data => {
                //console.log(data);
                this.model = data.sdata;

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

        this._packagesService.update(this.model)
            .subscribe(
            data => {
                //console.log(data);
                this.showAlert('alertAddUser');

                this._alertService.success("Package updated successfully");

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

}
