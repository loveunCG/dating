import { Component, OnInit, AfterViewInit, ComponentFactoryResolver, ViewChild, ViewContainerRef, ViewEncapsulation } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { FormsModule, Validator } from '@angular/forms';
import { Router, RouterLink } from "@angular/router";

import { AlertService } from "../../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../../auth/_directives/alert.component";

import { Helpers } from '../../../../../helpers';

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./addsubadmin.component.html",
    encapsulation: ViewEncapsulation.None,
})
export class AddsubadminComponent implements OnInit {

    model: any = {};
    loading = false;

    @ViewChild('alertAddUser', { read: ViewContainerRef }) alertAddUser: ViewContainerRef;

    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _alertService: AlertService,
        private cfr: ComponentFactoryResolver,
        private _router: Router) {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }
        this.model.privileges = {};
    }

    ngOnInit() {

    }

    addUser(form: any) {
        Helpers.setLoading(true);
        this.loading = true;

        this._userService.createsubadmin(this.model)
            .subscribe(
            data => {
                //console.log(data);
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                if (data.status == 1) {
                    this._alertService.success(data.message);
                    form.resetForm();

                    // $(".img-ul-clear").eq( 0 ).trigger('click');
                }
                if (data.status == 0) {
                    this._alertService.error(data.message);
                }

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
