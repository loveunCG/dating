import { Component, OnInit, AfterViewInit } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';

import { Router, RouterLink } from "@angular/router";

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./listcms.component.html",
    styles: []
})
export class ListcmsComponent implements OnInit {

    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _router: Router) {
    }

    ngOnInit() {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }

    }

    ngAfterViewInit() {
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/get_cmspages.js');

        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/deletecmstoast.js');

    }

}
