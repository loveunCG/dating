import { Component, OnInit, AfterViewInit } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';

import { Router, RouterLink } from "@angular/router";

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./userlist.component.html",
    styles: []
})
export class UserlistComponent implements OnInit {


    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _router: Router) {

    }

    ngOnInit() {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/demo/default/custom/components/datatables/base/get_userlist.js');
        // this._userService.getuserlist()
        //   .subscribe(
        //     data => {
        //       //console.log(data);
        //
        //     },
        //     error => {
        //
        //     });
    }

    ngAfterViewInit() {

        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/deleteusertoast.js');

        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/statusupdatetoast.js');

    }

    gotouser(id) {
        this._router.navigate(['/users/edit/', id]);
    }

}
