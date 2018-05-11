import { Component, OnInit, AfterViewInit } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';

import { Router, RouterLink, NavigationEnd } from "@angular/router";

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./earninglist.component.html",
    styles: [
        '.daterangepicker { left: 620.469px !important;width:60%; }'
    ]
})
export class EarninglistComponent implements OnInit {

    users = [];
    currentUrl = '';

    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _router: Router) {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }



        this._userService.getGirlUsers()
            .subscribe(
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
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/earningslist.js');
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/earningtoast.js');
    }

}
