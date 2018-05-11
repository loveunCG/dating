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
    templateUrl: "./addpackage.component.html",
    encapsulation: ViewEncapsulation.None,
})
export class AddpackageComponent implements OnInit {

    model: any = {};
    loading = false;
    bonus: any[] = [/[0-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, /\d/];

    @ViewChild('alertAddUser', { read: ViewContainerRef }) alertAddUser: ViewContainerRef;

    constructor(private _userService: UserService,
        private _packagesService: PackagesService,
        private _script: ScriptLoaderService,
        private _alertService: AlertService,
        private cfr: ComponentFactoryResolver,
        private _router: Router) { }

    ngOnInit() {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }
    }

    addUser(form: any) {
        Helpers.setLoading(true);
        this.loading = true;

        this._packagesService.create(this.model)
            .subscribe(
            data => {
                //console.log(data);
                this.showAlert('alertAddUser');

                this._alertService.success("Package added successfully");
                form.reset();

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
