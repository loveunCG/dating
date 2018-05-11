import { Component, OnInit, AfterViewInit, ComponentFactoryResolver, ViewChild, ViewContainerRef, ViewEncapsulation, ElementRef, NgZone } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { Router, RouterLink, NavigationEnd } from "@angular/router";

import { AlertService } from "../../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../../auth/_directives/alert.component";

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./addtouser.component.html",
    styles: []
})
export class AddtouserComponent implements OnInit {

    users = [];
    model: any = {};
    amounterror = '';

    @ViewChild('alertAddUser', { read: ViewContainerRef }) alertAddUser: ViewContainerRef;

    constructor(private _userService: UserService, private _router: Router, private _alertService: AlertService, private cfr: ComponentFactoryResolver) {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }

        this._userService.getGirlUsers().subscribe(
            data => {
                //console.log(data);
                if (data.error == false) {
                    this.users = data.sdata;
                }

            },
            error => {

            });
    }

    ngOnInit() {
    }

    showAlert(target) {
        this[target].clear();
        let factory = this.cfr.resolveComponentFactory(AlertComponent);
        let ref = this[target].createComponent(factory);
        ref.changeDetectorRef.detectChanges();
    }

    addtoWallet(form: any) {

        if (form.valid) {
            if (this.model.amount > 0) {
                this.amounterror = '';
                this.showAlert('alertAddUser');
                this._userService.addtowallet(this.model.amount, this.model.adduser).subscribe(
                    data => {
                        if (data.error) {
                            this._alertService.error(data.message);
                        } else {
                            this._alertService.success(data.message);
                            form.resetForm();
                        }
                    }, error => {
                        this._alertService.error('Something went wrong');
                    }
                );
            } else {
                this.amounterror = 'Amount is required';
            }
        }
    }
}
