import { Component, OnInit, AfterViewInit } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { FormsModule, Validator, NgModel } from '@angular/forms';
import { Router, RouterLink } from "@angular/router";

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./chatlist.component.html",
    styles: []
})
export class ChatlistComponent implements OnInit {

    users = [];
    model: any = {};

    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _router: Router) {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }

        this._userService.getAllUsers()
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
            'assets/app/js/chatlist.js');
    }

}
